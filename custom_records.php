<?php
 // $Id$
 // $Author$
 // desc: custom patient records engine
 // lic : GPL, v2

$page_name   = "custom_records.php";
$record_name = "Custom Records";
$table_name  = "patrecdata";
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed_open_db ();

// Check for no patient provided
if ($patient<1) {
  $page_title = _($record_name)." :: "._("ERROR");
  $display_buffer .= "
   <P>
   <B>"._("You must select a patient.")."</B>
   <P>
   <CENTER>
    <A HREF=\"patient.php\"
     >"._("Select a Patient")."</A> 
   </CENTER>
   <P>
  ";
  template_display();
} // end checking if patient is provided

$this_patient = CreateObject('FreeMED.Patient', $patient);

if ( (($action=="addform") or ($action=="modform") or
       ($action=="add")     or ($action=="mod"    ))
      AND ($form<1)) {
  $page_title = _($record_name)." :: "._("ERROR");
  $display_buffer .= "
   <P>
   <CENTER>
    <B>"._("You must select a template.")."</B>
   </CENTER>
   <P>
   <CENTER>
   <A HREF=\"manage.php?id=".urlencode($patient)."\"
    >"._("Manage Patient")."</A> 
   </CENTER>
   <P>
  ";
  template_display();
} // end checking for valud form

