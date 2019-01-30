<?php
/**
 * Plugin Name: REST Like Posts
 * Description: Plugin to process user like / unlike for posts (and comments), and update MAYOR information.
 * Version: 0.1
 * Author: Nicolas Buc
 * Author URI: http://c-current.com
 * Domain Path: /languages
 * Text Domain: restResponse
 */

/*
Post: Create the following posts metadata and API fields
nb_post_total_liked_count: total count of likes for the post - to be sent to app with rest API
nb_post_liked_user_ids: array containing the ids of users who like the Post
nb_post_registered_user_liked: whether current user liked the post. true / 1 if yes, false/0 if no

Future release
Comment: Create the following comment metadata
nb_comment_liked_user_ids: list of the ids of users who like the Post
nb_comment_total_liked_count: total count of likes for the post - to be sent to app with rest API
nb_comment_registered_user_liked: true / 1 if yes, false/0 if no - to be sent to app with rest API

User: Create the following user metadata
nb_user_likes_post_ids: list of post IDs a user likes
nb_user_likes_posts_count: total number of posts a user likes
nb_user_total_count_of_likes_received: total count of likes a user has received


Initial GET posts
When user taps an artist, a list of posts from the artist gets displayed
Each post includes the '*liked*' post metadata (2 fields to start): nb_post_total_liked_count and nb_post_registered_user_liked

LIKE post in the app
POST an update to nb_post_registered_user_liked, sending 0 or 1 (opposite of original value)
On the app side
- update nb_post_registered_user_liked to he new value
- update display of nb_post_total_liked_count
On the server side
- update nb_post_registered_user_liked
- update nb_post_total_liked_count
- update nb_post_liked_user_ids by adding / removing the id of the user to the list of ids
Could also update the user meta, but not critical in first place

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


define("MAYOR_LIKE_THRESHOLD", 2);

add_action( 'rest_api_init', 'nb_post_liked_rest_fields' );

/**
 * function for adding fields to the post REST endpoint
 * nb_post_total_liked_count: total count of likes for the post
 * nb_post_liked_user_ids: array with ids of the users who have liked a post
 * nb_post_registered_user_liked: true / 1 if yes - user liked this post, false/0 if no
 */
function nb_post_liked_rest_fields() {

    // schema for the nb_post_total_liked_count field
    $nb_post_total_liked_count_schema = array(
        'description'   => 'Total count of users who likeds this post',
        'type'          => 'integer',
        'context'       =>   array( 'view', 'edit')
    );

    // registering the nb_post_total_liked_count field
    register_rest_field(
        'post',
        'nb_post_total_liked_count',
        array(
            'get_callback'      => 'nb_get_post_total_liked_count',
            'update_callback'   => 'nb_update_post_total_liked_count',
            'delete_callback'   => null,
            'schema'            => $nb_post_total_liked_count_schema
        )
    );

    // schema for the nb_post_liked_user_ids field
    $nb_post_liked_user_ids_schema = array(
        'description'   => 'To record ids when user liked this post',
        'type'          => 'array',
        'context'       =>   array( 'view', 'edit' )
//        'context'       =>   array( 'view', 'edit', 'delete' )
    );

    // registering the nb_post_liked_user_ids field
    register_rest_field(
        'post',
        'nb_post_liked_user_ids',
        array(
            'get_callback'      => 'nb_get_post_liked_user_ids',
            'update_callback'   => 'nb_update_post_liked_user_ids',
            'delete_callback'   => null,
            'schema'            => $nb_post_liked_user_ids_schema
        )
    );

    // schema for the nb_post_registered_user_liked field
    $nb_post_registered_user_liked_schema = array(
        'description'   => 'To Flag when user liked this post',
        'type'          => 'array',
        'context'       =>   array( 'view', 'edit' )
//        'context'       =>   array( 'view', 'edit', 'delete' )
    );

    // registering the nb_post_registered_user_liked
    register_rest_field(
        'post',
        'nb_post_registered_user_liked',
        array(
            'get_callback'      => 'nb_get_post_registered_user_liked',
            'update_callback'   => 'nb_update_post_registered_user_liked',
            'delete_callback'   => null,
//            'delete_callback'   => 'nb_delete_post_registered_user_liked',
            'schema'            => $nb_post_registered_user_liked_schema
        )
    );


}

