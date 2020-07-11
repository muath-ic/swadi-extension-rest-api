<?php
/**
 * Plugin Name: تصفية المقالات
 * Description: تضيف هذه الإضافة خاصية إلى الاستعلام الخاص بالمقالات posts "التصفية" filter إلى مجموعات الواجهات API لتصفية النتائج التي تم إرجاعها بناءً على استعلامات WP_Query العامة ، هذه الخاصية تمت إزالتها من API عندما تم دمجها في WordPress core.
 * Author: معاذ السوادي
 * Author URI: http://v2.wp-api.org
 * Version: 0.1
 * License: GPL2+
 **/

 // Desc: This plugin adds a "filter" query parameter to API post collections to filter returned results based on public WP_Query parameters, adding back the "filter" parameter that was removed from the API when it was merged into WordPress core.

add_action( 'rest_api_init', 'rest_api_filter_add_filters' );

 /**
  * Add the necessary filter to each post type
  **/
function rest_api_filter_add_filters() {
	foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
		add_filter( 'rest_' . $post_type->name . '_query', 'rest_api_filter_add_filter_param', 10, 2 );
	}
}

/**
 * Add the filter parameter
 *
 * @param  array           $args    The query arguments.
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function rest_api_filter_add_filter_param( $args, $request ) {
	// Bail out if no filter parameter is set.
	if ( empty( $request['filter'] ) || ! is_array( $request['filter'] ) ) {
		return $args;
	}

	$filter = $request['filter'];

	if ( isset( $filter['posts_per_page'] ) && ( (int) $filter['posts_per_page'] >= 1 && (int) $filter['posts_per_page'] <= 100 ) ) {
		$args['posts_per_page'] = $filter['posts_per_page'];
	}

	global $wp;
	$vars = apply_filters( 'rest_query_vars', $wp->public_query_vars );

	// Allow valid meta query vars.
	$vars = array_unique( array_merge( $vars, array( 'meta_query', 'meta_key', 'meta_value', 'meta_compare' ) ) );

	foreach ( $vars as $var ) {
		if ( isset( $filter[ $var ] ) ) {
			$args[ $var ] = $filter[ $var ];
		}
	}
	return $args;
}


/**
 * BySwadi:
 * Modify the response of the REST API plugin for post.
 */
 add_action( 'rest_api_init', 'swadi_add_custom_rest_fields' );

/**
 * !Function for registering custom fields
 * ? post     = [swadi_author_name, swadi_post_views]
 * ? category = [swadi_author_name]
 */
function swadi_add_custom_rest_fields() {
    // schema for the swadi_author_name field
    $swadi_author_name_schema = array(
        'description'   => 'Name of the post author',
        'type'          => 'string',
        'context'       =>   array( 'view' )
    );

    // registering the swadi_author_name field
    register_rest_field(
        'post',
        'swadi_author_name',
        array(
            'get_callback'      => 'swadi_get_author_name',
            'update_callback'   => null,
            'schema'            => $swadi_author_name_schema
        )
    );

    // schema for swadi_post_views field
    $swadi_post_views_schema = array(
        'description'   => 'Post views count',
        'type'          => 'integer',
        'context'       =>   array( 'view', 'edit' )
    );

    // registering the swadi_post_views field
    register_rest_field(
        'post',
        'swadi_post_views',
        array(
            'get_callback'      => 'swadi_get_post_views',
            'update_callback'   => 'swadi_update_post_views',
            'schema'            => $swadi_post_views_schema
        )
    );

    // schema for swadi_post_views field
    $swadi_category_children_schema = array(
        'description'   => 'Is Category has children',
        'type'          => 'integer',
        'context'       =>   array( 'view' )
    );

    // registering the swadi_post_views field
    register_rest_field(
        'category',
        'swadi_category_children',
        array(
            'get_callback'      => 'swadi_has_category_children',
            'update_callback'   => null,
            'schema'            => $swadi_category_children_schema
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
function swadi_get_author_name( $object, $field_name, $request ) {
    return get_the_author_meta( 'display_name', $object['author'] );
}

/**
 * Callback for retrieving post views count
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return integer                          Post views count
 */
function swadi_get_post_views( $object, $field_name, $request ) {
    return (int) get_post_meta( $object['id'], $field_name, true );
}
/**
 * Callback for updating post views count
 * @param  mixed    $value          Post views count
 * @param  object   $object         The object from the response
 * @param  string   $field_name     Name of the current field
 * @return bool|int
 */
function swadi_update_post_views( $value, $object, $field_name ) {
    if ( ! $value || ! is_numeric( $value ) ) {
        return;
    }

    return update_post_meta( $object->ID, $field_name, (int) $value );
}

/**
 * TODO: Check if current category has subcategories
 */

/**
 * Function for registering custom fields in post collection
 */
/**
 * &TODO: complete this
 * Callback for retrieving post views count
 * @param  array            $object         The current post object
 * @param  string           $field_name     The name of the field
 * @param  WP_REST_request  $request        The current request
 * @return integer                          Post views count
 */
function swadi_has_category_children( $object, $field_name, $request ) {
    global $wpdb;
    $term = get_queried_object();
    $category_children_check = $wpdb->get_results(" SELECT * FROM ".$wpdb->prefix."term_taxonomy WHERE parent = ".$object['id']);
     if ($category_children_check) {
          return true;
     } else {
          return false;
     }
}
