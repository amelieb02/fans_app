<?php
/**
 * Plugin Name: REST Follow artists
 * Description: A simple plugin to create, read, update and delete the list of artists a user is following.
 * Version: 0.1
 * Author: Nicolas Buc
 * Author URI: http://c-current.com
 * Domain Path: /languages
 * Text Domain: restResponse
 */

/*
Create the following user metadata
nb_artist_total_following_count: total count of users following an artist - to be sent to app with rest API
nb_user_followed_artists_ids: list of artists ids that user is following
nb_artist_user_following_flag: true / 1 if yes, false/0 if no - to be sent to app with rest API
nb_artist_registered_users_following_ids: list of user ids following a specific artist


Load the list of artist with follow information
GET the list of artists with additional fields in the REST API, for each artist
* nb_artist_total_following_count: total number of followers
* nb_artist_user_following_flag: 1 if current user is already following, 0 otherwise

FOLLOW artist in the list of artists (could add follow on a post in future release)
Set Toggle - if not following yet then set, else unset:
* Add the ID of the artist to be followed to nb_user_followed_artists_ids
* Add the ID of the user to nb_artist_registered_users_following_ids
* Increment the nb_artist_total_following_count counter for the artist
* Toggle the nb_artist_user_following_flag to show user is now following this artist

GET the list of artists followed for a specific user
Might need separate API...
GET list of artists followed by user
* get array nb_user_followed_artists_ids
* for each element in array, return details of the artist
OPTION 1: Query in the app: get the array nb_user_followed_artists_ids
OPTION 2: Query on the server side, and an array with all artists details concatenated
OPTION 3:

*/


// insert one value into a meta field of user...
/*
global $wpdb;

$wpbd->insert(
    $wpbd->usermeta,
    array(
// will add to meta of user ID 1
      'user_id'     =>  '1',
      'meta_key'    =>  'nb_artists_followed',
// that user ID 4 is followed
      'meta_value'  => '4',
    ),
    array(
      '%d',
      '%s',
      '%d'
    )
);
*/
// update one value into a meta field of user...
/*
$wpbd->update(
    $wpbd->usermeta,
    array(
    //will update that user ID 4 is followed
      'meta_value'  => '4',
    ),
    array(
    // Where: followed by user ID 1, added to meta_key....
      'user_id'     =>  '1',
      'meta_key'    =>  'nb_artists_followed',
    ),
    array(
      '%d'
    ),
    array(
      '%d',
      '%s'
    )
);
*/
// to delete a value into a metafield of users...
// could use $wpdb->delete();
// or update by overwriting old value with new one


add_action( 'rest_api_init', 'nb_artists_followed_rest_fields' );

/**
 * function for adding fields to the post REST endpoint
 * nb_artist_total_following_count: total count of users following an artist - to be sent to app with rest API
 * nb_user_followed_artists_ids: list of artists ids that user is following
 * nb_artist_user_following_flag: true / 1 if yes, false/0 if no - to be sent to app with rest API
 * nb_artist_registered_users_following_ids: list of user ids following a specific artist
 */
function nb_artists_followed_rest_fields() {
    // schema for the nb_artists_followed field
    $nb_artist_total_following_count_schema = array(
        'description'   => 'Total number of users folowing the artist',
        'type'          => 'integer',
        'context'       =>   array( 'view', 'edit' )
    );

    // registering the nb_artist_total_following_count field
    register_rest_field(
        'user',
        'nb_artist_total_following_count',
        array(
            'get_callback'      => 'nb_get_artist_total_following_count',
            'update_callback'   => 'nb_update_artist_total_following_count',
            'delete_callback'   => null,
            'schema'            => $nb_artist_total_following_count_schema
        )
    );

    $nb_artist_user_following_flag_schema = array(
        'description'   => 'Flag in artist meta describing if current user is following',
        'type'          => 'integer',
        'context'       =>   array( 'view', 'edit' )
    );

    // registering the nb_artist_user_following_flag field
    register_rest_field(
        'user',
        'nb_artist_user_following_flag',
        array(
            'get_callback'      => 'nb_get_artist_user_following_flag',
            'update_callback'   => 'nb_update_artist_user_following_flag',
            'delete_callback'   => null,
            'schema'            => $nb_artist_user_following_flag_schema
        )
    );

    $nb_user_followed_artists_ids_schema = array(
        'description'   => 'List of IDs of the artists followed by user',
        'type'          => 'array',
        'context'       =>   array( 'view', 'edit', 'delete' )
    );

    // registering the nb_user_followed_artists_ids field
    register_rest_field(
        'user',
        'nb_user_followed_artists_ids',
        array(
            'get_callback'      => 'nb_get_user_followed_artists_ids',
            'update_callback'   => 'nb_update_user_followed_artists_ids',
            'delete_callback'   => 'nb_delete_user_followed_artists_ids',
            'schema'            => $nb_user_followed_artists_ids_schema
        )
    );

}

/**
 * Callback for retrieving the total count of users following the artist
 * @param  array            $object         The current user object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return array                           The array wil the IDs of artists followed
 */

