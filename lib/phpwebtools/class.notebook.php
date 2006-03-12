<?php
 // $Id$
 // $Author$
 // desc: PHP "clone" of GTK notebook widget
 // lic : LGPL

// recursive depth
$GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH'] = 0;

// Class: PHP.notebook
class notebook {

  // *** VARIABLES ***
  var $psize;      // size of notebook
  var $page;       // array of pages
  var $name;       // page names
  var $com_vars;   // common variables (hidden)
  var $vars;       // variables passed
  var $cur_page;   // currently displayed page
  var $location;   // location of the page
  var $FGCOLOR;    // foreground color
  var $BGCOLOR;    // background color
  var $LINECOLOR;  // CSS line color
  var $BCOLOR;     // CSS button color
  var $SUBMIT;     // name of the submit button text
  var $REFRESH;    // name of the refresh button text
  var $REVISE;     // name of the revise button text
  var $CANCEL;     // name of the cancel button text
  var $common_bar; // common submit/refresh bar at bottom of boxes?
  var $spill_at;   // number to spill over to next row at...
  var $stretch;    // stretch to 100% of available area?
  var $scroll;     // scrolling capabilities?
  var $actionvar;  // action variable
  var $formdisable;// disable form tags
  var $formname;   // actual name of form ... good for JS stuff
  var $tabs_loc;   // tabs location
  var $onsubmit;
  var $error;      // array of checks made on page "index" (error status)
  var $warning;    // array of checks made on page "index" (warning status)
  var $messages;

  // *** METHODS ***
	// Method: notebook constructor
	//
	// Parameters:
	//
	//	$com_vars - (optional) Array of variables that should be
	//	passed by every page. Defaults to none.
	//
	//	$options - (optional) Bitfield options.
	//		* NOTEBOOK_TABS_LEFT
	//		* NOTEBOOK_TABS_RIGHT
	//		* NOTEBOOK_NOFORM
	//		* NOTEBOOK_STRETCH
	//		* NOTEBOOK_COMMON_BAR
	//		* NOTEBOOK_SCROLL
	//
	//	$spill_at - (optional) Number of tabs to create a new row
	//	at when using top tabs. Defaults to off.
	//
	//	$form_name - (optional) Name of FORM tag NAME attribute.
	//	If not given, one is created.
	//
  function notebook ($com_vars = "", $options = 0, $spill_at = false, $form_name="") {
    $this->psize      = 0;           // start notebook size at 0

    // Check for a given form name
    if ($form_name == '') {
      // Fudge one if it isn't provided
      $this->formname = "form_".$GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH'];
    } else {
      $this->formname = $form_name;
    }

     // check for both left and right tabs being requested
    if (($options & NOTEBOOK_TABS_LEFT) and ($options & NOTEBOOK_TABS_RIGHT))
      DIE ("notebook->constructor :: only left or right can be used");

    list($this->location, $the_rest) = // initialize location 
     explode ("?", basename($GLOBALS["REQUEST_URI"]));

    $this->FGCOLOR    = "#ffffff";   // default foreground
    $this->BGCOLOR    = "#c5c5c5";   // default background
    $this->BCOLOR     = $this->FGCOLOR; // default is foreground color
    $this->SUBMIT     = "Submit";    // submit button text
    $this->REFRESH    = "Refresh";   // refresh button text
    $this->REVISE     = "Revise";    // revise button text
    $this->CANCEL     = "Cancel";    // cancel button text
    $this->common_bar = ($options & NOTEBOOK_COMMON_BAR); // common bar
    $this->spill_at   = $spill_at;   // pass spilling status/number
    $this->stretch    = ($options & NOTEBOOK_STRETCH); // set stretching
    $this->scroll     = ($options & NOTEBOOK_SCROLL);
    $this->onsubmit   = '';
    if (!empty ($com_vars)) {
      $this->vars[0]  = $com_vars;
      $this->com_vars = true;
    } else { // if there are no common variables
      $this->com_vars = false;
    } // end checking for common variables
    
    // Decide on line color for CSS
    if (($this->FGCOLOR == "#000000") or ($this->BGCOLOR == "#000000")) {
      $this->LINECOLOR = "#ffffff";
    } else {
      // Default to black
      $this->LINECOLOR = "#000000";
    }

    // Check for disabled notebook
    $this->formdisable = ($options & NOTEBOOK_NOFORM);

    // check for recursion
    if ($GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH'] == 0) {
      $this->formdisable = false;	// make sure forms are enabled
      $GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH']++;		// increase level of recursion
      $this->actionvar="__action";	// set default actionvar

      // only for depth of 0, grab FORM_ERROR and FORM_WARNING
      // NOTE: Remember to remove FORM_ERROR and FORM_WARNING messages
      //       during processing, so that once they are fixed, they
      //       don't keep cropping up.
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
    } else {
      $this->formdisable = true;	// disable form tags for embedded
      $GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH']++; // increase level of recursion
      $this->actionvar="__action";	// start with __action
      for ($i=1;$i<=$GLOBALS['__phpwebtools']['NOTEBOOK_DEPTH'];$i++)
        $this->actionvar = "_" . $this->actionvar;
    } // end if

     // move stuff into $this->tabs_loc
    if ($options & NOTEBOOK_TABS_LEFT) {
      $this->tabs_loc = NOTEBOOK_TABS_LEFT;
    } else if ($options & NOTEBOOK_TABS_RIGHT) {
      $this->tabs_loc = NOTEBOOK_TABS_RIGHT;
    } else {
      $this->tabs_loc = 0;
    } // end if..then for tabs location

    // during REFRESH action, we don't particularly want errors or
    // warnings, so we clear them, same for actionvar=actionvar_last
    global ${$this->actionvar}, ${$this->actionvar."_last"};
    switch (${$this->actionvar}) {
      case $this->REFRESH:
      case ${$this->actionvar."_last"}:
        unset($GLOBALS["FORM_ERROR"]);
        unset($GLOBALS["FORM_WARNING"]);
        break;
      default:
        // nothing
        break;
    } // end actionvar switch

  } // end constructor notebook

