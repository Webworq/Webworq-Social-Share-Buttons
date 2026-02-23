=== Ripple — Smart Social Share Buttons ===
Contributors: webworq
Tags: social sharing, share buttons, open graph, twitter card, linkedin
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 3.1.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smart social share buttons with Open Graph & Twitter Card metadata for rich link previews. Built by Webworq.

== Description ==

Ripple adds beautiful, customizable social sharing buttons to your WordPress posts — plus the Open Graph and Twitter Card metadata that makes shared links look great.

**Features:**

* 13 platforms: LinkedIn, X/Twitter, Bluesky, Facebook, WhatsApp, Email, Pinterest, Reddit, Telegram, Threads, Tumblr, LINE, and Copy Link
* Drag-and-drop platform ordering
* Multiple display modes: inline buttons or collapsible dropdown
* Floating share button (FAB) with 6 screen positions — works independently alongside in-content buttons
* FAB size options (small/medium/large) and mobile toggle
* 3 button shapes: circle, rounded, square
* Custom border radius slider (0–50px)
* Adjustable button spacing (4–24px)
* 8 color presets: Brand Colors, Mono Dark, Mono Light, Outline/Ghost, Minimal, Glass, Gradient, and Custom
* Per-variant custom colors: set different colors for inline, collapsible, and floating buttons
* 4 shadow presets: none, subtle, medium, bold
* 5 hover animations: lift, grow, glow, fade, shine
* Optional text labels
* Auto-placement or manual shortcode
* Open Graph & Twitter Card meta tags (auto-detects SEO plugins)
* Divi theme compatible
* Fully extensible via WordPress filters
* Automatic settings migration from v2 to v3

**Shortcode:**

Use `[ripple_share]` in any post, page, widget, or Divi Code Module. The legacy `[ripple_share]` shortcode still works for backward compatibility.

**Extending:**

Add custom platforms with the `ripple_platforms` filter. See class-platforms.php for examples.

== Installation ==

1. Upload the `ripple-social-share` folder to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Go to Settings > Ripple to configure

== Changelog ==

= 3.1.1 =
* Fixed all WordPress Plugin Check (PCP) errors for directory compliance
* Improved output escaping throughout admin interface (esc_html_e, esc_attr, wp_kses_post)
* Added translators comments for all placeholder strings
* Fixed auto-placement for Divi Theme Builder specialty column layouts
* Updated tested-up-to to WordPress 6.9

= 3.1.0 =
* Switched to official Automattic social-logos icon set (https://github.com/Automattic/social-logos)
* Removed Pocket and Viber platforms (now 13 platforms total)
* Fixed auto-placement for Divi Theme Builder layouts
* Admin preview now renders live buttons in the Styling tab

= 3.0.0 =
* Added 6 new platforms: Pinterest, Reddit, Telegram, Threads, Tumblr, LINE (total: 13)
* 8 color presets: Brand, Mono Dark, Mono Light, Outline, Minimal, Glass, Gradient, Custom
* Per-variant custom colors for inline, collapsible, and floating buttons
* 5 hover animations: lift, grow, glow, fade, shine
* 4 shadow presets: none, subtle, medium, bold
* Adjustable button spacing (4–24px range slider)
* Custom border radius (0–50px) alongside shape presets
* FAB size options: small (44px), medium (56px), large (68px)
* FAB mobile visibility toggle
* Full CSS custom properties architecture for dynamic theming
* Automatic settings migration from v2 to v3

= 2.0.0 =
* Rebranded to Ripple — Smart Social Share Buttons
* Webworq logo branding in admin settings page
* Floating FAB now expands horizontally (icons fan out sideways)
* Floating FAB is fully independent with own tab, position picker, and post types
* Visual position picker with 6 screen positions
* Display mode preview cards in styling tab

= 1.0.0 =
* Initial release
