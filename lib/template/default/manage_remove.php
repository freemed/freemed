<?php
	// $Id$
	// $Author$

// Import user object (if it doesn't exist)
if (!is_object($this_user)) { $this_user = CreateObject('FreeMED.User'); }

// Split apart pieces
if (count($this_user->manage_config) > 0) {
	$config = $this_user->manage_config;
} else {
	// No config, nothing to do
	$refresh = "manage.php?id=".$id;
	template_display();
} // end split apart pieces

// Run through them and unset "module"
foreach ($config['components'] AS $k => $v) { if ($v == $module) unset($config['components'][$k]); }

// Write to usermanageopt
$result = $sql->query($sql->update_query(
	"user",
	array("usermanageopt" => serialize($config)),
	array("id" => $this_user->user_number)
));

// Refresh
$refresh = "manage.php?id=".$id;
template_display();

?>
