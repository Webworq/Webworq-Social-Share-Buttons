<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Injects Open Graph and Twitter Card meta tags for rich link previews.
 *
 * These tags are used by LinkedIn, X/Twitter, Bluesky, Facebook, and
 * most other platforms when generating link previews.
 */
class Webworq_SS_Meta_Tags {

    public function __construct() {
        add_action( 'wp_head', array( $this, 'output_meta_tags' ), 1 );
    }

    public function output_meta_tags() {
        if ( ! Webworq_Social_Share::get_setting( 'inject_og', true ) ) return;
        if ( ! is_singular() ) return;

        // Check if common SEO plugins are active - skip if they handle OG
        if ( $this->seo_plugin_active() ) return;

        $post_id     = get_the_ID();
        $title        = get_the_title( $post_id );
        $url          = get_permalink( $post_id );
        $site_name    = get_bloginfo( 'name' );
        $description  = $this->get_description( $post_id );
        $image        = $this->get_image( $post_id );
        $type         = ( is_front_page() || is_home() ) ? 'website' : 'article';
        $locale       = get_locale();

        echo "\n<!-- Webworq Social Share - Open Graph & Twitter Card -->\n";

        // Open Graph
        $this->meta_tag( 'og:type', $type );
        $this->meta_tag( 'og:title', $title );
        $this->meta_tag( 'og:description', $description );
        $this->meta_tag( 'og:url', $url );
        $this->meta_tag( 'og:site_name', $site_name );
        $this->meta_tag( 'og:locale', $locale );

        if ( $image ) {
            $this->meta_tag( 'og:image', $image );
            $this->meta_tag( 'og:image:width', '1200' );
            $this->meta_tag( 'og:image:height', '630' );
        }

        if ( $type === 'article' ) {
            $this->meta_tag( 'article:published_time', get_the_date( 'c', $post_id ) );
            $this->meta_tag( 'article:modified_time', get_the_modified_date( 'c', $post_id ) );

            $author = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
            if ( $author ) {
                $this->meta_tag( 'article:author', $author );
            }
        }

        // Twitter Card
        echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";

        if ( $image ) {
            echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
        }

        $handle = Webworq_Social_Share::get_setting( 'twitter_handle', '' );
        if ( $handle ) {
            echo '<meta name="twitter:site" content="@' . esc_attr( $handle ) . '" />' . "\n";
        }

        echo "<!-- / Webworq Social Share -->\n\n";
    }

    /**
     * Get the post description for meta tags.
     */
    private function get_description( $post_id ) {
        if ( has_excerpt( $post_id ) ) {
            return wp_strip_all_tags( get_the_excerpt( $post_id ) );
        }

        $post = get_post( $post_id );
        $content = $post ? $post->post_content : '';
        $content = wp_strip_all_tags( strip_shortcodes( $content ) );
        return wp_trim_words( $content, 30, '...' );
    }

    /**
     * Get the best image for the post.
     */
    private function get_image( $post_id ) {
        // First: featured image
        $thumb = get_the_post_thumbnail_url( $post_id, 'large' );
        if ( $thumb ) return $thumb;

        // Second: first image in content
        $post = get_post( $post_id );
        if ( $post ) {
            preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $post->post_content, $matches );
            if ( ! empty( $matches[1] ) ) {
                return $matches[1];
            }
        }

        // Third: fallback image from settings
        $default = Webworq_Social_Share::get_setting( 'default_image', '' );
        if ( $default ) return $default;

        // Fourth: site icon
        $site_icon = get_site_icon_url( 512 );
        if ( $site_icon ) return $site_icon;

        return '';
    }

    /**
     * Output a single OG meta tag.
     */
    private function meta_tag( $property, $content ) {
        if ( ! $content ) return;
        echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
    }

    /**
     * Check if an SEO plugin is handling OG tags.
     */
    private function seo_plugin_active() {
        // Yoast SEO
        if ( defined( 'WPSEO_VERSION' ) ) return true;
        // RankMath
        if ( class_exists( 'RankMath' ) ) return true;
        // All in One SEO
        if ( class_exists( 'AIOSEO\\Plugin\\AIOSEO' ) || defined( 'AIOSEO_VERSION' ) ) return true;
        // SEOPress
        if ( defined( 'SEOPRESS_VERSION' ) ) return true;
        // The SEO Framework
        if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) return true;

        /**
         * Filter to indicate an SEO plugin handles OG tags.
         */
        return apply_filters( 'webworq_ss_seo_plugin_active', false );
    }
}

new Webworq_SS_Meta_Tags();
