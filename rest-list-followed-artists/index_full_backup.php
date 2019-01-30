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

// class Slug_Custom_Route extends WP_REST_Controller {

add_action( 'rest_api_init', 'nb_register_routes' );
  /**
   * Register the routes for the objects of the controller.
   */
  public function nb_register_routes() {
    $version = '1';
    $namespace = 'artists_followed/v' . $version;
    $base = 'lists';
    register_rest_route( $namespace, '/' . $base, array(
//      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_list_artists_followed' ),
//        'permission_callback' => array( $this, 'get_items_permissions_check' ),
//        'args'            => array( ),
//      ),
//      array(
//        'methods'         => WP_REST_Server::CREATABLE,
//        'callback'        => array( $this, 'create_item' ),
//        'permission_callback' => array( $this, 'create_item_permissions_check' ),
//        'args'            => $this->get_endpoint_args_for_item_schema( true ),
//      ),

    ) );
    /*
    register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_item' ),
        'permission_callback' => array( $this, 'get_item_permissions_check' ),
        'args'            => array(
          'context'          => array(
            'default'      => 'view',
          ),
        ),
      ),
      array(
        'methods'         => WP_REST_Server::EDITABLE,
        'callback'        => array( $this, 'update_item' ),
        'permission_callback' => array( $this, 'update_item_permissions_check' ),
        'args'            => $this->get_endpoint_args_for_item_schema( false ),
      ),
      array(
        'methods'  => WP_REST_Server::DELETABLE,
        'callback' => array( $this, 'delete_item' ),
        'permission_callback' => array( $this, 'delete_item_permissions_check' ),
        'args'     => array(
          'force'    => array(
            'default'      => false,
          ),
        ),
      ),
    ) );
    */

    /*
    register_rest_route( $namespace, '/' . $base . '/schema', array(
      'methods'         => WP_REST_Server::READABLE,
      'callback'        => array( $this, 'get_public_item_schema' ),
    ) );
    */
  }

  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_list_artists_followed( $request ) {
    $nb_user_followed_artists_ids_meta = null;

    // In first release need to be logged in to like a post
    $user_id = get_current_user_id();

    // get the array with the list of user_ids of users who liked the post
    $nb_user_followed_artists_ids_meta = get_user_meta( $user_id, 'nb_user_followed_artists_ids', true );
    // making sure that $nb_user_followed_artists_ids_meta is an array - or creating one if it does not exist yet
    if ( !is_array( $nb_user_followed_artists_ids_meta) ) {
          //If not an array, user is not following any artist: return
      	  $nb_user_followed_artists_ids_meta = array();
    }

    // Create and Populate the response
    // Might need to mirror more closely the class-wp-rest-users-controller.php
    $collection = array();
    foreach( $nb_user_followed_artists_ids_meta as $nb__artists_id ) {
        $itemdata = $this->prepare_item_for_response( $nb__artists_id, $request );
        $collection[] = $this->prepare_response_for_collection( $itemdata );
    }

//    return new WP_REST_Response( $collection, 200 );
    return rest_ensure_response( $collection );
  }

  /**
   * Get all users
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
 /** Function from the class-wp-rest-users-controller.php
  * Might need to replicate more closely in customized get_list_artists_followed
  public function get_items( $request ) {

      $prepared_args = array();
      $prepared_args['exclude'] = $request['exclude'];
      $prepared_args['include'] = $request['include'];
      $prepared_args['order'] = $request['order'];
      $prepared_args['number'] = $request['per_page'];
      $orderby_possibles = array(
          'id'              => 'ID',
          'include'         => 'include',
          'name'            => 'display_name',
          'registered_date' => 'registered',
          'slug'            => 'user_nicename',
          'email'           => 'user_email',
          'url'             => 'user_url',
      );
      $prepared_args['orderby'] = $orderby_possibles[ $request['orderby'] ];
      $prepared_args['search'] = $request['search'];
      $prepared_args['role__in'] = $request['roles'];
      /**
       * Filter arguments, before passing to WP_User_Query, when querying users via the REST API.
       *
       * @see https://developer.wordpress.org/reference/classes/wp_user_query/
       *
       * @param array           $prepared_args Array of arguments for WP_User_Query.
       * @param WP_REST_Request $request       The current request.
       */
       /** Still part of get_items Function from the class-wp-rest-users-controller.php
        * Might need to replicate more closely in customized get_list_artists_followed
      $prepared_args = apply_filters( 'rest_user_query', $prepared_args, $request );

      $query = new WP_User_Query( $prepared_args );

      $users = array();
      foreach ( $query->results as $user ) {
          $data = $this->prepare_item_for_response( $user, $request );
          $users[] = $this->prepare_response_for_collection( $data );
      }

      $response = rest_ensure_response( $users );
      $response->header( 'X-WP-Total', (int) $total_users );
      $response->header( 'X-WP-TotalPages', (int) $max_pages );

      $base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );


      return $response;
  }
  */


  /**
   * Get one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
   /*
  public function get_item( $request ) {
    //get parameters from request
   $params = $request->get_params();
    $item = array();//do a query, call another class, etc
   $data = $this->prepare_item_for_response( $item, $request );

    //return a response or error based on some conditional
   if ( 1 == 1 ) {
      return new WP_REST_Response( $data, 200 );
    }else{
      return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    }
  }
  */

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
   /*
  public function create_item( $request ) {

    $item = $this->prepare_item_for_database( $request );

    if ( function_exists( 'slug_some_function_to_create_item')  ) {
      $data = slug_some_function_to_create_item( $item );
      if ( is_array( $data ) ) {
        return new WP_REST_Response( $data, 200 );
      }
    }

    return new WP_Error( 'cant-create', __( 'message', 'text-domain'), array( 'status' => 500 ) );
  }
  */

  /**
   * Update one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
   /*
  public function update_item( $request ) {
    $item = $this->prepare_item_for_database( $request );

    if ( function_exists( 'slug_some_function_to_update_item')  ) {
      $data = slug_some_function_to_update_item( $item );
      if ( is_array( $data ) ) {
        return new WP_REST_Response( $data, 200 );
      }
    }

    return new WP_Error( 'cant-update', __( 'message', 'text-domain'), array( 'status' => 500 ) );

  }
  */

  /**
   * Delete one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
   /*
  public function delete_item( $request ) {
    $item = $this->prepare_item_for_database( $request );

    if ( function_exists( 'slug_some_function_to_delete_item')  ) {
      $deleted = slug_some_function_to_delete_item( $item );
      if (  $deleted  ) {
        return new WP_REST_Response( true, 200 );
      }
    }

    return new WP_Error( 'cant-delete', __( 'message', 'text-domain'), array( 'status' => 500 ) );
  }
  */

  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
