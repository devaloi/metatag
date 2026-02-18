# MetaTag

A lightweight WordPress plugin that adds customizable SEO meta tags, Open Graph, Twitter Cards, and JSON-LD structured data to any post or page.

## Features

- **Per-Post SEO Controls** — Custom title (with 60-char counter), meta description (160-char counter), focus keyword, canonical URL, noindex/nofollow toggles
- **Open Graph Tags** — `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name` with automatic fallbacks
- **Twitter Cards** — `summary_large_image` cards that inherit from OG values
- **JSON-LD Structured Data** — Article, WebPage, and BreadcrumbList schemas
- **Global Settings** — Title separator, default description, social profiles, homepage overrides

## Requirements

- WordPress 6.0+
- PHP 8.0+

## Installation

1. Download or clone this repository:
   ```bash
   git clone https://github.com/devaloi/metatag.git
   ```
2. Copy the `metatag` folder to `wp-content/plugins/`.
3. Activate the plugin in the WordPress admin under **Plugins**.
4. Configure global settings at **Settings → MetaTag SEO**.

## Usage

### Per-Post SEO

Edit any post or page and scroll to the **MetaTag — SEO Settings** meta box:

- **SEO Title** — Custom title tag (60 characters recommended)
- **Meta Description** — Custom meta description (160 characters recommended)
- **Focus Keyword** — Target keyword for the page
- **Canonical URL** — Override the default canonical URL
- **noindex / nofollow** — Control search engine indexing and link following

### Global Settings

Navigate to **Settings → MetaTag SEO** to configure:

| Setting | Description |
|---------|-------------|
| Title Separator | Character between title and site name (`\|`, `-`, `–`, `·`, etc.) |
| Title Format | Template using `%title%` and `%sitename%` tokens |
| Default Description | Fallback when no per-post description is set |
| Twitter Handle | Your site's Twitter `@username` |
| Facebook Page URL | Your Facebook page URL |
| Default OG Image | Fallback image for Open Graph |
| Homepage Title | Override the homepage `<title>` tag |
| Homepage Description | Override the homepage meta description |

## Hooks & Filters

### `metatag_meta_box_post_types`

Filter the post types that display the SEO meta box.

```php
add_filter( 'metatag_meta_box_post_types', function ( $post_types ) {
    $post_types[] = 'product';
    return $post_types;
} );
```

## Running Tests

Tests use the WordPress PHPUnit test suite:

```bash
# Set up the WordPress test environment first
# See: https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/
make test
```

## Linting

```bash
# Requires PHPCS with WordPress coding standards
make lint
```

## Tech Stack

| Component | Choice |
|-----------|--------|
| Platform | WordPress 6.0+ |
| Language | PHP 8.0+ |
| Testing | PHPUnit with WordPress test suite |
| Linting | PHPCS with WordPress standards |
| Admin UI | WordPress Settings API + meta boxes |

## License

[MIT](LICENSE)
