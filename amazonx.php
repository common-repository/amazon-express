<?php
/*
Plugin Name: Amazon Express
Plugin URI: http://www.rampantlogic.com/amazonx
Description: Loads Amazon product images and Amazon Associates referral links into posts from the ISBN/ASIN. Includes a one to five star rating system and shortcode to list all posts within a specified range of ratings.
Version: 1.0.1
Author: Rampant Logic
Author URI: http://www.rampantlogic.com
License: GPL2
*/

// This function was found at: 
// http://www.ilovebonnie.net/2009/07/27/amazon-aws-api-rest-authentication-for-php-5/
function amazonx_sign_request($secret_key, $request, $access_key = false, $version = '2009-03-01') {
    $uri_elements = parse_url($request);	// Get a nice array of elements to work with
	$request = $uri_elements['query'];    	// Grab our request elements
	parse_str($request, $parameters);		// Throw them into an array
	$parameters['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");	// Add the new required paramters
    $parameters['Version'] = $version;
    if (strlen($access_key) > 0) {
        $parameters['AWSAccessKeyId'] = $access_key;
    }
    ksort($parameters);		// The new authentication requirements need the keys to be sorted
    foreach ($parameters as $parameter => $value) {	// Create our new request
        // We need to be sure we properly encode the value of our parameter
        $parameter = str_replace("%7E", "~", rawurlencode($parameter));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $request_array[] = $parameter . '=' . $value;
    }   
    // Put our & symbol at the beginning of each of our request variables and put it in a string
    $new_request = implode('&', $request_array);
    // Create our signature string
    $signature_string = "GET\n{$uri_elements['host']}\n{$uri_elements['path']}\n{$new_request}";
    // Create our signature using hash_hmac
    $signature = urlencode(base64_encode(hash_hmac('sha256', $signature_string, $secret_key, true)));
    // Return our new request
    return "http://{$uri_elements['host']}{$uri_elements['path']}?{$new_request}&Signature={$signature}";
} 

function amazonx_construct_request($asin, $access_key, $assoc_tag, $response_group)
{
	$ret = "http://ecs.amazonaws.com/onca/xml?" .
	"Service=AWSECommerceService" .
	"&AWSAccessKeyId={$access_key}" . 
	"&Operation=ItemLookup" . 
	"&ItemId={$asin}" .
	"&ResponseGroup={$response_group}";
	if($assoc_tag != "") {
	  $ret .= "&AssociateTag={$assoc_tag}";
	}
	return $ret . "&Version=2010-11-01";
}

function amazonx_image_dir() {
   return plugins_url('/images', __FILE__);
}

function amazonx_star_html($rating)
{
  if($rating == 0)		// code for "no rating"
    return "";
	
  $filled_style = "";
  $filled_color = get_option('amazonx_starcolor');
  if($filled_color != "")
    $filled_style = 'style="color: ' . $filled_color . ';" ';
  $empty_style = "";
  $empty_color = get_option('amazonx_estarcolor');
  if($empty_color != "")
    $empty_style = 'style="color: ' . $empty_color . ';" ';
  $ret = '<span class="amazonx-rating">';		// keep even if no contents for spacing
  $ret .= '<span class="amazonx-filled-star" ' . $filled_style . '>';
  for($i = 0; $i < $rating; $i++)
    $ret .= '&#9733;';
  $ret .= '</span><span class="amazonx-empty-star" ' . $empty_style . '>';
  for(; $i < 5; $i++)
    $ret .= '&#9733;';
  $ret .= '</span></span>';
  return $ret;
}

function amazonx_below_image_html($asin, $rating)
{
  $below = '<img class="amazonx-button" src="' . amazonx_image_dir() . '/button.png">';	// default
  $code = get_option('amazonx_belowimg');
  if($code != "") {
	if((int)$code == 0)
	  $below = '<div style="font-size:0;height:5px;"></div>';
	else if((int)$code == 2)
      $below = amazonx_star_html($rating);
  }
  return $below;
}

function amazonx_cat_recur($category_node, $minrating, $maxrating, $showcat)
{	
  $direct_posts = (array)null;
  // find all posts in this category and remove results that are in subcategories
  if($category_node > 0) {		// don't list any posts in category 0
    $query = "cat={$category_node}&numberposts=-1&post_type=any";
    $posts = get_posts($query);
    foreach($posts as $thispost) {
      $categories = get_the_category($thispost->ID);
	  foreach($categories as $cat)
        if($cat->cat_ID == $category_node)
		  array_push($direct_posts, $thispost);
    }
  }
 
  $display_posts = (array)null;
  // load posts to be displayed in this category	  
  foreach($direct_posts as $thispost) {
    //setup_postdata($thispost);	// so we can use the_author_posts_link()
    $id = $thispost->ID;
    $asin = get_post_meta($id, 'amazonx_asin', true);
    $rating = get_post_meta($id, 'amazonx_rating', true);
    if($asin == "")
      continue;
	if((int)$minrating != 0 && (int)$rating < (int)$minrating)
	  continue;
	if((int)$maxrating != 0 && (int)$rating > (int)$maxrating)
	  continue;
	if((int)$maxrating != 0 && (int)$rating == 0)
	  continue;
	array_push($display_posts, $thispost);
  }
  
  // print the category title if there are posts to display
  if((int)$showcat !=0 && count($display_posts) > 0) {
    echo '<div class="amazonx-category">' . get_cat_name($category_node) . '</div>';	
  }
  
  foreach($display_posts as $thispost) {
  	//global $post;
	//$post = $thispost;			// setup global data for content filter
	setup_postdata($thispost);		// needed to get the content
	$id = $thispost->ID;
	$asin = get_post_meta($id, 'amazonx_asin', true);
    $rating = get_post_meta($id, 'amazonx_rating', true);
	//echo '<div class="post">';
	echo '<div class="amazonx-item">';
	echo amazonx_product_box_html($asin, $rating, true);
	echo '<h2 class="entry-title"><a href="' . get_permalink($id) . '">' . get_the_title($id) . '</a></h2>';
	if((int)get_option('amazonx_belowimg') != 2)	// omit rating stars here if they are under the product image
		echo amazonx_star_html($rating);
	echo '<br/><div class="amazonx-content">';
	echo get_the_content($id);
	echo '</div></div>';		
	//echo '</div>';
	echo '<div class="amazonx-clear"></div>';	
  }
  
  // now run through subcategories 
  $categories = get_categories("parent={$category_node}&hide_empty=0");
  foreach($categories as $cat)
	amazonx_cat_recur($cat->cat_ID, $minrating, $maxrating, $showcat);
}

function amazonx_write_table($atts) {
  extract(shortcode_atts(array(
      'minrating' => 0,
      'maxrating' => 0,
	  'category' => 0,		// 0 is the root category
	  'show_categories' => 1
  ), $atts));
  
  amazonx_cat_recur($category, $minrating, $maxrating, $show_categories);
}

function amazonx_save_metabox_data($post_id) {
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if(!isset($_POST['amazonx_noncename']))	// eliminates notices in debug mode
    return $post_id;
  if ( !wp_verify_nonce( $_POST['amazonx_noncename'], plugin_basename(__FILE__)))
    return $post_id;
  // verify if this is an auto save routine. If it is, our form has not been
  // submitted, so we dont want to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
    return $post_id;
  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return $post_id;
  }
  // OK, we're authenticated: we need to find and save the data
  $asin = $_POST['amazonx_asin'];
  $rating = $_POST['amazonx_rating'];
  if( ($asin == null) || ($rating == null)) {
    delete_post_meta($post_id, 'amazonx_asin');
    delete_post_meta($post_id, 'amazonx_rating');
  } else {
    update_post_meta($post_id, 'amazonx_asin', $asin);
    update_post_meta($post_id, 'amazonx_rating', $rating);
  }
}

