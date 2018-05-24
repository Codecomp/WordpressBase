<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie i" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>><![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta http-equiv="content-language" content="<?php echo get_bloginfo('language'); ?>">
    <meta content="True" name="HandheldFriendly">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title><?php wp_title(); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#000000">
    <meta name="msapplication-TileColor" content="#000000">
    <meta name="theme-color" content="#000000">

    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<div class="site-wrap">

    <header class="header" role="banner">

    </header>

    <div class="page-wrap">
