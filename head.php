<?php
/**
 * Description: wp_head filter for SearchCloak
 *              deals with other plugins that modify robots meta
 * Author: Lon Koenig
 * Author URI: http://lonk.me/
 *
 * @package searchcloak
 * @version 3.0.0
 */



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
			echo '<!-- cloak -->' . "\n";
 			$searchcloak_settings['nonindex'] = true;
 		}
 	}

 }





 /**
  * Start an output buffer right before head is rendered
  *
  * This buffer can be modified to add/remove/modify meta tags
  */
 function searchcloak_capture_head_start() {
 	ob_start( 'searchcloak_filter_head' ); // callback is our main "filter".
 	echo "<!-- start capture -->\n";
 }


 /**
  * Filter the header content to set meta information
  *
  * When this is run, searchcloak_noindex() has already been called during the <head> render.
  *
  * @param string $capture rendered header captured via ob_start().
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
 				$index = 'noindex';
 			}
 			if ( strpos( strtolower( $line ), 'nofollow' ) ) {
 				$follow = 'nofollow';
 			}
 		}
 		$capture .= "\n\t<!-- SearchCloak plugin combined " . $robots_count . " \"robots\" meta tags: -->\n";
 		$capture .= "\t<meta name=\"robots\" content=\"" . $index . ',' . $follow . "\" />\n";

 	}

 	$capture .= "\n\t<!-- SearchCloak plugin -->\n";
 	return $capture;
 	// return "<!-- whatevs -->\n";
 }

 /**
  *  Close the capture started by searchcloak_capture_head_start()
  *  This will trigger the callback where we filter the head contents.
  */
 function searchcloak_capture_head_end() {
 	ob_flush();
 	// echo "<!-- captured -->\n";
 }



 add_action( 'wp_head', 'searchcloak_noindex' ); // add our NOINDEX if needed.

 // filter the rendered head section to remove multiple robots meta tags.
 // add_action( 'wp_head', 'searchcloak_capture_head_start', PHP_INT_MIN + 1 ); // start recording just before head is rendered.
 // add_action( 'wp_head', 'searchcloak_capture_head_end',   PHP_INT_MAX - 1 ); // make sure it is the last thing in the head.


 /* add_action('wp', function(){ ;exit; } ); */


 add_action( 'wp_footer', 'searchcloak_rebug', PHP_INT_MAX - 1 ); // make sure it is the last thing in the head.


 /**
  * Print debug information as HTML comment
  */
 function searchcloak_rebug() {
 	global $GLOBALS;
 	echo "<!-- \n";
 	// echo print_r( $GLOBALS['wp_filter']['wp_head'], true );
 	echo "\n -->\n";
 }