/**
 * Callback for retrieving nb_post_total_liked_count
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return integer                          The total count of users who liked the post
 */

function nb_get_post_total_liked_count( $object, $field_name, $request ) {
    return (int) get_post_meta( $object['id'], $field_name, true );
}

/**
 * Callback for updating nb_post_total_liked_count
 * @param  mixed     $value          Updated nb_post_total_liked_count
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return integer                   The updated total count of users who liked the post
 */
function nb_update_post_total_liked_count( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        return;
    }
    return update_post_meta( $object->ID, $field_name, (int) $value );
}


/**
 * Callback for retrieving nb_post_liked_user_ids field
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field: nb_get_post_liked_user_ids
 * @param  WP_REST_request  $request        The current request
 * @return array            the array with all the ids of users who liked the post
 */

 function nb_get_post_liked_user_ids( $object, $field_name, $request ) {
      return get_post_meta( $object['id'], $field_name, true );
 }

 /**
  * Callback for updating nb_update_post_liked_user_ids
  * @param  mixed     $value          Updated nb_get_post_liked_user_ids
  * @param  object    $object         The object from the response
  * @param  string    $field_name     Name of the current field: nb_get_post_liked_user_ids
  * @return array                     the array with all the ids of users who liked the post
  */
 function nb_update_post_liked_user_ids( $value, $object, $field_name ) {
     $nb_post_liked_user_ids_meta = null;
     $user_id = 0;
     $user_id = get_current_user_id();
     $nb_post_liked_user_ids_meta = get_post_meta( $object->ID, 'nb_post_liked_user_ids', true );
//     if ( count( $nb_post_liked_user_ids_meta ) != 0 ) {
//         $nb_post_liked_user_ids = $nb_post_liked_user_ids_meta[0];
//     }
     if ( !is_array( $nb_post_liked_user_ids_meta ) ) {
 	     $nb_post_liked_user_ids_meta = array();
     }

    $user_key = array_search( $user_id, $nb_post_liked_user_ids_meta );

    if ( $user_key ) {
        // remove the user id from the array with ids of users who liked
        unset($nb_post_liked_user_ids_meta[$user_key]);
        // update the list of user ids in the post meta
        update_post_meta( $object->ID, 'nb_post_liked_user_ids', $nb_post_liked_user_ids_meta );
        return $nb_post_liked_user_ids_meta;

    } else {
        $nb_post_liked_user_ids_meta['user-' . $user_id] = $user_id;

        update_post_meta( $object->ID, 'nb_post_liked_user_ids', $nb_post_liked_user_ids_meta );
        return $nb_post_liked_user_ids_meta;
    };
}

/**
 * Callback for retrieving nb_post_registered_user_liked to check if user liked a post
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field: nb_get_post_liked_user_ids
 * @param  WP_REST_request  $request        The current request
 * @return integer                          1 if user liked , zero otherwise
 */
function nb_get_post_registered_user_liked( $object, $field_name, $request ) {
    // could test $object['id'] != 0
    $nb_post_liked_user_ids_meta = null;

    // In first release need to be logged in to like a post
    $user_id = get_current_user_id();
    $nb_post_liked_user_ids_meta = get_post_meta( $object['id'], 'nb_post_liked_user_ids', true );

//    if ( count( $nb_post_liked_user_ids_meta ) != 0 ) {
//		$nb_post_liked_user_ids = $nb_post_liked_user_ids_meta[0];
//    }
	if ( !is_array( $nb_post_liked_user_ids_meta ) ) {
		$nb_post_liked_user_ids_meta = array();
	}

    // check is $user_id is already in array to return 1, or 0 otherwise
    if ( in_array( $user_id, $nb_post_liked_user_ids_meta ) ) {
        return 1;
	} else {
		return 0;
	};
}

