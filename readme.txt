=== MetaTag ===
Contributors: devaloi
Tags: seo, meta tags, open graph, twitter cards, json-ld, schema, structured data
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

A lightweight WordPress plugin that adds customizable SEO meta tags, Open Graph, Twitter Cards, and JSON-LD structured data to any post or page.

== Description ==

MetaTag is a lightweight, no-bloat SEO plugin for WordPress. It adds customizable meta tags, Open Graph markup, Twitter Cards, and JSON-LD structured data to your posts and pages.

**Features:**

* Per-post/page SEO title and meta description with character counters
* Focus keyword field
* Canonical URL override
* noindex/nofollow toggles
* Open Graph tags (title, description, image, URL, type)
* Twitter Card tags (summary_large_image)
* JSON-LD structured data (Article, WebPage, BreadcrumbList)
* Global settings for title format, separator, social profiles
* Homepage meta overrides
* Fully translatable (i18n ready)

== Installation ==

1. Upload the `metatag` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure global settings under Settings → MetaTag SEO.
4. Edit any post or page to set per-post SEO fields.

== Frequently Asked Questions ==

= Does this plugin work with custom post types? =

Yes. By default, the meta box appears on posts and pages. You can add custom post types using the `metatag_meta_box_post_types` filter:

`add_filter( 'metatag_meta_box_post_types', function( $types ) {
    $types[] = 'product';
    return $types;
} );`

= What happens if I don't set a meta description? =

MetaTag uses a fallback chain: custom description → post excerpt → auto-generated from post content → global default description.

= Does this conflict with other SEO plugins? =

You should only run one SEO plugin at a time to avoid duplicate meta tags.

== Changelog ==

= 1.0.0 =
* Initial release
* Per-post SEO meta box with title, description, keyword, canonical URL, noindex, nofollow
* Open Graph tag output
* Twitter Card tag output
* JSON-LD structured data (Article, WebPage, BreadcrumbList)
* Admin settings page with title format, separator, social profiles, homepage overrides
