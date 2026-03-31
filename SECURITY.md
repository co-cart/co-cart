# Security Policy

Full details of the CoCart Security Policy can be found on [cocartapi.com/security-policy/](https://cocartapi.com/security-policy/).

## Supported Versions

The CoCart Headless Security Team believes in Responsible Disclosure by alerting the security team immediately and privately of any potential vulnerabilities. If a critical vulnerability is found in the current version of CoCart, we may opt to backport any patches to previous versions.

| Version | Supported |
|---------| --------- |
| 4.9.x   | Yes       |
| 4.8.x   | Yes       |
| 4.7.x   | Yes       |
| 4.6.x   | Yes       |
| 4.5.x   | Yes       |
| 4.4.x   | Yes       |
| 4.3.x   | Yes       |
| 4.2.x   | No        |
| 4.1.x   | No        |
| 4.0.x   | No        |
| < 4.0.0 | No        |

## Reporting a Vulnerability

CoCart Community is an open-source plugin for WordPress.

**For responsible disclosure of security issues, please submit your report based on instructions found on [cocartapi.com/security-policy/](https://cocartapi.com/security-policy/).**

Our most critical targets are:

* CoCart Community (this repository)
* cocartapi.com -- the primary marketplace and marketing site.

## Guidelines

We're committed to working with security researchers to resolve the vulnerabilities they discover. You can help us by following these guidelines:

*   Pen-testing Production:
    *   Please **setup a local environment** instead whenever possible. Most of our code is open source (see above).
    *   If that's not possible, **limit any data access/modification** to the bare minimum necessary to reproduce a PoC.
    *   **Don't automate form submissions!** That's very annoying for us, because it adds extra work for the volunteers who manage those systems, and reduces the signal/noise ratio in our communication channels.
*   Be Patient - Give us a reasonable time to correct the issue before you disclose the vulnerability.
