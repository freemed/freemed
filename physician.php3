<?php
  // file: physician.php3
  // note: physician database services
  // code: jeff b (jeff@univrel.pr.uconn.edu)
  //       adam b (gdrago23@yahoo.com)
  // translation: max k <amk@span.ch>
  // lic : GPL, v2

  $page_name   ="physician.php3"; // for help info, later
  $record_name ="Provider";
  $db_name     ="physician";

  include "global.var.inc";
  include "freemed-functions.inc"; // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

switch($action) {
 case "addform": case "add": // 'form' actions not necessary
 case "modform": case "mod": // in notebook implementation
  $book = new notebook ($page_name,
    array ("action", "_auth", "id", "been_here"), true );
  $book->set_submit_name("OK"); // not sure what this does...
  
  if (($action=="modform") AND (!$been_here)) { // load the values
    $r = freemed_get_link_rec ($id, $db_name);

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

    if (strlen($phyaddr1b)>0) $has_second_addr=true;
  } // fetch the data first time through
  
  switch($action) {
   case "addform": case "add":
    $action_name="Add";
    if (empty($been_here)) 
      $been_here=1;
   break; // inner addform/add switch
   case "modform": case "mod": 
    $action_name="Modify";
    if (empty($been_here)) 
      $been_here=1;
   break; // inner addform/add switch
  } // inner add/mod[form] switch
  
  $stat_q = "SELECT * FROM phystatus ORDER BY phystatus";
  $stat_r = fdb_query($stat_q); // have the result ready for display_selectbox
  
  $book->add_page (
    "Primary Information",
    array (
      "phylname", "phyfname", "phytitle", "phymname",
      "phytitle", "phypracname", "phyid1", "phystatus"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Last_name : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phylname SIZE=25 MAXLENGTH=52
     VALUE=\"$phylname\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$First_name : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyfname SIZE=25 MAXLENGTH=50
     VALUE=\"$phyfname\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Middle_Name : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phymname SIZE=25 MAXLENGTH=50
     VALUE=\"$phymname\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Title : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phytitle SIZE=10 MAXLENGTH=10
     VALUE=\"$phytitle\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Practice_Name : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phypracname SIZE=25 MAXLENGTH=30
     VALUE=\"$phypracname\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Internal_ID # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyid1 SIZE=11 MAXLENGTH=10
     VALUE=\"$phyid1\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Status : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".
    freemed_display_selectbox($stat_r, "#phystatus#", "phystatus")
    ."
    </TD></TR>
   </TABLE>
    "
  );
 
  $book->add_page (
    "Contact",
    array (
     "phyemail", "phycellular", "phypager",
     "phycellular_1", "phycellular_2", "phycellular_3", "phycellular_4",
     "phycellular_5",
     "phypager_1", "phypager_2", "phypager_3", "phypager_4",
     "phypager_5",
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT> 
    <$STDFONT_B>$Email_Address : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyemail SIZE=25 MAXLENGTH=30
     VALUE=\"$phyemail\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Cellular_Phone # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phycellular")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$BeeperPager # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phypager")."
    </TD></TR>
   </TABLE>
    "
  );
 
  $book->add_page (
    "Address",
    array (
     "phyaddr1a", "phyaddr2a", "phycitya", "phystatea", "phyphonea", "phyzipa",
     "phyphonea_1", "phyphonea_2", "phyphonea_3", "phyphonea_4",
     "phyphonea_5",
     "phyfaxa_1", "phyfaxa_2", "phyfaxa_3", "phyfaxa_4", "phyfaxa",
     "phyfaxa_5",
     "has_second_addr"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_Line 1 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr1a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1a\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_Line 2 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr2a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2a\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_City : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phycitya SIZE=21 MAXLENGTH=20
     VALUE=\"$phycitya\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_State : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phystatea SIZE=6 MAXLENGTH=5
     VALUE=\"$phystatea\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_Zip : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyzipa SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipa\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_Phone # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyphonea")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Primary_Address_Fax # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyfaxa")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>Has Second Address : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=CHECKBOX NAME=\"has_second_addr\" ".
    ($has_second_addr ? "CHECKED" : "").">".$book->generate_refresh()."
    </TD></TR>
   </TABLE>

    "
  );

  if ($has_second_addr)
    $book->add_page (
      "Address 2",
      array (
       "phyphoneb_1", "phyphoneb_2", "phyphoneb_3", "phyphoneb_4",
       "phyphoneb_5",
       "phyfaxb_1", "phyfaxb_2", "phyfaxb_3", "phyfaxb_4", "phyfaxb",
       "phyfaxb_5",
       "phyaddr1b", "phyaddr2b", "phycityb", "phystateb", "phyphoneb", "phyzipb"
      ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_Line 1 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr1b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_Line 2 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr2b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_City : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phycityb SIZE=20 MAXLENGTH=20
     VALUE=\"$phycityb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_State : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phystateb SIZE=6 MAXLENGTH=5
     VALUE=\"$phystateb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_Zip : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyzipb SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_Phone # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyphoneb")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Secondary_Address_Fax # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyfaxb")."
    </TD></TR>
   </TABLE>
      "
    ); // second address page

  $phy_deg_q = "SELECT * FROM degrees ORDER BY ".
               "degdegree, degname";
  $phy_deg_r = fdb_query($phy_deg_q);
  $spec_q = "SELECT * FROM specialties ORDER BY ".
            "specname, specdesc";
  $spec_r = fdb_query($spec_q);

  $book->add_page(
    "Personal",
    array (
      "phyupin", "phyref",
      "physsn1", "physsn2", "physsn3", 
      "phydeg1", "phydeg2", "phydeg3",
      "physpe1", "physpe2", "physpe3"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>$UPIN_Number : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyupin SIZE=16 MAXLENGTH=15
     VALUE=\"$phyupin\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Social_Security # : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=physsn1 SIZE=4 MAXLENGTH=3
     VALUE=\"$physsn1\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn2 SIZE=3 MAXLENGTH=2
     VALUE=\"$physsn2\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn3 SIZE=5 MAXLENGTH=4
     VALUE=\"$physsn3\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Degree 1 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($phy_deg_r, 
       "#degdegree#, #degname#", "phydeg1")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Degree 2 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($phy_deg_r, 
       "#degdegree#, #degname#", "phydeg2")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Degree 3 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($phy_deg_r, 
       "#degdegree#, #degname#", "phydeg3")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Specialty 1 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($spec_r, 
       "#specname#, #specdesc#", "physpe1")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Specialty 2 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($spec_r, 
       "#specname#, #specdesc#", "physpe2")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>$Specialty 3 : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($spec_r, 
       "#specname#, #specdesc#", "physpe3")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>Physician Internal/External<$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <SELECT NAME=\"phyref\">
      <OPTION VALUE=\"no\" ".
       ( ($phyref != "yes") ? "SELECTED" : "" ).">In-House
      <OPTION VALUE=\"yes\" ".
       ( ($phyref == "yes") ? "SELECTED" : "" ).">Referring
    </SELECT>
    </TD></TR>
  

   </TABLE>
    "
  );

  // cache this outside of the function call (can't abstract that while-loop)
  // $brackets is defined in global.var.inc
  $cmap_buf="";
  $int_r = fdb_query("SELECT * FROM intservtype");
  while ($i_r = fdb_fetch_array ($int_r)) {
    $i_id = $i_r ["id"];
    $cmap_buf .= "
     <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color ($_alternate)).">
      <TD>".fm_prep($i_r["intservtype"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phychargemap$brackets\"
        SIZE=15 MAXLENGTH=30 VALUE=\"".$phychargemap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types

  $book->add_page(
    "Charge Map",
    array (
      "phychargemap"
    ),
    "
    <INPUT TYPE=HIDDEN NAME=\"phychargemap$brackets\" VALUE=\"0\">

   <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
    BGCOLOR=\"#000000\"> <!-- black border --><TR><TD>
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>Internal Type</B></TD>
     <TD><B>Amount</B></TD>
    </TR>
    $cmap_buf
    </TABLE>
   </TD></TR></TABLE></CENTER>
    "
  );

  $insmap_buf = ""; // cache the output, as above
  $i_res = fdb_query("SELECT * FROM inscogroup");
  while ($i_r = fdb_fetch_array ($i_res)) {
    $i_id = $i_r ["id"];
    $insmap_buf .= "
     <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
      <TD>".fm_prep($i_r["inscogroup"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phyidmap$brackets\"
        SIZE=15 MAXLENGTH=30 VALUE=\"".$phyidmap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types

  $book->add_page(
    "Insurance IDs",
    array (
      "phyidmap"
    ),
    "
  <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 
   BGCOLOR=\"#000000\"> <!-- black border --><TR><TD>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phyidmap$brackets\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>Insurance Group</B></TD>
     <TD><B>ID Number</B></TD>
    </TR>
    $insmap_buf
    </TABLE>
  </TD></TR></TABLE></CENTER>
    "
  );
  // now display the thing
  freemed_display_box_top("$action_name $record_name", $page_name);
  if (!$book->is_done()) {
    echo "<CENTER>\n";
    $book->display();
    echo "</CENTER>\n";
  } else { // submit has been clicked
    if ($action=="modform") {
      echo "
        <P ALIGN=CENTER>
        <$STDFONT_B>$Modifying . . . <$STDFONT_E>
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
  
      $query = "UPDATE $db_name SET ".
        "phylname   ='$phylname',    ".
        "phyfname   ='$phyfname',    ".
        "phytitle   ='$phytitle',    ". 
        "phymname   ='$phymname',    ".     
        "phypracname='$phypracname', ".
        "phyaddr1a  ='$phyaddr1a',   ". 
        "phyaddr2a  ='$phyaddr2a',   ".
        "phycitya   ='$phycitya',    ".
        "phystatea  ='$phystatea',   ".
        "phyzipa    ='$phyzipa',     ". 
        "phyphonea  ='$phyphonea',   ".
        "phyfaxa    ='$phyfaxa',     ".
        "phyaddr1b  ='$phyaddr1b',   ".    
        "phyaddr2b  ='$phyaddr2b',   ".
        "phycityb   ='$phycityb',    ".
        "phystateb  ='$phystateb',   ".
        "phyzipb    ='$phyzipb',     ".
        "phyphoneb  ='$phyphoneb',   ".
        "phyfaxb    ='$phyfaxb',     ".
        "phyemail   ='$phyemail',    ".
        "phycellular = '$phycellular', ".
        "phypager   ='$phypager',    ".
        "phyupin    ='$phyupin',     ".
        "physsn     ='$physsn',      ".
        "phydeg1    ='$phydeg1',     ".
        "phydeg2    ='$phydeg2',     ".
        "phydeg3    ='$phydeg3',     ".
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
      
      if ($result) {
        echo "
	<$STDFONT_B>Done.<$STDFONT_E>
	";
      } else { // error!
        echo "
	<$STDFONT_B>Error! [$query, $result]<$STDFONT_E>
	";
      }  
      // finished the mod database call
    } else if ($action=="addform") {
      echo "
    <P ALIGN=CENTER>
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
      $query = "INSERT INTO $db_name VALUES ( 
        '$phylname',
        '$phyfname',
        '$phytitle',    
        '$phymname',
        '$phypracname',
        '$phyaddr1a',   
        '$phyaddr2a',
        '$phycitya',
        '$phystatea',
        '$phyzipa',     
        '$phyphonea',
        '$phyfaxa',
        '$phyaddr1b',
        '$phyaddr2b',
        '$phycityb',
        '$phystateb',
        '$phyzipb',    
        '$phyphoneb',
        '$phyfaxb',
        '$phyemail',   
        '$phycellular',
        '$phypager', 
        '$phyupin',
        '$physsn',
        '$phydeg1',    
        '$phydeg2',
        '$phydeg3',
        '".addslashes($physpe1).             "',
        '".addslashes($physpe2).             "',
        '".addslashes($physpe3).             "',
        '".addslashes($phyid1).              "',
        '".addslashes($phystatus).           "',
        '".addslashes($phyref).              "',
        '".addslashes($phyrefcount).         "',
        '".addslashes($phyrefamt).           "',
        '".addslashes($phyrefcoll).          "',
        '".fm_join_from_array($phychargemap)."',
        '".fm_join_from_array($phyidmap).    "',
        NULL ) ";

      $result = fdb_query($query);

      if ($result) {
        echo "
	<$STDFONT_B>Done.<$STDFONT_E>
	";
      } else { // error!
        echo "
	<$STDFONT_B>Error! [$query, $result]<$STDFONT_E>
	";
      }  
    } else { // error
      echo "
        <P ALIGN=CENTER>
	<$STDFONT_B>Trouble! \$action=$action!<$STDFONT_E>
	</P>
      ";
    } // error handler
  } // if executing the action
  freemed_display_box_bottom();
 
 break; // master add/mod[form]

 case "display" :
  freemed_display_box_top("$record_name View");
  $phy = freemed_get_link_rec($id, "physician");
  echo "
    <TABLE>
     <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>Name : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <$STDFONT_B>$phy[phyfname] 
          $phy[phymname] $phy[phylname], $phy[phytitle]<$STDFONT_E>
     </TD></TR>

     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <$STDFONT_B><A HREF=\"physician.php3?$_auth&action=modform&id=$id\"
       >Modify $record_name</A><$STDFONT_E>
     </TD></TR>
    </TABLE>
  ";
  freemed_display_box_bottom();
 break;

 default:
  freemed_display_box_top("$record_name");
  $phy_q = "SELECT * FROM physician ORDER BY phylname,phyfname";
  $phy_r = fdb_query($phy_q);
  echo freemed_display_itemlist (
    $phy_r,
    "physician.php3",
    array (
      "Last Name" => "phylname",
      "First Name" => "phyfname"
    ),
    array (
      "",
      ""
    )
  );
  freemed_display_box_bottom();
 break;
} // master action switch

/*
if ($action=="addform") {

  freemed_display_box_top ("$Add_Physician", $page_name);
  echo "
    <BR>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
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

  // [code ported up top]

} elseif ($action=="modform") {

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting_Physician", $page_name, $_ref);

  $result = fdb_query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <BR><BR>
    <I>$Physician $id deleted</I>.
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

  $r = freemed_get_link_rec ($id, $db_name);

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
    ".fm_phone_assemble("phyphonea")."
     <!-- ($phyphonea1) $phyphonea2-$phyphonea3 -->
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Primary_Address_Fax # : <$STDFONT_E>
    </TD><TD>
    ".fm_phone_assemble("phyfaxa")."
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

} else { // view is now the default

  $query = "SELECT * FROM $db_name ".
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
           degrees WHERE id='$spe1'"));
        $_d1 = $r["degdegree"]; // get degree name
      } else {
        $_d1 = "";
      } // first degree

      if ($physpe2!="0") {
        $r = fdb_fetch_array(fdb_query("SELECT * FROM
           degrees WHERE id='$spe2'"));
        $_d2 = $r["degdegree"]; // get degree name
      } else {
        $_d2 = "";
      } // second specialty

      if ($physpe3!="0") {
        $r = fdb_fetch_array(fdb_query("SELECT * FROM
           degrees WHERE id='$spe3'"));
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

  } else {
    echo "\n<B>$No_physicians_found_with_that_criteria.</B>\n";
  }

} // view is now the default
*/
freemed_close_db (); // close the database

freemed_display_html_bottom ();

?>
