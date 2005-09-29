<?php
 // $Id$
 // $Author$
 // desc: PHP "clone" of traditional GUI wizard
 // lic : LGPL

class wizard {

  // *** VARIABLES ***
  var $size;       // size of notebook
  var $page;       // array of pages
  var $name;       // page names
  var $verify;     // verification things
  var $com_vars;   // common variables (hidden)
  var $vars;       // variables passed
  var $cur_page;   // currently displayed page
  var $location;   // location of the page
  var $FGCOLOR;    // foreground color
  var $BGCOLOR;    // background color
  var $BCOLOR;     // CSS button color
  var $LINECOLOR;  // CSS line color
  var $FINISH;     // name of the submit button text
  var $REFRESH;    // name of the refresh button text
  var $PREVIOUS;   // name of the previous button text
  var $NEXT;       // name of the next button text
  var $CANCEL;     // name of the cancel button text
  var $REVISE;     // name of the revise button text
  var $PLEASEREVISE;     // heading of "Please Revise" bar
  var $common_bar; // common submit/refresh bar at bottom of boxes?
  var $spill_at;   // number to spill over to next row at...
  var $formname;
  var $onsubmit;

  // *** METHODS ***
  function wizard ($com_vars = "") {
    $this->size       = 0;           // start wizard size at 0
    
    $this->formname = "form_".($GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH'] + 0);

    list($this->location, $the_rest) = // initialize location 
      explode ("?", basename($GLOBALS["REQUEST_URI"]));

    $this->FGCOLOR    = "#ffffff";   // default foreground
    $this->BGCOLOR    = "#c5c5c5";   // default background
    $this->FINISH     = "Finish";    // submit button text
    $this->REFRESH    = "Refresh";   // refresh button text
    $this->PREVIOUS   = "Previous";  // previous button text
    $this->NEXT       = "Next";      // next button text
    $this->CANCEL     = "Cancel";    // cancel button text
    $this->REVISE     = "Revise";    // revise button text
    $this->PLEASEREVISE = "Please Revise";    // revise button text
//	$this->WIDTH      = "";

    if (is_array ($com_vars)) {
      $this->vars[0]  = $com_vars;
      $this->com_vars = true;
    } else { // if there are no common variables
      $this->com_vars = false;
    } // end checking for common variables

    // check for FORM_ERROR and FORM_WARNING before closing com_vars
    global $FORM_ERROR, $FORM_WARNING;
    if (is_array($FORM_ERROR)) {
      $this->vars[0][] = "FORM_ERROR";
      $this->com_vars = true;
      $FORM_ERROR = array_unique($FORM_ERROR);
     }
    if (is_array($FORM_WARNING)) {
      $this->vars[0][] = "FORM_WARNING";
      $this->com_vars = true;
      $FORM_WARNING = array_unique($FORM_WARNING);
    }

    // Decide on line color for CSS
    if (($this->FGCOLOR == "#000000") or ($this->BGCOLOR == "#000000")) {
      $this->LINECOLOR = "#ffffff";
    } else {
      $this->LINECOLOR = "#000000";
    }
  } // end constructor wizard

	// function wizard->been_here()
	//   simple check for whether we've been here, based on whether
	//   __action_last has been set or not
	function been_here () {
		global $__action_last;
		return isset($__action_last);
	} // end function wizard->been_here

  // function wizard->get_current_page()
  //   returns the current page of the wizard from global scope
  function get_current_page ($null_var = "") {
    global $__action, $__action_last;
    if (!empty($__action_last)) {
      switch ($__action) {
        case $this->PREVIOUS:
          return ($__action_last - 1);
          break; // if PREVIOUS
        case $this->CANCEL:
          DIE ("wizard->get_current_page : should not be here!");
          break; // if CANCEL
        case $this->REVISE:
        case $this->NEXT:
          return ($__action_last + 1);
          break; // if NEXT/REVISE
        case $this->REFRESH:
	default:
          return ($__action_last + 0);
          break; // if REFRESH
      } // end switch for action
    } else { // if __action_last is empty
      return 1; // default to page 1
    } // end checking if __action_last is empty
  } // end function wizard->get_current_page

  // function wizard->is_cancelled ()
  //   returns true if the form is ready to be processed
  function is_cancelled ($null_var = "") {
    global $__action;
    return ($__action == $this->CANCEL);
  } // end function wizard->is_cancelled

  // function wizard->is_done ()
  //   returns true if the form is ready to be processed
  function is_done ($null_var = "") {
    global $__action;
    return ($__action == $this->FINISH) and ($this->verify_page(&$message));
  } // end function wizard->is_done

  // function wizard->add_page (name, array of variables, text)
  //   adds a new "page" to the end of the wizard
  function add_page ($page_name, $page_vars, $page_text, $verify = NULL) {
    $this->size++; // increment counter
    $this->name[($this->size)]   = $page_name;
    $this->vars[($this->size)]   = $page_vars;
    $this->page[($this->size)]   = $page_text;
    $this->verify[($this->size)] = $verify;
  } // end function wizard->add_page

  // function wizard->display ()
  //   displays the current view of the wizard
  function display ($null_var = "") {
    global $__action, $__action_last, $FORM_ERROR;

    $buffer .= "<script LANGUAGE=\"javascript\">\n".
    	"var __".$this->formname."_cancelled = 0; \n".
	"</script>\n";

    // here we decide on the current page, and display it
    $this->cur_page = $this->get_current_page(); // grab current page
    if ($this->cur_page < 1) $this->cur_page = 1; // bounds checking

    // verification handler
    if (!$this->verify_page(&$error_text)) {

      // display members of FORM_ERROR[] array if it exists
      if (is_array($FORM_ERROR)) {
        foreach ($FORM_ERROR AS $k => $v) {
          $error_vars .= "<INPUT TYPE=\"HIDDEN\" NAME=\"FORM_ERROR[]\" ".
            "VALUE=\"".prepare($v)."\">\n";
        } // end looping for each FORM_ERROR value
      } // end checking for FORM_ERROR array

      // display members of FORM_WARNING[] array if it exists
      if (is_array($FORM_WARNING)) {
        foreach ($FORM_WARNING AS $k => $v) {
          $error_vars .= "<INPUT TYPE=\"HIDDEN\" NAME=\"FORM_WARNING[]\" ".
            "VALUE=\"".prepare($v)."\">\n";
        } // end looping for each FORM_WARNING value
      } // end checking for FORM_WARNING array

      // actual return message
      return "
      <FORM ACTION=\"".$this->location."\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"__action_last\"
       VALUE=\"".prepare($__action_last - 1)."\">
      ".$error_vars."
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 BGCOLOR=\"".
       $this->FGCOLOR."\" ".( !empty($this->WIDTH) ?
		" WIDTH=\"".$this->WIDTH."\"" : "100%" ).">
      <TR><TD COLSPAN=1 BGCOLOR=\"".$this->BGCOLOR."\">
       <CENTER><B>".$this->PLEASEREVISE."</B></CENTER>
      </TD></TR><TR><TD COLSPAN=1 BGCOLOR=\"".$this->FGCOLOR."\">
      ".$this->display_hidden_variables($this->cur_page)."
      ".$error_text."
      </TD></TR><TR BGCOLOR=\"".$this->BGCOLOR."\">
       <TD COLSPAN=1 ALIGN=CENTER>
       ".$this->generate_revise()."
       ".$this->generate_cancel()."
       </TD>
      </TR>
      </TD></TR></TABLE></FORM>
      ";
    } // end of verification handler

	   //( ($this->onsubmit) ? "onSubmit=\"".$this->onsubmit."\"" : "" ).">
    $buffer .= "
      <FORM ACTION=\"".$this->location."\" METHOD=POST NAME=\"".
	$this->formname."\" ".
	   ( ($this->onsubmit) ? "onSubmit=\"if (!(__".$this->formname."_cancelled)) {".$this->onsubmit."}\"" : "" ).">
      <INPUT TYPE=HIDDEN NAME=\"__action_last\"
       VALUE=\"".prepare($this->cur_page)."\">
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 BGCOLOR=\"".
       $this->FGCOLOR."\" ".( !empty($this->WIDTH) ?
		" WIDTH=\"".$this->WIDTH."\"" : "100%" ).">
    ";
    $buffer .= $this->display_hidden_variables($this->cur_page);
    $buffer .= "
      <TR><TD COLSPAN=3 BGCOLOR=\"".$this->BGCOLOR."\">
       <CENTER><B>".$this->name[($this->cur_page)]."</B></CENTER>
      </TD></TR><TR><TD COLSPAN=3 BGCOLOR=\"".$this->FGCOLOR."\">
      <!-- wizard body begins here -->
    ";
    $buffer .= $this->strip_form_tags($this->page[($this->cur_page)]);
    $buffer .= "
      </TD></TR><TR BGCOLOR=\"".$this->BGCOLOR."\">
       <TD COLSPAN=1 ALIGN=LEFT>
      ".(
       ($this->cur_page > 1) ?
       $this->generate_previous() : $this->generate_cancel()
      )."</TD>
       <TD COLSPAN=1 ALIGN=CENTER>
       ".$this->generate_refresh().(
       ($this->cur_page > 1) ? $this->generate_cancel() : "" )."
       </TD>
       <TD COLSPAN=1 ALIGN=RIGHT>
       ".(
       ($this->cur_page < $this->size) ?
       $this->generate_next() : $this->generate_finish()
       )."
       </TD>
      </TR>
      ";
    $buffer .= "
      <!-- wizard body ends here -->
      </TD></TR></TABLE></FORM>
    ";

	// return information
	return $buffer;
  } // end function wizard->display

  // function wizard->display_hidden_variables (page number)
  //   embeds all of the variables into the page in INPUT TYPE=HIDDEN
  //   tags, skipping over the current page. handles all types, including
  //   arrays.
  function display_hidden_variables($__page_to_display) {
	$buffer = "";

    // loop through pages
    for ($__i=($this->com_vars ? 0 : 1);$__i<=($this->size);$__i++) {
      if (($__page_to_display != $__i) and
          (!empty($this->vars[$__i]))) { // skip current page and empties
        $__this_set = $this->vars[$__i];
        for ($__j=0;$__j<(count($__this_set));$__j++) { // loop vars
	  $__inner_set = $__this_set [$__j];
          global $$__inner_set; // pull into local scope
	  if (!is_array($$__inner_set)) {
            // if it is just scalar, display it
	    $buffer .= "<input TYPE=\"HIDDEN\" ID=\"".prepare($__inner_set)."\" NAME=\"".prepare($__inner_set)."\" ".
          "VALUE=\"".prepare($$__inner_set, true)."\" />\n";
	  } else { // if it *is* an array
            for ($__k=0;$__k<(count($$__inner_set));$__k++) {
	      $buffer .= "<INPUT TYPE=HIDDEN NAME=\"".
	        prepare($__inner_set)."[]\" ".
			"VALUE=\"".prepare(${$__inner_set}[$__k], true)."\">\n";
	    } // end looping through array
	  } // end checking for array
	} // end looping through variables
      } // end checking for current page
    } // end looping through pages
	return $buffer;
  } // end function wizard->display_hidden_variables

  function set_button_color ($new_color) {
    $this->BCOLOR = $new_color;
  } // end function wizard->set_button_color

  function set_foreground_color ($new_color) {
    $this->FGCOLOR = $new_color;
  } // end function wizard->set_foreground_color

  function set_background_color ($new_color) {
    $this->BGCOLOR = $new_color;
  } // end function wizard->set_background_color

  function set_line_color ($new_color) {
    $this->LINECOLOR = $new_color;
  } // end function wizard->set_line_color

  function set_width ($width) {
    $this->WIDTH = $width;
  } // end function wizard->set_width

  // ---------------------------------------------- button names setting

  function set_cancel_name ($new_text) {
    $this->CANCEL = $new_text;
  } // end function wizard->set_cancel_name

  function set_finish_name ($new_text) {
    $this->FINISH = $new_text;
  } // end function wizard->set_finish_name

  function set_next_name ($new_text) {
    $this->NEXT = $new_text;
  } // end function wizard->set_next_name

  function set_previous_name ($new_text) {
    $this->PREVIOUS = $new_text;
  } // end function wizard->set_previous_name

  function set_refresh_name ($new_text) {
    $this->REFRESH = $new_text;
  } // end function wizard->set_refresh_name

  function set_revise_name ($new_text) {
    $this->REVISE = $new_text;
  } // end function wizard->set_revise_name

  // -----------------------------------------button generation routines

  function generate_finish ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"__action\" ".
      "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
      "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->FINISH)."\"/>\n";
  } // end function generate_finish

  function generate_refresh ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"__action\" ".
      "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
      "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->REFRESH)."\"/>\n";
  } // end function generate_refresh

  function generate_previous ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"__action\" ".
      "onClick=\"__".$this->formname."_cancelled = 1; return true;\" ".
      "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
      "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->PREVIOUS)."\"/>\n";
  } // end function generate_previous

  function generate_next ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"__action\" ".
      "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
      "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->NEXT)."\"/>\n";
  } // end function generate_next

  function generate_cancel ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"__action\" ".
      "onClick=\"__".$this->formname."_cancelled = 1; return true;\" ".
      "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
      "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->CANCEL)."\"/>\n";
  } // end function generate_next

  function generate_revise ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"__action\" ".
      "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
      "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->REVISE)."\"/>\n";
  } // end function generate_revise

  function verify_page (&$message) {
    global $__action, $__action_last;

    // if __action != $this->NEXT/$this->FINISH, don't verify
    if ( ($__action != $this->NEXT) and ($__action != $this->FINISH) ) return true;

    // check to see if there was a last action, if not, verified
    if (!isset($__action_last)) return true;

    // check to see if we have to verify anything at all
    if ($this->verify == NULL) return true;

    // get last page, verified
    $page_to_verify = $__action_last;
    $verify = $this->verify[$page_to_verify];

    // if we don't have to verify anything...
    if ($verify == NULL) return true;

    // call to external verification routine for actual checks
    return verify (&$message, $verify, CHECK_FOR_ERRORS);

  } // end function verify_page

  function set_onsubmit ($value = '') {
    $this->onsubmit = $value;
  } // end method set_onsubmit

	function strip_form_tags ( $page ) {
		$new = preg_replace('#<FORM[^>]*?>#i', '', $page);
		$new = preg_replace('#</FORM[^>]*?>#i', '', $new);
		return $new;
	} // end method strip_form_tags

} // end class wizard

?>
