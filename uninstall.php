<?php
/**
 * Uninstall cleanup: settings and the hazard data stored on products.
 *
 * @package degriz-hazard
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'degriz_hazard_settings' );

foreach ( array( '_degriz_hz_signal', '_degriz_hz_pictograms', '_degriz_hz_h', '_degriz_hz_p', '_degriz_hz_sds_id', '_degriz_hz_tds_id' ) as $degriz_hz_key ) {
	delete_post_meta_by_key( $degriz_hz_key );
}