function amazonx_write_metabox($post) {
  // write html for metabox form; use nonce for verification
  wp_nonce_field( plugin_basename(__FILE__), 'amazonx_noncename' );		// adds hidden field
  $postid = $post->ID;
  // check if the values are already set, and if so, we must load them into the boxes
  $asin = get_post_meta($postid, 'amazonx_asin', true);
  $rating = get_post_meta($postid, 'amazonx_rating', true);
  $selected = array("", "", "", "", "", "");
  if($rating != "")
  {
    $index = (int)$rating;
	if($index > 5 || $index < 0)
	  $index = 0;
	$selected[$index] = "selected ";
  }
  else
    $selected[0] = "selected ";
  echo 'ASIN: &nbsp;&nbsp;<input type="text" name="amazonx_asin" value="' . $asin . '" size="20" />';
  echo '<br/><br/>';
  echo 'Rating: <select name="amazonx_rating" style="width: 130px">';
  echo '<option ' . $selected[0] . 'value="0">No Rating</option>';
  echo '<option ' . $selected[5] . 'value="5">5 Stars</option>';
  echo '<option ' . $selected[4] . 'value="4">4 Stars</option>';
  echo '<option ' . $selected[3] . 'value="3">3 Stars</option>';
  echo '<option ' . $selected[2] . 'value="2">2 Stars</option>';
  echo '<option ' . $selected[1] . 'value="1">1 Star</option>';
  echo '</select>';
}

