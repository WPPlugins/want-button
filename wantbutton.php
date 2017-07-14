<?php
/*
Plugin Name: Wordpress Want Button
Plugin URI: http://wantbutton.com
Description: This provides publishers and users to add a Want button to the end of their posts.  The product information can be filled in on the post and edit pages.
Version: 1.0
Author: gjagasia
Author URI: http://wantbutton.com
License: GPL
*/

/*  Copyright 2012  gjagasia  (gjagasia@wanttt.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$function_names_used = array('want_button_script', 'register_jquery', 'error_check_js', 'get_want_button',
							'append_want_button_bottom','want_button_box','want_button_box_save','want_button_box_content',
							'check_version');
$error_items = '';


// Set of hooks that operate the plugin.

register_activation_hook(WP_PLUGIN_DIR . '/want-wordpress/wantbutton.php', 'check_version' );
add_action('save_post', 'want_button_box_save' );
add_action('wp_head', 'want_button_script');
add_action('admin_init', 'register_jquery');
add_action('admin_head', 'error_check_js');
add_action('admin_init', 'want_button_box' );


function some_unique_function_name_cannot_load()
{
      global $error_items;
      print '<div class="error"><p><strong>'
      . __('The "Wordpress Want Button" plugin cannot load correctly')
      . '</strong> '
      . __('Another plugin has declared conflicting class, function, or
   			constant names:')
      . "<ul'>$error_items</ul>"
      . '</p><p>'
      . __  ('You must deactivate the plugins that are using these
   			conflicting names.')
      . '</p></div>';
}

function check_version() {
	global $wp_version;
	if (version_compare($wp_version, '3.0', '<')) {
	exit("This plugin requires WordPress 3.0 or greater.");
	}
}

// This is static Want button javascript.  This shouldn't be changed, if you intend for the button to continue working.

function want_button_script()
{
	echo '<script>
			(function() {
			        var _w = document.createElement("script"); _w.type = "text/javascript"; _w.async = true;
			        _w.src = "http://button.wanttt.com/button/script/";
			        var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(_w, s);
			})();
			</script>';
}

// Make sure we have access to jquery in the admin panel.

function register_jquery() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
    wp_enqueue_script( 'jquery' );
}

// Using jquery, while the toggle for the Want button is enabled, it won't let you submit if some required fields are missing.  Uncheck the box and it'll work fine.

function error_check_js() {
    echo '<script type="text/javascript"> 
		$(document).ready(function() {
			$("#publish").click(function(event) {
				if ($("#wb_enable_toggle").is(":checked") == true) {
					if ($("#wb_product_name").val().length == 0) {
						event.preventDefault();
						alert("Product Name is empty and required, please enter a value.");
					}
					if ($("#wb_merchant_name").val().length == 0) {
						event.preventDefault();
						alert("Merchant name is empty and required, please enter a value.")
					}
					if ($("#wb_image_url").val().length == 0) {
						event.preventDefault();
						alert("Product Image URL is empty and required, please enter a valid URL.")
					}
					if ($("#wb_product_price").val().length == 0) {
						$("#wb_product_price").val() = 0;
					}
				}
			});
		});
	  </script>';
}

// This takes in the parameters and builds the want button for posting, based on the post meta data box.

function get_want_button( ){
	global $post;
	$postID = $post->ID;

	$wb_product_url = get_post_meta( $postID, 'wb_product_url', true);
	$wb_product_name = get_post_meta( $postID, 'wb_product_name', true);
	$wb_product_price = get_post_meta( $postID, 'wb_product_price', true);
	$wb_merchant_name = get_post_meta( $postID, 'wb_merchant_name', true);
	$wb_image_url = get_post_meta( $postID, 'wb_image_url', true);
	$wb_enable_toggle = get_post_meta( $postID, 'wb_enable_toggle', 1);


	// Re-add this code if you want to append Gangnam Style to your posts.  
	// I highly recommend against this.
	// That song is toxic.
	//   -Gandhi
	// if ( $wb_enable_toggle == 0) {
	// 	$wantbutton = '<img src="http://25.media.tumblr.com/tumblr_m879e8m2AF1rwq9q8o1_500.gif">
	// 				   <p>I hope you are happy.</p>';
	// }
	if ( $wb_enable_toggle == 1) {
	$wantbutton =   '<a href="http://wanttt.com/want/initial_popup/" 
					data-return_url="' . $wb_product_url . '" 
					data-merchant_name="' . $wb_merchant_name . '" 
					data-title="' . $wb_product_name . '" 
					data-price="' . $wb_product_price . '" 
					data-image_url="' . $wb_image_url . '" 
					data-count="true" data-style="wb1" data-page_source="WPPLUGIN" class="wantButton"></a>';

	return $wantbutton;
	}
	else {
		$wantbutton = '';
		return $wantbutton;
	}
}

// Append on the want button.  
// Version 1.0 doesn't allow you to change how the want button is oriented on the page.
// This is in the scope for later versions, depending on user interest.

if( !function_exists("append_want_button_bottom")){
	function append_want_button_bottom($content){
		if( !is_page( )){
			$wantbutton = get_want_button( );

			// This is simply an append to the end of content.
			return $content . $wantbutton;
		} else{
			return $content;
		}
	}

	/*	add our filter function to the hook */

	add_filter('the_content', 'append_want_button_bottom');
}

