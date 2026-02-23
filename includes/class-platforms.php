<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Platform registry.
 *
 * To add a new platform, use the 'ripple_platforms' filter:
 *
 *   add_filter( 'ripple_platforms', function( $platforms ) {
 *       $platforms['threads'] = array(
 *           'label'     => 'Threads',
 *           'color'     => '#000000',
 *           'share_url' => 'https://www.threads.net/intent/post?text={title}+{url}',
 *           'icon'      => '<svg>...</svg>',
 *       );
 *       return $platforms;
 *   });
 *
 * Icons: Automattic social-logos (https://github.com/Automattic/social-logos)
 */
class Ripple_Platforms {

    /**
     * Get all registered platforms.
     *
     * Share URL placeholders:
     *   {url}         - Encoded post URL
     *   {title}       - Encoded post title
     *   {description} - Encoded post excerpt
     *   {image}       - Encoded featured image URL
     */
    public static function get_all() {
        $platforms = array(

            'linkedin' => array(
                'label'     => 'LinkedIn',
                'color'     => '#0A66C2',
                'share_url' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.7 3H4.3A1.3 1.3 0 0 0 3 4.3v15.4A1.3 1.3 0 0 0 4.3 21h15.4a1.3 1.3 0 0 0 1.3-1.3V4.3A1.3 1.3 0 0 0 19.7 3M8.339 18.338H5.667v-8.59h2.672zM7.004 8.574a1.548 1.548 0 1 1-.002-3.096 1.548 1.548 0 0 1 .002 3.096m11.335 9.764H15.67v-4.177c0-.996-.017-2.278-1.387-2.278-1.389 0-1.601 1.086-1.601 2.206v4.249h-2.667v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.779 3.203 4.092v4.711z"/></svg>',
            ),

            'x' => array(
                'label'     => 'X',
                'color'     => '#000000',
                'share_url' => 'https://x.com/intent/post?text={title}&url={url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.982 10.622 20.54 3h-1.554l-5.693 6.618L8.745 3H3.5l6.876 10.007L3.5 21h1.554l6.012-6.989L15.868 21h5.245zm-2.128 2.474-.697-.997-5.543-7.93H8l4.474 6.4.697.996 5.815 8.318h-2.387z"/></svg>',
            ),

            'bluesky' => array(
                'label'     => 'Bluesky',
                'color'     => '#0085FF',
                'share_url' => 'https://bsky.app/intent/compose?text={title}+{url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M21.2 3.3c-.5-.2-1.4-.5-3.6 1C15.4 6 12.9 9.2 12 11c-.9-1.8-3.4-5-5.7-6.7-2.2-1.6-3-1.3-3.6-1S2 4.6 2 5.1s.3 4.7.5 5.4c.7 2.3 3.1 3.1 5.3 2.8-3.3.5-6.2 1.7-2.4 5.9 4.2 4.3 5.7-.9 6.5-3.6.8 2.7 1.7 7.7 6.4 3.6 3.6-3.6 1-5.4-2.3-5.9 2.2.2 4.6-.5 5.3-2.8.4-.7.7-4.8.7-5.4 0-.5-.1-1.5-.8-1.8"/></svg>',
            ),

            'facebook' => array(
                'label'     => 'Facebook',
                'color'     => '#1877F2',
                'share_url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10"/></svg>',
            ),

            'whatsapp' => array(
                'label'     => 'WhatsApp',
                'color'     => '#25D366',
                'share_url' => 'https://api.whatsapp.com/send?text={title}%20{url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="m2.048 22 1.406-5.136a9.9 9.9 0 0 1-1.323-4.955C2.133 6.446 6.579 2 12.042 2a9.85 9.85 0 0 1 7.011 2.906 9.85 9.85 0 0 1 2.9 7.011c-.002 5.464-4.448 9.91-9.91 9.91h-.004a9.9 9.9 0 0 1-4.736-1.206zm5.497-3.172.301.179a8.2 8.2 0 0 0 4.193 1.148h.003c4.54 0 8.235-3.695 8.237-8.237a8.2 8.2 0 0 0-2.41-5.828 8.18 8.18 0 0 0-5.824-2.416c-4.544 0-8.239 3.695-8.241 8.237a8.2 8.2 0 0 0 1.259 4.384l.196.312-.832 3.04zm9.49-4.554c-.062-.103-.227-.165-.475-.289s-1.465-.723-1.692-.806-.392-.124-.557.124-.64.806-.784.971-.289.186-.536.062-1.046-.385-1.991-1.229c-.736-.657-1.233-1.468-1.378-1.715s-.015-.382.109-.505c.111-.111.248-.289.371-.434.124-.145.165-.248.248-.413s.041-.31-.021-.434-.557-1.343-.763-1.839c-.202-.483-.407-.417-.559-.425-.144-.007-.31-.009-.475-.009a.9.9 0 0 0-.66.31c-.226.248-.866.847-.866 2.066s.887 2.396 1.011 2.562 1.746 2.666 4.23 3.739c.591.255 1.052.408 1.412.522.593.189 1.133.162 1.56.098.476-.071 1.465-.599 1.671-1.177.206-.58.206-1.075.145-1.179"/></svg>',
            ),

            'email' => array(
                'label'     => 'Email',
                'color'     => '#EA4335',
                'share_url' => 'mailto:?subject={title}&body={description}%20{url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2m0 4.236-8 4.882-8-4.882V6h16z"/></svg>',
            ),

            'pinterest' => array(
                'label'     => 'Pinterest',
                'color'     => '#E60023',
                'share_url' => 'https://pinterest.com/pin/create/button/?url={url}&media={image}&description={title}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.087-.791-.166-2.005.035-2.869.182-.78 1.173-4.971 1.173-4.971s-.299-.599-.299-1.484c0-1.39.806-2.429 1.809-2.429.853 0 1.265.641 1.265 1.409 0 .858-.546 2.141-.828 3.329-.236.996.499 1.807 1.481 1.807 1.777 0 3.144-1.874 3.144-4.579 0-2.394-1.72-4.068-4.177-4.068-2.845 0-4.515 2.134-4.515 4.34 0 .859.331 1.781.744 2.282a.3.3 0 0 1 .069.287c-.077.316-.246.995-.279 1.134-.044.183-.145.222-.334.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.051 6.607-6.051 3.469 0 6.165 2.472 6.165 5.775 0 3.446-2.173 6.22-5.189 6.22-1.013 0-1.966-.526-2.292-1.148l-.623 2.377c-.226.869-.835 1.957-1.243 2.622.936.289 1.93.445 2.961.445 5.523 0 10-4.477 10-10S17.523 2 12 2"/></svg>',
            ),

            'reddit' => array(
                'label'     => 'Reddit',
                'color'     => '#FF4500',
                'share_url' => 'https://www.reddit.com/submit?url={url}&title={title}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10H3.448a.6.6 0 0 1-.424-1.024L4.93 19.07A9.97 9.97 0 0 1 2 12C2 6.477 6.477 2 12 2m3.656 2.666c-.804 0-1.475.57-1.632 1.33a2.69 2.69 0 0 0-2.4 2.672v.008c-1.466.062-2.804.479-3.866 1.137a2.335 2.335 0 1 0-2.418 3.963c.077 2.711 3.031 4.892 6.665 4.892s6.59-2.183 6.664-4.896a2.336 2.336 0 0 0-1.001-4.445c-.535 0-1.028.18-1.422.484-1.072-.664-2.425-1.08-3.905-1.136v-.007c0-.992.737-1.815 1.693-1.95l.038.134a1.668 1.668 0 0 0 3.25-.52c0-.92-.746-1.666-1.666-1.666M12.005 14.99c.811 0 1.588.04 2.307.112.123.013.201.14.154.254a2.667 2.667 0 0 1-4.922 0 .185.185 0 0 1 .152-.254 23 23 0 0 1 2.309-.112m-3.086-3.344c.654 0 1.154.687 1.115 1.534-.039.846-.527 1.155-1.181 1.155-.655 0-1.228-.345-1.189-1.191.04-.847.601-1.497 1.255-1.498m6.172 0c.654 0 1.216.65 1.255 1.498.039.846-.535 1.191-1.189 1.191-.653 0-1.142-.308-1.181-1.155s.46-1.533 1.115-1.533"/></svg>',
            ),

            'telegram' => array(
                'label'     => 'Telegram',
                'color'     => '#26A5E4',
                'share_url' => 'https://t.me/share/url?url={url}&text={title}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2m3.08 14.757s-.25.625-.936.325l-2.541-1.949-1.63 1.486s-.127.096-.266.036c0 0-.12-.011-.27-.486s-.911-2.972-.911-2.972L6 12.349s-.387-.137-.425-.438c-.037-.3.437-.462.437-.462l10.03-3.934s.824-.362.824.238z"/></svg>',
            ),

            'threads' => array(
                'label'     => 'Threads',
                'color'     => '#000000',
                'share_url' => 'https://www.threads.net/intent/post?text={title}+{url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 192" fill="currentColor"><path d="M141.537 88.988a67 67 0 0 0-2.518-1.143c-1.482-27.307-16.403-42.94-41.457-43.1h-.34c-14.986 0-27.449 6.396-35.12 18.036l13.779 9.452c5.73-8.695 14.724-10.548 21.348-10.548h.229c8.249.053 14.474 2.452 18.503 7.129 2.932 3.405 4.893 8.111 5.864 14.05-7.314-1.243-15.224-1.626-23.68-1.14-23.82 1.371-39.134 15.264-38.105 34.568.522 9.792 5.4 18.216 13.735 23.719 7.047 4.652 16.124 6.927 25.557 6.412 12.458-.683 22.231-5.436 29.049-14.127 5.178-6.6 8.453-15.153 9.899-25.93 5.937 3.583 10.337 8.298 12.767 13.966 4.132 9.635 4.373 25.468-8.546 38.376-11.319 11.308-24.925 16.2-45.488 16.351-22.809-.169-40.06-7.484-51.275-21.742C35.236 139.966 29.808 120.682 29.605 96c.203-24.682 5.63-43.966 16.133-57.317C56.954 24.425 74.204 17.11 97.013 16.94c22.975.17 40.526 7.52 52.171 21.847 5.71 7.026 10.015 15.86 12.853 26.162l16.147-4.308c-3.44-12.68-8.853-23.606-16.219-32.668C147.036 9.607 125.202.195 97.07 0h-.113C68.882.194 47.292 9.642 32.788 28.08 19.882 44.485 13.224 67.315 13.001 95.932L13 96v.067c.224 28.617 6.882 51.447 19.788 67.854C47.292 182.358 68.882 191.806 96.957 192h.113c24.96-.173 42.554-6.708 57.048-21.189 18.963-18.945 18.392-42.692 12.142-57.27-4.484-10.454-13.033-18.945-24.723-24.553M98.44 129.507c-10.44.588-21.286-4.098-21.82-14.135-.397-7.442 5.296-15.746 22.461-16.735q2.948-.17 5.79-.169c6.235 0 12.068.606 17.371 1.765-1.978 24.702-13.58 28.713-23.802 29.274"/></svg>',
            ),

            'tumblr' => array(
                'label'     => 'Tumblr',
                'color'     => '#36465D',
                'share_url' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={url}&title={title}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-5.569 14.265c-2.446.042-3.372-1.742-3.372-2.998v-3.668H8.923v-1.45c1.703-.614 2.113-2.15 2.209-3.025.007-.06.054-.084.081-.084h1.645V8.9h2.246v1.7H12.85v3.495c.008.476.182 1.131 1.081 1.107.298-.008.697-.094.906-.194l.54 1.601c-.205.296-1.121.641-1.946.656"/></svg>',
            ),

            'line' => array(
                'label'     => 'LINE',
                'color'     => '#00C300',
                'share_url' => 'https://social-plugins.line.me/lineit/share?url={url}',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14.255 9.572v3.333c0 .084-.066.15-.15.15h-.534a.16.16 0 0 1-.122-.061l-1.528-2.063v1.978c0 .084-.066.15-.15.15h-.534a.15.15 0 0 1-.15-.15V9.576c0-.084.066-.15.15-.15h.529a.14.14 0 0 1 .122.066l1.528 2.063V9.577c0-.084.066-.15.15-.15h.534a.15.15 0 0 1 .155.145m-3.844-.15h-.534a.15.15 0 0 0-.15.15v3.333c0 .084.066.15.15.15h.534c.084 0 .15-.066.15-.15V9.572c0-.08-.066-.15-.15-.15m-1.289 2.794H7.664V9.572a.15.15 0 0 0-.15-.15H6.98a.15.15 0 0 0-.15.15v3.333q0 .062.042.103a.16.16 0 0 0 .103.042h2.142c.084 0 .15-.066.15-.15v-.534a.15.15 0 0 0-.145-.15m7.945-2.794h-2.142c-.08 0-.15.066-.15.15v3.333c0 .08.066.15.15.15h2.142c.084 0 .15-.066.15-.15v-.534a.15.15 0 0 0-.15-.15h-1.458v-.563h1.458c.084 0 .15-.066.15-.15v-.539a.15.15 0 0 0-.15-.15h-1.458v-.563h1.458c.084 0 .15-.066.15-.15v-.534c-.005-.08-.07-.15-.15-.15M22.5 5.33v13.373c-.005 2.1-1.725 3.802-3.83 3.797H5.297c-2.1-.005-3.802-1.73-3.797-3.83V5.297c.005-2.1 1.73-3.802 3.83-3.797h13.373c2.1.005 3.802 1.725 3.797 3.83m-2.888 5.747c0-3.422-3.431-6.206-7.645-6.206s-7.645 2.784-7.645 6.206c0 3.066 2.719 5.634 6.394 6.122.895.192.792.52.591 1.725-.033.192-.155.755.661.413s4.402-2.592 6.009-4.439c1.106-1.219 1.636-2.452 1.636-3.82"/></svg>',
            ),

            'copy_link' => array(
                'label'     => 'Copy Link',
                'color'     => '#6B7280',
                'share_url' => '#copy',
                'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17 13H7v-2h10zm1-6h-1c-1.631 0-3.065.792-3.977 2H18c1.103 0 2 .897 2 2v2c0 1.103-.897 2-2 2h-4.977c.913 1.208 2.347 2 3.977 2h1a4 4 0 0 0 4-4v-2a4 4 0 0 0-4-4M2 11v2a4 4 0 0 0 4 4h1c1.63 0 3.065-.792 3.977-2H6c-1.103 0-2-.897-2-2v-2c0-1.103.897-2 2-2h4.977C10.065 7.792 8.631 7 7 7H6a4 4 0 0 0-4 4"/></svg>',
            ),

        );

        /**
         * Filter the registered platforms.
         *
         * @param array $platforms Associative array of platform configs.
         */
        return apply_filters( 'ripple_platforms', $platforms );
    }

    /**
     * Get only enabled platforms based on settings.
     */
    public static function get_enabled() {
        $all     = self::get_all();
        $enabled = Ripple_Social_Share::get_setting( 'platforms', array( 'linkedin', 'x', 'bluesky' ) );
        $result  = array();

        foreach ( $enabled as $slug ) {
            if ( isset( $all[ $slug ] ) ) {
                $result[ $slug ] = $all[ $slug ];
            }
        }

        return $result;
    }

    /**
     * Build a share URL for a platform.
     */
    public static function build_share_url( $platform_config, $post_id = null ) {
        if ( null === $post_id ) {
            $post_id = get_the_ID();
        }

        $post = get_post( $post_id );
        if ( ! $post ) return '#';

        $url         = get_permalink( $post_id );
        $title       = get_the_title( $post_id );
        $description = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( $post->post_content, 30 );
        $image       = get_the_post_thumbnail_url( $post_id, 'large' );

        $share_url = $platform_config['share_url'];
        $share_url = str_replace( '{url}', rawurlencode( $url ), $share_url );
        $share_url = str_replace( '{title}', rawurlencode( $title ), $share_url );
        $share_url = str_replace( '{description}', rawurlencode( $description ), $share_url );
        $share_url = str_replace( '{image}', rawurlencode( $image ? $image : '' ), $share_url );

        return $share_url;
    }
}
