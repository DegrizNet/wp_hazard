<?php
namespace Degriz\Hazard\Tests;

use Degriz\Hazard\Data;
use PHPUnit\Framework\TestCase;

class Data_Test extends TestCase {

	public function test_sanitize_keeps_only_known_codes_without_duplicates(): void {
		$out = Data::sanitize_codes( array( 'GHS05', 'GHS99', ' GHS07 ', 'GHS05', '', 5 ), array( 'GHS05', 'GHS07' ) );
		$this->assertSame( array( 'GHS05', 'GHS07' ), $out );
	}

	public function test_sanitize_rejects_non_arrays(): void {
		$this->assertSame( array(), Data::sanitize_codes( 'GHS05', array( 'GHS05' ) ) );
		$this->assertSame( array(), Data::sanitize_codes( null, array( 'GHS05' ) ) );
	}

	public function test_codes_are_ordered_naturally_with_euh_last(): void {
		$codes = array( 'EUH210', 'H400', 'H290', 'H315', 'EUH001', 'H1000', 'H225' );
		usort( $codes, array( Data::class, 'compare_codes' ) );
		$this->assertSame( array( 'H225', 'H290', 'H315', 'H400', 'H1000', 'EUH001', 'EUH210' ), $codes );
	}

	public function test_bundled_slovenian_statements_are_complete_and_well_formed(): void {
		$json = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/includes/data/statements-sl.json' ), true );
		$this->assertIsArray( $json );
		foreach ( array( 'H315', 'H318', 'H319', 'H410', 'H290', 'H302', 'H314' ) as $code ) {
			$this->assertArrayHasKey( $code, $json['H'] );
		}
		foreach ( array( 'P102', 'P280', 'P305+P351+P338', 'P501' ) as $code ) {
			$this->assertArrayHasKey( $code, $json['P'] );
		}
		foreach ( array_merge( $json['H'], $json['EUH'], $json['P'] ) as $code => $text ) {
			$this->assertNotSame( '', trim( $text ), $code );
		}
		$this->assertSame( 'Povzroča draženje kože.', $json['H']['H315'] );
	}

	public function test_statement_list_records_its_official_source(): void {
		$json = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/includes/data/statements-sl.json' ), true );
		$this->assertSame( '2026-07-01', $json['meta']['consolidation_date'] );
		$this->assertStringContainsString( 'publications.europa.eu', $json['meta']['source_url'] );
		$this->assertGreaterThanOrEqual( 120, count( $json['P'] ) );
		foreach ( array( 'EUH380', 'EUH381', 'EUH430', 'EUH431', 'EUH440', 'EUH441', 'EUH450', 'EUH451' ) as $code ) {
			$this->assertArrayHasKey( $code, $json['EUH'] );
		}
		$this->assertSame( 'Odstraniti vsebino/posodo …', $json['P']['P501'] );
		$this->assertStringContainsString( '<navesti način izpostavljenosti', $json['H']['H340'] );
		$this->assertArrayNotHasKey( 'EUH001', $json['EUH'] );
	}

	public function test_every_pictogram_has_an_svg_file(): void {
		for ( $i = 1; $i <= 9; $i++ ) {
			$file = dirname( __DIR__ ) . '/assets/pictograms/GHS0' . $i . '.svg';
			$this->assertFileExists( $file );
			$this->assertStringContainsString( '<svg', (string) file_get_contents( $file ) );
		}
	}

	public function test_has_content(): void {
		$empty = array( 'signal' => '', 'pictograms' => array(), 'h' => array(), 'p' => array(), 'sds' => 0, 'tds' => 0 );
		$this->assertFalse( Data::has_content( $empty ) );
		$this->assertTrue( Data::has_content( array_merge( $empty, array( 'sds' => 12 ) ) ) );
		$this->assertTrue( Data::has_content( array_merge( $empty, array( 'signal' => 'danger' ) ) ) );
	}
}
