=== Webworq Social Share Buttons ===
Contributors: webworq
Tags: social sharing, share buttons, open graph, twitter card, linkedin
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 4.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smart social share buttons with Open Graph & Twitter Card metadata for rich link previews. Built by Webworq.

== Description ==

Webworq Social Share adds beautiful, customizable social sharing buttons to your WordPress posts — plus the Open Graph and Twitter Card metadata that makes shared links look great.

**Features:**

* 13 platforms: LinkedIn, X/Twitter, Bluesky, Facebook, WhatsApp, Email, Pinterest, Reddit, Telegram, Threads, Tumblr, LINE, and Copy Link
* Drag-and-drop platform ordering
* Multiple display modes: inline buttons or collapsible dropdown
* Floating share button (FAB) with 6 screen positions — works independently alongside in-content buttons
* FAB size options (small/medium/large) and mobile toggle
* 3 button shapes: circle, rounded, square
* Custom border radius slider (0-50px)
* Adjustable button spacing (4-24px)
* 8 color presets: Brand Colors, Mono Dark, Mono Light, Outline/Ghost, Minimal, Glass, Gradient, and Custom
* Per-variant custom colors: set different colors for inline, collapsible, and floating buttons
* 4 shadow presets: none, subtle, medium, bold
* 5 hover animations: lift, grow, glow, fade, shine
* Optional text labels
* Auto-placement or manual shortcode
* Open Graph & Twitter Card meta tags (auto-detects SEO plugins)
* Compatible with Divi Theme Builder, Elementor, Bricks, Beaver Builder, and more
* Fully extensible via WordPress filters

**Shortcode:**

Use `[webworq_share]` in any post, page, widget, or Divi Code Module.

**Extending:**

Add custom platforms with the `webworq_ss_platforms` filter. See class-platforms.php for examples.

== Installation ==

1. Upload the `webworq-social-share` folder to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Go to Settings > Webworq Social Share to configure

== Changelog ==

= 4.0.0 =
* Rebranded from Ripple to Webworq Social Share Buttons
* New slug: webworq-social-share
* Updated all CSS class prefixes, constants, and function names
* Automatic settings migration from previous versions
* SVG icon sanitization with proper viewBox preservation
* Fixed all WordPress Plugin Check (PCP) errors
* Compatible with Divi Theme Builder specialty column layouts

= 3.1.2 =
* Fixed SVG icon rendering — wp_kses was stripping case-sensitive viewBox attribute
* Added SVG-safe sanitization helpers

= 3.1.1 =
* Fixed all WordPress Plugin Check (PCP) errors for directory compliance
* Improved output escaping throughout admin interface
* Added translators comments for all placeholder strings
* Fixed auto-placement for Divi Theme Builder specialty column layouts
* Updated tested-up-to to WordPress 6.9

= 3.1.0 =
* Switched to official Automattic social-logos icon set
* Removed Pocket and Viber platforms (now 13 platforms total)
* Fixed auto-placement for Divi Theme Builder layouts
* Admin preview now renders live buttons in the Styling tab

= 3.0.0 =
* Added 6 new platforms: Pinterest, Reddit, Telegram, Threads, Tumblr, LINE (total: 13)
* 8 color presets: Brand, Mono Dark, Mono Light, Outline, Minimal, Glass, Gradient, Custom
* Per-variant custom colors for inline, collapsible, and floating buttons
* 5 hover animations: lift, grow, glow, fade, shine
* 4 shadow presets: none, subtle, medium, bold
* Adjustable button spacing (4-24px range slider)
* Custom border radius (0-50px) alongside shape presets
* FAB size options: small (44px), medium (56px), large (68px)
* FAB mobile visibility toggle
* Full CSS custom properties architecture for dynamic theming
* Automatic settings migration from v2 to v3

= 1.0.0 =
* Initial release
