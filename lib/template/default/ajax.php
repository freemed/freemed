<?php
	// $Id$
	// $Author$
	//
	// Ajax functionality

$ajax = CreateObject('PHP.Sajax', 'ajax_provider.php');
$ajax->export('lookup');

// Form header information regarding this
ob_start();
?>

<!-- Hidden div tag -->
<style type="text/css">
.hiddendiv {
	display: none;
	font-size: 10pt;
	border-left: 1px solid #000000;
	border-bottom: 1px solid #000000;
	border-right: 1px solid #000000;
}
</style>

<!-- Javascript libraries for AJAX -->
<script language="javascript" src="lib/template/default/stringTokenizer.js"></script>

<!-- Common functions -->
<script language="javascript">
<?php $ajax->show_javascript(); ?>
</script>
<?php

// Dump output buffer to header
$GLOBALS['__freemed']['header'] .= ob_get_contents();
ob_end_clean();

//----- Widget creation code

function ajax_widget ( $name, $module, &$obj ) {
	global ${$name};
	ob_start();
?>
<script language="javascript">
function x_<?php print $name; ?>_check_input(i) {
	if (document.getElementById(i + '_text').value.length >= 3) {
		document.getElementById(i + '_hiddendiv').style.display = 'block';
		document.getElementById(i + '_hiddendiv').innerHTML = 'Loading ... ';
		x_lookup('<?php print $module; ?>', document.getElementById(i + '_text').value, x_<?php print $name; ?>_populate);
	} else {
		document.getElementById(i + '_hiddendiv').innerHTML = '';
		document.getElementById(i + '_hiddendiv').style.display = 'none';
	}
}
function x_<?php print $name; ?>_set_field (k, v) {
	document.getElementById('<?php print $name; ?>_hiddendiv').style.display = 'none';
	document.getElementById('<?php print $name; ?>_text').value = k;
	document.getElementById('<?php print $name; ?>').value = v;
}

function x_<?php print $name; ?>_populate(data) {
	if (data.length < 3) {
		document.getElementById('<?php print $name; ?>_hiddendiv').innerHTML = 'No results.';
	} else {
		document.getElementById('<?php print $name; ?>_hiddendiv').innerHTML = '';
		var tokenizer = new StringTokenizer ( data, '|' );
		var alt = 0;
		while (tokenizer.hasMoreTokens()) {
			var _this_one = tokenizer.nextToken();
			if (alt==0) { alt = 1; } else { alt = 0; }
			if (alt==0) { myColor='#cccccc'; } else { myColor='#bbbbbb'; }
			if (_this_one.indexOf('@') != -1) {
				var innerTokenizer = new StringTokenizer ( _this_one, '@' );
				try {
					var _k = innerTokenizer.nextToken();
					var _v = innerTokenizer.nextToken();
					document.getElementById('<?php print $name; ?>_hiddendiv').innerHTML += '<div style="background: '+myColor+';" onClick="x_<?php print $name; ?>_set_field(\'' + _k + '\', ' + _v + ');"><span>' + _k + '</span></div>\n';
				} catch (e) {}
			} else {
				document.getElementById('<?php print $name; ?>_hiddendiv').innerHTML += '<div style="background: '+myColor+';">' + _this_one + '</div>\n';

			}
		}
	}
}
</script>

<table border="0" cellspacing="0" cellpadding="0">
<tr><td><input type="text" id="<?php print $name; ?>_text" style="width:300px;" maxlength="150" onKeyup="x_<?php print $name; ?>_check_input('<?php print $name; ?>');" onClick="if (document.getElementById('<?php print $name; ?>').value > 0) { this.value = ''; }" value="<?php if (${$name}) { print $obj->to_text(${$name}); } ?>" autocomplete="off" />
<input type="hidden" id="<?php print $name; ?>" name="<?php print $name; ?>" value="<?php if (${$name}) { print htmlentities(${$name}); } ?>" />
</td></tr>
<tr><td width="300"><div id="<?php print $name; ?>_hiddendiv" class="hiddendiv"></div></td></tr>
</table>
<?php
	$buffer .= ob_get_contents();
	ob_end_clean();
	return $buffer;
} // end function ajax_widget

?>
