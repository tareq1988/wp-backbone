<?php

/**
 * _bootstraps - 2013 functions and definitions
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */

/**
 * Bootstrap Theme Class
 *
 * @package _bootstraps - 2013 1.0
 */
class WeDevs_Bootstrap {

    function __construct() {
        add_action( 'after_setup_theme', array($this, 'setup_theme') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_action( 'widgets_init', array($this, 'widgets_init') );
        remove_action( 'wp_head', 'wp_generator' );
    }

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which runs
     * before the init hook. The init hook is too late for some features, such as indicating
     * support post thumbnails.
     *
     * @package _bootstraps - 2013 1.0
     */
    function setup_theme() {

        /**
         * Make theme available for translation
         * Translations can be filed in the /languages/ directory
         * If you're building a theme based on Tareq\'s Planet - 2013, use a find and replace
         * to change 'tp' to the name of your theme in all the template files
         */
        load_theme_textdomain( 'wedevs', get_template_directory() . '/languages' );

        /**
         * Add default posts and comments RSS feed links to head
         */
        add_theme_support( 'automatic-feed-links' );

        /**
         * Enable support for Post Thumbnails
         */
        add_theme_support( 'post-thumbnails' );

        /**
         * This theme uses wp_nav_menu() in one location.
         */
        register_nav_menus( array(
            'primary' => __( 'Primary Menu', 'wedevs' ),
        ) );

        /**
         * Add support for the Aside Post Formats
         */
        add_theme_support( 'post-formats', array('aside',) );
    }

    /**
     * Enqueue scripts and styles
     */
    function enqueue_scripts() {
        // cache the directory path, maybe helpful?
        $template_directory = get_template_directory_uri();

        // all styles
        wp_enqueue_style( 'bootstrap', $template_directory . '/css/bootstrap.css' );
        wp_enqueue_style( 'style', $template_directory . '/css/style.css' );

        // all scripts
        wp_enqueue_script( 'wp-api');
        wp_enqueue_script( 'backbone');
        wp_enqueue_script( 'underscore');

        // comment reply on single posts
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        // wp_enqueue_script( 'jquery-prettyphoto', $template_directory . '/js/jquery.prettyPhoto.js', array('jquery', 'theme-script'), '20120202', true );
        wp_enqueue_script( 'theme-script', $template_directory . '/js/scripts.js', array('jquery'), '20120206', true );
        wp_localize_script( 'theme-script', 'wedevsBackbone', array( 'base' => home_url( '/' ) ) );
    }

    /**
     * Register widgetized area and update sidebar with default widgets
     *
     * @package _bootstraps - 2013 1.0
     */
    function widgets_init() {
        register_sidebar( array(
            'name' => __( 'Sidebar', 'wedevs' ),
            'id' => 'sidebar-1',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ) );
    }

}

$wedevs_bootstrap = new WeDevs_Bootstrap();
