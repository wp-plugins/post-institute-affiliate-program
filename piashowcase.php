<?php
/**
  Plugin Name: ES Post Institute Affiliate Program
  Plugin URI: http://www.equalserving.com/wordpress-plugins/post-institute-affiliate-wordpress-plugin/
  Description: A plugin to feature from The Post Institute. Simply enter the product numbers of any product.
  Configuration requires entering an affiliate id to earn commissions.

  Author: EqualServing.com
  Version: 0.1
  Author URI: http://www.equalserving.com

  Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=H8KWRPTET2SK2&lc=US&item_name=Post%20Institute%20Affiliate%20Wordpress%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted

  To remove the RSS cache DELETE FROM wp_options WHERE option_name LIKE ('_transient%_feed_%')

*/

if ($_POST['option_page'] == "espiaaff-settings") {
	espiaaff_admin();
}

function do_not_cache_feeds(&$feed) {
	$feed->enable_cache(false);
}
if ( defined('WP_DEBUG') && WP_DEBUG )
	add_action( 'wp_feed_options', 'do_not_cache_feeds' );

function es_pia_display_widget($options = array("items" => 5)) {
	// The options passed to this widget are:
	// items - the number of products to list
	// random - randomly select [# of items] from the feed
	// prd_cds - specific codes to display.

	//print_r($options);
	// Get a SimplePie object from a feed source.
	$feed = 'feed://equalserving.com/client_area/durovy/affiliate_manager/';
	$feed = 'feed://postinstitute.com/affiliate/product/feed/';
	$items = $options['items'];
	if (isset($options['prd_cds']) && $options['prd_cds'] != "") {
		$feed .= "?prd_cds=".$options['prd_cds'];
	} else if (isset($options['random']) && ($options['prd_cds'] == "1" || strtolower($options['prd_cds']) == "yes")) {
		$feed .= "?random=1";
	}
	$rss = fetch_feed($feed);
	//print_r($rss);
	if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly
		// Figure out how many total items there are, but limit it to $items.
		$maxitems = $rss->get_item_quantity($items);

 		// Build an array of all the items, starting with element 0 (first element).
 		$rss_items = $rss->get_items(0, $maxitems);
	endif;
	$retVal = '<ul class="pia-products">';
	//$retVal .= "<ul>";
    if ($maxitems == 0) {
    	$retVal .= '<li>No items.</li>';
    } else {
	    // Loop through each feed item and display each item as a hyperlink.
    	foreach ( $rss_items as $item ) :
    		$link = $item->get_permalink()."&aff_id=".get_option("espiaaff_affiliate_id");
    		$retVal .= "    <li>";
   		 	$retVal .= '        <a href="'. esc_url( $link ).'" title="'.$item->get_title().'" class="title">'.esc_html( $item->get_title() )."</a>".html_entity_decode($item->get_description()).'<a href="'. esc_url( $link ).'" title="'.$item->get_title().'">Read more about '.$item->get_title().'</a>';
   		 	$retVal .= '<div class="clear">&nbsp;</div>';
	    	$retVal .= "    </li>";
		endforeach;
    	$retVal .= "</ul>";
	}
	return $retVal;
}
add_shortcode( 'es_pia_display_widget', 'es_pia_display_widget' );

/**
 * Settings link in the plugins page menu
 * @param array $links
 * @param string $file
 * @return array
 */
