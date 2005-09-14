<?php
 // $Id$
 // desc: verification routines
 // code: jeff b <jeff@ourexchange.net>
 // lic : LGPL

if (!defined("__VERIFY_PHP__")) {

define ('__VERIFY_PHP__', true);

// define wizard verification criteria
define ( 'VERIFY_NONZERO',       1 );
define ( 'VERIFY_NONNULL',       2 );
define ( 'VERIFY_GREATERTHAN',   3 );
define ( 'VERIFY_LESSTHAN',      4 );
define ( 'VERIFY_BETWEEN',       5 );
define ( 'VERIFY_DATEVALID',     6 );
define ( 'VERIFY_DATEBEFORE',    7 );
define ( 'VERIFY_DATEAFTER',     8 );
define ( 'VERIFY_FUNCTION',      9 );
define ( 'VERIFY_LONGER',       10 );
define ( 'VERIFY_SHORTER',      11 );
define ( 'VERIFY_EMAIL',        12 );
define ( 'VERIFY_NUMERIC',      13 );
define ( 'VERIFY_NUMNZ',        14 );  // numeric non zero
define ( 'VERIFY_PHONE',        15 );  


define ( 'CHECK_FOR_ERRORS',     1 );
define ( 'CHECK_FOR_WARNINGS',   2 );

function verify (&$message, $verify=NULL, $type=CHECK_FOR_ERRORS) {
	global $FORM_ERROR, $FORM_WARNING;

	// check to see if we have to verify anything at all
	if ($verify == NULL) return true;

	reset ($verify); $any_failed = $failed = false;
	while (list ($garbage, $verify_me) = each ($verify)) {
	// each time "failed" is set to false, but any_failed retains
		$failed = false;

		// pull apart the error array
		list (
			$var_name,
			$var_criteria,
			$var_value,
			$var_text,
		) = $verify_me;

		// grab the variable
		global ${$var_name},
			${$var_name."_m"},
			${$var_name."_d"},
			${$var_name."_y"};

		  // check the criteria
		switch ($var_criteria) {

                        case VERIFY_PHONE:
                            $phone = ereg_replace("[^0-9]", "", ${$var_name}); 
                            if(!ereg("^[0-9]{10}$",$phone))
                                $failed = true;
                            break;

                        case VERIFY_EMAIL:
                            if ((!ereg(".+\@.+\..+", ${$var_name})) || 
                                (!ereg("^[a-zA-Z0-9_@.-]+$", ${$var_name})))
                                $failed = true;
                            break;

                        case VERIFY_NUMERIC:
                            if (!is_numeric(${$var_name})) $failed = true;
                            break;

                        case VERIFY_NUMNZ:
                            if (!is_numeric(${$var_name})) 
                            {
                                $failed = true;
                                break;
                            }
                            if (${$var_name} == 0) $failed = true;
                            break;
                            

			case VERIFY_NONZERO:
		if ( (${$var_name} == 0) or empty (${$var_name}) ) $failed = true; 
		break; // end VERIFY_NONZERO

			case VERIFY_NONNULL:
		if ( strlen ( ${$var_name} ) == 0 ) $failed = true;
		break; // end VERIFY_NONNULL

			case VERIFY_GREATERTHAN:
		if ( ${$var_name} <= $var_value ) $failed = true;
		break; // end VERIFY_GREATERTHAN

			case VERIFY_LESSTHAN:
		if ( ${$var_name} >= $var_value ) $failed = true;
		break; // end VERIFY_LESSTHAN

			case VERIFY_BETWEEN:
		if ( empty ($var_value) ) DIE ("wizard->verify_page :: need to have values");
		list ($top, $bottom) = explode (",", $var_value);
		if ( (${$var_name} > $var_top) or (${$var_name} < $var_bottom) )
		$failed = true;
		break; // end VERIFY_BETWEEN

			case VERIFY_DATEVALID:
		$_m = ${$var_name."_m"};
		$_d = ${$var_name."_d"};
		$_y = ${$var_name."_y"};
		if ( ($_m<=0) or ($_d<=0) or ($_y<=0) ) $failed = true;
		if (!checkdate ($_m, $_d, $_y)) $failed = true;
		break; // end VERIFY_DATEVALID

			case VERIFY_DATEBEFORE:
		$_m = ${$var_name."_m"};
		$_d = ${$var_name."_d"};
		$_y = ${$var_name."_y"};
		if ( ($_m<=0) or ($_d<=0) or ($_y<=0) ) $failed = true;
		  // get actual date to compare, and split
		list ($__y, $__m, $__d) = explode ("-", $var_value);
		if ($_y > $__y) $failed = true;
		if ( ($_m > $__m) and ($_y == $__y) ) $failed = true;
		if ( ($_d > $__d) and ($_m == $__m) and ($_y == $__y) ) $failed = true;
		break; // end VERIFY_DATEBEFORE

			case VERIFY_DATEAFTER:
		$_m = ${$var_name."_m"};
		$_d = ${$var_name."_d"};
		$_y = ${$var_name."_y"};
		if ( ($_m<=0) or ($_d<=0) or ($_y<=0) ) $failed = true;
		  // get actual date to compare, and split
		list ($__y, $__m, $__d) = explode ("-", $var_value);
		if ($_y < $__y) $failed = true;
		if ( ($_m < $__m) and ($_y == $__y) ) $failed = true;
		if ( ($_d < $__d) and ($_m == $__m) and ($_y == $__y) ) $failed = true;
		break; // end VERIFY_DATEAFTER

			case VERIFY_FUNCTION:
		if ( empty($var_value) )
			DIE("verify :: must specify a valid function ($var_value)");
		if ( ! $var_value ( ${$var_name} ) ) $failed = true;
		break; // end VERIFY_FUNCTION

			case VERIFY_LONGER:
		if ( strlen(${$var_name}) < $var_value ) $failed = true;
		break; // end VERIFY_LONGER

			case VERIFY_SHORTER:
		if ( strlen(${$var_name}) > $var_value ) $failed = true;
		break; // end VERIFY_SHORTER	

		} // end of checking criteria

	      // if failed...
		if ($failed) {
			switch ($type) {

				case CHECK_FOR_ERRORS:
				$FORM_ERROR[] = $var_name;
				break; // end CHECK_FOR_ERRORS

				case CHECK_FOR_WARNINGS:
				$FORM_WARNING[] = $var_name;
				break; // end CHECK_FOR_WARNINGS

			} // end switch true
			$message .= $var_text."<BR>\n";
			$any_failed = true;
		} else {
			// remove FORM_ERROR if there is one for this variable
			switch ($type) {

				case CHECK_FOR_ERRORS:
				if (is_array($FORM_ERROR)) {
					$FORM_ERROR = array_unique ($FORM_ERROR);
					foreach ($FORM_ERROR AS $error_key => $error_var)
					if ($error_var == $var_name)
						unset ($FORM_ERROR[$error_key]);
				} // end if is array FORM_ERROR
				break;

				case CHECK_FOR_WARNINGS:
				if (is_array($FORM_WARNING)) {
					$FORM_WARNING = array_unique ($FORM_WARNING);
					foreach ($FORM_WARNING AS $error_key => $error_var)
					if ($error_var == $var_name)
						unset ($FORM_WARNING[$error_key]);
				} // end if is array FORM_WARNING
				break;

			} // end switch type
		} // end if failed

	} // end of while loop

	// if failed, false
	if ($any_failed) return false;

	// if we get to here, it must have passed
	return true;
} // end function verify_page

} // end checking if defined

?>
