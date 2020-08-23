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

if ( ! class_exists( 'Sb_Security_Check' ) ) {
	require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-sb-support-display.php';

	( new Sb_Support_Display() )->register();
}

add_filter(
	'site_status_tests',
	function( $tests ) {
		$tests['direct']['sb_checks'] = [
			'label' => __( 'Theme & Plugin Support Checks', 'wpsecuritycheck' ),
			'test'  => 'sb_support_check',
		];

		return $tests;
	}
);

/**
 * Site status tests integration test runner.
 */
function sb_support_check() {
	$result = array(
		'label'       => __( 'Theme & plugin support checks passed', 'wpsecuritycheck' ),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Security' ),
			'color' => 'blue',
		),
		'description' => __( 'Checks against your WordPress themes and plugins have passed.', 'wpsecuritycheck' ),
		'actions'     => '',
	);

	require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-sb-support-check.php';
	$check = new Sb_Support_Check();

	$fails   = [];
	$plugins = get_plugins();
	foreach ( $plugins as $plugin ) {
		if ( isset( $plugin['Name'] ) ) {
			$plugin_fails = [];
			$slug         = sanitize_title( $plugin['Name'] );
			$resp         = $check->check_wordpress_plugin_directory( $slug );

			if ( $resp['success'] ) {
				if ( ! $resp['age_test']['passed'] ) {
					// translators: %d is the number of years since the plugin in question last recieved an update on wordpress.org.
					$fails[ $plugin['Title'] ][] = sprintf( __( 'No updates in %d years(s).', 'wpsecuritycheck' ), $resp['age_test']['age'] );
				}
			}
		}
	}

	if ( count( $fails ) > 0 ) {
		$html = '';
		foreach ( $fails as $plugin_title => $plugin_fail ) {
			$html .= "<p><strong>{$plugin_title}</strong></p><ol>";
			foreach ( $plugin_fail as $subfail ) {
				$html .= "<li>{$subfail}</li>";
			}
			$html .= '</ol>';
		}
		$description = wp_kses_post( $html );

		$result['label']       = __( 'Some theme & plugin checks have failed', 'wpsecuritycheck' );
		$result['status']      = 'recommended';
		$result['description'] = $description;
	}

	return $result;
}
