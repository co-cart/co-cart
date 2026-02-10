# ETag Support for Caching

CoCart supports HTTP ETags on cart and product endpoints, enabling efficient caching for headless and mobile applications.

## What Are ETags?

An ETag (Entity Tag) is an HTTP header that acts as a version identifier for a resource. When the resource changes, the ETag changes. This allows clients to:

- **Reduce bandwidth** - Only download data when it has actually changed
- **Improve performance** - 304 responses are nearly instant (no body to serialize)
- **Detect stale data** - Know immediately if local state is outdated

## Supported Endpoints

### Cart Endpoints

Cart endpoints return ETags on **all methods** (GET, POST, PUT, DELETE) because mutations return the updated cart state. This allows you to use the ETag from a mutation response immediately in subsequent GET requests.

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/cocart/v2/cart` | GET | Full cart contents |
| `/cocart/v2/cart/items` | GET | Cart items only |
| `/cocart/v2/cart/items/count` | GET | Item count |
| `/cocart/v2/cart/totals` | GET | Cart totals |
| `/cocart/v2/cart/add-item` | POST | Add item (returns cart + ETag) |
| `/cocart/v2/cart/item/{key}` | GET, PUT, DELETE | Item operations (return cart + ETag) |
| `/cocart/v2/cart/update` | POST | Update cart (returns cart + ETag) |
| `/cocart/v2/cart/clear` | POST | Clear cart (returns cart + ETag) |

### Product Endpoints

Product endpoints return ETags on **GET only** (products are read-only resources).

| Endpoint | Description |
|----------|-------------|
| `GET /wp-json/cocart/v2/products` | Product collection |
| `GET /wp-json/cocart/v2/products/{id}` | Single product |
| `GET /wp-json/cocart/v2/products/categories` | Category collection |
| `GET /wp-json/cocart/v2/products/categories/{id}` | Single category |
| `GET /wp-json/cocart/v2/products/tags` | Tag collection |
| `GET /wp-json/cocart/v2/products/tags/{id}` | Single tag |
| `GET /wp-json/cocart/v2/products/attributes` | Attribute collection |
| `GET /wp-json/cocart/v2/products/attributes/{id}` | Single attribute |
| `GET /wp-json/cocart/v2/products/reviews` | Review collection |
| `GET /wp-json/cocart/v2/products/reviews/{id}` | Single review |

## Basic Usage

### 1. First Request - Get the ETag

```bash
curl -i https://example.com/wp-json/cocart/v2/cart?cart_key=abc123
```

Response:

```http
HTTP/1.1 200 OK
ETag: W/"9f86d081884c7d659a2feaa0c55ad015"
Content-Type: application/json

{"cart_hash":"9f86d081884c7d659a2feaa0c55ad015","items":[...],...}
```

### 2. Subsequent Requests - Use If-None-Match

```bash
curl -i https://example.com/wp-json/cocart/v2/cart?cart_key=abc123 \
  -H 'If-None-Match: W/"9f86d081884c7d659a2feaa0c55ad015"'
```

If cart unchanged:

```http
HTTP/1.1 304 Not Modified
ETag: W/"9f86d081884c7d659a2feaa0c55ad015"
```

If cart changed:

```http
HTTP/1.1 200 OK
ETag: W/"a1b2c3d4e5f6..."
Content-Type: application/json

{"cart_hash":"a1b2c3d4e5f6...","items":[...],...}
```

## JavaScript Example

```javascript
class CartCache {
  constructor(cartKey) {
    this.cartKey = cartKey;
    this.etag = null;
    this.cachedCart = null;
  }

  async getCart() {
    const headers = {};

    if (this.etag) {
      headers['If-None-Match'] = this.etag;
    }

    const response = await fetch(
      `/wp-json/cocart/v2/cart?cart_key=${this.cartKey}`,
      { headers }
    );

    // Store new ETag
    const newEtag = response.headers.get('ETag');
    if (newEtag) {
      this.etag = newEtag;
    }

    // Cart unchanged - use cached data
    if (response.status === 304) {
      return this.cachedCart;
    }

    // Cart changed - update cache
    this.cachedCart = await response.json();
    return this.cachedCart;
  }
}

// Usage
const cart = new CartCache('customer_abc123');
const data = await cart.getCart(); // Full response
const data2 = await cart.getCart(); // 304 if unchanged
```

## React Hook Example

```javascript
import { useState, useCallback } from 'react';

function useCart(cartKey) {
  const [cart, setCart] = useState(null);
  const [etag, setEtag] = useState(null);
  const [loading, setLoading] = useState(false);

  const refreshCart = useCallback(async () => {
    setLoading(true);

    const headers = {};
    if (etag) {
      headers['If-None-Match'] = etag;
    }

    try {
      const response = await fetch(
        `/wp-json/cocart/v2/cart?cart_key=${cartKey}`,
        { headers }
      );

      const newEtag = response.headers.get('ETag');
      if (newEtag) {
        setEtag(newEtag);
      }

      if (response.status !== 304) {
        const data = await response.json();
        setCart(data);
      }
    } finally {
      setLoading(false);
    }
  }, [cartKey, etag]);

  return { cart, loading, refreshCart };
}
```

## Mutation Workflow: Getting ETags from Cart Changes

Cart mutations (POST, PUT, DELETE) return ETags in the response, allowing you to immediately use them for subsequent conditional GET requests. This eliminates the need for an initial GET request just to establish an ETag baseline.

### Common Pattern: Add Item → Conditional GET

```javascript
// Step 1: Add item to cart (mutation returns cart + ETag)
const addResponse = await fetch('/wp-json/cocart/v2/cart/add-item', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    product_id: 123,
    quantity: 1,
    cart_key: 'customer_abc123'
  })
});

