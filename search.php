<?php
/**
 * Description: WordPress search filter for SearchCloak plugin
 * Author: Lon Koenig
 * Author URI: http://lonk.me/
 *
 * @package searchcloak
 * @version 3.0.0
 */


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
