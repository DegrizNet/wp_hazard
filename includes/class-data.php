<?php
/**
 * Hazard reference data (pictograms, signal words, statements) and the per-product record.
 *
 * Statement texts are the official Slovenian wording of the CLP Regulation (EC) 1272/2008, as
 * published by the European Commission (SAMANCTA, "Stavki H/P", version 1.1): see readme.txt.
 *
 * @package degriz-hazard
 */

namespace Degriz\Hazard;

defined( 'ABSPATH' ) || defined( 'DEGRIZ_HAZARD_TESTS' ) || exit;

class Data {

	public const META_SIGNAL = '_degriz_hz_signal';
	public const META_PICTO  = '_degriz_hz_pictograms';
	public const META_H      = '_degriz_hz_h';
	public const META_P      = '_degriz_hz_p';
	public const META_SDS    = '_degriz_hz_sds_id';
	public const META_TDS    = '_degriz_hz_tds_id';

	/** @var array<string,array<string,string>>|null */
	private static $statements = null;

	/**
	 * Signal words as CLP prints them in Slovenian.
	 *
	 * @return array<string,string> key => label
	 */
	public static function signal_words(): array {
		return array(
			'danger'  => __( 'Danger', 'degriz-hazard' ),
			'warning' => __( 'Warning', 'degriz-hazard' ),
		);
	}

	/**
	 * GHS pictograms: code => label (the hazard the pictogram stands for).
	 *
	 * @return array<string,string>
	 */
	public static function pictograms(): array {
		return array(
			'GHS01' => __( 'Explosive', 'degriz-hazard' ),
			'GHS02' => __( 'Flammable', 'degriz-hazard' ),
			'GHS03' => __( 'Oxidising', 'degriz-hazard' ),
			'GHS04' => __( 'Gas under pressure', 'degriz-hazard' ),
			'GHS05' => __( 'Corrosive', 'degriz-hazard' ),
			'GHS06' => __( 'Acute toxicity', 'degriz-hazard' ),
			'GHS07' => __( 'Irritant, harmful', 'degriz-hazard' ),
			'GHS08' => __( 'Serious health hazard', 'degriz-hazard' ),
			'GHS09' => __( 'Hazardous to the environment', 'degriz-hazard' ),
		);
	}

	/**
	 * Statements of one group.
	 *
	 * @param string $group 'H' (hazard, EUH included) or 'P' (precautionary).
	 * @return array<string,string> code => Slovenian text
	 */
	public static function statements( string $group ): array {
		if ( null === self::$statements ) {
			$file = dirname( __DIR__ ) . '/includes/data/statements-sl.json';
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local data file.
			$json = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
			$json = is_array( $json ) ? $json : array();
			$h    = array_merge( $json['H'] ?? array(), $json['EUH'] ?? array() );
			uksort( $h, array( __CLASS__, 'compare_codes' ) );
			$p = $json['P'] ?? array();
			uksort( $p, array( __CLASS__, 'compare_codes' ) );
			self::$statements = array(
				'H' => $h,
				'P' => $p,
			);
		}
		$out = self::$statements[ 'P' === $group ? 'P' : 'H' ];

		// Texts the shop completed or corrected in the settings (the bundled list abbreviates some statements).
		foreach ( Settings::overrides() as $code => $text ) {
			if ( isset( $out[ $code ] ) ) {
				$out[ $code ] = $text;
			}
		}

		/**
		 * Filter the statement list (add codes or texts).
		 *
		 * @param array<string,string> $out   Code => text.
		 * @param string               $group H or P.
		 */
		return apply_filters( 'degriz_hazard_statements', $out, $group );
	}

	/**
	 * Natural order of statement codes: H before EUH, then by number.
	 *
	 * @param string $a Code.
	 * @param string $b Code.
	 * @return int
	 */
	public static function compare_codes( $a, $b ): int {
		$key = static function ( string $code ): string {
			$prefix = 0 === strpos( $code, 'EUH' ) ? '1' : '0';
			return $prefix . preg_replace_callback(
				'/\d+/',
				static function ( array $m ): string {
					return str_pad( $m[0], 4, '0', STR_PAD_LEFT );
				},
				$code
			);
		};
		return strcmp( $key( (string) $a ), $key( (string) $b ) );
	}

	/**
	 * Keep only known codes, without duplicates.
	 *
	 * @param mixed    $input   Posted values.
	 * @param string[] $allowed Allowed codes.
	 * @return string[]
	 */
	public static function sanitize_codes( $input, array $allowed ): array {
		$input = is_array( $input ) ? $input : array();
		$out   = array();
		foreach ( $input as $code ) {
			$code = trim( (string) $code );
			if ( in_array( $code, $allowed, true ) && ! in_array( $code, $out, true ) ) {
				$out[] = $code;
			}
		}
		return $out;
	}

	/**
	 * The hazard record of a product (variations use their parent's).
	 *
	 * @param \WC_Product $product Product or variation.
	 * @return array{signal:string,pictograms:string[],h:string[],p:string[],sds:int,tds:int}
	 */
	public static function for_product( \WC_Product $product ): array {
		if ( $product->is_type( 'variation' ) && $product->get_parent_id() ) {
			$parent = wc_get_product( $product->get_parent_id() );
			if ( $parent ) {
				$product = $parent;
			}
		}
		$list = static function ( string $key ) use ( $product ): array {
			$value = $product->get_meta( $key );
			return is_array( $value ) ? array_values( array_map( 'strval', $value ) ) : array();
		};

		return array(
			'signal'     => (string) $product->get_meta( self::META_SIGNAL ),
			'pictograms' => $list( self::META_PICTO ),
			'h'          => $list( self::META_H ),
			'p'          => $list( self::META_P ),
			'sds'        => (int) $product->get_meta( self::META_SDS ),
			'tds'        => (int) $product->get_meta( self::META_TDS ),
		);
	}

	/**
	 * True when a record has anything to show.
	 *
	 * @param array<string,mixed> $record Record from for_product().
	 * @return bool
	 */
	public static function has_content( array $record ): bool {
		return '' !== $record['signal'] || $record['pictograms'] || $record['h'] || $record['p'] || $record['sds'] || $record['tds'];
	}
}
