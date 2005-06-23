<?php
	// $Id$
	// $Author$
	//
	// Ajax functionality

$ajax = CreateObject('PHP.Sajax', 'ajax_provider.php');
$ajax->export('lookup', 'module_html', 'module_recent', 'patient_lookup');

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
.hiddendiv * { cursor: pointer; }
.hiddendiv div:hover {
	background: #0000ff;
	color: #ffffff;
}
</style>

<!-- Javascript libraries for AJAX -->
<script language="javascript" src="lib/template/default/stringTokenizer.js"></script>

<!-- Common functions -->
<script language="javascript">
<?php $ajax->show_javascript(); ?>

// replaceSelection functions
function setSelectionRange(input, selectionStart, selectionEnd) {
	if (input.setSelectionRange) {
		input.focus();
		input.setSelectionRange(selectionStart, selectionEnd);
	} else if (input.createTextRange) {
		var range = input.createTextRange();
		range.collapse(true);
		range.moveEnd('character', selectionEnd);
		range.moveStart('character', selectionStart);
		range.select();
	}
}
function setCaretToEnd (input) {
	setSelectionRange(input, input.value.length, input.value.length);
}
function setCaretToBegin (input) {
	setSelectionRange(input, 0, 0);
}
function setCaretToPos (input, pos) {
	setSelectionRange(input, pos, pos);
}
function replaceSelection (input, replaceString) {
	if (input.setSelectionRange) {
		var selectionStart = input.selectionStart;
		var selectionEnd = input.selectionEnd;
		input.value = input.value.substring(0, selectionStart)
			+ replaceString
			+ input.value.substring(selectionEnd);
		if (selectionStart != selectionEnd) // has there been a selection
			setSelectionRange(input, selectionStart, selectionStart + replaceString.length);
		else // set caret
			setCaretToPos(input, selectionStart + replaceString.length);
	} else if (document.selection) {
		var range = document.selection.createRange();
		if (range.parentElement() == input) {
			var isCollapsed = range.text == '';
			range.text = replaceString;
			if (!isCollapsed)  { // there has been a selection
				//it appears range.select() should select the newly 
				//inserted text but that fails with IE
				range.moveStart('character', -replaceString.length);
				range.select();
			}
		}
	}
}

function hilight ( t, s ) {
	n = new String( t );
	n = n.replace( new RegExp('('+s+')', 'gi'), '<b>$1</b>' );
	return n;
}
</script>
<?php

// Dump output buffer to header
$GLOBALS['__freemed']['header'] .= ob_get_contents();
ob_end_clean();

//----- Widget creation code

// Function: ajax_expand_module_html
//
//	AJAX widget to allow "expanding" of a hidden DIV tag to allow
//	dynamic content to be inserted without server refreshes.
//
// Parameters:
//
//	$content_id - DOM id of the content DIV
//
//	$module - Module class name
//
//	$method - Method that is to be called in the module in question
//
//	$param - Optional parameter
//
// Returns:
//
//	HTML widget
//
function ajax_expand_module_html ( $content_id, $module, $method, $param ) {
	$expand_id = $content_id . "_expand";
	ob_start();
?>
<script language="javascript">
function x_<?php print $content_id; ?>_toggle ( ) {
	var check_toggle = document.getElementById('<?php print $expand_id; ?>').innerHTML;
	if (check_toggle == '+') {
		x_module_html('<?php print $module; ?>', '<?php print $method; ?>', '<?php print addslashes($param); ?>', x_<?php print $content_id; ?>_expand_div);
	} else {
		x_<?php print $content_id; ?>_contract_div();
	}
}

function x_<?php print $content_id; ?>_contract_div () {
	document.getElementById('<?php print $expand_id; ?>').innerHTML = '+';
	document.getElementById('<?php print $content_id; ?>').innerHTML = '';
}

function x_<?php print $content_id; ?>_expand_div (v) {
	//alert('calling <?print $module; ?> <?php print $method; ?> <?php print $param; ?>');
	document.getElementById('<?php print $expand_id; ?>').innerHTML = '-';
	document.getElementById('<?php print $content_id; ?>').innerHTML = v;
}
</script>
<?php
	$GLOBALS['__freemed']['header'] .= ob_get_contents();
	ob_end_clean();

	// Create HTML part
	$buffer .= "<a onClick=\"x_".$content_id."_toggle(); return true;\"><span id=\"".$expand_id."\">+</span></a>";
	return $buffer;
} // end function ajax_expand_module_html

