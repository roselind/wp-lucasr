<?php
/**
 * Functions and definitions.
 *
 * @package WordPress
 * @subpackage lucasr
 */


function lucasr_setup() {
    load_theme_textdomain( 'lucasr', get_template_directory() . '/languages' );

    add_theme_support( 'post-thumbnails' );
    add_image_size( 'hero-image-desktop', 960, 400, true );
    add_image_size( 'hero-image-tablet', 960, 400, true );
    add_image_size( 'hero-image-phone', 960, 400, true );
    set_post_thumbnail_size( 960, 400 );

    add_post_type_support( 'page', 'excerpt' );
}
add_action( 'after_setup_theme', 'lucasr_setup' );


function lucasr_wp_title( $title, $sep ) {
    global $paged, $page;

    if ( is_feed() )
        return $title;

    // Add the site name.
    $title .= get_bloginfo( 'name' );

    // Add the site description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        $title = "$title $sep $site_description";

    // Add a page number if necessary.
    if ( $paged >= 2 || $page >= 2 )
        $title = "$title $sep " . sprintf( __( 'Page %s', 'lucasr' ), max( $paged, $page ) );

    return $title;
}
add_filter( 'wp_title', 'lucasr_wp_title', 10, 2 );


function lucasr_scripts_styles() {
    wp_enqueue_style( 'lucasr-boostrap', get_template_directory_uri() . '/css/bootstrap.min.css' );
    wp_enqueue_style( 'lucasr-boostrap-responsive', get_template_directory_uri() . '/css/bootstrap-responsive.min.css', array( 'lucasr-boostrap' ) );
    wp_enqueue_style( 'lucasr-style', get_stylesheet_uri(), array( 'lucasr-boostrap', 'lucasr-boostrap-responsive' ) );

    wp_enqueue_script( 'lucasr-picturefill', get_template_directory_uri() . '/js/picturefill.min.js', array(), false, true );
    wp_enqueue_script( 'lucasr-typekit', 'http://use.typekit.net/tww7dlq.js', array(), false, true );
}
add_action( 'wp_enqueue_scripts', 'lucasr_scripts_styles' );


function lucasr_home_pagesize( $query ) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( is_home() ) {
        $query->set( 'posts_per_page', 1 );
        return;
    }
}
add_action( 'pre_get_posts', 'lucasr_home_pagesize', 1 );


function lucasr_delete_cache_on_new_post() {
     delete_transient( 'recent_posts' );
     delete_transient( 'popular_posts' );
}
add_action( 'publish_post', 'lucasr_delete_cache_on_new_post' );


function lucasr_custom_image_sizes( $sizes ) {
        unset( $sizes['medium'] );
        unset( $sizes['large'] );

        $my_img_sizes = array(
            "hero-image" => __( 'Hero', 'lucasr' )
        );

        $new_img_sizes = array_merge( $sizes, $my_img_sizes );
        return $new_img_sizes;
}
add_filter('image_size_names_choose', 'lucasr_custom_image_sizes');


function lucasr_the_post_thumbnail_caption() {
    $thumbnail_id = get_post_thumbnail_id();
    if ( $thumbnail_id !== null )
        echo get_post( $thumbnail_id )->post_excerpt;
    else
        _e( 'No caption', 'lucasr' );
}


function lucasr_header_title() {
    if ( is_home() || is_front_page() )
        _e( 'Hi There!', 'lucasr' );
    elseif ( is_page( 'about' ) )
        _e( 'About', 'lucasr' );
    else
        _e( 'Blog', 'lucasr' );
}


function lucasr_get_recent_posts() {
    $recent_posts = get_transient( 'recent_posts' );

    if ( $recent_posts === false ) {
        $recent_posts = new WP_Query( array(
            'posts_per_page' => 5,
            'offset' => 1,
            'order' => 'DESC',
            'orderby' => 'date'
        ) );

        set_transient( 'recent_posts', $recent_posts, 24 * HOUR_IN_SECONDS );
    }

    return $recent_posts;
}


function lucasr_get_popular_posts() {
    $popular_posts = get_transient( 'popular_posts' );

    if ( $popular_posts === false ) {
        $popular_posts = new WP_Query( array(
            'meta_key' => 'post_views_count',
            'orderby' => 'meta_value_num',
            'posts_per_page' => 5
        ) );

        set_transient( 'popular_posts', $popular_posts, 24 * HOUR_IN_SECONDS );
    }

    return $popular_posts;
}
?>