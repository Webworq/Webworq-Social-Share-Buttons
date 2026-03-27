# Webworq Social Share Buttons

Smart social share buttons for WordPress with Open Graph & Twitter Card metadata for rich link previews. Built by [Webworq](https://webworq.dk).

## Features

- **13 platforms**: LinkedIn, X/Twitter, Bluesky, Facebook, WhatsApp, Email, Pinterest, Reddit, Telegram, Threads, Tumblr, LINE, and Copy Link
- **3 display modes**: Inline buttons, collapsible dropdown, or floating FAB
- **8 color presets**: Brand Colors, Mono Dark, Mono Light, Outline/Ghost, Minimal, Glass, Gradient, and Custom
- **5 hover animations**: Lift, grow, glow, fade, shine
- **Open Graph & Twitter Card** meta tags with SEO plugin auto-detection
- **Page builder support**: Divi (incl. Theme Builder), Elementor, Bricks, Beaver Builder, and standard themes
- Drag-and-drop platform ordering
- 3 button shapes (circle, rounded, square) with custom border radius
- Floating share button with 6 screen positions and mobile toggle
- Per-variant custom colors for inline, collapsible, and floating modes
- Auto-placement (before/after/both) or manual `[webworq_share]` shortcode
- Icons from the [Automattic social-logos](https://github.com/Automattic/social-logos) icon set
- Fully extensible via WordPress filters

## Requirements

- WordPress 5.0+
- PHP 7.4+

## Installation

1. Download the latest release zip
2. In WordPress, go to **Plugins → Add New → Upload Plugin**
3. Upload the zip and activate
4. Configure at **Settings → Webworq Social Share**

## Shortcode

Use `[webworq_share]` in any post, page, widget, or page builder code module.

Optional attributes:
```
[webworq_share platforms="linkedin,x,bluesky" style="rounded" size="large" mode="inline"]
```

## Extending

Add custom platforms with the `webworq_ss_platforms` filter:

```php
add_filter( 'webworq_ss_platforms', function( $platforms ) {
    $platforms['mastodon'] = array(
        'label'     => 'Mastodon',
        'icon'      => '<svg>...</svg>',
        'color'     => '#6364FF',
        'url'       => 'https://mastodonshare.com/?text={title}&url={url}',
        'enabled'   => true,
    );
    return $platforms;
});
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

## License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