function espiaaff_set_plugin_meta($links, $file) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ($file == $plugin) {
		return array_merge(
			$links,
			array( sprintf( '<a href="admin.php?page='.$file.'">%s</a>', __('Settings') ),
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=H8KWRPTET2SK2&lc=US&item_name=Post%20Institute%20Affiliate%20Wordpress%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">Donate</a>')
		);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'espiaaff_set_plugin_meta', 10, 2 );

function espiaaff_admin_menu() {
	add_options_page('PI Affiliate Options', 'Post Institute Affiliate Settings', 'administrator', __FILE__, 'espiaaff_plugin_options');
	/* Using registered $page handle to hook stylesheet loading */
    add_action('admin_print_styles', 'espiaaff_plugin_admin_styles' );
}
add_action('admin_menu', 'espiaaff_admin_menu');

function espiaaff_plugin_admin_styles() {
	/*
	* It will be called only on your plugin admin page, enqueue your stylesheet here
	*/
	wp_enqueue_style( "piashowcase-admin-style", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/css/admin.css"));
}

function espiaaff_plugin_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if ( $_REQUEST['saved'] ) {
		echo '<div id="message" class="updated fade"><p><strong>PI Affiliate settings saved.</strong></p></div>';
	}
	if ( $_REQUEST['reset'] ) {
		echo '<div id="message" class="updated fade"><p><strong>PI Affiliate settings reset.</strong></p></div>';
	}

	echo '<div class="wrap espiaaff_settings_page">';
	echo '<div class="wrap">';
	echo '<div  class="icon32" style="margin:5px 90px 10px 10px;"><img src="'.  path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/images/weblogo.jpeg") .'"></div>' ;

	echo '<h2>Your Post Institute Affiliate Information</h2>';

	echo '<div id="poststuff" class="metabox-holder">';

	echo '<form name="updatesettings" id="updatesettings" method="post" action="'. $_SERVER['REQUEST_URI']. '">';

	echo '                    <div style="width:70%;margin-right:20px;float: left; min-height:500px;">';

	echo '                        <div class="meta-box">';

    settings_fields( 'espiaaff-settings' );
	echo '	<table class="form-table">';

	$pluginoptions = array (
		array("name" => __('Affiliate ID','thematic'),
			"desc" => __('Your Post Institute Affiliate ID','thematic'),
			"id" => "espiaaff_affiliate_id",
			"std" => "999999",
			"type" => "text"
		),
		array("name" => __('Load Default CSS','thematic'),
			"desc" => __('Load the default css file that comes with this plugin.','thematic'),
			"id" => "espiaaff_load_css",
			"std" => "9",
			"type" => "checkbox"
		),
	);

	foreach ($pluginoptions as $value) {
		// Output the appropriate form element
		switch ( $value['type'] ) {
			case 'text':
			?>
			<tr valign="top">
				<th scope="row"><?php echo $value['name']; ?>:</th>
				<td>
					<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text"
						value="<?php echo stripslashes(get_option( $value['id'],$value['std'] )); ?>"/>
					<?php echo $value['desc']; ?>
				</td>
			</tr>
			<?php
			break;
			case 'select':
			?>
			<tr valign="top">
				<th scope="row"><?php echo $value['name']; ?></th>
				<td>
					<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
						<option value="">--</option>
						<?php foreach ($value['options'] as $key=>$option) {
							if ($key == get_option($value['id'], $value['std']) ) {
								$selected = "selected=\"selected\"";
							} else {
								$selected = "";
							}
							?>
							<option value="<?php echo $key ?>" <?php echo $selected ?>> <?php echo $option; ?></option>
						<?php } ?>
					</select>
					<?php echo $value['desc']; ?>
				</td>
			</tr>
			<?php
			break;
			case 'textarea':
				$ta_options = $value['options'];
				?>
				<tr valign="top">
					<th scope="row"><?php echo $value['name']; ?>:</th>
					<td>
						<textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"
							cols="<?php echo $ta_options['cols']; ?>"
							rows="<?php echo $ta_options['rows']; ?>"><?php
							echo stripslashes(get_option($value['id'], $value['std'])); ?>
						</textarea>
						<br /><?php echo $value['desc']; ?>
					</td>
				</tr>
			<?php
			break;
			case "radio":
			?>
				<tr valign="top">
					<th scope="row"><?php echo $value['name']; ?>:</th>
					<td>
						<?php foreach ($value['options'] as $key=>$option) {
							if ($key == get_option($value['id'], $value['std']) ) {
								$checked = "checked=\"checked\"";
							} else {
								$checked = "";
							}
							?>
							<input type="radio"
								name="<?php echo $value['id']; ?>"
								value="<?php echo $key; ?>"
								<?php echo $checked; ?>
								/><?php echo $option; ?>
								<br />
						<?php } ?>
						<?php echo $value['desc']; ?>
					</td>
				</tr>
			<?php
			break;
			case "checkbox":
			?>
				<tr valign="top">
					<th scope="row"><?php echo $value['name']; ?></th>
					<td>
						<?php
						if(get_option($value['id'])){
							$checked = "checked=\"checked\"";
						} else {
							$checked = "";
						}
						?>
						<input type="checkbox"
							name="<?php echo $value['id']; ?>"
							id="<?php echo $value['id']; ?>"
							value="true"
							<?php echo $checked; ?>
							/>
							<?php echo $value['desc']; ?>
					</td>
				</tr>
			<?php
			break;
			default:
			break;
		}
	}
	echo '	</table>';
?>
</div></div>
                    <div style="float:left;width:28%">

                        <div class='meta-box'>
                          <div <?php $my_close_open_win_id = 'espiaaff_meta_settings_3'; ?>
                              id="<?php echo $my_close_open_win_id; ?>"
                              class="postbox <?php if ( '1' == get_user_option( 'espiaaff_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" >
                              <div title="<?php _e('Click to toggle','espiaaff'); ?>" class="handlediv"
                                   onclick="javascript:verify_window_opening(<?php $user = wp_get_current_user(); if (isset( $user->ID )){ echo $user->ID;} else {echo '0'; }; ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                <h3 class='hndle'><span><?php _e('Help', 'espiaaff'); ?></span></h3> <div class="inside">

                                    <div>

                                        <?php _e('<p>Do you need help to get this plugin working?</p>
<p><strong>Please check following resources: </strong></p>
<p>- Enroll in the <a href="http://www.postinstitute.com/trk.html?p=MDAFFMGR&w=asu" target="_blank">Post Institute Affiliate Program</a><br />
- Visit the <a href="http://www.equalserving.com/wordpress-plugins/post-institute-affiliate-wordpress-plugin/" target="_blank">Post Institute Affiliate WordPress Plugin</a><br />
- Check the <a href="http://www.equalserving.com/wordpress-plugins/post-institute-affiliate-wordpress-plugin/#espiaaff-install" target="_blank">detailed installation instruction</a><br />
- See <a href="http://wpdemo.equalserving.com/2011/11/affiliate-test/" target="_blank">how the plugin works</a></p>', 'espiaaff'); ?>

                                    </div>

                        </div> </div> </div>



                    </div>


                    <div style="float:left;width:28%">

                        <div class='meta-box'>
                          <div <?php $my_close_open_win_id = 'espiaaff_meta_settings_4'; ?>
                              id="<?php echo $my_close_open_win_id; ?>"
                              class="postbox <?php if ( '1' == get_user_option( 'espiaaff_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" >
                              <div title="<?php _e('Click to toggle','espiaaff'); ?>" class="handlediv"
                                   onclick="javascript:verify_window_opening(<?php $user = wp_get_current_user(); if (isset( $user->ID )){ echo $user->ID;} else {echo '0'; }; ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                <h3 class='hndle'><span><?php _e('Did you know?', 'espiaaff'); ?></span></h3> <div class="inside">

                                    <div>

                                        <?php _e('<p>You can add Post Institute product details and images into every post or page on your WordPress Blog.</p>
<p><strong>How do I add Post Institute product details and images into posts or pages?</strong></p>
<p>It is very easy, all you need to do is to add the following Shortcode into the place where you want to have the product details:<br /><br /><center>[espiaaff prd_cds="rocket"]</center><br /><br />Now replace the "rocket" with the product codes you want to display separated by a comma (,).', 'espiaaff'); ?>

                                    </div>

                        </div> </div> </div>



                    </div>

<div class="clear" style="height:0px;"></div>
<?php

	echo '	<p class="submit">';
	echo '	<input type="hidden" name="espiaaff_admin" value="update_settings" />';
	echo '	<input type="submit" class="button-primary" value="Save Changes" />';
	echo '	</p>';
	echo '	</form>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}

function register_espiaaffsettings() {
	//register our settings
	register_setting('espiaaff-settings', 'espiaaff_affiliate_id');
	register_setting('espiaaff-settings', 'espiaaff_load_css');
	add_option('espiaaff_affiliate_id', '');
	add_option('espiaaff_load_css', '');
}
add_action('admin_init', 'register_espiaaffsettings');

function espiaaff_admin()
{

	//print_r($_POST);
	switch ($_POST['action']) {

	case ("update"):

		update_option('espiaaff_affiliate_id', $_POST['espiaaff_affiliate_id']);
		update_option('espiaaff_load_css', $_POST['espiaaff_load_css']);
		break;

	case ("update_fields"):
		break;
	}
}

add_action( 'widgets_init', 'espiaaff_register_widgets' );

//register our widget

function espiaaff_register_widgets() {
	register_widget( 'espiaaff_widget_info' );
}

add_filter('the_posts', 'conditionally_add_piashowcase_scripts_and_styles'); // the_posts gets triggered before wp_head

function conditionally_add_piashowcase_scripts_and_styles($posts){
	if (empty($posts)) return $posts;

	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (stripos($post->post_content, '[es_pia_display_widget') !== false) {
			$shortcode_found = true; // bingo!
			break;
		}
	}

	if ($shortcode_found) {
		// enqueue here
		if (get_option("espiaaff_load_css")) {
			wp_enqueue_style( "piashowcase-style", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/css/piashowcase-page.css"));
		}
		//wp_enqueue_script( "piashowcase", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/js/piashowcase.js"), array( 'jquery' ) );
	}

	return $posts;
}

//espiaaff_widget_info class
class espiaaff_widget_info extends WP_Widget {

    //process the new widget
    function espiaaff_widget_info() {
        $widget_ops = array(
			'classname' => 'espiaaff_widget_class',
			'description' => 'Display Post Institute Affiliate products.'
			);
        $this->WP_Widget( 'espiaaff_widget_info', 'Post Institute Affiliate Widget', $widget_ops );
    	add_action('wp_enqueue_scripts', array(&$this, 'piashowcase_files'));
    }

     //build the widget settings form
    function form($instance) {
        $defaults = array( 'title' => 'Post Institute  Products', 'prd_cd' => '' );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
        $prd_cd = $instance['prd_cd'];
        ?>
            <p>Title: <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"  type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
            <p>Product Code: <input class="widefat" name="<?php echo $this->get_field_name( 'prd_cd' ); ?>"  type="text" value="<?php echo esc_attr( $prd_cd ); ?>" /></p>
        <?php
    }

    //save the widget settings
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['prd_cd'] = strip_tags( $new_instance['prd_cd'] );

        return $instance;
    }

	function piashowcase_files(){

 		if ( is_active_widget(false, false, $this->id_base, true) ) {
			wp_enqueue_script( "piashowcase", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/js/piashowcase.js"), array( 'jquery' ) );
			if (get_option("espiaaff_load_css")) {
				wp_enqueue_style( "piashowcase", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/css/piashowcase-widget.css"));
			}
 		}
	}

    //display the widget
    function widget($args, $instance) {

        extract($args);

        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        $prd_cd = empty( $instance['prd_cd'] ) ? '&nbsp;' : $instance['prd_cd'];

        if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
        //echo '<p>Product Code: ' . $prd_cd . '</p>';

		// Get a SimplePie object from a feed source.
		$feed = 'feed://equalserving.com/client_area/durovy/affiliate_manager/';
		$feed = 'feed://postinstitute.com/affiliate/product/feed/';
		if (isset($prd_cd) && $prd_cd != "") {
			$feed .= "?prd_cds=".$prd_cd;
		}
		//echo "<p>".$feed."</p>";
		$rss = fetch_feed($feed);
		//print_r($rss);
		if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly
			// Figure out how many total items there are, but limit it to 5.
			$maxitems = $rss->get_item_quantity(5);

	 		// Build an array of all the items, starting with element 0 (first element).
	 		$rss_items = $rss->get_items(0, $maxitems);
		endif;
		$retVal = '<div id="sidebar-pia-products">';
		$retVal .= "<ul>";
	    if ($maxitems == 0) {
	    	$retVal .= '<li>No items.</li>';
	    } else {
		    // Loop through each feed item and display each item as a hyperlink.
	    	foreach ( $rss_items as $item ) :
	    		$link = $item->get_permalink()."&aff_id=".get_option("espiaaff_affiliate_id");
	    		$retVal .= "    <li>";
	   		 	$retVal .= '        <a href="'. esc_url( $link ).'" title="'.$item->get_title().'" class="title">'.esc_html( $item->get_title() )."</a>".html_entity_decode($item->get_description()).'<a href="'. esc_url( $link ).'" title="'.$item->get_title().'">Read more about '.$item->get_title().'</a>';
		    	$retVal .= "    </li>";
			endforeach;
	    	$retVal .= "</ul></div>";

    	}
    	echo $retVal;
       	echo $after_widget;
	}
}
?>