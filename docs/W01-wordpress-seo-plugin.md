# W01: metatag — WordPress SEO Meta Plugin

**Catalog ID:** W01 | **Size:** S | **Language:** PHP / WordPress
**Repo name:** `metatag`
**One-liner:** A lightweight WordPress plugin that adds customizable SEO meta tags, Open Graph, Twitter Cards, and JSON-LD structured data to any post or page.

---

## Why This Stands Out

- **Shows WordPress plugin architecture** — hooks, filters, admin UI, meta boxes
- **Proper WordPress coding standards** — PHPCS, sanitization, nonces, escaping
- **Real SEO value** — Open Graph, Twitter Cards, JSON-LD schema.org markup
- **Admin UI without a framework** — vanilla WP Settings API and meta boxes
- **Lightweight** — no bloat, no upsells, does one thing well
- **Demonstrates** PHP OOP, WP plugin patterns, security best practices

---

## Architecture

```
metatag/
├── metatag.php                # Plugin entry point, registration
├── includes/
│   ├── class-metatag.php      # Main plugin class (singleton, hooks)
│   ├── class-meta-output.php  # Render meta tags in <head>
│   ├── class-admin.php        # Settings page
│   ├── class-meta-box.php     # Per-post meta box UI
│   ├── class-open-graph.php   # OG tag generation
│   ├── class-twitter-card.php # Twitter Card generation
│   └── class-json-ld.php      # JSON-LD structured data
├── assets/
│   ├── css/
│   │   └── admin.css          # Admin UI styles (minimal)
│   └── js/
│       └── admin.js           # Admin UI interactions (character counters)
├── tests/
│   ├── bootstrap.php
│   ├── test-meta-output.php
│   ├── test-open-graph.php
│   ├── test-twitter-card.php
│   └── test-json-ld.php
├── languages/
│   └── metatag.pot            # Translation template
├── readme.txt                 # WordPress.org-style readme
├── Makefile
├── phpcs.xml
├── .gitignore
├── LICENSE
└── README.md
```

---

## Features

### Per-Post/Page SEO
- Custom meta title (with character counter — 60 char target)
- Custom meta description (with character counter — 160 char target)
- Focus keyword
- Canonical URL override
- noindex/nofollow toggles

### Open Graph Tags
- `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`
- Auto-populate from post content, allow per-post overrides
- Image from featured image or first image in content

### Twitter Cards
- `twitter:card` (summary_large_image by default)
- `twitter:title`, `twitter:description`, `twitter:image`
- Site-wide Twitter handle setting

### JSON-LD Structured Data
- Article schema for posts
- WebPage schema for pages
- BreadcrumbList
- Organization/Person for the site

### Global Settings
- Default title format: `%title% | %sitename%`
- Default description (fallback)
- Social profiles (Twitter handle, Facebook page)
- Separator character preference
- Homepage meta overrides

---

## Phases

### Phase 1: Plugin Scaffold & Meta Box

**1.1 — Plugin setup**
- Plugin header, activation/deactivation hooks
- Main class with singleton pattern
- Autoloader or simple includes
- Register hooks in `init()`

**1.2 — Meta box**
- Register meta box for posts and pages
- Fields: SEO title, description, focus keyword, canonical URL, noindex, nofollow
- Save with nonce verification, sanitization, `update_post_meta()`
- Character counters in JS for title (60) and description (160)

### Phase 2: Meta Output

**2.1 — Meta tag rendering**
- Hook into `wp_head` with appropriate priority
- Output: `<meta name="description">`, `<link rel="canonical">`
- Fallback chain: custom value → excerpt → auto-generated from content
- Title filter via `document_title_parts` or `wp_title`
- Respect noindex/nofollow settings

**2.2 — Open Graph**
- Output OG tags in `wp_head`
- Image: featured image → first content image → site default
- Handle image dimensions for `og:image:width` / `og:image:height`

**2.3 — Twitter Cards**
- Output Twitter meta tags
- Inherit from OG values where appropriate

**2.4 — JSON-LD**
- Output `<script type="application/ld+json">`
- Article schema: headline, author, datePublished, dateModified, image
- WebPage schema for pages
- BreadcrumbList from post hierarchy

### Phase 3: Admin Settings Page

**3.1 — Settings page**
- Register under Settings menu
- Use WordPress Settings API (register_setting, add_settings_section, add_settings_field)
- Sections: General, Social Profiles, Homepage
- Sanitize all inputs

**3.2 — Settings fields**
- Title separator (|, -, –, ·, etc.)
- Default title format
- Twitter handle
- Facebook page URL
- Homepage title, description overrides
- Default OG image

### Phase 4: Tests

**4.1 — Unit tests with WP test suite**
- Meta output: correct tags generated for various post states
- Open Graph: image selection fallback chain, tag format
- Twitter Cards: inheritance from OG
- JSON-LD: valid JSON, correct schema types
- Settings: sanitization, defaults

**4.2 — Edge cases**
- Post with no featured image, no excerpt
- Custom post types
- Empty settings (all defaults work)
- Special characters in title/description (proper escaping)

### Phase 5: Documentation & Polish

**5.1 — README.md**
- Install, activate, configure
- Feature overview with screenshots descriptions
- Hook/filter reference for developers
- Contributing guidelines

**5.2 — WordPress.org readme.txt**
- Standard format: description, installation, FAQ, changelog
- Follows WordPress.org plugin guidelines

**5.3 — Final checks**
- PHPCS clean (WordPress coding standards)
- All text is translatable (i18n ready)
- No direct database queries (use WP APIs)
- No hardcoded URLs or paths
- Activate/deactivate/delete lifecycle clean

---

## Tech Stack

| Component | Choice |
|-----------|--------|
| Platform | WordPress 6.0+ |
| Language | PHP 8.0+ |
| Testing | PHPUnit with WP test suite |
| Linting | PHPCS with WordPress standards |
| Admin UI | WordPress Settings API + meta boxes |
| Build | None (vanilla PHP, no transpilation) |

---

## Commit Plan

1. `feat: scaffold plugin with main class and activation hooks`
2. `feat: add per-post meta box with SEO fields`
3. `feat: add meta tag output in wp_head`
4. `feat: add Open Graph tag generation`
5. `feat: add Twitter Card support`
6. `feat: add JSON-LD structured data`
7. `feat: add admin settings page`
8. `test: add unit tests for meta output and OG`
9. `refactor: sanitization, escaping, i18n`
10. `docs: add README and WordPress readme.txt`
11. `chore: PHPCS cleanup and final polish`
