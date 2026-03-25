# CoCart REST API Unit Tests

This directory contains the unit tests for the CoCart REST API plugin. The tests are built using PHPUnit and follow WordPress testing conventions.

## Requirements

- PHP 7.4 or higher
- WordPress 6.3 or higher
- WooCommerce 7.0 or higher
- MySQL/MariaDB database
- Composer

## Installation

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Set up WordPress test environment:**
   ```bash
   bin/install-wp-tests.sh <db-name> <db-user> <db-password> [db-host] [wp-version]
   ```
   
   Example:
   ```bash
   bin/install-wp-tests.sh cocart_test root password localhost latest
   ```

## Running Tests

### Run all tests:
```bash
composer test
```

### Run tests with coverage:
```bash
composer test:coverage
```

### Run specific test file:
```bash
./vendor/bin/phpunit tests/unit/test-cart-controller.php
```

### Run specific test method:
```bash
./vendor/bin/phpunit --filter test_add_item_to_cart tests/unit/test-cart-controller.php
```

## Test Structure

### Framework Classes

- `tests/framework/class-cocart-unit-test-case.php` - Base unit test case
- `tests/framework/class-cocart-rest-test-case.php` - REST API test case
- `tests/framework/class-cocart-api-test-case.php` - CoCart API test case

### Test Files

- `tests/unit/test-cart-controller.php` - Cart functionality tests
- `tests/unit/test-products-controller.php` - Product functionality tests
- `tests/unit/test-authentication.php` - Authentication tests
- `tests/unit/test-sessions-controller.php` - Sessions API tests

## Writing Tests

### Basic Test Structure

```php
<?php
/**
 * Test Example
 *
 * @package CoCart\Tests\Unit
 */

class Test_Example extends CoCart_API_Test_Case {

    public function test_example() {
        // Your test code here
        $this->assertTrue( true );
    }
}
```

### Available Test Methods

#### Cart Operations
- `add_item_to_cart( $product_id, $quantity, $version, $params )`
- `remove_item_from_cart( $item_key, $version, $params )`
- `update_item_in_cart( $item_key, $quantity, $version, $params )`
- `clear_cart( $version, $params )`
- `get_cart( $version, $params )`
- `get_cart_totals( $version, $params )`
- `get_cart_count( $version, $params )`

#### Product Operations
- `create_product( $args )` - Create a test product
- `create_customer( $args )` - Create a test customer
- `create_order( $args )` - Create a test order

#### Authentication
- `create_wc_api_key( $args )` - Create WooCommerce API key
- `authenticate_with_wc_api_key( $key_data )` - Get auth headers
- `authenticated_admin_request( $method, $endpoint, $params, $key_data )` - Make authenticated admin request
- `get_sessions( $key_data )` - Get sessions (requires API key)
- `get_session( $cart_key, $key_data )` - Get specific session (requires API key)

#### Assertions
- `assert_rest_response_status( $expected, $response )`
- `assert_rest_response_content_type( $expected, $response )`
- `assert_rest_response_contains( $expected, $response )`
- `assert_rest_response_error( $expected, $response )`
- `assert_cart_is_empty( $version )`
- `assert_cart_has_items( $expected, $version )`
- `assert_cart_contains_product( $product_id, $version )`

### Example Tests

#### Cart Test Example
```php
public function test_add_product_to_cart() {
    // Create a test product
    $product = $this->create_product( array(
        'name'          => 'Test Product',
        'regular_price' => '10.00',
    ) );

    // Add product to cart
    $response = $this->add_item_to_cart( $product->get_id(), 2 );

    // Assert response
    $this->assert_rest_response_status( 200, $response );

    // Verify cart contains the product
    $this->assert_cart_contains_product( $product->get_id() );
}
```

#### Sessions API Test Example
```php
public function test_get_sessions_with_api_key() {
    // Create WooCommerce API key
    $key_data = $this->create_wc_api_key( array(
        'description' => 'Test Sessions API Key',
        'permissions' => 'read_write',
    ) );

    // Get sessions (requires admin authentication)
    $response = $this->get_sessions( $key_data );

    // Assert response
    $this->assert_rest_response_status( 200, $response );

    $data = $response->get_data();
    $this->assertArrayHasKey( 'sessions', $data );
}
```

#### Authentication Test Example
```php
public function test_basic_authentication() {
    // Create user
    $user = $this->create_customer( array(
        'username' => 'testuser',
        'email'    => 'test@example.com',
    ) );

    wp_set_password( 'password123', $user->get_id() );

    // Create basic auth header
    $auth_header = 'Basic ' . base64_encode( 'testuser:password123' );

    // Make authenticated request
    $response = $this->cocart_v2_request( 'POST', 'login', array(), array(
        'Authorization' => $auth_header,
    ) );

    $this->assert_rest_response_status( 200, $response );
}
```

## Authentication Methods

### Basic Authentication
For customer-facing endpoints, CoCart supports basic authentication using username and password:

```php
$auth_header = 'Basic ' . base64_encode( 'username:password' );
$headers = array( 'Authorization' => $auth_header );
```

### WooCommerce API Keys
For admin endpoints (like sessions), CoCart requires WooCommerce API keys:

```php
// Create API key
$key_data = $this->create_wc_api_key( array(
    'description' => 'Admin API Key',
    'permissions' => 'read_write',
) );

// Get auth headers
$headers = $this->authenticate_with_wc_api_key( $key_data );

// Make authenticated request
$response = $this->authenticated_admin_request( 'GET', 'sessions', array(), $key_data );
```

## API Versions

The tests support both CoCart API versions:

- **v1**: Legacy API endpoints
- **v2**: Current API endpoints (default)

Most test methods accept a `$version` parameter to specify which API version to test.

## Database

Tests use a separate test database to avoid affecting your development data. The database is created automatically when you run the installation script.

## Coverage Reports

Coverage reports are generated in `tests/coverage/` when running tests with coverage. Open `tests/coverage/index.html` in your browser to view the coverage report.

## Continuous Integration

The tests are designed to work with CI/CD pipelines. The `phpunit.xml.dist` file contains the configuration needed for automated testing.

## Troubleshooting

### Common Issues

1. **Database connection errors**: Ensure your database credentials are correct and the database server is running.

2. **WordPress not found**: Make sure the WordPress installation script completed successfully.

3. **WooCommerce not found**: Ensure WooCommerce is properly installed and activated.

4. **Permission errors**: Make sure the `bin/install-wp-tests.sh` script is executable.

5. **Authentication errors**: For admin endpoints, ensure you're using WooCommerce API keys with proper permissions.

### Debug Mode

To run tests in debug mode, set the `WP_DEBUG` environment variable:

```bash
WP_DEBUG=1 composer test
```

## Contributing

When adding new tests:

1. Follow the existing naming conventions
2. Use descriptive test method names
3. Test both success and failure scenarios
4. Include edge cases and error conditions
5. Update this README if adding new test utilities
6. For admin endpoints, use WooCommerce API key authentication

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)
- [CoCart API Documentation](https://docs.cocartapi.com/)
- [WooCommerce REST API Documentation](https://woocommerce.github.io/woocommerce-rest-api-docs/) 