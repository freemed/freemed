<?php
  # file: macros.inc
  # note: macros for commonly used constants
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

  $__MACROS__ = true;   // we have loaded macros.inc

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

?>
