<?php
 // $Id$
 // desc: echos text/plain file from input
 // lic : GPL, v2

Header ("Content-type: ".( empty($_POST['type']) ? "text/plain" : $_POST['type'] ) );
print $_POST['text'];

?>
