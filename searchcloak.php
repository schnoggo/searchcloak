<?php
/**
 * Description: Allows Pages to be omitted from search results and search engine indexes
 * Author: Lon Koenig and Firebrand LLC
 * Author URI: http://lonk.me/
 *
 * @package searchcloak
 * @version 2.1.2
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
	Restructure the plugin as a class
	Reformat comments as docblocks
	Change the head wp_head action to a filter to avoid duplicate robots noindex
Add Page/Post listing view options (column for "cloaked")
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

/**
 * Adds an interface box to the editor window
 *
 * @global array $searchcloak_settings shared data for the plugin
 */
function searchcloak_add_meta_box() {
	global $searchcloak_settings;
	$post_permissions = $searchcloak_settings['post_permissions'];
	// tack in the custom post types.
	$options = get_option( $searchcloak_settings['op_name'] );
	if ( is_array( $options ) ) {
		foreach ( $options as $k => $v ) {
			if ( 'on' === $v ) {
				$post_permissions[ $k ] = 'edit_post';
			}
		}
	}

	// step through all our supported post types and add a meta box.
	foreach ( array_keys( $post_permissions ) as $post_type ) {
		add_meta_box(
			'searchcloak', // $id
			'SearchCloak', // $title
			'searchcloak_inner_meta_box', // $callback
			$post_type,  // 'page'
			'side', // $context ['normal' | 'advanced' | 'side'].
			'default' // $priority ['high' | 'core' | 'default' | 'low']
			// $callback_args
		);
	}
}


/**
 * Hook up the checkboxes if admin
 */
function searchcloak_add_to_theme() {
	if ( is_admin() ) {
		add_action( 'add_meta_boxes', 'searchcloak_add_meta_box' );
		// Use the save_post action to save new post data.
		add_action( 'save_post', 'searchcloak_save_taxonomy_data' );
	}
}


/**
 * A callback to the add_meta_box function
 * to draw the interface for our editor window box
 */
function searchcloak_inner_meta_box() {
	global $post;
	// Get all the possible taxonomy terms for 'searchcloak'.
	$tax_terms = get_terms(
		'searchcloak',
		array(
			'hide_empty' => false,
		)
	);

	// build the form:
	// Add an nonce field so we can check for it when we save.
	wp_nonce_field( 'searchcloak_inner_meta_box', 'searchcloak_nonce' ); // generate hidden input field.
	$names = wp_get_object_terms( $post->ID, 'searchcloak' );

	if ( ! is_wp_error( $names ) ) {
		echo '<select name="post_searchcloak" id="post_searchcloak">' . "\n";
		$selected_option = 'showinsearch'; // the default option is to show.
		if ( ! empty( $names ) ) {
			$selected_option = $names[0]->slug; // if there is a selection, use it.
		}
		foreach ( $tax_terms as $this_tax ) {
			// maybe replace the following test with
			// 1) fetch post type info with get_post_types()
			// 2) check 'hierarchical'.
			if ( ( 'post' !== $post->post_type ) || ( ( 'post' === $post->post_type ) && ( 'hidechildren' !== $this_tax->slug ) ) ) {
				// skip children option on 'post' pages.
				echo '<option class="searchcloak-option" value="' . esc_attr( $this_tax->slug ) . '"';
				if ( $this_tax->slug === $selected_option ) {
					echo ' selected';
				}
				echo '>' . esc_attr( $this_tax->name ) . "</option>\n";
			}
		}
		echo '</select>' . "\n";

	} else {
		echo 'Error';
	}
}


/**
 * Write out the user-selected taxonomy data.
 * Only if user has permissions and matching nonce.
 *
 * @param integer $post_id WordPress Post ID for this page.
 *
 * @global WP_Post $_POST
 *   searchcloak_nonce - form nonce
 *   post_type - regular post type: page, post, etc.
 *   post_searchcloak
 *
 * @global array $searchcloak_settings shared data for the plugin
 *
 * @return boolean|integer false = failed | otherwise post_id
 *   $post_id if not allowed to write
 *   form value is success
 *   false if not page or post
 */
