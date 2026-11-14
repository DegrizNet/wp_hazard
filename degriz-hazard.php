<?php
/**
 * Plugin Name:       Degriz Hazard
 * Plugin URI:        https://degriz.net
 * Description:       CLP hazard information for WooCommerce products: signal word, GHS pictograms, H and P statements and safety data sheet downloads.
 * Version:           1.0.1
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Author:            Degriz
 * Author URI:        https://degriz.net
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       degriz-hazard
 * Domain Path:       /languages
 * WC requires at least: 8.0
 *
 * @package degriz-hazard
 */

defined( 'ABSPATH' ) || exit;

define( 'DEGRIZ_HAZARD_VERSION', '1.0.1' );
define( 'DEGRIZ_HAZARD_FILE', __FILE__ );
define( 'DEGRIZ_HAZARD_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEGRIZ_HAZARD_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Degriz\\Hazard\\';
		if ( 0 !== strncmp( $class_name, $prefix, strlen( $prefix ) ) ) {
			return;
		}
		$short = strtolower( str_replace( '_', '-', substr( $class_name, strlen( $prefix ) ) ) );
		$file  = DEGRIZ_HAZARD_DIR . 'includes/class-' . $short . '.php';
		if ( is_file( $file ) ) {
			require_once $file;
		}
	}
);

// Compatibility with HPOS (custom order tables) and the Cart and Checkout blocks.
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', DEGRIZ_HAZARD_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', DEGRIZ_HAZARD_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain( 'degriz-hazard', false, dirname( plugin_basename( DEGRIZ_HAZARD_FILE ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function (): void {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Degriz Hazard requires WooCommerce to be active.', 'degriz-hazard' ) . '</p></div>';
				}
			);
			return;
		}

		\Degriz\Hazard\Plugin::instance()->init();
	},
	20
);

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	static function ( array $links ): array {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=degriz-hazard' ) ) . '">' . esc_html__( 'Settings', 'degriz-hazard' ) . '</a>' );
		return $links;
	}
);
