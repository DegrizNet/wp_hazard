<?php
/**
 * Product editor: the "Hazard (CLP)" tab, saving, bulk edit.
 *
 * @package degriz-hazard
 */

namespace Degriz\Hazard;

defined( 'ABSPATH' ) || exit;

class Admin {

	/**
	 * @return void
	 */
	public function init(): void {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save' ) );
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'bulk_fields' ) );
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'bulk_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * @param array $tabs Product data tabs.
	 * @return array
	 */
	public function tab( $tabs ) {
		$tabs['degriz_hazard'] = array(
			'label'    => __( 'Hazard (CLP)', 'degriz-hazard' ),
			'target'   => 'degriz_hazard_data',
			'class'    => array( 'hide_if_grouped' ),
			'priority' => 85,
		);
		return $tabs;
	}

	/**
	 * Scripts and styles on the product editor and the product list.
	 *
	 * @param string $hook Admin page hook.
	 * @return void
	 */
	public function assets( $hook ): void {
		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'degriz-hazard-admin', DEGRIZ_HAZARD_URL . 'assets/css/admin.css', array(), DEGRIZ_HAZARD_VERSION );
		wp_enqueue_script( 'degriz-hazard-admin', DEGRIZ_HAZARD_URL . 'assets/js/admin.js', array( 'jquery' ), DEGRIZ_HAZARD_VERSION, true );
		wp_localize_script(
			'degriz-hazard-admin',
			'degrizHazard',
			array(
				'chooseTitle'  => __( 'Choose a PDF', 'degriz-hazard' ),
				'chooseButton' => __( 'Use this file', 'degriz-hazard' ),
			)
		);
	}

	/**
	 * The panel.
	 *
	 * @return void
	 */
	public function panel(): void {
		global $post;
		$product = $post ? wc_get_product( $post->ID ) : false;
		$record  = $product ? Data::for_product( $product ) : array(
			'signal'     => '',
			'pictograms' => array(),
			'h'          => array(),
			'p'          => array(),
			'sds'        => 0,
			'tds'        => 0,
		);
		wp_nonce_field( 'degriz_hazard_save', 'degriz_hazard_nonce' );
		?>
		<div id="degriz_hazard_data" class="panel woocommerce_options_panel degriz-hz-panel">
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_html_e( 'Signal word', 'degriz-hazard' ); ?></label>
					<label class="degriz-hz-inline"><input type="radio" name="degriz_hz_signal" value="" <?php checked( $record['signal'], '' ); ?>> <?php esc_html_e( 'None', 'degriz-hazard' ); ?></label>
					<?php foreach ( Data::signal_words() as $key => $label ) : ?>
						<label class="degriz-hz-inline"><input type="radio" name="degriz_hz_signal" value="<?php echo esc_attr( $key ); ?>" <?php checked( $record['signal'], $key ); ?>> <?php echo esc_html( $label ); ?></label>
					<?php endforeach; ?>
				</p>
				<div class="form-field degriz-hz-pictos">
					<label><?php esc_html_e( 'GHS pictograms', 'degriz-hazard' ); ?></label>
					<ul class="degriz-hz-picto-list">
						<?php foreach ( Data::pictograms() as $code => $label ) : ?>
							<li>
								<label>
									<input type="checkbox" name="degriz_hz_pictograms[]" value="<?php echo esc_attr( $code ); ?>" <?php checked( in_array( $code, $record['pictograms'], true ) ); ?>>
									<img src="<?php echo esc_url( DEGRIZ_HAZARD_URL . 'assets/pictograms/' . $code . '.svg' ); ?>" alt="" width="48" height="48">
									<span><?php echo esc_html( $code . ' ' . $label ); ?></span>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label for="degriz_hz_h"><?php esc_html_e( 'Hazard statements (H)', 'degriz-hazard' ); ?></label>
					<?php $this->statement_select( 'degriz_hz_h', Data::statements( 'H' ), $record['h'] ); ?>
				</p>
				<p class="form-field">
					<label for="degriz_hz_p"><?php esc_html_e( 'Precautionary statements (P)', 'degriz-hazard' ); ?></label>
					<?php $this->statement_select( 'degriz_hz_p', Data::statements( 'P' ), $record['p'] ); ?>
				</p>
			</div>
			<div class="options_group">
				<?php
				$this->file_field( 'degriz_hz_sds', __( 'Safety data sheet (PDF)', 'degriz-hazard' ), $record['sds'] );
				$this->file_field( 'degriz_hz_tds', __( 'Technical data sheet (PDF)', 'degriz-hazard' ), $record['tds'] );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Multi select of statements, enhanced by WooCommerce (selectWoo).
	 *
	 * @param string               $name     Field name.
	 * @param array<string,string> $options  Code => text.
	 * @param string[]             $selected Selected codes.
	 * @return void
	 */
	private function statement_select( string $name, array $options, array $selected ): void {
		?>
		<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>[]" class="wc-enhanced-select degriz-hz-select" multiple="multiple">
			<?php foreach ( $options as $code => $text ) : ?>
				<option value="<?php echo esc_attr( $code ); ?>" <?php selected( in_array( $code, $selected, true ) ); ?>><?php echo esc_html( $code . ' - ' . $text ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * PDF picker field.
	 *
	 * @param string $name  Field name.
	 * @param string $label Label.
	 * @param int    $id    Attachment id.
	 * @return void
	 */
	private function file_field( string $name, string $label, int $id ): void {
		$file = $id ? get_the_title( $id ) : '';
		?>
		<p class="form-field degriz-hz-file">
			<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
			<input type="hidden" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $id ); ?>">
			<span class="degriz-hz-file-name"><?php echo esc_html( $file ); ?></span>
			<button type="button" class="button degriz-hz-pick" data-target="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Choose file', 'degriz-hazard' ); ?></button>
			<button type="button" class="button-link degriz-hz-clear" data-target="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Remove', 'degriz-hazard' ); ?></button>
		</p>
		<?php
	}

	/**
	 * A PDF attachment id, or 0.
	 *
	 * @param mixed $value Posted value.
	 * @return int
	 */
	private function pdf_id( $value ): int {
		$id = absint( $value );
		return $id && 'application/pdf' === get_post_mime_type( $id ) ? $id : 0;
	}

	/**
	 * Save the panel.
	 *
	 * @param \WC_Product $product Product being saved.
	 * @return void
	 */
	public function save( $product ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified below; every value is checked against the allowed lists.
		if ( ! isset( $_POST['degriz_hazard_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['degriz_hazard_nonce'] ) ), 'degriz_hazard_save' ) ) {
			return;
		}
		$signal = isset( $_POST['degriz_hz_signal'] ) ? sanitize_key( wp_unslash( $_POST['degriz_hz_signal'] ) ) : '';
		$product->update_meta_data( Data::META_SIGNAL, array_key_exists( $signal, Data::signal_words() ) ? $signal : '' );
		$product->update_meta_data( Data::META_PICTO, Data::sanitize_codes( wp_unslash( $_POST['degriz_hz_pictograms'] ?? array() ), array_keys( Data::pictograms() ) ) );
		$product->update_meta_data( Data::META_H, Data::sanitize_codes( wp_unslash( $_POST['degriz_hz_h'] ?? array() ), array_keys( Data::statements( 'H' ) ) ) );
		$product->update_meta_data( Data::META_P, Data::sanitize_codes( wp_unslash( $_POST['degriz_hz_p'] ?? array() ), array_keys( Data::statements( 'P' ) ) ) );
		$product->update_meta_data( Data::META_SDS, $this->pdf_id( wp_unslash( $_POST['degriz_hz_sds'] ?? 0 ) ) );
		$product->update_meta_data( Data::META_TDS, $this->pdf_id( wp_unslash( $_POST['degriz_hz_tds'] ?? 0 ) ) );
		// phpcs:enable
	}

	/**
	 * Fields in the bulk edit box of the product list.
	 *
	 * @return void
	 */
	public function bulk_fields(): void {
		wp_nonce_field( 'degriz_hazard_bulk', 'degriz_hazard_bulk_nonce' );
		?>
		<div class="inline-edit-group degriz-hz-bulk">
			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Signal word', 'degriz-hazard' ); ?></span>
				<select name="degriz_hz_bulk_signal">
					<option value=""><?php esc_html_e( '- No change -', 'degriz-hazard' ); ?></option>
					<?php foreach ( Data::signal_words() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
					<option value="__clear"><?php esc_html_e( 'Clear all hazard data', 'degriz-hazard' ); ?></option>
				</select>
			</label>
			<div class="alignleft degriz-hz-bulk-pictos">
				<span class="title"><?php esc_html_e( 'Add GHS pictograms', 'degriz-hazard' ); ?></span>
				<?php foreach ( Data::pictograms() as $code => $label ) : ?>
					<label><input type="checkbox" name="degriz_hz_bulk_pictograms[]" value="<?php echo esc_attr( $code ); ?>"> <?php echo esc_html( $code ); ?></label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Apply the bulk edit: set the signal word, add pictograms, or clear everything.
	 *
	 * @param \WC_Product $product Product being saved.
	 * @return void
	 */
	public function bulk_save( $product ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified below; values are checked against the allowed lists.
		if ( ! isset( $_REQUEST['degriz_hazard_bulk_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['degriz_hazard_bulk_nonce'] ) ), 'degriz_hazard_bulk' ) ) {
			return;
		}
		$signal = isset( $_REQUEST['degriz_hz_bulk_signal'] ) ? sanitize_key( wp_unslash( $_REQUEST['degriz_hz_bulk_signal'] ) ) : '';
		$add    = Data::sanitize_codes( wp_unslash( $_REQUEST['degriz_hz_bulk_pictograms'] ?? array() ), array_keys( Data::pictograms() ) );
		// phpcs:enable

		if ( '__clear' === $signal ) {
			foreach ( array( Data::META_SIGNAL, Data::META_PICTO, Data::META_H, Data::META_P, Data::META_SDS, Data::META_TDS ) as $key ) {
				$product->delete_meta_data( $key );
			}
			$product->save_meta_data();
			return;
		}
		if ( array_key_exists( $signal, Data::signal_words() ) ) {
			$product->update_meta_data( Data::META_SIGNAL, $signal );
		}
		if ( $add ) {
			$have = $product->get_meta( Data::META_PICTO );
			$have = is_array( $have ) ? $have : array();
			$all  = array_values( array_unique( array_merge( $have, $add ) ) );
			sort( $all );
			$product->update_meta_data( Data::META_PICTO, $all );
		}
		// WooCommerce fires this hook after it has saved the product, so the meta is saved here.
		$product->save_meta_data();
	}
}
