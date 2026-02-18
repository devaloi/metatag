# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-02-18

### Added
- Per-post SEO meta box with title, description, focus keyword, canonical URL, noindex, and nofollow fields
- Character counters for SEO title (60 chars) and meta description (160 chars)
- Meta tag output in `wp_head`: description, canonical URL, robots directives
- Title tag customization via `document_title_parts` and `document_title_separator` filters
- Description fallback chain: custom value → excerpt → post content → global default
- Open Graph tags: `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`, `og:locale`
- Open Graph image fallback: featured image → first content image → site default
- Open Graph image dimensions (`og:image:width`, `og:image:height`)
- Twitter Card tags: `summary_large_image` with title, description, image
- Twitter Card values inherited from Open Graph for consistency
- JSON-LD structured data: Article schema for posts, WebPage schema for pages
- JSON-LD WebSite schema for the front page
- JSON-LD BreadcrumbList with Home → Category → Post hierarchy
- Admin settings page under Settings → MetaTag SEO
- Global settings: title separator, title format, default description
- Social profile settings: Twitter handle, Facebook page URL, default OG image
- Homepage SEO overrides: custom title and description
- Filter `metatag_meta_box_post_types` to extend meta box to custom post types
- Full i18n support with `metatag` text domain
- Proper plugin lifecycle: activation sets defaults, uninstall cleans up all data
- Unit tests for meta output, Open Graph, Twitter Cards, JSON-LD, settings, and meta box
- GitHub Actions CI: PHPCS lint + PHPUnit across PHP 8.0–8.3
