<?php
/**
 * Plugin Name: REST Users Endpoint Modifier
 * Description: Plugin to modify the response of the REST API plugin for C/Current
 * Version: 0.1
 * Author: Nicolas Buc
 * Author URI: http://c-current.com
 * Domain Path: /languages
 * Text Domain: restResponse
 */



add_action( 'rest_api_init', 'nb_add_custom_rest_user_endpoint_fields' );

function nb_add_custom_rest_user_endpoint_fields() {
    // schema for the profile picture ID
    $nb_user_profile_picture_schema = array(
        'description'   => 'user profile picture ID and URL',
        'type'          => 'string',
        'context'       =>   array( 'view' )
    );

    // registering the nb_rest_get_user_field for the profile picture
    register_rest_field(
        'user',
        'user_profile_picture',
        array(
            'get_callback'      => 'nb_rest_get_user_profile_picture',
            'update_callback'   => null,
            'schema'            => $nb_user_profile_picture_schema
        )
    );




    // schema for nb_user_mayor_per_artist field
    $nb_user_mayor_per_artist_schema = array(
        'description'   => 'Array containing the list of artists for which the user is mayor',
        'type'          => 'array',
        'context'       =>   array( 'view' )
    );

    // registering the nb_user_mayor_per_artist field
    register_rest_field(
        'user',
        'nb_user_mayor_per_artist',
        array(
            'get_callback'      => 'nb_get_user_mayor_per_artist',
            'update_callback'   => null,
            'schema'            => $nb_user_mayor_per_artist_schema
        )
    );


    // schema for nb_user_likes_received_per_artist field
    $nb_user_likes_received_per_artist_schema = array(
        'description'   => 'Array containing the list of artists about which the posts of the user were liked',
        'type'          => 'array',
        'context'       =>   array( 'view' )
    );

    // registering the nb_user_likes_received_per_artist field
    register_rest_field(
        'user',
        'nb_user_likes_received_per_artist',
        array(
            'get_callback'      => 'nb_get_user_likes_received_per_artist',
            'update_callback'   => null,
            'schema'            => $nb_user_likes_received_per_artist_schema
        )
    );

}


/**
 * OLD Code kept as an example for potential ideas
 * @param  integer          $user_id        The current user object
 * @param  string           $field_name     The name of the meta to retrieve
 * @param  WP_REST_request  $request        The current request
 * @return integer | null   The ID of the picture of the user or null if none
 */
 /*
function nb_rest_get_user_field( $user, $field_name, $request ) {
    $user_id = $user[ 'id' ];
    $user_id_pointeur = $user->id;
      return array (
      'id-crochet' => $user_id,
      'id-pointeur' => $user_id_pointeur

    );
    }
    */

    /*
    if ( ! $user_id ) {
			return new WP_Error( 'rest_users_enpoint_response_modifier', __( 'User not found.', 'rest_users_enpoint_response_modifier' ), array( 'status' => 404 ) );
		}
    return get_user_meta( $user_id, 'wp_metronet_image_id', true );
    */
    /*
    $user = get_user_by( 'id', (int) $request['parent_id'] );
    From docs :
    $cu = wp_get_current_user();

    From metronet-profile-picture.php
    $user_id = $data[ 'id' ];
		$user = get_user_by( 'id', $user_id );
    */



/**
 * Callback for retrieving user profile picture ID
 * @param  integer          $user           The current user object
 * @param  string           $field_name     The name of the meta to retrieve
 * @param  WP_REST_request  $request        The current request
 * @param  boolean          $single         return only one value or more
 * @return string | null   The URL of the picture of the user or null if none
 */
function nb_rest_get_user_profile_picture ( $user, $field_name, $request ) {
    $user_id = $user[ 'id' ];
    if ( ! $user_id ) {
			return new WP_Error( 'rest_users_enpoint_response_modifier', __( 'User not found.', 'rest_users_enpoint_response_modifier' ), array( 'status' => 404 ) );
		}
    $user_profile_thumbnail_id = get_user_meta( $user_id, 'wp_metronet_image_id', true );
    if ( ! $user_profile_thumbnail_id ) {
      return new WP_Error( 'rest-users-endpoint-profile-picture', __( 'Profile picture not found.', 'rest-users-endpoint' ), array( 'status' => 404 ) );
    }


    $attachment_url = wp_get_attachment_url( $user_profile_thumbnail_id );
    return array(
      'user_profile_picture_ID' => $user_profile_thumbnail_id,
      'user_profile_picture_URL' => $attachment_url,
    );


}

/**
* Callback for retrieving nb_user_mayor_per_artist
* @param  array             $user         The current user object
* @param  string            $field_name   The name of the field
* @param  WP_REST_request   $request      The current request
* @return array                           nb_user_mayor_per_artist
*/

function nb_get_user_mayor_per_artist( $user, $field_name, $request ) {
    $user_id = $user['id'];

    $nb_user_mayor_per_artist_meta = get_user_meta( $user_id, $field_name, true );
    if ( !is_array( $nb_user_mayor_per_artist_meta ) ) {
		$nb_user_mayor_per_artist_meta = array();
	}
    return $nb_user_mayor_per_artist_meta;

    // cleaner format
    // return get_user_meta( $user['id'], $field_name, true );
}

/**
* Callback for retrieving nb_user_likes_received_per_artist
* @param  array             $user         The current user object
* @param  string            $field_name   The name of the field
* @param  WP_REST_request   $request      The current request
* @return array                           nb_user_mayor_per_artist
*/

function nb_get_user_likes_received_per_artist( $user, $field_name, $request ) {

    $nb_user_likes_received_per_artist_meta = get_user_meta( $user['id'], $field_name, true );
    if ( !is_array( $nb_user_likes_received_per_artist_meta ) ) {
		$nb_user_likes_received_per_artist_meta = array();
	}
    return $nb_user_likes_received_per_artist_meta;
}
