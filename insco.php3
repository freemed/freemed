<?php
  # file: insco.php3
  # note: insurance company database services
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL, v2

  $page_name   = "insco.php3";
  $record_name = "Insurance Company";
  $db_name     = "insco";

  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top();
  freemed_display_banner();

if ($action=="addform") {
  
  freemed_display_box_top ("$Add $record_name", $page_name);
  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 

    <$STDFONT_B>Company Name (full) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"insconame\" SIZE=20 MAXLENGTH=50
     VALUE=\"$insconame\">
    <BR>

    <$STDFONT_B>Company Name (on forms) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscoalias\" SIZE=20 MAXLENGTH=30
     VALUE=\"$inscoalias\">
    <BR>

    <$STDFONT_B>Address Line 1<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoaddr1 SIZE=30 MAXLENGTH=30
     VALUE=\"$inscoaddr1\">
    <BR>
    <$STDFONT_B>Address Line 2<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoaddr2 SIZE=30 MAXLENGTH=30
     VALUE=\"$inscoaddr2\">
    <BR>
    <$STDFONT_B>City<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscocity SIZE=20 MAXLENGTH=20
     VALUE=\"$inscocity\">
    <BR>
    <$STDFONT_B>State<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscostate SIZE=4 MAXLENGTH=3
     VALUE=\"$inscostate\">
    <BR>
    <$STDFONT_B>Zip/Postal Code<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscozip SIZE=10 MAXLENGTH=10
     VALUE=\"$inscozip\">
    <BR>
    <$STDFONT_B>Phone #<$STDFONT_E>
  ";
  fm_phone_entry ("inscophone");
  echo "
    <BR>
    <$STDFONT_B>Facsimile #<$STDFONT_E>
  ";
  fm_phone_entry ("inscofax");
  echo "
    <BR>

    <$STDFONT_B>Email Address<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoemail SIZE=20 MAXLENGTH=50
     VALUE=\"$inscoemail\">
    <BR>

    <$STDFONT_B>Web Site (<I>http://insco.com</I>)<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscowebsite SIZE=15 MAXLENGTH=100
     VALUE=\"$inscowebsite\">
    <BR>
  
    <$STDFONT_B>NEIC ID #<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoid SIZE=11 MAXLENGTH=10
     VALUE=\"$inscoid\">
    <BR>

    <$STDFONT_B>Insurance Group : <$STDFONT_E>
    <SELECT NAME=\"inscogroup\">
  ";

  freemed_display_inscogroups($inscogroup);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Insurance Type :<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscotype\" SIZE=10
     MAXLENGTH=30>
    <BR> 

    <$STDFONT_B>Insurance Assign? :<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscoassign\" SIZE=10
     MAXLENGTH=12>
    <BR>
 
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" Add \">
    <INPUT TYPE=RESET  VALUE=\"Clear\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom();

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >Abandon Addition</A>
    </CENTER>
  "; // abandon addition

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  $inscodtadd = $cur_date; // set date added to current
  $inscodtmod = $cur_date; // set date modified to current

  $query = "INSERT INTO $database.$db_name VALUES ( ".
    "'$inscodtadd',     ".
    "'$inscodtmod',     ".
    "'".addslashes($insconame)."',          ".
    "'".addslashes($inscoalias)."',         ".
    "'".addslashes($inscoaddr1)."',         ".
    "'".addslashes($inscoaddr2)."',         ".
    "'".addslashes($inscocity)."',          ".
    "'".addslashes($inscostate)."',         ".
    "'".addslashes($inscozip)."',           ".
    "'".fm_phone_assemble("inscophone")."', ".
    "'".fm_phone_assemble("inscofax")."',   ".
    "'".addslashes($inscocontact)."',       ".
    "'".addslashes($inscoid)."',            ".
    "'".addslashes($inscowebsite)."',       ".
    "'".addslashes($inscoemail)."',         ".
    "'".addslashes($inscogroup)."',         ".
    "'".addslashes($inscotype)."',          ".
    "'".addslashes($inscoassign)."',        ".
    "'".addslashes(fm_join_from_array($inscomod))."', ".
    " NULL ) ";

  $result = fdb_query($query);
  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo "$result";
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>done.</B></$STDFONT_B>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  }

  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\">Return
     to the Insurance Company Menu</A>
    </CENTER>
  ";

} elseif ($action=="modform") {

  freemed_display_box_top("Modify Insurance Company", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY someone!</B>
     </CENTER>

     <P>
    ";

    if ($debug)
      echo "
        ID = [<B>$id</B>]<P>
      ";

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >Return to the Main Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, $db_name);

  $inscodtadd   = $r["inscodtadd"  ];
  $inscodtmod   = $r["inscodtmod"  ];
  $insconame    = fm_prep($r["insconame"   ]);
  $inscoalias   = fm_prep($r["inscoalias"  ]);
  $inscoaddr1   = fm_prep($r["inscoaddr1"  ]);
  $inscoaddr2   = fm_prep($r["inscoaddr2"  ]);
  $inscocity    = fm_prep($r["inscocity"   ]);
  $inscostate   = fm_prep($r["inscostate"  ]);
  $inscozip     = fm_prep($r["inscozip"    ]);
  $inscophone   = $r["inscophone"  ];
  $inscofax     = $r["inscofax"    ];
  $inscocontact = $r["inscocontact"];
  $inscoid      = fm_prep($r["inscoid"     ]);
  $inscowebsite = fm_prep($r["inscowebsite"]);
  $inscoemail   = fm_prep($r["inscoemail"  ]);
  $inscogroup   = $r["inscogroup"  ];
  $inscotype    = $r["inscotype"   ];
  $inscoassign  = $r["inscoassign" ];
  $inscomod     = $r["inscomod"    ];
  $id           = $r["id"          ];

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\">

    <$STDFONT_B>Company Name (full) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"insconame\" SIZE=20 MAXLENGTH=50
     VALUE=\"$insconame\">
    <BR>

    <$STDFONT_B>Company Name (on forms) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscoalias\" SIZE=20 MAXLENGTH=30
     VALUE=\"$inscoalias\">
    <BR>

    <$STDFONT_B>Address Line 1 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoaddr1 SIZE=30 MAXLENGTH=30
     VALUE=\"$inscoaddr1\">
    <BR>
    <$STDFONT_B>Address Line 2 : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoaddr2 SIZE=30 MAXLENGTH=30
     VALUE=\"$inscoaddr2\">
    <BR>
    <$STDFONT_B>City : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscocity SIZE=20 MAXLENGTH=20
     VALUE=\"$inscocity\">
    <BR>
    <$STDFONT_B>State : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscostate SIZE=4 MAXLENGTH=3
     VALUE=\"$inscostate\">
    <BR>
    <$STDFONT_B>Zip/Postal Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscozip SIZE=10 MAXLENGTH=10
     VALUE=\"$inscozip\">
    <BR>
    <$STDFONT_B>Phone # : <$STDFONT_E>
  ";
  fm_phone_entry ("inscophone");
  echo "
    <BR>
    <$STDFONT_B>Facsimile # : <$STDFONT_E>
  ";
  fm_phone_entry ("inscofax");
  echo "
    <BR>

    <$STDFONT_B>Email Address : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoemail SIZE=20 MAXLENGTH=50
     VALUE=\"$inscoemail\">
    <BR>

    <$STDFONT_B>Web Site : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscowebsite SIZE=15 MAXLENGTH=100
     VALUE=\"$inscowebsite\">
    <BR>
  
    <$STDFONT_B>NEIC ID # : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=inscoid SIZE=11 MAXLENGTH=10
     VALUE=\"$inscoid\">
    <BR>

    <$STDFONT_B>Insurance Group : <$STDFONT_E>
    <SELECT NAME=\"inscogroup\">
  ";

  freemed_display_inscogroups($inscogroup);

  echo "
    </SELECT>
    <BR>

    <$STDFONT_B>Insurance Type : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscotype\" SIZE=10
     MAXLENGTH=30>
    <BR> 

    <$STDFONT_B>Insurance Assign? : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscoassign\" SIZE=10
     MAXLENGTH=12>
    <BR>

    <$STDFONT_B>Modifiers : <$STDFONT_E>
  ";
  freemed_multiple_choice ("SELECT * FROM $database.insmod
    ORDER BY insmoddesc", "insmoddesc", "inscomod",
    $inscomod, false);
  echo "
    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" Update \">
    <INPUT TYPE=RESET  VALUE=\"Clear\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();
  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >Abandon Modification</A>
    </CENTER>
  "; // abandon modification

} elseif ($action=="mod") {

  freemed_display_box_top("Modifying Insurance Company", $page_name);

  echo "
    <P>
    <$STDFONT_B>Modifying . . . 
  ";

  $inscodtmod = $cur_date; // set date modified to current

  $query = "UPDATE $database.$db_name SET ".
    "inscodtmod   ='$inscodtmod',   ".
    "insconame    ='$insconame',    ".
    "inscoalias   ='$inscoalias',   ".
    "inscoaddr1   ='$inscoaddr1',   ".
    "inscoaddr2   ='$inscoaddr2',   ".
    "inscocity    ='$inscocity',    ".
    "inscostate   ='$inscostate',   ".
    "inscozip     ='$inscozip',     ".
    "inscophone   ='$inscophone',   ".
    "inscofax     ='$inscofax',     ".
    "inscocontact ='$inscocontact', ".
    "inscoid      ='$inscoid',      ".
    "inscowebsite ='$inscowebsite', ".
    "inscoemail   ='$inscoemail',   ".
    "inscogroup   ='$inscogroup',   ".
    "inscotype    ='$inscotype',    ".
    "inscoassign  ='$inscoassign',  ".
    "inscomod     ='".addslashes(fm_join_from_array($inscomod))."'  ". 
    "WHERE id='$id'";

  $result = fdb_query($query);
  if ($debug) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>done.</B></$STDFONT_B>
    ";
  } else {
    echo ("<B>ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\">Return
     to the Insurance Company Menu</A>
    </CENTER>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="del") {

  freemed_display_box_top("Deleted Insurance Company", $page_name);

  $result = fdb_query("DELETE FROM $database.$db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>Insurance Company $id deleted<I>.
  ";
  if ($debug) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >Delete Another</A></CENTER>
  ";

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\">Return
     to the Insurance Company Menu</A>
    </CENTER>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="searchform") {

  freemed_display_box_top("Insurance Company Search", $page_name);
  echo "
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <CENTER><$STDFONT_B><B>INSURANCE COMPANY SEARCH</B><$STDFONT_E></CENTER>
    <P>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"search\">
 
    <$STDFONT_B>Last name: <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"phylname\" VALUE=\"$phylname\">
    <BR>

    <$STDFONT_B>First name: <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"phyfname\" VALUE=\"$phyfname\">
    <BR>

    <BR>
    <INPUT TYPE=SUBMIT VALUE=\" Search \">
    <INPUT TYPE=SUBMIT VALUE=\" Clear \">
    </FORM>
  ";
  freemed_display_box_bottom ();
  
} elseif ($action=="show") {

  // pull up an insurance company record (for view or
  // printout, not modification)

  freemed_display_box_top("Insurance Company Details", $page_name);

  if (strlen($id)<1) {
    echo "

     <CENTER>
      <B>You must specify an id #!</B>
      <BR><BR>
      <A HREF=\"$page_name?$_auth&action=view\"
       >Return to the Insurance Company Menu</A>
     </CENTER>

     <BR><BR>
    ";

    if ($debug)
      echo "
        ID = [<B>$id</B>]<P>
      ";

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >Return to the Main Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, $db_name);

  $inscodtadd   = $r["inscodtadd"  ];
  $inscodtmod   = $r["inscodtmod"  ];
  $insconame    = $r["insconame"   ];
  $inscoalias   = $r["inscoalias"  ];
  $inscoaddr1   = $r["inscoaddr1"  ];
  $inscoaddr2   = $r["inscoaddr2"  ];
  $inscocity    = $r["inscocity"   ];
  $inscostate   = $r["inscostate"  ];
  $inscozip     = $r["inscozip"    ];
  $inscophone   = $r["inscophone"  ];
  $inscofax     = $r["inscofax"    ];
  $inscocontact = $r["inscocontact"];
  $inscoid      = $r["inscoid"     ];
  $inscowebsite = $r["inscowebsite"];
  $inscoemail   = $r["inscoemail"  ];
  $inscogroup   = $r["inscogroup"  ];
  $inscotype    = $r["inscotype"   ];
  $inscoassign  = $r["inscoassign" ];
  $id           = $r["id"          ];

  echo "
    <P>

    <$STDFONT_B>Date added : <$STDFONT_E>
    $inscodtadd
    <BR>

    <$STDFONT_B>Date last modified : <$STDFONT_E>
    $inscodtmod
    <BR>

    <P>
    <B> NOT DONE YET! </B>
    <P>

  ";

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >Return to Insurance Company Menu</A>
    </CENTER>
  "; // abandon modification

  freemed_display_box_bottom ();

} else { // view is now the default

  $query = "SELECT * FROM $database.$db_name ".
    "ORDER BY insconame";

  $result   = fdb_query($query);
  $num_rows = fdb_num_rows ($result);
  if ($result) {
    freemed_display_box_top("Insurance Companies", $_ref, $page_name);

    // set count to first insco
    $count = 1;

    // validate the starting result number (avoid divide by zero)
    if (empty ($start) or ($start>$num_rows)) $start = 1;

    // determine how many pages there are...
    $total_num_res_pages = ceil ($num_rows / $max_num_res);

    // determine current page number
    if ($start != 0) { $this_page = ceil ($start / $max_num_res); }
     else            { $this_page = 1;                            }

    // determine if there are previous/next page links
    if (($total_num_res_pages>1) and ($this_page>1)) { $prev = true;  }
     else                                            { $prev = false; }
    if (($total_num_res_pages>1) and ($this_page<$total_num_res_pages))
                                                     { $next = true;  }
     else                                            { $next = false; }
    if ($total_num_res_pages>1)                      { $disp = true;  }
     else                                            { $disp = false; }

    // calculate last displayed
    $last_displayed = ($start + $max_num_res) - 1;
    if ($last_displayed > $num_rows) $last_displayed = $num_rows;

    if ($disp) {
     echo "\n <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER><TR>\n";
     if ($prev) {
      echo "
       <TD VALIGN=MIDDLE>
       &nbsp;
       <A HREF=\"$page_name?$_auth&action=$action&start=".
       abs($start-($max_num_res))."\"
       >prev</A>
       &nbsp;
       </TD>
      ";
     } else {
      echo "<TD VALIGN=MIDDLE>&nbsp;prev&nbsp;</TD>\n";
     } // end checking for prev
     echo "
      <TD VALIGN=MIDDLE>
      <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"$_auth\">
       <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"$action\">
       <SELECT NAME=\"start\">
     ";
     for ($i=1;$i<=$total_num_res_pages;$i++) {
      if ($i==$this_page) { $this_selected = "SELECTED"; }
       else               { $this_selected = "";         }
      $v = abs(($i - 1) * $max_num_res)+1;
      echo "<OPTION VALUE=\"$v\" $this_selected>$i of $total_num_res_pages\n";
     } // end of if loop
     echo "
       </SELECT>
      </TD><TD VALIGN=MIDDLE>
       <INPUT TYPE=SUBMIT VALUE=\"go\">
      </FORM>
      </TD>
     ";
     if ($next) {
      echo "
       <TD VALIGN=MIDDLE>
       &nbsp;
       <A HREF=\"$page_name?$_auth&action=$action&start=".
       abs($start+($max_num_res))."\"
       >next</A>
       &nbsp;</TD>
      ";
     } else {
      echo "<TD VALIGN=MIDDLE>&nbsp;next&nbsp;</TD>\n";
     } // end checking for next
     echo "\n</TR></TABLE><BR>\n";
    } // end checking for disp

    freemed_display_actionbar($page_name);
    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>Name</B></TD>
       <TD><B>Area</B></TD>
       <TD><B>Group</B></TD>
       <TD><B>Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    // skip entries ahead of it
    if ($start>1) {
     for ($j=1;$j<=($start);$j++) $r = fdb_fetch_array ($result);
    }

    while (($r = fdb_fetch_array($result)) and ($count<=$max_num_res)) {
      $id      = $r["id"        ];
      $icname  = fm_prep($r["insconame" ]);
      $iccity  = fm_prep($r["inscocity" ]);
      $icstate = fm_prep($r["inscostate"]);
      $icgroup = fm_prep($r["inscogroup"]);

      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      if ((empty($iccity)) OR
          (empty($icstate))) {
        $__location__ = "<B>NONE SPECIFIED</B>";
      } else {
        $__location__ = "$iccity, $icstate";
      } // derive location

       // here, the actual data is displayed
      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$icname</TD>
        <TD>$__location__</TD>
        <TD><I>";
      freemed_inscogroup_display($icgroup);
      echo "</I></TD> 
        <TD><!-- not implemented yet: <A HREF=
         \"$page_name?$_auth&id=$id&action=show\"
         ><FONT SIZE=-1>VIEW$id_mod</FONT></A>
         &nbsp; still not implemented--><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($user)>$delete_level) {
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>DEL$id_mod</FONT></A>
        "; // show delete
      }
      echo "
        </TD></TR>
      ";
      $count++;
    } // while there are no more

    echo "
      </TABLE>
      <P>
    "; // do bottom of the table

    freemed_display_actionbar ();
    freemed_display_box_bottom ();
  } else {
    echo "\n<B>no inscos found with that criteria.</B>\n";
  }

} // view is now the default

freemed_close_db (); // close the db
freemed_display_html_bottom ();

?>
