<?php
 // file: manage_bills.php3 
 // desc: manga patients bills
 // code: Fred Forester fforest@netcarrier.com 
 // lic : GPL, v2

 $page_name = "manage_bills.php3";
 $db_name = "procrec";
 $record_name = "Procrec";
 include ("global.var.inc");
 include ("freemed-functions.inc");
 

 freemed_open_db ($LoginCookie);

 freemed_display_html_top();
 freemed_display_banner();

 switch ($action) 
 { // master action switch
  case "list":
   // procduce a list only. Don't acutally process any bills
	$result = fdb_query ("SELECT DISTINCT patient.*
				FROM patient,procrec 
				WHERE patient.id = procrec.procpatient 
				AND procrec.procbalcurrent > 0 ORDER BY ptlname");
	if ($result)
	{
		freemed_display_box_top($record_name, $_ref, $page_name);
		freemed_display_actionbar($page_name, $_ref);

    		echo "
      		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
      		<TR>
       		<TD><B>Name</B></TD>
       		<TD><B>Payments</B></TD>
       		<TD><B>Procedures</B></TD>
      		</TR>
    		"; // header of box

    		$_alternate = freemed_bar_alternate_color ();
		while ($r = fdb_fetch_array($result)) 
		{

      			$ptlname  = $r["ptlname"  ] ;
      			$ptfname  = $r["ptfname"  ] ;
      			$id        = $r["id"        ] ;

        		// alternate the bar color
     			$_alternate = freemed_bar_alternate_color ($_alternate);

      			echo "
        			<TR BGCOLOR=$_alternate>
        			<TD><A HREF=
         			\"patient.php3?$_auth&id=$id&action=display\"
         			>$ptlname, $ptfname</A></TD>
        			<TD><A HREF=
         			\"manage_payment_records.php3?$_auth&id=$id&patient=$id&bills=yes\"
         			><FONT SIZE=-1>View/Manage</FONT></A></TD>
        			<TD><A HREF=
         			\"procedure.php3?$_auth&id=$id&patient=$id\"
         			><FONT SIZE=-1>View/Manage</FONT></A></TD>
      				";


    		} // while there are no more

	 	echo "
      		</TABLE>
    		"; // end table (fixed 19990617)
	
		
		freemed_display_box_bottom();



	} // end of result set
        break;
	

//        echo freemed_display_itemlist(
//    		$result,
//    		"manage_payment_records.php3",
//    		array ( // control
//      		_("Last Name")       => "ptlname",
//      		_("First Name")      => "ptfname"
//    	),array ("","",""),
//        "", "", "",ITEMLIST_VIEW,"yes");
//	  freemed_display_box_bottom();
//        break;
 }  // end action


 freemed_display_html_bottom ();
 freemed_close_db ();
?>
