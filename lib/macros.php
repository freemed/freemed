<?php
 // $Id$
 // note: macros for commonly used constants
 // lic : GPL, v2

if (!defined("__MACROS_PHP__")) {

define (__MACROS_PHP__, true);

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
define (PATIENT,              0);
define (PRIMARY,              1);
define (SECONDARY,            2);
define (TERTIARY,             3);
define (WORKCOMP,             4);

	// ledger transaction types
define (PAYMENT,              0);
define (ADJUSTMENT,           1);
define (REFUND,               2);
define (DENIAL,               3);
define (REBILL,               4);
define (PROCEDURE,            5);
define (TRANSFER,             6);
define (WITHHOLD,             7);
define (DEDUCTABLE,           8);
define (FEEADJUST,            9);
define (BILLED,               10);
define (PATWRITEOFF,          11);
define (INSWRITEOFF,          12);

} // end checking for __MACROS_PHP__

?>
