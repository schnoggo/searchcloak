<?php
/**
 * Description: Backend interface for SearchCloak WordPress plugin
 * Author: Lon Koenig
 * Author URI: http://lonk.me/
 *
 * @package searchcloak
 * @version 3.0.0
 */

add_action( 'admin_menu', 'searchcloak_add_admin_menu' );
add_action( 'admin_init', 'searchcloak_settings_init' );
add_action( 'admin_head', 'searchcloak_admin_css' );



/**
 * Creates a WordPress Options page
 */
function searchcloak_add_admin_menu() {
	add_options_page(
		'SearchCloak', // page_title.
		'SearchCloak',  // menu_title.
		'manage_options', // capability.
		'searchcloak',  // menu_slug.
		'searchcloak_options_page' // display function.
	);
}


/**
 * Back-end initialization for the plugin
 */
function searchcloak_settings_init() {
	global $searchcloak_settings;
	register_setting(
		$searchcloak_settings['admin_page'], // option_group.
		'searchcloak_settings',  // option_name.
		'searchcloak_sanitize'
	);

	add_settings_section(
		'searchcloak_searchCloakPage_section',
		__( 'Enable SearchCloak options for these custom post types:', 'searchcloak' ),
		'searchcloak_settings_section_callback',
		$searchcloak_settings['admin_page']
	);

	$options = get_option( $searchcloak_settings['op_name'] );
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$args = array(
		'public'              => true,
		'exclude_from_search' => false,
		'_builtin'            => false,
	);

	$post_types = get_post_types( $args, 'names', 'and' );
	foreach ( $post_types  as $cpt_name ) {
		$field_name = 'searchcloak_cpt_checkboxes[' . $cpt_name . ']';
		$id         = 'sc_' . $cpt_name;
		$checked    = false;
		if ( array_key_exists( $cpt_name, $options ) ) {
			$checked = checked( $options[ $cpt_name ], 'on', false );
		}
		add_settings_field(
			$id, // id.
			$cpt_name, // title.
			'searchcloak_cpt_checkboxes_render', // callback (render display).
			$searchcloak_settings['admin_page'], // page (must match add_theme_page() or add_options_page in our case).
			'searchcloak_searchCloakPage_section', // section.
			array(
				'id'      => $id,
				'label'   => $cpt_name,
				'checked' => $checked,
			) // args.
		);
	}
	searchcloak_add_to_theme(); // add the edit box to pages/posts
}


/**
 * Callback for add_settings_field
 * https://developer.wordpress.org/reference/functions/add_settings_field/
 *
 * @param  array $args optional parameters - see WordPress doc above.
 */
function searchcloak_cpt_checkboxes_render( $args ) {
	global $searchcloak_settings;
	$name = $searchcloak_settings['op_name'] . '[' . $args['label'] . ']';
	echo '<input type="checkbox" '
		. 'name="' . esc_attr( $name ) . '" '
		. 'id="' . esc_attr( $args['id'] ) . '" '
		. esc_attr( $args['checked'] )
		. " />\n";

	echo '<label for="' . esc_attr( $args['id'] ) . '">'
		. esc_attr( $args['label'] )
		. '</label>'
		. "\n";

	echo "<br />\n";

}

/**
 * Callback for add_settings_section()
 * https://developer.wordpress.org/reference/functions/add_settings_section/
 */
function searchcloak_settings_section_callback() {
	// echo __( 'This section description', 'searchcloak' ); .
}


/**
 * Callback to render admin Options page
 * https://developer.wordpress.org/reference/functions/add_options_page/
 */
function searchcloak_options_page() {
	global $searchcloak_settings;
	?>
	<form action='options.php' method='post'>
		<h2>SearchCloak</h2>

		<?php
		settings_fields( $searchcloak_settings['admin_page'] );
		do_settings_sections( $searchcloak_settings['admin_page'] );
		submit_button();
		?>

	</form>
	<?php
}

/**
 * Very bad sanitizing function
 * (Doesn't actually do anything)
 *
 * @param  array $input whatever is passed in sanitizing callback.
 *
 * @return array sanitized input
 */
function searchcloak_sanitize( $input ) {
	return $input; // return validated input.
}




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
				echo '>' . esc_attr( $this_tax->description ) . "</option>\n";
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
 * Echo out the CSS for the admin panel
 */
function searchcloak_admin_css() {
	echo '
	<style id="search-cloak-admin-css">
	.settings_page_searchcloak .form-table th {
		display:none;
	}
	</style>';
}
