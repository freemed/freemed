<?php
 // $Id$
 // $Author$
 // desc: contains identifying information about the template

$TEMPLATE_NAME = "New Skin";
$TEMPLATE_DESC = "FreeMED DHTML Skin (0.7.x)";
$TEMPLATE_AUTHOR = "jeff b";
$TEMPLATE_OPTIONS = array (
	array(
		'name'    => __("Color Scheme"),
		'var'     => "stylesheet",
		'options' => array (
			__("Default Blue") => "stylesheet.css"
		)
	),

	array(
		'name'    => __("Display Language Selection"),
		'var'     => "language_bar",
		'options' => array (
			__("Yes") => '1',
			__("No")  => '0'
		)
	)
);

?>
