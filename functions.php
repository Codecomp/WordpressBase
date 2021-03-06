<?php

// Require Composer's auto loading file
if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once($composer);
}

// Initialise Timber
$timber = new Timber\Timber();
Timber::$dirname = 'layouts';

/**
 * Here's what's happening with these hooks:
 * 1. WordPress detects theme in themes/ahoy
 * 2. When we activate, we tell WordPress that the theme is actually in themes/ahoy/templates
 * 3. When we call get_template_directory() or get_template_directory_uri(), we point it back to themes/ahoy
 *
 * We do this so that the Template Hierarchy will look in themes/ahoy/templates for core WordPress themes
 * But functions.php, style.css, and index.php are all still located in themes/ahoy
 *
 * get_template_directory()   -> /srv/www/example.com/wp-content/themes/ahoy
 * get_stylesheet_directory() -> /srv/www/example.com/wp-content/themes/ahoy
 * locate_template()
 * ├── STYLESHEETPATH         -> /srv/www/example.com/wp-content/themes/ahoy
 * └── TEMPLATEPATH           -> /srv/www/example.com/wp-content/themes/ahoy/templates
 */
add_filter('template', function ($stylesheet) {
    return dirname($stylesheet);
});
add_action('after_switch_theme', function () {
    $stylesheet = get_option('template');
    if (basename($stylesheet) !== 'templates') {
        update_option('template', $stylesheet . '/templates');
    }
});

/**
 * The $function_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
$function_includes = [
    'components/functions/helpers.php',
    'components/functions/reset.php',
    'components/functions/setup.php',
    'components/functions/admin.php',
    'components/functions/theme.php',
    'components/functions/cpt.php',
    'components/functions/email.php',
    'components/functions/newsletter.php',
    'components/functions/social.php',
    'components/functions/woocommerce.php',
    'components/functions/acf.php'
];

array_walk($function_includes, function ($file) {
    locate_template($file, true, true);
});