function searchcloak_save_taxonomy_data( $post_id ) {
	global $searchcloak_settings;
	$ret_val          = $post_id; // the return value for not allowed.
	$post_permissions = $searchcloak_settings['post_permissions'];
	// tack in the custom post types.
	$options = get_option( $searchcloak_settings['op_name'] );
	if ( is_array( $options ) ) {
		foreach ( $options as $k => $v ) {
			if ( 'on' === $v ) {
				$post_permissions[ $k ] = 'edit_post';
			}
		}
	}

	if (
		( array_key_exists( 'post_type', $_POST ) )
		&&
		( array_key_exists( 'searchcloak_nonce', $_POST ) )
		) {
		$passed_type = sanitize_key( $_POST['post_type'] );
		// verify this came from our screen and with proper authorization.

		if ( wp_verify_nonce( sanitize_key( $_POST['searchcloak_nonce'] ), 'searchcloak_inner_meta_box' ) ) {

			// check if this is an auto-save. If it is, our form has not been submitted, so we dont want to do anything.
			if ( ( ! defined( 'DOING_AUTOSAVE' ) ) || ( ! DOING_AUTOSAVE ) ) {

				// Check permissions.
				if (
					( array_key_exists( $passed_type, $post_permissions ) )
					&&
					( current_user_can( $post_permissions[ $passed_type ], $post_id ) )
					) {
					// OK, we're authenticated: we need to find and save the data.
					$post    = get_post( $post_id );
					$ret_val = false;
					if ( array_key_exists( $post->post_type, $post_permissions ) ) {
						if ( array_key_exists( 'post_searchcloak', $_POST ) ) {
							$ret_val = sanitize_key( $_POST['post_searchcloak'] );
							wp_set_object_terms( $post_id, $ret_val, 'searchcloak' );
						}
					}
				}
			}
		}
	}
	return $ret_val;
}


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
				'label'        => __( 'SearchCloak' ),
				'query_var'    => 'searchcloak',
				'hierarchical' => false,
				'public'       => false,
				// 'meta_box_cb' => 'searchcloak_metabox',
				'rewrite'      => false,
			)
		);

		wp_insert_term(
			'show',
			'searchcloak',
			array(
				'description' => 'Display in search results',
				'slug'        => 'showinsearch',
			)
		);
		wp_insert_term(
			'cloak',
			'searchcloak',
			array(
				'description' => 'Hide from search results',
				'slug'        => 'hidefromsearch',
			)
		);
		wp_insert_term(
			'children',
			'searchcloak',
			array(
				'description' => 'Hide children from search results',
				'slug'        => 'hidechildren',
			)
		);

	}
}



/**
 * A filter function for pre_get_posts
 *
 * Removes our flagged elements from the query object
 *
 * @param  WP_Query $query Default WordPress query object.
 *
 * @return WP_Query modified query
 *
 * NOTE:
 * The taxonomy terms are hard-coded: 'hidechildren' 'hidefromsearch'
 */
function searchcloak_search_filters( $query ) {
	if ( ! is_admin() ) { // don't filter searches if we are in the backend.
		if ( $query->is_search ) {
			// look for parent exclusion.
			$parent_term  = get_term_by( 'slug', 'hidechildren', 'searchcloak' );
			$parent_nodes = get_objects_in_term( $parent_term->term_id, $parent_term->taxonomy );

			$query->set( 'post_parent__not_in', $parent_nodes );
			$query->set( 'post__not_in', $parent_nodes );

			$self_term    = get_term_by( 'slug', 'hidefromsearch', 'searchcloak' );
			$hidden_posts = get_objects_in_term( $self_term->term_id, $self_term->taxonomy );
			$query->set( 'post__not_in', $hidden_posts );
		}
	}
	return $query;
}


/**
 * An wp_head action that adds robot meta information
 *
 * Cloaked pages get
 * <meta name="robots" content="noindex,follow">
 *
 * @global WP_Post $_POST
 *
 * No return value.
 * outputs meta tag if appropriate.
 */
