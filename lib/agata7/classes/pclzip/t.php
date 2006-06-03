<?php

//$pattern = '/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/';

$pattern = '/(a)|(s)/';
$string  = 'aSASDFSDFs';

var_dump(preg_split($pattern, trim ($string), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
?>