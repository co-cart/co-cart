# Security Policy

Full details of the CoCart Security Policy can be found on [cocartapi.com/security-policy/](https://cocartapi.com/security-policy/).

## Supported Versions

The CoCart Headless Security Team believes in Responsible Disclosure by alerting the security team immediately and privately of any potential vulnerabilities. If a critical vulnerability is found in the current version of CoCart, we may opt to backport any patches to previous versions.

| Version | Supported |
|---------| --------- |
| 4.9.x   | Yes       |
| 4.8.x   | Yes       |
| < 4.8.x | No        |

## Reporting a Vulnerability

CoCart Community is an open-source plugin for WordPress.

**For responsible disclosure of security issues, please submit your report based on instructions found on [cocartapi.com/security-policy/](https://cocartapi.com/security-policy/).**

## Scope

**In scope:**

*   CoCart Community (this repository)
*   cocartapi.com — the primary marketplace and marketing site.

**Out of scope:**

*   The CoCart community Discord server
*   Third-party plugins or themes that extend CoCart
*   The translation platform (translate.cocartapi.com)
*   Issues that only affect end-of-life PHP versions (currently PHP 8.1 and below)

## Response Timeline

*   **Acknowledgement:** We aim to acknowledge your report within 48 hours.
*   **Fix:** We aim to release a fix within 30 days. Critical vulnerabilities may be patched sooner; lower-severity issues may take longer depending on complexity.

## Guidelines

We're committed to working with security researchers to resolve the vulnerabilities they discover. You can help us by following these guidelines:

*   Pen-testing Production:
    *   Please **setup a local environment** instead whenever possible. Most of our code is open source (see above).
    *   If that's not possible, **limit any data access/modification** to the bare minimum necessary to reproduce a PoC.
    *   **Don't automate form submissions!** That's very annoying for us, because it adds extra work for the volunteers who manage those systems, and reduces the signal/noise ratio in our communication channels.
*   Be Patient - Give us a reasonable time to correct the issue before you disclose the vulnerability.