function nb_get_artist_total_following_count( $object, $field_name, $request ) {
    return (int) get_user_meta( $object['id'], $field_name, true );
}

/**
 * Callback for updating the total count of users following the artist
 * In most cases will in fact be updated when toggling the user_following_flag
 * @param  mixed     $value          Updated nb_post_total_liked_count
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return integer                   The updated total count of users following the artist
*/

function nb_update_artist_total_following_count( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        return;
    }
    return update_user_meta( $object->ID, $field_name, (int) $value );

}

/**
 * Callback for retrieving the total count of users following the artist
 * @param  array            $object         The current user object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return array                           The array wil the IDs of artists followed
 */

function nb_get_artist_user_following_flag( $object, $field_name, $request ) {
    // could test $object['id'] != 0
    $nb_artist_registered_users_following_ids = null;
    $nb_artist_registered_users_following_ids_meta = null;

// OPTION 1: Get the flag from artist metadata
    // $nb_artist_user_following_flag = get_user_meta( $object['id'], 'nb_artist_user_following_flag', true );

// OPTION 2: Get the list of users following artist, and check if user_id is in the list
    // In first release need to be logged in to like a post
    $user_id = get_current_user_id();
    $nb_artist_registered_users_following_ids_meta = get_user_meta( $object['id'], 'nb_artist_registered_users_following_ids', true );

    // check is $user_id is already in array to return 1, or 0 otherwise
	if ( !is_array( $nb_artist_registered_users_following_ids_meta ) ) {
		$nb_artist_registered_users_following_ids_meta = array();
	}
    if ( in_array( $user_id, $nb_artist_registered_users_following_ids_meta ) ) {
        return 1;
	} else {
		return 0;
	};

}

/**
 * FOLLOW artist in the list of artists (could add follow on a post in future release)
 * Works as a Toggle - if not following yet then set, else unset:
 * Add the ID of the artist to be followed to nb_user_followed_artists_ids
 * Add the ID of the user to nb_artist_registered_users_following_ids
 * Increment the nb_artist_total_following_count counter for the artist
 * Toggle the nb_artist_user_following_flag to show user is now following this artist
 * Callback for updating the total count of users following the artist
 * nb_artist_total_following_count: total count of users following an artist - to be sent to app with rest API
 * nb_user_followed_artists_ids: list of artists ids that user is following
 * nb_artist_user_following_flag: true / 1 if yes, false/0 if no - to be sent to app with rest API
 * nb_artist_registered_users_following_ids: list of user ids following a specific artist
 *
 * @param  mixed     $value          Updated nb_post_total_liked_count
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return integer                   The updated total count of users following the artist
*/

