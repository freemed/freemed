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

// Explode modular and static components
$modular = $config[modular_components];
$static  = $config[static_components];

// Run through them and unset "module"
foreach ($modular AS $k => $v) { if ($v == $module) unset($modular[$k]); }
foreach ($static AS $k => $v) { if ($v == $module) unset($static[$k]); }

// Pull them back into config
unset($config[static_components]);
foreach ($static AS $k => $v) {
	if (!empty($v)) $config[static_components][] = $v;
}
unset($config[modular_components]);
foreach ($modular AS $k => $v) {
	if (!empty($v)) $config[modular_components][] = $v;
}

// Put this back together and write to usermanageopt
$hash = "";
foreach ($config as $k => $v) {
	$hash .= "/".$k."=".sql_squash($v);
}
$result = $sql->query($sql->update_query(
	"user",
	array("usermanageopt" => $hash),
	array("id" => $this_user->user_number)
));

// Refresh
$refresh = "manage.php?id=".$id;
template_display();

?>
