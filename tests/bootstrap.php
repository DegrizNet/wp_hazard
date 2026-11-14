<?php
/**
 * PHPUnit bootstrap: the reference data helpers have no WordPress dependency.
 */

define( 'DEGRIZ_HAZARD_TESTS', true );
error_reporting( E_ALL );

require dirname( __DIR__ ) . '/includes/class-data.php';
