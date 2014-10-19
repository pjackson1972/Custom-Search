<?php
/**
 * 
 * Plugin Name: IC Custom Search
 * Plugin URI: http://www.ivycat.com
 * Description: This plugin generates a custom search for the canned-fresh site
 * Author: Patrick Jackson <patrick@ivycat.com>
 * Version: 1.0.0
 * Author URI: http://www.ivycat.com
 * Text Domain: custom-search
 * 
 * 
 * @category ic-custom-search
 * @author: Patrick Jackson <patrick@ivycat.com>
 * @version: 1.1.1
 * 
 */

include 'classes/class-ic-custom-search-shortcode-handler.php';

add_shortcode('ic-search-form', array('IC_Custom_Search_Shortcode_Handler','ic_search_form') );
add_shortcode('ic-search-results', array('IC_Custom_Search_Shortcode_Handler','ic_search_results') );