function nb_update_artist_user_following_flag( $value, $object, $field_name ) {
    if ( ! is_numeric( $value ) ) {
//        echo ("nb_update_post_views failed");
        return;
    }

    $nb_artist_total_following_count = 0;
    $nb_artist_user_following_flag = 0;
    $user_id = 0;

    $nb_user_followed_artists_ids_meta = null;
    $nb_artist_registered_users_following_ids_meta = null;

    // In first release need to be logged in to like a post
    $user_id = get_current_user_id();
    // Get the total number of followers for the artist
    $nb_artist_total_following_count = get_user_meta( $object->ID, 'nb_artist_total_following_count', true );

    // get the array with the list of user_ids of users who follow the artist
    $nb_artist_registered_users_following_ids_meta = get_user_meta( $object->ID, 'nb_artist_registered_users_following_ids', true );
    // get the array with the list of user_ids of artists the user is following
    $nb_user_followed_artists_ids_meta = get_user_meta( $user_id, 'nb_user_followed_artists_ids', true );

    // making sure that $nb_artist_registered_users_following_ids_meta is an array - or creating one if it does not exist yet
    if ( !is_array( $nb_artist_registered_users_following_ids_meta) ) {
    	$nb_artist_registered_users_following_ids_meta = array();
    }
    // making sure that $nb_user_followed_artists_ids_meta is an array - or creating one if it does not exist yet
    if ( !is_array( $nb_user_followed_artists_ids_meta) ) {
    	$nb_user_followed_artists_ids_meta = array();
    }
    // OPTION 1: use the value received in the API call as flag to add or remove

    // OPTION 2: Check if the $user_id is already in the list of users who follow the artist,
    //  Toggle: remove if it is or add if it is not
    $user_key = array_search( $user_id, $nb_artist_registered_users_following_ids_meta );
    // if user is already following the artist, unfollow
    if ( $user_key ) {
        // decrease the $nb_artist_total_following_count for the artist
        $nb_artist_total_following_count = ( $nb_artist_total_following_count > 0 ) ? --$nb_artist_total_following_count : 0; // Prevent negative number
        update_user_meta( $object->ID, 'nb_artist_total_following_count', $nb_artist_total_following_count );

        // remove the user id from the artist array with ids of users following the artist
        unset($nb_artist_registered_users_following_ids_meta[$user_key]);
//Alternative        unset($nb_artist_registered_users_following_ids_meta['user-' . $user_key]);
        update_user_meta( $object->ID, 'nb_artist_registered_users_following_ids', $nb_artist_registered_users_following_ids_meta );

        // remove the artist id from the user array with ids of artists followed by the user
//TODO
        // Check that array not empty
        if ($count_artist_key = count($nb_user_followed_artists_ids_meta)) {
            foreach( $nb_user_followed_artists_ids_meta as $nb_artist_meta_key => $nb_artist_meta_value ) {
//            foreach( $nb_user_followed_artists_ids_meta as $nb_artist_meta_key => $nb_artist_meta_value) {
//            for( $artist_key = 0; $artist_key < $count_artist_key; $artist_key++ ) {
                if ($nb_user_followed_artists_ids_meta[$nb_artist_meta_key]['ID'] = $object->ID)
// http://stackoverflow.com/questions/25917122/php-delete-sub-array-from-multidimensional-array-by-sub-array-key-value
//        $artist_key = array_search( $object->ID, $nb_user_followed_artists_ids_meta );
//        unset($nb_user_followed_artists_ids_meta['artist-' . $artist_key]);
//        unset($nb_user_followed_artists_ids_meta['artist-' . $object->ID]);
//        unset($nb_user_followed_artists_ids_meta[$user_key]);
                unset($nb_user_followed_artists_ids_meta[$nb_artist_meta_key]);
            }
        }
        update_user_meta( $user_id, 'nb_user_followed_artists_ids', $nb_user_followed_artists_ids_meta );

        // Unset the user is following the artist flag
        $nb_artist_user_following_flag = 0;
        update_user_meta( $object->ID, 'nb_artist_user_following_flag', $nb_artist_user_following_flag );
//        update_user_meta( $object->ID, $field_name, (int) $nb_artist_user_following_flag );
        return 0;

    } else {

        // increment the $nb_artist_total_following_count for the artist
        ++ $nb_artist_total_following_count;
        update_user_meta( $object->ID, 'nb_artist_total_following_count', $nb_artist_total_following_count );

        // Add user id to array in artist meta with all user_ids following the artist
        $nb_artist_registered_users_following_ids_meta['user-' . $user_id] = $user_id;
        update_user_meta( $object->ID, 'nb_artist_registered_users_following_ids', $nb_artist_registered_users_following_ids_meta );

        // Create sub-array with key artist detail: ID, name, picture
        $artist_profile_array = array();
        $artist_profile_array['ID'] = $object->ID;
        $artist_profile_array['Name'] = $object->user_login;

        // Get artist profile picture
        $artist_profile_thumbnail_id = (int) get_user_meta( $object->ID, 'wp_metronet_image_id', true );
        $artist_profile_array['Profile_picture_ID'] = $artist_profile_thumbnail_id;
        if ( ! $artist_profile_thumbnail_id ) {
//TODO instead of error when no profile picture, use a default picture
          return new WP_Error( 'nb_update_artist_user_following_flag', __( 'Profile picture not found.', 'rest-follow-artist' ), array( 'status' => 404 ) );
        }
        $artist_profile_array['Profile_picture_URL'] = wp_get_attachment_url( $artist_profile_thumbnail_id );


        // Add artist profile array to array with all artist_ids followed by user
//        $nb_user_followed_artists_ids_meta['artist-' . $object->ID] = $object->ID;
//        $nb_user_followed_artists_ids_meta['artist-' . $object->ID] = $artist_profile_array;
//      Push new array into existing list of artist ids
        $nb_user_followed_artists_ids_meta[] = $artist_profile_array;
        update_user_meta( $user_id, 'nb_user_followed_artists_ids', $nb_user_followed_artists_ids_meta );

        // Set the user is following the artist flag
        $nb_artist_user_following_flag = 1;
        update_user_meta( $object->ID, 'nb_artist_user_following_flag', $nb_artist_user_following_flag );
        return 1;

    };

}

/**
 * Callback for retrieving the IDs of artists followed by the current user
 * @param  array            $object         The current user object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return array                           The array wil the IDs of artists followed
 */
function nb_get_user_followed_artists_ids( $object, $field_name, $request ) {
    $user_id = get_current_user_id();
    return get_user_meta( $user_id, 'nb_user_followed_artists_ids', true );
}

/**
 * PLACEHOLDER: Callback for updating the IDs of artists followed by the current user
 * @param  mixed     $value          Updated nb_post_total_liked_count
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return integer                   The updated total count of users following the artist
*/
function nb_update_user_followed_artists_ids( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        return;
    }
    $user_id = get_current_user_id();
    return update_user_meta( $user_id, $field_name, (int) $value );

}

/**
 * PLACEHOLDER: Callback for deleting the IDs of artists followed by the current user
 * @param  mixed     $value          Updated nb_post_total_liked_count
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return integer                   The updated total count of users following the artist
*/
function nb_delete_user_followed_artists_ids( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        return;
    }
    return update_user_meta( $object->ID, $field_name, (int) $value );

}
