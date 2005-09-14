<?php
 // $Id$
 // desc: date and time tools
 // code: jeff b <jeff@ourexchange.net>
 // lic : LGPL

if (!defined("__DATE_TOOLS_PHP__")) {

define ('__DATE_TOOLS_PHP__', true);

function date_assemble ($datevar, $array_index=-1) {
  global ${$datevar."_m"}, ${$datevar."_d"}, ${$datevar."_y"},
    ${$datevar};
  if ($array_index == -1) {
    $m = ${$datevar."_m"};
    $d = ${$datevar."_d"};
    $y = ${$datevar."_y"};
  } else {
    $m = ${$datevar."_m"}[$array_index];
    $d = ${$datevar."_d"}[$array_index];
    $y = ${$datevar."_y"}[$array_index];
  } // end checking for array index

  // Use the whole if they are empty
  if (empty($m) and empty($d) and empty($y)) return ${$datevar};
  
  // ensure proper format
  if (strlen($m) == 1) $m = "0".$m;
  if (strlen($d) == 1) $d = "0".$d;

  // return the composited string
  return $y."-".$m."-".$d;
} // end function date_assemble

function date_entry ($datevar, $epoch=1900, $format="mdy", $array_index=-1) {
  global $$datevar, ${$datevar."_m"}, ${$datevar."_d"}, ${$datevar."_y"};

  // move into local vars
  if (($array_index+0)==-1) {
    $w = $$datevar;
    $m = ${$datevar."_m"};
    $d = ${$datevar."_d"};
    $y = ${$datevar."_y"};
    $suffix="";
  } else { // if it *is* an array
    $w = ${$datevar}[$array_index];
    $m = ${$datevar."_m"}[$array_index];
    $d = ${$datevar."_d"}[$array_index];
    $y = ${$datevar."_y"}[$array_index];
    $suffix = "[]";
  } // end moving into local vars

  if (!empty($w) and (empty($m) or empty($d) or empty($y))) {
    // if the whole thing is there, split into $m,$d,$y
    $y = substr ($w, 0, 4);
    $m = substr ($w, 5, 2);
    $d = substr ($w, 8, 2);
  } elseif (empty($y) and empty($m) and empty($d)) {
    $y = date ("Y")+0;
    $m = date ("m")+0;
    $d = date ("d")+0;
  } // end if not empty whole data

  // set boundaries
  $starting_year = $epoch;
  $ending_year   = date("Y")+10;

  // legacy dates check
  if (($y>1800) and ($y<$starting_year)) $starting_year = $y;
  if (($y>1800) and ($y>$ending_year  )) $ending_year   = $y;

  // form individual parts
  $month_part = "\n<SELECT NAME=\"".$datevar."_m$suffix\">
    <OPTION VALUE=\"00\" ".
    ( ($m == 0) ? "SELECTED" : "" ).">--";
  for ($i=1;$i<=12;$i++) {
    $prefix = ( ($i<10) ? "0" : "" );
    $month_part .= "\n<OPTION VALUE=\"".( ($i<10) ? "0" : "" ).$i."\" ".
      ( ($i == $m) ? "SELECTED" : "" ).">".date("M", mktime(0,0,0,$i,1,1));
  } // end of for loop (months)
  $month_part .= "\n</SELECT>\n";

  $day_part = "\n<SELECT NAME=\"".$datevar."_d$suffix\">
    <OPTION VALUE=\"00\" ".
    ( ($d == 0) ? "SELECTED" : "" ).">--";
  for ($i=1;$i<=31;$i++) {
    $prefix = ( ($i<10) ? "0" : "" );
    $day_part .= "\n<OPTION VALUE=\"".( ($i<10) ? "0" : "" ).$i."\" ".
      ( ($i == $d) ? "SELECTED" : "" ).">".
      ( ($i<10) ? "0" : "" ).$i;
  } // end of for loop (days)
  $day_part .= "\n</SELECT>\n";

  $year_part = "\n<SELECT NAME=\"".$datevar."_y$suffix\">
    <OPTION VALUE=\"0000\" ".
    ( ($d == 0) ? "SELECTED" : "" ).">----";
  for ($i=$starting_year;$i<=$ending_year;$i++) {
    $year_part .= "\n<OPTION VALUE=\"".$i."\" ".
      ( ($i == $y) ? "SELECTED" : "" ).">$i";
  } // end of for loop (years)
  $year_part .= "\n</SELECT>\n";

  // choose date format and return
  switch ($format) {
    case "ymd":
      return $year_part  . $month_part . $day_part;  break;
    case "dmy":
      return $day_part   . $month_part . $year_part; break;
    case "mdy": default:
      return $month_part . $day_part   . $year_part; break;
  } // end switch format
} // end function date_entry

function date_vars ($varname) {
  return array ($varname, $varname."_m", $varname."_d", $varname."_y");
} // end function date_vars

function date_diff ($begin_date, $end_date="") {
  if (empty($end_date)) $end_date = date ("Y-m-d");

  $begin_y = substr ($begin_date, 0, 4) + 0;
  $begin_m = substr ($begin_date, 5, 2) + 0;
  $begin_d = substr ($begin_date, 8, 2) + 0;
  $end_y   = substr ($end_date,   0, 4) + 0;
  $end_m   = substr ($end_date,   5, 2) + 0;
  $end_d   = substr ($end_date,   8, 2) + 0;

  if ( ($begin_y > $end_y) or
      (($begin_y == $end_y) and ($begin_m > $end_m)) or
      (($begin_y == $end_y) and ($begin_m == $end_m) and
       ($begin_d > $end_d)) ) {
    // switch the dates
    $t_y     = $begin_y; $t_m     = $begin_m; $t_d     = $begin_d;
    $begin_y = $end_y;   $begin_m = $end_m;   $begin_d = $end_d;
    $end_y   = $t_y;     $end_m   = $t_m;     $end_d   = $t_d;
  } // end checking if we have to reverse everything

  // determine difference in years
  $year_diff = $end_y - $begin_y;

  // determine difference in months
  $month_diff = $end_m - $begin_m;
  // perform roll overs for year
  if ($month_diff < 0) {
    $month_diff += 12;
    $year_diff--; // decrement from year
  }

  // determine difference in months
  $day_diff = $end_d - $begin_d;
  // perform roll overs for month
  if ($month_diff < 0) {
    $day_diff += 31; // KLUDGE!! KLUDGE!!
    $month_diff--; // decrement from month
  }

  // return as a list
  return array ($year_diff, $month_diff, $day_diff);
} // end function date_diff

function date_diff_display ($begin_date, $end_date="", $year_text="year(s)",
                            $month_text="month(s)", $day_text="day(s)") {
  // grab the difference			    
  list ($y, $m, $d) = date_diff ($begin_date, $end_date);

  // handle born today
  if ( ($y==0) and ($m==0) and ($d==0) )
    return "0 ".$day_text;

  // empty buffer
  $buffer = "";

  // add year(s)
  if ($y > 0) $buffer .= ($y+0)." ".$year_text." ";

  // add month(s) if years < 2
  if (($m > 0) and ($y<2)) $buffer .= ($m+0)." ".$month_text." ";

  // add day(s) if no years at all and less than 6 months
  if (($d > 0) and ($y==0) and ($m<6)) $buffer .= ($d+0)." ".$day_text;

  // return buffer
  return $buffer;
} // end function date_diff_display

} // end checking if defined

?>
