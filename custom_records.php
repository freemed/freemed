<?php
 // $Id$
 // desc: custom patient records engine
 // lic : GPL, v2

 $page_name   = "custom_records.php";
 $record_name = "Custom Records";
 $table_name  = "patrecdata";

 include ("lib/freemed.php");
 include ("lib/API.php");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

 if ($patient<1) {
  freemed_display_box_top (_($record_name)." :: "._("ERROR"));
  echo "
   <P>
   <B>"._("You must select a patient.")."</B>
   <P>
   <CENTER>
    <A HREF=\"patient.php?$_auth\"
     ><$STDFONT_B>"._("Select a Patient")."<$STDFONT_E></A> 
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE("");
 } // end checking if patient is provided

 $this_patient = new Patient ($patient);

 if ( (($action=="addform") or ($action=="modform") or
       ($action=="add")     or ($action=="mod"    ))
      AND ($form<1)) {
  freemed_display_box_top (_($record_name)." :: "._("ERROR"));
  echo "
   <P>
   <CENTER>
    <B><$STDFONT_B>"._("You must select a template.")."<$STDFONT_E></B>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  freemed_close_db ();
  freemed_display_html_bottom ();
  DIE("");
 } // end checking for valud form

 switch ($action) {
  case "addform":
  case "modform":
   switch($action) {
    case "addform":
     $this_action = "add";
     $template = $form;  // we use the provided one when adding.
     break;
    case "modform":
     $this_action = "mod";
     $result = $sql->query ("SELECT * FROM $table_name WHERE id='$id'");
     $r = $sql->fetch_array ($result);
     $template = $r["prtemplate"];
     $form = $template;   // to allow us to pass it as hidden
     if ($patient != $r["prpatient"]) {
       // FINISH THIS LATER !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     } // end if patient does not "own" record
     $prdata = $r["prdata"]; // get the actual data
     $this_data = fm_split_into_array ($prdata);

     $this_template = freemed_get_link_rec($template, "patrectemplate");
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
   freemed_display_box_top ( (($action=="addform") ? _("Add") : _("Modify")).
     " "._($record_name)); 
   echo freemed_patient_box($this_patient)."
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
                      WHERE id='$template'"));
   $prtfname    = fm_split_into_array ($f_r["prtfname"   ]);
   $prtftype    = fm_split_into_array ($f_r["prtftype"   ]);
   $prtftypefor = fm_split_into_array ($f_r["prtftypefor"]);
   $prtfmaxlen  = fm_split_into_array ($f_r["prtfmaxlen" ]);
   $prtfcom     = fm_split_into_array ($f_r["prtfcom"    ]);
   $number_of_questions = count($prtfname);

   // function name
   echo "
    <TR BGCOLOR=#000000>
     <TD COLSPAN=2>
      <CENTER>
      <$STDFONT_B COLOR=#cccccc><B>".prepare($f_r["prtname"])."</B><$STDFONT_E>
      </CENTER>
     </TD>
    </TR>
   ";

   for ($i=0;$i<$number_of_questions;$i++) {
     $this_question = prepare(chop($prtfname[$i]));
     $this_type     = $prtftype[$i]; // get the type of question
     // begin the row...
     if ($prtftype[$i] != "heading") {
      echo "
       <TR>
       <TD ALIGN=RIGHT>
        <B><$STDFONT_B>$this_question<$STDFONT_E></B>
       </TD>
       <TD ALIGN=LEFT>
      ";
     } else {
      echo "
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
	 echo freemed_display_selectbox(
	   $cpt_q, "#cptcode# (#cptnameint#)", "this_answer");
         break;
        case "cptmod":
	 $cptmod_q = "SELECT * FROM cptmod ORDER BY cptmod, cptmoddescrip";
	 $cptmod_r = $sql->query($cptmod_q);
	 echo freemed_display_selectbox(
	   $cptmod_q, "#cptmod# (#cptmoddescrip#)", "this_answer");
         break;
        case "facilities":
        case "facility":
        case "pos":
	 $fac_q = "SELECT * FROM facility ORDER BY psrname, psrnote";
	 $fac_r = $sql->query($fac_q);
	 echo freemed_display_selectbox(
	   $fac_r, "#psrname# [#psrnote#]", "this_answer");
         break;
        case "frmlry":
        case "drugs":
	 $frm_q = "SELECT * FROM frmlry ORDER BY trdmrkname";
	 $frm_r = $sql->query($frm_q);
	 echo freemed_display_selectbox(
	   $frm_r, "#trdmrkname#", "this_answer");
         break;
        case "doc":
        case "physician":
        case "phy":
	 $doc_q = "SELECT * FROM physician ORDER BY phylname, phyfname";
	 $doc_r = $sql->query($doc_q);
	 echo freemed_display_selectbox(
	   $doc_r, "#phylname#, #phyfname#", "this_answer");
         break;
        default:
         echo "\n<B>NOT IMPLEMENTED!</B>\n";
         break;
       } // end inner switch
       break;
      case "multi":
       echo "\nNOT IMPLEMENTED YET!\n";
       break;
      case "number": // range of numbers, selectable
       list ($lowerlimit, $upperlimit, $step) = explode (",", $prtftypefor[$i]);
       if (!$leftright) {
         fm_number_select ("answer$i", $lowerlimit, $upperlimit, $step);
       } else {
         echo "<B>L</B>&nbsp;";
         fm_number_select ("answer".$i."_l", $lowerlimit, $upperlimit, $step);
         echo "&nbsp;<B>R</B>&nbsp;";
         fm_number_select ("answer".$i."_r", $lowerlimit, $upperlimit, $step);
       }
       break;
      case "time":
       fm_number_select ("answer".$i."_h", 0, 23, 1, true);
       echo " <B>:</B> \n";
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
        echo "
         <I>".prepare($this_answer)."</I>
         <INPUT TYPE=HIDDEN NAME=\"answer".$i."_y\" VALUE=\"$this_y\">
         <INPUT TYPE=HIDDEN NAME=\"answer".$i."_m\" VALUE=\"$this_m\">
         <INPUT TYPE=HIDDEN NAME=\"answer".$i."_d\" VALUE=\"$this_d\">
        ";
       } else { echo fm_date_entry("answer$i"); }
       break;
      case "select":
       $options = explode(",", $prtftypefor[$i]); // get options
       if (count($options)<1) { echo _("ERROR")."\n"; break; }
       if (!$leftright) {
        echo "\n<SELECT NAME=\"answer$i\">\n";
        for ($each_option=0;$each_option<count($options);$each_option++) {
          $options[$each_options] = chop($option[$each_option]);
          if ($this_answer==$options[$each_option]) { $select = "SELECTED"; }
           else                                     { $select = "";         }
          if (!empty($options[$each_option]))
          echo "<OPTION VALUE=\"".$options[$each_option]."\" $select>".
              $options[$each_option]."\n"; // display the option
        } // end for
        echo "\n</SELECT>\n";
       } else { // if it _is_ left&right
        echo "\n<B>L</B>&nbsp;<SELECT NAME=\"answer".$i."_l\">\n";
        for ($each_option=0;$each_option<count($options);$each_option++) {
          $options[$each_option] = chop($options[$each_option]);
          if ($this_answer_l==$options[$each_option]) { $select = "SELECTED"; }
           else                                       { $select = "";         }
          if (!empty($options[$each_option]))
           echo "<OPTION VALUE=\"".$options[$each_option]."\" $select>".
                 $options[$each_option]."\n"; // display the option
        } // end for
        echo "\n</SELECT>\n";
        echo "\n&nbsp;<B>R</B>&nbsp;<SELECT NAME=\"answer".$i."_r\">\n";
        for ($each_option=0;$each_option<count($options);$each_option++) {
          $options[$each_option] = chop($options[$each_option]);
          if ($this_answer_r==$options[$each_option]) { $select = "SELECTED"; }
           else                                       { $select = "";         }
          if (!empty($options[$each_option]))
           echo "<OPTION VALUE=\"".$options[$each_option]."\" $select>".
                 $options[$each_option]."\n"; // display the option
        } // end for
        echo "\n</SELECT>\n";
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
       echo "<INPUT TYPE=TEXT NAME=\"answer$i\" SIZE=$size MAXLENGTH=$maxlength
              VALUE=\"".prepare($this_answer)."\">\n";
       break;
      case "phone":
       echo fm_phone_entry ("answer$i");
       break;
      case "heading":
       echo "
        <$HEADERFONT_B>".prepare($prtfname[$i])."<$HEADERFONT_E>
        <INPUT TYPE=HIDDEN NAME=\"answer$i\" VALUE=\"\">
       ";
       break;
      case "check": // checkbox
       if (empty($prtftypefor[$i])) {
         if (strtolower($this_answer)=="on") { $this_checked = "CHECKED"; }
          else                               { $this_checked = "";        }
         echo "<INPUT TYPE=CHECKBOX NAME=\"answer$i\" $this_checked>\n";
       } else { // if it is _not_ empty...
         $params = explode (",", $prtftypefor[$i]);
         for ($cb=0;$cb<count($params);$cb++) {
           $params[$cb] = trim($params[$cb]);
           if (fm_value_in_array($this_answer, $params[$cb]))
                                             { $this_checked = "CHECKED"; }
           else                              { $this_checked = "";        }
           echo "<INPUT TYPE=CHECKBOX NAME=\"answer$i$brackets\" ".
                "VALUE=\"".$params[$cb]."\" $this_checked>".$params[$cb].
                "&nbsp;\n";
         } // end of "for" loop 
       } // end of checking for formatting field empty
       break;
      default: // cheap cop out on default value
       echo "&nbsp;\n"; 
       break;
     } // end switch for this_type
     // end the row...
     echo "
       </TD>
      </TR>
     "; 
   } // end $i for loop (loop for display questions

   echo "
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
   freemed_display_box_bottom ();
   break; // end add/modform

  case "add":
  case "mod":
   // first compact the record...
   $template    = freemed_get_link_rec ($form, "patrectemplate");
   $form_length = count(fm_split_into_array($template["prtfname"]));
   $prtftype    = fm_split_into_array($template["prtftype"]);
   $prtfmaxlen  = fm_split_into_array($template["prtfmaxlen"]);

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
   freemed_display_box_top (( ($action=="add") ? _("Adding") : _("Modifying")).
     " "._($record_name));
   echo "
     <P><CENTER>
     <$STDFONT_B>".( ($action=="add") ? _("Adding") : _("Modifying") )." ... 
    ";
   if ($debug)    echo "<BR>(query = \"$query\")<BR>\n";
   $result = $sql->query ($query); // send the prepared query through
   if ($result) { echo _("done").".\n"; }
    else        { echo _("ERROR")."\n"; }
   echo "
    </CENTER><$STDFONT_E>
    <P>
    <CENTER>
     <A HREF=\"manage.php?$_auth&id=$patient\"
      ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> | 
     <A HREF=\"$page_name?$_auth&patient=$patient\"
      ><$STDFONT_B>"._("View/Modify")." "._($record_name)."<$STDFONT_E></A>
    </CENTER>
    <P>
    ";
   freemed_display_box_bottom ();
   break;

  default: // default view is listing...
   freemed_display_box_top (_($record_name));
   $result = $sql->query ("SELECT * FROM $table_name
                         WHERE prpatient='".addslashes($patient)."'
                         ORDER BY prdtadd DESC");
   if (($result==0) or ($sql->num_rows($result)<1)) {
     echo "
      ".freemed_patient_box($this_patient)."
      <P>
      <CENTER>
       <B><$STDFONT_B>"._("No records for this patient.")."<$STDFONT_E></B>
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
       echo "<OPTION VALUE=\"".$f_r["id"]."\">".$f_r["prtname"]."\n";
     } // end of this internal loop
     echo "
       </SELECT>
       <INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
      </FORM>
      </CENTER>
      <P>
      <CENTER>
       <A HREF=\"manage.php?$_auth&id=$patient\"
        ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> |
       <A HREF=\"main.php?$_auth\"
        ><$STDFONT_B>"._("Return to the Main Menu")."<$STDFONT_E></A>
      </CENTER>
      <P>
      ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE("");
   } // end checking if no result
   echo "
     ".freemed_patient_box($this_patient)."
     <P>
     <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0
      VALIGN=MIDDLE ALIGN=CENTER> 
      <TR BGCOLOR=#000000>
       <TD><$STDFONT_B COLOR=#ffffff>"._("Date Added")."<$STDFONT_E></TD>
       <TD><$STDFONT_B COLOR=#ffffff>"._("Form")."<$STDFONT_E></TD>
       <TD><$STDFONT_B COLOR=#ffffff>"._("Action")."<$STDFONT_E></TD>
      </TR>
    ";
   $_alternate = freemed_bar_alternate_color ($_alternate);
   while ($r = $sql->fetch_array ($result)) {
     $dtadd    = $r["prdtadd"   ];
     $template = $r["prtemplate"];
     $id       = $r["id"        ];
     $formname = freemed_get_link_field ($template, "patrectemplate",
                                         "prtname");
     $_alternate = freemed_bar_alternate_color ($_alternate);
     echo "
      <TR BGCOLOR=$_alternate>
       <TD><$STDFONT_B>$dtadd<$STDFONT_E></TD>
       <TD><$STDFONT_B>".prepare($formname)."<$STDFONT_E></TD>
       <TD>
      ";
     if (0==0)
      echo "
       <A HREF=\"$page_name?$_auth&id=$id&patient=$patient&".
        "form=$template&action=modform\"
       ><FONT SIZE=-1>"._("MOD")."</FONT></A>
      ";

     if (freemed_get_userlevel($LoginCookie)>$delete_level)
      echo "
       <A HREF=\"$page_name?$_auth&id=$id&patient=$patient&action=del\"
       ><FONT SIZE=-1>"._("DEL")."</FONT></A>
      ";

     echo "
       &nbsp;
       </TD> 
      </TR>
      ";
   } // end while loop
   echo "
    </TABLE>
    <P>
    <CENTER>
     <A HREF=\"manage.php?$_auth&id=$patient\"
     ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A> |
     <A HREF=\"main.php?$_auth\"
     ><$STDFONT_B>"._("Return to the Main Menu")."<$STDFONT_E></A>
    </CENTER>
    <P>
    "; // end table
   freemed_display_box_bottom ();
   break;
 } // end master action switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
