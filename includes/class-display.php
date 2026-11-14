<?php
/**
 * Front end: the "Safety and warnings" tab on the product page and small pictograms on product cards.
 *
 * @package degriz-hazard
 */

namespace Degriz\Hazard;

defined( 'ABSPATH' ) || exit;

class Display {

	/**
	 * @return void
	 */
	public function init(): void {
		add_filter( 'woocommerce_product_tabs', array( $this, 'tab' ) );
		add_filter( 'woocommerce_get_price_html', array( $this, 'card_pictograms' ), 30, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * @return void
	 */
	public function enqueue(): void {
		wp_enqueue_style( 'degriz-hazard', DEGRIZ_HAZARD_URL . 'assets/css/frontend.css', array(), DEGRIZ_HAZARD_VERSION );
	}

	/**
	 * Add the tab when the product has hazard data.
	 *
	 * @param array $tabs Product tabs.
	 * @return array
	 */
	public function tab( $tabs ) {
		if ( ! Settings::get( 'show_tab' ) ) {
			return $tabs;
		}
		global $product;
		if ( ! $product instanceof \WC_Product || ! Data::has_content( Data::for_product( $product ) ) ) {
			return $tabs;
		}
		$tabs['degriz_hazard'] = array(
			'title'    => Settings::tab_title(),
			'priority' => 30,
			'callback' => array( $this, 'tab_content' ),
		);
		return $tabs;
	}

	/**
	 * Tab content.
	 *
	 * @return void
	 */
	public function tab_content(): void {
		global $product;
		if ( $product instanceof \WC_Product ) {
			echo $this->render( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template.
		}
	}

	/**
	 * Small pictograms under the price on product cards (optional).
	 *
	 * @param string      $html    Price HTML.
	 * @param \WC_Product $product Product.
	 * @return string
	 */
	public function card_pictograms( $html, $product ) {
		if ( ! Settings::get( 'show_cards' ) || ! $product instanceof \WC_Product ) {
			return $html;
		}
		if ( is_singular( 'product' ) && (int) get_queried_object_id() === $product->get_id() ) {
			return $html;
		}
		$record = Data::for_product( $product );
		if ( ! $record['pictograms'] && '' === $record['signal'] ) {
			return $html;
		}
		return $html . $this->render_pictograms( $record, 'degriz-hz__pictos--small' );
	}

	/**
	 * Pictogram row.
	 *
	 * @param array  $record Hazard record.
	 * @param string $css_class Extra CSS class.
	 * @return string
	 */
	private function render_pictograms( array $record, string $css_class = '' ): string {
		$labels = Data::pictograms();
		$html   = '';
		foreach ( $record['pictograms'] as $code ) {
			if ( ! isset( $labels[ $code ] ) ) {
				continue;
			}
			$html .= '<img class="degriz-hz__picto" src="' . esc_url( DEGRIZ_HAZARD_URL . 'assets/pictograms/' . $code . '.svg' ) . '" alt="' . esc_attr( $code . ' ' . $labels[ $code ] ) . '" title="' . esc_attr( $labels[ $code ] ) . '" width="64" height="64" loading="lazy">';
		}
		$signal = '';
		if ( '' !== $record['signal'] ) {
			$words = Data::signal_words();
			if ( isset( $words[ $record['signal'] ] ) ) {
				$signal = '<span class="degriz-hz__signal degriz-hz__signal--' . esc_attr( $record['signal'] ) . '">' . esc_html( $words[ $record['signal'] ] ) . '</span>';
			}
		}
		return '<span class="degriz-hz__pictos ' . esc_attr( $css_class ) . '">' . $html . $signal . '</span>';
	}

	/**
	 * The full block for a product.
	 *
	 * @param \WC_Product $product Product or variation.
	 * @return string
	 */
	public function render( \WC_Product $product ): string {
		$record     = Data::for_product( $product );
		$h_all      = Data::statements( 'H' );
		$p_all      = Data::statements( 'P' );
		$pictograms = $this->render_pictograms( $record );
		$files      = array();
		foreach ( array(
			'sds' => __( 'Safety data sheet (PDF)', 'degriz-hazard' ),
			'tds' => __( 'Technical data sheet (PDF)', 'degriz-hazard' ),
		) as $key => $label ) {
			$url = $record[ $key ] ? wp_get_attachment_url( $record[ $key ] ) : '';
			if ( $url ) {
				$path    = get_attached_file( $record[ $key ] );
				$size    = $path && is_readable( $path ) ? size_format( (int) filesize( $path ) ) : '';
				$files[] = array(
					'url'   => $url,
					'label' => $label,
					'size'  => $size,
				);
			}
		}
		$words = Data::signal_words();
		$h     = array();
		foreach ( $record['h'] as $code ) {
			if ( isset( $h_all[ $code ] ) ) {
				$h[ $code ] = $h_all[ $code ];
			}
		}
		$p = array();
		foreach ( $record['p'] as $code ) {
			if ( isset( $p_all[ $code ] ) ) {
				$p[ $code ] = $p_all[ $code ];
			}
		}

		ob_start();
		include DEGRIZ_HAZARD_DIR . 'templates/hazard-info.php';
		return (string) ob_get_clean();
	}
}