	// Method: been_here
	//
	// Returns:
	//
	//	Boolean, if this notebook has been loaded already
	//
	function been_here ($null_var = "") {
		global ${$this->actionvar."_been_here"};
		return isset(${$this->actionvar."_been_here"});
	} // end function notebook->been_here

	// Method: get_current_page
	//
	//	Get the current page identifier.
	//
	// Parameters:
	//
	//	$action - (optional) Value passed by action button. Defaults
	//	to pulling from the global scope.
	//
	// Returns:
	//
	//	Returns the current page of the notebook
	//
  function get_current_page ($action = NULL) {
    global ${$this->actionvar};
    if ($action == NULL) $look_for = ${$this->actionvar};
      else $look_for = $action;
    // check for Revise...
    if ($look_for == $this->REVISE)
      $look_for = ${$this->actionvar."_last"};
    $found = 0;
    for ($i=1;$i<=($this->psize);$i++) {
      if ($this->name[$i] == $look_for) $found = $i;
    }  
    return $found;
  } // end function notebook->get_current_page

	// Method: get_vars
	//
	//	Get all variables used by the notebook
	//
	// Returns:
	//
	//	Array of all variables used by the notebook
	//
  function get_vars ($null_var = "") {
    // get local copy of variables
    $local_vars = $this->vars;

    // add action, been here, etc
    $local_vars[] = $this->actionvar."_last";
    $local_vars[] = $this->actionvar."_been_here";

    // return new var array
    return $local_vars;
  } // end function notebook->get_vars

	// Method: is_cancelled
	//
	//	Determine if the form has been cancelled
	//
	// Returns:
	//
	//	Boolean, whether the form has been cancelled
	//
  function is_cancelled ($null_var = "") {
    global ${$this->actionvar};
    return (html_entity_decode(${$this->actionvar}) == html_entity_decode($this->CANCEL));
  } // end function notebook->is_cancelled

	// Method: is_done
	//
	// Returns:
	//
	//	Boolean, whether the form is ready to be processed
	//
  function is_done ($null_var = "") {
    global ${$this->actionvar};
    //$this->verify_page(&$this->messages); // KLUDGE!
    return (html_entity_decode(${$this->actionvar}) == html_entity_decode($this->SUBMIT));
  } // end function notebook->is_done