function amazonx_register_metabox() {
  add_meta_box('amazonx_metabox_id', 'Amazon Express', 'amazonx_write_metabox', 'post', 'side');
  add_meta_box('amazonx_metabox_id', 'Amazon Express', 'amazonx_write_metabox', 'page', 'side');
}

function amazonx_register_menu() {
  add_options_page('Amazon Express Options', 'Amazon Express', 'manage_options', 'amazonx', 'amazonx_options');
}

function amazonx_options() {
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
  
  include('amazonx-options.php');
}

function amazonx_product_box_html($asin, $rating, $listing = false)
{
  // these will be loaded from the settings
  $access_key = get_option('amazonx_accesskey'); 
  $secret_key = get_option('amazonx_secretkey');
  if((int)get_option('amazonx_reg') == 1)
	$assoc_tag = get_option('amazonx_assoctag');
  else
    $assoc_tag = strrev('02-cigoltnapmar');
  
  if($access_key == FALSE || $secret_key == FALSE)
	return "";
	
  if($assoc_tag == FALSE)
    $assoc_tag = "";
	
  $request = amazonx_construct_request($asin, $access_key, $assoc_tag, 'Small,Images');
  $signed_request = amazonx_sign_request($secret_key, $request, $access_key, '2010-11-01');
  $rest_response = file_get_contents($signed_request);
 
  $xml = simplexml_load_string($rest_response);
  $img_url = $xml->Items->Item[0]->LargeImage->URL;
  $referral_url = $xml->Items->Item[0]->DetailPageURL;
  
  $imgstyle = "";
  if($listing == true)
	$width = get_option('amazonx_listwidth');
  else
	$width = get_option('amazonx_postwidth');
  if($width != "")
    $imgstyle = 'style="width: ' . $width . 'px;"';
	
  $divstyle = "";
  $boxcolor = get_option('amazonx_boxcolor');
  $bordercolor = get_option('amazonx_bordercolor');
  if($boxcolor != "" || $bordercolor != "") {
	$divstyle = 'style="';
	if($boxcolor != "")
		$divstyle .= 'background: ' . $boxcolor . ';';
	if($bordercolor != "")
		$divstyle .= 'border: 1px solid ' . $bordercolor . ';';
	$divstyle .= '"';
  }
  
  return '<div class="amazonx-product" ' . $divstyle . '><center>'
	. '<a href="' . $referral_url . '">'
	. '<img class="amazonx-image" '. $imgstyle . ' src="' . $img_url . '"><br/>'
	. amazonx_below_image_html($asin, $rating) . '</a></center></div>';
}

function amazonx_the_content($content)
{
  global $post;		// grab post data so we can read the metadata
  
  //if(!is_singular()) // is_singular = is_single || is_page || is_attachment
  //  return $content;

  $id = $post->ID;		// this just gets page_id when in the booklist page
  $asin = get_post_meta($id, 'amazonx_asin', true);
  $rating = get_post_meta($id, 'amazonx_rating', true);
  
  if($asin == "")
    return $content;			// do nothing
  
  $star_html = "";
  if((int)get_option('amazonx_belowimg') != 2)
	$star_html = amazonx_star_html($rating);
	
  $clear = '<div style="clear: both; font-size: 0; height: 15px;"></div>';
	
  return amazonx_product_box_html($asin, $rating, false) . $content . $star_html . $clear;
}

function amazonx_admin_notice()
{
  if(get_option('amazonx_accesskey') == "" || get_option('amazonx_secretkey') == "")
  {
	echo '<div class="updated"><p>Amazon Express is not yet configured. Click <a href="';
	echo admin_url('options-general.php?page=amazonx');
	echo '">here</a> to access the configuration page.</p></div>';
  }
}

// initialization
wp_enqueue_style('amazonx', plugins_url('/amazonx.css', __FILE__));
add_shortcode('amazonx', 'amazonx_write_table');
// when Wordpress is rendering meta boxes, register metabox
add_action('admin_menu', 'amazonx_register_metabox');
add_action('admin_menu', 'amazonx_register_menu');
// Process and save the data entered in the metabox 
add_action('save_post', 'amazonx_save_metabox_data');
// Add cover images to beginning of posts
add_filter('the_content', 'amazonx_the_content');
add_action('admin_notices', 'amazonx_admin_notice');

?>