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

// Figure out whether it is moved up or down
switch ($action) {
	case 'moveup': $modifier = -1; break;
	case 'movedown': $modifier = 1; break;
	default: trigger_error("Should never get here!");
} // end action

// Run through them and modify level of module
foreach ($config['modular_components'] AS $k => $v) {
	if ($v['module'] == $module) {
	       	$config['modular_components'][$k][order] += $modifier;
	}
}
foreach ($config['static_components'] AS $k => $v) {
	if ($v['static'] == $module) {
	       	$config['static_components'][$k][order] += $modifier;
	}
}

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
