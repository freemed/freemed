<?php

include_once "lib/freemed.php";

$this_patient = CreateObject('FreeMED.Patient', 1);

$display_buffer .= "<PRE>\n";
$display_buffer .= htmlentities(prepare(freemed_emr_xml_export($this_patient)));
$display_buffer .= "</PRE>\n";

?>
