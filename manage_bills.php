<?php
 // $Id$
 // desc: manage patients bills
 // code: Fred Forester <fforest@netcarrier.com>
 // lic : GPL, v2

 $page_name = "manage_bills.php";
 $db_name = "procrec";
 $record_name = "Manage Patient Bills";
 include ("lib/freemed.php");
 include ("lib/API.php");
 

 freemed_open_db ($LoginCookie);

 freemed_display_html_top();
 freemed_display_banner();

 switch ($action) 
 { // master action switch
  case "list":
   // procduce a list only. Don't acutally process any bills
	$result = $sql->query ("SELECT DISTINCT patient.*
				FROM patient,procrec 
				WHERE patient.id = procrec.procpatient 
				AND procrec.procbalcurrent > 0 ORDER BY ptlname");
	if ($result)
	{
		freemed_display_box_top($record_name, $_ref, $page_name);
		freemed_display_actionbar($page_name, $_ref);

    		echo "
      		<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 WIDTH=100%>
      		<TR>
       		<TD><B>Name</B></TD>
                <TD COLSPAN=2 ALIGN=CENTER><B>Billing Functions</B></TD>
       		<TD><B>Procedures</B></TD>
       		<TD><B>Billed</B></TD>
      		</TR>
    		"; // header of box

		while ($r = $sql->fetch_array($result)) 
		{

      			$ptlname  = $r["ptlname"  ] ;
      			$ptfname  = $r["ptfname"  ] ;
      			$id        = $r["id"        ] ;

        		// alternate the bar color

      			echo "
        			<TR BGCOLOR=\"".(
     			$_alternate = freemed_bar_alternate_color ()
					)."\">
        			<TD><A HREF=
         			\"patient.php?$_auth&id=$id&action=display\"
         			>$ptlname, $ptfname</A></TD>
        			<TD><A HREF=
         			\"manage_payment_records.php?$_auth&id=$id&patient=$id&bills=yes\"
         			><FONT SIZE=-1>View/Manage</FONT></A></TD>
        			<TD><A HREF=
         			\"payment_record.php?_ref=$page_name&id=$id&patient=$id\"
         			><FONT SIZE=-1>Patient Ledger</FONT></A></TD>
        			<TD><A HREF=
         			\"module_loader.php?$_auth&module=procedureModule&id=$id&patient=$id\"
         			><FONT SIZE=-1>View/Manage</FONT></A></TD>
      				";
 			// see if all procs are billed. if not then show No
         		$billed_result = $sql->query("SELECT COUNT(*) FROM procrec where
                                                        procpatient='$id' AND procbilled='0'
                                                        AND procbalcurrent>'0'");
                        $billed = $sql->fetch_array($billed_result);
                        if ($billed)
                        {
                                if ($billed[0] > 0)
                                        echo "<TD> <FONT COLOR=#ff0000>&nbspNO&nbsp</FONT></TD>";
                                else
                                        echo "<TD>YES</TD>";
                        }


    		} // while there are no more

	 	echo "
      		</TABLE>
    		"; // end table (fixed 19990617)
	
		
		freemed_display_box_bottom();



	} // end of result set
        break;
	

//        echo freemed_display_itemlist(
//    		$result,
//    		"manage_payment_records.php",
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