/**
 * Callback for updating nb_post_registered_user_liked when user liked a post
 * @param  mixed     $value          Updated nb_post_registered_user_liked
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return integer                   1 if user liked , zero otherwise
 */
function nb_update_post_registered_user_liked( $value, $object, $field_name ) {

// Declare variables
    $nb_post_liked_user_ids_meta = null;
    $nb_post_total_liked_count = 0;
    $user_id = 0;
    $nb_post_user_liked = 0;

//    $user_key = null;
//    Not using value in this version, so no need to test
//    if ( ! is_numeric( $value ) ) {
//        echo ("nb_update_post_views failed");
//        return;
//    }

    // In first release need to be logged in to like a post
    $user_id = get_current_user_id();
    // Get the total number of likes for the post
    $nb_post_total_liked_count = get_post_meta( $object->ID, 'nb_post_total_liked_count', true );
    // get the array with the list of user_ids of users who liked the post
    $nb_post_liked_user_ids_meta = get_post_meta( $object->ID, 'nb_post_liked_user_ids', true );
//    if ( count( $nb_post_liked_user_ids_meta ) != 0 ) {
//        $nb_post_liked_user_ids_meta = $nb_post_liked_user_ids_meta[0];
//    }

    // making sure that $nb_post_liked_user_ids is an array - or creating one if it does not exist yet
    if ( !is_array( $nb_post_liked_user_ids_meta) ) {
		$nb_post_liked_user_ids_meta = array();
	}

// OPTION 1: use the value received in the API call as flag to add or remove

// OPTION 2: Check if the $user_id is already in the list, if so remove or add if not
    //  check if user_id is already in the list of users who liked the post
    $user_key = array_search( $user_id, $nb_post_liked_user_ids_meta );
    // if user liked the post already, unlike
    if ( $user_key ) {
        // decrease the nb_user_like_count for the author of the post
        $nb_post_total_liked_count = ( $nb_post_total_liked_count > 0 ) ? --$nb_post_total_liked_count : 0; // Prevent negative number
        update_post_meta( $object->ID, 'nb_post_total_liked_count', $nb_post_total_liked_count );

        // remove the user id from the array with ids of users who liked
        unset($nb_post_liked_user_ids_meta[$user_key]);
        // update the list of user ids in the post meta
        update_post_meta( $object->ID, 'nb_post_liked_user_ids', $nb_post_liked_user_ids_meta );

        // reset the user liked the Post in nb_post_registered_user_liked
        $nb_post_user_liked = 0;
        update_post_meta( $object->ID, $field_name, (int) $nb_post_user_liked );
        //TODO update the REST API field nb_post_registered_user_liked

        // Decrement nb_of_likes received by the author for posts he wrote for this artist
        $nb_return = nb_update_user_likes_received_for_artist(-1, $object);

        return 0;

    } else {

        // increment the nb_user_like_count of users who liked this post
        ++ $nb_post_total_liked_count;
        update_post_meta( $object->ID, 'nb_post_total_liked_count', $nb_post_total_liked_count );

        // Add user id to array with all user_ids, nb_post_liked_user_ids
        $nb_post_liked_user_ids_meta['user-' . $user_id] = $user_id;
        update_post_meta( $object->ID, 'nb_post_liked_user_ids', $nb_post_liked_user_ids_meta );

        // set the user liked the Post in nb_post_registered_user_liked
        $nb_post_user_liked = 1;
        update_post_meta( $object->ID, $field_name, (int) $nb_post_user_liked );
        //TODO update the REST API field nb_post_registered_user_liked

        // Increment nb_of_likes received by the author for posts he wrote for this artist
        $nb_return = nb_update_user_likes_received_for_artist(1, $object);

        return 1;

    };
}

