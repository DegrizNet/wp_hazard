<?php
/**
 * Settings: storage, defaults, and the page under WooCommerce.
 *
 * @package degriz-hazard
 */

namespace Degriz\Hazard;

defined( 'ABSPATH' ) || exit;

class Settings {

	public const OPTION = 'degriz_hazard_settings';

	/**
	 * Default values.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults(): array {
		return array(
			'show_tab'   => 1,
			'show_cards' => 0,
			'tab_title'  => '',
			'overrides'  => '',
		);
	}

	/**
	 * Statement texts the shop completed: one "CODE | text" per line.
	 *
	 * @return array<string,string> code => text
	 */
	public static function overrides(): array {
		$out = array();
		foreach ( preg_split( '/\R/', (string) self::get( 'overrides' ) ) as $line ) {
			$parts = explode( '|', $line, 2 );
			if ( 2 === count( $parts ) && preg_match( '/^(EUH|H|P)[0-9A-Za-z+]+$/', trim( $parts[0] ) ) && '' !== trim( $parts[1] ) ) {
				$out[ trim( $parts[0] ) ] = trim( $parts[1] );
			}
		}
		return $out;
	}

	/**
	 * One setting, with its default.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function get( string $key ) {
		$saved    = get_option( self::OPTION, array() );
		$defaults = self::defaults();
		if ( is_array( $saved ) && array_key_exists( $key, $saved ) ) {
			return $saved[ $key ];
		}
		return $defaults[ $key ] ?? null;
	}

	/**
	 * Title of the tab and of the block.
	 *
	 * @return string
	 */
	public static function tab_title(): string {
		$title = trim( (string) self::get( 'tab_title' ) );
		return '' === $title ? __( 'Safety and warnings', 'degriz-hazard' ) : $title;
	}

	/**
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
	}

	/**
	 * @return void
	 */
	public function menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Hazard information (CLP)', 'degriz-hazard' ),
			__( 'Hazard (CLP)', 'degriz-hazard' ),
			'manage_woocommerce',
			'degriz-hazard',
			array( $this, 'render_page' )
		);
	}

	/**
	 * @return void
	 */
	public function register(): void {
		register_setting(
			'degriz_hazard',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitise the posted settings (unchecked boxes are absent).
	 *
	 * @param mixed $input Posted values.
	 * @return array<string,mixed>
	 */
	public function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();
		return array(
			'show_tab'   => empty( $input['show_tab'] ) ? 0 : 1,
			'show_cards' => empty( $input['show_cards'] ) ? 0 : 1,
			'tab_title'  => sanitize_text_field( (string) ( $input['tab_title'] ?? '' ) ),
			'overrides'  => sanitize_textarea_field( (string) ( $input['overrides'] ?? '' ) ),
		);
	}

	/**
	 * Settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$name = self::OPTION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hazard information (CLP)', 'degriz-hazard' ); ?></h1>
			<?php settings_errors(); ?>
			<p><?php esc_html_e( 'The hazard data is entered per product, in the product editor, tab "Hazard (CLP)". Variations use the data of their parent product.', 'degriz-hazard' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'degriz_hazard' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Product page', 'degriz-hazard' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_tab]" value="1" <?php checked( (int) self::get( 'show_tab' ), 1 ); ?>> <?php esc_html_e( 'Show the safety tab', 'degriz-hazard' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="dhz-title"><?php esc_html_e( 'Tab title', 'degriz-hazard' ); ?></label></th>
						<td><input id="dhz-title" type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[tab_title]" value="<?php echo esc_attr( (string) self::get( 'tab_title' ) ); ?>" placeholder="<?php echo esc_attr__( 'Safety and warnings', 'degriz-hazard' ); ?>">
							<p class="description"><?php esc_html_e( 'Leave empty for the default title.', 'degriz-hazard' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Product cards', 'degriz-hazard' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_cards]" value="1" <?php checked( (int) self::get( 'show_cards' ), 1 ); ?>> <?php esc_html_e( 'Show small pictograms under the price (shop, categories, related products)', 'degriz-hazard' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="dhz-overrides"><?php esc_html_e( 'Statement texts', 'degriz-hazard' ); ?></label></th>
						<td><textarea id="dhz-overrides" class="large-text code" rows="6" name="<?php echo esc_attr( $name ); ?>[overrides]" placeholder="P501 | ..."><?php echo esc_textarea( (string) self::get( 'overrides' ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'The bundled list is the Slovenian list of the European Commission, which shortens some statements (for example P501 ends with "..."). To show a complete text, enter it here, one per line as: CODE | text.', 'degriz-hazard' ); ?></p></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
