<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function espiaaff_delete_plugin() {
	global $wpdb;

	delete_option('espiaaff_affiliate_id');
	delete_option('espiaaff_load_css');

}

espiaaff_delete_plugin();

?>