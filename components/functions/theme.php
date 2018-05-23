<?php

/**
 * Include theme required css and javascript files and localise
 * php variables for JavaScript use
 */
function theme_enqueue()
{
	wp_enqueue_style('site-styles', get_template_directory_uri() . '/dist/css/main.css', array(), filemtime(get_template_directory() . '/dist/css/main.css'));
	wp_enqueue_script('site-scripts', get_template_directory_uri() . '/dist/js/main.js', array(), filemtime(get_template_directory() . '/dist/js/main.js'), true);

	$localisation = array(
		'template' 	=> get_template_directory_uri(),
		'assets' 	=> get_template_directory_uri() . '/dist/',
		'site' 		=> get_site_url(),
		'ajax'		=> admin_url('admin-ajax.php'),
		'nonce'		=> wp_create_nonce('ajax-nonce'),
        'gmap_key'  => get_field('google_maps_api_key', 'options'),
        'translate' => array(
            'error'     => __('There appears to have been a problem please try again later', 'tmp'),
            'thanks'    => __('Thank you', 'tmp')
        )
	);

	wp_localize_script( 'site-scripts', 'WP', $localisation );
}
add_action('wp_enqueue_scripts', 'theme_enqueue');

/**
 * Remove enqueued scripts and css files
 */
function theme_dequeue()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('wp_enqueue_scripts', 'theme_dequeue');

/**
 * Register theme support
 */
function theme_init()
{
    //Register theme supports
    add_theme_support('post-thumbnails');
    add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
    load_theme_textdomain( 'tmp', TEMPLATEPATH.'/components/languages' );

	//Register navigation menus
	register_nav_menus(
		array(
			'main-nav' 		=> 'Main Navigation Menu',
			'footer-nav' 	=> 'Footer Navigation Menu'
		)
	);

	//Add image sizes (name, width, height, crop)
	add_image_size('email-banner', 600, null, true); // used for contact emails
}
add_action('init', 'theme_init');

/**
 * Add 'menu-parent-item' class to menu parent items generated by wp_nav_menu
 *
 * @param $items
 * @return mixed
 */
function add_menu_parent_class($items)
{
	$parents = array();
	foreach ($items as $item) {
		if ($item->menu_item_parent && $item->menu_item_parent > 0) {
			$parents[] = $item->menu_item_parent;
		}
	}

	foreach ($items as $item) {
		if (in_array($item->ID, $parents)) {
			$item->classes[] = 'menu-parent-item';
		}
	}
	return $items;
}
add_filter('wp_nav_menu_objects', 'add_menu_parent_class');

/**
 * Add 'current-menu-item' class menu to current menu item generated by wp_nav_menu
 *
 * @param $classes
 * @param $item
 * @return array
 */
function add_current_nav_class($classes, $item)
{
	// Getting the current post details
	global $post;

	if( !isset($post) )
		return $classes;

	// Getting the post type of the current post
	$current_post_type = get_post_type_object(get_post_type($post->ID));
	//If not in a post type get the hell out of here
	if( !is_array( $current_post_type_slug = $current_post_type->rewrite ) ){
		return $classes;
	}
	$current_post_type_slug = $current_post_type->rewrite['slug'];

	// Getting the URL of the menu item
	$menu_slug = strtolower(trim($item->url));

	// If the menu item URL contains the current post types slug add the current-menu-item class
	if (strpos($menu_slug,$current_post_type_slug) !== false) {
		$classes[] = 'current-menu-item';
	}

	// Return the corrected set of classes to be added to the menu item
	return $classes;
}
add_action('nav_menu_css_class', 'add_current_nav_class', 10, 2 );

/**
 * Add custom header scripts to the header
 */
function custom_header_scripts(){
    the_field('custom_header_scripts', 'option');
}
add_action('wp_head', 'custom_header_scripts');

/**
 * Add custom footer scripts to the footer
 */
function custom_footer_scripts(){
    the_field('custom_footer_scripts', 'option');
}
add_action('wp_head', 'custom_footer_scripts');

/**
 * Add the query vars required for calling favicons directly
 */
function add_favicon_query_vars( $query_vars ){
    $query_vars[] = 'fav_request';
    return $query_vars;
}
add_filter( 'query_vars', 'add_favicon_query_vars', 10, 1 );

/**
 * Add query rules for favicon requests
 */
function add_favicon_rules() {
    $files = array(
        'android-chrome-192x192.png',
        'android-chrome-512x512.png',
        'apple-touch-icon.png',
        'browserconfig.xml',
        'favicon-16x16.png',
        'favicon-32x32.png',
        'favicon.ico',
        'mstile-150x150.png',
        'safari-pinned-tab.svg',
        'site.webmanifest'
    );

    foreach($files as $file){
        $url = locate_template('assets/favicons/' . $file, false, false);
        if($url){
            add_rewrite_rule('^'.preg_quote($file, '/').'?', 'index.php?fav_request=' . $file, 'top');
        }
    }
    flush_rewrite_rules();
}
add_action('init', 'add_favicon_rules');

/**
 * Request the favicon instead of a page
 */
function check_favicon_request(){
    global $wp;

    if( $wp->query_vars && array_key_exists( 'fav_request', $wp->query_vars )  ) {
        //echo $wp->query_vars['fav_request'];
        $path = locate_template('assets/favicons/' . $wp->query_vars['fav_request'], false, false);
        if( function_exists('finfo_file') ){
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $path);
            if($mime_type === 'image/vnd.microsoft.icon')
                $mime_type = 'image/x-icon';
        } else {
            switch(pathinfo($path, PATHINFO_EXTENSION)){
                case 'png':
                    $mime_type = 'image/png';
                    break;
                case 'svg':
                    $mime_type = 'image/svg+xml';
                    break;
                case 'ico':
                    $mime_type = 'image/x-icon';
                    break;
                case 'xml':
                    $mime_type = 'application/xml';
                    break;
                case 'webmanifest':
                    $mime_type = 'text/plain';
                    break;
                default:
                    $mime_type = false;
                    break;
            }
        }

        if($mime_type)
            header('Content-Type: ' . $mime_type);
        header('filename:" '.basename($path).'"');
        header('Content-Length: ' . filesize($path));
        readfile($path);

        exit;
    }
}
add_action( 'parse_query', 'check_favicon_request');

/**
 * Disable default WordPress Site Icons
 */
function remove_site_icon(){
    return array();
}
add_filter('site_icon_meta_tags', 'remove_site_icon');

/**
 * Removes control from site identity menu for setting the site favicon
 */
function remove_site_ico_control(){
    global $wp_customize;
    $wp_customize->remove_control('site_icon');
}
add_action('customize_register', 'remove_site_ico_control', 20);