//TODO
    //return true; <--use to make readable by all
   //return current_user_can( 'edit_something' );
   /* if ( ! current_user_can( 'edit_posts' ) ) {
    return new WP_Error( 'rest_forbidden', esc_html__( 'Sorry, you cannot view this post resource.', 'my-text-domain' ), array( 'status' => rest_authorization_required_code() ) );
    //rest_authorization_required_code() will return 401 for non authenticated or 403 for non authorised
    }
    */
   return true;
  }

  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check( $request ) {
    return $this->get_items_permissions_check( $request );
  }

  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check( $request ) {
    return current_user_can( 'edit_something' );
  }

  /**
   * Check if a given request has access to update a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check( $request ) {
    return $this->create_item_permissions_check( $request );
  }

  /**
   * Check if a given request has access to delete a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check( $request ) {
    return $this->create_item_permissions_check( $request );
  }

  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database( $request ) {
    return array();
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
      $data = array();
// should call public WP_REST_Controller::filter_response_by_context( $data, $context )
      /* In REST API User endpoint
      $schema = $this->get_item_schema();
      if ( ! empty( $schema['properties']['id'] ) ) {
          $data['id'] = $user->ID;
      }
      */
      $data['id'] = $item->ID;
      //$data['name'] = $item->name;
      //$data['Profile_picture_ID'] = $item->Profile_picture_ID;
      //$data['Profile_picture_URL'] = $item->Profile_picture_URL;

        // In REST API User endpoint
        // Wrap the data in a response object
  		// $response = rest_ensure_response( $data );

  		// $response->add_links( $this->prepare_links( $user ) );

  		/**
  		 * Filter user data returned from the REST API.
  		 *
  		 * @param WP_REST_Response $response  The response object.
  		 * @param object           $user      User object used to create response.
  		 * @param WP_REST_Request  $request   Request object.
  		 */
  		// return apply_filters( 'rest_prepare_user', $response, $user, $request );


    return $data();
  }

  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'        => 'Current page of the collection.',
        'type'               => 'integer',
        'default'            => 1,
        'sanitize_callback'  => 'absint',
      ),
      'per_page' => array(
        'description'        => 'Maximum number of items to be returned in result set.',
        'type'               => 'integer',
        'default'            => 10,
        'sanitize_callback'  => 'absint',
      ),
      'search'   => array(
        'description'        => 'Limit results to those matching a string.',
        'type'               => 'string',
        'sanitize_callback'  => 'sanitize_text_field',
      ),
    );
  }
//}
