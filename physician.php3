<?php
  # file: physician.php3
  # note: physician database services
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # translation: max k <amk@span.ch>
  # lic : GPL, v2

  $page_name="physician.php3"; // for help info, later

  include "global.var.inc";
  include "freemed-functions.inc"; // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

if ($action=="addform") {

  freemed_display_box_top ("$Add_Physician", $page_name);
  echo "
    <BR>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD>
    <$STDFONT_B>$Last_name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phylname SIZE=25 MAXLENGTH=52
     VALUE=\"$phylname\">
    </TD><TD>
    <$STDFONT_B>$First_name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyfname SIZE=25 MAXLENGTH=50
     VALUE=\"$phyfname\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Title<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phytitle SIZE=10 MAXLENGTH=10
     VALUE=\"$phytitle\">
    </TD><TD>
    <$STDFONT_B>$Middle_Name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phymname SIZE=25 MAXLENGTH=50
     VALUE=\"$phymname\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Practice_Name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phypracname SIZE=25 MAXLENGTH=30
     VALUE=\"$phypracname\">
    </TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>

  <TR><TD>
    <$STDFONT_B>$Internal_ID #<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyid1 SIZE=11 MAXLENGTH=10
     VALUE=\"$phyid1\">
    </TD><TD>
    <$STDFONT_B>$Status <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phystatus\">
  ";

  freemed_display_phystatus($phystatus);

  echo "
    </SELECT>
    </TD></TR>

    <TR><TD COLSPAN=4><HR></TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Line 1<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr1a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1a\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Line 2<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr2a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2a\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_City<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phycitya SIZE=21 MAXLENGTH=20
     VALUE=\"$phycitya\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_State<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phystatea SIZE=6 MAXLENGTH=5
     VALUE=\"$phystatea\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Zip<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyzipa SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipa\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Phone # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyphonea");
  echo "
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Fax # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyfaxa");
  echo "
    </TD></TR>
    <TR><TD COLSPAN=4><HR></TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Line 1<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr1b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1b\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Line 2<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr2b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2b\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_City<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phycityb SIZE=20 MAXLENGTH=20
     VALUE=\"$phycityb\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_State<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phystateb SIZE=6 MAXLENGTH=5
     VALUE=\"$phystateb\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Zip<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyzipb SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipb\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Phone # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyphoneb");
  echo "
    <!-- <B>(</B>
    <INPUT TYPE=TEXT NAME=phyphoneb1 SIZE=4 MAXLENGTH=3
     VALUE=\"$phyphoneb1\"> <B>)</B>
    <INPUT TYPE=TEXT NAME=phyphoneb2 SIZE=4 MAXLENGTH=3
     VALUE=\"$phyphoneb2\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=phyphoneb3 SIZE=5 MAXLENGTH=4
     VALUE=\"$phyphoneb3\"> -->
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Fax # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyfaxb");
  echo "
    <!-- <B>(</B>
    <INPUT TYPE=TEXT NAME=phyfaxb1 SIZE=4 MAXLENGTH=3
     VALUE=\"$phyfaxb1\"> <B>)</B>
    <INPUT TYPE=TEXT NAME=phyfaxb2 SIZE=4 MAXLENGTH=3
     VALUE=\"$phyfaxb2\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=phyfaxb3 SIZE=5 MAXLENGTH=4
     VALUE=\"$phyfaxb3\"> -->
    </TD></TR>
    <TR><TD COLSPAN=4><HR></TD></TR>
    <TR><TD> 
    <$STDFONT_B>$Email_Address<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyemail SIZE=25 MAXLENGTH=30
     VALUE=\"$phyemail\">
    </TD><TD>
    <$STDFONT_B>$Cellular_Phone # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phycellular");
  echo "
    </TD></TR>
    <TR><TD COLSPAN=2>&nbsp;</TD><TD ALIGN=RIGHT>
    <$STDFONT_B>$BeeperPager # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phypager");
  echo "
    </TD></TR>
    <TR><TD>
     <$STDFONT_B>$UPIN_Number<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyupin SIZE=16 MAXLENGTH=15
     VALUE=\"$phyupin\">
    </TD><TD>
    <$STDFONT_B>$Social_Security # <$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=physsn1 SIZE=4 MAXLENGTH=3
     VALUE=\"$physsn1\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn2 SIZE=3 MAXLENGTH=2
     VALUE=\"$physsn2\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn3 SIZE=5 MAXLENGTH=4
     VALUE=\"$physsn3\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Degree 1<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phydeg1\">
  ";

  freemed_display_phy_degrees ($phydeg1);

  echo "
    </SELECT>
    </TD><TD>
       ";

  echo "
    <$STDFONT_B>$Specialty 1<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"physpe1\">
  ";

  freemed_display_phy_specialties ($physpe1);

  echo "
    </SELECT>
    </TD></TR>
        ";

  echo "
    <TR><TD>
    <$STDFONT_B>$Degree 2<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phydeg2\">
  ";

  freemed_display_phy_degrees ($phydeg2);

  echo "
    </SELECT>
    </TD><TD>
       ";

  echo "
    <$STDFONT_B>$Specialty 2<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"physpe2\">
  ";

  freemed_display_phy_specialties ($physpe2);

  echo "
    </SELECT>
    </TD></TR>
       " ;

 echo "
    <TR><TD>
    <$STDFONT_B>$Degree 3<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phydeg3\">
      " ;

  freemed_display_phy_degrees ($phydeg3);

  echo "
    </SELECT>
    </TD><TD>
       ";
  echo "
    <$STDFONT_B>$Specialty 3<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"physpe3\">
  ";

  freemed_display_phy_specialties ($physpe3);

  echo "
    </SELECT>
    </TD></TR>

  
    <TR><TD>
    <$STDFONT_B>Physician Internal/External<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phyref\">
      <OPTION VALUE=\"no\" ".
       ( ($phyref != "yes") ? "SELECTED" : "" ).">In-House
      <OPTION VALUE=\"yes\" ".
       ( ($phyref == "yes") ? "SELECTED" : "" ).">Referring
    </SELECT>
    </TD><TD>&nbsp;</TD><TD>&nbsp</TD></TR>

    <!-- this shouldn't be here !!! HELP!! HELP!!
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Number_of_Referrals<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefcount SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefcount\">
     </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Referral_Amount ($S_charged)<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefamt SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefamt\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Referral_Amount ($S_received)<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefcoll SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefcoll\">
    </TD></TR> -->

    </TABLE>
    <P>

    <TABLE BORDER=1 CELLSPACING=2 CELLPADDING=1
     VALIGN=MIDDLE ALIGN=CENTER><TR><TD>

    <CENTER>
     <$STDFONT_B><B>Unit Relative Value Charges</B><$STDFONT_E>
    </CENTER>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phychargemap$brackets\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>Internal Type</B></TD>
     <TD><B>Amount</B></TD>
    </TR>
  ";
  $_alternate = freemed_bar_alternate_color ();  
  $i_res = fdb_query("SELECT * FROM $database.intservtype");
  while ($i_r = fdb_fetch_array ($i_res)) {
    $_alternate = freemed_bar_alternate_color ($_alternate);  
    $i_id = $i_r ["id"];
    echo "
     <TR BGCOLOR=$_alternate>
      <TD>".fm_prep($i_r["intservtype"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phychargemap$brackets\"
        SIZE=10 MAXLENGTH=9 VALUE=\"".$phychargemap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types
  echo "
    </TABLE>

    </TD></TR></TABLE>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom ();

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >$Abandon_Addition</A>
    </CENTER>
  "; // abandon addition

} elseif ($action=="add") {

  freemed_display_box_top ("$Adding_Physician", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

  // assemble phone #s
  $phyphonea   = fm_phone_assemble ("phyphonea");
  $phyphoneb   = fm_phone_assemble ("phyphoneb");
  $phyfaxa     = fm_phone_assemble ("phyfaxa");
  $phyfaxb     = fm_phone_assemble ("phyfaxb");
  $phycellular = fm_phone_assemble ("phycellular");
  $phypager    = fm_phone_assemble ("phypager");

  // assemble ssn
  $physsn    = $physsn1.$physsn2.$physsn3;

    // actual query/insert
  $query = "INSERT INTO $database.physician VALUES ( ".
    "'$phylname',  '$phyfname',    '$phytitle',   ". 
    "'$phymname',  '$phypracname', '$phyaddr1a',  ". 
    "'$phyaddr2a', '$phycitya',    '$phystatea',     '$phyzipa',    ". 
    "'$phyphonea', '$phyfaxa',     '$phyaddr1b',  ".
    "'$phyaddr2b', '$phycityb',    '$phystateb',     '$phyzipb',    ".
    "'$phyphoneb', '$phyfaxb',     '$phyemail',   ".
    "'$phycellular', '$phypager', ". // 19990804
    "'$phyupin',   '$physsn',      '$phydeg1',    ".
    "'$phydeg2',   '$phydeg3',     '$physpe1',    ".
    "'$physpe2',   '$physpe3',     '$phyid1',     ".
    "'$phystatus', '$phyref',      '$phyrefcount',".
    "'$phyrefamt', '$phyrefcoll',  '$phyintext',  ".
    "'".fm_join_from_array($phychargemap)."',     ".
    "'".fm_join_from_array($phyidmap)."',         ".
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
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  echo "
   <P>
   <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
    ><$STDFONT_B>Return to $record_name Menu<$STDFONT_E></A>
   </CENTER>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify_Physician", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>$Please_use_the_MODIFY_form_to_MODIFY_someone !</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, "physician");

  $phylname    = $r["phylname"   ];
  $phyfname    = $r["phyfname"   ];
  $phytitle    = $r["phytitle"   ];
  $phymname    = $r["phymname"   ];
  $phypracname = $r["phypracname"];
  $phyaddr1a   = $r["phyaddr1a"  ];
  $phyaddr2a   = $r["phyaddr2a"  ];
  $phycitya    = $r["phycitya"   ];
  $phystatea   = $r["phystatea"  ]; // 19990622
  $phyzipa     = $r["phyzipa"    ];
  $phyphonea   = $r["phyphonea"  ];
  $phyfaxa     = $r["phyfaxa"    ];
  $phyaddr1b   = $r["phyaddr1b"  ];
  $phyaddr2b   = $r["phyaddr2b"  ];
  $phycityb    = $r["phycityb"   ];
  $phystateb   = $r["phystateb"  ]; // 19990622
  $phyzipb     = $r["phyzipb"    ];
  $phyphoneb   = $r["phyphoneb"  ];
  $phyfaxb     = $r["phyfaxb"    ];
  $phyemail    = $r["phyemail"   ];
  $phycellular = $r["phycellular"]; // 19990804
  $phypager    = $r["phypager"   ]; // 19990804
  $phyupin     = $r["phyupin"    ];
  $physsn      = $r["physsn"     ];
  $phydeg1     = $r["phydeg1"    ]; // 19990830
  $phydeg2     = $r["phydeg2"    ]; // ..
  $phydeg3     = $r["phydeg3"    ]; // ..
  $physpe1     = $r["physpe1"    ];
  $physpe2     = $r["physpe2"    ];
  $physpe3     = $r["physpe3"    ];
  $phyid1      = $r["phyid1"     ];
  $phystatus   = $r["phystatus"  ];
  $phyref      = $r["phyref"     ];
  $phyrefcount = $r["phyrefcount"];
  $phyrefamt   = $r["phyrefamt"  ];
  $phyrefcoll  = $r["phyrefcoll" ];
  $phychargemap = fm_split_into_array( $r[phychargemap] );
  $phyidmap = fm_split_into_array( $r[phyidmap] );

  // disassemble ssn
  $physsn1    = substr($physsn,    0, 3);
  $physsn2    = substr($physsn,    3, 2);
  $physsn3    = substr($physsn,    5, 4);

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD>
    <$STDFONT_B>$Last_name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phylname SIZE=25 MAXLENGTH=52
     VALUE=\"$phylname\">
    </TD><TD>
    <$STDFONT_B>$First_name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyfname SIZE=25 MAXLENGTH=50
     VALUE=\"$phyfname\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Title<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phytitle SIZE=10 MAXLENGTH=10
     VALUE=\"$phytitle\">
    </TD><TD>
    <$STDFONT_B>$Middle_Name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phymname SIZE=25 MAXLENGTH=50
     VALUE=\"$phymname\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Practice_Name<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phypracname SIZE=25 MAXLENGTH=30
     VALUE=\"$phypracname\">
    </TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>

  <TR><TD>
    <$STDFONT_B>$Internal_ID #<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyid1 SIZE=11 MAXLENGTH=10
     VALUE=\"$phyid1\">
    </TD><TD>
    <$STDFONT_B>$Status <$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phystatus\">
  ";

  freemed_display_phystatus($phystatus);

  echo "
    </SELECT>
    </TD></TR>

    <TR><TD COLSPAN=4><HR></TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Line 1<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr1a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1a\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Line 2<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr2a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2a\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_City<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phycitya SIZE=21 MAXLENGTH=20
     VALUE=\"$phycitya\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_State<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phystatea SIZE=6 MAXLENGTH=5
     VALUE=\"$phystatea\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Zip<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyzipa SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipa\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Phone # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyphonea");
  echo "
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Primary_Address_Fax # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyfaxa");
  echo "
    </TD></TR>
    <TR><TD COLSPAN=4><HR></TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Line 1<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr1b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1b\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Line 2<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyaddr2b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2b\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_City<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phycityb SIZE=20 MAXLENGTH=20
     VALUE=\"$phycityb\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_State<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phystateb SIZE=6 MAXLENGTH=5
     VALUE=\"$phystateb\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Zip<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyzipb SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipb\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Phone # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyphoneb");
  echo "
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Secondary_Address_Fax # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phyfaxb");
  echo "
    </TD></TR>
    <TR><TD COLSPAN=4><HR></TD></TR>
    <TR><TD> 
    <$STDFONT_B>$Email_Address<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyemail SIZE=25 MAXLENGTH=30
     VALUE=\"$phyemail\">
    </TD><TD>
    <$STDFONT_B>$Cellular_Phone # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phycellular");
  echo "
    </TD></TR>
    <TR><TD COLSPAN=2>&nbsp;</TD><TD ALIGN=RIGHT>
    <$STDFONT_B>$BeeperPager # <$STDFONT_E>
    </TD><TD>
  ";
  fm_phone_entry ("phypager");
  echo "
    </TD></TR>
    <TR><TD>
     <$STDFONT_B>$UPIN_Number<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyupin SIZE=16 MAXLENGTH=15
     VALUE=\"$phyupin\">
    </TD><TD>
    <$STDFONT_B>$Social_Security # <$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=physsn1 SIZE=4 MAXLENGTH=3
     VALUE=\"$physsn1\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn2 SIZE=3 MAXLENGTH=2
     VALUE=\"$physsn2\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn3 SIZE=5 MAXLENGTH=4
     VALUE=\"$physsn3\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Degree 1<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phydeg1\">
  ";

  freemed_display_phy_degrees ($phydeg1);

  echo "
    </SELECT>
    </TD><TD>
       ";

  echo "
    <$STDFONT_B>$Specialty 1<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"physpe1\">
  ";

  freemed_display_phy_specialties ($physpe1);

  echo "
    </SELECT>
    </TD></TR>
        ";

  echo "
    <TR><TD>
    <$STDFONT_B>$Degree 2<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phydeg2\">
  ";

  freemed_display_phy_degrees ($phydeg2);

  echo "
    </SELECT>
    </TD><TD>
       ";

  echo "
    <$STDFONT_B>$Specialty 2<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"physpe2\">
  ";

  freemed_display_phy_specialties ($physpe2);

  echo "
    </SELECT>
    </TD></TR>
       " ;

 echo "
    <TR><TD>
    <$STDFONT_B>$Degree 3<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phydeg3\">
      " ;

  freemed_display_phy_degrees ($phydeg3);

  echo "
    </SELECT>
    </TD><TD>
       ";
  echo "
    <$STDFONT_B>$Specialty 3<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"physpe3\">
  ";

  freemed_display_phy_specialties ($physpe3);

  echo "
     </SELECT>
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>Physician Internal/External<$STDFONT_E>
    </TD><TD>
    <SELECT NAME=\"phyref\">
      <OPTION VALUE=\"no\" ".
       ( ($phyref != "yes") ? "SELECTED" : "" ).">In-House
      <OPTION VALUE=\"yes\" ".
       ( ($phyref == "yes") ? "SELECTED" : "" ).">Referring
    </SELECT>
    </TD><TD>&nbsp;</TD><TD>&nbsp</TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Number_of_Referrals<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefcount SIZE=10 MAXLENGTH=10
     VALUE=\"".fm_prep($phyrefcount)."\">
     </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Referral_Amount ($S_charged)<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefamt SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefamt\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Referral_Amount ($S_received)<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"phyrefcoll\" SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefcoll\">
    </TD></TR></TABLE>

    <P>

    <TABLE BORDER=1 CELLSPACING=2 CELLPADDING=1
     VALIGN=MIDDLE ALIGN=CENTER><TR><TD>

    <CENTER>
     <$STDFONT_B><B>Unit Relative Value Charges</B><$STDFONT_E>
    </CENTER>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phychargemap$brackets\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>Internal Type</B></TD>
     <TD><B>Amount</B></TD>
    </TR>
  ";
  $_alternate = freemed_bar_alternate_color ();  
  $i_res = fdb_query("SELECT * FROM $database.intservtype");
  while ($i_r = fdb_fetch_array ($i_res)) {
    $_alternate = freemed_bar_alternate_color ($_alternate);  
    $i_id = $i_r ["id"];
    echo "
     <TR BGCOLOR=$_alternate>
      <TD>".fm_prep($i_r["intservtype"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phychargemap$brackets\"
        SIZE=10 MAXLENGTH=9 VALUE=\"".$phychargemap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types
  echo "
    </TABLE>

    </TR></TABLE>

    <TABLE BORDER=1 CELLSPACING=2 CELLPADDING=1
     VALIGN=MIDDLE ALIGN=CENTER><TR><TD>

    <CENTER>
     <$STDFONT_B><B>Insurance ID #s by Group</B><$STDFONT_E>
    </CENTER>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phyidmap$brackets\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>Insurance Group</B></TD>
     <TD><B>ID Number</B></TD>
    </TR>
  ";
  $_alternate = freemed_bar_alternate_color ();  
  $i_res = fdb_query("SELECT * FROM $database.inscogroup");
  while ($i_r = fdb_fetch_array ($i_res)) {
    $_alternate = freemed_bar_alternate_color ($_alternate);  
    $i_id = $i_r ["id"];
    echo "
     <TR BGCOLOR=$_alternate>
      <TD>".fm_prep($i_r["inscogroup"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phyidmap$brackets\"
        SIZE=10 MAXLENGTH=9 VALUE=\"".$phyidmap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types
  echo "
    </TABLE>

    </TR></TABLE>

    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Clear\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom ();

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >$Abandon_Modification</A>
    </CENTER>
  "; // abandon modification

} elseif ($action=="mod") {

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying_Physician", $page_name);

  echo "
    <BR><BR>
    <$STDFONT_B>$Modifying . . . 
  ";

  // reassemble phone #s
  $phyphonea = fm_phone_assemble ("phyphonea");
  $phyphoneb = fm_phone_assemble ("phyphoneb");
  $phyfaxa   = fm_phone_assemble ("phyfaxa");
  $phyfaxb   = fm_phone_assemble ("phyfaxb");
  $phycellular = fm_phone_assemble ("phycellular");
  $phypager    = fm_phone_assemble ("phypager");

  // reassemble ssn #
  $physsn    = $physsn1.$physsn2.$physsn3;

  $query = "UPDATE $database.physician SET ".
    "phylname   ='$phylname',    ".
    "phyfname   ='$phyfname',    ".
    "phytitle   ='$phytitle',    ". 
    "phymname   ='$phymname',    ".     
    "phypracname='$phypracname', ".
    "phyaddr1a  ='$phyaddr1a',   ". 
    "phyaddr2a  ='$phyaddr2a',   ".
    "phycitya   ='$phycitya',    ".
    "phystatea  ='$phystatea',   ". // 19990622
    "phyzipa    ='$phyzipa',     ". 
    "phyphonea  ='$phyphonea',   ".
    "phyfaxa    ='$phyfaxa',     ".
    "phyaddr1b  ='$phyaddr1b',   ".    
    "phyaddr2b  ='$phyaddr2b',   ".
    "phycityb   ='$phycityb',    ".
    "phystateb  ='$phystateb',   ". // 19990622
    "phyzipb    ='$phyzipb',     ".
    "phyphoneb  ='$phyphoneb',   ".
    "phyfaxb    ='$phyfaxb',     ".
    "phyemail   ='$phyemail',    ".
    "phycellular = '$phycellular', ". // 19990804
    "phypager   ='$phypager',    ".   // 19990804
    "phyupin    ='$phyupin',     ".
    "physsn     ='$physsn',      ".
    "phydeg1    ='$phydeg1',     ".  // 19990830
    "phydeg2    ='$phydeg2',     ".  // ..
    "phydeg3    ='$phydeg3',     ".  // ..
    "physpe1    ='$physpe1',     ".
    "physpe2    ='$physpe2',     ".
    "physpe3    ='$physpe3',     ".
    "phyid1     ='$phyid1',      ".
    "phystatus  ='$phystatus',   ".
    "phyref     ='$phyref',      ".
    "phyrefcount='$phyrefcount', ".
    "phyrefamt  ='$phyrefamt',   ".
    "phyrefcoll ='$phyrefcoll',  ".
    "phychargemap='".fm_join_from_array($phychargemap)."', ".
    "phyidmap    ='".fm_join_from_array($phyidmap)    ."'  ". 
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
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  freemed_display_box_bottom ();

  freemed_display_bottom_links ("$Physician", $page_name, $_ref);


} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting_Physician", $page_name, $_ref);

  $result = fdb_query("DELETE FROM $database.physician
    WHERE (id = \"$id\")");

  echo "
    <BR><BR>
    <I>$Physician $id deleted<I>.
  ";
  if ($debug) {
    echo "
      <BR><B>$RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=select\"
     >$Delete_Another</A></CENTER>
  ";

  freemed_display_box_bottom ();

  freemed_display_bottom_links ("$Physician", $page_name, $_ref);

} elseif ($action=="show") {

  // this section is still quite broken, but should
  // allow someone to pull up a physician record,
  // then return them to the menu.

  // multiple choices and RDBMS stuff is not
  // implemented yet.

  freemed_display_box_top ("$Physician_Display", $page_name, $_ref);

  if (empty($id)) {
    echo "

     <CENTER>
      <B>$You_must_specify_an_id #!</B>
      <BR><BR>
      <A HREF=\"$page_name?$_auth&action=view\"
       >$Return_to_the_Physician_Menu</A>
     </CENTER>

     <BR><BR>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freened_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, "physician");

  $phylname    = $r["phylname"   ];
  $phyfname    = $r["phyfname"   ];
  $phytitle    = $r["phytitle"   ];
  $phymname    = $r["phymname"   ];
  $phypracname = $r["phypracname"];
  $phyaddr1a   = $r["phyaddr1a"  ];
  $phyaddr2a   = $r["phyaddr2a"  ];
  $phycitya    = $r["phycitya"   ];
  $phystatea   = $r["phystatea"  ];
  $phyzipa     = $r["phyzipa"    ];
  $phyphonea   = $r["phyphonea"  ];
  $phyfaxa     = $r["phyfaxa"    ];
  $phyaddr1b   = $r["phyaddr1b"  ];
  $phyaddr2b   = $r["phyaddr2b"  ];
  $phycityb    = $r["phycityb"   ];
  $phystateb   = $r["phystateb"  ];
  $phyzipb     = $r["phyzipb"    ];
  $phyphoneb   = $r["phyphoneb"  ];
  $phyfaxb     = $r["phyfaxb"    ];
  $phyemail    = $r["phyemail"   ];
  $phycellular = $r["phycellular"];
  $phypager    = $r["phypager"   ];
  $phyupin     = $r["phyupin"    ];
  $physsn      = $r["physsn"     ];
  $phydeg1     = $r["phydeg1"    ];
  $phydeg2     = $r["phydeg2"    ];
  $phydeg3     = $r["phydeg3"    ];
  $physpe1     = $r["physpe1"    ];
  $physpe2     = $r["physpe2"    ];
  $physpe3     = $r["physpe3"    ];
  $phyid1      = $r["phyid1"     ];
  $phystatus   = $r["phystatus"  ];
  $phyref      = $r["phyref"     ];
  $phyrefcount = $r["phyrefcount"];
  $phyrefamt   = $r["phyrefamt"  ];
  $phyrefcoll  = $r["phyrefcoll" ];

  // disassemble ssn
  $physsn1    = substr($physsn,    0, 3);
  $physsn2    = substr($physsn,    3, 2);
  $physsn3    = substr($physsn,    5, 4);

  // get real text of phystatus
  $phystatus = freemed_get_link_field ($phystatus, "phystatus",
    "phystatus");

  echo "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=1 WIDTH=100%>

    <TR><TD>
    <$STDFONT_B>$Last_name : <$STDFONT_E>
    </TD><TD>
    $phylname
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$First_name : <$STDFONT_E>
    </TD><TD>
    $phyfname
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$Title : <$STDFONT_E>
    </TD><TD>
    $phytitle
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$Middle_Name : <$STDFONT_E>
    </TD><TD>
    $phymname
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$Practice_Name : <$STDFONT_E>
    </TD><TD>
    $phypracname
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$Primary_Address_Line 1 : <$STDFONT_E>
    </TD><TD>
    $phyaddr1a
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$Primary_Address_Line 2 : <$STDFONT_E>
    </TD><TD>
    $phyaddr2a
    </TD></TR>

    <TR><TD>
    <$STDFONT_B>$Primary_Address_City : <$STDFONT_E>
    </TD><TD>
    $phycitya
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Primary_Address_State : <$STDFONT_E>
    </TD><TD>
    $phystatea
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Primary_Address_Zip : <$STDFONT_E>
    </TD><TD>
    $phyzipa
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Primary_Address_Phone # : <$STDFONT_E>
    </TD><TD>
    ".fm_date_display("phyphonea")."
     <!-- ($phyphonea1) $phyphonea2-$phyphonea3 -->
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Primary_Address_Fax # : <$STDFONT_E>
    </TD><TD>
    ($phyfaxa1) $phyfaxa2-$phyfaxa3
    </TD></TR>

  ";

  // check if we have to display this
  if ((strlen(trim($phyaddr1b))!=0) AND (strlen(trim($phyaddr2b))!=0)) {
    echo "
    <TR><TD>
    <$STDFONT_B>$Secondary_Address_Line 1 : <$STDFONT_E>
    </TD><TD>
    $phyaddr1b
    </TD></TR>
    <TR><TD>    
    <$STDFONT_B>$Secondary_Address_Line 2 : <$STDFONT_E>
    </TD><TD>
    $phyaddr2b
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Secondary_Address_City : <$STDFONT_E>
    </TD><TD>
    $phycityb
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Secondary_Address_State : <$STDFONT_E>
    </TD><TD>
    $phystateb
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Secondary_Address_Zip : <$STDFONT_E>
    </TD><TD>
    $phyzipb
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Secondary_Address_Phone # : <$STDFONT_E>
    </TD><TD>
    ($phyphoneb1) $phyphoneb2-$phyphoneb3
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Secondary_Address_Fax # : <$STDFONT_E>
    </TD><TD>
    $phyfaxb
    </TD></TR>

  ";
  } // end checking for secondary address

  echo "
    <TR><TD>
    <$STDFONT_B>$Email_Address : <$STDFONT_E>
    </TD><TD>
    <A HREF=\"$_mail_handler$phyemail\"
     >$phyemail</A>
    </TD></TR>

     <TR><TD> 
    <$STDFONT_B>$UPIN_Number : <$STDFONT_E>
    </TD><TD>
    $phyupin
    </TD></TR>

     <TR><TD>
    <$STDFONT_B>$Social_Security #  : <$STDFONT_E>
    </TD><TD>
    $physsn1-$physsn2-$physsn3
    </TD></TR>

     <TR><TD>
    <$STDFONT_B>$Specialty 1 : <$STDFONT_E>
    </TD><TD>

  ";

  freemed_specialty_display($physpe1);

  echo "
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Specialty 2 : <$STDFONT_E>
    </TD><TD>
  ";

  freemed_specialty_display($physpe2);

  echo "
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Specialty 3 : <$STDFONT_E>
    </TD><TD>
  ";

  freemed_specialty_display($physpe3);

  echo "
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Internal_ID # : <$STDFONT_E>
    </TD><TD>
    $phyid1
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Status : <$STDFONT_E>
    </TD><TD>
    $phystatus
    </TD></TR>
     <TR><TD>

    <$STDFONT_B>$Reference : <$STDFONT_E>
    </TD><TD>
  ";

    // is the doc a PCP or a referring doc??
  switch ($phyref) {
    case "no":
      echo "\n$Primary_care_provider\n";
      break;
    case "yes":
      echo "\n$Referring\n";
      break;
    default:
      echo "\n$NONE_SELECTED\n";
  }

  echo "
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Number_of_Referrals : <$STDFONT_E>
    </TD><TD>
    $phyrefcount
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Referral_Amount ($S_charged) : <$STDFONT_E>
    </TD><TD>
    $phyrefamt
    </TD></TR>
     <TR><TD>
    <$STDFONT_B>$Referral_Amount ($S_received) : <$STDFONT_E>
    </TD><TD>
    $phyrefcoll
    </TD></TR>
    </TABLE>

  ";

  freemed_display_box_bottom ();

  freemed_display_bottom_links ("$Physician", $page_name);

} else { // view is now the default

  $query = "SELECT * FROM $database.physician ".
    "ORDER BY phylname, phyfname";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ("$Physicians", $_ref, $page_name);

    freemed_display_actionbar($page_name);
    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Last_Name</B></TD>
       <TD><B>$First_Name</B></TD>
       <TD><B>$Degrees</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ($_alternate);

    while ($r = fdb_fetch_array($result)) {
    
      $id    = $r["id"      ];
      $lname = $r["phylname"];
      $fname = $r["phyfname"];
      $spe1  = $r["physpe1" ];
      $spe2  = $r["physpe2" ];
      $spe3  = $r["physpe3" ];

        // alternate colors
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

        // here we get __degrees__ from the
        // specialty database

      if ($spe1!="0") {
        $r = fdb_fetch_array(fdb_query("SELECT * FROM
           $database.degrees WHERE id='$spe1'"));
        $_d1 = $r["degdegree"]; // get degree name
      } else {
        $_d1 = "";
      } // first degree

      if ($physpe2!="0") {
        $r = fdb_fetch_array(fdb_query("SELECT * FROM
           $database.degrees WHERE id='$spe2'"));
        $_d2 = $r["degdegree"]; // get degree name
      } else {
        $_d2 = "";
      } // second specialty

      if ($physpe3!="0") {
        $r = fdb_fetch_array(fdb_query("SELECT * FROM
           $database.degrees WHERE id='$spe3'"));
        $_d3 = $r["degdegree"]; // get degree name
      } else {
        $_d3 = "";
      } // third specialty

        // assemble 1 and 2
      if (($_d1!="") AND ($_d2!="")) {
        $_spe = $_d1.", ".$_d2;
      } elseif (($_s1!="") AND ($_d2=="")) {
        $_spe = $_d1;
      } elseif (($_d1=="") AND ($_d2!="")) {
        $_spe = $_d2;
      } elseif (($_d1=="") AND ($_d2=="")) {
        $_spe = "";
      }
        // now tack on 3
      if (($_spe!="") AND ($_d3!="")) {
        $__degrees__ = $_spe.", ".$_d3;
      } elseif (($_spe!="") AND ($_d3=="")) {
        $__degrees__ = $_spe;
      } elseif (($_spe=="") AND ($_d3!="")) {
        $__degrees__ = $_d3;
      } elseif (($_spe=="") AND ($_d3=="")) {
        $__degrees__ = "";
      }

      // to solve lack of color problem in Netscape, and
      // maybe other platforms, insert a &nbsp;
      if ($__degrees__=="") {
        $__degrees__="&nbsp;";
      }

       // here, the actual data is displayed
      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$lname</TD>
        <TD>$fname</TD>
        <TD>$__degrees__</TD> 
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=show\"
         ><FONT SIZE=-1>$VIEW$id_mod</FONT></A>
         &nbsp;<A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel ($user)>$delete_level) {
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$DEL$id_mod</FONT></A>
        "; // show delete
      }
      echo "
        </TD></TR>
      ";

    } // while there are no more

    echo "
      </TABLE>
    "; // do bottom of the table

    freemed_display_actionbar($page_name); // bottom action bar
    freemed_display_box_bottom ();
    freemed_display_bottom_links ("$Physicians", $page_name, $_ref);

    //if ((strlen($_ref)<5) OR ($_ref=="main.php3")) {
    //  echo "
    //    <BR><BR>
    //    <CENTER><A HREF=\"main.php3?$_auth\"
    //     >$Return_to_the_Main_Menu</A>
    //    </CENTER>
    //  ";
    //} else {
    //  echo "
    //    <BR><BR>
    //    <CENTER><A HREF=\"$_ref?$_auth\"
    //     >$Return_to_Previous_Menu</A>
    //    </CENTER>
    //  ";
    //} // page footer
  } else {
    echo "\n<B>$No_physicians_found_with_that_criteria.</B>\n";
  }

} // view is now the default

freemed_close_db (); // close the database

freemed_display_html_bottom ();

?>
