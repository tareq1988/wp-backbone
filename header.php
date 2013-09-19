<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'wedevs' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="page" class="hfeed site">
        <?php do_action( 'before' ); ?>
        <header id="masthead" class="site-header" role="banner">
            <div class="container">
                <div class="row">
                    <div class="span12 header">
                        <hgroup>
                            <h1 class="site-title"><a href="<?php echo home_url( '#/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?> <small> - <?php bloginfo( 'description' ); ?></small></a></h1>
                        </hgroup>
                        <span class="loading"><span class="image"></span>Loading...</span>
                    </div><!-- .span12 -->
                </div><!-- .row -->
            </div><!-- .container -->
        </header><!-- #masthead .site-header -->

        <div id="main" class="site-main">
            <div class="container content-wrap">
                <div class="row">
