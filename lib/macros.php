<?php
 // $Id$
 // note: macros for commonly used constants
 // lic : GPL, v2

if (!defined("__MACROS_PHP__")) {

define ('__MACROS_PHP__', true);

  // *****************************
  // User permission flags
  // *****************************
define ('USER_ADMIN',             1);
define ('USER_DELETE',            2);
define ('USER_DATABASE',          4);
define ('USER_DISABLED',          8);
define ('USER_ROOT',             16);

  // *****************************
  // Certification types
  // *****************************
define ('AMBULANCE',              1);
define ('CHIROPRACTIC',           2);
define ('DME',                    3); //Durable Medical Equipment
define ('ENT',                    4); //Enteral Nutrition Therapy
define ('PNT',                    5); //Parenteral Nutrition Therapy
define ('DMEPOS',                 6); //Prosthetics Orthotics and Supplies  

  // *****************************
  // Certification form numbers
  // *****************************
  // DMEPOS forms
define ('F0102',                 1); //Hospital Bed and Support Services
define ('F0203',                 2); //Manual and Motorized Wheelchairs
define ('F0302',                 3); //Continuous Positive Airway Pressure System
define ('F0403',                 4); //Lymphedema Pump, Osteogenesis Stimulator
define ('F0502',                 5); //Reserved for Future Use
define ('F0602',                 6); //Transcutaneous Electrical Nerve Stimulator (TENS)
define ('F0702',                 7); //Seat Lift Mechanisms, Power Operated Vehicle 
define ('F0802',                 8); //Immunosuppressive Drugs
define ('F0902',                 9); //External Infusion Pump
define ('F1002',                 10); //Parenteral and Enteral Nutrition
 

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

// coverage status
define ('ACTIVE',              0);
define ('DELETED',             1);

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
define ('WRITEOFF',             12);  // not used yet

// itemlist macros
define ('ITEMLIST_VIEW',        1);
define ('ITEMLIST_MOD',         2);
define ('ITEMLIST_DEL',         4);
define ('ITEMLIST_LOCK',        8);

// Flags for EMR summaries
define ('SUMMARY_VIEW',			1);
define ('SUMMARY_VIEW_NEWWINDOW',	2);
define ('SUMMARY_LOCK',			4);
define ('SUMMARY_PRINT',		8);
define ('SUMMARY_DELETE',		16);

} // end checking for __MACROS_PHP__

?>
