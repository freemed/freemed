<?php
 // $Id$
 // desc: echos text/plain file from input
 // lic : GPL, v2

Header ("Content-type: ".
     ( empty($type) ? "text/plain" : $type ) );
echo $text;

?>
