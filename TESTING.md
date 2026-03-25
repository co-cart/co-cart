# CoCart REST API Testing Setup

This document provides a complete guide to the WordPress unit testing setup for the CoCart REST API plugin.

## Overview

The testing setup includes:

- **PHPUnit** for test execution
- **WordPress test framework** for WordPress-specific testing
- **Custom test framework** for CoCart-specific functionality
- **Comprehensive test coverage** for cart and product operations
- **CI/CD ready** configuration

## Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Set up test environment:**
   ```bash
   bin/install-wp-tests.sh cocart_test root password localhost latest
   ```

3. **Run tests:**
   ```bash
   composer test
   ```

## Test Structure

```
tests/
├── bootstrap.php                    # Test bootstrap file
├── framework/                       # Test framework classes
│   ├── class-cocart-unit-test-case.php
│   ├── class-cocart-rest-test-case.php
│   └── class-cocart-api-test-case.php
├── unit/                           # Unit tests
│   ├── test-cart-controller.php
│   ├── test-products-controller.php
│   └── test-authentication.php
└── README.md                       # Detailed testing guide
```

## Test Framework Classes

### CoCart_Unit_Test_Case
Base test case that provides:
- WordPress test environment setup
- WooCommerce integration
- Test data creation helpers
- Common assertions

### CoCart_REST_Test_Case
Extends the unit test case with:
- REST API server setup
- HTTP request helpers
- REST-specific assertions
- Authentication helpers

### CoCart_API_Test_Case
Extends the REST test case with:
- CoCart API-specific methods
- Cart operation helpers
- Product operation helpers
- API version support (v1/v2)

## Available Test Methods

### Cart Operations
- `add_item_to_cart( $product_id, $quantity, $version, $params )`
- `remove_item_from_cart( $item_key, $version, $params )`
- `update_item_in_cart( $item_key, $quantity, $version, $params )`
- `clear_cart( $version, $params )`
- `get_cart( $version, $params )`
- `get_cart_totals( $version, $params )`
- `get_cart_count( $version, $params )`

### Test Data Creation
- `create_product( $args )` - Create test products
- `create_customer( $args )` - Create test customers
- `create_order( $args )` - Create test orders

### Assertions
- `assert_rest_response_status( $expected, $response )`
- `assert_rest_response_content_type( $expected, $response )`
- `assert_rest_response_contains( $expected, $response )`
- `assert_rest_response_error( $expected, $response )`
- `assert_cart_is_empty( $version )`
- `assert_cart_has_items( $expected, $version )`
- `assert_cart_contains_product( $product_id, $version )`

## Configuration Files

### phpunit.xml.dist
PHPUnit configuration with:
- Test suite definitions
- Coverage reporting
- Environment variables
- File exclusions

### composer.json
Updated with:
- PHPUnit and testing dependencies
- Test scripts
- Autoloading for test classes

### bin/install-wp-tests.sh
WordPress test environment installer that:
- Downloads WordPress core
- Sets up test database
- Configures test environment
- Installs WordPress test framework

## Running Tests

### Basic Commands
```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
./vendor/bin/phpunit tests/unit/test-cart-controller.php

# Run specific test method
./vendor/bin/phpunit --filter test_add_item_to_cart
```

### Environment Variables
```bash
# Debug mode
WP_DEBUG=1 composer test

# Custom WordPress version
WP_VERSION=6.4 composer test

# Custom database
DB_HOST=127.0.0.1 composer test
```

## Test Examples

### Cart Test Example
```php
public function test_add_item_to_cart() {
    $product = $this->create_product();
    
    $response = $this->add_item_to_cart( $product->get_id(), 2 );
    
    $this->assert_rest_response_status( 200, $response );
    $this->assert_cart_contains_product( $product->get_id() );
}
```

### Product Test Example
```php
public function test_get_single_product() {
    $product = $this->create_product( array(
        'name'          => 'Test Product',
        'regular_price' => '25.00',
    ) );

    $response = $this->cocart_v2_request( 'GET', 'products/' . $product->get_id() );

    $this->assert_rest_response_status( 200, $response );
    $this->assertEquals( 'Test Product', $response->get_data()['name'] );
}
```

## Coverage Reports

Coverage reports are generated in `tests/coverage/` and include:
- HTML coverage report
- Text coverage summary
- Line-by-line coverage details

## Continuous Integration

The testing setup is designed for CI/CD with:
- Automated test execution
- Coverage reporting
- Exit codes for build systems
- Environment variable support

## Troubleshooting

### Common Issues

1. **Database Connection**
   - Verify database credentials
   - Ensure MySQL/MariaDB is running
   - Check database permissions

2. **WordPress Installation**
   - Run the install script with correct parameters
   - Check file permissions
   - Verify PHP extensions

3. **WooCommerce Integration**
   - Ensure WooCommerce is properly installed
   - Check WooCommerce version compatibility
   - Verify WooCommerce activation

4. **Test Failures**
   - Check test database isolation
   - Verify test data cleanup
   - Review error messages

### Debug Commands
```bash
# Verbose output
./vendor/bin/phpunit --verbose

# Stop on first failure
./vendor/bin/phpunit --stop-on-failure

# Generate coverage with debug info
./vendor/bin/phpunit --coverage-html tests/coverage --debug
```

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Data Cleanup**: Always clean up test data
3. **Descriptive Names**: Use clear test method names
4. **Edge Cases**: Test both success and failure scenarios
5. **API Versions**: Test both v1 and v2 APIs
6. **Coverage**: Aim for high test coverage

## Contributing

When adding new tests:
1. Follow existing naming conventions
2. Use the provided test framework classes
3. Include both positive and negative test cases
4. Update documentation as needed
5. Ensure tests pass in CI environment

## Resources

- [PHPUnit Documentation](https://phpunit.de/)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)
- [CoCart API Documentation](https://docs.cocartapi.com/)
- [WooCommerce Testing Guide](https://github.com/woocommerce/woocommerce/wiki/Testing) 