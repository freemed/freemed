<?php
 // $Id$
 // $Author$

//----- class.label.php

// paper sizes
define ( 'LABEL_PAPER_LETTER',	1 );	// 8 1/2 x 11 "
define ( 'LABEL_PAPER_TABLOID',	2 );	// 11 x 17 "

//----- class.notebook.php

define ('NOTEBOOK_NO_OPTIONS',    0);
define ('NOTEBOOK_COMMON_BAR',    1);
define ('NOTEBOOK_STRETCH',       2);
define ('NOTEBOOK_TABS_LEFT',     4);
define ('NOTEBOOK_TABS_RIGHT',    8);
define ('NOTEBOOK_NOFORM',       16);
define ('NOTEBOOK_SCROLL',       32);

//----- class.sql.php

// var types
define ( 'SQL__BIT_SHIFT',		8);

define ( 'SQL__BLOB',			1);
function SQL__CHAR ($size) 		{ return 2 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__DATE',			3);
function SQL__DOUBLE ($size)		{ return 4 + ($size<<SQL__BIT_SHIFT); }
function SQL__ENUM ($elements = "") {
	if ($elements != "") return array(5, $elements);
	else return 5;
} // end SQL__ENUM
function SQL__INT ($size)		{ return 6 + ($size<<SQL__BIT_SHIFT); }
function SQL__INT_UNSIGNED ($size)	{ return 7 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__TEXT',			8);
function SQL__VARCHAR ($size) 		{ return 9 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__REAL',			10);
function SQL__TIMESTAMP ($size) 		{ return 11 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__SERIAL',			12);
define ( 'SQL__TIME',			13);

function SQL__AUTO_INCREMENT ($var)	{ return ($var) + (1<<(SQL__BIT_SHIFT*3)); } 
function SQL__NOT_NULL ($var)	{ return ($var) + (2<<(SQL__BIT_SHIFT*3)); } 

	// define upper limit
define ( 'SQL__THRESHHOLD', (1<<(SQL__BIT_SHIFT*3))-1 );

	// quick hack for TIMESTAMP's NOW()
define ( 'SQL__NOW', "~~~~~NOW~~~~~" );


//----- class.wizard.php

// define wizard verification criteria
// CURRENTLY ONLY FOR COMPATIBILITY
define ( 'WIZARD_VERIFY_NONZERO',       1 );
define ( 'WIZARD_VERIFY_NONNULL',       2 );
define ( 'WIZARD_VERIFY_GREATERTHAN',   3 );
define ( 'WIZARD_VERIFY_LESSTHAN',      4 );
define ( 'WIZARD_VERIFY_BETWEEN',       5 );
define ( 'WIZARD_VERIFY_DATEVALID',     6 );
define ( 'WIZARD_VERIFY_DATEBEFORE',    7 );
define ( 'WIZARD_VERIFY_DATEAFTER',     8 );
define ( 'WIZARD_VERIFY_FUNCTION',      9 );
define ( 'WIZARD_VERIFY_LONGER',       10 );
define ( 'WIZARD_VERIFY_SHORTER',      11 );

?>
