<?php
/**
 * Plugin bootstrap.
 *
 * @package degriz-hazard
 */

namespace Degriz\Hazard;

defined( 'ABSPATH' ) || exit;

class Plugin {

	/** @var Plugin|null */
	private static $instance = null;

	/**
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return void
	 */
	public function init(): void {
		( new Settings() )->init();
		( new Display() )->init();
		if ( is_admin() ) {
			( new Admin() )->init();
		}
	}
}
