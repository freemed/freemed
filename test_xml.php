<?php

include_once "lib/freemed.php";
include_once "lib/API.php";
include_once "lib/module.php";
include_once "lib/module_emr.php";

$this_patient = new Patient ( 1 );

echo "<PRE>\n";
echo htmlentities(prepare(freemed_emr_xml_export($this_patient)));
echo "</PRE>\n";

?>
