# How to create a cart session and access it <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [Where are the cart sessions stored?](#where-are-the-cart-sessions-stored)
- [How is a cart session created?](#how-is-a-cart-session-created)
- [What is a Cart Key?](#what-is-a-cart-key)
- [Can I create a Cart Key of my own?](#can-i-create-a-cart-key-of-my-own)
- [How does CoCart access the cart session?](#how-does-cocart-access-the-cart-session)
  - [For a Guest Customer](#for-a-guest-customer)
    - [Finding the Cart Key](#finding-the-cart-key)
    - [Updating the cart as a guest](#updating-the-cart-as-a-guest)
  - [For a Registered Customer](#for-a-registered-customer)
    - [How do I authenticate for a registered customer?](#how-do-i-authenticate-for-a-registered-customer)

## Where are the cart sessions stored?

CoCart creates a single table in your WordPress database upon installing to store the cart sessions. The table name is `cocart_carts`. You will see a prefix in front of the table name based on your WordPress configuration so by default it would be labelled as `wp_cocart_carts`.

## How is a cart session created?

A cart session is created after the first product is added to the cart. Once that cart session is created in the database to save the users cart information, it is given a unique cart key, an expiration timestamp and when the cart will expire if not updated since.

## What is a Cart Key?

A cart key is what identifies the cart session stored in the database.

## Can I create a Cart Key of my own?

If you are wanting to create a cart key of your own for guest customers, it cannot be longer than _42 characters_ as that is the limit for storing the key in the database.

> A cart session will not be created with your cart key until the first item is added to the cart.

## How does CoCart access the cart session?

When requesting the cart session via the Cart API, you first have to decide on the state of the user. Are they a returning customer or a guest customer? Following the state of the user is important to track the cart session throughout the users shopping experience.

- [Guest Customer](#for-a-guest-customer)
- [Registered Customer](#for-a-registered-customer)

### For a Guest Customer

For guest customers, in order to identify the cart session for all Cart API requests you make, you need to store the **cart key** somewhere in your application after it is given to you. This is essentially your token so remembering it is important.

Here are a few storage suggestions for the cart key:

- using a cookie
- or using local storage
- or using a wrapper like localForage or PouchDB
- or using local database like SQLite or Hive
- or your choice based on the app you developed

Once you have captured the cart key in your application, you will then need to use the captured cart key for all future Cart API requests. Not doing so will lead to several cart sessions being created with no way of getting the correct cart session back the next time you add a product or make a change to the cart.

> Example shows getting the cart session with a cart key using [CoCart JS library](https://github.com/co-cart/cocart-js-lib).

```js
CoCart.get("cart?cart_key=43de87a471f517c779841b08be852b26")
.then((response) => {
  // Successful request
  console.log("Response Status:", response.status);
  console.log("Response Headers:", response.headers);
  console.log("Response Data:", response.data);
})
.catch((error) => {
  // Invalid request, for 4xx and 5xx statuses
  console.log("Response Status:", error.response.status);
  console.log("Response Headers:", error.response.headers);
  console.log("Response Data:", error.response.data);
})
.finally(() => {
  // Always executed.
});
```

#### Finding the Cart Key

When the cart session is created you will see the cart key returned in the cart response or via the returned headers. In the cart response you will find it under the field `cart_key`.

```json
{
    "cart_hash": "cd541ec1948b600728b49c198b1f4d84",
    "cart_key": "43de87a471f517c779841b08be852b26",
    "currency": {
        "currency_code": "USD",
        "currency_symbol": "$",
        "currency_minor_unit": 2,
        "currency_decimal_separator": ".",
        "currency_thousand_separator": ",",
        "currency_prefix": "$",
        "currency_suffix": ""
    },
    ...
}
```

However, if you filtered the cart response to return only the fields you require, the `cart_key` field may not be there.

Which is why as a backup, you can still find the cart key in the returned headers. Just look for `CoCart-API-Cart-Key`.

> Note: The cart key only returns for guest sessions to help identify the cart session. Logged in users or authenticated users don't require the cart key when using the Cart API.

#### Updating the cart as a guest

When you want to update the cart for a guest session, you need to provide the cart key and query it to the end of the endpoint. This tells CoCart to load that cart session before proceeding with the action requested.

> Here we are showing another product being added to the same cart using [CoCart JS library](https://github.com/co-cart/cocart-js-lib).

```js
CoCart.post("cart/add-item?cart_key=43de87a471f517c779841b08be852b26", {
  id: "71",
  quantity: "2"
})
.then((response) => {
  // Successful request
  console.log("Response Status:", response.status);
  console.log("Response Headers:", response.headers);
  console.log("Response Data:", response.data);
})
.catch((error) => {
  // Invalid request, for 4xx and 5xx statuses
  console.log("Response Status:", error.response.status);
  console.log("Response Headers:", error.response.headers);
  console.log("Response Data:", error.response.data);
})
.finally(() => {
  // Always executed.
});
```

### For a Registered Customer

For a registered customer, using a cart key is not needed. This is because we identify the cart session using the registered users ID instead and has already fetched the cart session for you.

This allows you to update the cart without having to worry about the cart key.

However, if your user started out as a guest but does have an account on your site then you can use the cart key to transfer over the session as you authenticate them.

#### How do I authenticate for a registered customer?

Assuming you are using the latest [CoCart JS library](https://github.com/co-cart/cocart-js-lib) you would need to pass the customers login credentials before making any Cart API request in the library options.

```js
// import CoCartAPI from "@cocart/cocart-rest-api"; // Supports ESM
const CoCartAPI = require("@cocart/cocart-rest-api").default;

const CoCart = new CoCartAPI({
  url: "https://example-store.com",
  username: 'sebtest123', // Username, email address or billing phone number
  password: 'happycoding24'
});
```

If you are using [CoCart's JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication/) plugin then you need to pass the token in the library options as so.

```js
// import CoCartAPI from "@cocart/cocart-rest-api"; // Supports ESM
const CoCartAPI = require("@cocart/cocart-rest-api").default;

const CoCart = new CoCartAPI({
  url: "https://example-store.com",
  token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOlwvXC9jb2NhcnQtcGx1Z2luLXRydW5rLmxvY2FsIiwiaWF0IjoxNjc3ODg0ODQyLCJleHAiOjE2NzgwNTc2NDIsImRhdGEiOnsidXNlciI6eyJpZCI6MSwidXNlcm5hbWUiOiJzZWJhc3RpZW4iLCJwYXNzd29yZCI6ImRhcmtQYW5kYTI2TWF5In0sInNlY3JldF9rZXkiOiJ0aGlzaXN0aGV3YXkifX0.tGW-wGKIv_BnLz6rfQPMx3pvBKnxB9UwyT2IYK2BoKg'
});
```

Other methods require passing **Authorization Headers**.

- **Basic Authentication**

```php
'Authorization: Basic ' . base64_encode($username . ':' . $password)`
```

If you have the _base64_ string of the login credentials already you can put it as is instead of generating the _base64_ encoded string. - **Requires CoCart v4 or above**.

- **JWT Authentication** - [Requires CoCart JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication/) plugin.

```php
'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwOlwvXC9jb2NhcnQtcGx1Z2luLXRydW5rLmxvY2FsIiwiaWF0IjoxNjc3ODg0ODQyLCJleHAiOjE2NzgwNTc2NDIsImRhdGEiOnsidXNlciI6eyJpZCI6MSwidXNlcm5hbWUiOiJzZWJhc3RpZW4iLCJwYXNzd29yZCI6ImRhcmtQYW5kYTI2TWF5In0sInNlY3JldF9rZXkiOiJ0aGlzaXN0aGV3YXkifX0.tGW-wGKIv_BnLz6rfQPMx3pvBKnxB9UwyT2IYK2BoKg'
```

**Queried on the endpoint**

> ⚠️ We don't recommend this method but for basic authentication it's supported.

```sh
curl "https://example-store.com/wp-json/cocart/v2/cart?username=$username&password=$password"
```

<!-- FEEDBACK -->

---

🐞 Found a mistake, or have a suggestion? [Leave feedback about this document here.](https://github.com/co-cart/co-cart/issues/new?assignees=&labels=type%3A+documentation&template=doc_feedback.md&title=Feedback+on+./docs/how-to-create-and-access-cart.md)

<!-- /FEEDBACK -->
