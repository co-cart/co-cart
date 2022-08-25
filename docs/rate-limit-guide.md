# Rate Limiting Guide <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [Limit information](#limit-information)
- [Methods restricted by Rate Limiting](#methods-restricted-by-rate-limiting)
- [Rate Limiting options filter](#rate-limiting-options-filter)
- [Proxy standard support](#proxy-standard-support)
- [Limit usage information observability](#limit-usage-information-observability)
    - [Response headers example](#response-headers-example)
- [Tracking limit abuses](#tracking-limit-abuses)
    - [Custom tracking usage example](#custom-tracking-usage-example)

Rate Limiting can now be enabled for CoCart.

This was designed to prevent abuse of endpoints from excessive calls and performance degradation on the machine running the store.

It is unauthenticated, rate limits are keyed by either `USER ID` (logged in) or `IP ADDRESS` (guest user), and standard support for running behind a proxy, load balancer, etc. for unauthenticated users can be enabled.

## Limit information

A default maximum of 25 requests can be made within a 10-second time frame. These can be changed through an [options filter](#rate-limiting-options-filter).

## Methods restricted by Rate Limiting

`POST`, `PUT`, `PATCH`, and `DELETE`

## Rate Limiting options filter

A filter is available for setting options for rate limiting:

```php
add_filter( 'cocart_api_rate_limit_options', function() {
	return [
		'enabled' => RateLimits::ENABLED, // enables/disables Rate Limiting. Default: false
		'proxy_support' => RateLimits::PROXY_SUPPORT, // enables/disables Proxy support. Default: false
		'limit' => RateLimits::LIMIT, // limit of request per timeframe. Default: 25
		'seconds' => RateLimits::SECONDS, // timeframe in seconds. Default: 10
	];
} );
```

## Proxy standard support

If the Store is running behind a proxy, load balancer, cache service, CDNs, etc. keying limits by IP is supported through standard IP forwarding headers, namely:

- `X_REAL_IP`|`CLIENT_IP` *Custom popular implementations that simplify obtaining the origin IP for the request*
- `X_FORWARDED_FOR` *De-facto standard header for identifying the originating IP, [Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For)*
- `X_FORWARDED` *[Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded), [RFC 7239](https://datatracker.ietf.org/doc/html/rfc7239)*

This is disabled by default.

## Limit usage information observability

Current limit information can be observed via custom response headers:

- `RateLimit-Limit` *Maximum requests per time frame.*
- `RateLimit-Remaining` *Requests available during current time frame.*
- `RateLimit-Reset` *Unix timestamp of next time frame reset.*
- `RateLimit-Retry-After` *Seconds until requests are unblocked again. Only shown when the limit is reached.*

### Response headers example

```http
RateLimit-Limit: 5
RateLimit-Remaining: 0
RateLimit-Reset: 1654880642
RateLimit-Retry-After: 28
```

## Tracking limit abuses

This uses a modified wc_rate_limit table with an additional remaining column for tracking the request count in any given request window.
A custom action `cocart_api_rate_limit_exceeded` was implemented for extendability in tracking such abuses.

### Custom tracking usage example

```php
add_action(
    'cocart_api_rate_limit_exceeded',
    function ( $offending_ip ) { /* Custom tracking implementation */ }
);
```

🐞 Found a mistake, or have a suggestion? [Leave feedback about this document here.](https://github.com/co-cart/co-cart/issues/new?assignees=&labels=type%3A+documentation&template=doc_feedback.md&title=Feedback+on+./docs/rate-limit-guide.md)

<!-- /FEEDBACK -->
