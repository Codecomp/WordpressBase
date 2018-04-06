<?php

/*********************************************************************
 * General front end settings and functions
 *********************************************************************/

/**
 * Add misc scripts fields
 *
 * @param $tabs
 * @return mixed
 */
function theme_options_tabs_scripts( $tabs ) {

    $tabs['Misc Scripts & Analytics'] = array(
        array (
            'name' => 'Header Scripts',
            'key'  => 'custom_header_scripts',
            'instructions' => __('Add any code here that needs to be output in the website header', 'tmp'),
            'type' => 'textarea',
        ),
        array (
            'name' => 'Footer Scripts',
            'key'  => 'custom_footer_scripts',
            'instructions' => __('Add any code here that needs to be output in the website footer', 'tmp'),
            'type' => 'textarea',
        ),
    );

    return $tabs;
}
add_filter( 'theme_options_tabs', 'theme_options_tabs_scripts' );

/**
 * Include theme required css and javascript files and localise
 * php variables for JavaScript use
 */
function theme_enqueue()
{
    $assets = array(
        'js' 	=> get_template_directory_uri() . '/dist/js/',
        'css' 	=> get_template_directory_uri() . '/dist/css/'
    );

	// Theme
	wp_enqueue_style('site-styles', 	$assets['css'] . 'main.css');
	wp_enqueue_script('site-scripts', 	$assets['js'] . 'main.js', array( 'jquery', 'tweenmax' ), false, true);

	// Localize site directory data to javascript
	$localisation = array(
		'template' 	=> get_template_directory_uri(),
		'assets' 	=> get_template_directory_uri() . '/assets/',
		'site' 		=> get_site_url(),
		'ajax'		=> admin_url('admin-ajax.php'),
		'nonce'		=> wp_create_nonce('ajax-nonce'),
	);

	wp_localize_script( 'site-scripts', 'WP', $localisation );
}
add_action('wp_enqueue_scripts', 'theme_enqueue');

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
 * Adds a browser class to body tag
 *
 * @param $classes
 * @return array
 */
function browser_body_class($classes) {
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	if($is_lynx) $classes[] = 'lynx';
	elseif($is_gecko) $classes[] = 'gecko';
	elseif($is_opera) $classes[] = 'opera';
	elseif($is_NS4) $classes[] = 'ns4';
	elseif($is_safari) $classes[] = 'safari';
	elseif($is_chrome) $classes[] = 'chrome';
	elseif($is_IE) $classes[] = 'ie';
	else $classes[] = 'unknown';

	if($is_iphone) $classes[] = 'iphone';
	return $classes;
}
add_filter('body_class','browser_body_class');

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