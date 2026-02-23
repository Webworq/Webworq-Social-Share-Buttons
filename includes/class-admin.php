<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Ripple_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function add_menu() {
        add_options_page(
            __( 'Ripple — Smart Social Share Buttons', 'ripple-social-share' ),
            __( 'Ripple', 'ripple-social-share' ),
            'manage_options',
            'ripple-social-share',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting( 'ripple_settings_group', 'ripple_settings', array( $this, 'sanitize' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( 'settings_page_ripple-social-share' !== $hook ) return;

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_style( 'ripple-frontend', RIPPLE_PLUGIN_URL . 'assets/css/frontend.css', array(), RIPPLE_VERSION );
        wp_enqueue_style( 'ripple-admin', RIPPLE_PLUGIN_URL . 'assets/css/admin.css', array( 'ripple-frontend' ), RIPPLE_VERSION );
        wp_enqueue_script( 'ripple-admin', RIPPLE_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), RIPPLE_VERSION, true );
    }

    public function sanitize( $input ) {
        $clean = array();

        // Platforms - array of slugs
        $clean['platforms'] = isset( $input['platforms'] ) && is_array( $input['platforms'] )
            ? array_map( 'sanitize_key', $input['platforms'] )
            : array();

        // Placement (before/after/both/none — floating is independent)
        $valid_placements = array( 'after', 'before', 'both', 'none' );
        $clean['auto_placement'] = isset( $input['auto_placement'] ) && in_array( $input['auto_placement'], $valid_placements )
            ? $input['auto_placement']
            : 'after';

        // Post types - allow empty array when explicitly submitted
        if ( ! empty( $input['post_types_submitted'] ) ) {
            $clean['post_types'] = isset( $input['post_types'] ) && is_array( $input['post_types'] )
                ? array_map( 'sanitize_key', $input['post_types'] )
                : array();
        } else {
            $clean['post_types'] = isset( $input['post_types'] ) && is_array( $input['post_types'] )
                ? array_map( 'sanitize_key', $input['post_types'] )
                : array( 'post' );
        }

        // Display mode (inline or collapsible — for in-content placement)
        $valid_modes_dm = array( 'inline', 'collapsible' );
        $clean['display_mode'] = isset( $input['display_mode'] ) && in_array( $input['display_mode'], $valid_modes_dm )
            ? $input['display_mode'] : 'inline';

        // Floating button (independent toggle)
        $clean['floating_enabled'] = ! empty( $input['floating_enabled'] );

        // Floating position
        $valid_positions = array( 'top-left', 'middle-left', 'bottom-left', 'top-right', 'middle-right', 'bottom-right' );
        $clean['floating_position'] = isset( $input['floating_position'] ) && in_array( $input['floating_position'], $valid_positions )
            ? $input['floating_position'] : 'bottom-right';

        // Floating post types
        if ( ! empty( $input['floating_post_types_submitted'] ) ) {
            $clean['floating_post_types'] = isset( $input['floating_post_types'] ) && is_array( $input['floating_post_types'] )
                ? array_map( 'sanitize_key', $input['floating_post_types'] )
                : array();
        } else {
            $clean['floating_post_types'] = isset( $input['floating_post_types'] ) && is_array( $input['floating_post_types'] )
                ? array_map( 'sanitize_key', $input['floating_post_types'] )
                : array( 'post' );
        }

        // Styling - Legacy (keep for backward compat)
        $valid_styles = array( 'circle', 'rounded', 'square' );
        $clean['style'] = isset( $input['style'] ) && in_array( $input['style'], $valid_styles )
            ? $input['style'] : 'circle';

        $valid_sizes = array( 'small', 'medium', 'large' );
        $clean['size'] = isset( $input['size'] ) && in_array( $input['size'], $valid_sizes )
            ? $input['size'] : 'medium';

        $valid_modes = array( 'brand', 'mono-dark', 'mono-light', 'custom' );
        $clean['color_mode'] = isset( $input['color_mode'] ) && in_array( $input['color_mode'], $valid_modes )
            ? $input['color_mode'] : 'brand';

        $clean['custom_color'] = isset( $input['custom_color'] )
            ? sanitize_hex_color( $input['custom_color'] ) : '#333333';

        $clean['custom_hover'] = isset( $input['custom_hover'] )
            ? sanitize_hex_color( $input['custom_hover'] ) : '#555555';

        $clean['show_labels'] = ! empty( $input['show_labels'] );

        $clean['share_heading'] = isset( $input['share_heading'] )
            ? sanitize_text_field( $input['share_heading'] ) : '';

        // Styling - New v3.0
        // Button gap
        $clean['button_gap'] = isset( $input['button_gap'] ) ? max( 4, min( 24, (int) $input['button_gap'] ) ) : 8;

        // Border radius
        $valid_radius_types = array( 'shape', 'custom' );
        $clean['border_radius_type'] = isset( $input['border_radius_type'] ) && in_array( $input['border_radius_type'], $valid_radius_types ) ? $input['border_radius_type'] : 'shape';
        $clean['border_radius_custom'] = isset( $input['border_radius_custom'] ) ? max( 0, min( 50, (int) $input['border_radius_custom'] ) ) : 20;

        // Shadow
        $valid_shadows = array( 'none', 'subtle', 'medium', 'bold' );
        $clean['shadow_preset'] = isset( $input['shadow_preset'] ) && in_array( $input['shadow_preset'], $valid_shadows ) ? $input['shadow_preset'] : 'none';

        // Hover animation
        $valid_hovers = array( 'lift', 'grow', 'glow', 'fade', 'shine' );
        $clean['hover_animation'] = isset( $input['hover_animation'] ) && in_array( $input['hover_animation'], $valid_hovers ) ? $input['hover_animation'] : 'lift';

        // Color preset
        $valid_presets = array( 'brand', 'mono-dark', 'mono-light', 'outline', 'minimal', 'glass', 'gradient', 'custom' );
        $clean['color_preset'] = isset( $input['color_preset'] ) && in_array( $input['color_preset'], $valid_presets ) ? $input['color_preset'] : 'brand';

        // Per-variant colors
        $color_defaults = array(
            'inline' => array( 'bg' => '#333333', 'text' => '#ffffff', 'hover_bg' => '#555555', 'hover_text' => '#ffffff', 'border' => '' ),
            'collapsible' => array( 'trigger_bg' => '#333333', 'trigger_text' => '#ffffff', 'trigger_icon' => '#ffffff', 'trigger_hover_bg' => '#555555', 'panel_bg' => '#f9f9f9' ),
            'floating' => array( 'trigger_bg' => '#333333', 'trigger_icon' => '#ffffff', 'trigger_hover_bg' => '#555555' ),
        );
        $clean['colors'] = array();
        foreach ( $color_defaults as $variant => $fields ) {
            $clean['colors'][ $variant ] = array();
            foreach ( $fields as $field => $default ) {
                $val = isset( $input['colors'][ $variant ][ $field ] ) ? sanitize_hex_color( $input['colors'][ $variant ][ $field ] ) : '';
                $clean['colors'][ $variant ][ $field ] = $val ? $val : $default;
            }
        }

        // FAB extras
        $valid_fab_sizes = array( 'small', 'medium', 'large' );
        $clean['fab_size'] = isset( $input['fab_size'] ) && in_array( $input['fab_size'], $valid_fab_sizes ) ? $input['fab_size'] : 'medium';
        $clean['fab_mobile'] = ! empty( $input['fab_mobile'] );

        // Meta tags
        $clean['inject_og'] = ! empty( $input['inject_og'] );

        $clean['default_image'] = isset( $input['default_image'] )
            ? esc_url_raw( $input['default_image'] ) : '';

        $clean['twitter_handle'] = isset( $input['twitter_handle'] )
            ? sanitize_text_field( ltrim( $input['twitter_handle'], '@' ) ) : '';

        // Platform order
        $clean['platform_order'] = isset( $input['platform_order'] )
            ? sanitize_text_field( $input['platform_order'] ) : '';

        return $clean;
    }

    public function render_page() {
        $settings  = Ripple_Social_Share::get_settings();
        $platforms = Ripple_Platforms::get_all();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'platforms';
        ?>
        <div class="wrap ripple-wrap">
            <div class="ripple-header">
                <h1>
                    <span class="ripple-logo"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1050 560"><g transform="translate(-66, 0)"><path d="M482.028,127.552c68.678-3.438,133.791,20.53,184.477,66.29l305.786,305.871c-11.529,6.257-23.924,11.151-36.506,14.931-89.755,26.968-184.864,3.869-251.798-61.098L385.201,154.676c-.615-2.799,13.565-8.158,16.384-9.324,25.113-10.379,53.294-16.441,80.443-17.8h0Z"/><path d="M562.559,383.751c37.056,37.172,74.009,74.57,110.897,111.928,2.256,2.285,5.104,1.704,4.126,5.766-10.939,3.084-21.029,8.499-31.842,12.036-88.447,28.927-186.958,6.104-253.544-58.195L92.748,155.923l-1.054-4.09c96.742-44.785,204.585-27.196,282.992,43.779,33.721,30.524,64.337,65.524,96.438,97.709,30.263,30.342,61.198,60.099,91.435,90.431h0Z"/><path d="M1040.143,130.723l-1.741,3.51c-47.331,46.61-93.494,94.433-140.972,140.891-22.606,22.121-40.946,45.234-74.833,21.098l-167.233-166.664c23.573-1.811,47.174-.719,70.806-1.133,81.628-1.429,163.864-2.228,245.511,0,22.879.624,45.593.686,68.462,2.297h0Z" fill="#f60"/></g></svg></span>
                    <?php esc_html_e( 'Ripple', 'ripple-social-share' ); ?>
                    <span class="ripple-version">v<?php echo esc_html( RIPPLE_VERSION ); ?></span>
                </h1>
                <p class="ripple-tagline"><?php esc_html_e( 'Smart Social Share Buttons &amp; Open Graph by Webworq', 'ripple-social-share' ); ?></p>
            </div>

            <nav class="nav-tab-wrapper ripple-tabs">
                <a href="?page=ripple-social-share&tab=platforms" class="nav-tab <?php echo $active_tab === 'platforms' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Platforms', 'ripple-social-share' ); ?>
                </a>
                <a href="?page=ripple-social-share&tab=styling" class="nav-tab <?php echo $active_tab === 'styling' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Styling', 'ripple-social-share' ); ?>
                </a>
                <a href="?page=ripple-social-share&tab=placement" class="nav-tab <?php echo $active_tab === 'placement' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Placement', 'ripple-social-share' ); ?>
                </a>
                <a href="?page=ripple-social-share&tab=floating" class="nav-tab <?php echo $active_tab === 'floating' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Floating', 'ripple-social-share' ); ?>
                </a>
                <a href="?page=ripple-social-share&tab=metadata" class="nav-tab <?php echo $active_tab === 'metadata' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Metadata', 'ripple-social-share' ); ?>
                </a>
            </nav>

            <form method="post" action="options.php" class="ripple-form">
                <?php settings_fields( 'ripple_settings_group' ); ?>

                <?php if ( $active_tab === 'platforms' ) : ?>
                <div class="ripple-section">
                    <h2><?php esc_html_e( 'Choose & Order Platforms', 'ripple-social-share' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Toggle platforms on/off and drag to reorder. New platforms can be added via the ripple_platforms filter.', 'ripple-social-share' ); ?></p>

                    <input type="hidden" name="ripple_settings[platform_order]" id="ripple-platform-order"
                           value="<?php echo esc_attr( isset( $settings['platform_order'] ) ? $settings['platform_order'] : '' ); ?>">

                    <ul id="ripple-platform-list" class="ripple-platform-list">
                        <?php
                        $enabled = isset( $settings['platforms'] ) ? $settings['platforms'] : array();
                        $order   = array();

                        if ( ! empty( $settings['platform_order'] ) ) {
                            $order = explode( ',', $settings['platform_order'] );
                        }

                        $all_slugs = array_keys( $platforms );
                        $ordered_slugs = array();
                        foreach ( $order as $s ) {
                            if ( in_array( $s, $all_slugs ) ) {
                                $ordered_slugs[] = $s;
                            }
                        }
                        foreach ( $all_slugs as $s ) {
                            if ( ! in_array( $s, $ordered_slugs ) ) {
                                $ordered_slugs[] = $s;
                            }
                        }

                        foreach ( $ordered_slugs as $slug ) :
                            $p = $platforms[ $slug ];
                            $checked = in_array( $slug, $enabled ) ? 'checked' : '';
                        ?>
                        <li class="ripple-platform-item" data-slug="<?php echo esc_attr( $slug ); ?>">
                            <span class="ripple-drag-handle">&#9776;</span>
                            <span class="ripple-platform-icon" style="color: <?php echo esc_attr( $p['color'] ); ?>">
                                <?php echo wp_kses_post( $p['icon'] ); ?>
                            </span>
                            <label>
                                <input type="checkbox" name="ripple_settings[platforms][]"
                                       value="<?php echo esc_attr( $slug ); ?>" <?php echo esc_attr( $checked ); ?>>
                                <?php echo esc_html( $p['label'] ); ?>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php elseif ( $active_tab === 'styling' ) : ?>
                <div class="ripple-section">
                    <h2><?php esc_html_e( 'Button Style', 'ripple-social-share' ); ?></h2>

                    <!-- SECTION 1: DISPLAY MODE -->
                    <h3><?php esc_html_e( 'Display Mode', 'ripple-social-share' ); ?></h3>
                    <p class="description"><?php esc_html_e( 'Choose how buttons appear on your site. The floating button is configured separately under Floating tab.', 'ripple-social-share' ); ?></p>

                    <?php $dm = isset( $settings['display_mode'] ) ? $settings['display_mode'] : 'inline'; ?>
                    <div class="ripple-mode-cards">
                        <label class="ripple-mode-card <?php echo $dm === 'inline' ? 'ripple-mode-card-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[display_mode]" value="inline" <?php checked( $dm, 'inline' ); ?>>
                            <div class="ripple-mode-card-preview">
                                <div class="ripple-preview-inline">
                                    <div class="ripple-preview-dot" style="background:#0A66C2;"></div>
                                    <div class="ripple-preview-dot" style="background:#000;"></div>
                                    <div class="ripple-preview-dot" style="background:#0085FF;"></div>
                                    <div class="ripple-preview-dot" style="background:#1877F2;"></div>
                                </div>
                            </div>
                            <div class="ripple-mode-card-info">
                                <strong><?php esc_html_e( 'Inline', 'ripple-social-share' ); ?></strong>
                                <span><?php esc_html_e( 'All buttons visible in a row', 'ripple-social-share' ); ?></span>
                            </div>
                        </label>

                        <label class="ripple-mode-card <?php echo $dm === 'collapsible' ? 'ripple-mode-card-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[display_mode]" value="collapsible" <?php checked( $dm, 'collapsible' ); ?>>
                            <div class="ripple-mode-card-preview">
                                <div class="ripple-preview-collapsible">
                                    <div class="ripple-preview-trigger-btn">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="#fff"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                                        <span>Share</span>
                                        <svg width="8" height="8" viewBox="0 0 24 24" fill="#fff"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                                    </div>
                                    <div class="ripple-preview-dropdown">
                                        <div class="ripple-preview-dropdown-item"><div class="ripple-preview-dot-sm" style="background:#0A66C2;"></div><span>LinkedIn</span></div>
                                        <div class="ripple-preview-dropdown-item"><div class="ripple-preview-dot-sm" style="background:#000;"></div><span>X</span></div>
                                        <div class="ripple-preview-dropdown-item"><div class="ripple-preview-dot-sm" style="background:#0085FF;"></div><span>Bluesky</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="ripple-mode-card-info">
                                <strong><?php esc_html_e( 'Collapsible', 'ripple-social-share' ); ?></strong>
                                <span><?php esc_html_e( 'Share button that expands on click', 'ripple-social-share' ); ?></span>
                            </div>
                        </label>
                    </div>

                    <hr style="margin:30px 0;">

                    <!-- SECTION 2: GLOBAL STYLE -->
                    <h3><?php esc_html_e( 'Global Style', 'ripple-social-share' ); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Shape', 'ripple-social-share' ); ?></th>
                            <td>
                                <fieldset class="ripple-shape-picker">
                                    <?php
                                    $shapes = array( 'circle' => 'Circle', 'rounded' => 'Rounded', 'square' => 'Square' );
                                    $current_style = isset( $settings['style'] ) ? $settings['style'] : 'circle';
                                    foreach ( $shapes as $val => $label ) :
                                    ?>
                                    <label class="ripple-shape-option <?php echo $current_style === $val ? 'selected' : ''; ?>">
                                        <input type="radio" name="ripple_settings[style]" value="<?php echo esc_attr( $val ); ?>"
                                            <?php checked( $current_style, $val ); ?>>
                                        <span class="ripple-shape-preview ripple-shape-<?php echo esc_attr( $val ); ?>"></span>
                                        <?php echo esc_html( $label ); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description" style="margin-top:8px;"><?php esc_html_e( 'Button corner style. Choose between completely circular, slightly rounded, or perfectly square.', 'ripple-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Border Radius', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php
                                $radius_type = isset( $settings['border_radius_type'] ) ? $settings['border_radius_type'] : 'shape';
                                $radius_custom = isset( $settings['border_radius_custom'] ) ? $settings['border_radius_custom'] : 20;
                                ?>
                                <label style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                    <input type="radio" name="ripple_settings[border_radius_type]" value="shape"
                                        <?php checked( $radius_type, 'shape' ); ?> class="ripple-radius-toggle">
                                    <span><?php esc_html_e( 'Use shape preset', 'ripple-social-share' ); ?></span>
                                </label>
                                <label style="display:flex; align-items:center; gap:10px;">
                                    <input type="radio" name="ripple_settings[border_radius_type]" value="custom"
                                        <?php checked( $radius_type, 'custom' ); ?> class="ripple-radius-toggle">
                                    <span><?php esc_html_e( 'Custom radius:', 'ripple-social-share' ); ?></span>
                                </label>
                                <div class="ripple-custom-radius-input" style="<?php echo $radius_type === 'custom' ? '' : 'display:none;'; ?> margin-top:8px;">
                                    <input type="range" name="ripple_settings[border_radius_custom]" min="0" max="50"
                                        value="<?php echo esc_attr( $radius_custom ); ?>" class="ripple-range-slider">
                                    <span class="ripple-range-value"><?php echo esc_html( $radius_custom ); ?>px</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Size', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php
                                $sizes = array( 'small' => 'Small (32px)', 'medium' => 'Medium (40px)', 'large' => 'Large (48px)' );
                                $current_size = isset( $settings['size'] ) ? $settings['size'] : 'medium';
                                ?>
                                <select name="ripple_settings[size]">
                                    <?php foreach ( $sizes as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_size, $val ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Button Spacing', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php $gap = isset( $settings['button_gap'] ) ? $settings['button_gap'] : 8; ?>
                                <input type="range" name="ripple_settings[button_gap]" min="4" max="24" value="<?php echo esc_attr( $gap ); ?>" class="ripple-range-slider">
                                <span class="ripple-range-value"><?php echo esc_html( $gap ); ?>px</span>
                                <p class="description" style="margin-top:8px;"><?php esc_html_e( 'Space between buttons (4-24px)', 'ripple-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Shadow', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php $shadow = isset( $settings['shadow_preset'] ) ? $settings['shadow_preset'] : 'none'; ?>
                                <select name="ripple_settings[shadow_preset]">
                                    <option value="none" <?php selected( $shadow, 'none' ); ?>><?php esc_html_e( 'None', 'ripple-social-share' ); ?></option>
                                    <option value="subtle" <?php selected( $shadow, 'subtle' ); ?>><?php esc_html_e( 'Subtle', 'ripple-social-share' ); ?></option>
                                    <option value="medium" <?php selected( $shadow, 'medium' ); ?>><?php esc_html_e( 'Medium', 'ripple-social-share' ); ?></option>
                                    <option value="bold" <?php selected( $shadow, 'bold' ); ?>><?php esc_html_e( 'Bold', 'ripple-social-share' ); ?></option>
                                </select>
                                <p class="description" style="margin-top:8px;"><?php esc_html_e( 'Button shadow effect for depth', 'ripple-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Hover Effect', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php $hover = isset( $settings['hover_animation'] ) ? $settings['hover_animation'] : 'lift'; ?>
                                <select name="ripple_settings[hover_animation]">
                                    <option value="lift" <?php selected( $hover, 'lift' ); ?>><?php esc_html_e( 'Lift up', 'ripple-social-share' ); ?></option>
                                    <option value="grow" <?php selected( $hover, 'grow' ); ?>><?php esc_html_e( 'Grow larger', 'ripple-social-share' ); ?></option>
                                    <option value="glow" <?php selected( $hover, 'glow' ); ?>><?php esc_html_e( 'Glow shadow', 'ripple-social-share' ); ?></option>
                                    <option value="fade" <?php selected( $hover, 'fade' ); ?>><?php esc_html_e( 'Fade out', 'ripple-social-share' ); ?></option>
                                    <option value="shine" <?php selected( $hover, 'shine' ); ?>><?php esc_html_e( 'Shine bright', 'ripple-social-share' ); ?></option>
                                </select>
                                <p class="description" style="margin-top:8px;"><?php esc_html_e( 'Animation when hovering over buttons', 'ripple-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Show Labels', 'ripple-social-share' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ripple_settings[show_labels]" value="1"
                                        <?php checked( ! empty( $settings['show_labels'] ) ); ?>>
                                    <?php esc_html_e( 'Show platform name next to icon', 'ripple-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Heading Text', 'ripple-social-share' ); ?></th>
                            <td>
                                <input type="text" name="ripple_settings[share_heading]" class="regular-text"
                                       value="<?php echo esc_attr( isset( $settings['share_heading'] ) ? $settings['share_heading'] : '' ); ?>"
                                       placeholder="<?php esc_html_e( 'e.g. Share this post', 'ripple-social-share' ); ?>">
                                <p class="description"><?php esc_html_e( 'Optional heading above the buttons. Leave blank for no heading.', 'ripple-social-share' ); ?></p>
                            </td>
                        </tr>
                    </table>

                    <hr style="margin:30px 0;">

                    <!-- SECTION 3: COLOR THEME -->
                    <h3><?php esc_html_e( 'Color Theme', 'ripple-social-share' ); ?></h3>

                    <p class="description" style="margin-bottom:16px;"><?php esc_html_e( 'Choose a preset color scheme for your buttons.', 'ripple-social-share' ); ?></p>

                    <?php $color_preset = isset( $settings['color_preset'] ) ? $settings['color_preset'] : 'brand'; ?>
                    <div class="ripple-preset-grid">
                        <label class="ripple-preset-card <?php echo $color_preset === 'brand' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="brand" <?php checked( $color_preset, 'brand' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <div style="width:24px; height:24px; background:#0A66C2; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#000; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#0085FF; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#1877F2; border-radius:50%;"></div>
                                </div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Brand Colors', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'mono-dark' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="mono-dark" <?php checked( $color_preset, 'mono-dark' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="display:flex; gap:6px;">
                                    <div style="width:24px; height:24px; background:#333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#333; border-radius:50%;"></div>
                                </div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Mono Dark', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'mono-light' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="mono-light" <?php checked( $color_preset, 'mono-light' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="display:flex; gap:6px;">
                                    <div style="width:24px; height:24px; background:#e0e0e0; border-radius:50%; border:1px solid #ccc;"></div>
                                    <div style="width:24px; height:24px; background:#e0e0e0; border-radius:50%; border:1px solid #ccc;"></div>
                                    <div style="width:24px; height:24px; background:#e0e0e0; border-radius:50%; border:1px solid #ccc;"></div>
                                </div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Mono Light', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'outline' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="outline" <?php checked( $color_preset, 'outline' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="display:flex; gap:6px;">
                                    <div style="width:24px; height:24px; border:2px solid #333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; border:2px solid #333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; border:2px solid #333; border-radius:50%;"></div>
                                </div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Outline', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'minimal' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="minimal" <?php checked( $color_preset, 'minimal' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="display:flex; gap:8px;">
                                    <div style="width:16px; height:16px; background:#0A66C2;"></div>
                                    <div style="width:16px; height:16px; background:#000;"></div>
                                    <div style="width:16px; height:16px; background:#0085FF;"></div>
                                </div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Minimal', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'glass' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="glass" <?php checked( $color_preset, 'glass' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="width:60px; height:30px; background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); border-radius:6px; backdrop-filter:blur(10px);"></div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Glass', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'gradient' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="gradient" <?php checked( $color_preset, 'gradient' ); ?>>
                            <div class="ripple-preset-preview">
                                <div style="width:60px; height:30px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:6px;"></div>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Gradient', 'ripple-social-share' ); ?></span>
                        </label>

                        <label class="ripple-preset-card <?php echo $color_preset === 'custom' ? 'ripple-preset-active' : ''; ?>">
                            <input type="radio" name="ripple_settings[color_preset]" value="custom" <?php checked( $color_preset, 'custom' ); ?>>
                            <div class="ripple-preset-preview">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 2v20M2 12h20"/>
                                </svg>
                            </div>
                            <span class="ripple-preset-label"><?php esc_html_e( 'Custom', 'ripple-social-share' ); ?></span>
                        </label>
                    </div>

                    <!-- Per-variant color pickers (only shown when preset = 'custom') -->
                    <div class="ripple-color-subtabs-wrapper" style="<?php echo $color_preset === 'custom' ? '' : 'display:none;'; ?> margin-top:24px; padding-top:24px; border-top:1px solid #ddd;">
                        <h4><?php esc_html_e( 'Custom Colors', 'ripple-social-share' ); ?></h4>

                        <div class="ripple-color-subtabs">
                            <button type="button" class="ripple-color-subtab ripple-color-subtab-inline active" data-variant="inline">
                                <?php esc_html_e( 'Inline', 'ripple-social-share' ); ?>
                            </button>
                            <button type="button" class="ripple-color-subtab ripple-color-subtab-collapsible" data-variant="collapsible">
                                <?php esc_html_e( 'Collapsible', 'ripple-social-share' ); ?>
                            </button>
                            <button type="button" class="ripple-color-subtab ripple-color-subtab-floating" data-variant="floating">
                                <?php esc_html_e( 'Floating', 'ripple-social-share' ); ?>
                            </button>
                        </div>

                        <!-- Inline variant -->
                        <div class="ripple-color-panel ripple-color-panel-inline" style="display:block;">
                            <table class="form-table">
                                <tr>
                                    <th><?php esc_html_e( 'Button Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][inline][bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['bg'] ?? '#333333' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Icon/Text Color', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][inline][text]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['text'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hover Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][inline][hover_bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['hover_bg'] ?? '#555555' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Hover Text Color', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][inline][hover_text]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['hover_text'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Border Color', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][inline][border]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['border'] ?? '' ); ?>">
                                        <p class="description"><?php esc_html_e( 'Optional. Leave empty for no border.', 'ripple-social-share' ); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Collapsible variant -->
                        <div class="ripple-color-panel ripple-color-panel-collapsible" style="display:none;">
                            <table class="form-table">
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][collapsible][trigger_bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_bg'] ?? '#333333' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Text Color', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][collapsible][trigger_text]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_text'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Icon Color', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][collapsible][trigger_icon]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_icon'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Hover Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][collapsible][trigger_hover_bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_hover_bg'] ?? '#555555' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Panel Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][collapsible][panel_bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['panel_bg'] ?? '#f9f9f9' ); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Floating variant -->
                        <div class="ripple-color-panel ripple-color-panel-floating" style="display:none;">
                            <table class="form-table">
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][floating][trigger_bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['floating']['trigger_bg'] ?? '#333333' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Icon Color', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][floating][trigger_icon]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['floating']['trigger_icon'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Trigger Hover Background', 'ripple-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="ripple_settings[colors][floating][trigger_hover_bg]" class="ripple-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['floating']['trigger_hover_bg'] ?? '#555555' ); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr style="margin:30px 0;">

                    <h3><?php esc_html_e( 'Preview', 'ripple-social-share' ); ?></h3>
                    <div id="ripple-preview" class="ripple-preview-box">
                        <?php
                        // Render a live preview using the current settings
                        if ( class_exists( 'Ripple_Platforms' ) ) {
                            $preview_platforms = Ripple_Platforms::get_enabled();
                            if ( ! empty( $preview_platforms ) ) {
                                $p_style        = isset( $settings['style'] ) ? $settings['style'] : 'circle';
                                $p_size         = isset( $settings['size'] ) ? $settings['size'] : 'medium';
                                $p_color_preset = isset( $settings['color_preset'] ) ? $settings['color_preset'] : 'brand';
                                $p_show_labels  = ! empty( $settings['show_labels'] );
                                $p_heading      = isset( $settings['share_heading'] ) ? $settings['share_heading'] : '';
                                $p_display_mode = isset( $settings['display_mode'] ) ? $settings['display_mode'] : 'inline';
                                $p_hover        = isset( $settings['hover_animation'] ) ? $settings['hover_animation'] : 'lift';

                                $p_classes = array( 'ripple-share-buttons', 'ripple-mode-' . $p_display_mode, 'ripple-style-' . $p_style, 'ripple-size-' . $p_size, 'ripple-preset-' . $p_color_preset, 'ripple-hover-' . $p_hover );
                                if ( $p_show_labels ) $p_classes[] = 'ripple-with-labels';

                                // Build dynamic CSS vars for preview
                                $p_css = '<style>';
                                $size_map = array( 'small' => '32px', 'medium' => '40px', 'large' => '48px' );
                                $icon_map = array( 'small' => '16px', 'medium' => '20px', 'large' => '24px' );
                                $p_css .= '.ripple-preview-box{--ripple-btn-size:' . $size_map[$p_size] . ';--ripple-icon-size:' . $icon_map[$p_size] . ';';
                                $gap = isset( $settings['button_gap'] ) ? $settings['button_gap'] : 8;
                                $p_css .= '--ripple-btn-gap:' . $gap . 'px;';
                                if ( isset( $settings['border_radius_type'] ) && $settings['border_radius_type'] === 'custom' ) {
                                    $radius = isset( $settings['border_radius_custom'] ) ? $settings['border_radius_custom'] : 20;
                                    $p_css .= '--ripple-btn-radius:' . $radius . 'px;';
                                }
                                $shadow_map = array( 'none' => 'none', 'subtle' => '0 1px 3px rgba(0,0,0,0.1)', 'medium' => '0 2px 8px rgba(0,0,0,0.15)', 'bold' => '0 4px 16px rgba(0,0,0,0.2)' );
                                $shadow = isset( $settings['shadow_preset'] ) ? $settings['shadow_preset'] : 'none';
                                if ( isset( $shadow_map[$shadow] ) ) $p_css .= '--ripple-btn-shadow:' . $shadow_map[$shadow] . ';';
                                $p_css .= '}</style>';
                                echo wp_kses_post( $p_css );

                                if ( $p_display_mode === 'collapsible' ) {
                                    // Show collapsible preview
                                    ?>
                                    <div class="<?php echo esc_attr( implode( ' ', $p_classes ) ); ?> ripple-open">
                                        <button type="button" class="ripple-trigger" aria-expanded="true">
                                            <span class="ripple-trigger-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg></span>
                                            <span class="ripple-trigger-label"><?php echo esc_html( $p_heading ? $p_heading : __( 'Share', 'ripple-social-share' ) ); ?></span>
                                            <span class="ripple-trigger-arrow"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg></span>
                                        </button>
                                        <div class="ripple-collapsible-panel" aria-hidden="false">
                                            <div class="ripple-buttons-wrap">
                                                <?php foreach ( $preview_platforms as $slug => $platform ) : ?>
                                                <a class="ripple-btn ripple-btn-<?php echo esc_attr( $slug ); ?>"
                                                   href="#" onclick="return false;"
                                                   <?php echo $p_color_preset === 'brand' ? 'style="--ripple-btn-color:' . esc_attr( $platform['color'] ) . ';"' : ''; ?>
                                                   title="<?php echo esc_attr( $platform['label'] ); ?>">
                                                    <span class="ripple-icon"><?php echo wp_kses_post( $platform['icon'] ); ?></span>
                                                    <span class="ripple-label"><?php echo esc_html( $platform['label'] ); ?></span>
                                                </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    // Show inline preview
                                    ?>
                                    <div class="<?php echo esc_attr( implode( ' ', $p_classes ) ); ?>">
                                        <?php if ( $p_heading ) : ?>
                                        <span class="ripple-heading"><?php echo esc_html( $p_heading ); ?></span>
                                        <?php endif; ?>
                                        <div class="ripple-buttons-wrap">
                                            <?php foreach ( $preview_platforms as $slug => $platform ) : ?>
                                            <a class="ripple-btn ripple-btn-<?php echo esc_attr( $slug ); ?>"
                                               href="#" onclick="return false;"
                                               <?php echo $p_color_preset === 'brand' ? 'style="--ripple-btn-color:' . esc_attr( $platform['color'] ) . ';"' : ''; ?>
                                               title="<?php echo esc_attr( $platform['label'] ); ?>">
                                                <span class="ripple-icon"><?php echo wp_kses_post( $platform['icon'] ); ?></span>
                                                <?php if ( $p_show_labels ) : ?>
                                                <span class="ripple-label"><?php echo esc_html( $platform['label'] ); ?></span>
                                                <?php endif; ?>
                                            </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<p class="description">' . esc_html( __( 'Enable some platforms in the Platforms tab to see a preview.', 'ripple-social-share' ) ) . '</p>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <?php elseif ( $active_tab === 'placement' ) : ?>
                <div class="ripple-section">
                    <h2><?php esc_html_e( 'In-Content Buttons', 'ripple-social-share' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'These settings control the inline/collapsible share buttons placed within your content.', 'ripple-social-share' ); ?></p>

                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Position', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php $ap = isset( $settings['auto_placement'] ) ? $settings['auto_placement'] : 'after'; ?>
                                <select name="ripple_settings[auto_placement]" id="ripple-auto-placement">
                                    <option value="after" <?php selected( $ap, 'after' ); ?>><?php esc_html_e( 'After content', 'ripple-social-share' ); ?></option>
                                    <option value="before" <?php selected( $ap, 'before' ); ?>><?php esc_html_e( 'Before content', 'ripple-social-share' ); ?></option>
                                    <option value="both" <?php selected( $ap, 'both' ); ?>><?php esc_html_e( 'Before & after content', 'ripple-social-share' ); ?></option>
                                    <option value="none" <?php selected( $ap, 'none' ); ?>><?php esc_html_e( 'Manual only (shortcode)', 'ripple-social-share' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="ripple-post-types-row" style="<?php echo $ap === 'none' ? 'display:none;' : ''; ?>">
                            <th><?php esc_html_e( 'Post Types', 'ripple-social-share' ); ?></th>
                            <td>
                                <input type="hidden" name="ripple_settings[post_types_submitted]" value="1">
                                <?php
                                $post_types = get_post_types( array( 'public' => true ), 'objects' );
                                $active_pts = isset( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );
                                foreach ( $post_types as $pt ) :
                                    if ( $pt->name === 'attachment' ) continue;
                                ?>
                                <label style="display:block; margin-bottom:4px;">
                                    <input type="checkbox" name="ripple_settings[post_types][]"
                                           value="<?php echo esc_attr( $pt->name ); ?>"
                                        <?php checked( in_array( $pt->name, $active_pts ) ); ?>>
                                    <?php echo esc_html( $pt->label ); ?>
                                </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </table>

                    <hr style="margin:30px 0;">

                    <h3><?php esc_html_e( 'Shortcode', 'ripple-social-share' ); ?></h3>
                    <p class="description">
                        <?php esc_html_e( 'Use the shortcode below anywhere in your content, widgets, or Divi Code Module:', 'ripple-social-share' ); ?>
                    </p>
                    <code class="ripple-shortcode-display">[ripple_share]</code>
                    <p class="description" style="margin-top:8px;">
                        <?php esc_html_e( 'Override display mode per shortcode:', 'ripple-social-share' ); ?>
                        <code>[ripple_share mode="collapsible"]</code> &nbsp;
                        <code>[ripple_share mode="inline"]</code>
                    </p>
                    <p class="description" style="margin-top:4px;">
                        <?php esc_html_e( 'In PHP templates:', 'ripple-social-share' ); ?>
                        <code>&lt;?php echo do_shortcode('[ripple_share]'); ?&gt;</code>
                    </p>
                    <p class="description" style="margin-top:4px; color:#888;">
                        <?php esc_html_e( 'The legacy shortcode [webworq_share] still works for backward compatibility.', 'ripple-social-share' ); ?>
                    </p>
                </div>

                <?php elseif ( $active_tab === 'floating' ) : ?>
                <div class="ripple-section">
                    <h2><?php esc_html_e( 'Floating Share Button', 'ripple-social-share' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'A sticky share button fixed to the screen corner. Works independently from the in-content buttons — you can enable both at the same time.', 'ripple-social-share' ); ?></p>

                    <?php
                    $float_enabled  = ! empty( $settings['floating_enabled'] );
                    $float_position = isset( $settings['floating_position'] ) ? $settings['floating_position'] : 'bottom-right';
                    $float_pts      = isset( $settings['floating_post_types'] ) ? $settings['floating_post_types'] : array( 'post' );
                    $fab_size       = isset( $settings['fab_size'] ) ? $settings['fab_size'] : 'medium';
                    $fab_mobile     = isset( $settings['fab_mobile'] ) ? $settings['fab_mobile'] : true;
                    $post_types     = get_post_types( array( 'public' => true ), 'objects' );
                    ?>

                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Enable', 'ripple-social-share' ); ?></th>
                            <td>
                                <label class="ripple-toggle-label">
                                    <input type="checkbox" name="ripple_settings[floating_enabled]" value="1" id="ripple-floating-enabled"
                                        <?php checked( $float_enabled ); ?>>
                                    <?php esc_html_e( 'Show floating share button', 'ripple-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr class="ripple-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php esc_html_e( 'Screen Position', 'ripple-social-share' ); ?></th>
                            <td>
                                <div class="ripple-position-picker">
                                    <div class="ripple-position-grid">
                                        <?php
                                        $positions = array(
                                            'top-left'     => __( 'Top Left', 'ripple-social-share' ),
                                            'top-right'    => __( 'Top Right', 'ripple-social-share' ),
                                            'middle-left'  => __( 'Middle Left', 'ripple-social-share' ),
                                            'middle-right' => __( 'Middle Right', 'ripple-social-share' ),
                                            'bottom-left'  => __( 'Bottom Left', 'ripple-social-share' ),
                                            'bottom-right' => __( 'Bottom Right', 'ripple-social-share' ),
                                        );
                                        foreach ( $positions as $pos_val => $pos_label ) :
                                        ?>
                                        <label class="ripple-position-cell ripple-position-<?php echo esc_attr( $pos_val ); ?> <?php echo $float_position === $pos_val ? 'ripple-position-active' : ''; ?>">
                                            <input type="radio" name="ripple_settings[floating_position]" value="<?php echo esc_attr( $pos_val ); ?>"
                                                <?php checked( $float_position, $pos_val ); ?>>
                                            <span class="ripple-position-dot"></span>
                                            <span class="ripple-position-label"><?php echo esc_html( $pos_label ); ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="ripple-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php esc_html_e( 'Show On', 'ripple-social-share' ); ?></th>
                            <td>
                                <input type="hidden" name="ripple_settings[floating_post_types_submitted]" value="1">
                                <?php
                                foreach ( $post_types as $pt ) :
                                    if ( $pt->name === 'attachment' ) continue;
                                ?>
                                <label style="display:block; margin-bottom:4px;">
                                    <input type="checkbox" name="ripple_settings[floating_post_types][]"
                                           value="<?php echo esc_attr( $pt->name ); ?>"
                                        <?php checked( in_array( $pt->name, $float_pts ) ); ?>>
                                    <?php echo esc_html( $pt->label ); ?>
                                </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <tr class="ripple-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php esc_html_e( 'FAB Size', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php
                                $fab_sizes = array(
                                    'small' => 'Small (44px)',
                                    'medium' => 'Medium (56px)',
                                    'large' => 'Large (68px)'
                                );
                                ?>
                                <select name="ripple_settings[fab_size]">
                                    <?php foreach ( $fab_sizes as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $fab_size, $val ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="ripple-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php esc_html_e( 'Show on Mobile', 'ripple-social-share' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ripple_settings[fab_mobile]" value="1"
                                        <?php checked( $fab_mobile ); ?>>
                                    <?php esc_html_e( 'Display on mobile devices', 'ripple-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php elseif ( $active_tab === 'metadata' ) : ?>
                <div class="ripple-section">
                    <h2><?php esc_html_e( 'Open Graph & Twitter Card Metadata', 'ripple-social-share' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'These meta tags ensure posts show rich previews when shared. If you already use an SEO plugin (Yoast, RankMath, etc.) that handles OG tags, disable this to avoid duplicates.', 'ripple-social-share' ); ?></p>

                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e( 'Inject Meta Tags', 'ripple-social-share' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ripple_settings[inject_og]" value="1"
                                        <?php checked( ! empty( $settings['inject_og'] ) ); ?>>
                                    <?php esc_html_e( 'Add Open Graph & Twitter Card meta tags to &lt;head&gt;', 'ripple-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Fallback Image', 'ripple-social-share' ); ?></th>
                            <td>
                                <?php $default_img = isset( $settings['default_image'] ) ? $settings['default_image'] : ''; ?>
                                <div class="ripple-image-upload">
                                    <input type="text" name="ripple_settings[default_image]" id="ripple-default-image"
                                           class="regular-text" value="<?php echo esc_url( $default_img ); ?>"
                                           placeholder="https://">
                                    <button type="button" class="button" id="ripple-upload-image">
                                        <?php esc_html_e( 'Choose Image', 'ripple-social-share' ); ?>
                                    </button>
                                </div>
                                <p class="description"><?php esc_html_e( 'Used when a post has no featured image. Recommended: 1200x630px.', 'ripple-social-share' ); ?></p>
                                <?php if ( $default_img ) : ?>
                                <img src="<?php echo esc_url( $default_img ); ?>" style="max-width:300px;margin-top:8px;border-radius:4px;">
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'X/Twitter Handle', 'ripple-social-share' ); ?></th>
                            <td>
                                <input type="text" name="ripple_settings[twitter_handle]" class="regular-text"
                                       value="<?php echo esc_attr( isset( $settings['twitter_handle'] ) ? $settings['twitter_handle'] : '' ); ?>"
                                       placeholder="username">
                                <p class="description"><?php esc_html_e( 'Without the @ symbol. Used for twitter:site meta tag.', 'ripple-social-share' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <?php
                // Preserve settings from other tabs as hidden fields
                $this->preserve_other_tabs( $settings, $active_tab, $platforms );
                ?>

                <?php submit_button( __( 'Save Settings', 'ripple-social-share' ) ); ?>
            </form>

            <div class="ripple-footer">
                <p>
                    <?php
                    echo wp_kses_post( sprintf(
                        /* translators: %1$s: heart symbol, %2$s: company name with link */
                        __( 'Built with %1$s by %2$s', 'ripple-social-share' ),
                        '<span style="color:#e25555;">&hearts;</span>',
                        '<a href="https://webworq.dk" target="_blank" class="ripple-footer-brand"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1050 560" class="ripple-footer-logo"><g transform="translate(-66, 0)"><path d="M482.028,127.552c68.678-3.438,133.791,20.53,184.477,66.29l305.786,305.871c-11.529,6.257-23.924,11.151-36.506,14.931-89.755,26.968-184.864,3.869-251.798-61.098L385.201,154.676c-.615-2.799,13.565-8.158,16.384-9.324,25.113-10.379,53.294-16.441,80.443-17.8h0Z"/><path d="M562.559,383.751c37.056,37.172,74.009,74.57,110.897,111.928,2.256,2.285,5.104,1.704,4.126,5.766-10.939,3.084-21.029,8.499-31.842,12.036-88.447,28.927-186.958,6.104-253.544-58.195L92.748,155.923l-1.054-4.09c96.742-44.785,204.585-27.196,282.992,43.779,33.721,30.524,64.337,65.524,96.438,97.709,30.263,30.342,61.198,60.099,91.435,90.431h0Z"/><path d="M1040.143,130.723l-1.741,3.51c-47.331,46.61-93.494,94.433-140.972,140.891-22.606,22.121-40.946,45.234-74.833,21.098l-167.233-166.664c23.573-1.811,47.174-.719,70.806-1.133,81.628-1.429,163.864-2.228,245.511,0,22.879.624,45.593.686,68.462,2.297h0Z" fill="#f60"/></g></svg> Webworq</a>'
                    ) );
                    ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Preserve settings from non-active tabs via hidden fields.
     */
    private function preserve_other_tabs( $settings, $active_tab, $platforms ) {
        if ( $active_tab !== 'platforms' ) {
            $enabled = isset( $settings['platforms'] ) ? $settings['platforms'] : array();
            foreach ( $enabled as $slug ) {
                echo '<input type="hidden" name="ripple_settings[platforms][]" value="' . esc_attr( $slug ) . '">';
            }
            $order = isset( $settings['platform_order'] ) ? $settings['platform_order'] : '';
            echo '<input type="hidden" name="ripple_settings[platform_order]" value="' . esc_attr( $order ) . '">';
        }

        if ( $active_tab !== 'styling' ) {
            // Legacy fields
            $legacy_fields = array( 'display_mode', 'style', 'size', 'color_mode', 'custom_color', 'custom_hover', 'share_heading' );
            foreach ( $legacy_fields as $f ) {
                if ( isset( $settings[ $f ] ) ) {
                    echo '<input type="hidden" name="ripple_settings[' . esc_attr( $f ) . ']" value="' . esc_attr( $settings[ $f ] ) . '">';
                }
            }
            if ( ! empty( $settings['show_labels'] ) ) {
                echo '<input type="hidden" name="ripple_settings[show_labels]" value="1">';
            }
            // New v3.0 styling fields
            $new_fields = array( 'button_gap', 'border_radius_type', 'border_radius_custom', 'shadow_preset', 'hover_animation', 'color_preset' );
            foreach ( $new_fields as $f ) {
                if ( isset( $settings[ $f ] ) ) {
                    echo '<input type="hidden" name="ripple_settings[' . esc_attr( $f ) . ']" value="' . esc_attr( $settings[ $f ] ) . '">';
                }
            }
            // Colors array
            if ( isset( $settings['colors'] ) && is_array( $settings['colors'] ) ) {
                foreach ( $settings['colors'] as $variant => $variant_colors ) {
                    foreach ( $variant_colors as $color_key => $color_val ) {
                        echo '<input type="hidden" name="ripple_settings[colors][' . esc_attr( $variant ) . '][' . esc_attr( $color_key ) . ']" value="' . esc_attr( $color_val ) . '">';
                    }
                }
            }
        }

        if ( $active_tab !== 'placement' ) {
            // In-content placement
            $ap = isset( $settings['auto_placement'] ) ? $settings['auto_placement'] : 'after';
            echo '<input type="hidden" name="ripple_settings[auto_placement]" value="' . esc_attr( $ap ) . '">';
            $pts = isset( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );
            foreach ( $pts as $pt ) {
                echo '<input type="hidden" name="ripple_settings[post_types][]" value="' . esc_attr( $pt ) . '">';
            }
        }

        if ( $active_tab !== 'floating' ) {
            // Floating settings
            if ( ! empty( $settings['floating_enabled'] ) ) {
                echo '<input type="hidden" name="ripple_settings[floating_enabled]" value="1">';
            }
            $fp = isset( $settings['floating_position'] ) ? $settings['floating_position'] : 'bottom-right';
            echo '<input type="hidden" name="ripple_settings[floating_position]" value="' . esc_attr( $fp ) . '">';
            $fpts = isset( $settings['floating_post_types'] ) ? $settings['floating_post_types'] : array( 'post' );
            foreach ( $fpts as $fpt ) {
                echo '<input type="hidden" name="ripple_settings[floating_post_types][]" value="' . esc_attr( $fpt ) . '">';
            }
            // FAB extras (editable on Floating tab)
            if ( isset( $settings['fab_size'] ) ) {
                echo '<input type="hidden" name="ripple_settings[fab_size]" value="' . esc_attr( $settings['fab_size'] ) . '">';
            }
            if ( ! empty( $settings['fab_mobile'] ) ) {
                echo '<input type="hidden" name="ripple_settings[fab_mobile]" value="1">';
            }
        }

        if ( $active_tab !== 'metadata' ) {
            if ( ! empty( $settings['inject_og'] ) ) {
                echo '<input type="hidden" name="ripple_settings[inject_og]" value="1">';
            }
            $meta_fields = array( 'default_image', 'twitter_handle' );
            foreach ( $meta_fields as $f ) {
                if ( isset( $settings[ $f ] ) ) {
                    echo '<input type="hidden" name="ripple_settings[' . esc_attr( $f ) . ']" value="' . esc_attr( $settings[ $f ] ) . '">';
                }
            }
        }
    }
}

new Ripple_Admin();
