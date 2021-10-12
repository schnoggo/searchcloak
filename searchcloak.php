<?php
/**
 * Plugin Name: SearchCloak
 * Version: 3.0.0
 *
 * Description: Allows Pages to be omitted from search results and search engine indexes
 * Author: Lon Koenig
 * Author URI: http://lonk.me/
 *
 * @package searchcloak
 * @version 3.0.0
 */

/*
This plugin uses a custom taxonomy called "searchcloak" to store per-page settings.
Each page can have one of three values:
show (default) show this page in search results
cloak - hide this page
children - hide this page and its children from search results

On cloaked pages, we set a robots meta value noindex,follow to discourage search engines from displaying the page.
We also add a search filter to remove this page from regular WordPress searches.


To Do:
	- Restructure the plugin as a class
	- Change the head wp_head action to a filter to avoid duplicate robots noindex
  - Add Page/Post listing view options (column for "cloaked")
*/

// nasty global! Get this in a class ASAP!
$searchcloak_settings = array(
	'post_permissions' => array(
		'post' => 'edit_post',
		'page' => 'edit_page',
		// 'mycustomposttype' => 'edit_post'
	),
	'admin_page'       => 'searchcloak', // 'searchCloakPage',
	'op_name'          => 'searchcloak_settings',
	'nonindex'         => '',
);
$searchcloak_head_robots = array();

/**
 * Create a simple taxonomy that can be used to toggle search visibility.
 * The taxonomy is called "searchcloak"
 */
function create_searchcloak_taxonomy() {
	if ( ! taxonomy_exists( 'searchcloak' ) ) {
		register_taxonomy(
			'searchcloak',
			'page',
			array(
				'label'        => __( 'SearchCloak', 'searchcloak' ),
				'query_var'    => 'searchcloak',
				'hierarchical' => false,
				'public'       => false,
				// 'meta_box_cb' => 'searchcloak_metabox',
				'rewrite'      => false,
			)
		);

		wp_insert_term(
			'show',          // term.
			'searchcloak',   // taxonomy.
			array(
				'description' => __( 'Display in search results', 'searchcloak' ),
				'slug'        => 'showinsearch',
			)
		);
		wp_insert_term(
			'cloak',
			'searchcloak',
			array(
				'description' => __( 'Hide from search results', 'searchcloak' ),
				'slug'        => 'hidefromsearch',
			)
		);
		wp_insert_term(
			'children',
			'searchcloak',
			array(
				'description' => __( 'Hide children from search results', 'searchcloak' ),
				'slug'        => 'hidechildren',
			)
		);

	}
}






add_action( 'init', 'create_searchcloak_taxonomy', 0 );
add_filter( 'pre_get_posts', 'searchcloak_search_filters', 2 ); // number is priority - lower=earlier.


require_once 'head.php';
require_once 'search.php';
if ( is_admin() ) {
	include_once 'searchcloak-admin.php';
}
