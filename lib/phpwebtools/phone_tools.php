<?php
 // $Id$
 // desc: phone number tools
 // code: jeff b <jeff@ourexchange.net>
 // lic : LGPL

if (!defined("__PHONE_TOOLS_PHP__")) {

define ('__PHONE_TOOLS_PHP__', true);

define ('PHONE_NUMBER_UNFORMATTED',	0);
define ('PHONE_NUMBER_USA', 		1);
define ('PHONE_NUMBER_EUROPE', 		2);

function phone_assemble ($phonevar, $phonefmt, $array_index=-1) {
  global ${$phonevar."_1"},
         ${$phonevar."_2"},
         ${$phonevar."_3"},
         ${$phonevar."_4"},
         ${$phonevar."_5"};
  if ($array_index == -1) {
    $p1 = ${$phonevar."_1"};
    $p2 = ${$phonevar."_2"};
    $p3 = ${$phonevar."_3"};
    $p4 = ${$phonevar."_4"};
    $p5 = ${$phonevar."_5"};
  } else {
    $p1 = ${$phonevar."_1"}[$array_index];
    $p2 = ${$phonevar."_2"}[$array_index];
    $p3 = ${$phonevar."_3"}[$array_index];
    $p4 = ${$phonevar."_4"}[$array_index];
    $p5 = ${$phonevar."_5"}[$array_index];
  } // end checking for array index
  
  // return the composited string
  switch ($phonefmt) {
    case PHONE_NUMBER_UNFORMATTED:
     return $p1.$p2.$p3.$p4.$p5;
     break; // end PHONE_NUMBER_UNFORMATTED

    case PHONE_NUMBER_USA:
     return $p1.$p2.$p3.$p4;
     break; // end PHONE_NUMBER_USA

    case PHONE_NUMBER_EUROPE:
     return $p1.$p2.$p3.$p4.$p5;
     break; // end PHONE_NUMBER_EUROPE

    default:
     
     break;
  } // end switching through the formats
} // end function phone_assemble

function phone_display($phonevar, $format=PHONE_NUMBER_UNFORMATTED, $array_index=-1) {
	global ${$phonevar};
	$w = ${$phonevar};
	switch ($format) {
		case PHONE_NUMBER_EUROPE:
			$p1 = substr ( $w, 0, 2 );
			$p2 = substr ( $w, 2, 2 );
			$p3 = substr ( $w, 4, 2 );
			$p4 = substr ( $w, 6, 2 );
			$p5 = substr ( $w, 8, 2 );
			break;

		case PHONE_NUMBER_USA:
			$p1 = substr ( $w,  0, 3 );
			$p2 = substr ( $w,  3, 3 );
			$p3 = substr ( $w,  6, 4 );
			$p4 = substr ( $w, 10, 4 );
			return "(".$p1.") ".$p2."-".$p3.
				( $p4>0 ? " x$p4" : "" );
			break;

		case PHONE_NUMBER_UNFORMATTED:
		default:
			return ${$phonevar};
			break;
	}
	
} // end function phone_display

function phone_entry ($phonevar, $format=PHONE_NUMBER_UNFORMATTED,
                      $array_index=-1) {
  global $$phonevar,
         ${$phonevar."_1"},
         ${$phonevar."_2"},
         ${$phonevar."_3"},
         ${$phonevar."_4"},
         ${$phonevar."_5"};

  // Check to see if autoskip JS is enabled
  if (!$GLOBALS['__phpwebtools']['autoskip']) {
    // Enable autoskip
    $buffer .= "
    	<script LANGUAGE=\"JavaScript\">
	function autoskip(here, next) {
		if (here.value.length==here.getAttribute('maxlength') && here.getAttribute) {
			next.focus()
		}
	}
	</script>
    ";
    
    // Set for future reference
    $GLOBALS['__phpwebtools']['autoskip'] = 1;
  }

  // move into local vars
  if (($array_index+0)==-1) {
    $w = ${$phonevar};
    $p1 = ${$phonevar."_1"};
    $p2 = ${$phonevar."_2"};
    $p3 = ${$phonevar."_3"};
    $p4 = ${$phonevar."_4"};
    $p5 = ${$phonevar."_5"};
    $suffix="";
  } else { // if it *is* an array
    $w = ${$phonevar}[$array_index];
    $p1 = ${$phonevar."_1"}[$array_index];
    $p2 = ${$phonevar."_2"}[$array_index];
    $p3 = ${$phonevar."_3"}[$array_index];
    $p4 = ${$phonevar."_4"}[$array_index];
    $p5 = ${$phonevar."_5"}[$array_index];
    $suffix = "[]";
  } // end moving into local vars

  if (!empty($w) and empty($p1)) {
    // if the whole thing is there, split into parts
	switch ($format) {
		case PHONE_NUMBER_EUROPE:
			$p1 = substr ( $w, 0, 2 );
			$p2 = substr ( $w, 2, 2 );
			$p3 = substr ( $w, 4, 2 );
			$p4 = substr ( $w, 6, 2 );
			$p5 = substr ( $w, 8, 2 );
			break;

		case PHONE_NUMBER_USA:
			$p1 = substr ( $w,  0, 3 );
			$p2 = substr ( $w,  3, 3 );
			$p3 = substr ( $w,  6, 4 );
			$p4 = substr ( $w, 10, 4 );
			break;

		case PHONE_NUMBER_UNFORMATTED:
		default:
			break;
	}
  } // end checking for parts

    switch ($format) {
      case PHONE_NUMBER_EUROPE:
       $buffer .=
         "<INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_1\" 
	  SIZE=3 MAXLENGTH=2 VALUE=\"".prepare($p1)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_2); return true;\">
         &nbsp; <B>-</B> &nbsp;
         <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_2\" 
	  SIZE=3 MAXLENGTH=2 VALUE=\"".prepare($p2)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_3); return true;\">
         &nbsp; <B>-</B> &nbsp;
         <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_3\"
	  SIZE=3 MAXLENGTH=2 VALUE=\"".prepare($p3)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_4); return true;\">
         &nbsp; <B>-</B> &nbsp;
         <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_4\" 
	  SIZE=3 MAXLENGTH=2 VALUE=\"".prepare($p4)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_5); return true;\">
         &nbsp; <B>-</B> &nbsp;
         <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_5\" 
	  SIZE=3 MAXLENGTH=2 VALUE=\"".prepare($p5)."\">\n";
       break;

      case PHONE_NUMBER_USA:
       $buffer .=
         "<B>(</B> &nbsp;
	  <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_1\" 
	  SIZE=4 MAXLENGTH=3 VALUE=\"".prepare($p1)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_2); return true;\">
         &nbsp; <B>)</B> &nbsp;
	  <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_2\" 
	  SIZE=4 MAXLENGTH=3 VALUE=\"".prepare($p2)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_3); return true;\">
         &nbsp; <B>-</B> &nbsp;
	  <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_3\" 
	  SIZE=5 MAXLENGTH=4 VALUE=\"".prepare($p3)."\"
	  onKeyup=\"autoskip(this,".$phonevar."_4); return true;\">
	 &nbsp; <B>x</B> &nbsp; 
	  <INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."_4\" 
	  SIZE=5 MAXLENGTH=4 VALUE=\"".prepare($p4)."\">\n";
       break;
    
      case PHONE_NUMBER_UNFORMATTED:
      default:
       $buffer .=
         "<INPUT TYPE=TEXT NAME=\"".prepare($phonevar)."\" ". 
	 "SIZE=16 MAXLENGTH=16 VALUE=\"".prepare($w)."\">\n";
       break; // end of default/UNFORMATTED
    } // end format switch

  // return proper part
  return $buffer;
} // end function phone_entry

function phone_vars ($varname) {
  return array ($varname,
                $varname."_1",
		$varname."_2",
		$varname."_3",
		$varname."_4",
		$varname."_5");
} // end function phone_vars

} // end checking if defined

?>
