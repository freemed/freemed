<?php
	// $Id$
	// $Author$

//----- Set page title
$page_title = PACKAGENAME." ".__("Login");

$display_buffer .= "
<!--
<div ALIGN=\"CENTER\">
	<i>".__("version")." ".DISPLAY_VERSION."</i>
</div>
<p/>
-->

<style type=\"text/css\">
body {
	background-image: url(lib/template/newskin/img/freemed_logo_300x300.png);
	background-repeat: repeat-x;
	background-position: 25% 30%;
}
#login_form {
	position: fixed;
	top: 25%;
	left: 30%;
	border: 1px dashed #555555;
	padding: 1em;

	/* Background and opacity */
	background: #ffffff;
	-moz-opacity: 0.75;
	opacity: 0.75;
	filter: alpha(opacity=75);
}
</style>

<div align=\"center\">
".( $message ? "<h2>".prepare($message)."</h2>" : "" )."
</div>

<table BORDER=\"0\" CELLPADDING=\"2\" id=\"login_form\">
<tr><td ALIGN=\"RIGHT\">
<form ACTION=\"authenticate.php\" METHOD=\"POST\">

<input TYPE=\"HIDDEN\" NAME=\"__dummy\"
 VALUE=\"01234567890123456789012345678901234567890
        01234567890123456789012345678901234567890
        01234567890123456789012345678901234567890\"/>
<b>".__("Username")."</b> :
</TD><TD ALIGN=\"LEFT\">
<input TYPE=\"TEXT\" NAME=\"_username\" LENGTH=\"20\" MAXLENGTH=\"32\"/>
</td></tr>
<tr><td ALIGN=\"RIGHT\">
<b>".__("Password")."</b> :
</td><td>
<input TYPE=\"PASSWORD\" NAME=\"_password\" LENGTH=\"20\" MAXLENGTH=\"32\"/></td></tr> 
<tr><td ALIGN=\"RIGHT\">
<b>".__("Language")."</b> :
</td><td ALIGN=\"LEFT\">
";

// Create a language registry object
$lregistry = CreateObject('FreeMED.LanguageRegistry');
$display_buffer .= $lregistry->widget('_l')."</td></tr>\n";

if ($GLOBALS['sql']->query ("SELECT * FROM config")) {
	$display_buffer .= "
	<tr><td ALIGN=\"RIGHT\">
	<b>".__("Facility")."</b> :
	</td><td ALIGN=\"LEFT\">
	<select NAME=\"_f\">
	".freemed_display_facilities ("_f", true, "0")."
	</select>
	</td></tr>
	";
} // end checking for connection

if (!empty($_URL))
 $display_buffer .= "
  <tr><td ALIGN=\"RIGHT\">
   <b>".__("Resume")." : </b>
  </td><td ALIGN=\"LEFT\">
   <input TYPE=\"RADIO\" NAME=\"_URL\" VALUE=\"$_URL\" CHECKED/>".__("Resume")."<br/>
   <input TYPE=\"RADIO\" NAME=\"_URL\" VALUE=\"\"/>".__("Reset Resume")."
  </td></tr>
 ";

$display_buffer .= "
<tr>
	<td colspan=\"2\" align=\"center\">
	<input TYPE=\"SUBMIT\" VALUE=\"".__("Sign In")."\" CLASS=\"button\" />
	<input TYPE=\"RESET\"  VALUE=\"".__("Clear")."\" CLASS=\"button\" />
  	</td>
</tr>
</table>
</form>
";

if ($debug) {
	$display_buffer .= "
	<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"0\"
	 VALIGN=\"BOTTOM\" ALIGN=\"CENTER\" BGCOLOR=\"#000000\">
	<tr><td BGCOLOR=\"#000000\">
	<div ALIGN=\"CENTER\">
      	<b><font COLOR=\"#ffffff\" SIZE=\"-2\">".__("DEBUG IS ON")."</font></b>
	</div>
	</td></tr></table>
	";
}

if ($debug) {
	$display_buffer .= "
	<br/>
	<small>
	<a HREF=\"CHANGELOG\">CHANGELOG for ".VERSION."</a>
	</small>
	";
}

//----- Use template
template_display();

?>
