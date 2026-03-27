<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Webworq_SS_Frontend {

    private $floating_rendered = false;

    public function __construct() {
        add_shortcode( 'ripple_share', array( $this, 'shortcode' ) );
        add_shortcode( 'webworq_share', array( $this, 'shortcode' ) ); // backward compat
        add_filter( 'the_content', array( $this, 'auto_insert' ), 99 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_floating' ), 50 );
        add_action( 'wp_footer', array( $this, 'footer_scripts' ), 99 );
    }

    public function enqueue_assets() {
        $is_floating = ! empty( Webworq_Social_Share::get_setting( 'floating_enabled' ) ) && $this->should_display_floating();
        if ( ! $this->should_display() && ! $this->has_shortcode_in_content() && ! $is_floating ) {
            return;
        }

        wp_enqueue_style( 'webworq-ss-frontend', WEBWORQ_SS_PLUGIN_URL . 'assets/css/frontend.css', array(), WEBWORQ_SS_VERSION );

        $settings = Webworq_Social_Share::get_settings();
        $custom_css = $this->build_dynamic_css( $settings );
        wp_add_inline_style( 'webworq-ss-frontend', $custom_css );
    }

    private function should_display() {
        if ( ! is_singular() ) return false;
        $post_types = Webworq_Social_Share::get_setting( 'post_types', array( 'post' ) );
        return in_array( get_post_type(), $post_types );
    }

    private function has_shortcode_in_content() {
        global $post;
        return $post && ( has_shortcode( $post->post_content, 'ripple_share' ) || has_shortcode( $post->post_content, 'webworq_share' ) );
    }

    public function auto_insert( $content ) {
        if ( ! $this->should_display() ) return $content;
        if ( $this->has_shortcode_in_content() ) return $content;

        $placement = Webworq_Social_Share::get_setting( 'auto_placement', 'after' );

        // Manual mode doesn't insert into content
        if ( $placement === 'none' ) return $content;

        $buttons = $this->render_buttons();

        switch ( $placement ) {
            case 'before':
                return $buttons . $content;
            case 'both':
                return $buttons . $content . $buttons;
            case 'after':
            default:
                return $content . $buttons;
        }
    }

    public function shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'platforms' => '',
            'style'     => '',
            'size'      => '',
            'mode'      => '',  // override display mode per shortcode
        ), $atts, 'webworq_share' );

        return $this->render_buttons( $atts );
    }

    /**
     * Check if floating button should display on current page.
     */
    private function should_display_floating() {
        if ( ! is_singular() ) return false;
        $float_post_types = Webworq_Social_Share::get_setting( 'floating_post_types', array( 'post' ) );
        return in_array( get_post_type(), $float_post_types );
    }

    /**
     * Render floating button in footer when floating is enabled.
     */
    public function render_floating() {
        if ( $this->floating_rendered ) return;

        $float_enabled = Webworq_Social_Share::get_setting( 'floating_enabled', false );
        if ( empty( $float_enabled ) ) return;
        if ( ! $this->should_display_floating() ) return;

        $this->floating_rendered = true;
        echo $this->render_buttons( array( 'mode' => 'floating' ) );
    }

    /**
     * Get the prepared platforms list (with ordering).
     */
    private function get_platforms( $overrides = array() ) {
        $settings = Webworq_Social_Share::get_settings();

        if ( ! empty( $overrides['platforms'] ) ) {
            $platform_slugs = array_map( 'trim', explode( ',', $overrides['platforms'] ) );
            $all_platforms = Webworq_SS_Platforms::get_all();
            $platforms = array();
            foreach ( $platform_slugs as $slug ) {
                if ( isset( $all_platforms[ $slug ] ) ) {
                    $platforms[ $slug ] = $all_platforms[ $slug ];
                }
            }
        } else {
            $platforms = Webworq_SS_Platforms::get_enabled();
        }

        if ( empty( $platforms ) ) return array();

        // Respect platform order
        if ( ! empty( $settings['platform_order'] ) ) {
            $order = explode( ',', $settings['platform_order'] );
            $ordered = array();
            foreach ( $order as $slug ) {
                if ( isset( $platforms[ $slug ] ) ) {
                    $ordered[ $slug ] = $platforms[ $slug ];
                }
            }
            foreach ( $platforms as $slug => $p ) {
                if ( ! isset( $ordered[ $slug ] ) ) {
                    $ordered[ $slug ] = $p;
                }
            }
            $platforms = $ordered;
        }

        return $platforms;
    }

    /**
     * Render a single platform button.
     */
    private function render_single_button( $slug, $platform, $post_id, $color_preset, $show_labels ) {
        $share_url  = Webworq_SS_Platforms::build_share_url( $platform, $post_id );
        $is_copy    = ( $slug === 'copy_link' );
        $is_email   = ( $slug === 'email' );

        $style_attr = '';
        if ( $color_preset === 'brand' ) {
            $style_attr = '--webworq-ss-btn-color:' . $platform['color'] . ';';
        }

        $html = '<a class="webworq-ss-btn webworq-ss-btn-' . esc_attr( $slug ) . '"';
        $html .= ' href="' . ( $is_copy ? '#' : esc_url( $share_url ) ) . '"';

        if ( $is_copy ) {
            $html .= ' data-copy-url="' . esc_url( get_permalink( $post_id ) ) . '"';
        }
        if ( ! $is_copy && ! $is_email ) {
            $html .= ' target="_blank" rel="noopener noreferrer"';
        }
        if ( $style_attr ) {
            $html .= ' style="' . esc_attr( $style_attr ) . '"';
        }

        $label_text = $is_copy ? $platform['label'] : sprintf( __( 'Share on %s', 'webworq-social-share' ), $platform['label'] );
        $html .= ' title="' . esc_attr( $label_text ) . '"';
        $html .= ' aria-label="' . esc_attr( $label_text ) . '">';
        $html .= '<span class="webworq-ss-icon">' . $platform['icon'] . '</span>';

        if ( $show_labels ) {
            $html .= '<span class="webworq-ss-label">' . esc_html( $platform['label'] ) . '</span>';
        }

        $html .= '</a>';
        return $html;
    }

    /**
     * Render the sharing buttons HTML.
     */
    public function render_buttons( $overrides = array() ) {
        $settings   = Webworq_Social_Share::get_settings();
        $platforms  = $this->get_platforms( $overrides );

        if ( empty( $platforms ) ) return '';

        $style        = ! empty( $overrides['style'] ) ? $overrides['style'] : ( isset( $settings['style'] ) ? $settings['style'] : 'circle' );
        $size         = ! empty( $overrides['size'] ) ? $overrides['size'] : ( isset( $settings['size'] ) ? $settings['size'] : 'medium' );
        $color_preset = isset( $settings['color_preset'] ) ? $settings['color_preset'] : 'brand';
        $show_labels  = ! empty( $settings['show_labels'] );
        $heading      = isset( $settings['share_heading'] ) ? $settings['share_heading'] : '';
        // Mode: shortcode can override (inline/collapsible/floating), otherwise use display_mode setting
        $mode = ! empty( $overrides['mode'] ) ? $overrides['mode'] : ( isset( $settings['display_mode'] ) ? $settings['display_mode'] : 'inline' );

        $post_id = get_the_ID();

        switch ( $mode ) {
            case 'collapsible':
                return $this->render_collapsible( $platforms, $post_id, $style, $size, $color_preset, $show_labels, $heading );
            case 'floating':
                return $this->render_floating_fab( $platforms, $post_id, $style, $size, $color_preset );
            default:
                return $this->render_inline( $platforms, $post_id, $style, $size, $color_preset, $show_labels, $heading );
        }
    }

    /**
     * MODE: Inline - all buttons visible.
     */
    private function render_inline( $platforms, $post_id, $style, $size, $color_preset, $show_labels, $heading ) {
        $classes = array( 'webworq-ss-share-buttons', 'webworq-ss-mode-inline', 'webworq-ss-style-' . $style, 'webworq-ss-size-' . $size, 'webworq-ss-preset-' . $color_preset );
        if ( $show_labels ) $classes[] = 'webworq-ss-with-labels';

        $settings = Webworq_Social_Share::get_settings();
        $hover = isset( $settings['hover_animation'] ) ? $settings['hover_animation'] : 'lift';
        $classes[] = 'webworq-ss-hover-' . $hover;

        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
            <?php if ( $heading ) : ?>
            <span class="webworq-ss-heading"><?php echo esc_html( $heading ); ?></span>
            <?php endif; ?>
            <div class="webworq-ss-buttons-wrap">
                <?php foreach ( $platforms as $slug => $platform ) {
                    echo $this->render_single_button( $slug, $platform, $post_id, $color_preset, $show_labels );
                } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * MODE: Collapsible - share trigger that expands to show options.
     */
    private function render_collapsible( $platforms, $post_id, $style, $size, $color_preset, $show_labels, $heading ) {
        $classes = array( 'webworq-ss-share-buttons', 'webworq-ss-mode-collapsible', 'webworq-ss-style-' . $style, 'webworq-ss-size-' . $size, 'webworq-ss-preset-' . $color_preset );

        $settings = Webworq_Social_Share::get_settings();
        $hover = isset( $settings['hover_animation'] ) ? $settings['hover_animation'] : 'lift';
        $classes[] = 'webworq-ss-hover-' . $hover;

        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
            <button type="button" class="webworq-ss-trigger" aria-expanded="false" aria-label="<?php esc_attr_e( 'Share this post', 'webworq-social-share' ); ?>">
                <span class="webworq-ss-trigger-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                </span>
                <span class="webworq-ss-trigger-label"><?php echo esc_html( $heading ? $heading : __( 'Share', 'webworq-social-share' ) ); ?></span>
                <span class="webworq-ss-trigger-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>
                </span>
            </button>
            <div class="webworq-ss-collapsible-panel" aria-hidden="true">
                <div class="webworq-ss-buttons-wrap">
                    <?php foreach ( $platforms as $slug => $platform ) {
                        echo $this->render_single_button( $slug, $platform, $post_id, $color_preset, true );
                    } ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * MODE: Floating FAB - fixed position button that fans out.
     */
    private function render_floating_fab( $platforms, $post_id, $style, $size, $color_preset ) {
        $settings = Webworq_Social_Share::get_settings();
        $position = isset( $settings['floating_position'] ) ? $settings['floating_position'] : 'bottom-right';
        $fab_mobile = isset( $settings['fab_mobile'] ) ? $settings['fab_mobile'] : true;
        $hover = isset( $settings['hover_animation'] ) ? $settings['hover_animation'] : 'lift';

        $classes = array( 'webworq-ss-floating-wrap', 'webworq-ss-float-' . $position, 'webworq-ss-style-' . $style, 'webworq-ss-size-' . $size, 'webworq-ss-preset-' . $color_preset, 'webworq-ss-hover-' . $hover );

        // Add mobile hide class if disabled
        if ( ! $fab_mobile ) {
            $classes[] = 'webworq-ss-fab-hide-mobile';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-label="<?php esc_attr_e( 'Share', 'webworq-social-share' ); ?>">
            <div class="webworq-ss-floating-options" aria-hidden="true">
                <?php
                $i = 0;
                foreach ( $platforms as $slug => $platform ) {
                    $share_url = Webworq_SS_Platforms::build_share_url( $platform, $post_id );
                    $is_copy   = ( $slug === 'copy_link' );
                    $is_email  = ( $slug === 'email' );

                    $style_attr = '';
                    if ( $color_preset === 'brand' ) {
                        $style_attr = '--webworq-ss-btn-color:' . $platform['color'] . ';';
                    }
                    $style_attr .= '--webworq-ss-fab-index:' . $i . ';';

                    $label = $is_copy ? $platform['label'] : sprintf( __( 'Share on %s', 'webworq-social-share' ), $platform['label'] );
                    ?>
                    <a class="webworq-ss-fab-option webworq-ss-btn-<?php echo esc_attr( $slug ); ?>"
                       href="<?php echo $is_copy ? '#' : esc_url( $share_url ); ?>"
                       <?php echo $is_copy ? 'data-copy-url="' . esc_url( get_permalink( $post_id ) ) . '"' : ''; ?>
                       <?php echo ( ! $is_copy && ! $is_email ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                       style="<?php echo esc_attr( $style_attr ); ?>"
                       title="<?php echo esc_attr( $label ); ?>"
                       aria-label="<?php echo esc_attr( $label ); ?>">
                        <span class="webworq-ss-icon"><?php echo $platform['icon']; ?></span>
                        <span class="webworq-ss-fab-tooltip"><?php echo esc_html( $platform['label'] ); ?></span>
                    </a>
                    <?php
                    $i++;
                }
                ?>
            </div>
            <button type="button" class="webworq-ss-fab-trigger" aria-expanded="false" aria-label="<?php esc_attr_e( 'Share this post', 'webworq-social-share' ); ?>">
                <span class="webworq-ss-fab-icon-share">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/></svg>
                </span>
                <span class="webworq-ss-fab-icon-close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </span>
            </button>
        </div>
        <div class="webworq-ss-fab-backdrop" aria-hidden="true"></div>
        <?php
        return ob_get_clean();
    }

    private function build_dynamic_css( $settings ) {
        $css = ':root{';

        $size_map = array( 'small' => '32px', 'medium' => '40px', 'large' => '48px' );
        $icon_map = array( 'small' => '16px', 'medium' => '20px', 'large' => '24px' );
        $size = isset( $settings['size'] ) ? $settings['size'] : 'medium';

        $css .= '--webworq-ss-btn-size:' . $size_map[ $size ] . ';';
        $css .= '--webworq-ss-icon-size:' . $icon_map[ $size ] . ';';

        // Button gap
        $gap = isset( $settings['button_gap'] ) ? $settings['button_gap'] : 8;
        $css .= '--webworq-ss-btn-gap:' . $gap . 'px;';

        // Border radius
        if ( isset( $settings['border_radius_type'] ) && $settings['border_radius_type'] === 'custom' ) {
            $radius = isset( $settings['border_radius_custom'] ) ? $settings['border_radius_custom'] : 20;
            $css .= '--webworq-ss-btn-radius:' . $radius . 'px;';
        }

        // Shadow
        $shadow_map = array(
            'none'   => 'none',
            'subtle' => '0 1px 3px rgba(0,0,0,0.1)',
            'medium' => '0 2px 8px rgba(0,0,0,0.15)',
            'bold'   => '0 4px 16px rgba(0,0,0,0.2)',
        );
        $shadow = isset( $settings['shadow_preset'] ) ? $settings['shadow_preset'] : 'none';
        if ( isset( $shadow_map[ $shadow ] ) ) {
            $css .= '--webworq-ss-btn-shadow:' . $shadow_map[ $shadow ] . ';';
        }

        // FAB size
        $fab_sizes = array( 'small' => '44px', 'medium' => '56px', 'large' => '68px' );
        $fab_size = isset( $settings['fab_size'] ) ? $settings['fab_size'] : 'medium';
        $css .= '--webworq-ss-fab-size:' . ( isset( $fab_sizes[ $fab_size ] ) ? $fab_sizes[ $fab_size ] : '56px' ) . ';';

        // Color preset resolution
        $color_preset = isset( $settings['color_preset'] ) ? $settings['color_preset'] : 'brand';

        if ( $color_preset === 'custom' ) {
            $colors = isset( $settings['colors'] ) ? $settings['colors'] : array();
            // Inline
            $css .= '--webworq-ss-inline-bg:' . ( isset( $colors['inline']['bg'] ) ? $colors['inline']['bg'] : '#333' ) . ';';
            $css .= '--webworq-ss-inline-text:' . ( isset( $colors['inline']['text'] ) ? $colors['inline']['text'] : '#fff' ) . ';';
            $css .= '--webworq-ss-inline-hover-bg:' . ( isset( $colors['inline']['hover_bg'] ) ? $colors['inline']['hover_bg'] : '#555' ) . ';';
            $css .= '--webworq-ss-inline-hover-text:' . ( isset( $colors['inline']['hover_text'] ) ? $colors['inline']['hover_text'] : '#fff' ) . ';';
            if ( ! empty( $colors['inline']['border'] ) ) {
                $css .= '--webworq-ss-inline-border:' . $colors['inline']['border'] . ';';
            }
            // Collapsible
            $css .= '--webworq-ss-collapsible-trigger-bg:' . ( isset( $colors['collapsible']['trigger_bg'] ) ? $colors['collapsible']['trigger_bg'] : '#333' ) . ';';
            $css .= '--webworq-ss-collapsible-trigger-text:' . ( isset( $colors['collapsible']['trigger_text'] ) ? $colors['collapsible']['trigger_text'] : '#fff' ) . ';';
            $css .= '--webworq-ss-collapsible-trigger-icon:' . ( isset( $colors['collapsible']['trigger_icon'] ) ? $colors['collapsible']['trigger_icon'] : '#fff' ) . ';';
            $css .= '--webworq-ss-collapsible-trigger-hover:' . ( isset( $colors['collapsible']['trigger_hover_bg'] ) ? $colors['collapsible']['trigger_hover_bg'] : '#555' ) . ';';
            $css .= '--webworq-ss-collapsible-panel-bg:' . ( isset( $colors['collapsible']['panel_bg'] ) ? $colors['collapsible']['panel_bg'] : '#f9f9f9' ) . ';';
            // Floating
            $css .= '--webworq-ss-floating-trigger-bg:' . ( isset( $colors['floating']['trigger_bg'] ) ? $colors['floating']['trigger_bg'] : '#333' ) . ';';
            $css .= '--webworq-ss-floating-trigger-icon:' . ( isset( $colors['floating']['trigger_icon'] ) ? $colors['floating']['trigger_icon'] : '#fff' ) . ';';
            $css .= '--webworq-ss-floating-trigger-hover:' . ( isset( $colors['floating']['trigger_hover_bg'] ) ? $colors['floating']['trigger_hover_bg'] : '#555' ) . ';';
        } elseif ( $color_preset === 'mono-dark' ) {
            $css .= '--webworq-ss-btn-color:#333;--webworq-ss-btn-hover:#555;';
        } elseif ( $color_preset === 'mono-light' ) {
            $css .= '--webworq-ss-btn-color:#e0e0e0;--webworq-ss-btn-hover:#ccc;';
        }
        // brand, outline, minimal, glass, gradient — handled by CSS classes

        $css .= '}';

        // Mobile hide for FAB
        if ( ! isset( $settings['fab_mobile'] ) || ! $settings['fab_mobile'] ) {
            $css .= '@media (max-width: 768px) { .webworq-ss-fab-hide-mobile { display: none !important; } }';
        }

        return $css;
    }

    /**
     * All JS for collapsible, floating, and copy-link.
     */
    public function footer_scripts() {
        $is_floating = ! empty( Webworq_Social_Share::get_setting( 'floating_enabled' ) ) && $this->should_display_floating();
        if ( ! $this->should_display() && ! $this->has_shortcode_in_content() && ! $is_floating ) return;
        ?>
        <script>
        (function(){
            /* --- Copy to clipboard --- */
            function copyToClipboard(text) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                }
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                ta.style.top = '-9999px';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                return new Promise(function(resolve, reject) {
                    document.execCommand('copy') ? resolve() : reject();
                    document.body.removeChild(ta);
                });
            }

            function showCopySuccess(btn) {
                var icon = btn.querySelector('.webworq-ss-icon');
                if (icon) {
                    var orig = icon.innerHTML;
                    icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
                    setTimeout(function(){ icon.innerHTML = orig; }, 2000);
                }
            }

            /* --- Collapsible mode --- */
            document.querySelectorAll('.webworq-ss-mode-collapsible .webworq-ss-trigger').forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    var parent = this.closest('.webworq-ss-mode-collapsible');
                    var panel = parent.querySelector('.webworq-ss-collapsible-panel');
                    var isOpen = parent.classList.contains('webworq-ss-open');

                    if (isOpen) {
                        parent.classList.remove('webworq-ss-open');
                        this.setAttribute('aria-expanded', 'false');
                        panel.setAttribute('aria-hidden', 'true');
                    } else {
                        parent.classList.add('webworq-ss-open');
                        this.setAttribute('aria-expanded', 'true');
                        panel.setAttribute('aria-hidden', 'false');
                    }
                });
            });

            /* --- Floating FAB mode --- */
            document.querySelectorAll('.webworq-ss-fab-trigger').forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var wrap = this.closest('.webworq-ss-floating-wrap');
                    var opts = wrap.querySelector('.webworq-ss-floating-options');
                    var backdrop = wrap.nextElementSibling;
                    var isOpen = wrap.classList.contains('webworq-ss-fab-open');

                    if (isOpen) {
                        closeFab(wrap);
                    } else {
                        wrap.classList.add('webworq-ss-fab-open');
                        this.setAttribute('aria-expanded', 'true');
                        opts.setAttribute('aria-hidden', 'false');
                        if (backdrop) backdrop.classList.add('webworq-ss-fab-backdrop-visible');
                    }
                });
            });

            // Close FAB on backdrop tap
            document.querySelectorAll('.webworq-ss-fab-backdrop').forEach(function(bd) {
                bd.addEventListener('click', function() {
                    var wrap = this.previousElementSibling;
                    if (wrap && wrap.classList.contains('webworq-ss-fab-open')) {
                        closeFab(wrap);
                    }
                });
            });

            // Close FAB on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.webworq-ss-floating-wrap.webworq-ss-fab-open').forEach(function(wrap) {
                        closeFab(wrap);
                    });
                    document.querySelectorAll('.webworq-ss-mode-collapsible.webworq-ss-open').forEach(function(c) {
                        c.classList.remove('webworq-ss-open');
                        c.querySelector('.webworq-ss-trigger').setAttribute('aria-expanded', 'false');
                        c.querySelector('.webworq-ss-collapsible-panel').setAttribute('aria-hidden', 'true');
                    });
                }
            });

            function closeFab(wrap) {
                wrap.classList.remove('webworq-ss-fab-open');
                var trigger = wrap.querySelector('.webworq-ss-fab-trigger');
                var opts = wrap.querySelector('.webworq-ss-floating-options');
                var backdrop = wrap.nextElementSibling;
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
                if (opts) opts.setAttribute('aria-hidden', 'true');
                if (backdrop) backdrop.classList.remove('webworq-ss-fab-backdrop-visible');
            }

            // Close FAB after clicking a share option
            document.querySelectorAll('.webworq-ss-fab-option').forEach(function(opt) {
                opt.addEventListener('click', function() {
                    var wrap = this.closest('.webworq-ss-floating-wrap');
                    if (wrap) {
                        setTimeout(function(){ closeFab(wrap); }, 300);
                    }
                });
            });

            /* --- Copy link handler (works in all modes) --- */
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('[data-copy-url]');
                if (!btn) return;
                e.preventDefault();
                var url = btn.getAttribute('data-copy-url');
                if (!url) return;
                copyToClipboard(url).then(function() {
                    showCopySuccess(btn);
                }).catch(function() {});
            });

            /* --- Close collapsible when clicking outside --- */
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.webworq-ss-mode-collapsible')) {
                    document.querySelectorAll('.webworq-ss-mode-collapsible.webworq-ss-open').forEach(function(c) {
                        c.classList.remove('webworq-ss-open');
                        c.querySelector('.webworq-ss-trigger').setAttribute('aria-expanded', 'false');
                        c.querySelector('.webworq-ss-collapsible-panel').setAttribute('aria-hidden', 'true');
                    });
                }
            });
        })();
        </script>
        <?php
    }
}

new Webworq_SS_Frontend();