// Function: ajax_insert_module_text
//
//	AJAX widget to allow for inserting of text into text areas,
//	based on the recent_record callback. Only modules with
//	$this->date_field defined will appear in the widget.
//
// Parameters:
//
//	$text_id - DOM id of the text widget in question
//
//	$patient - id of the patient to qualify
//
//	$date - (optional) date to qualify
//
// Returns:
//
//	AJAX widget
//
function ajax_insert_module_text ( $text_id, $patient, $date = NULL ) {
	ob_start();
?>
<script language="javascript">
function x_<?php print $text_id; ?>_call_insert ( ) {
	var module_name = document.getElementById('<?php print $text_id; ?>_module_select').value;
	x_module_recent(module_name, '<?php print addslashes($patient); ?>', x_<?php print $text_id; ?>_insert_text);
	document.getElementById('<?php print $text_id; ?>_module_select').selectedIndex = 0;
}

function x_<?php print $text_id; ?>_insert_text (v) {
	//alert('calling <?print $module; ?> <?php print $method; ?> <?php print $param; ?>');
	replaceSelection(document.getElementById('<?php print $text_id; ?>'),v);
}
</script>
<?php
	$GLOBALS['__freemed']['header'] .= ob_get_contents();
	ob_end_clean();

	// Make a list of all modules which fit the bill
	$_cache = freemed::module_cache();
	$widget = "<option value=\"\">".__("Select information to insert:")."</option>\n";
	foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $v) {
		if ($v['META_INFORMATION']['date_field']) {
			$widget .= "<option value=\"".prepare($v['MODULE_CLASS'])."\">".prepare($v['MODULE_NAME'])."</option>\n";
			//$list[$v['MODULE_NAME']] = $v['MODULE_CLASS'];
		}
	} // end foreach

	// Create HTML part
	$buffer .= "<select id=\"".$text_id."_module_select\" name=\"".$text_id."_module_select\" onChange=\"if (this.selectedIndex > 0) { x_".$text_id."_call_insert(); } return true;\">\n" . $widget . "</select>\n";
	return $buffer;
} // end function ajax_insert_module_text

function ajax_widget ( $name, $module, &$obj, $field='id', $autosubmit=false ) {
	global ${$name};
	ob_start();
?>
<script language="javascript">
function x_<?php print $name; ?>_check_input(i) {
	if (document.getElementById(i + '_text').value.length >= 3) {
		document.getElementById(i + '_hiddendiv').style.display = 'block';
		document.getElementById(i + '_hiddendiv').innerHTML = 'Loading ... ';
		<?php if ($module == 'patient') { ?>
		x_patient_lookup(document.getElementById(i + '_text').value, x_<?php print $name; ?>_populate);
		<?php } else { ?>
		x_lookup('<?php print $module; ?>', document.getElementById(i + '_text').value, '<?php print $field; ?>', x_<?php print $name; ?>_populate);
		<?php } ?>
	} else {
		document.getElementById(i + '_hiddendiv').innerHTML = '';
		document.getElementById(i + '_hiddendiv').style.display = 'none';
	}
}
function x_<?php print $name; ?>_set_field (k, v) {
	document.getElementById('<?php print $name; ?>_hiddendiv').style.display = 'none';
	document.getElementById('<?php print $name; ?>_text').value = k;
	document.getElementById('<?php print $name; ?>').value = v;
	<?php if ($autosubmit) { ?>
	document.getElementById('<?php print $name; ?>').form.submit();
	<?php } ?>
}

function x_<?php print $name; ?>_populate(data) {
	hilight_string = document.getElementById('<?php print $name; ?>_text').value;
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
					var _k = hilight(innerTokenizer.nextToken(), hilight_string);
					var _v = innerTokenizer.nextToken();
					document.getElementById('<?php print $name; ?>_hiddendiv').innerHTML += '<div onClick="x_<?php print $name; ?>_set_field(\'' + _k + '\', ' + _v + ');"><span>' + _k + '</span></div>\n';
				} catch (e) {}
			} else {
				document.getElementById('<?php print $name; ?>_hiddendiv').innerHTML += '<div style="background: #ffffff; color: #000000;" onClick="document.getElementById(\'<?php print $name; ?>_hiddendiv\').style.display = \'none\';">' + hilight(_this_one, hilight_string) + '</div>\n';

			}
		}
	}
}
</script>

<table border="0" cellspacing="0" cellpadding="0">
<tr><td><input <?php if (freemed::config_value('tooltip')) { ?> onMouseover="tooltip('<?php 
print __("Type a portion of the entry that you want to find, then select it from the pulldown menu.");
?>'); return true;" onMouseOut="hidetooltip(); return true;" <?php } ?> type="text" id="<?php print $name; ?>_text" style="width:300px;" maxlength="150" onKeyup="x_<?php print $name; ?>_check_input('<?php print $name; ?>');" onClick="if (document.getElementById('<?php print $name; ?>').value > 0) { this.value = ''; document.getElementById('<?php print $name; ?>').value = 0; }" value="<?php if (${$name}) { print $obj->to_text(${$name}, $field); } ?>" autocomplete="off" />
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
