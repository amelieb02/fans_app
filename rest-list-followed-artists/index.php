<?php
/**
 * Plugin Name: REST List Followed Artists
 * Description: A simple plugin to create, read, update and delete the list of artists a user is following.
 * Version: 0.1
 * Author: Nicolas Buc
 * Author URI: http://c-current.com
 * Domain Path: /languages
 * Text Domain: restResponse
 */

 /**
 * This is our callback function that embeds our list in a WP_REST_Response
 */
function nb_get_artist_lists() {
    // rest_ensure_response() wraps the data we want to return into a WP_REST_Response, and ensures it will be properly returned.
    $nb_user_followed_artists_ids_meta = null;
    // In first release need to be logged in to like a post
    $user_id = get_current_user_id();
    // get the array with the list of user_ids of users who liked the post
    $nb_user_followed_artists_ids_meta = get_user_meta( $user_id, 'nb_user_followed_artists_ids', true );
    // Section from the official API documentation
//    $collection = array();
//    foreach( $nb_user_followed_artists_ids_meta as $nb__artists_id ) {
//        $itemdata = $this->prepare_item_for_response( $nb__artists_id, $request );
//        $collection[] = $this->prepare_response_for_collection( $itemdata );
//    }
//    $count = count($collection)
    return rest_ensure_response( $nb_user_followed_artists_ids_meta );
}

/**
 * This function is where we register our routes for our example endpoint.
 */
function nb_register_artist_lists_routes() {
    $version = '1';
    $namespace = 'artists_followed/v' . $version;
    $base = '/lists';
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( $namespace, '/lists', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'nb_get_artist_lists',
    ) );
}

add_action( 'rest_api_init', 'nb_register_artist_lists_routes' );
