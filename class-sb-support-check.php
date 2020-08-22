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
 * Main operatioal class.
 */
class Sb_Support_Check {
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
		$columns['wpsc_status'] = esc_attr_e( 'Support Check', 'wpsecuritycheck' );

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
				$check_wp_site = $this->check_wordpress_directory( $plugin_data['slug'] );

				if ( $check_wp_site['success'] ) {
					if ( $check_wp_site['age'] > 1 ) {
						$label = ( 1 === $check_wp_site['age'] ) ? 'year' : 'years';
						echo esc_html( Sb_Support_Emoji::NEGATIVE . " No updates in {$check_wp_site['age']} {$label}." );
					} else {
						echo esc_html( Sb_Support_Emoji::POSITIVE );
					}
				} else {
					echo esc_html( $check_wp_site['message'] );
				}
			} else {
				echo esc_html( Sb_Support_Emoji::QUERY . ' Plugin information not found.' );
			}
		}
	}

	/**
	 * Gets a collection of analysis information from the WordPress directory.
	 *
	 * @param string $slug WordPress plugin identifier.
	 * @return array 'success' boolean. 'message' upon fail.
	 */
	private function check_wordpress_directory( $slug ) {
		$payload = wp_remote_get( "https://api.wordpress.org/plugins/info/1.0/{$slug}.json" );
		if ( ! is_wp_error( $payload ) && 200 === wp_remote_retrieve_response_code( $payload ) ) {
			$plugin_details   = json_decode( wp_remote_retrieve_body( $payload ) );
			$last_update_date = DateTime::createFromFormat( 'Y-m-d', substr( $plugin_details->last_updated, 0, 10 ) );

			$days_since_update = $last_update_date->diff( new DateTime() )->days;
			return [
				'success' => true,
				'age'     => $this->days_to_years( $days_since_update ),
			];
		} else {
			return [
				'success' => false,
				'message' => Sb_Support_Emoji::QUERY . ' Plugin not found on the WordPress directory.',
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
