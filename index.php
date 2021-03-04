<?php 
/*
Plugin Name: DLINQ general modifications
Plugin URI:  https://github.com/
Description: For stuff that's magical
Version:     1.0
Author:      DLINQ
Author URI:  http://dlinq.middcreate.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('wp_enqueue_scripts', 'dlinq_custom_load_scripts');

function prefix_load_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('dlinq_custom-main-js', plugin_dir_url( __FILE__) . 'js/dlinq_custom-main.js', $deps, $version, $in_footer); 
    wp_enqueue_style( 'dlinq_custom-main-css', plugin_dir_url( __FILE__) . 'css/dlinq_custom-main.css');
}



//change sort order of royal slider plugin for events to go from oldest to youngest rather than reverse
// Add to your theme functions.php from http://help.dimsemenov.com/kb/wordpress-royalslider-advanced/wp-modifying-order-of-posts-in-slider
function old_events_custom_query($args, $index) {
    // $args is WP_Query arguments object. 
    // feel free to change it here
    $args['orderby'] = 'date';
    $args['order'] = 'ASC';

    // $index is an ID of slider that you're modifying

    return $args;
}
add_filter('new_royalslider_posts_slider_query_args', 'old_events_custom_query', 10, 2);



//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
