<?php
/**
 * Runs some simple security and vaildity checks on non-standard WordPress functionality.
 *
 * @package sb-security-check
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin & Theme Security Check
 * Description:       Runs some simple security and vaildity checks on non-standard WordPress functionality.
 * Plugin URI:        https://github.com/soup-bowl
 * Version:           0.1-dev
 * Author:            soup-bowl
 * Author URI:        https://www.soupbowl.io
 * License:           MIT
 * Text Domain:       wpsecuritycheck
 */

class sb_security_check {
	public function register() {
		add_filter( 'manage_plugins_columns', array( &$this, 'wpa65189_add_plugins_column' ) );
		add_action( 'manage_plugins_custom_column' , array( &$this, 'wpa65189_render_plugins_column' ), 10, 3 );
	}

	public function wpa65189_add_plugins_column( $columns ) {
		$columns['wpsc_status'] = 'Security Check';
		return $columns;
	}

	public function wpa65189_render_plugins_column( $column_name, $plugin_file, $plugin_data ) {
		if ( 'wpsc_status' == $column_name ) {
			if ( isset( $plugin_data['slug'] ) ) {
				$payload = wp_remote_get( "https://api.wordpress.org/plugins/info/1.0/{$plugin_data['slug']}.json" );
				if ( ! is_wp_error( $payload ) && 200 === wp_remote_retrieve_response_code( $payload ) ) {
					$plugin_details   = json_decode( wp_remote_retrieve_body( $payload ) );
					$last_update_date = DateTime::createFromFormat( 'Y-m-d', substr( $plugin_details->last_updated, 0, 10 ) );

					if( $last_update_date->diff( new DateTime() )->days > 365 ) {
						echo 'No updates in a year.';
					}
					#echo '<pre>';var_dump( $plugin_details );echo '</pre>';
				}
			}
		}
	}
}

( new sb_security_check() )->register();