/**
 * Callback for deleting nb_post_registered_user_liked
 * @param  mixed     $value          Increment or decrement the number of likes for the related artist
 * @param  object    $object         The object from the response
 * @param  string    $field_name     Name of the current field
 * @return boolean                   1 if sucessfull, 0 if failed
 */
function nb_delete_post_registered_user_liked( $value, $object, $field_name ) {

    return delete_post_meta( $object->ID, 'nb_post_liked_user_ids' );
//    return delete_post_meta( $object->ID, $field_name );
}

/**
 * Callback for updating nb_user_likes_received_per_artist, the nb of likes the author of the post received for related artist
 * @param  mixed     $value          Updated nb_post_total_liked_count
 * @param  object    $object         The post object
 * @return boolean                   1 if sucessfull, 0 if failed
 */
function nb_update_user_likes_received_for_artist( $value, $object ) {
    // Get the id of the author of the current post
    $author_id = (int) $object->post_author;
//DEBUG
    // $author_id = (int) get_the_author_meta('ID');

    // Get from the post metadata the artist the post is related to
    $nb_post_related_artist_id = get_post_meta( $object->ID, 'nb_post_related_artist_id', true );
    // If the post related artist has not been set, use the post author_id instead
    if(!$nb_post_related_artist_id) {
        $nb_post_related_artist_id = $author_id;
        update_post_meta( $object->ID, 'nb_post_related_artist_id', $author_id );
    }
    // Update the number of likes the author has received for the related artist
    // Get the metadata $nb_user_likes_received_by_artist of the author of the post
    // array artists id as key, and nb of likes as value
    $nb_user_likes_received_per_artist_meta = get_user_meta( $author_id, 'nb_user_likes_received_per_artist', true );
    // Check if array had been set already, if not create it
    if ( !is_array( $nb_user_likes_received_per_artist_meta ) ) {
		$nb_user_likes_received_per_artist_meta = array();
	}
    // Get the metadata $nb_user_mayor_per_artist of the author of the post
    // Array with artist ID as key, and 1 or 0 as value if author is a mayor for that artist
    $nb_user_mayor_per_artist_meta = get_user_meta( $author_id, 'nb_user_mayor_per_artist', true );
    if ( !is_array( $nb_user_mayor_per_artist_meta ) ) {
		$nb_user_mayor_per_artist_meta = array();
	}

    // If existing, increment or decrement based on value
    if ( $value > 0 ) {
        // Increment the number of likes for related artist
        $nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id] =
        ($nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id]>0) ?
        ++ $nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id] : 1;

        //Test and set the mayor flag for the author of the post, for the related artist
        if ($nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id] >= MAYOR_LIKE_THRESHOLD) {
            $nb_user_mayor_per_artist_meta['artist-' . $nb_post_related_artist_id] = 1;
            // update the mayor Flag in user metadata
            update_user_meta( $author_id, 'nb_user_mayor_per_artist', $nb_user_mayor_per_artist_meta );
        }
    } else {
        // Decrement the number of likes for related artist or set to zero
        $nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id] =
        ($nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id]>0) ?
        -- $nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id] : 0;
        //Test and unset the mayor flag for the author of the post, for the related artist
        if ($nb_user_mayor_per_artist_meta['artist-' . $nb_post_related_artist_id] == 1) {
            if ($nb_user_likes_received_per_artist_meta['artist-' . $nb_post_related_artist_id] < MAYOR_LIKE_THRESHOLD) {
            $nb_user_mayor_per_artist_meta['artist-' . $nb_post_related_artist_id] = 0;
            // update the mayor Flag in user metadata
            update_user_meta( $author_id, 'nb_user_mayor_per_artist', $nb_user_mayor_per_artist_meta );
            }
        }

    }
    // update the metadata of the author of the post with the new values
    update_user_meta( $author_id, 'nb_user_likes_received_per_artist', $nb_user_likes_received_per_artist_meta );

    return 1;
}
