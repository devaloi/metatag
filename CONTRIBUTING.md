# Contributing to MetaTag

Thank you for considering contributing to MetaTag! This document explains how to set up a development environment and submit changes.

## Development Setup

### Prerequisites

- PHP 8.0+
- WordPress 6.0+ (local install for testing)
- [Composer](https://getcomposer.org/) (for dev dependencies)
- MySQL or MariaDB (for the WP test suite)

### Getting Started

1. Clone the repository:
   ```bash
   git clone https://github.com/devaloi/metatag.git
   cd metatag
   ```

2. Install dev dependencies:
   ```bash
   make install
   ```

3. Set up the WordPress test suite:
   ```bash
   bash <(curl -s https://raw.githubusercontent.com/wp-cli/scaffold-command/main/templates/install-wp-tests.sh) \
     wordpress_test root '' localhost latest
   ```

4. Run tests:
   ```bash
   make test
   ```

5. Run the linter:
   ```bash
   make lint
   ```

## Coding Standards

This project follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/):

- **PHP:** WordPress-Core via PHPCS. Run `make lint` before committing.
- **JavaScript:** No framework. Vanilla JS following WordPress conventions.
- **CSS:** Minimal, scoped to `.metatag-` prefixed classes.

All text strings must be translatable using the `metatag` text domain.

## Submitting Changes

1. Create a feature branch from `main`:
   ```bash
   git checkout -b feat/your-feature
   ```

2. Make your changes with clear, focused commits:
   - Use conventional commit messages: `feat:`, `fix:`, `test:`, `refactor:`, `docs:`, `chore:`
   - One logical change per commit

3. Ensure all checks pass:
   ```bash
   make lint && make test
   ```

4. Push and open a pull request against `main`.

## What Makes a Good PR

- **Focused:** One feature or fix per PR.
- **Tested:** New behavior has tests. Existing tests still pass.
- **Lint clean:** PHPCS passes with no errors.
- **Documented:** Update README or inline docs if the public API changes.

## Reporting Issues

Open an issue on GitHub with:
- WordPress and PHP versions
- Steps to reproduce
- Expected vs. actual behavior

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
