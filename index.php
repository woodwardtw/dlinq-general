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



//LIMIT EMAILS TO MIDDLEBURY DOMAIN FOR CATALOG COURSE REQUEST
//from https://gravitywiz.com/banlimit-email-domains-for-gravity-form-email-fields/
class GW_Email_Domain_Validator {

    private $_args;

    function __construct( $args ) {

        $this->_args = wp_parse_args( $args, array(
            'form_id'            => false,
            'field_id'           => false,
            'domains'            => false,
            // translators: placeholder is a domain for emails that are not allowed.
            'validation_message' => __( 'Sorry, <strong>%s</strong> email accounts are not eligible for this form.' ),
            'mode'               => 'ban', // also accepts "limit"
        ) );

        // convert field ID to an array for consistency, it can be passed as an array or a single ID
        if ( $this->_args['field_id'] && ! is_array( $this->_args['field_id'] ) ) {
            $this->_args['field_id'] = array( $this->_args['field_id'] );
        }

        $form_filter = $this->_args['form_id'] ? "_{$this->_args['form_id']}" : '';

        add_filter( "gform_validation{$form_filter}", array( $this, 'validate' ) );

    }

    function validate( $validation_result ) {

        $form = $validation_result['form'];

        foreach ( $form['fields'] as &$field ) {

            // if this is not an email field, skip
            if ( RGFormsModel::get_input_type( $field ) != 'email' ) {
                continue;
            }

            // if field ID was passed and current field is not in that array, skip
            if ( $this->_args['field_id'] && ! in_array( $field['id'], $this->_args['field_id'] ) ) {
                continue;
            }

            $page_number = GFFormDisplay::get_source_page( $form['id'] );
            if ( $page_number > 0 && $field->pageNumber != $page_number ) {
                continue;
            }

            if ( GFFormsModel::is_field_hidden( $form, $field, array() ) ) {
                continue;
            }

            $domain = $this->get_email_domain( $field );

            // if domain is valid OR if the email field is empty, skip
            if ( $this->is_domain_valid( $domain ) || empty( $domain ) ) {
                continue;
            }

            $validation_result['is_valid'] = false;
            $field['failed_validation']    = true;
            $field['validation_message']   = sprintf( $this->_args['validation_message'], $domain );

        }

        $validation_result['form'] = $form;
        return $validation_result;
    }

    function get_email_domain( $field ) {
        $email = explode( '@', rgpost( "input_{$field['id']}" ) );
        return trim( rgar( $email, 1 ) );
    }

    function is_domain_valid( $domain ) {

        $mode   = $this->_args['mode'];
        $domain = strtolower( $domain );

        foreach ( $this->_args['domains'] as $_domain ) {

            $_domain = strtolower( $_domain );

            $full_match   = $domain == $_domain;
            $suffix_match = strpos( $_domain, '.' ) === 0 && $this->string_ends_with( $domain, $_domain );
            $has_match    = $full_match || $suffix_match;

            if ( $mode == 'ban' && $has_match ) {
                return false;
            } elseif ( $mode == 'limit' && $has_match ) {
                return true;
            }
        }

        return $mode == 'limit' ? false : true;
    }

    function string_ends_with( $string, $text ) {

        $length      = strlen( $string );
        $text_length = strlen( $text );

        if ( $text_length > $length ) {
            return false;
        }

        return substr_compare( $string, $text, $length - $text_length, $text_length ) === 0;
    }

}

class GWEmailDomainControl extends GW_Email_Domain_Validator { }

new GW_Email_Domain_Validator( array(
    'form_id'            => 15,
    'field_id'           => 3,
    'domains'            => array( 'middlebury.edu' ),
    'validation_message' => __( 'Sorry. You have to use a Middlebury email domain.' ),
    'mode'               => 'limit',
) );
