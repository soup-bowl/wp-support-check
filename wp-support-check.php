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