function searchcloak_noindex() {
	// To-do:
	// Move this to a filter which can check for existing meta tag.
	//
	global $searchcloak_settings;
	global $post;
	if ( null !== $post ) { // only on editable pages.
		$show_it = true;

		// first, check to see if this node should be hidden.
		$r = get_the_terms( $post->ID, 'searchcloak' );
		if ( is_array( $r ) ) {
			foreach ( $r as $r2 ) {
				if ( 'show' !== $r2->name ) {
					$show_it = false;
				}
			}
		}

		if ( $show_it ) {
			// now check parents.
			$test_posts = get_post_ancestors( $post->ID );
			foreach ( $test_posts as $a_post ) {
				$r = get_the_terms( $a_post, 'searchcloak' );
				if ( is_array( $r ) ) {
					foreach ( $r as $r2 ) {
						if ( 'children' === $r2->name ) {
							$show_it = false;
							break; // only need one.
						}
					}
				}
			}
		}

		if ( ! $show_it ) {
			echo '<meta name="robots" content="noindex,follow">' . "\n";
			$searchcloak_settings['nonindex'] = true;
		}
	}
	// debug.

	/*
	$n = $post->post_title;
	echo '<!-- ';
	echo '<meta name="testy" content="';
	echo 'name:'.$n;
	echo ' tax: ';
	$r = get_the_taxonomies($post->ID);
	echo print_r($r, true);
	echo ' terms: ';
	$r = get_the_terms($post->ID, 'searchcloak');
	echo print_r($r, true);
	echo '">'."\n";
	echo ' -->';
	*/
}

/**
 * Start an output buffer right before head is rendered
 *
 * This buffer can be modified to add/remove/modify meta tags
 */
function searchcloak_capture_head_start() {
	ob_start( 'searchcloak_filter_head' );
}


/**
 * Filter the header content to set meta information
 *
 * When this is run, searchcloak_noindex() has already been called during the <head> render.
 *
 * @param  string $capture rendered header captured via ob_start().
 *
 * @global array $searchcloak_settings shared data for the plugin
 *
 * @return string the modified header
 */
function searchcloak_filter_head( $capture ) {
	global $searchcloak_settings;

	$robots_count = preg_match_all( '/^\s*<\s*meta\s+name\s*=\s*[\'"]+.*robots.*[\'"]+.*?$/m', $capture, $robot_lines );
	if ( $robots_count > 1 ) { // there are multiple <meta name="robots"> tags. Condense them into one.
		// step 1: remove all the robots lines: (they are already captured in $robot_lines).
		$capture = preg_replace( '/^\s*<\s*meta\s+name\s*=\s*[\'"]+.*robots.*[\'"]+.*?$/m', '', $capture );
		$index   = 'index';
		$follow  = 'follow';
		foreach ( $robot_lines[0] as $line ) {
			$line = trim( $line );
			$line = preg_replace( '/^\s*(<\s*meta\s+name\s*=\s*[\'"]+.*robots.*[\'"]+.*?>).*$/i', '$1', $line ); // strip off non-tag stuff (probably comments).
			if ( strpos( strtolower( $line ), 'noindex' ) ) {
				$index = 'noindex';}
			if ( strpos( strtolower( $line ), 'nofollow' ) ) {
				$follow = 'nofollow';}
		}
		$capture .= "\n\t<!-- SearchCloak plugin combined " . $robots_count . " \"robots\" meta tags: -->\n";
		$capture .= "\t<meta name=\"robots\" content=\"" . $index . ',' . $follow . "\" />\n";

	}
	return $capture;
}

/**
 *  Close the capture started by searchcloak_capture_head_start()
 *  This will trigger the callback where we filter the head contents.
 */
function searchcloak_capture_head_end() {
	ob_end_flush();
}

searchcloak_add_to_theme();
add_action( 'init', 'create_searchcloak_taxonomy', 0 );
add_filter( 'pre_get_posts', 'searchcloak_search_filters', 2 ); // number is priority - lower=earlier.

add_action( 'wp_head', 'searchcloak_noindex' ); // add our NOINDEX if needed.

// filter the rendered head section to remove multiple robots meta tags.
add_action( 'get_header', 'searchcloak_capture_head_start' ); // start recording just before head is rendered.
add_action( 'wp_head', 'searchcloak_capture_head_end', PHP_INT_MAX - 1 ); // make sure it is the last thing in the head.

require_once 'searchcloak-admin.php';
