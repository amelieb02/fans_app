<?php
/**
 * Plugin Name: REST Post Response Modifier
 * Description: Simple plugin to modify the response of the post REST API plugin, and add metadata.
 * Version: 0.1
 * Author: Nicolas Buc
 * Author URI: http://imbilal.com
 * Domain Path: /languages
 * Text Domain: restResponse
 */



add_action( 'rest_api_init', 'nb_add_custom_rest_fields' );

function nb_add_custom_rest_fields() {
    // schema for the nb_author_name field
    $nb_author_name_schema = array(
        'description'   => 'Name of the post author',
        'type'          => 'string',
        'context'       =>   array( 'view' )
    );

    // registering the nb_author_name field
    register_rest_field(
        'post',
        'nb_author_name',
        array(
            'get_callback'      => 'nb_get_author_name',
            'update_callback'   => null,
            'schema'            => $nb_author_name_schema
        )
    );

    // schema for nb_post_image_src field
    $nb_post_image_src_schema = array(
        'description'   => 'Post image source URL',
        'type'          => 'string',
        'context'       =>   array( 'view' )
    );

    // Registering the nb_featured_image_src field
    register_rest_field(
      'post',
      'nb_featured_image_src',
      array(
        'get_callback'    => 'nb_get_image_src',
        'update_callback' => null,
        'schema'          => $nb_post_image_src_schema
      )
    );

//TODO implement nb_post_views
    // schema for nb_post_views field
    $nb_post_views_schema = array(
        'description'   => 'Post views count',
        'type'          => 'integer',
        'context'       => array( 'view', 'edit' )
    );

    // registering the nb_post_views field
    register_rest_field(
        'post',
        'nb_post_views',
        array(
            'get_callback'      => 'nb_get_post_views',
            'update_callback'   => 'nb_update_post_views',
            'schema'            => $nb_post_views_schema
        )
    );

    // schema for nb_post_number_of_comments field
    $nb_post_number_of_comments_schema = array(
        'description'   => 'Post number of comments',
        'type'          => 'integer',
        'context'       => array( 'view' )
    );

    // registering the nb_post_number_of_comments field
    register_rest_field(
        'post',
        'nb_post_number_of_comments',
        array(
            'get_callback'      => 'nb_get_post_number_of_comments',
            'update_callback'   => null,
            'schema'            => $nb_post_number_of_comments_schema
        )
    );

    // schema for nb_post_related_artist_id field
    $nb_post_related_artist_id_schema = array(
        'description'   => 'Id of the artist the post is about',
        'type'          => 'integer',
        'context'       => array( 'view', 'edit' )
    );

    // registering the nb_post_number_of_comments field
    register_rest_field(
        'post',
        'nb_post_related_artist_id',
        array(
            'get_callback'      => 'nb_get_post_related_artist_id',
            'update_callback'   => 'nb_update_post_related_artist_id',
            'schema'            => $nb_post_related_artist_id_schema
        )
    );
}

/**
 * Callback for retrieving author name
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return string                           The name of the author
 */
function nb_get_author_name( $object, $field_name, $request ) {
    return get_the_author_meta( 'display_name', $object['author'] );
}

/**
 * Callback for retrieving image source
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return string                           The image source URI
 */
function nb_get_image_src( $object, $field_name, $request ) {
  $featured_img_array = wp_get_attachment_image_src( $object[ 'featured_media'], 'full', true);
  return $featured_img_array[0];
}

/**
* Callback for retrieving post number of comments
* @param  array             $object         The current post object
* @param  string            $field_name     The name of the field
* @param  WP_REST_request   $request        The current request
* @return integer                           Post views count
*/
function nb_get_post_number_of_comments( $object, $field_name, $request ) {
    return (int) get_comments_number( );

}


/**
* Callback for retrieving post views count
* @param  array             $object         The current post object
* @param  string            $field_name     The name of the field - this is captured whatever the position in the declaration...
* @param  WP_REST_request   $request        The current request
* @return integer                           Post views count
*/
function nb_get_post_views( $object, $field_name, $request ) {

    return (int) get_post_meta( $object['id'], $field_name, true );

}

/**
* Callback for updating post views count
* @param  mixed     $value          Post views count
* @param  object    $object         The object from the response
* @param  string    $field_name     Name of the current field
* @return bool|int
*/
function nb_update_post_views( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        echo ("nb_update_post_views failed");
        return;
    }

    return update_post_meta( $object->ID, $field_name, (int) $value );
}

/**
* Callback for retrieving post related artist id
* @param  array             $object         The current post object
* @param  string            $field_name     The name of the field - this is captured whatever the position in the declaration...
* @param  WP_REST_request   $request        The current request
* @return integer                           post related artist id
*/
function nb_get_post_related_artist_id( $object, $field_name, $request ) {

    return (int) get_post_meta( $object['id'], $field_name, true );

}

/**
* Callback for updating post related artist id
* @param  mixed     $value          Post views count
* @param  object    $object         The object from the response
* @param  string    $field_name     Name of the current field
* @return bool|int
*/
function nb_update_post_related_artist_id( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        echo ("nb_update_post_related_artist_id failed");
        return;
    }

    return update_post_meta( $object->ID, $field_name, (int) $value );
}