switch ($action) {
  case "addform":
  case "modform":
   switch($action) {
    case "addform":
     $this_action = "add";
     $form_template = $form;  // we use the provided one when adding.
     break;
    case "modform":
     $this_action = "mod";
     $result = $sql->query ("SELECT * FROM $table_name WHERE id='$id'");
     $r = $sql->fetch_array ($result);
     $form_template = $r["prtemplate"];
     $form = $form_template;   // to allow us to pass it as hidden
     if ($patient != $r["prpatient"]) {
       // FINISH THIS LATER !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
       	die ("access violation");
     } // end if patient does not "own" record
     $prdata = $r["prdata"]; // get the actual data
     $this_data = fm_split_into_array ($prdata);

     $this_template = freemed::get_link_rec($form_template, "patrectemplate");
     $type_n  = fm_split_into_array ($this_template["prtftype"   ]);
     $typefor = fm_split_into_array ($this_template["prtftypefor"]);
     $maxlen  = fm_split_into_array ($this_template["prtfmaxlen" ]);

     for ($j=0;$j<=count($this_data);$j++) {
       if (strstr($maxlen[$j], "2")) {  $leftright = true;  }
        else                         {  $leftright = false; }
       if (($type_n[$j]=="check") and (!empty($typefor[$j]))) {
         ${"answer".$j} = explode (",", $this_data[$j]) ;
       } elseif (($type_n[$j]=="time") and (!empty($this_data[$j]))) {
         list (${"answer".$j."_h"}, ${"answer".$j."_m"}) =
               explode (",", $this_data[$j]) ;
       } elseif ($leftright) {
         list (${"answer".$j."_l"}, ${"answer".$j."_r"}) =
               explode (",", $this_data[$j]) ;
       } else { // regular..
         ${"answer".$j} = $this_data[$j];
       }     
     } // end internal loop
     break;
   } // end interior action switch
   $page_title = (($action=="addform") ? _("Add") : _("Modify")).
     " "._($record_name); 
   $display_buffer .= freemed::patient_box($this_patient)."
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
     <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"".prepare($id)."\">
     <INPUT TYPE=HIDDEN NAME=\"form\"    VALUE=\"".prepare($form)."\">
    <TABLE WIDTH=100% BORDER=0 CELLSPACING=2 CELLPADDING=5
     VALIGN=MIDDLE ALIGN=CENTER>
   ";

   // now, here's the heart of the whole routine...
   $f_r = $sql->fetch_array ($sql->query ("SELECT * FROM patrectemplate
                      WHERE id='".addslashes($form_template)."'"));
   $prtfname    = fm_split_into_array ($f_r["prtfname"   ]);
   $prtftype    = fm_split_into_array ($f_r["prtftype"   ]);
   $prtftypefor = fm_split_into_array ($f_r["prtftypefor"]);
   $prtfmaxlen  = fm_split_into_array ($f_r["prtfmaxlen" ]);
   $prtfcom     = fm_split_into_array ($f_r["prtfcom"    ]);
   $number_of_questions = count($prtfname);

   // function name
   $display_buffer .= "
    <TR BGCOLOR=#000000>
     <TD COLSPAN=2>
      <CENTER>
      <FONT COLOR=#cccccc><B>".prepare($f_r["prtname"])."</B></FONT>
      </CENTER>
     </TD>
    </TR>
   ";

   for ($i=0;$i<$number_of_questions;$i++) {
     $this_question = prepare(chop($prtfname[$i]));
     $this_type     = $prtftype[$i]; // get the type of question
     // begin the row...
     if ($prtftype[$i] != "heading") {
      $display_buffer .= "
       <TR>
       <TD ALIGN=RIGHT>
        <B>$this_question</B>
       </TD>
       <TD ALIGN=LEFT>
      ";
     } else {
      $display_buffer .= "
       <TR>
       <TD ALIGN=CENTER VALIGN=MIDDLE COLSPAN=2 BGCOLOR=#bbbbbb>
      ";
     } // end if/else for heading
     $this_answer = ${"answer".$i};
     $leftright = false;

     // determine if left/right
     if (strstr($prtfmaxlen[$i], "2")) {
       $leftright = true;
       $this_answer_l = ${"answer".$i."_l"};
       $this_answer_r = ${"answer".$i."_r"};
     } // end if left/right

     switch ($this_type) { // generate answer by box...
      case "link":
       // there _has_ to be a better way than this...
       switch ($prtftypefor[$i]) {
        case "cpt":
        case "cptcodes":
	 $cpt_q = "SELECT * FROM cpt ORDER BY cptcode, cptnameint";
	 $cpt_r = $sql->query($cpt_q);
	 $display_buffer .= freemed_display_selectbox(
	   $cpt_q, "#cptcode# (#cptnameint#)", "this_answer");
         break;
        case "cptmod":
	 $cptmod_q = "SELECT * FROM cptmod ORDER BY cptmod, cptmoddescrip";
	 $cptmod_r = $sql->query($cptmod_q);
	 $display_buffer .= freemed_display_selectbox(
	   $cptmod_q, "#cptmod# (#cptmoddescrip#)", "this_answer");
         break;
        case "facilities":
        case "facility":
        case "pos":
	 $fac_q = "SELECT * FROM facility ORDER BY psrname, psrnote";
	 $fac_r = $sql->query($fac_q);
	 $display_buffer .= freemed_display_selectbox(
	   $fac_r, "#psrname# [#psrnote#]", "this_answer");
         break;
        case "frmlry":
        case "drugs":
	 $frm_q = "SELECT * FROM frmlry ORDER BY trdmrkname";
	 $frm_r = $sql->query($frm_q);
	 $display_buffer .= freemed_display_selectbox(
	   $frm_r, "#trdmrkname#", "this_answer");
         break;
        case "doc":
        case "physician":
        case "phy":
	 $doc_q = "SELECT * FROM physician ORDER BY phylname, phyfname";
	 $doc_r = $sql->query($doc_q);
	 $display_buffer .= freemed_display_selectbox(
	   $doc_r, "#phylname#, #phyfname#", "this_answer");
         break;
        default:
         $display_buffer .= "\n<B>NOT IMPLEMENTED!</B>\n";
         break;
       } // end inner switch
       break;
      case "multi":
       $display_buffer .= "\nNOT IMPLEMENTED YET!\n";
       break;
      case "number": // range of numbers, selectable
       list ($lowerlimit, $upperlimit, $step) = explode (",", $prtftypefor[$i]);
       if (!$leftright) {
         fm_number_select ("answer$i", $lowerlimit, $upperlimit, $step);
       } else {
         $display_buffer .= "<B>L</B>&nbsp;";
         fm_number_select ("answer".$i."_l", $lowerlimit, $upperlimit, $step);
         $display_buffer .= "&nbsp;<B>R</B>&nbsp;";
         fm_number_select ("answer".$i."_r", $lowerlimit, $upperlimit, $step);
       }
       break;
      case "time":
       fm_number_select ("answer".$i."_h", 0, 23, 1, true);
       $display_buffer .= " <B>:</B> \n";
       fm_number_select ("answer".$i."_m", 0, 59, 5, true);
       break;
      case "date":
       if (empty($this_answer))                  // if nothing is provided...
        ${"answer".$i} = $cur_date ;             // ... give the current date
       // quick and dirty patch to allow locking of dates...
       if (($action=="modform") and (strstr($prtfmaxlen[$i],"L"))) {
        $this_y = substr($this_answer, 0, 4);
        $this_m = substr($this_answer, 5, 2);
        $this_d = substr($this_answer, 8, 2);
        $display_buffer .= "
         <I>".prepare($this_answer)."</I>
         <INPUT TYPE=HIDDEN NAME=\"answer".$i."_y\" VALUE=\"$this_y\">
         <INPUT TYPE=HIDDEN NAME=\"answer".$i."_m\" VALUE=\"$this_m\">
         <INPUT TYPE=HIDDEN NAME=\"answer".$i."_d\" VALUE=\"$this_d\">
        ";
       } else { $display_buffer .= fm_date_entry("answer$i"); }
       break;
      case "select":
       $options = explode(",", $prtftypefor[$i]); // get options
       if (count($options)<1) { $display_buffer .= _("ERROR")."\n"; break; }
       if (!$leftright) {
        $display_buffer .= "\n<SELECT NAME=\"answer$i\">\n";
        for ($each_option=0;$each_option<count($options);$each_option++) {
          $options[$each_options] = chop($option[$each_option]);
          if ($this_answer==$options[$each_option]) { $select = "SELECTED"; }
           else                                     { $select = "";         }
          if (!empty($options[$each_option]))
          $display_buffer .= "<OPTION VALUE=\"".$options[$each_option]."\" $select>".
              $options[$each_option]."\n"; // display the option
        } // end for
        $display_buffer .= "\n</SELECT>\n";
       } else { // if it _is_ left&right
        $display_buffer .= "\n<B>L</B>&nbsp;<SELECT NAME=\"answer".$i."_l\">\n";
        for ($each_option=0;$each_option<count($options);$each_option++) {
          $options[$each_option] = chop($options[$each_option]);
          if ($this_answer_l==$options[$each_option]) { $select = "SELECTED"; }
           else                                       { $select = "";         }
          if (!empty($options[$each_option]))
           $display_buffer .= "<OPTION VALUE=\"".$options[$each_option]."\" $select>".
                 $options[$each_option]."\n"; // display the option
        } // end for
        $display_buffer .= "\n</SELECT>\n";
        $display_buffer .= "\n&nbsp;<B>R</B>&nbsp;<SELECT NAME=\"answer".$i."_r\">\n";
        for ($each_option=0;$each_option<count($options);$each_option++) {
          $options[$each_option] = chop($options[$each_option]);
          if ($this_answer_r==$options[$each_option]) { $select = "SELECTED"; }
           else                                       { $select = "";         }
          if (!empty($options[$each_option]))
           $display_buffer .= "<OPTION VALUE=\"".$options[$each_option]."\" $select>".
                 $options[$each_option]."\n"; // display the option
        } // end for
        $display_buffer .= "\n</SELECT>\n";
       } // end left/right clause
       break;
      case "text":
       if (strstr($prtfmaxlen[$i], ",")) { // check for a comma delimited len
         list ($size, $maxlength) = explode (",", $prtfmaxlen[$i]);
         $size += 0; $maxlength += 0;      // convert to numbers
       } else { // calculate if none given
         $size      = $prtfmaxlen[$i] + 1; // convert to number, KFM +1 fix
         $maxlength = $size - 1;           // recalc maximum length
       }
       $display_buffer .= "<INPUT TYPE=TEXT NAME=\"answer$i\" SIZE=$size MAXLENGTH=$maxlength
              VALUE=\"".prepare($this_answer)."\">\n";
       break;
      case "phone":
       $display_buffer .= fm_phone_entry ("answer$i");
       break;
      case "heading":
       $display_buffer .= "
        ".prepare($prtfname[$i])."
        <INPUT TYPE=HIDDEN NAME=\"answer$i\" VALUE=\"\">
       ";
       break;
      case "check": // checkbox
       if (empty($prtftypefor[$i])) {
         if (strtolower($this_answer)=="on") { $this_checked = "CHECKED"; }
          else                               { $this_checked = "";        }
         $display_buffer .= "<INPUT TYPE=CHECKBOX NAME=\"answer$i\" $this_checked>\n";
       } else { // if it is _not_ empty...
         $params = explode (",", $prtftypefor[$i]);
         for ($cb=0;$cb<count($params);$cb++) {
           $params[$cb] = trim($params[$cb]);
           if (fm_value_in_array($this_answer, $params[$cb]))
                                             { $this_checked = "CHECKED"; }
           else                              { $this_checked = "";        }
           $display_buffer .= "<INPUT TYPE=CHECKBOX NAME=\"answer$i$brackets\" ".
                "VALUE=\"".$params[$cb]."\" $this_checked>".$params[$cb].
                "&nbsp;\n";
         } // end of "for" loop 
       } // end of checking for formatting field empty
       break;
      default: // cheap cop out on default value
       $display_buffer .= "&nbsp;\n"; 
       break;
     } // end switch for this_type
     // end the row...
     $display_buffer .= "
       </TD>
      </TR>
     "; 
   } // end $i for loop (loop for display questions

   $display_buffer .= "
     </TABLE>
     <P>
     <CENTER>
     <SELECT NAME=\"action\">
      <OPTION VALUE=\"".( ($action=="addform") ? "add" : "mod" )."\">".
        ( ($action=="addform") ? _("Add") : _("Modify") )."
      <OPTION VALUE=\"\">"._("back")."
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">
     </CENTER>
    </FORM>
    <P>
   ";
   break; // end add/modform

  case "add":
  case "mod":
   // first compact the record...
   $form_template = freemed::get_link_rec ($form, "patrectemplate");
   $form_length = count(fm_split_into_array($form_template["prtfname"]));
   $prtftype    = fm_split_into_array($form_template["prtftype"]);
   $prtfmaxlen  = fm_split_into_array($form_template["prtfmaxlen"]);

   $current_form = array ();          // clear current form array
   for ($i=0;$i<=$form_length;$i++) { // loop for each element
    if (strstr($prtfmaxlen[$i], "2")) { $leftright = true;  }
     else                             { $leftright = false; }
    switch ($prtftype[$i]) { // do different things with different types
     case "date":
      $current_form[$i] = fm_date_assemble("answer$i");
      break;
     case "phone":
      $current_form[$i] = fm_phone_assemble("answer$i");
      break;
     case "check":
      if (is_array (${"answer".$i})) { 
        $current_form["$i"] = implode (",", ${"answer".$i}) ;
      } else {
        $current_form["$i"] = ${"answer".$i} ;
      }
      break;
     case "time":
      $current_form["$i"] = trim (${"answer".$i."_h"}) .
        trim (${"answer".$i."_m"}) ;
      break;
     default:
      if (!$leftright) { $current_form["$i"] = ${"answer".$i}; }
       else {
         $current_form["$i"] = ${"answer".$i."_l"} . "," . 
                                        ${"answer".$i."_r"} ;
       } // end of checking for left/right
      break;
    } // end inner switch
   } // end of for each element loop

   // now squash current_form
   $this_data = fm_join_from_array($current_form);

   // do action specific things
   switch ($action) {
     case "add":
      $query = "INSERT INTO $table_name VALUES (
                '".addslashes($patient).  "',
                '".addslashes($form).     "',
                '$cur_date',
                '',
                '".addslashes($this_data)."',
                NULL )";
      break;
     case "mod":
      $query = "UPDATE $table_name SET
                prpatient  = '".addslashes($patient)  ."',
                prtemplate = '".addslashes($form)     ."',
                prdtmod    = '$cur_date',
                prdata     = '".addslashes($this_data)."'
                WHERE   id = '$id'";
      break;
   } // end inner action switch 
   $page_title =  ( ($action=="add") ? _("Adding") : _("Modifying")).
     " "._($record_name);
   $display_buffer .= "
     <P><CENTER>
     ".( ($action=="add") ? _("Adding") : _("Modifying") )." ... 
    ";
   if ($debug)    $display_buffer .= "<BR>(query = \"$query\")<BR>\n";
   $result = $sql->query ($query); // send the prepared query through
   if ($result) { $display_buffer .= _("done").".\n"; }
    else        { $display_buffer .= _("ERROR")."\n"; }
   $display_buffer .= "
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"manage.php?id=".urlencode($patient)."\"
      >"._("Manage Patient")."</A> | 
     <A HREF=\"$page_name?patient=".urlencode($patient)."\"
      >"._("View/Modify")." "._($record_name)."</A>
    </CENTER>
    <P>
    ";
   break;

  default: // default view is listing...
   $page_title = _($record_name);
   $result = $sql->query ("SELECT * FROM $table_name
                         WHERE prpatient='".addslashes($patient)."'
                         ORDER BY prdtadd DESC");
   if (($result==0) or ($sql->num_rows($result)<1)) {
     $display_buffer .= freemed::patient_box($this_patient)."
      <P>
      <CENTER>
       <B>"._("No records for this patient.")."</B>
      </CENTER>
      <P>
      <CENTER>
      <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"addform\">
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
       <SELECT NAME=\"form\">
        <OPTION VALUE=\"\">"._("NONE SELECTED")."
     ";
     $f_result = $sql->query ("SELECT * FROM patrectemplate
                             ORDER BY prtname");
     while ($f_r = $sql->fetch_array ($f_result)) {
       $display_buffer .= "<OPTION VALUE=\"".$f_r["id"]."\">".$f_r["prtname"]."\n";
     } // end of this internal loop
     $display_buffer .= "
       </SELECT>
       <INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
      </FORM>
      </CENTER>
      <P>
      <CENTER>
       <A HREF=\"manage.php?id=$patient\"
        >"._("Manage Patient")."</A> |
       <A HREF=\"main.php\"
        >"._("Return to the Main Menu")."</A>
      </CENTER>
      <P>
      ";
     template_display();
   } // end checking if no result
   $display_buffer .= freemed::patient_box($this_patient)."
     <P>
     <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0
      VALIGN=MIDDLE ALIGN=CENTER> 
      <TR CLASS=\"reverse\">
       <TD><b>"._("Date Added")."</b></TD>
       <TD><b>"._("Form")."</b></TD>
       <TD><b>"._("Action")."</b></TD>
      </TR>
    ";
     $dtadd    = $r["prdtadd"   ];
     $form_template = $r["prtemplate"];
     $id       = $r["id"        ];
     $formname = freemed::get_link_field ($form_template, "patrectemplate",
                                         "prtname");
     $display_buffer .= "
      <TR CLASS=\"".freemed_alternate()."\">
       <TD>$dtadd</TD>
       <TD>".prepare($formname)."</TD>
       <TD>
      ";
     if (0==0)
      $display_buffer .= "
       <A HREF=\"$page_name?id=$id&patient=$patient&".
        "form=$form_template&action=modform\"
       ><FONT SIZE=-1>"._("MOD")."</FONT></A>
      ";

     if (freemed::user_flag(USER_DELETE))
      $display_buffer .= "
       <A HREF=\"$page_name?id=$id&patient=$patient&action=del\"
       ><FONT SIZE=-1>"._("DEL")."</FONT></A>
      ";

     $display_buffer .= "
       &nbsp;
       </TD> 
      </TR>
      ";
   } // end while loop
   $display_buffer .= "
    </TABLE>
    <P>
    <CENTER>
     <A HREF=\"manage.php?id=$patient\"
     >"._("Manage Patient")."</A> |
     <A HREF=\"main.php\"
     >"._("Return to the Main Menu")."</A>
    </CENTER>
    <P>
    "; // end table
   break;
} // end master action switch

//----- Display page template
template_display();

?>
