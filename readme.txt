=== Webworq Social Share Buttons ===
Contributors: webworq
Tags: social sharing, share buttons, open graph, twitter card, linkedin
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 4.0.4
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

== External services ==

This plugin generates share links that redirect the user's browser to the following third-party social platforms. No data is sent from your server — the share URL (containing the post title and permalink) is constructed as a standard link that the visitor clicks. The visitor's browser then navigates directly to the platform.

= LinkedIn =
Used to share a link on LinkedIn.
Data sent: post URL (via the visitor's browser upon click).
Service provided by LinkedIn Corporation.
Terms of Service: https://www.linkedin.com/legal/user-agreement
Privacy Policy: https://www.linkedin.com/legal/privacy-policy

= X (formerly Twitter) =
Used to share a link on X.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by X Corp.
Terms of Service: https://x.com/en/tos
Privacy Policy: https://x.com/en/privacy

= Bluesky =
Used to share a link on Bluesky.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Bluesky PBC.
Terms of Service: https://bsky.social/about/support/tos
Privacy Policy: https://bsky.social/about/support/privacy-policy

= Facebook =
Used to share a link on Facebook.
Data sent: post URL (via the visitor's browser upon click).
Service provided by Meta Platforms, Inc.
Terms of Service: https://www.facebook.com/terms.php
Privacy Policy: https://www.facebook.com/privacy/policy/

= WhatsApp =
Used to share a link via WhatsApp.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Meta Platforms, Inc.
Terms of Service: https://www.whatsapp.com/legal/terms-of-service
Privacy Policy: https://www.whatsapp.com/legal/privacy-policy

= Pinterest =
Used to pin a link on Pinterest.
Data sent: post URL, title, and featured image URL (via the visitor's browser upon click).
Service provided by Pinterest, Inc.
Terms of Service: https://policy.pinterest.com/en/terms-of-service
Privacy Policy: https://policy.pinterest.com/en/privacy-policy

= Reddit =
Used to submit a link on Reddit.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Reddit, Inc.
Terms of Service: https://www.redditinc.com/policies/user-agreement
Privacy Policy: https://www.reddit.com/policies/privacy-policy

= Telegram =
Used to share a link via Telegram.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Telegram FZ-LLC.
Terms of Service: https://telegram.org/tos
Privacy Policy: https://telegram.org/privacy

= Threads =
Used to share a link on Threads.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Meta Platforms, Inc.
Terms of Service: https://help.instagram.com/769983657850450
Privacy Policy: https://www.facebook.com/privacy/policy/

= Pocket =
Used to save a link to Pocket.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Mozilla Corporation.
Terms of Service: https://getpocket.com/en/tos/
Privacy Policy: https://getpocket.com/en/privacy/

= Tumblr =
Used to share a link on Tumblr.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Automattic Inc.
Terms of Service: https://www.tumblr.com/policy/en/terms-of-service
Privacy Policy: https://www.tumblr.com/privacy/en

= LINE =
Used to share a link via LINE.
Data sent: post URL (via the visitor's browser upon click).
Service provided by LY Corporation.
Terms of Service: https://terms.line.me/line_terms
Privacy Policy: https://terms.line.me/line_rules

= Viber =
Used to share a link via Viber.
Data sent: post URL and title (via the visitor's browser upon click).
Service provided by Viber Media S.à r.l.
Terms of Service: https://www.viber.com/en/terms/viber-terms-use/
Privacy Policy: https://www.viber.com/en/terms/viber-privacy-policy/

== Installation ==

1. Upload the `webworq-social-share` folder to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Go to Settings > Webworq Social Share to configure

== Changelog ==

= 4.0.4 =
* Added External Services section documenting all third-party platform URLs with terms/privacy links
* Escaped all remaining unescaped variables in admin and frontend output
* Applied webworq_ss_kses_icon() to all icon echo statements
* Fixed inline script timing — moved wp_add_inline_script() into enqueue_assets() so JS loads correctly
* Moved inline scripts to wp_add_inline_script() for Plugin Check compliance
* Wrapped all frontend output with proper kses sanitization
* Replaced all _e() with esc_html_e() for escaped translations
* Upgraded __() calls to esc_html__() where appropriate
* Removed legacy shortcode alias

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