const cart = await addResponse.json();
const etag = addResponse.headers.get('ETag'); // ← Get ETag from mutation response

// Step 2: Store ETag with cart data
localStorage.setItem('cart', JSON.stringify(cart));
localStorage.setItem('cart_etag', etag);

// Step 3: Later, check if cart changed (conditional GET)
const checkResponse = await fetch('/wp-json/cocart/v2/cart?cart_key=customer_abc123', {
  headers: { 'If-None-Match': etag }
});

if (checkResponse.status === 304) {
  // Cart unchanged, use cached data
  const cachedCart = JSON.parse(localStorage.getItem('cart'));
  console.log('Using cached cart:', cachedCart);
} else {
  // Cart changed, update cache with new data
  const updatedCart = await checkResponse.json();
  const newEtag = checkResponse.headers.get('ETag');
  localStorage.setItem('cart', JSON.stringify(updatedCart));
  localStorage.setItem('cart_etag', newEtag);
  console.log('Cart updated:', updatedCart);
}
```

### Update Quantity Example

```javascript
// Update item quantity (returns cart + ETag)
const updateResponse = await fetch('/wp-json/cocart/v2/cart/item/abc123', {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    quantity: 3,
    cart_key: 'customer_abc123'
  })
});

const updatedCart = await updateResponse.json();
const newEtag = updateResponse.headers.get('ETag');

// Store new cart state and ETag
myCache.set('cart', updatedCart);
myCache.set('etag', newEtag);

// Next GET request can use the new ETag
const nextCheck = await fetch('/wp-json/cocart/v2/cart?cart_key=customer_abc123', {
  headers: { 'If-None-Match': newEtag }
});
// Returns 304 if cart hasn't changed since the update
```

### Remove Item Example

```javascript
// Remove item from cart (returns updated cart + ETag)
const deleteResponse = await fetch('/wp-json/cocart/v2/cart/item/abc123?cart_key=customer_abc123', {
  method: 'DELETE'
});

const cartAfterDelete = await deleteResponse.json();
const etagAfterDelete = deleteResponse.headers.get('ETag');

// Poll cart state using ETag from delete operation
setInterval(async () => {
  const pollResponse = await fetch('/wp-json/cocart/v2/cart?cart_key=customer_abc123', {
    headers: { 'If-None-Match': etagAfterDelete }
  });

  if (pollResponse.status === 304) {
    console.log('Cart still unchanged');
  } else {
    console.log('Cart has changed, updating UI');
    const newCart = await pollResponse.json();
    updateUI(newCart);
  }
}, 5000);
```

### Why This Matters

**Without mutation ETags:**
```
1. User starts with empty cart
2. POST /cart/add-item → Returns cart (no ETag)
3. GET /cart → Must fetch full response to get ETag
4. GET /cart → Now can use If-None-Match
```

**With mutation ETags:**
```
1. User starts with empty cart
2. POST /cart/add-item → Returns cart + ETag
3. GET /cart → Can immediately use If-None-Match (304 if unchanged)
```

This eliminates one full cart fetch, saving bandwidth and improving performance for the common workflow where users start with an empty cart and add items.

## CORS Configuration

For cross-origin requests, CoCart exposes the ETag header and allows If-None-Match:

```http
Access-Control-Expose-Headers: ETag
Access-Control-Allow-Headers: If-None-Match
```

No additional configuration is required.

## Technical Details

### Weak vs Strong ETags

CoCart uses **weak ETags** (prefixed with `W/`):

```
W/"9f86d081884c7d659a2feaa0c55ad015"
```

Weak ETags indicate semantic equivalence rather than byte-for-byte identity. This is appropriate because:

- Response may include varying timestamps
- Field ordering in JSON is not guaranteed
- Query parameters like `fields` affect response structure

### How Cart ETags are Generated

The ETag for cart endpoints is derived from the cart hash but is not identical to it. The cart hash is calculated from:

- Cart session contents (items, quantities, variations)
- Cart total price

```php
$cart_hash = md5( wp_json_encode( $cart_session ) . $cart_total['total'] );
```

The ETag is then generated by hashing the cart hash with a salt and version:

```php
$etag = md5( 'cocart_etag_' . $cart_hash . COCART_VERSION );
```

This means:
- The ETag is opaque and cannot be reverse-engineered to the cart hash
- ETags automatically invalidate when CoCart is updated
- The `cart_hash` in the response body remains useful for client-side comparison

### How Product ETags are Generated

Product ETags are based on different data depending on the endpoint:

| Endpoint Type | ETag Based On |
|---------------|---------------|
| Single product | Product ID + `post_modified` date + price + stock + stock status |
| Product collection | Latest `post_modified` + product count + query params |
| Single category/tag | Term ID + product count in term |
| Category/tag collection | Total terms + latest term ID + query params |
| Single review | Review ID + `comment_date` |
| Review collection | Latest review date + review count + query params |
| Attributes | Attribute count |

**Single product sensitivity**: The ETag for single products includes price, stock quantity, and stock status. This ensures the ETag changes when an order reduces stock or when prices are updated programmatically (which may not update `post_modified`).

**Query parameter sensitivity**: Product collection ETags include pagination (`page`, `per_page`), sorting (`orderby`, `order`), and filtering (`category`, `tag`, `search`) parameters. This ensures different queries return different ETags.

### Empty Results

- Empty carts do not return an ETag header
- Product collections with no results will not return an ETag
- Single resources that don't exist will return a 404, not 304

## Customization

### Adding Custom Routes

Use filters to add ETag support to custom endpoints:

```php
// Add custom cart-like routes (uses cart hash logic).
add_filter( 'cocart_etag_cart_routes', function( $routes ) {
    $routes[] = '/^cocart\/v2\/my-cart-endpoint$/';
    return $routes;
});

