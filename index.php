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
    wp_enqueue_style( 'dlinq_custom_css', plugin_dir_url( __FILE__) . 'css/dlinq_custom-main.css');
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
//add_action('save_post', 'detox_json_cache', 10, 3);

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

//search fix for multisite relevanssi
add_action( 'the_post', 'rlv_switch_blog' );
/**
 * Switches the blog if necessary.
 *
 * If the current post blog is different than the current blog, switches the blog.
 * If the blog has been switched, makes sure it's restored first to keep the switch
 * stack clean.
 *
 * @param WP_Post $post The post object.
 */
function rlv_switch_blog( $post ) {
    global $relevanssi_blog_id, $relevanssi_original_blog_id;
    
    if ( ! isset( $post->blog_id ) ) {
        return;
    }

    if ( ! isset( $relevanssi_original_blog_id ) ) {
        $relevanssi_original_blog_id = get_current_blog_id();
    }

    if ( $post->blog_id !== get_current_blog_id() ) {
        if ( isset( $relevanssi_blog_id ) && $relevanssi_blog_id !== $post->blog_id ) {
            restore_current_blog();
        }
        switch_to_blog( $post->blog_id );
        $relevanssi_blog_id = $post->blog_id;
    }
}

add_shortcode( 'relevanssi_restore_blog', 'rlv_restore_blog' );
/**
 * Restores the blog if the blog ID is not the original value.
 */
function rlv_restore_blog() {
    global $relevanssi_blog_id, $relevanssi_original_blog_id;
    if ( $relevanssi_blog_id !== $relevanssi_original_blog_id ) {
        restore_current_blog();
    }
}


add_filter( 'the_content', 'rlv_restore_shortcode', 1 );
 
function rlv_restore_shortcode( $content ) {
     if ( is_search() ) {
        //rlv_restore_blog();
        switch_to_blog(1);
        return $content;
    } else {
        return $content;
    }
}

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

//article custom post type

// Register Custom Post Type article
// Post Type Key: article

function create_article_cpt() {

  $labels = array(
    'name' => __( 'Articles', 'Post Type General Name', 'textdomain' ),
    'singular_name' => __( 'Article', 'Post Type Singular Name', 'textdomain' ),
    'menu_name' => __( 'Article', 'textdomain' ),
    'name_admin_bar' => __( 'Article', 'textdomain' ),
    'archives' => __( 'Article Archives', 'textdomain' ),
    'attributes' => __( 'Article Attributes', 'textdomain' ),
    'parent_item_colon' => __( 'Article:', 'textdomain' ),
    'all_items' => __( 'All Articles', 'textdomain' ),
    'add_new_item' => __( 'Add New Article', 'textdomain' ),
    'add_new' => __( 'Add New', 'textdomain' ),
    'new_item' => __( 'New Article', 'textdomain' ),
    'edit_item' => __( 'Edit Article', 'textdomain' ),
    'update_item' => __( 'Update Article', 'textdomain' ),
    'view_item' => __( 'View Article', 'textdomain' ),
    'view_items' => __( 'View Articles', 'textdomain' ),
    'search_items' => __( 'Search Articles', 'textdomain' ),
    'not_found' => __( 'Not found', 'textdomain' ),
    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
    'featured_image' => __( 'Featured Image', 'textdomain' ),
    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
    'insert_into_item' => __( 'Insert into article', 'textdomain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this article', 'textdomain' ),
    'items_list' => __( 'Article list', 'textdomain' ),
    'items_list_navigation' => __( 'Article list navigation', 'textdomain' ),
    'filter_items_list' => __( 'Filter Article list', 'textdomain' ),
  );
  $args = array(
    'label' => __( 'article', 'textdomain' ),
    'description' => __( '', 'textdomain' ),
    'labels' => $labels,
    'menu_icon' => '',
    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
    'taxonomies' => array('category', 'post_tag'),
    'public' => true,
    'show_ui' => false,
    'show_in_menu' => false,
    'menu_position' => 5,
    'show_in_admin_bar' => false,
    'show_in_nav_menus' => false,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-universal-access-alt',
  );
  register_post_type( 'article', $args );
  
  // flush rewrite rules because we changed the permalink structure
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}
add_action( 'init', 'create_article_cpt', 0 );


function dlinq_doc_change ( $content ) {
    $post_id = get_the_ID();

    if ( get_post_type() == 'avada_portfolio' ) {
        // foreach ( get_the_terms( get_the_ID(), 'portfolio_category' ) as $tax ) {
        //         $tax->name;
        //     }
        $terms = get_the_terms( get_the_ID(), 'portfolio_category' );
        $tech = false;
       foreach ( $terms as $term ) {
            if ( in_array( $term->name, ['Tech Resources'] ) && $tech == false) {
                $tech = true;
           }
        }

        $cats = get_the_terms( get_the_ID(), 'portfolio_category' );
        if($tech == true){
            $alert = "<div class='doc-alert'>📢 We're moving our documentation to a new system on July 1. <a href='https://dlinq.middcreate.net/documentation/'>Check out the new version now</a>. 📢</div>";
            return $alert . $content;

        } else {
            return $content;
        }
       
    }
 
    return $content;
}
add_filter( 'the_content', 'dlinq_doc_change');

 function array_match($needle, $haystack){
        $haystack =  (array) $haystack;
        $needle =  (array) $needle;
        foreach ($needle as $key => $value) {
            if( trim($value) != trim($haystack[$key]) ){
                return false;
            }
        }
        return true;
    }