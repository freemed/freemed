<?php
 // $Id$
 // note: macros for commonly used constants
 // lic : GPL, v2

if (!defined(__MACROS_PHP__)) {

define (__MACROS_PHP__, true);

  // *****************************
  //    payment related macros
  // *****************************

  define (PAYMENT_TARGET_INS1,  0);
  define (PAYMENT_TARGET_INS2,  1);
  define (PAYMENT_TARGET_INS3,  2);
  define (PAYMENT_TARGET_WCOMP, 3);
  define (PAYMENT_TARGET_PAT,   4);

  define (PAYMENT,              0);
  define (ADJUSTMENT,           1);
  define (REFUND,               2);
  define (DENIAL,               3);
  define (REBILL,               4);
  define (PROCEDURE,            5);
  define (TRANSFER,             6);
  define (WITHHOLD,             7);
  define (DEDUCTABLE,           8);

} // end checking for __MACROS_PHP__

?>
