<?php
 // $Id$
 // $Author$
 // code: jeff b (jeff@ourexchange.net)
 // lic : GPL, v2

//----- Set page title
$page_title = PACKAGENAME." "._("Login");

$display_buffer .= "
<div ALIGN=\"CENTER\">
	<i>version ".VERSION."</i>
</div>

<p/>

<table WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"2\">
<tr><td ALIGN=\"RIGHT\">
<form ACTION=\"authenticate.php\" METHOD=\"POST\">

<input TYPE=\"HIDDEN\" NAME=\"__dummy\"
 VALUE=\"01234567890123456789012345678901234567890
        01234567890123456789012345678901234567890
        01234567890123456789012345678901234567890\"/>
"._("Username")." :
</TD><TD ALIGN=\"LEFT\">
<input TYPE=\"TEXT\" NAME=\"_username\" LENGTH=\"20\" MAXLENGTH=\"32\"/>
</td></tr>
<tr><td ALIGN=\"RIGHT\">
"._("Password")." :
</td><td>
<input TYPE=\"PASSWORD\" NAME=\"_password\" LENGTH=\"20\" MAXLENGTH=\"32\"/></td></tr> 
<tr><td ALIGN=\"RIGHT\">
"._("Language")." :
</td><td ALIGN=\"LEFT\">
<select NAME=\"_l\">
 <option VALUE=\"$language\">"._("Default Language")."</option>
";

// actually open the language registry
$f_reg = fopen ( "./lang/registry", "r");
while ($f_line = fgets ($f_reg, 255)) {
	if (substr ($f_line, 0, 1) != "#") { // skip comments
		$f_line_array = explode (":", $f_line);
		$display_buffer .= " <option VALUE=\"".
				prepare(strtolower($f_line_array[0]))."\">".
				prepare($f_line_array[1])."</option>\n";
	} // end of skipping comments
} // end while we have more lines to get
fclose ($f_reg);

$display_buffer .= "
</select>
</tr>
";

if ($sql->query ("SELECT * FROM config")) {
	$display_buffer .= "
	<tr><td ALIGN=\"RIGHT\">
	"._("Facility")." :
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
   <b>"._("Resume")." : </b>
  </td><td ALIGN=\"LEFT\">
   <input TYPE=\"RADIO\" NAME=\"_URL\" VALUE=\"$_URL\" CHECKED/>"._("Resume")."<br/>
   <input TYPE=\"RADIO\" NAME=\"_URL\" VALUE=\"\"/>"._("Reset Resume")."
  </td></tr>
 ";

$display_buffer .= "
</table>
<div ALIGN=\"CENTER\">
  <input TYPE=\"SUBMIT\" VALUE=\""._("Enter the database")."\"/>
  <input TYPE=\"RESET\"  VALUE=\""._("Clear")."\"/>
</div>
</form>
";

if ($debug) {
	$display_buffer .= "
	<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"0\"
	 VALIGN=\"BOTTOM\" ALIGN=\"CENTER\" BGCOLOR=\"#000000\">
	<tr><td BGCOLOR=\"#000000\">
	<div ALIGN=\"CENTER\">
      	<b><font COLOR=\"#ffffff\" SIZE=\"-2\">"._("DEBUG IS ON")."</font></b>
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