// Add custom product-like routes (uses product modified date logic).
add_filter( 'cocart_etag_product_routes', function( $routes ) {
    $routes[] = '/^cocart\/v2\/my-product-endpoint$/';
    return $routes;
});

// Add any additional routes (for third-party plugins like CoCart Plus).
add_filter( 'cocart_etag_routes', function( $routes ) {
    $routes[] = '/^cocart\/v2\/customer\/dashboard$/';
    return $routes;
});
```

| Filter | Purpose |
|--------|---------|
| `cocart_etag_cart_routes` | Cart endpoints using cart hash logic |
| `cocart_etag_product_routes` | Product endpoints using modified date logic |
| `cocart_etag_routes` | Any additional endpoints (third-party plugins) |

## How Performance Benefits Work

Understanding when and how ETags improve performance is important for effective implementation.

### The Client Must Send If-None-Match

ETags only provide a performance benefit when **the client sends the `If-None-Match` header** with the previously received ETag value. Without this header, the server has nothing to compare against and must process the request fully.

| Request Type | Server Processing | Response |
|--------------|-------------------|----------|
| No `If-None-Match` header | Full cart load, all queries | 200 + full body |
| `If-None-Match` with matching ETag | Single DB query only | 304, no body |
| `If-None-Match` with stale ETag | Full cart load, all queries | 200 + full body |

### What Happens With a Matching ETag

When you send `If-None-Match` and the ETag matches:

1. CoCart intercepts the request **before** the cart endpoint runs
2. Only a single database query executes (to fetch the cart hash)
3. The cart is **NOT** loaded into memory
4. WooCommerce cart calculations are **NOT** performed
5. A 304 response is returned immediately with no body

This is significantly faster than a full cart request because:
- No cart session deserialization
- No product data loading
- No price calculations
- No tax calculations
- No shipping calculations
- No JSON serialization of the response

### What Happens Without If-None-Match

If you do not send the `If-None-Match` header:

1. The full cart endpoint executes normally
2. All database queries run
3. The cart is fully loaded and calculated
4. A 200 response is returned with the full cart body
5. The `CoCart-Cache` header will show `MISS`

**This is expected behavior** - without knowing what ETag the client has, the server cannot determine if the cart has changed.

### Common Misconception

Simply receiving an ETag in the response does not make subsequent requests faster. You must:

1. **Store the ETag** when you receive it
2. **Send it back** in the `If-None-Match` header on subsequent requests
3. **Handle the 304 response** by using your cached data

### Testing ETag Performance

To verify ETags are working correctly:

```bash
# Request 1: Get cart and ETag (full processing)
curl -i -w "\nTime: %{time_total}s\n" \
  https://example.com/wp-json/cocart/v2/cart?cart_key=abc123

# Request 2: Send If-None-Match (should be faster, 304 response)
curl -i -w "\nTime: %{time_total}s\n" \
  -H 'If-None-Match: W/"your-etag-here"' \
  https://example.com/wp-json/cocart/v2/cart?cart_key=abc123
```

The second request should complete noticeably faster and return a 304 status.

## Best Practices

1. **Always store the ETag** - Save it alongside your cached cart data
2. **Always send If-None-Match** - Include the stored ETag on every GET request
3. **Handle both responses** - Your code should work whether you get 200 or 304
4. **Invalidate on mutations** - Clear your cached ETag after POST/PUT/DELETE operations
5. **Do not cache empty carts** - No ETag means no caching benefit

## Troubleshooting

### ETag header not appearing

- Ensure you are making a GET request
- Check the cart is not empty
- Verify the endpoint is in the supported list

### Always getting 200 instead of 304

- Confirm you are sending `If-None-Match` header (not `If-Match`)
- Check the ETag value includes the `W/` prefix and quotes
- Verify the cart has not changed between requests
