# Contributing to CoCart Community ✨

CoCart Community helps power many headless stores across the internet, and your contributions are greatly appreciated.

This is a **public repository** — open-source under the GPLv3+ license. All contributions to the project will be released under the same license. You maintain copyright over any contribution you make. If you have questions before contributing, reach out in the [CoCart community Discord](https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore).

## Ways to Contribute

- **Report a bug** — use the [bug report template](https://github.com/co-cart/co-cart/issues/new?template=bug_report.yml).
- **Suggest an enhancement** — use the [enhancement template](https://github.com/co-cart/co-cart/issues/new?template=enhancement.yml) for straightforward ideas, or discuss complex proposals in the [Discord community](https://cocartapi.com/community/) first.
- **Test open issues or pull requests** — look for issues tagged [`status: awaiting triage`](https://github.com/co-cart/co-cart/issues?q=is%3Aissue+is%3Aopen+label%3A%22status%3A+awaiting+triage%22) and share your findings in a comment.
- **Submit a fix or improvement** — see [Submitting a Pull Request](#submitting-a-pull-request) below.
- **Translate strings** — see [Translating CoCart](#translating-cocart) below.
- **Report a security vulnerability** — see [Security Disclosures](#security-disclosures) below. Do not open a public issue.

## What Happens After You Submit

All new issues are automatically tagged `status: awaiting triage`. Here is what the label lifecycle looks like:

1. **`status: awaiting triage`** — your issue was received; a maintainer will review it soon.
2. Maintainer may add one of:
   - `needs: template` — your issue was submitted without completing the template. A maintainer will post the required fields and ask you to fill them in.
   - `needs: author feedback` — a maintainer needs more information from you. Please respond to keep the issue open.
   - `type: support request` — this is a support question; the issue will be closed automatically with a redirect to Discord.
3. When you reply to a `needs: author feedback` issue, it transitions automatically to `needs: triage feedback` and a maintainer will re-review.
4. Maintainer may then add `status: developer reproduction`, `status: needs reproduction`, or `needs: votes` depending on the outcome.
5. Issues with `needs: author feedback` that receive no activity for 14 days are marked `status: stale` and closed after a further 14 days.

### Label Reference

| Label | What it means for you |
|---|---|
| `status: awaiting triage` | Received; a maintainer will review soon |
| `needs: author feedback` | A maintainer needs more info — please respond to keep the issue open |
| `needs: template` | Your issue was submitted without the required template details — a maintainer will post what's needed |
| `needs: triage feedback` | Your reply was received; a maintainer will re-review |
| `needs: developer feedback` | Under review by a CoCart developer |
| `needs: votes` | Feature is parked; community interest determines whether it is prioritised |
| `status: needs reproduction` | A developer is attempting to reproduce your bug |
| `status: developer reproduction` | Bug confirmed internally; being investigated |
| `status: in progress` | Someone is actively working on this |
| `status: stale` | No activity for 14 days; auto-closes in 14 more days without a reply |
| `type: support request` | Redirected to Discord — GitHub is for confirmed bugs and enhancement requests only |

## Local Development Setup

### Requirements

- **Node.js** 20.0.0 or higher — use [nvm](https://github.com/nvm-sh/nvm) to manage versions
- **Composer** 2.x
- **PHP** 8.2 or higher

### Getting started

```bash
# Install Node dependencies
npm ci

# Install PHP dependencies
composer install

# Compile CSS and JS to verify your setup
npx grunt css js
```

If `npx grunt css js` completes without errors, your environment is ready.

### Useful commands

```bash
npx grunt watch        # Watch SCSS/JS for changes and recompile automatically
composer phpcs         # Check PHP coding standards
composer phpcbf        # Auto-fix coding standards issues
composer phpstan       # Static analysis
```

## Submitting a Pull Request

1. [Fork](https://help.github.com/articles/fork-a-repo/) this repository.
2. Create a branch from `trunk` and make your changes.
3. Run `composer phpcs` and fix any violations before committing.
4. When committing, reference the related issue number (e.g. `Fix #123`). Write good, descriptive commit messages — see [this post](https://chris.beams.io/posts/git-commit/) for guidance.
5. Push to your fork and [open a pull request](https://help.github.com/articles/using-pull-requests/) against `trunk`.
6. Fill out all applicable sections of the pull request template.

**Labels on pull requests** are set by maintainers — you do not need to add them yourself:

| Label | What it means |
|---|---|
| `release: cherry-pick` | This change also needs to land in the current release branch |
| `release: add changelog` | The changelog entry has not been written yet |

Please do not modify the changelog directly or update `.pot` files — these are handled by the CoCart team.

## Security Disclosures

**Please do not open a public GitHub issue to report a security vulnerability.**

Follow the responsible disclosure process described at [cocartapi.com/security-policy/](https://cocartapi.com/security-policy/). Give us reasonable time to address the issue before disclosing it publicly.

## Translating CoCart

Translations are managed via the [CoCart Community project on translate.cocartapi.com](https://translate.cocartapi.com/projects/cart-rest-api-for-woocommerce/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore). Join and help translate there — even if CoCart Community is already 100% translated for your language, new strings are added regularly.

### String Localisation Guidelines

1. Use `cart-rest-api-for-woocommerce` as the textdomain in all strings.
2. When using dynamic strings in `printf`/`sprintf` with more than one replacement, use numbered arguments — e.g. `Test %1$s string %2$s.`
3. Use sentence case — e.g. `Some thing` not `Some Thing`.
4. Avoid HTML in strings. If HTML is needed, insert it via `sprintf`.

For more detail, see [i18n for WordPress Developers](https://codex.wordpress.org/I18n_for_WordPress_Developers).

## FAQ

**`npm ci` fails with a lock file error.**
This happens when `package.json` has been updated but `package-lock.json` hasn't been regenerated. Run `npm install` once to sync the lock file, then use `npm ci` on subsequent installs.

**Which template should I use — Enhancement or Feature Request?**
Use **Enhancement** when you want to improve or extend something that already exists (a current endpoint, hook, filter, or behaviour). Use **Feature Request** when you are proposing something entirely new that CoCart does not currently support.

**My issue was closed with `needs: votes` — is it rejected permanently?**
No. Closing with `needs: votes` means the idea doesn't fit current priorities, not that it will never happen. We monitor these issues over time; community upvotes and comments signal demand and can bring an issue back into consideration.

**Do I need an issue before opening a pull request?**
For bug fixes, no — a PR with a clear description is fine. For new features or non-trivial changes, open an issue or discuss in [Discord](https://cocartapi.com/community/) first. This avoids investing time in a PR that may not align with the project's direction.
