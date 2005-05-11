<?php
	// $Id$
	// $Author$

//----- Only show this if there are key bindings defined
if (is_array($GLOBALS['__freemed']['key_bindings'])) { ?>
<script language="javascript">
// Activate key bindings
nn = (document.layers) ? true : false;
ie = (document.all) ? true : false;
function keyDown(e) {
	var evt = (e) ? e : (window.event) ? event : null;
	if(e) {
		var key = (e.charCode) ? e.charCode : ((e.keyCode) ? e.keyCode : ((e.which) ? e.which : 0));
<?php
// Loop through assigned keybindings
foreach ($GLOBALS['__freemed']['key_bindings'] AS $key => $binding) {
	if (is_array($binding)) {
		switch ($binding['action']) {
			default:
			break;
		}
	} else {
		// If nothing specified, use location
		?>
		if(key=='<?php print $key; ?>') window.location='<?php print $binding; ?>';
		<?php
	}
}
?>
	} 
}
document.onkeydown=keyDown; if(nn) document.captureEvents(Event.KEYDOWN);
</script>
<?php } ?>
