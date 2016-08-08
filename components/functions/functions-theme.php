<?php

/**
 * Include required scripts and styles
 */
function ahoy_enquire()
{
	wp_enqueue_script('jquery');

	wp_enqueue_style('ahoy-style', 					get_template_directory_uri() . '/assets/css/main.css');
	wp_enqueue_script('ahoy-script-includes', 		get_stylesheet_directory_uri() . '/assets/js/includes.js', array( 'jquery' ));
	wp_enqueue_script('ahoy-script', 				get_stylesheet_directory_uri() . '/assets/js/main.js', array( 'jquery', 'ahoy-script-includes' ));

	// Localize site directory data to javascript
	$url_arr = array(
		'template_dir' 	=> get_template_directory_uri(),
		'base_dir' 		=> get_site_url(),
        'clean_url'		=> clean_site_url()
	);
	wp_localize_script( 'ahoy-script', 'url_data', $url_arr );
}
add_action('wp_enqueue_scripts', 'ahoy_enquire');

/**
 * Function run on theme activation
 */
function ahoy_init()
{
    //Register theme supports
    add_theme_support('post-thumbnails');
    add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
    load_theme_textdomain( 'tmp', TEMPLATEPATH.'/components/languages' );

	//Register navigation menus
	/*
	register_nav_menus(
		array(
			'header-menu-left' 	=> 'Main Menu Left',
			'header-menu-right' => 'Main Menu Right',
			'footer-bottom' 	=> 'Footer Bottom'
		)
	);
	*/

	//Customise post type support
	/*
	add_post_type_support('post', 'excerpt');
	remove_post_type_support('post', 'custom-fields');
	remove_post_type_support('post', 'page-attributes');
	*/

	//Add image sizes
	/*
	add_image_size('archive-thumb', 500, 250, true);
	*/
}
add_action('init', 'ahoy_init');

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
