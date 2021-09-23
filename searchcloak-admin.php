<?php
/**
 * Description: Backend interface for SearchCloak WordPress plugin
 * Author: Lon Koenig and Firebrand LLC
 * Author URI: http://lonk.me/
 *
 * @package searchcloak
 * @version 2.1.2
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
		__( 'Apply SearchCloak to these custom post types:', 'WordPress' ),
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
	// echo __( 'This section description', 'WordPress' ); .
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
?>