	// Method: add_page
	//
	//	Adds a new "page" to the end of the notebook
	//
	// Parameters:
	//
	//	$page_name - Text name of the notebook page
	//
	//	$page_vars - Array of variables that are defined on this
	//	page
	//
	//	$text - Text of this notebook page
	//
	//	$err - (optional) Array of error conditions
	//
	//	$warn - (optional) Array of warning conditions
	//
  function add_page ($page_name, $page_vars, $page_text,
      $error=NULL, $warning=NULL) {
    if ($page_name == $this->SUBMIT)
      DIE ("notebook->add_page :: name ($page_name) is same as submit name");
    if ($page_name == $this->REFRESH)
      DIE ("notebook->add_page :: name ($page_name) is same as refresh name");
    // FIXME: check for other pages with the same name
    $this->psize++; // increment counter
    $this->name[($this->psize)] = $page_name;
    $this->vars[($this->psize)] = $page_vars;
    $this->page[($this->psize)] = $page_text;
    $this->error[($this->psize)] = $error;
    $this->warning[($this->psize)] = $warning;
    // remove crap from the name
    $this->name[($this->psize)] = eregi_replace (
      "<[A-Z/]*>", "", $this->name[($this->psize)]);
  } // end function add_page

	// Method: display
	//
	//	Gets the HTML code for the current view of the notebook
	//
	// Returns:
	//
	//	XHTML-compliant notebook widget code
	//
  function display ($null_var = "") {
    global ${$this->actionvar}, ${$this->actionvar."_last"},
      $FORM_ERROR, $FORM_WARNING;

    $buffer .= "<script LANGUAGE=\"javascript\">\n".
    	"var __".$this->formname."_cancelled = 0; \n".
	"</script>\n";

    // flow of code here:
    //   * if it's a revise, verify, then display actionvar_last
    //   * else check and possibly display revise

    if (${$this->actionvar} == $this->REVISE) {
      // make sure we verify the page
      $stub = $this->verify_page (&$this->messages);
      // then set to the last action, so that we come back to
      // where we were
      ${$this->actionvar} = ${$this->actionvar."_last"};
    } else {
      // handle refresh here
      if (${$this->actionvar} == $this->REFRESH)
        ${$this->actionvar} = ${$this->actionvar."_last"};

      // check to see if we're valid
      if (!$this->verify_page (&$this->messages)) {
        if (is_array($FORM_ERROR)) {
          foreach ($FORM_ERROR AS $k => $v) {
            $error_vars .= "<input TYPE=\"HIDDEN\" NAME=\"FORM_ERROR[]\" ".
              "VALUE=\"".prepare($v)."\"/>\n";
          } // end looping for FORM_ERROR
        } // end if is array FORM_ERROR
	    //echo "after verify_page: action = ".${$this->actionvar}.", last = ".${$this->actionvar."_last"}."<br/>\n";
        $buffer .= "
          ".( (!$this->formdisable) ?
           "<form ACTION=\"".$this->location."\" METHOD=\"POST\" ".
	   "NAME=\"".$this->formname."\" ".
	   ( ($this->onsubmit) ? "onSubmit=\"if (!(__".$this->formname."_cancelled)) {".$this->onsubmit."}\"" : "" ).
	   ">" : "" )."
          <input TYPE=\"HIDDEN\" NAME=\"".$this->actionvar."_last\"
           VALUE=\"".prepare(${$this->actionvar."_last"})."\"/>
          <input TYPE=\"HIDDEN\" NAME=\"".$this->actionvar."_been_here\" VALUE=\"1\"/>
          ".$error_vars;
        for ($i=0; $i<($this->psize); $i++)
          $buffer .= $this->display_hidden_variables($i);
        $buffer .= "
          <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\" BGCOLOR=\"".
           $this->FGCOLOR."\" ".( ($this->stretch) ? "WIDTH=\"100%\"" : "")." ".
           ">\n";
        $buffer .= "Messages:\n";
        $buffer .= 
          "<tr><td COLSPAN=\"".( $this->spill_at ? $this->spill_at : $this->psize)
          ."\" ".($this->stretch ? "ALIGN=\"CENTER\"" : "" ).">
          ".($this->stretch ? "<div ALIGN=\"CENTER\">" : "" )."
          <!-- notebook body begins here -->
          ".$this->messages."
        ";
        $buffer .= "
           ".($this->stretch ? "</div>" : "" )."
           </td></tr><tr><td COLSPAN=\"".
           ( $this->spill_at ? $this->spill_at : $this->psize)."\"
                     BGCOLOR=\"".$this->BGCOLOR."\">
           <div ALIGN=\"CENTER\" ".
	   ">
           ".(
           (${$this->actionvar} != $this->REVISE) ?
           $this->generate_submit()."
           &nbsp;
           ".$this->generate_refresh() :
           $this->generate_revise()
           )."
           </div>
          </td></tr>
          <!-- notebook body ends here -->
          </td></tr></table>".( (!$this->formdisable) ? "</form>" : "" )."
        ";
        return $buffer;
      } else {
        // if we're fine ... 
        // currently do nothing, but we'll have something here soon
      } // end checking for verify
    } // end checking for revise status

    // provision for embedded table
    if (empty(${$this->actionvar}) and (!empty(${$this->actionvar."_last"})))
      ${$this->actionvar} = ${$this->actionvar."_last"};

    // here we decide on the current page, and display it
    $this->cur_page = $this->get_current_page(); // grab current page
    if ($this->cur_page < 1) $this->cur_page = 1; // bounds checking
    $buffer .= "
      ".( (!$this->formdisable) ?
           "<form ACTION=\"".$this->location."\" METHOD=\"POST\" ".
	   "NAME=\"".$this->formname."\" ".
	   ( ($this->onsubmit) ? "onSubmit=\"if (!(__".$this->formname."_cancelled)) {".$this->onsubmit."}\"" : "" ).
	   ">" : "" )."
      <input TYPE=\"HIDDEN\" NAME=\"".$this->actionvar."_last\"
       VALUE=\"".prepare(${$this->actionvar})."\"/>
      <input TYPE=\"HIDDEN\" NAME=\"".$this->actionvar."_been_here\" VALUE=\"1\"/>
      ".$error_vars."
      ".$this->display_hidden_variables($this->cur_page)."
      <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"2\" BGCOLOR=\"".
       $this->FGCOLOR."\" ".( ($this->stretch) ? "WIDTH=\"100%\" " : "").
           ( ($this->scroll) ? "STYLE=\"height: 100%; overflow: auto;\"" : "").
	">\n";

     // if the tabs are on the top
    if (!$this->tabs_loc) {
      $buffer .= $this->display_notebook_bar($this->cur_page)."
        <tr><td COLSPAN=\"".( $this->spill_at ? $this->spill_at : $this->psize)
          ."\" ".($this->stretch ? "ALIGN=\"CENTER\"" : "" )." ".
	  "style=\"border: 1px solid; border-color: $this->FGCOLOR $this->LINECOLOR ".
	  "$this->LINECOLOR $this->LINECOLOR;\">
	  ".($this->stretch ? "<div ALIGN=\"CENTER\" ".

           ( ($this->scroll) ? "STYLE=\"height: 100%; overflow: auto;\"" : "").

		">" : "" )."
        <!-- notebook body begins here -->
        ";
      if (${$this->actionvar} != $this->REVISE)
        $buffer .= $this->strip_form_tags($this->page[($this->cur_page)]);
    } else { // if we use side tabs
      $buffer .= $this->display_notebook_sidebar($this->cur_page)."\n";
    } // end checking for this->tabs_loc

     // back to common stuff
    if ($this->common_bar) $buffer .= "
	".($this->stretch ? "</div>" : "" )."
      </td></tr><tr><td COLSPAN=\"".
        ( $this->spill_at ? $this->spill_at : $this->psize)."\"
                     BGCOLOR=\"".$this->BGCOLOR."\">
       <div ALIGN=\"CENTER\">
       ".$this->generate_submit()."&nbsp;".
         $this->generate_refresh()."&nbsp;".
         $this->generate_cancel()."
       </div>
      </td></tr>
      ";
    $buffer .= "
      <!-- notebook body ends here -->
      </td></tr></table>".( (!$this->formdisable) ? "</form>" : "" )."
    ";

    return $buffer;
  } // end function notebook->display

	// Method: display_notebook_bar
	//
	//	Get the HTML code for the notebook button bars when
	//	displayed at the top of the notebook.
	//
	// Parameters:
	//
	//	$page_to_display - Number of current page. This has to
	//	be looked up.
	//
	// Returns:
	//
	//	XHTML-compliant notebook button code
	//
	// See Also:
	//	<display_notebook_sidebar>
	//
  function display_notebook_bar($page_to_display) {
    $buffer = ""; // initialize buffer
    unset ($bars);

    // Check for invalid spill_at
    if ($this->spill_at < 1) {
      $width_factor = $this->psize;
    } else {
      $width_factor = $this->spill_at;
    }
    
    $cur_bar = 1;
    $bars[$cur_bar] = "\n<tr>\n";
    for ($i=1;$i<=$this->psize;$i++) { // loop through pages
      if (($this->spill_at) AND
          ((($i - 1) % $this->spill_at) == 0) AND
	  ($i != 1)) {
	$bars[$cur_bar] .= "\n</tr>\n";
	$cur_bar++;
        $bars[$cur_bar] = "\n<tr>\n";
      } // end checking for spill
      $bars[$cur_bar] .= "
        <td COLSPAN=\"1\" ALIGN=\"LEFT\" VALIGN=\"BOTTOM\"
	 style=\"border: 1px solid; -moz-border-radius-topleft: 15px; -moz-border-radius-topright: 15px; border-color: $this->LINECOLOR $this->LINECOLOR ".
	 ( ($i == $page_to_display) ?
	   $this->FGCOLOR : $this->BGCOLOR )." $this->LINECOLOR; ".
	   // Figure out width so everything is even
	   "width: ".((int)((1 / $width_factor) * 100))."%;".
	   "\" ".( ($i == $page_to_display) ?
	   "BGCOLOR=\"".$this->FGCOLOR."\"" :
	   "BGCOLOR=\"".$this->BGCOLOR."\"" ).">
         <input TYPE=\"SUBMIT\" NAME=\"".$this->actionvar."\" VALUE=\"".
	 prepare($this->name[$i]).
	 "\" style=\"text-align: left; border: 0px; ".
	 "text-decoration: none; width: 90%; ". 
	 // Bold current page
	 ( ($i == $page_to_display) ? "font-weight: bold; " : "" ).
	 // Set background appropriately
	 "background-color: ".( ($i == $page_to_display) ?
	   $this->FGCOLOR : $this->BGCOLOR ).";\"/>
	</td>
      ";
      if ($i == $page_to_display) $active_bar = $cur_bar;
    } // end looping through pages
    if (($this->spill_at) AND
        (($this->psize % $this->spill_at) > 0)) {
      $bars[$cur_bar] .= "\n<td COLSPAN=\"".
        ($this->spill_at - ($this->psize % $this->spill_at))."\" ".
           "ALIGN=\"LEFT\" VALIGN=\"BOTTOM\" BGCOLOR=\"".$this->BGCOLOR."\">".
	   "&nbsp;</td>\n";
    } // end checking for spill
    $bars[$cur_bar] .= "\n</tr>\n";

    // display bars
    for ($i=1;$i<=$cur_bar;$i++) {
      // if it isn't the active bar, display it
      if ($i != $active_bar) $buffer .= $bars[$i];
    } // loop through all the bars

    // now display active bar
    $buffer .= $bars[$active_bar];

    return $buffer;
  } // end function notebook->display_notebook_bar

	// Method: display_notebook_sidebar
	//
	//	Get the HTML code for the notebook button bars when
	//	displayed at the left or right of the notebook.
	//
	// Parameters:
	//
	//	$page_to_display - Number of current page. This has to
	//	be looked up.
	//
	// Returns:
	//
	//	XHTML-compliant notebook button code
	//
	// See Also:
	//	<display_notebook_bar>
	//
  function display_notebook_sidebar($page_to_display) {
    $buffer = ""; // initialize buffer

    // figure out left or right
    $lr = ( ($this->tabs_loc == NOTEBOOK_TABS_LEFT) ? 'left' : 'right' );

    for ($i=1;$i<=$this->psize;$i++) { // loop through pages
      $bar_part = "
        <td COLSPAN=\"1\" ALIGN=\"LEFT\" VALIGN=\"TOP\" WIDTH=\"20%\" ".
	 "style=\"border: 1px solid; -moz-border-radius-top".$lr.": 10px; ".
	 " -moz-border-radius-bottom".$lr.": 10px; border-color: ".
	 ( ($this->tabs_loc == NOTEBOOK_TABS_LEFT) ?
	 "$this->LINECOLOR ".( ($i == $page_to_display) ?
	  $this->FGCOLOR : $this->BGCOLOR )." $this->LINECOLOR $this->LINECOLOR" :
	 "$this->LINECOLOR $this->LINECOLOR $this->LINECOLOR".
	 ( ($i == $page_to_display) ? $this->FGCOLOR : $this->BGCOLOR ) ).
	  ";\" ".
         ( ($i == $page_to_display) ?
         "BGCOLOR=\"".$this->FGCOLOR."\"" :
         "BGCOLOR=\"".$this->BGCOLOR."\"" ).">
         <input TYPE=\"SUBMIT\" NAME=\"".$this->actionvar."\" VALUE=\"".
         prepare($this->name[$i])."\" ".
	 " class=\"".
		( ($i == $page_to_display) ?
		'notebook_tab_side' : 'notebook_tab_side_current' )."\"".
	 " style=\"text-align: left; border: 0px; ".
	 "text-decoration: none; width: 90%; height: 90%; ".
	 // Bold the current page
	 ( ($i == $page_to_display) ? "font-weight: bold; " : "" ).
	 // Determine proper background color
	 "background-color: ".( ($i == $page_to_display) ?
	   $this->FGCOLOR : $this->BGCOLOR ).";\"/>
        </td>
      ";
      if ($i == 1) {
        $page_part .= "\n<td ROWSPAN=\"".$this->psize."\" ".
	 "style=\"border: 1px solid; border-color: ".
	 ( ($this->tabs_loc == NOTEBOOK_TABS_LEFT) ?
	 "$this->LINECOLOR $this->LINECOLOR $this->LINECOLOR $this->FGCOLOR" :
	 "$this->LINECOLOR $this->FGCOLOR $this->LINECOLOR $this->LINECOLOR" ).
	  ";\">\n".
          ( (${$this->actionvar} == $this->REVISE) ?
            $this->messages : $this->page[($this->cur_page)] )."\n</td>\n";
      } else {
        $page_part = "";
      } // end checking if we display this page

      // check if we're doing left or right
      if ($this->tabs_loc == NOTEBOOK_TABS_LEFT) {
        $buffer .= "\n<tr>\n" . $bar_part . $page_part . "\n</tr>\n";
      } else if ($this->tabs_loc == NOTEBOOK_TABS_RIGHT) {
        $buffer .= "\n<tr>\n" . $page_part . $bar_part . "\n</tr>\n";
      } // end checking for loc of tabs

      $buffer .= "\n</tr>\n";
    } // end looping through pages

    return $buffer;
  } // end function notebook->display_notebook_sidebar

	// Method: display_hidden_variables
	//
	//	Get HTML code for embedding variables that are not
	//	displayed in the current page in INPUT TYPE=HIDDEN tags.
	//	Properly handles arrays.
	//
	// Parameters:
	//
	//	$__page_to_display - Number of the page to be displayed
	//
	// Returns:
	//
	//	XHTML-compliant hidden variable code
	//
  function display_hidden_variables($__page_to_display) {
    $buffer = ""; // initialize buffer
    // loop through pages
    for ($__i=($this->com_vars ? 0 : 1);$__i<=($this->psize);$__i++) {
      if (($__page_to_display != $__i) and
          (!empty($this->vars[$__i]))) { // skip current page and empties
        $__this_set = flatten_array ($this->vars[$__i]);
        for ($__j=0;$__j<(count($__this_set));$__j++) { // loop vars
	  $__inner_set = $__this_set [$__j];
          global $$__inner_set; // pull into local scope
	  if (!is_array($$__inner_set)) {
            // if it is just scalar, display it
	    if (!empty($$__inner_set))
	      $buffer .= "<input TYPE=\"HIDDEN\" ID=\"".prepare($__inner_set)."\" NAME=\"".prepare($__inner_set)."\" ".
            "VALUE=\"".prepare($$__inner_set, true)."\"/>\n";
	  } else { // if it *is* an array
	    foreach (${$__inner_set} AS $__k => $__v) {
	      if (is_array($__v)) {
	        foreach ($__v AS $___k => $___v) {
	          $buffer .= "<input TYPE=\"HIDDEN\" NAME=\"".prepare($__inner_set)."[".prepare($__k)."][".prepare($___k)."]\" ".
                  "VALUE=\"".prepare(${$__inner_set}[$__k][$___k], true)."\"/>\n";
                }
	      } else {
	      $buffer .= "<input TYPE=\"HIDDEN\" NAME=\"".prepare($__inner_set)."[$__k]\" ".
            "VALUE=\"".prepare(${$__inner_set}[$__k], true)."\"/>\n";
	      }
	    }
            for ($__k=0;$__k<(count($$__inner_set));$__k++) {
	    } // end looping through array
	  } // end checking for array
	} // end looping through variables
      } // end checking for current page
    } // end looping through pages
    return $buffer;
  } // end function notebook->display_hidden_variables

	// TODO: Have to finish documenting this class

  function generate_cancel ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" ".
         "onClick=\"__".$this->formname."_cancelled = 1; return true;\" ".
         "id=\"".$this->formname."_cancel\" ".
         "NAME=\"".$this->actionvar."\" ".
	 "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
	 "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->CANCEL)."\"/>\n";
  } // end function notebook->generate_cancel

  function generate_refresh ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"".$this->actionvar."\" ".
	 "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
	 "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->REFRESH)."\"/>\n";
  } // end function notebook->generate_refresh

  function generate_revise ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"".$this->actionvar."\" ".
	 "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
	 "background-color: $this->BCOLOR;\" ".
      "VALUE=\"".prepare($this->REVISE)."\"/>\n";
  } // end function notebook->generate_revise

  function generate_submit ($null_val = "") {
    return " <input TYPE=\"SUBMIT\" NAME=\"".$this->actionvar."\" ".
	 "style=\"border: 1px solid; border-color: $this->LINECOLOR; ".
	 "background-color: $this->BCOLOR; font-weight: bold;\" ".
      "VALUE=\"".prepare($this->SUBMIT)."\"/>\n";
  } // end function generate_submit

  function set_foreground_color ($new_color) {
    $this->FGCOLOR = $new_color;
  } // end function notebook->set_foreground_color

  function set_background_color ($new_color) {
    $this->BGCOLOR = $new_color;
  } // end function notebook->set_background_color

  function set_line_color ($new_color) {
    $this->LINECOLOR = $new_color;
  } // end function notebook->set_line_color

  function set_button_color ($new_color) {
    $this->BCOLOR = $new_color;
  } // end function notebook->set_button_color

  function set_cancel_name ($new_text) {
    $this->CANCEL = $new_text;
  } // end function notebook->set_cancel_name

  function set_submit_name ($new_text) {
    $this->SUBMIT = $new_text;
  } // end function notebook->set_submit_name

  function set_refresh_name ($new_text) {
    $this->REFRESH = $new_text;
  } // end function notebook->set_refresh_name

  function set_revise_name ($new_text) {
    $this->REVISE = $new_text;
  } // end function notebook->set_revise_name

  function set_spill_at ($spill_at) {
    $this->spill_at = $spill_at;
  } // end function notebook->set_spill_at

  function set_stretch_on ($null_var="") {
    $this->stretch = true;
  } // end function notebook->set_stretch_on

  function set_stretch_off ($null_var="") {
    $this->stretch = false;
  } // end function notebook->set_stretch_off

  function set_onsubmit ($value = '') {
    $this->onsubmit = $value;
  } // end method set_onsubmit

  function verify_page (&$message) {
    global ${$this->actionvar}, ${$this->actionvar."_been_here"},
      ${$this->actionvar."_last"}, $FORM_ERROR, $FORM_WARNING;

    // if this is the first time, verify automatically (can't be wrong)
    if (!isset(${$this->actionvar."_been_here"})) return true;
    if (!isset(${$this->actionvar."_last"})) return true;

    // set prospective actionvar
    $prospective_action = ${$this->actionvar};

	// ask for the page name
    // check if there is no actionvar_last...
    if (empty(${$this->actionvar."_last"})) {
      // then we know that it came from the first tab
      $last_page = 1;
    } else {
      // or else, we divine it...
      $last_page = $this->get_current_page(${$this->actionvar."_last"});
    } // end checking for empty actionvar_last

	// if there was no last page...
    if (!$last_page) return true;

    // verification calls for checking errors and warnings
    // note: it only doesn't "pass" if it has errors
	$passed = verify (&$message, $this->error[$last_page], CHECK_FOR_ERRORS);
	$warn = verify (&$message, $this->warning[$last_page], CHECK_FOR_WARNINGS);

	// if not passed, change actionvar to the proper place
	if (!$passed) {
      ${$this->actionvar} = (
        (${$this->actionvar} != $this->REVISE) ?
        $this->REVISE :
        ${$this->actionvar}
      );
    }
	
	// "passthru" passed variable
	return $passed;

  } // end function notebook->verify_page

	function strip_form_tags ( $page ) {
		$new = preg_replace('#<FORM[^>]*?>#i', '', $page);
		$new = preg_replace('#</FORM[^>]*?>#i', '', $new);
		return $new;
	} // end method strip_form_tags

} // end class notebook

?>
