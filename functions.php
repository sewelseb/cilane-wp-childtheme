<?php

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    $parenthandle = 'parent-style'; 
    $theme = wp_get_theme();
    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css', 
        array(),  
        $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array( $parenthandle ),
        $theme->get('Version') 
    );
}

/**
 * Events
 */
add_filter( 'tribe_events_pre_get_posts', 'redirect_from_events' );
function redirect_from_events( $query ) {
  if ( is_user_logged_in() )
    return;
 
  if ( ! $query->is_main_query() || ! $query->get( 'eventDisplay' ) )
    return;
 
  // Look for a page with a slug of "logged-in-users-only".
  $target_page = get_posts( [
    'post_type' => 'page',
    'name' => 'logged-in-users-only'
   ] );
 
  // Use the target page URL if found, else use the home page URL.
  if ( empty( $target_page ) ) {
    $url = get_home_url();
  } else {
    $target_page = current( $target_page );
    $url = get_permalink( $target_page->ID );
  }
   
  // Redirect!
  wp_safe_redirect( $url );
  exit;
}
add_filter( 'posts_where', 'restrict_events', 100 );
function restrict_events( $where_sql ) {
  global $wpdb;
  if ( is_user_logged_in() || ! class_exists( 'Tribe__Events__Main' ) ) {
    return $where_sql;
  }
  return $wpdb->prepare( " $where_sql AND $wpdb->posts.post_type <> %s ", Tribe__Events__Main::POSTTYPE );
}

/**
 * end Events
 */