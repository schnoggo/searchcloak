<?php
add_action( 'admin_menu', 'searchcloak_add_admin_menu' );
add_action( 'admin_init', 'searchcloak_settings_init' );
add_action('admin_head', 'searchcloak_admin_css');

function searchcloak_add_admin_menu(  ) { 

	add_options_page( 
		'SearchCloak', // page_title
		'SearchCloak',  // menu_title
		'manage_options', // capability
		'searchcloak',  // menu_slug
		'searchcloak_options_page' // display function
	);

}


function searchcloak_settings_init(  ) { 
	global $searchcloak_settings;
	register_setting(
		$searchcloak_settings['admin_page'], //option_group
		'searchcloak_settings',  //option_name
		'searchcloak_sanitize'
	);

	add_settings_section(
		'searchcloak_searchCloakPage_section', 
		__( 'Apply SearchCloak to these custom post types:', 'wordpress' ), 
		'searchcloak_settings_section_callback', 
		$searchcloak_settings['admin_page']
	);



	$options = get_option( $searchcloak_settings['op_name'] );
	if (!is_array($options)){ $options = array(); }
	$args = array(
	   'public'   => true,
	   'exclude_from_search' => false,
	   '_builtin' => false
	);
	$post_types = get_post_types( $args, 'names', 'and' );
	foreach ( $post_types  as $cpt_name ) {
		//$field_name = 'sc_' . $cpt_name;
		$field_name = 'searchcloak_cpt_checkboxes[' .  $cpt_name .']';
		$id = 'sc_'  . $cpt_name; 
		$checked = false;
		if (array_key_exists($cpt_name, $options)){
			$checked = checked($options[ $cpt_name], 'on', false);
		}
		add_settings_field( 
            $id, // id
            $cpt_name, // title
            'searchcloak_cpt_checkboxes_render', // callback (render display)
            $searchcloak_settings['admin_page'], // page (must match add_theme_page() or add_options_page in our case)
            'searchcloak_searchCloakPage_section', // section
            array(
            	'id' => $id,
            	'label' =>$cpt_name,
            	'checked' => $checked,
            ) // args 
        );
	}

}


function searchcloak_cpt_checkboxes_render($args){
/*
	echo "<pre>\ncallback()\n";
	echo print_r($args);
	echo "\n</pre>\n";
*/
	global $searchcloak_settings;
	$name =  $searchcloak_settings['op_name'] . '[' .  $args['label']  . ']';
	echo '<input type="checkbox" '
		. 'name="' . $name . '" '
		. 'id="' . $args['id'] . '" '
		. $args['checked']
		. " />\n";

	echo '<label for="' .  $args['id'] . '">'
		. $args['label'] 
		. '</label>'
		. "\n";

	echo "<br />\n";

}


function searchcloak_settings_section_callback(  ) { 

	//echo __( 'This section description', 'wordpress' );


}


function searchcloak_options_page(  ) { 
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


function searchcloak_sanitize( $input ){
	return $input; // return validated input

}



function searchcloak_admin_css(){
	echo '
	<style id="search-cloak-admin-css">
	.settings_page_searchcloak .form-table th {
		display:none;
	}
	</style>'
	;	
}
?>