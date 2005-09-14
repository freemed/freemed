<?php
 // $Id$
 // desc: box class for php (freshmeat style)
 // code: jeff b <jeff@ourexchange.net>
 // lic : LGPL

if (!defined("__CLASS_BOX_PHP__")) {

define ('__CLASS_BOX_PHP__', true);

class box {

  var $box_title;  // box title
  var $box_buffer; // buffer for all box text

  function box ($box_title, $box_text = "") {
    $this->box_title = $box_title;
    $this->box_buffer = ( !empty($box_text) ? $box_text : "" );
    if (!empty($box_text)) $this->box_display ();
  } // end constructor box

  function box_add ($this_text) {
    $this->box_buffer .= $this_text;
    return true;
  } // end function box_add

  function box_display ($null_val = "") {
    echo $this->box_top ().
         $this->box_get_content ().
         $this->box_bottom ();
  } // end function box_display

  function box_get_content ($null_val = "") {
    return $this->box_buffer;
  } // end function box_get_content

  function box_top ($width = "") {
    return "
      <TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\"
       BGCOLOR=\"#000000\" ".
       ( !empty($width) ? "WIDTH=\"90%\"" : "" ).">
      <TR><TD>

      <TABLE WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"1\"
       CELLPADDING=\"0\">
      <TR><TD BGCOLOR=\"#f0f0f0\">
      <FONT FACE=\"Lucida, Verdana, Helvetica, Arial\">

      <TABLE BORDER=\"0\" CELLPADDING=\"3\" CELLSPACING=\"0\"
       BGCOLOR=\"#f0f0f0\" WIDTH=\"100%\">
      <TR><TD WIDTH=\"30%\" VALIGN=TOP ALIGN=\"CENTER\"
       BGCOLOR=\"#000000\" NOBR>
      <B><FONT FACE=\"Lucida, Verdana, Helvetica, Arial\" COLOR=\"#ffffff\">
      ".htmlentities($this->box_title)."

      </FONT></B></TD>
      <TD></TD></TR>
      <TR><TD COLSPAN=2>
    ";
  } // end function box_top

  function box_bottom ($null_val = "") {
    return "
      </TD></TR></TABLE>

      </TD></TR></TABLE>

      </TD></TR></TABLE>
    ";
  } // end function box_bottom 

} // end class box

} // check if defined

?>
