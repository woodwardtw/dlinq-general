<?php 
/*
Plugin Name: DLINQ general 
Plugin URI:  https://github.com/
Description: For stuff that's practical
Version:     1.0
Author:      DLINQ
Author URI:  https://dlinq.middcreate.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('wp_enqueue_scripts', 'dlinq_general_load_scripts');

function dlinq_general_load_scripts() { 
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('dlinq_general-main-js', plugin_dir_url( __FILE__) . 'js/dlinq_general-main.js', $deps, $version, $in_footer); 
    //wp_enqueue_style( 'prefix-main-css', plugin_dir_url( __FILE__) . 'css/prefix-main.css');
}

//change sort order of royal slider plugin for events to go from oldest to youngest rather than reverse
// Add to your theme functions.php from http://help.dimsemenov.com/kb/wordpress-royalslider-advanced/wp-modifying-order-of-posts-in-slider
function old_events_custom_query($args, $index) {
    // $args is WP_Query arguments object. 
    // feel free to change it here
    $args['orderby'] = 'date';
    $args['order'] = 'ASC';
    $index = 1;//slider ID

    // $index is an ID of slider that you're modifying

    return $args;
}
add_filter('new_royalslider_posts_slider_query_args', 'old_events_custom_query', 10, 2);



//Digital detox 2022


// Listen for publishing of a new post from https://davidwalsh.name/wordpress-publish-post-hook
function detox_json_cache($post_id, $post, $update) {
   //write_log(__LINE__);
  if($post->post_type === 'post') {
   $url = 'https://dlinq.middcreate.net/wp-json/wp/v2/posts/?categories=391&per_page=50';
   $file = file_get_contents($url);
   $destination = get_home_path();
   file_put_contents($destination . 'detox-2022/json/detox.json', $file);
  }
}
add_action('save_post', 'detox_json_cache', 10, 3);

function detox_added_page_content ( $content ) {
   global $post;
   $take_action = '';
   $keep_reading = '';
    if ( get_field('take_action', $post->ID) ) {
         $take_action = '<h2>Take Action</h2>' . get_field('take_action', $post->ID);
    }
    if ( get_field('keep_reading', $post->ID) ) {
         $keep_reading = '<h2>Keep Reading</h2>' .get_field('keep_reading', $post->ID);
    }
 
    return $content . '<div class="hide">' . $take_action . $keep_reading . '</div>';
}
add_filter( 'the_content', 'detox_added_page_content');

function detox_author_to_rest_api($response, $post, $request) {
 
    if (isset($post)) {
        $author_id = get_fields($post->post_author);
        $response->data['detox_author'] = get_the_author_meta('display_name', $author_id);
    }
    return $response;
}
add_filter('rest_prepare_post', 'detox_author_to_rest_api', 10, 3);//if you leave it as post, it's just for posts




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
