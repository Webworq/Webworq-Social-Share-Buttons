<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Webworq_SS_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function add_menu() {
        add_options_page(
            __( 'Webworq Social Share Buttons', 'webworq-social-share' ),
            __( 'Webworq Share', 'webworq-social-share' ),
            'manage_options',
            'webworq-social-share',
            array( $this, 'render_page' )
        );
    }

    public function register_settings() {
        register_setting( 'webworq_ss_settings_group', 'webworq_ss_settings', array( $this, 'sanitize' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( 'settings_page_webworq-social-share' !== $hook ) return;

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_style( 'webworq-ss-admin', WEBWORQ_SS_PLUGIN_URL . 'assets/css/admin.css', array(), WEBWORQ_SS_VERSION );
        wp_enqueue_script( 'webworq-ss-admin', WEBWORQ_SS_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ), WEBWORQ_SS_VERSION, true );
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
        $settings  = Webworq_Social_Share::get_settings();
        $platforms = Webworq_SS_Platforms::get_all();
        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'platforms';
        ?>
        <div class="wrap webworq-ss-wrap">
            <div class="webworq-ss-header">
                <h1>
                    <span class="webworq-ss-logo"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1050 560"><g transform="translate(-66, 0)"><path d="M482.028,127.552c68.678-3.438,133.791,20.53,184.477,66.29l305.786,305.871c-11.529,6.257-23.924,11.151-36.506,14.931-89.755,26.968-184.864,3.869-251.798-61.098L385.201,154.676c-.615-2.799,13.565-8.158,16.384-9.324,25.113-10.379,53.294-16.441,80.443-17.8h0Z"/><path d="M562.559,383.751c37.056,37.172,74.009,74.57,110.897,111.928,2.256,2.285,5.104,1.704,4.126,5.766-10.939,3.084-21.029,8.499-31.842,12.036-88.447,28.927-186.958,6.104-253.544-58.195L92.748,155.923l-1.054-4.09c96.742-44.785,204.585-27.196,282.992,43.779,33.721,30.524,64.337,65.524,96.438,97.709,30.263,30.342,61.198,60.099,91.435,90.431h0Z"/><path d="M1040.143,130.723l-1.741,3.51c-47.331,46.61-93.494,94.433-140.972,140.891-22.606,22.121-40.946,45.234-74.833,21.098l-167.233-166.664c23.573-1.811,47.174-.719,70.806-1.133,81.628-1.429,163.864-2.228,245.511,0,22.879.624,45.593.686,68.462,2.297h0Z" fill="#f60"/></g></svg></span>
                    <?php _e( 'Webworq Share', 'webworq-social-share' ); ?>
                    <span class="webworq-ss-version">v<?php echo esc_html( WEBWORQ_SS_VERSION ); ?></span>
                </h1>
                <p class="webworq-ss-tagline"><?php _e( 'Smart Social Share Buttons &amp; Open Graph by Webworq', 'webworq-social-share' ); ?></p>
            </div>

            <nav class="nav-tab-wrapper webworq-ss-tabs">
                <a href="?page=webworq-social-share&tab=platforms" class="nav-tab <?php echo $active_tab === 'platforms' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Platforms', 'webworq-social-share' ); ?>
                </a>
                <a href="?page=webworq-social-share&tab=styling" class="nav-tab <?php echo $active_tab === 'styling' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Styling', 'webworq-social-share' ); ?>
                </a>
                <a href="?page=webworq-social-share&tab=placement" class="nav-tab <?php echo $active_tab === 'placement' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Placement', 'webworq-social-share' ); ?>
                </a>
                <a href="?page=webworq-social-share&tab=floating" class="nav-tab <?php echo $active_tab === 'floating' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Floating', 'webworq-social-share' ); ?>
                </a>
                <a href="?page=webworq-social-share&tab=metadata" class="nav-tab <?php echo $active_tab === 'metadata' ? 'nav-tab-active' : ''; ?>">
                    <?php _e( 'Metadata', 'webworq-social-share' ); ?>
                </a>
            </nav>

            <form method="post" action="options.php" class="webworq-ss-form">
                <?php settings_fields( 'webworq_ss_settings_group' ); ?>

                <?php if ( $active_tab === 'platforms' ) : ?>
                <div class="webworq-ss-section">
                    <h2><?php _e( 'Choose & Order Platforms', 'webworq-social-share' ); ?></h2>
                    <p class="description"><?php _e( 'Toggle platforms on/off and drag to reorder. New platforms can be added via the webworq_ss_platforms filter.', 'webworq-social-share' ); ?></p>

                    <input type="hidden" name="webworq_ss_settings[platform_order]" id="webworq-ss-platform-order"
                           value="<?php echo esc_attr( isset( $settings['platform_order'] ) ? $settings['platform_order'] : '' ); ?>">

                    <ul id="webworq-ss-platform-list" class="webworq-ss-platform-list">
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
                        <li class="webworq-ss-platform-item" data-slug="<?php echo esc_attr( $slug ); ?>">
                            <span class="webworq-ss-drag-handle">&#9776;</span>
                            <span class="webworq-ss-platform-icon" style="color: <?php echo esc_attr( $p['color'] ); ?>">
                                <?php echo $p['icon']; ?>
                            </span>
                            <label>
                                <input type="checkbox" name="webworq_ss_settings[platforms][]"
                                       value="<?php echo esc_attr( $slug ); ?>" <?php echo $checked; ?>>
                                <?php echo esc_html( $p['label'] ); ?>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php elseif ( $active_tab === 'styling' ) : ?>
                <div class="webworq-ss-section">
                    <h2><?php _e( 'Button Style', 'webworq-social-share' ); ?></h2>

                    <!-- SECTION 1: DISPLAY MODE -->
                    <h3><?php _e( 'Display Mode', 'webworq-social-share' ); ?></h3>
                    <p class="description"><?php _e( 'Choose how buttons appear on your site. The floating button is configured separately under Floating tab.', 'webworq-social-share' ); ?></p>

                    <?php $dm = isset( $settings['display_mode'] ) ? $settings['display_mode'] : 'inline'; ?>
                    <div class="webworq-ss-mode-cards">
                        <label class="webworq-ss-mode-card <?php echo $dm === 'inline' ? 'webworq-ss-mode-card-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[display_mode]" value="inline" <?php checked( $dm, 'inline' ); ?>>
                            <div class="webworq-ss-mode-card-preview">
                                <div class="webworq-ss-preview-inline">
                                    <div class="webworq-ss-preview-dot" style="background:#0A66C2;"></div>
                                    <div class="webworq-ss-preview-dot" style="background:#000;"></div>
                                    <div class="webworq-ss-preview-dot" style="background:#0085FF;"></div>
                                    <div class="webworq-ss-preview-dot" style="background:#1877F2;"></div>
                                </div>
                            </div>
                            <div class="webworq-ss-mode-card-info">
                                <strong><?php _e( 'Inline', 'webworq-social-share' ); ?></strong>
                                <span><?php _e( 'All buttons visible in a row', 'webworq-social-share' ); ?></span>
                            </div>
                        </label>

                        <label class="webworq-ss-mode-card <?php echo $dm === 'collapsible' ? 'webworq-ss-mode-card-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[display_mode]" value="collapsible" <?php checked( $dm, 'collapsible' ); ?>>
                            <div class="webworq-ss-mode-card-preview">
                                <div class="webworq-ss-preview-collapsible">
                                    <div class="webworq-ss-preview-trigger-btn">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="#fff"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                                        <span>Share</span>
                                        <svg width="8" height="8" viewBox="0 0 24 24" fill="#fff"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                                    </div>
                                    <div class="webworq-ss-preview-dropdown">
                                        <div class="webworq-ss-preview-dropdown-item"><div class="webworq-ss-preview-dot-sm" style="background:#0A66C2;"></div><span>LinkedIn</span></div>
                                        <div class="webworq-ss-preview-dropdown-item"><div class="webworq-ss-preview-dot-sm" style="background:#000;"></div><span>X</span></div>
                                        <div class="webworq-ss-preview-dropdown-item"><div class="webworq-ss-preview-dot-sm" style="background:#0085FF;"></div><span>Bluesky</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="webworq-ss-mode-card-info">
                                <strong><?php _e( 'Collapsible', 'webworq-social-share' ); ?></strong>
                                <span><?php _e( 'Share button that expands on click', 'webworq-social-share' ); ?></span>
                            </div>
                        </label>
                    </div>

                    <hr style="margin:30px 0;">

                    <!-- SECTION 2: GLOBAL STYLE -->
                    <h3><?php _e( 'Global Style', 'webworq-social-share' ); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th><?php _e( 'Shape', 'webworq-social-share' ); ?></th>
                            <td>
                                <fieldset class="webworq-ss-shape-picker">
                                    <?php
                                    $shapes = array( 'circle' => 'Circle', 'rounded' => 'Rounded', 'square' => 'Square' );
                                    $current_style = isset( $settings['style'] ) ? $settings['style'] : 'circle';
                                    foreach ( $shapes as $val => $label ) :
                                    ?>
                                    <label class="webworq-ss-shape-option <?php echo $current_style === $val ? 'selected' : ''; ?>">
                                        <input type="radio" name="webworq_ss_settings[style]" value="<?php echo $val; ?>"
                                            <?php checked( $current_style, $val ); ?>>
                                        <span class="webworq-ss-shape-preview webworq-ss-shape-<?php echo $val; ?>"></span>
                                        <?php echo esc_html( $label ); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </fieldset>
                                <p class="description" style="margin-top:8px;"><?php _e( 'Button corner style. Choose between completely circular, slightly rounded, or perfectly square.', 'webworq-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Border Radius', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php
                                $radius_type = isset( $settings['border_radius_type'] ) ? $settings['border_radius_type'] : 'shape';
                                $radius_custom = isset( $settings['border_radius_custom'] ) ? $settings['border_radius_custom'] : 20;
                                ?>
                                <label style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                    <input type="radio" name="webworq_ss_settings[border_radius_type]" value="shape"
                                        <?php checked( $radius_type, 'shape' ); ?> class="webworq-ss-radius-toggle">
                                    <span><?php _e( 'Use shape preset', 'webworq-social-share' ); ?></span>
                                </label>
                                <label style="display:flex; align-items:center; gap:10px;">
                                    <input type="radio" name="webworq_ss_settings[border_radius_type]" value="custom"
                                        <?php checked( $radius_type, 'custom' ); ?> class="webworq-ss-radius-toggle">
                                    <span><?php _e( 'Custom radius:', 'webworq-social-share' ); ?></span>
                                </label>
                                <div class="webworq-ss-custom-radius-input" style="<?php echo $radius_type === 'custom' ? '' : 'display:none;'; ?> margin-top:8px;">
                                    <input type="range" name="webworq_ss_settings[border_radius_custom]" min="0" max="50"
                                        value="<?php echo esc_attr( $radius_custom ); ?>" class="webworq-ss-range-slider">
                                    <span class="webworq-ss-range-value"><?php echo esc_html( $radius_custom ); ?>px</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Size', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php
                                $sizes = array( 'small' => 'Small (32px)', 'medium' => 'Medium (40px)', 'large' => 'Large (48px)' );
                                $current_size = isset( $settings['size'] ) ? $settings['size'] : 'medium';
                                ?>
                                <select name="webworq_ss_settings[size]">
                                    <?php foreach ( $sizes as $val => $label ) : ?>
                                    <option value="<?php echo $val; ?>" <?php selected( $current_size, $val ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Button Spacing', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php $gap = isset( $settings['button_gap'] ) ? $settings['button_gap'] : 8; ?>
                                <input type="range" name="webworq_ss_settings[button_gap]" min="4" max="24" value="<?php echo esc_attr( $gap ); ?>" class="webworq-ss-range-slider">
                                <span class="webworq-ss-range-value"><?php echo esc_html( $gap ); ?>px</span>
                                <p class="description" style="margin-top:8px;"><?php _e( 'Space between buttons (4-24px)', 'webworq-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Shadow', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php $shadow = isset( $settings['shadow_preset'] ) ? $settings['shadow_preset'] : 'none'; ?>
                                <select name="webworq_ss_settings[shadow_preset]">
                                    <option value="none" <?php selected( $shadow, 'none' ); ?>><?php _e( 'None', 'webworq-social-share' ); ?></option>
                                    <option value="subtle" <?php selected( $shadow, 'subtle' ); ?>><?php _e( 'Subtle', 'webworq-social-share' ); ?></option>
                                    <option value="medium" <?php selected( $shadow, 'medium' ); ?>><?php _e( 'Medium', 'webworq-social-share' ); ?></option>
                                    <option value="bold" <?php selected( $shadow, 'bold' ); ?>><?php _e( 'Bold', 'webworq-social-share' ); ?></option>
                                </select>
                                <p class="description" style="margin-top:8px;"><?php _e( 'Button shadow effect for depth', 'webworq-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Hover Effect', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php $hover = isset( $settings['hover_animation'] ) ? $settings['hover_animation'] : 'lift'; ?>
                                <select name="webworq_ss_settings[hover_animation]">
                                    <option value="lift" <?php selected( $hover, 'lift' ); ?>><?php _e( 'Lift up', 'webworq-social-share' ); ?></option>
                                    <option value="grow" <?php selected( $hover, 'grow' ); ?>><?php _e( 'Grow larger', 'webworq-social-share' ); ?></option>
                                    <option value="glow" <?php selected( $hover, 'glow' ); ?>><?php _e( 'Glow shadow', 'webworq-social-share' ); ?></option>
                                    <option value="fade" <?php selected( $hover, 'fade' ); ?>><?php _e( 'Fade out', 'webworq-social-share' ); ?></option>
                                    <option value="shine" <?php selected( $hover, 'shine' ); ?>><?php _e( 'Shine bright', 'webworq-social-share' ); ?></option>
                                </select>
                                <p class="description" style="margin-top:8px;"><?php _e( 'Animation when hovering over buttons', 'webworq-social-share' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Show Labels', 'webworq-social-share' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="webworq_ss_settings[show_labels]" value="1"
                                        <?php checked( ! empty( $settings['show_labels'] ) ); ?>>
                                    <?php _e( 'Show platform name next to icon', 'webworq-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Heading Text', 'webworq-social-share' ); ?></th>
                            <td>
                                <input type="text" name="webworq_ss_settings[share_heading]" class="regular-text"
                                       value="<?php echo esc_attr( isset( $settings['share_heading'] ) ? $settings['share_heading'] : '' ); ?>"
                                       placeholder="<?php _e( 'e.g. Share this post', 'webworq-social-share' ); ?>">
                                <p class="description"><?php _e( 'Optional heading above the buttons. Leave blank for no heading.', 'webworq-social-share' ); ?></p>
                            </td>
                        </tr>
                    </table>

                    <hr style="margin:30px 0;">

                    <!-- SECTION 3: COLOR THEME -->
                    <h3><?php _e( 'Color Theme', 'webworq-social-share' ); ?></h3>

                    <p class="description" style="margin-bottom:16px;"><?php _e( 'Choose a preset color scheme for your buttons.', 'webworq-social-share' ); ?></p>

                    <?php $color_preset = isset( $settings['color_preset'] ) ? $settings['color_preset'] : 'brand'; ?>
                    <div class="webworq-ss-preset-grid">
                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'brand' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="brand" <?php checked( $color_preset, 'brand' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <div style="width:24px; height:24px; background:#0A66C2; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#000; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#0085FF; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#1877F2; border-radius:50%;"></div>
                                </div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Brand Colors', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'mono-dark' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="mono-dark" <?php checked( $color_preset, 'mono-dark' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="display:flex; gap:6px;">
                                    <div style="width:24px; height:24px; background:#333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; background:#333; border-radius:50%;"></div>
                                </div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Mono Dark', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'mono-light' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="mono-light" <?php checked( $color_preset, 'mono-light' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="display:flex; gap:6px;">
                                    <div style="width:24px; height:24px; background:#e0e0e0; border-radius:50%; border:1px solid #ccc;"></div>
                                    <div style="width:24px; height:24px; background:#e0e0e0; border-radius:50%; border:1px solid #ccc;"></div>
                                    <div style="width:24px; height:24px; background:#e0e0e0; border-radius:50%; border:1px solid #ccc;"></div>
                                </div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Mono Light', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'outline' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="outline" <?php checked( $color_preset, 'outline' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="display:flex; gap:6px;">
                                    <div style="width:24px; height:24px; border:2px solid #333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; border:2px solid #333; border-radius:50%;"></div>
                                    <div style="width:24px; height:24px; border:2px solid #333; border-radius:50%;"></div>
                                </div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Outline', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'minimal' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="minimal" <?php checked( $color_preset, 'minimal' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="display:flex; gap:8px;">
                                    <div style="width:16px; height:16px; background:#0A66C2;"></div>
                                    <div style="width:16px; height:16px; background:#000;"></div>
                                    <div style="width:16px; height:16px; background:#0085FF;"></div>
                                </div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Minimal', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'glass' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="glass" <?php checked( $color_preset, 'glass' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="width:60px; height:30px; background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); border-radius:6px; backdrop-filter:blur(10px);"></div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Glass', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'gradient' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="gradient" <?php checked( $color_preset, 'gradient' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <div style="width:60px; height:30px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:6px;"></div>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Gradient', 'webworq-social-share' ); ?></span>
                        </label>

                        <label class="webworq-ss-preset-card <?php echo $color_preset === 'custom' ? 'webworq-ss-preset-active' : ''; ?>">
                            <input type="radio" name="webworq_ss_settings[color_preset]" value="custom" <?php checked( $color_preset, 'custom' ); ?>>
                            <div class="webworq-ss-preset-preview">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 2v20M2 12h20"/>
                                </svg>
                            </div>
                            <span class="webworq-ss-preset-label"><?php _e( 'Custom', 'webworq-social-share' ); ?></span>
                        </label>
                    </div>

                    <!-- Per-variant color pickers (only shown when preset = 'custom') -->
                    <div class="webworq-ss-color-subtabs-wrapper" style="<?php echo $color_preset === 'custom' ? '' : 'display:none;'; ?> margin-top:24px; padding-top:24px; border-top:1px solid #ddd;">
                        <h4><?php _e( 'Custom Colors', 'webworq-social-share' ); ?></h4>

                        <div class="webworq-ss-color-subtabs">
                            <button type="button" class="webworq-ss-color-subtab webworq-ss-color-subtab-inline active" data-variant="inline">
                                <?php _e( 'Inline', 'webworq-social-share' ); ?>
                            </button>
                            <button type="button" class="webworq-ss-color-subtab webworq-ss-color-subtab-collapsible" data-variant="collapsible">
                                <?php _e( 'Collapsible', 'webworq-social-share' ); ?>
                            </button>
                            <button type="button" class="webworq-ss-color-subtab webworq-ss-color-subtab-floating" data-variant="floating">
                                <?php _e( 'Floating', 'webworq-social-share' ); ?>
                            </button>
                        </div>

                        <!-- Inline variant -->
                        <div class="webworq-ss-color-panel webworq-ss-color-panel-inline" style="display:block;">
                            <table class="form-table">
                                <tr>
                                    <th><?php _e( 'Button Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][inline][bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['bg'] ?? '#333333' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Icon/Text Color', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][inline][text]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['text'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Hover Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][inline][hover_bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['hover_bg'] ?? '#555555' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Hover Text Color', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][inline][hover_text]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['hover_text'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Border Color', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][inline][border]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['inline']['border'] ?? '' ); ?>">
                                        <p class="description"><?php _e( 'Optional. Leave empty for no border.', 'webworq-social-share' ); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Collapsible variant -->
                        <div class="webworq-ss-color-panel webworq-ss-color-panel-collapsible" style="display:none;">
                            <table class="form-table">
                                <tr>
                                    <th><?php _e( 'Trigger Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][collapsible][trigger_bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_bg'] ?? '#333333' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Trigger Text Color', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][collapsible][trigger_text]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_text'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Trigger Icon Color', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][collapsible][trigger_icon]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_icon'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Trigger Hover Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][collapsible][trigger_hover_bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['trigger_hover_bg'] ?? '#555555' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Panel Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][collapsible][panel_bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['collapsible']['panel_bg'] ?? '#f9f9f9' ); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Floating variant -->
                        <div class="webworq-ss-color-panel webworq-ss-color-panel-floating" style="display:none;">
                            <table class="form-table">
                                <tr>
                                    <th><?php _e( 'Trigger Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][floating][trigger_bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['floating']['trigger_bg'] ?? '#333333' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Trigger Icon Color', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][floating][trigger_icon]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['floating']['trigger_icon'] ?? '#ffffff' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Trigger Hover Background', 'webworq-social-share' ); ?></th>
                                    <td>
                                        <input type="text" name="webworq_ss_settings[colors][floating][trigger_hover_bg]" class="webworq-ss-color-field"
                                               value="<?php echo esc_attr( $settings['colors']['floating']['trigger_hover_bg'] ?? '#555555' ); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr style="margin:30px 0;">

                    <h3><?php _e( 'Preview', 'webworq-social-share' ); ?></h3>
                    <div id="webworq-ss-preview" class="webworq-ss-preview-box">
                        <p class="description"><?php _e( 'Save settings to see an updated preview, or visit any post on your site.', 'webworq-social-share' ); ?></p>
                    </div>
                </div>

                <?php elseif ( $active_tab === 'placement' ) : ?>
                <div class="webworq-ss-section">
                    <h2><?php _e( 'In-Content Buttons', 'webworq-social-share' ); ?></h2>
                    <p class="description"><?php _e( 'These settings control the inline/collapsible share buttons placed within your content.', 'webworq-social-share' ); ?></p>

                    <table class="form-table">
                        <tr>
                            <th><?php _e( 'Position', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php $ap = isset( $settings['auto_placement'] ) ? $settings['auto_placement'] : 'after'; ?>
                                <select name="webworq_ss_settings[auto_placement]" id="webworq-ss-auto-placement">
                                    <option value="after" <?php selected( $ap, 'after' ); ?>><?php _e( 'After content', 'webworq-social-share' ); ?></option>
                                    <option value="before" <?php selected( $ap, 'before' ); ?>><?php _e( 'Before content', 'webworq-social-share' ); ?></option>
                                    <option value="both" <?php selected( $ap, 'both' ); ?>><?php _e( 'Before & after content', 'webworq-social-share' ); ?></option>
                                    <option value="none" <?php selected( $ap, 'none' ); ?>><?php _e( 'Manual only (shortcode)', 'webworq-social-share' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="webworq-ss-post-types-row" style="<?php echo $ap === 'none' ? 'display:none;' : ''; ?>">
                            <th><?php _e( 'Post Types', 'webworq-social-share' ); ?></th>
                            <td>
                                <input type="hidden" name="webworq_ss_settings[post_types_submitted]" value="1">
                                <?php
                                $post_types = get_post_types( array( 'public' => true ), 'objects' );
                                $active_pts = isset( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );
                                foreach ( $post_types as $pt ) :
                                    if ( $pt->name === 'attachment' ) continue;
                                ?>
                                <label style="display:block; margin-bottom:4px;">
                                    <input type="checkbox" name="webworq_ss_settings[post_types][]"
                                           value="<?php echo esc_attr( $pt->name ); ?>"
                                        <?php checked( in_array( $pt->name, $active_pts ) ); ?>>
                                    <?php echo esc_html( $pt->label ); ?>
                                </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </table>

                    <hr style="margin:30px 0;">

                    <h3><?php _e( 'Shortcode', 'webworq-social-share' ); ?></h3>
                    <p class="description">
                        <?php _e( 'Use the shortcode below anywhere in your content, widgets, or Divi Code Module:', 'webworq-social-share' ); ?>
                    </p>
                    <code class="webworq-ss-shortcode-display">[webworq_share]</code>
                    <p class="description" style="margin-top:8px;">
                        <?php _e( 'Override display mode per shortcode:', 'webworq-social-share' ); ?>
                        <code>[webworq_share mode="collapsible"]</code> &nbsp;
                        <code>[webworq_share mode="inline"]</code>
                    </p>
                    <p class="description" style="margin-top:4px;">
                        <?php _e( 'In PHP templates:', 'webworq-social-share' ); ?>
                        <code>&lt;?php echo do_shortcode('[webworq_share]'); ?&gt;</code>
                    </p>
                    <p class="description" style="margin-top:4px; color:#888;">
                        <?php _e( 'The legacy shortcode [webworq_share] still works for backward compatibility.', 'webworq-social-share' ); ?>
                    </p>
                </div>

                <?php elseif ( $active_tab === 'floating' ) : ?>
                <div class="webworq-ss-section">
                    <h2><?php _e( 'Floating Share Button', 'webworq-social-share' ); ?></h2>
                    <p class="description"><?php _e( 'A sticky share button fixed to the screen corner. Works independently from the in-content buttons — you can enable both at the same time.', 'webworq-social-share' ); ?></p>

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
                            <th><?php _e( 'Enable', 'webworq-social-share' ); ?></th>
                            <td>
                                <label class="webworq-ss-toggle-label">
                                    <input type="checkbox" name="webworq_ss_settings[floating_enabled]" value="1" id="webworq-ss-floating-enabled"
                                        <?php checked( $float_enabled ); ?>>
                                    <?php _e( 'Show floating share button', 'webworq-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr class="webworq-ss-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php _e( 'Screen Position', 'webworq-social-share' ); ?></th>
                            <td>
                                <div class="webworq-ss-position-picker">
                                    <div class="webworq-ss-position-grid">
                                        <?php
                                        $positions = array(
                                            'top-left'     => __( 'Top Left', 'webworq-social-share' ),
                                            'top-right'    => __( 'Top Right', 'webworq-social-share' ),
                                            'middle-left'  => __( 'Middle Left', 'webworq-social-share' ),
                                            'middle-right' => __( 'Middle Right', 'webworq-social-share' ),
                                            'bottom-left'  => __( 'Bottom Left', 'webworq-social-share' ),
                                            'bottom-right' => __( 'Bottom Right', 'webworq-social-share' ),
                                        );
                                        foreach ( $positions as $pos_val => $pos_label ) :
                                        ?>
                                        <label class="webworq-ss-position-cell webworq-ss-position-<?php echo esc_attr( $pos_val ); ?> <?php echo $float_position === $pos_val ? 'webworq-ss-position-active' : ''; ?>">
                                            <input type="radio" name="webworq_ss_settings[floating_position]" value="<?php echo esc_attr( $pos_val ); ?>"
                                                <?php checked( $float_position, $pos_val ); ?>>
                                            <span class="webworq-ss-position-dot"></span>
                                            <span class="webworq-ss-position-label"><?php echo esc_html( $pos_label ); ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="webworq-ss-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php _e( 'Show On', 'webworq-social-share' ); ?></th>
                            <td>
                                <input type="hidden" name="webworq_ss_settings[floating_post_types_submitted]" value="1">
                                <?php
                                foreach ( $post_types as $pt ) :
                                    if ( $pt->name === 'attachment' ) continue;
                                ?>
                                <label style="display:block; margin-bottom:4px;">
                                    <input type="checkbox" name="webworq_ss_settings[floating_post_types][]"
                                           value="<?php echo esc_attr( $pt->name ); ?>"
                                        <?php checked( in_array( $pt->name, $float_pts ) ); ?>>
                                    <?php echo esc_html( $pt->label ); ?>
                                </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <tr class="webworq-ss-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php _e( 'FAB Size', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php
                                $fab_sizes = array(
                                    'small' => 'Small (44px)',
                                    'medium' => 'Medium (56px)',
                                    'large' => 'Large (68px)'
                                );
                                ?>
                                <select name="webworq_ss_settings[fab_size]">
                                    <?php foreach ( $fab_sizes as $val => $label ) : ?>
                                    <option value="<?php echo $val; ?>" <?php selected( $fab_size, $val ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="webworq-ss-floating-settings" style="<?php echo ! $float_enabled ? 'display:none;' : ''; ?>">
                            <th><?php _e( 'Show on Mobile', 'webworq-social-share' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="webworq_ss_settings[fab_mobile]" value="1"
                                        <?php checked( $fab_mobile ); ?>>
                                    <?php _e( 'Display on mobile devices', 'webworq-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php elseif ( $active_tab === 'metadata' ) : ?>
                <div class="webworq-ss-section">
                    <h2><?php _e( 'Open Graph & Twitter Card Metadata', 'webworq-social-share' ); ?></h2>
                    <p class="description"><?php _e( 'These meta tags ensure posts show rich previews when shared. If you already use an SEO plugin (Yoast, RankMath, etc.) that handles OG tags, disable this to avoid duplicates.', 'webworq-social-share' ); ?></p>

                    <table class="form-table">
                        <tr>
                            <th><?php _e( 'Inject Meta Tags', 'webworq-social-share' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="webworq_ss_settings[inject_og]" value="1"
                                        <?php checked( ! empty( $settings['inject_og'] ) ); ?>>
                                    <?php _e( 'Add Open Graph & Twitter Card meta tags to &lt;head&gt;', 'webworq-social-share' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Fallback Image', 'webworq-social-share' ); ?></th>
                            <td>
                                <?php $default_img = isset( $settings['default_image'] ) ? $settings['default_image'] : ''; ?>
                                <div class="webworq-ss-image-upload">
                                    <input type="text" name="webworq_ss_settings[default_image]" id="webworq-ss-default-image"
                                           class="regular-text" value="<?php echo esc_url( $default_img ); ?>"
                                           placeholder="https://">
                                    <button type="button" class="button" id="webworq-ss-upload-image">
                                        <?php _e( 'Choose Image', 'webworq-social-share' ); ?>
                                    </button>
                                </div>
                                <p class="description"><?php _e( 'Used when a post has no featured image. Recommended: 1200x630px.', 'webworq-social-share' ); ?></p>
                                <?php if ( $default_img ) : ?>
                                <img src="<?php echo esc_url( $default_img ); ?>" style="max-width:300px;margin-top:8px;border-radius:4px;">
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'X/Twitter Handle', 'webworq-social-share' ); ?></th>
                            <td>
                                <input type="text" name="webworq_ss_settings[twitter_handle]" class="regular-text"
                                       value="<?php echo esc_attr( isset( $settings['twitter_handle'] ) ? $settings['twitter_handle'] : '' ); ?>"
                                       placeholder="username">
                                <p class="description"><?php _e( 'Without the @ symbol. Used for twitter:site meta tag.', 'webworq-social-share' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <?php
                // Preserve settings from other tabs as hidden fields
                $this->preserve_other_tabs( $settings, $active_tab, $platforms );
                ?>

                <?php submit_button( __( 'Save Settings', 'webworq-social-share' ) ); ?>
            </form>

            <div class="webworq-ss-footer">
                <p>
                    <?php printf(
                        __( 'Built with %s by %s', 'webworq-social-share' ),
                        '<span style="color:#e25555;">&hearts;</span>',
                        '<a href="https://webworq.dk" target="_blank" class="webworq-ss-footer-brand"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1050 560" class="webworq-ss-footer-logo"><g transform="translate(-66, 0)"><path d="M482.028,127.552c68.678-3.438,133.791,20.53,184.477,66.29l305.786,305.871c-11.529,6.257-23.924,11.151-36.506,14.931-89.755,26.968-184.864,3.869-251.798-61.098L385.201,154.676c-.615-2.799,13.565-8.158,16.384-9.324,25.113-10.379,53.294-16.441,80.443-17.8h0Z"/><path d="M562.559,383.751c37.056,37.172,74.009,74.57,110.897,111.928,2.256,2.285,5.104,1.704,4.126,5.766-10.939,3.084-21.029,8.499-31.842,12.036-88.447,28.927-186.958,6.104-253.544-58.195L92.748,155.923l-1.054-4.09c96.742-44.785,204.585-27.196,282.992,43.779,33.721,30.524,64.337,65.524,96.438,97.709,30.263,30.342,61.198,60.099,91.435,90.431h0Z"/><path d="M1040.143,130.723l-1.741,3.51c-47.331,46.61-93.494,94.433-140.972,140.891-22.606,22.121-40.946,45.234-74.833,21.098l-167.233-166.664c23.573-1.811,47.174-.719,70.806-1.133,81.628-1.429,163.864-2.228,245.511,0,22.879.624,45.593.686,68.462,2.297h0Z" fill="#f60"/></g></svg> Webworq</a>'
                    ); ?>
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
                echo '<input type="hidden" name="webworq_ss_settings[platforms][]" value="' . esc_attr( $slug ) . '">';
            }
            $order = isset( $settings['platform_order'] ) ? $settings['platform_order'] : '';
            echo '<input type="hidden" name="webworq_ss_settings[platform_order]" value="' . esc_attr( $order ) . '">';
        }

        if ( $active_tab !== 'styling' ) {
            // Legacy fields
            $legacy_fields = array( 'display_mode', 'style', 'size', 'color_mode', 'custom_color', 'custom_hover', 'share_heading' );
            foreach ( $legacy_fields as $f ) {
                if ( isset( $settings[ $f ] ) ) {
                    echo '<input type="hidden" name="webworq_ss_settings[' . $f . ']" value="' . esc_attr( $settings[ $f ] ) . '">';
                }
            }
            if ( ! empty( $settings['show_labels'] ) ) {
                echo '<input type="hidden" name="webworq_ss_settings[show_labels]" value="1">';
            }
            // New v3.0 styling fields
            $new_fields = array( 'button_gap', 'border_radius_type', 'border_radius_custom', 'shadow_preset', 'hover_animation', 'color_preset' );
            foreach ( $new_fields as $f ) {
                if ( isset( $settings[ $f ] ) ) {
                    echo '<input type="hidden" name="webworq_ss_settings[' . $f . ']" value="' . esc_attr( $settings[ $f ] ) . '">';
                }
            }
            // Colors array
            if ( isset( $settings['colors'] ) && is_array( $settings['colors'] ) ) {
                foreach ( $settings['colors'] as $variant => $variant_colors ) {
                    foreach ( $variant_colors as $color_key => $color_val ) {
                        echo '<input type="hidden" name="webworq_ss_settings[colors][' . esc_attr( $variant ) . '][' . esc_attr( $color_key ) . ']" value="' . esc_attr( $color_val ) . '">';
                    }
                }
            }
        }

        if ( $active_tab !== 'placement' ) {
            // In-content placement
            $ap = isset( $settings['auto_placement'] ) ? $settings['auto_placement'] : 'after';
            echo '<input type="hidden" name="webworq_ss_settings[auto_placement]" value="' . esc_attr( $ap ) . '">';
            $pts = isset( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );
            foreach ( $pts as $pt ) {
                echo '<input type="hidden" name="webworq_ss_settings[post_types][]" value="' . esc_attr( $pt ) . '">';
            }
        }

        if ( $active_tab !== 'floating' ) {
            // Floating settings
            if ( ! empty( $settings['floating_enabled'] ) ) {
                echo '<input type="hidden" name="webworq_ss_settings[floating_enabled]" value="1">';
            }
            $fp = isset( $settings['floating_position'] ) ? $settings['floating_position'] : 'bottom-right';
            echo '<input type="hidden" name="webworq_ss_settings[floating_position]" value="' . esc_attr( $fp ) . '">';
            $fpts = isset( $settings['floating_post_types'] ) ? $settings['floating_post_types'] : array( 'post' );
            foreach ( $fpts as $fpt ) {
                echo '<input type="hidden" name="webworq_ss_settings[floating_post_types][]" value="' . esc_attr( $fpt ) . '">';
            }
            // FAB extras (editable on Floating tab)
            if ( isset( $settings['fab_size'] ) ) {
                echo '<input type="hidden" name="webworq_ss_settings[fab_size]" value="' . esc_attr( $settings['fab_size'] ) . '">';
            }
            if ( ! empty( $settings['fab_mobile'] ) ) {
                echo '<input type="hidden" name="webworq_ss_settings[fab_mobile]" value="1">';
            }
        }

        if ( $active_tab !== 'metadata' ) {
            if ( ! empty( $settings['inject_og'] ) ) {
                echo '<input type="hidden" name="webworq_ss_settings[inject_og]" value="1">';
            }
            $meta_fields = array( 'default_image', 'twitter_handle' );
            foreach ( $meta_fields as $f ) {
                if ( isset( $settings[ $f ] ) ) {
                    echo '<input type="hidden" name="webworq_ss_settings[' . $f . ']" value="' . esc_attr( $settings[ $f ] ) . '">';
                }
            }
        }
    }
}

new Webworq_SS_Admin();