// Add want meta box to the post and edit pages.

function want_button_box() {
	add_meta_box( 'want_button_box','Want Button Settings', 'want_button_box_content', 'page', 'advanced', 'high' );
	add_meta_box( 'want_button_box','Want Button Settings', 'want_button_box_content', 'post', 'advanced', 'high' );
}


// Style the want button meta box for the post and edit pages.

function want_button_box_content( $post ) {

	$wb_merchant_name = get_post_meta( $post->ID, 'wb_merchant_name', true);
	$wb_product_url = get_post_meta( $post->ID, 'wb_product_url', true);
	$wb_product_name = get_post_meta( $post->ID, 'wb_product_name', true);
	$wb_product_price = get_post_meta( $post->ID, 'wb_product_price', true);
	$wb_image_url = get_post_meta( $post->ID, 'wb_image_url', true);
	$wb_enable_toggle = get_post_meta( $post->ID, 'wb_enable_toggle', 1);

	?>

	<p>
	</p>
		<p>
		<input name="wb_enable_toggle" id="wb_enable_toggle" type="checkbox" value="1"
		<?php checked( ($wb_enable_toggle === true) ); ?>/>
		<label for="wb_enable_toggle">Enable Want Button</label>
		<p class="description">
			Enable the Want button for this page.
		</p>
 		<input type="hidden" name="wb_button_what_is_this" value="1" />
	</p>

	<p>

		<table class="form-table inside">
			<tr valign="top">
				<td>
					<label for="wb_merchant_name">Name of the Merchant (required)</label><br/>
					<input type="text" id="wb_merchant_name" name="wb_merchant_name" value="<?php echo $wb_merchant_name; ?>" class="widefat"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="wb_product_url">Product Detail Page URL (if left blank it will default to the blog post URL)</label><br/>
					<input type="text" id="wb_product_url" name="wb_product_url" value="<?php echo $wb_product_url; ?>" class="widefat"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="wb_product_name">Product Name (required)</label><br/>
					<input type="text" id="wb_product_name" name="wb_product_name" value="<?php echo $wb_product_name; ?>" class="widefat"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="wb_product_price">Price (if left blank it will default to N/A)</label><br/>
					<input type="text" id="wb_product_price" name="wb_product_price" value="<?php echo $wb_product_price; ?>" class="widefat"/>
				</td>
			</tr>
			<tr valign="top">
				<td>
					<label for="wb_image_url">High Resolution Product Image URL (required)</label><br/>
					<input type="text" id="wb_image_url" name="wb_image_url" value="<?php echo $wb_image_url; ?>" class="widefat"/>
				</td>
			</tr>
		</table>
	</p>		
		
	<?php	
}

// Actually save the post meta data on the edit and post pages.

function want_button_box_save( $post_id ) {
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	// Record sharing disable - how about fuck you
	if ( isset( $_POST['post_type'] ) && ( 'post' == $_POST['post_type'] || 'page' == $_POST['post_type'] ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			if ( isset( $_POST['wb_button_what_is_this'] ) ) {					


				if ( isset( $_POST['wb_merchant_name'] ) && isset( $_POST['wb_product_url'] ) && isset( $_POST['wb_product_name'] ) && isset( $_POST['wb_product_price'] )  && isset( $_POST['wb_image_url'] )) {
					$urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

					if (eregi($urlregex, $_POST['wb_product_url'])) {
						update_post_meta( $post_id, 'wb_product_url', $_POST['wb_product_url']);
					} 
					else {
						update_post_meta( $post_id, 'wb_product_url', $_POST['']);
					}

					update_post_meta( $post_id, 'wb_merchant_name', $_POST['wb_merchant_name']);
					update_post_meta( $post_id, 'wb_product_name', $_POST['wb_product_name']);

					update_post_meta( $post_id, 'wb_product_price', $_POST['wb_product_price']);

					update_post_meta( $post_id, 'wb_image_url', $_POST['wb_image_url']);
					update_post_meta( $post_id, 'wb_enable_toggle', $_POST['wb_enable_toggle']);
				}					
				else {
					delete_post_meta( $post_id, 'wb_merchant_name' );
					delete_post_meta( $post_id, 'wb_product_url' );
					delete_post_meta( $post_id, 'wb_product_name' );
					delete_post_meta( $post_id, 'wb_product_price' );
					delete_post_meta( $post_id, 'wb_image_url' );
					delete_post_meta( $post_id, 'wb_enable_toggle');
				}
			}
		}
	}

	return $post_id;
}





?>