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

/**
 * Main operatioal class.
 */
class Sb_Security_Check {
	/**
	 * Inform the WordPress system of our plugin functionality.
	 */
	public function register() {
		add_filter( 'manage_plugins_columns', array( &$this, 'plugin_table_columns' ) );
		add_action( 'manage_plugins_custom_column', array( &$this, 'plugin_table_content' ), 10, 3 );
	}

	/**
	 * Register the plugin column.
	 *
	 * @param string[] $columns Column names.
	 * @return string[]
	 */
	public function plugin_table_columns( $columns ) {
		$columns['wpsc_status'] = 'Security Check';

		return $columns;
	}

	/**
	 * Process the response displayed in the plugin column.
	 *
	 * @param string $column_name Processed column name.
	 * @param string $plugin_file The location of the plugin operational file.
	 * @param array  $plugin_data Information about the currently processed plugin.
	 * @return void Function prints to the page.
	 */
	public function plugin_table_content( $column_name, $plugin_file, $plugin_data ) {
		if ( 'wpsc_status' === $column_name ) {
			if ( isset( $plugin_data['slug'] ) ) {
				$payload = wp_remote_get( "https://api.wordpress.org/plugins/info/1.0/{$plugin_data['slug']}.json" );
				if ( ! is_wp_error( $payload ) && 200 === wp_remote_retrieve_response_code( $payload ) ) {
					$plugin_details   = json_decode( wp_remote_retrieve_body( $payload ) );
					$last_update_date = DateTime::createFromFormat( 'Y-m-d', substr( $plugin_details->last_updated, 0, 10 ) );

					$days_since_update = $last_update_date->diff( new DateTime() )->days;
					if ( $last_update_date->diff( new DateTime() )->days > 365 ) {
						$years = $this->days_to_years( $days_since_update );
						$label = ( 1 === $years ) ? 'year' : 'years';

						echo esc_html( Sb_Support_Emoji::NEGATIVE . " No updates in {$years} {$label}." );
					} else {
						echo esc_html( Sb_Support_Emoji::POSITIVE );
					}
					// echo '<pre>';var_dump( $plugin_details );echo '</pre>';.
				} else {
					echo esc_html( Sb_Support_Emoji::QUERY . ' Plugin not found on the WordPress directory.' );
				}
			} else {
				echo esc_html( Sb_Support_Emoji::QUERY . ' Plugin information not found.' );
			}
		}
	}

	/**
	 * Converts days to years.
	 *
	 * @param integer $days Numerical day reference.
	 * @return integer
	 */
	private function days_to_years( $days ) {
		return floor( $days / 365 );
	}
}

/**
 * Emojis provided by latin reference.
 */
class Sb_Support_Emoji {
	const POSITIVE = "\u{2714}";
	const NEGATIVE = "\u{274C}";
	const QUERY    = "\u{2753}";
}

( new Sb_Security_Check() )->register();
