<?php
/**
 * Runs some simple security and vaildity checks on non-standard WordPress functionality.
 *
 * @package sb-security-check
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

/**
 * Emojis.
 */
require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-sb-support-emoji.php';

/**
 * Theme and plugin checking functionality.
 */
class Sb_Support_Check {
	/**
	 * Gets a collection of analysis information from the WordPress directory.
	 *
	 * @param string $slug WordPress plugin identifier.
	 * @return array 'success' boolean. 'message' upon fail.
	 */
	public function check_wordpress_plugin_directory( $slug ) {
		$payload = wp_remote_get( "https://api.wordpress.org/plugins/info/1.0/{$slug}.json" );
		if ( ! is_wp_error( $payload ) && 200 === wp_remote_retrieve_response_code( $payload ) ) {
			$plugin_details    = json_decode( wp_remote_retrieve_body( $payload ) );
			$last_update_date  = DateTime::createFromFormat( 'Y-m-d', substr( $plugin_details->last_updated, 0, 10 ) );
			$days_since_update = $last_update_date->diff( new DateTime() )->days;

			// Pulls in the current version of WordPress we're on ($wp_version).
			require ABSPATH . WPINC . '/version.php';

			return [
				'success'             => true,
				'remote_tested_wp'    => $plugin_details->tested,
				'remote_requires_wp'  => $plugin_details->requires,
				'remote_requires_php' => $plugin_details->requires_php,
				'age'                 => $this->days_to_years( $days_since_update ),
			];
		} else {
			return [
				'success' => false,
				'message' => 'plugin_not_found',
			];
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
