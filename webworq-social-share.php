<?php
/**
 * Plugin Name: Webworq Social Share Buttons
 * Plugin URI: https://github.com/Webworq/Ripple---Smart-Social-Share-Buttons
 * Description: Smart social share buttons with Open Graph & Twitter Card metadata for rich link previews. Built by Webworq.
 * Version: 4.0.0
 * Author: Webworq
 * Author URI: https://webworq.dk
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: webworq-social-share
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WEBWORQ_SS_VERSION', '4.0.0' );
define( 'WEBWORQ_SS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEBWORQ_SS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WEBWORQ_SS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 */
final class Webworq_Social_Share {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->maybe_migrate_settings();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Runtime check: migrate old settings or set defaults if nothing exists.
     * This ensures the plugin works even if the activation hook didn't fire
     * (e.g. manual upload, replacing the old plugin).
     */
    private function maybe_migrate_settings() {
        $settings = get_option( 'webworq_ss_settings', false );
        if ( false !== $settings ) {
            return; // Settings already exist, nothing to do
        }

        // Try migrating from Ripple option key
        $ripple_settings = get_option( 'ripple_settings', false );
        if ( false !== $ripple_settings ) {
            update_option( 'webworq_ss_settings', $ripple_settings );
            delete_option( 'ripple_settings' );
            $this->migrate_settings( $ripple_settings );
            return;
        }

        // Try migrating from old WSS option key
        $old_settings = get_option( 'wss_settings', false );
        if ( false !== $old_settings ) {
            update_option( 'webworq_ss_settings', $old_settings );
            delete_option( 'wss_settings' );
            $this->migrate_settings( $old_settings );
            return;
        }

        // No settings at all — set defaults (fresh install without activation hook)
        $this->activate();
    }

