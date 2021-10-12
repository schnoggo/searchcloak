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
