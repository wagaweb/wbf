<?php

if(!function_exists('wbf_async_scripts')):
    /**
     * Enable to load js script async
     * @source https://ikreativ.com/async-with-wordpress-enqueue/
     *
     * @param string $url
     *
     * @return string
     */
    function wbf_async_scripts($url) {
        if(strpos( $url, '#asyncload') === false){
            return $url;
        }
        elseif(is_admin()){
            return str_replace( '#asyncload', '', $url );
        }
        else{
            return str_replace( '#asyncload', '', $url )."' async='async";
        }
    }
    add_filter( 'clean_url', 'wbf_async_scripts', 11, 1 );
endif;

if(!function_exists( 'wbf_wp_title')):
    /**
     * Creates the title based on current view
     * @since 0.1.0
     *
     * @param string $title
     * @param string $sep
     *
     * @return string
     */
    function wbf_wp_title( $title, $sep ) {

        global $paged, $page;

        if ( is_feed() )
            return $title;

        // Add the site name.
        $title .= get_bloginfo( 'name', 'display' );

        // Add the site description for the home/front page.
        $site_description = get_bloginfo( 'description', 'display' );

        if ( $site_description && ( is_home() || is_front_page() ) )
            $title = "$title $sep $site_description";

        // Add a page number if necessary.
        if ( $paged >= 2 || $page >= 2 )
            $title = "$title $sep " . sprintf( __( 'Page %s', 'wbf' ), max( $paged, $page ) );

        return $title;
    }
    add_filter( 'wp_title', 'wbf_wp_title', 10, 2 );
endif;

if(!function_exists('wbf_comment_reply_link')):
    /**
     * Style comment reply links as buttons
     * @since 0.1.0
     *
     * @param string $link
     *
     * @return string
     */
    function wbf_comment_reply_link( $link ) {

        return str_replace( 'comment-reply-link', 'btn btn-default btn-xs', $link );
    }
    add_filter( 'comment_reply_link', 'wbf_comment_reply_link' );
endif;

if(!function_exists( 'wbf_nice_search_redirect')):
    /**
     * Pretty search URL. Changes /?s=foo to /search/foo. http://txfx.net/wordpress-plugins/nice-search/
     * @since 0.1.0
     */
    function wbf_nice_search_redirect() {
        if ( is_search() && get_option( 'permalink_structure' ) != '' && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) === false && strpos( $_SERVER['REQUEST_URI'], '/search/' ) === false ) {
            wp_redirect( home_url( '/search/' . str_replace( array( ' ', '%20' ),  array( '+', '+' ), get_query_var( 's' ) ) ) );
            exit();
        }
    }
    add_action( 'template_redirect', 'wbf_nice_search_redirect' );
endif;

if (!function_exists( 'wbf_excerpt_more')):
    /**
     * Style the excerpt continuation
     *
     * @param string $more
     *
     * @return string
     */
    function wbf_excerpt_more( $more ) {
        return ' ... <a href="'. get_permalink( get_the_ID() ) . '">'. __( 'Continue Reading ', 'wbf' ) .' &raquo;</a>';
    }
    add_filter('excerpt_more', 'wbf_excerpt_more');
endif;

if (!function_exists( 'wbf_head_cleanup')):
    /**
     * Cleanup the head
     * @source http://geoffgraham.me/wordpress-how-to-clean-up-the-header/
     * @since 0.1.0
     */
    function wbf_head_cleanup() {
        // EditURI link
        remove_action( 'wp_head', 'rsd_link' );
        // Category feed links
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        // Post and comment feed links
        remove_action( 'wp_head', 'feed_links', 2 );
        // Windows Live Writer
        remove_action( 'wp_head', 'wlwmanifest_link' );
        // Index link
        remove_action( 'wp_head', 'index_rel_link' );
        // Previous link
        remove_action( 'wp_head', 'parent_post_rel_link', 10);
        // Start link
        remove_action( 'wp_head', 'start_post_rel_link', 10);
        // Canonical
        remove_action('wp_head', 'rel_canonical', 10);
        // Shortlink
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10);
        // Links for adjacent posts
        remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        // WP version
        remove_action( 'wp_head', 'wp_generator' );
    }
    add_action('init', 'wbf_head_cleanup');
endif;


