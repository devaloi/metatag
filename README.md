# MetaTag

[![CI](https://github.com/devaloi/metatag/actions/workflows/ci.yml/badge.svg)](https://github.com/devaloi/metatag/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-8892BF.svg)](https://www.php.net/)
[![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-21759B.svg)](https://wordpress.org/)

A lightweight WordPress plugin that adds customizable SEO meta tags, Open Graph, Twitter Cards, and JSON-LD structured data to any post or page.

## Why MetaTag?

Most SEO plugins are bloated with upsells, slow admin panels, and features nobody asked for. MetaTag does one thing well: it puts the right meta tags in your `<head>`. No bloat, no framework dependencies, no premium tier — just clean WordPress plugin architecture.

## Features

- **Per-Post SEO Controls** — Custom title (60-char counter), meta description (160-char counter), focus keyword, canonical URL, noindex/nofollow toggles
- **Open Graph Tags** — `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name` with automatic fallbacks
- **Twitter Cards** — `summary_large_image` cards that inherit from Open Graph values
- **JSON-LD Structured Data** — Article, WebPage, WebSite, and BreadcrumbList schemas
- **Global Settings** — Title separator, default description, social profiles, homepage overrides
- **Developer Friendly** — Filterable post types, clean hooks, zero external dependencies

## Architecture

```
metatag/
├── metatag.php                  # Plugin entry point, constants, bootstrap
├── includes/
│   ├── class-metatag.php        # Singleton orchestrator — loads deps, registers hooks
│   ├── class-helpers.php        # Shared utilities — title/description/image resolution
│   ├── class-meta-box.php       # Per-post meta box UI and save logic
│   ├── class-meta-output.php    # <meta> description, canonical, robots in wp_head
│   ├── class-open-graph.php     # Open Graph property tags
│   ├── class-twitter-card.php   # Twitter Card name tags (inherits from OG data)
│   ├── class-json-ld.php        # JSON-LD structured data (Article, WebPage, Breadcrumbs)
│   └── class-admin.php          # Settings page via WordPress Settings API
├── assets/
│   ├── css/admin.css            # Meta box styles
│   └── js/admin.js              # Character counters for title/description
├── tests/                       # PHPUnit with WordPress test suite
├── uninstall.php                # Clean removal of all plugin data
├── languages/                   # i18n support
└── readme.txt                   # WordPress.org plugin readme
```

**Key design decisions:**
- **Singleton pattern** for the main class prevents double-initialization
- **Shared helpers** eliminate duplication across OG, Twitter, and meta output classes
- **Fallback chains** ensure every page has a description and image, even without manual input
- **WordPress APIs only** — no raw SQL, no direct DB queries (except uninstall cleanup)

## Requirements

- WordPress 6.0+
- PHP 8.0+

## Installation

```bash
git clone https://github.com/devaloi/metatag.git
```

Copy the `metatag` folder to `wp-content/plugins/`, then activate in **Plugins → Installed Plugins**.

Configure global settings at **Settings → MetaTag SEO**.

## Usage

### Per-Post SEO

Edit any post or page and scroll to the **MetaTag — SEO Settings** meta box:

| Field | Purpose |
|-------|---------|
| SEO Title | Custom `<title>` tag (60 chars recommended) |
| Meta Description | Custom meta description (160 chars recommended) |
| Focus Keyword | Target keyword for the page |
| Canonical URL | Override the default canonical URL |
| noindex | Prevent search engines from indexing |
| nofollow | Prevent search engines from following links |

### Global Settings

Navigate to **Settings → MetaTag SEO**:

| Setting | Description |
|---------|-------------|
| Title Separator | Character between title and site name (`\|`, `-`, `–`, `·`) |
| Title Format | Template using `%title%` and `%sitename%` tokens |
| Default Description | Fallback when no per-post description is set |
| Twitter Handle | Site-wide Twitter `@username` for cards |
| Facebook Page URL | Facebook page URL for social profiles |
| Default OG Image | Fallback image when a post has no featured image |
| Homepage Title | Override the homepage `<title>` tag |
| Homepage Description | Override the homepage meta description |

### Description Fallback Chain

MetaTag resolves descriptions in this order:

1. Custom meta description (per-post)
2. Post excerpt
3. Auto-generated from post content (first 30 words)
4. Global default description (from settings)

### Image Fallback Chain

For Open Graph and Twitter Cards:

1. Featured image (with dimensions)
2. First `<img>` in post content
3. Default OG image (from settings)

## Hooks & Filters

### `metatag_meta_box_post_types`

Control which post types show the SEO meta box (default: `post`, `page`):

```php
add_filter( 'metatag_meta_box_post_types', function ( $post_types ) {
    $post_types[] = 'product';
    return $post_types;
} );
```

## Running Tests

```bash
# Set up the WordPress test suite (one-time)
bash <(curl -s https://raw.githubusercontent.com/wp-cli/scaffold-command/main/templates/install-wp-tests.sh) \
  wordpress_test root '' localhost latest

# Run tests
make test

# Run linter
make lint
```

## Tech Stack

| Component | Choice |
|-----------|--------|
| Platform | WordPress 6.0+ |
| Language | PHP 8.0+ |
| Testing | PHPUnit with WordPress test suite |
| Linting | PHPCS with WordPress coding standards |
| CI | GitHub Actions (PHP 8.0–8.3 matrix) |
| Admin UI | WordPress Settings API + meta boxes |
| Build | None — vanilla PHP, no transpilation |

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup, coding standards, and PR guidelines.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed list of changes.

## License

[MIT](LICENSE)
