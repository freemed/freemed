<?php
	// $Id$

function FormatMonetary($number, $precision, $thousep, $decsep)
{
  $zeros = '000000000000';

  if (strstr($number, '.'))
  {
    $a = explode('.', $number);
  }
  else if (strstr($number, ','))
  {
    $a = explode(',', $number);
  }
  else
  {
    $a[0] = $number;
  }
  $part1 = $a[0];
  $part2 = substr($a[1],0,$precision);
  if (!$part2)
    $part2 = substr($zeros, 0, $precision);

  $tmp = strrev($part1);

  for ($n=0; $n<strlen($tmp); $n++)
  {
    if ($i==3)
    {
      $resultpart1 .= $thousep;
      $i = 0;
    }
    $i ++;
    $resultpart1 .= substr($tmp,$n,1);
  }
  $part1 = strrev($resultpart1);
  $result = $part1 . $decsep . $part2;
  return $result;
}
?>