if (!function_exists( 'wbf_sanitize_file_name_chars')):
    /**
     * Improved default function sanitize_file_name()
     * @source https://wordpress.org/plugins/wp-sanitize-file-name-plus/
     */
    function wbf_sanitize_file_name_chars($filename)
    {
        $invalid = array(
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ā'=>'A', 'Ă'=>'A', 'Ȧ'=>'A', 'Ä'=>'A', 'Ả'=>'A', 'Å'=>'A', 'Ǎ'=>'A', 'Ȁ'=>'A', 'Ȃ'=>'A', 'Ą'=>'A', 'Ạ'=>'A', 'Ḁ'=>'A', 'Ầ'=>'A', 'Ấ'=>'A', 'Ẫ'=>'A', 'Ẩ'=>'A', 'Ằ'=>'A', 'Ắ'=>'A', 'Ẵ'=>'A', 'Ẳ'=>'A', 'Ǡ'=>'A', 'Ǟ'=>'A', 'Ǻ'=>'A', 'Ậ'=>'A', 'Ặ'=>'A',
            'Æ'=>'AE', 'Ǽ'=>'AE', 'Ǣ'=>'AE',
            'Ḃ'=>'B', 'Ɓ'=>'B', 'Ḅ'=>'B', 'Ḇ'=>'B', 'Ƃ'=>'B', 'Ƅ'=>'B', 'Þ'=>'B',
            'Ĉ'=>'C', 'Ċ'=>'C', 'Č'=>'C', 'Ƈ'=>'C', 'Ç'=>'C', 'Ḉ'=>'C',
            'Ḋ'=>'D', 'Ɗ'=>'D', 'Ḍ'=>'D', 'Ḏ'=>'D', 'Ḑ'=>'D', 'Ḓ'=>'D', 'Ď'=>'D',
            'Đ'=>'Dj', 'Ɖ'=>'Dj',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ẽ'=>'E', 'Ē'=>'E', 'Ĕ'=>'E', 'Ė'=>'E', 'Ë'=>'E', 'Ẻ'=>'E', 'Ě'=>'E', 'Ȅ'=>'E', 'Ȇ'=>'E', 'Ẹ'=>'E', 'Ȩ'=>'E', 'Ę'=>'E', 'Ḙ'=>'E', 'Ḛ'=>'E', 'Ề'=>'E', 'Ế'=>'E', 'Ễ'=>'E', 'Ể'=>'E', 'Ḕ'=>'E', 'Ḗ'=>'E', 'Ệ'=>'E', 'Ḝ'=>'E', 'Ǝ'=>'E', 'Ɛ'=>'E',
            'Ḟ'=>'F', 'Ƒ'=>'F',
            'Ǵ'=>'G', 'Ĝ'=>'G', 'Ḡ'=>'G', 'Ğ'=>'G', 'Ġ'=>'G', 'Ǧ'=>'G', 'Ɠ'=>'G', 'Ģ'=>'G', 'Ǥ'=>'G',
            'Ĥ'=>'H', 'Ḧ'=>'H', 'Ȟ'=>'H', 'Ƕ'=>'H', 'Ḥ'=>'H', 'Ḩ'=>'H', 'Ḫ'=>'H', 'Ħ'=>'H',
            'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ĩ'=>'I', 'Ī'=>'I', 'Ĭ'=>'I', 'İ'=>'I', 'Ï'=>'I', 'Ỉ'=>'I', 'Ǐ'=>'I', 'Ị'=>'I', 'Į'=>'I', 'Ȉ'=>'I', 'Ȋ'=>'I', 'Ḭ'=>'I', 'Ɨ'=>'I', 'Ḯ'=>'I',
            'Ĳ'=>'IJ',
            'Ĵ'=>'J',
            'Ḱ'=>'K', 'Ǩ'=>'K', 'Ḵ'=>'K', 'Ƙ'=>'K', 'Ḳ'=>'K', 'Ķ'=>'K', 'Ĺ'=>'L', 'Ḻ'=>'L', 'Ḷ'=>'L', 'Ļ'=>'L', 'Ḽ'=>'L', 'Ľ'=>'L', 'Ŀ'=>'L', 'Ł'=>'L', 'Ḹ'=>'L',
            'Ḿ'=>'M', 'Ṁ'=>'M', 'Ṃ'=>'M', 'Ɯ'=>'M', 'Ñ'=>'N', 'Ǹ'=>'N', 'Ń'=>'N', 'Ñ'=>'N', 'Ṅ'=>'N', 'Ň'=>'N', 'Ŋ'=>'N', 'Ɲ'=>'N', 'Ṇ'=>'N', 'Ņ'=>'N', 'Ṋ'=>'N', 'Ṉ'=>'N', 'Ƞ'=>'N',
            'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ō'=>'O', 'Ŏ'=>'O', 'Ȯ'=>'O', 'Ö'=>'O', 'Ỏ'=>'O', 'Ő'=>'O', 'Ǒ'=>'O', 'Ȍ'=>'O', 'Ȏ'=>'O', 'Ơ'=>'O', 'Ǫ'=>'O', 'Ọ'=>'O', 'Ɵ'=>'O', 'Ø'=>'O', 'Ồ'=>'O', 'Ố'=>'O', 'Ỗ'=>'O', 'Ổ'=>'O', 'Ȱ'=>'O', 'Ȫ'=>'O', 'Ȭ'=>'O', 'Ṍ'=>'O', 'Ṑ'=>'O', 'Ṓ'=>'O', 'Ờ'=>'O', 'Ớ'=>'O', 'Ỡ'=>'O', 'Ở'=>'O', 'Ợ'=>'O', 'Ǭ'=>'O', 'Ộ'=>'O', 'Ǿ'=>'O', 'Ɔ'=>'O', 'Œ'=>'OE',
            'Ṕ'=>'P', 'Ṗ'=>'P', 'Ƥ'=>'P',
            'Ŕ'=>'R', 'Ṙ'=>'R', 'Ř'=>'R',	'Ȑ'=>'R', 'Ȓ'=>'R', 'Ṛ'=>'R', 'Ŗ'=>'R', 'Ṟ'=>'R', 'Ṝ'=>'R', 'Ʀ'=>'R',
            'Ś'=>'S', 'Ŝ'=>'S', 'Ṡ'=>'S', 'Š'=>'S', 'Ṣ'=>'S', 'Ș'=>'S', 'Ş'=>'S', 'Ṥ'=>'S', 'Ṧ'=>'S', 'Ṩ'=>'S',
            'Ṫ'=>'T', 'Ť'=>'T', 'Ƭ'=>'T', 'Ʈ'=>'T', 'Ṭ'=>'T', 'Ț'=>'T', 'Ţ'=>'T', 'Ṱ'=>'T', 'Ṯ'=>'T', 'Ŧ'=>'T',
            'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ũ'=>'U', 'Ū'=>'U', 'Ŭ'=>'U', 'Ü'=>'U', 'Ủ'=>'U', 'Ů'=>'U', 'Ű'=>'U', 'Ǔ'=>'U', 'Ȕ'=>'U', 'Ȗ'=>'U', 'Ư'=>'U', 'Ụ'=>'U', 'Ṳ'=>'U', 'Ų'=>'U', 'Ṷ'=>'U', 'Ṵ'=>'U', 	'Ṹ'=>'U', 'Ṻ'=>'U', 'Ǜ'=>'U', 'Ǘ'=>'U', 'Ǖ'=>'U', 'Ǚ'=>'U', 'Ừ'=>'U', 	'Ứ'=>'U', 'Ữ'=>'U', 'Ử'=>'U', 'Ự'=>'U',
            'Ṽ'=>'V', 'Ṿ'=>'V', 'Ʋ'=>'V',
            'Ẁ'=>'W', 'Ẃ'=>'W', 'Ŵ'=>'W', 'Ẇ'=>'W', 'Ẅ'=>'W', 'Ẉ'=>'W',
            'Ẋ'=>'X', 'Ẍ'=>'X',
            'Ỳ'=>'Y', 'Ý'=>'Y', 'Ŷ'=>'Y', 'Ỹ'=>'Y', 'Ȳ'=>'Y', 'Ẏ'=>'Y', 'Ÿ'=>'Y', 'Ỷ'=>'Y', 'Ƴ'=>'Y', 'Ỵ'=>'Y',
            'Ź'=>'Z', 'Ẑ'=>'Z', 'Ż'=>'Z', 'Ž'=>'Z', 'Ȥ'=>'Z', 'Ẓ'=>'Z', 'Ẕ'=>'Z', 'Ƶ'=>'Z',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ā'=>'a', 'ă'=>'a', 'ȧ'=>'a', 'ä'=>'a', 'ả'=>'a', 'å'=>'a', 'ǎ'=>'a', 'ȁ'=>'a', 'ą'=>'a', 'ạ'=>'a', 'ḁ'=>'a', 'ẚ'=>'a', 'ầ'=>'a', 'ấ'=>'a', 'ẫ'=>'a', 'ẩ'=>'a', 'ằ'=>'a', 'ắ'=>'a', 'ẵ'=>'a', 'ẳ'=>'a', 'ǡ'=>'a', 'ǟ'=>'a', 'ǻ'=>'a', 'ậ'=>'a', 'ặ'=>'a',
            'æ'=>'ae', 'ǽ'=>'ae', 'ǣ'=>'ae',
            'ḃ'=>'b', 'ɓ'=>'b', 'ḅ'=>'b', 'ḇ'=>'b', 'ƀ'=>'b', 'ƃ'=>'b', 'ƅ'=>'b', 'þ'=>'b',
            'ć'=>'c', 'ĉ'=>'c', 'ċ'=>'c', 'č'=>'c', 'ƈ'=>'c', 'ç'=>'c', 'ḉ'=>'c',
            'ḋ'=>'d', 'ɗ'=>'d', 'ḍ'=>'d', 'ḏ'=>'d', 'ḑ'=>'d', 'ḓ'=>'d', 'ď'=>'d', 'đ'=>'d', 'ƌ'=>'d', 'ȡ'=>'d',
            'đ'=>'dj',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ẽ'=>'e', 'ē'=>'e', 'ĕ'=>'e', 'ė'=>'e', 'ë'=>'e', 'ẻ'=>'e', 'ě'=>'e', 'ȅ'=>'e', 'ȇ'=>'e', 'ẹ'=>'e', 'ȩ'=>'e', 'ę'=>'e', 'ḙ'=>'e', 'ḛ'=>'e', 'ề'=>'e', 'ế'=>'e', 			'ễ'=>'e', 'ể'=>'e', 'ḕ'=>'e', 'ḗ'=>'e', 'ệ'=>'e', 'ḝ'=>'e', 'ǝ'=>'e', 'ɛ'=>'e',
                'ḟ'=>'f', 'ƒ'=>'f',
                'ǵ'=>'g', 'ĝ'=>'g', 'ḡ'=>'g', 'ğ'=>'g', 'ġ'=>'g', 'ǧ'=>'g', 'ɠ'=>'g', 'ģ'=>'g', 'ǥ'=>'g',
                'ĥ'=>'h', 'ḣ'=>'h', 'ḧ'=>'h', 'ȟ'=>'h', 'ƕ'=>'h', 'ḥ'=>'h', 'ḩ'=>'h', 'ḫ'=>'h', 'ẖ'=>'h', 'ħ'=>'h',
                'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ĩ'=>'i', 'ī'=>'i', 'ĭ'=>'i', 'ı'=>'i', 'ï'=>'i', 'ỉ'=>'i', 'ǐ'=>'i', 'ị'=>'i', 'į'=>'i', 'ȉ'=>'i', 'ȋ'=>'i', 'ḭ'=>'i',  'ɨ'=>'i', 'ḯ'=>'i',
                'ĳ'=>'ij',
                'ĵ'=>'j', 'ǰ'=>'j',
                'ḱ'=>'k', 'ǩ'=>'k', 'ḵ'=>'k', 'ƙ'=>'k', 'ḳ'=>'k', 'ķ'=>'k',
                'ĺ'=>'l', 'ḻ'=>'l', 'ḷ'=>'l', 'ļ'=>'l', 'ḽ'=>'l', 'ľ'=>'l', 'ŀ'=>'l', 'ł'=>'l', 'ƚ'=>'l', 'ḹ'=>'l', 'ȴ'=>'l',
                'ḿ'=>'m', 'ṁ'=>'m', 'ṃ'=>'m', 'ɯ'=>'m',
                'ǹ'=>'n', 'ń'=>'n', 'ñ'=>'n', 'ṅ'=>'n', 'ň'=>'n', 'ŋ'=>'n', 'ɲ'=>'n', 'ṇ'=>'n', 'ņ'=>'n', 'ṋ'=>'n', 'ṉ'=>'n', 'ŉ'=>'n', 'ƞ'=>'n', 'ȵ'=>'n',
                'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ō'=>'o', 'ŏ'=>'o', 'ȯ'=>'o', 'ö'=>'o', 'ỏ'=>'o', 'ő'=>'o', 'ǒ'=>'o', 'ȍ'=>'o', 'ȏ'=>'o', 'ơ'=>'o', 'ǫ'=>'o', 'ọ'=>'o', 'ɵ'=>'o', 'ø'=>'o', 'ồ'=>'o', 'ố'=>'o', 'ỗ'=>'o', 'ổ'=>'o', 'ȱ'=>'o', 'ȫ'=>'o', 'ȭ'=>'o', 'ṍ'=>'o', 'ṏ'=>'o', 'ṑ'=>'o', 'ṓ'=>'o', 'ờ'=>'o', 'ớ'=>'o', 'ỡ'=>'o', 'ở'=>'o', 'ợ'=>'o', 'ǭ'=>'o', 'ộ'=>'o', 'ǿ'=>'o', 'ɔ'=>'o',
                'œ'=>'oe',
                'ṕ'=>'p', 'ṗ'=>'p', 'ƥ'=>'p',
                'ŕ'=>'r', 'ṙ'=>'r', 'ř'=>'r', 'ȑ'=>'r', 'ȓ'=>'r', 'ṛ'=>'r', 'ŗ'=>'r', 'ṟ'=>'r', 'ṝ'=>'r',
                'ś'=>'s', 'ŝ'=>'s', 'ṡ'=>'s', 'š'=>'s', 'ṣ'=>'s', 'ș'=>'s', 'ş'=>'s', 'ṥ'=>'s', 'ṧ'=>'s', 'ṩ'=>'s', 'ſ'=>'s', 'ẛ'=>'s',
                'ß'=>'Ss',
                'ṫ'=>'t', 'ẗ'=>'t', 'ť'=>'t', 'ƭ'=>'t', 'ʈ'=>'t', 'ƫ'=>'t', 'ṭ'=>'t', 'ț'=>'t', 'ţ'=>'t', 'ṱ'=>'t', 'ṯ'=>'t', 'ŧ'=>'t', 'ȶ'=>'t',
                'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ũ'=>'u', 'ū'=>'u', 'ŭ'=>'u', 'ü'=>'u', 'ủ'=>'u', 'ů'=>'u', 'ű'=>'u', 'ǔ'=>'u', 'ȕ'=>'u', 'ȗ'=>'u', 'ư'=>'u', 'ụ'=>'u', 'ṳ'=>'u', 'ų'=>'u', 'ṷ'=>'u', 'ṵ'=>'u', 'ṹ'=>'u', 'ṻ'=>'u', 'ǜ'=>'u', 'ǘ'=>'u', 'ǖ'=>'u', 'ǚ'=>'u', 'ừ'=>'u', 'ứ'=>'u', 'ữ'=>'u', 'ử'=>'u', 'ự'=>'u',
                'ṽ'=>'v', 'ṿ'=>'v',
                'ẁ'=>'w', 'ẃ'=>'w', 'ŵ'=>'w', 'ẇ'=>'w', 'ẅ'=>'w', 'ẘ'=>'w', 'ẉ'=>'w',
                'ẋ'=>'x', 'ẍ'=>'x',
                'ý'=>'y', 'ý'=>'y', 'ỳ'=>'y', 'ý'=>'y', 'ŷ'=>'y', 'ȳ'=>'y', 'ẏ'=>'y', 'ÿ'=>'y', 'ÿ'=>'y', 'ỷ'=>'y', 'ẙ'=>'y', 'ƴ'=>'y', 'ỵ'=>'y',
                'ź'=>'z', 'ẑ'=>'z', 'ż'=>'z', 'ž'=>'z', 'ȥ'=>'z', 'ẓ'=>'z', 'ẕ'=>'z', 'ƶ'=>'z',
                '№'=>'No',
                'º'=>'o',
                'ª'=>'a',
                '€'=>'E',
                '©'=>'C',
                '℗'=>'P',
                '™'=>'tm',
                '℠'=>'sm',
                '’' => '',
                '_'=>'-',
                '%20'=>'-'
            );

            $sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $filename);

            $sanitized_filename = remove_accents($sanitized_filename);

            $sanitized_filename = preg_replace('/[^a-zA-Z0-9-_\.]/', 'x', strtolower($sanitized_filename));

            return $sanitized_filename;
        }
        add_filter('sanitize_file_name', 'wbf_sanitize_file_name_chars', 10);
endif;