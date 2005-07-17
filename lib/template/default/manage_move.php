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

foreach ($config['components'] AS $k => $v) {
	if ($v['module'] == $module) {
	       	$pos = $config['components'][$k][order];
	}
}
$nextup = 1; $nextdown = 999999999;
foreach ($config['components'] AS $k => $v) {
	if ($v['order'] > $nextup and $v['order'] < $pos) { $nextup = $v['order']; $upswitch = $v['module']; }
	if ($v['order'] < $nextdown and $v['order'] > $pos) { $nextdown = $v['order']; $downswitch = $v['module']; }
}

// Run through them and modify level of module
foreach ($config['components'] AS $k => $v) {
	if ($v['module'] == $module) {
		switch ($action) {
			case 'moveup':
			       	$config['components'][$k][order] = $nextup;
			       	$config['components'][$upswitch][order] = $pos;
				break;

			case 'movedown':
			       	$config['components'][$k][order] = $nextdown;
			       	$config['components'][$downswitch][order] = $pos;
				break;

			default: trigger_error("Should never get here!");
		}
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