    private function includes() {
        require_once WEBWORQ_SS_PLUGIN_DIR . 'includes/class-platforms.php';
        require_once WEBWORQ_SS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WEBWORQ_SS_PLUGIN_DIR . 'includes/class-meta-tags.php';
        require_once WEBWORQ_SS_PLUGIN_DIR . 'includes/class-frontend.php';
    }

    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_filter( 'plugin_action_links_' . WEBWORQ_SS_PLUGIN_BASENAME, array( $this, 'settings_link' ) );
    }

    /**
     * Set default options on activation.
     */
    public function activate() {
        // Check for old option keys and migrate if needed
        $ripple_settings = get_option( 'ripple_settings', false );
        if ( false !== $ripple_settings ) {
            update_option( 'webworq_ss_settings', $ripple_settings );
            delete_option( 'ripple_settings' );
        }

        $old_settings = get_option( 'wss_settings', false );
        if ( false !== $old_settings ) {
            update_option( 'webworq_ss_settings', $old_settings );
            delete_option( 'wss_settings' );
        }

        $existing = get_option( 'webworq_ss_settings', false );

        $defaults = array(
            // Platforms: which ones are enabled
            'platforms'       => array( 'linkedin', 'x', 'bluesky', 'facebook' ),
            // Placement
            'auto_placement'  => 'after',   // 'before', 'after', 'both', 'none'
            'post_types'      => array( 'post' ),
            // Display mode
            'display_mode'    => 'inline',  // 'inline', 'collapsible'
            // Floating (independent)
            'floating_enabled'    => false,
            'floating_position'   => 'bottom-right',
            'floating_post_types' => array( 'post' ),
            // Styling - Legacy (for backward compat)
            'style'           => 'circle',  // 'circle', 'rounded', 'square'
            'size'            => 'medium',  // 'small', 'medium', 'large'
            'color_mode'      => 'brand',   // 'brand', 'mono-dark', 'mono-light', 'custom'
            'custom_color'    => '#333333',
            'custom_hover'    => '#555555',
            'show_labels'     => false,
            'share_heading'   => '',
            // Styling - New v3.0
            'button_gap'           => 8,        // px range 4-24
            'border_radius_type'   => 'shape',  // 'shape' or 'custom'
            'border_radius_custom' => 20,       // px when custom
            'shadow_preset'        => 'none',   // none|subtle|medium|bold
            'hover_animation'      => 'lift',   // lift|grow|glow|fade|shine
            'color_preset'         => 'brand',  // brand|mono-dark|mono-light|outline|minimal|glass|gradient|custom
            'colors' => array(
                'inline' => array(
                    'bg'         => '#333333',
                    'text'       => '#ffffff',
                    'hover_bg'   => '#555555',
                    'hover_text' => '#ffffff',
                    'border'     => '',
                ),
                'collapsible' => array(
                    'trigger_bg'       => '#333333',
                    'trigger_text'     => '#ffffff',
                    'trigger_icon'     => '#ffffff',
                    'trigger_hover_bg' => '#555555',
                    'panel_bg'         => '#f9f9f9',
                ),
                'floating' => array(
                    'trigger_bg'       => '#333333',
                    'trigger_icon'     => '#ffffff',
                    'trigger_hover_bg' => '#555555',
                ),
            ),
            'fab_size'    => 'medium',  // small(44px)|medium(56px)|large(68px)
            'fab_mobile'  => true,      // show on mobile
            // Meta tags
            'inject_og'       => true,
            'default_image'   => '',
            'twitter_handle'  => '',
        );

        // Only set defaults if options don't exist yet
        if ( false === $existing ) {
            update_option( 'webworq_ss_settings', $defaults );
        } else {
            // Migration: upgrade v2 settings to v3
            $this->migrate_settings( $existing );
        }
    }

    /**
     * Migrate v2 settings to v3 format.
     */
    private function migrate_settings( $settings ) {
        $changed = false;

        // If color_preset doesn't exist, migrate from old color_mode
        if ( ! isset( $settings['color_preset'] ) ) {
            $old_mode = $settings['color_mode'] ?? 'brand';
            $settings['color_preset'] = $old_mode;

            // If old mode was 'custom', map colors
            if ( $old_mode === 'custom' ) {
                $settings['colors'] = array(
                    'inline' => array(
                        'bg'         => $settings['custom_color'] ?? '#333333',
                        'text'       => '#ffffff',
                        'hover_bg'   => $settings['custom_hover'] ?? '#555555',
                        'hover_text' => '#ffffff',
                        'border'     => '',
                    ),
                    'collapsible' => array(
                        'trigger_bg'       => $settings['custom_color'] ?? '#333333',
                        'trigger_text'     => '#ffffff',
                        'trigger_icon'     => '#ffffff',
                        'trigger_hover_bg' => $settings['custom_hover'] ?? '#555555',
                        'panel_bg'         => '#f9f9f9',
                    ),
                    'floating' => array(
                        'trigger_bg'       => $settings['custom_color'] ?? '#333333',
                        'trigger_icon'     => '#ffffff',
                        'trigger_hover_bg' => $settings['custom_hover'] ?? '#555555',
                    ),
                );
            }
            $changed = true;
        }

        // Add new v3 defaults if missing
        if ( ! isset( $settings['button_gap'] ) ) {
            $settings['button_gap'] = 8;
            $changed = true;
        }
        if ( ! isset( $settings['border_radius_type'] ) ) {
            $settings['border_radius_type'] = 'shape';
            $changed = true;
        }
        if ( ! isset( $settings['border_radius_custom'] ) ) {
            $settings['border_radius_custom'] = 20;
            $changed = true;
        }
        if ( ! isset( $settings['shadow_preset'] ) ) {
            $settings['shadow_preset'] = 'none';
            $changed = true;
        }
        if ( ! isset( $settings['hover_animation'] ) ) {
            $settings['hover_animation'] = 'lift';
            $changed = true;
        }
        if ( ! isset( $settings['fab_size'] ) ) {
            $settings['fab_size'] = 'medium';
            $changed = true;
        }
        if ( ! isset( $settings['fab_mobile'] ) ) {
            $settings['fab_mobile'] = true;
            $changed = true;
        }

        if ( $changed ) {
            update_option( 'webworq_ss_settings', $settings );
        }
    }

    /**
     * Add settings link to plugins page.
     */
    public function settings_link( $links ) {
        $settings = '<a href="' . admin_url( 'options-general.php?page=webworq-social-share' ) . '">' . __( 'Settings', 'webworq-social-share' ) . '</a>';
        array_unshift( $links, $settings );
        return $links;
    }

    /**
     * Get a plugin setting.
     */
    public static function get_setting( $key, $default = null ) {
        $settings = get_option( 'webworq_ss_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }

    /**
     * Get all settings.
     */
    public static function get_settings() {
        return get_option( 'webworq_ss_settings', array() );
    }
}

/**
 * Sanitize SVG icon markup with wp_kses using allowed SVG tags.
 *
 * wp_kses_post() strips SVG elements because they are not in the
 * default allowed-HTML list. This helper adds the SVG tags and
 * attributes used by the Automattic social-logos icon set.
 *
 * @param string $svg The SVG markup to sanitize.
 * @return string Sanitized SVG markup.
 */
function webworq_ss_kses_icon( $svg ) {
    $svg_attrs = array(
        'xmlns'       => true,
        'viewbox'     => true, // Lowercase: wp_kses lowercases attr names before lookup; restored to viewBox in output.
        'fill'        => true,
        'width'       => true,
        'height'      => true,
        'class'       => true,
        'id'          => true,
        'aria-hidden' => true,
        'role'        => true,
        'focusable'   => true,
        'style'       => true,
    );

    $allowed = array(
        'svg'      => $svg_attrs,
        'path'     => array(
            'd'              => true,
            'fill'           => true,
            'fill-rule'      => true,
            'clip-rule'      => true,
            'stroke'         => true,
            'stroke-width'   => true,
            'stroke-linecap' => true,
            'transform'      => true,
            'class'          => true,
        ),
        'g'        => array(
            'transform' => true,
            'fill'      => true,
            'class'     => true,
            'id'        => true,
        ),
        'circle'   => array(
            'cx'   => true,
            'cy'   => true,
            'r'    => true,
            'fill' => true,
        ),
        'rect'     => array(
            'x'      => true,
            'y'      => true,
            'width'  => true,
            'height' => true,
            'rx'     => true,
            'ry'     => true,
            'fill'   => true,
        ),
        'polygon'  => array(
            'points' => true,
            'fill'   => true,
        ),
        'polyline' => array(
            'points' => true,
            'fill'   => true,
            'stroke' => true,
        ),
        'line'     => array(
            'x1'     => true,
            'y1'     => true,
            'x2'     => true,
            'y2'     => true,
            'stroke' => true,
        ),
        'defs'     => array(),
        'clippath' => array( // Lowercase: wp_kses lowercases tag names.
            'id' => true,
        ),
        'use'      => array(
            'href'      => true,
            'xlink:href' => true,
        ),
    );

    $result = wp_kses( $svg, $allowed );

    // wp_kses lowercases all attribute names, but SVG requires
    // case-sensitive attributes like viewBox. Restore them.
    $result = str_replace( 'viewbox=', 'viewBox=', $result );
    $result = str_replace( 'preserveaspectratio=', 'preserveAspectRatio=', $result );

    return $result;
}

/**
 * Sanitize plugin HTML output that may contain SVG icons.
 *
 * Merges wp_kses_post allowed tags with the SVG tags used by
 * the plugin icon set, so full button HTML passes through intact.
 *
 * @param string $html The HTML to sanitize.
 * @return string Sanitized HTML.
 */
function webworq_ss_kses_output( $html ) {
    // Start with all tags allowed by wp_kses_post.
    $allowed = wp_kses_allowed_html( 'post' );

    // Add SVG tags on top.
    $svg_attrs = array(
        'xmlns'       => true,
        'viewbox'     => true, // Lowercase: wp_kses lowercases attr names before lookup; restored to viewBox in output.
        'fill'        => true,
        'width'       => true,
        'height'      => true,
        'class'       => true,
        'id'          => true,
        'aria-hidden' => true,
        'role'        => true,
        'focusable'   => true,
        'style'       => true,
    );

    $allowed['svg']  = $svg_attrs;
    $allowed['path'] = array(
        'd'              => true,
        'fill'           => true,
        'fill-rule'      => true,
        'clip-rule'      => true,
        'stroke'         => true,
        'stroke-width'   => true,
        'stroke-linecap' => true,
        'transform'      => true,
        'class'          => true,
    );
    $allowed['g'] = array(
        'transform' => true,
        'fill'      => true,
        'class'     => true,
        'id'        => true,
    );

    $result = wp_kses( $html, $allowed );

    // wp_kses lowercases all attribute names, but SVG requires
    // case-sensitive attributes like viewBox. Restore them.
    $result = str_replace( 'viewbox=', 'viewBox=', $result );
    $result = str_replace( 'preserveaspectratio=', 'preserveAspectRatio=', $result );

    return $result;
}

/**
 * Initialize the plugin.
 */
function webworq_social_share() {
    return Webworq_Social_Share::instance();
}
add_action( 'plugins_loaded', 'webworq_social_share' );
