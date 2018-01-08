
<?php
   /*
   Plugin Name: SLC Categories
   Plugin URI: none
   Description: a plugin to easily display categories
   Version: 1.0
   Author: Brian Moniz
   Author URI: http://slcutahdesign.com
   License: GPL2
   */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );




$list = function()
{
$categories = get_categories();
global $wpdb;
foreach ($categories as $category)
{
	$image = $wpdb->get_results( "SELECT * FROM $wpdb->termmeta WHERE `term_id` = $category->term_id");
	echo "<pre>";
	print_r($image);
	echo "</pre>";
}
};

add_shortcode('slc-categories', $list);