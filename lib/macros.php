<?php
 // $Id$
 // note: macros for commonly used constants
 // lic : GPL, v2

if (!defined("__MACROS_PHP__")) {

define ('__MACROS_PHP__', true);

  // *****************************
  //    payment related macros
  // *****************************

$PAYER_TYPES = array (
	"Patient",
	"Primary",
	"Secondary",
	"Tertiary",
	"WorkComp"
);

	// coverage types
define ('PATIENT',              0);
define ('PRIMARY',              1);
define ('SECONDARY',            2);
define ('TERTIARY',             3);
define ('WORKCOMP',             4);
define ('MAXCOVTYPES',          4);   // max coverages contained in procrec cov1-4 (unless zero)

	// ledger transaction types
define ('PAYMENT',              0);
define ('ADJUSTMENT',           1);
define ('REFUND',               2);
define ('DENIAL',               3);
define ('REBILL',               4);
define ('PROCEDURE',            5);
define ('TRANSFER',             6);
define ('WITHHOLD',             7);
define ('DEDUCTABLE',           8);
define ('FEEADJUST',            9);
define ('BILLED',               10);
define ('COPAY',                11);
define ('PATWRITEOFF',          12);  // not used yet
define ('INSWRITEOFF',          13);  // not used yet

// itemlist macros
define ('ITEMLIST_VIEW',        1);
define ('ITEMLIST_MOD',         2);
define ('ITEMLIST_DEL',         4);

} // end checking for __MACROS_PHP__

?>
