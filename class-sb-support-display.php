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
 * Checking/testing mechanisms.
 */
require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-sb-support-check.php';

/**
 * Human interface displays of the checking initalisation and results.
 */
class Sb_Support_Display {
	/**
	 * Checking/testing mechanisms.
	 *
	 * @var Sb_Support_Check
	 */
	private $checks;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->checks = new Sb_Support_Check();
	}

	/**
	 * Inform the WordPress system of our plugin functionality.
	 *
	 * @return void Runs filter/action registration hooks with WordPress.
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
		$columns['wpsc_status'] = esc_attr( 'Support Check', 'wpsecuritycheck' );

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
		$non_wp_fail_msg = Sb_Support_Emoji::QUERY . ' ' . __( 'Plugin not found on the WordPress directory.', 'wpsecuritycheck' );

		if ( 'wpsc_status' === $column_name ) {
			if ( isset( $plugin_data['url'], $plugin_data['slug'] ) && false !== strpos( $plugin_data['url'], '//wordpress.org/plugins' ) ) {
				$check_wp_site = $this->checks->check_wordpress_plugin_directory( $plugin_data['slug'] );
				$fails         = [];

				if ( $check_wp_site['success'] ) {
					// Check how long it has been since the plugin was updated.
					if ( $check_wp_site['age'] > 1 ) {
						// translators: %d is the number of years since the plugin in question last recieved an update on wordpress.org.
						$fails[] = sprintf( __( 'No updates in %d years(s)', 'wpsecuritycheck' ), $check_wp_site['age'] );
					}

					// Tell the user the results.
					$this->success_or_fail_message( $fails );
				} else {
					echo esc_html( $non_wp_fail_msg );
				}
			} else {
				echo esc_html( $non_wp_fail_msg );
			}
		}
	}

	/**
	 * Prints a message on-screen (when called) relating to the success of the tests.
	 *
	 * @param array|null $fails The fail-response messages provided by the tests.
	 * @return void Prints the status message to page.
	 */
	private function success_or_fail_message( $fails ) {
		if ( empty( $fails ) ) {
			$fails = [];
		}

		$fail_count = count( $fails );
		if ( $fail_count > 0 ) {
			$fail_disp = '';
			foreach ( $fails as $fail ) {
				$fail_disp .= '<li>' . esc_html( $fail ) . '</li>';
			}

			echo wp_kses(
				// Translators: %d is a count of how many checks failed.
				Sb_Support_Emoji::NEGATIVE . ' ' . sprintf( __( 'Failed %d check(s)', 'wpsecuritycheck' ), $fail_count ) . ":<ol>{$fail_disp}</ol>",
				[
					'ol' => [],
					'li' => [],
				]
			);
		} else {
			echo esc_html( Sb_Support_Emoji::POSITIVE . ' ' . __( 'Passed check(s).', 'wpsecuritycheck' ) );
		}
	}
}
