<?php
 // $Id$
 // desc: fixed type forms editing engine
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class FixedFormsMaintenance extends MaintenanceModule {

	var $MODULE_NAME	= "Fixed Forms Maintenance";
	var $MODULE_AUTHOR	= "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION	= "0.1";
	var $MODULE_DESCRIPTION = "
		Fixed forms can be used when generating fixed-column
		reports and text output. These are mainly used with
		insurance/form filing and generation of custom forms
		for internal facility use.
	";
	var $MODULE_FILE	= __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name	= "Fixed Form";
	var $table_name		= "fixedform";

	var $variables		= array (
		"ffname",
		"ffdescrip",
		"fftype",
		"ffpagelength",
		"fflinelength",
		"ffloopnum",
		"ffloopoffset",
		"ffcheckchar",
		"ffrow",
		"ffcol",
		"fflength",
		"ffdata",
		"ffformat",
		"ffcomment"
	);

	function FixedFormsMaintenance () {
		// Table definition
		$this->table_definition = array (
			'ffname' => SQL_VARCHAR(50),
			'ffdescrip' => SQL_VARCHAR(100),
			'fftype' => SQL_INT_UNSIGNED(0),
			'ffpagelength' => SQL_INT_UNSIGNED(0),
			'fflinelength' => SQL_INT_UNSIGNED(0),
			'ffloopnum' => SQL_INT_UNSIGNED(0),
			'ffloopoffset' => SQL_INT_UNSIGNED(0),
			'ffcheckchar' => SQL_CHAR(1),
			'ffrow' => SQL_TEXT,
			'ffcol' => SQL_TEXT,
			'fflength' => SQL_TEXT,
			'ffdata' => SQL_TEXT,
			'ffformat' => SQL_TEXT,
			'ffcomment' => SQL_TEXT,
			'id' => SQL_SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor FixedFormsMaintenance

	function addform() {
		global $display_buffer;
		
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$display_buffer .= "
		 <FORM ACTION=\"$this->page_name\" METHOD=POST>
		  <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
		  <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
		  <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\""."add"."\">
		  <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
		 <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
		  ALIGN=CENTER>
		 <TR>
		  <TD ALIGN=RIGHT>"._("Name of Form")." : </TD>
		   <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffname\" SIZE=40 MAXLENGTH=50
			VALUE=\"".prepare($ffname)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Page Length")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffpagelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffpagelength)."\">
		  </TD>
		 </TR><TR>
		  <TD ALIGN=RIGHT>"._("Description")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffdescrip\" SIZE=40 MAXLENGTH=100
			VALUE=\"".prepare($ffdescrip)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Line Length")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"fflinelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($fflinelength)."\">
		  </TD>
		 </TR><TR>
		  <TD ALIGN=RIGHT>"._("Loop Repetitions")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffloopnum\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffloopnum)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Check Char")." : <BR>
						  "._("(<I>example: \"X\"</I>)")."</TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffcheckchar\" SIZE=2 MAXLENGTH=1
			VALUE=\"".prepare($ffcheckchar)."\"> 
		  </TD>
		 </TR><TR>
		  <TD ALIGN=RIGHT>"._("Loop Line Offset")." : <BR>
					 "._("(<I>\"1\" skips to the next line</I>)")."</TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffloopoffset\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffloopoffset)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Type")." : </TD>
		  <TD ALIGN=LEFT>
		   <SELECT NAME=\"fftype\">
			<OPTION VALUE=\"0\" ".
			  ( ($fftype==0) ? "SELECTED" : "" ).">"._("Generic")."
			<OPTION VALUE=\"1\" ".
			  ( ($fftype==1) ? "SELECTED" : "" ).">"._("Insurance Claim")."
			<OPTION VALUE=\"2\" ".
			  ( ($fftype==2) ? "SELECTED" : "" ).">"._("Patient Bill")."
			<OPTION VALUE=\"3\" ".
			  ( ($fftype==3) ? "SELECTED" : "" ).">"."NSF Format"."
			<OPTION VALUE=\"4\" ".
			  ( ($fftype==4) ? "SELECTED" : "" ).">"."EMR Report"."
		   </SELECT>
		  </TD>
		 </TR>
		 </TABLE>
		 <P>
	 	 <CENTER><INPUT TYPE=SUBMIT VALUE=\""._("Add")."\"></CENTER>
		 </FORM>
		";
		$display_buffer .= "
		<P>
		<CENTER>
		<A HREF=\"$this->page_name?module=$module\"
		 >"._("Abandon Modification")."</A>
		</CENTER>
	  	";


	}

	function modform() {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

      	if ($id<1) 
		{
			$display_buffer .= "
			 "._("You must select a record to modify.")."
			";
			template_display();
		}
		//$display_buffer .= "modform<BR>";
      	if ($been_here != "yes") 
		{
         	// now we extract the data, since the record was given...
        	$query  = "SELECT * FROM $this->table_name WHERE id='$id'";
			$result = $sql->query ($query);
			$r      = $sql->fetch_array ($result);
			extract ($r); 
			$row          = fm_split_into_array ($ffrow);
			$col          = fm_split_into_array ($ffcol);
			$len          = fm_split_into_array ($fflength);
			$data         = fm_split_into_array ($ffdata);
			$format       = fm_split_into_array ($ffformat);
			$comment      = fm_split_into_array ($ffcomment);
			$maxlines = 50;
			//create color array 
			$numcolors = count($row);
			$colors[0] = "#000000";
			$colors = array_pad($colors,$numcolors,"#000000");
		} // end checking if we have been here yet...


		if ($editaction=="save")
		{
			// replace file data with screen content
			$this->DoSaveScreen($row,$col,$len,$data,$format,$comment,$start,$last);
			$this->mod();
			return;
		}

		if ($editaction=="copyform")
		{
			$ffdescrip = $ffdescrip." Copy";
			$ffname = $ffname." Copy";
			$this->add();
			return;
		}
   		// set the fftype properly
   		for ($i=0;$i<=20;$i++) ${"_type_".$i} = "";
   			${"_type_".$fftype} = "SELECTED";

		if ($editaction=="insert")
			$this->DoInsert($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction=="insertb")
			$this->DoInsertB($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction=="copy")	
			$this->DoCopy($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction=="reseq")	
			$this->DoResq($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction=="delete")	
			$this->DoDelete($row,$col,$len,$data,$format,$comment,$mark,$linecount);

		$display_buffer .= "
		 <FORM ACTION=\"$this->page_name\" METHOD=POST>
		  <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
		  <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
		  <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\""."modform"."\">
		  <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">";

		//$line_total = count($row); 
        //$display_buffer .= "total $line_total<BR>";

        //if ($line_total != 0)
        //{
        //  for($i=0;$i<$line_total;$i++)
        //  {
        //        $display_buffer .= "
        //          <INPUT TYPE=HIDDEN NAME=\"row$brackets\" VALUE=\"".prepare($row[$i])."\">
        //          <INPUT TYPE=HIDDEN NAME=\"col$brackets\" VALUE=\"".prepare($col[$i])."\">
        //          <INPUT TYPE=HIDDEN NAME=\"len$brackets\" VALUE=\"".prepare($len[$i])."\">
        //          <INPUT TYPE=HIDDEN NAME=\"data$brackets\" VALUE=\"".prepare($data[$i])."\">
        //          <INPUT TYPE=HIDDEN NAME=\"format$brackets\" VALUE=\"".prepare($format[$i])."\">
        //          <INPUT TYPE=HIDDEN NAME=\"comment$brackets\" VALUE=\"".prepare($comment[$i])."\">
        //          <INPUT TYPE=HIDDEN NAME=\"colors$brackets\" VALUE=\"".prepare($colors[$i])."\">
        //          ";
        //  }
        //}


		$display_buffer .= "
		 <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
		  ALIGN=CENTER>
		 <TR>
		  <TD ALIGN=RIGHT>"._("Name of Form")." : </TD>
		   <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffname\" SIZE=40 MAXLENGTH=50
			VALUE=\"".prepare($ffname)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Page Length")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffpagelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffpagelength)."\">
		  </TD>
		 </TR><TR>
		  <TD ALIGN=RIGHT>"._("Description")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffdescrip\" SIZE=40 MAXLENGTH=100
			VALUE=\"".prepare($ffdescrip)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Line Length")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"fflinelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($fflinelength)."\">
		  </TD>
		 </TR><TR>
		  <TD ALIGN=RIGHT>"._("Loop Repetitions")." : </TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffloopnum\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffloopnum)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Check Char")." : <BR>
						  "._("(<I>example: \"X\"</I>)")."</TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffcheckchar\" SIZE=2 MAXLENGTH=1
			VALUE=\"".prepare($ffcheckchar)."\"> 
		  </TD>
		 </TR><TR>
		  <TD ALIGN=RIGHT>"._("Loop Line Offset")." : <BR>
					 "._("(<I>\"1\" skips to the next line</I>)")."</TD>
		  <TD ALIGN=LEFT>
		   <INPUT TYPE=TEXT NAME=\"ffloopoffset\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffloopoffset)."\">
		  </TD>
		  <TD ALIGN=RIGHT>"._("Type")." : </TD>
		  <TD ALIGN=LEFT>
		   <SELECT NAME=\"fftype\">
			<OPTION VALUE=\"0\" ".
			  ( ($fftype==0) ? "SELECTED" : "" ).">"._("Generic")."
			<OPTION VALUE=\"1\" ".
			  ( ($fftype==1) ? "SELECTED" : "" ).">"._("Insurance Claim")."
			<OPTION VALUE=\"2\" ".
			  ( ($fftype==2) ? "SELECTED" : "" ).">"._("Patient Bill")."
			<OPTION VALUE=\"3\" ".
			  ( ($fftype==3) ? "SELECTED" : "" ).">"."NSF Format"."
		   </SELECT>
		  </TD>
		 </TR>
		 </TABLE>
		 <P>
		 <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
		  ALIGN=CENTER>
		  <TR CLASS=\"reverse\">
		   <TD>#</TD>
		   <TD ALIGN=\"CENTER\"><B>"._("Mark")."</B></TD>
		   <TD><B>"._("Row/Line")."</B></TD>
		   <TD><B>"._("Column")."</B></TD>
		   <TD><B>"._("Length")."</B></TD>
		   <TD><B>"._("Data")."</B></TD>
		   <TD><B>"._("Format")."</B></TD>
		   <TD><B>"._("Comment")."</B></TD>
		  </TR>
		 ";


		
   		$line_total = count($row); // previous # of lines
		if ($editaction=="refresh")
		{
			//make all black
			for ($i=0;$i<$line_total;$i++)
			{
				$colors[$i] = "#000000";
			}
		}

		//$display_buffer .= "maxline $maxlines <BR>";
		if (!isset($start))
		{
			$start=0;
			$last = ($line_total > $maxlines) ? $maxlines : $line_total;
			//$display_buffer .= "first end $last<BR>";
	
		}

		if ($editaction=="next")
		{
			// replace file data with screen content
			$this->DoSaveScreen($row,$col,$len,$data,$format,$comment,$start,$last);

			//$display_buffer .= "last $last<BR>";
			$start = $start + $maxlines;
			$last = $last + $maxlines;
			//$display_buffer .= "last $last<BR>";
			if ($start > $line_total)
			{
				$start = 0;
				$last = ($line_total > $maxlines) ? $maxlines : $line_total;
			
			}
			else
			if ($last > $line_total)
			{
				$last = $line_total;
			}
		}

		if ($editaction=="prev")
		{
			// replace file data with screen content
			$this->DoSaveScreen($row,$col,$len,$data,$format,$comment,$start,$last);

			//$display_buffer .= "last $last<BR>";
			$start = $start - $maxlines;
			$last = $start + $maxlines;
			//$display_buffer .= "last $last<BR>";
			if ($start < 0)
			{
				$start = 0;
			}
			if ($last <= 0)
			{
				$last = ($line_total > $maxlines) ? $maxlines : $line_total;
			}
		}
		
		//$display_buffer .= "start last $start $last<BR>";
		if ($line_total != 0)
		{

			for($i=$start;$i<$last;$i++)
			{
				$display_buffer .= "
				 <TR CLASS=\"".freemed_alternate()."\">
				  <TD><FONT COLOR=\"$colors[$i]\">".($i+1)."</FONT></TD>
				  <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"mark$brackets\" VALUE=\"$i\"></CENTER></TD>
				  <TD><INPUT TYPE=TEXT NAME=\"drow$brackets\" SIZE=5
					MAXLENGTH=3 VALUE=\"".prepare($row[$i])."\"></TD>
				  <TD><INPUT TYPE=TEXT NAME=\"dcol$brackets\" SIZE=5
					MAXLENGTH=3 VALUE=\"".prepare($col[$i])."\"></TD>
				  <TD><INPUT TYPE=TEXT NAME=\"dlen$brackets\" SIZE=5
					MAXLENGTH=3 VALUE=\"".prepare($len[$i])."\"></TD>
				  <TD><INPUT TYPE=TEXT NAME=\"ddata$brackets\" SIZE=20
					MAXLENGTH=100 VALUE=\"".prepare($data[$i])."\"></TD>
				  <TD><INPUT TYPE=TEXT NAME=\"dformat$brackets\" SIZE=5
					MAXLENGTH=100 VALUE=\"".prepare($format[$i])."\"></TD>
				  <TD><INPUT TYPE=TEXT NAME=\"dcomment$brackets\" SIZE=20
					MAXLENGTH=100 VALUE=\"".prepare($comment[$i])."\"></TD>
				 </TR>
				 ";
			}
			
		}
		

		// display the bottom of the repetitive table
		$display_buffer .= "
		  </TABLE>
		   <INPUT TYPE=HIDDEN NAME=\"start\" VALUE=\"".prepare($start)."\">
		   <INPUT TYPE=HIDDEN NAME=\"last\" VALUE=\"".prepare($last)."\">
		   <INPUT TYPE=HIDDEN NAME=\"maxlines\" VALUE=\"".prepare($maxlines)."\">";

        if ($line_total != 0)
        {
          for($i=0;$i<$line_total;$i++)
          {
                $display_buffer .= "
                  <INPUT TYPE=HIDDEN NAME=\"row$brackets\" VALUE=\"".prepare($row[$i])."\">
                  <INPUT TYPE=HIDDEN NAME=\"col$brackets\" VALUE=\"".prepare($col[$i])."\">
                  <INPUT TYPE=HIDDEN NAME=\"len$brackets\" VALUE=\"".prepare($len[$i])."\">
                  <INPUT TYPE=HIDDEN NAME=\"data$brackets\" VALUE=\"".prepare($data[$i])."\">
                  <INPUT TYPE=HIDDEN NAME=\"format$brackets\" VALUE=\"".prepare($format[$i])."\">
                  <INPUT TYPE=HIDDEN NAME=\"comment$brackets\" VALUE=\"".prepare($comment[$i])."\">
                  <INPUT TYPE=HIDDEN NAME=\"colors$brackets\" VALUE=\"".prepare($colors[$i])."\">
                  ";
          }
        }
		$display_buffer .= "
		  <P>
		  <CENTER>
		  <FONT SIZE=\"-1\">Line Count :
		   <INPUT TYPE=TEXT NAME=\"linecount\" VALUE=\"0\"
			SIZE=2 MAXLENGTH=2>
		  </FONT>
		  </CENTER>
		  <BR>
		  <CENTER>
		  <SELECT NAME=\"editaction\">
		   <OPTION VALUE=\"next\">Next
		   <OPTION VALUE=\"prev\">Prev
		   <OPTION VALUE=\"refresh\">Refresh
		   <OPTION VALUE=\"copy\">Copy After
		   <OPTION VALUE=\"reseq\">ReSequence
		   <OPTION VALUE=\"insert\">Insert After
		   <OPTION VALUE=\"insertb\">Insert Before
		   <OPTION VALUE=\"delete\">Delete
		   <OPTION VALUE=\"copyform\">Copy form
		   <OPTION VALUE=\"save\">Save
		  </SELECT>
		  <INPUT TYPE=SUBMIT VALUE=\"go!\">
		  </CENTER>
		";
		$display_buffer .= "
		<P>
		<CENTER>
		<A HREF=\"$this->page_name?module=$module\"
		 >"._("Menu")."</A>
		</CENTER>
	  	";


	
	} // end modform

	function DoDelete(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount) {
		global $display_buffer;
		$numrows = count($row);
		if ($numrows == 0)
			return false;
	
		//$display_buffer .= "numrows before $numrows<BR>";
		$n=0;	
		for ($i=0;$i<$numrows;$i++)
		{
			if (!fm_value_in_array($mark,$i))
			{
				$newrow[$n] = $row[$i];
				$newcol[$n] = $col[$i];
				$newlen[$n] = $len[$i];
				$newdata[$n] = $data[$i];
				$newformat[$n] = $format[$i];
				$newcomment[$n] = $comment[$i];
				$newcolor[$n] = "#000000";
				$n++;
				//$display_buffer .= "$i $n <BR>";
			}
		}
		$row = $newrow;
		$col = $newcol;
		$len = $newlen;
		$data = $newdata;
		$format = $newformat;
		$comment = $newcomment;
		//$numrows = count($row);
		//$display_buffer .= "numrows after $numrows<BR>";
		return true;
		
		
	}

	function DoInsertB(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount,&$colors) {
		global $display_buffer;
		// insert before
		$numrows = count($row);
		if ($numrows == 0)
		{
			$row[0] = "0";
			$col[0] = "0";
			$len[0] = "0";
			$data[0] = "X";
			$format[0] = "X";
			$comment[0] = "X";
			$colors[0] = "#ff0000"; // show inserts as red;
			return true;
		}

		if ($linecount == 0)
			return false;

		$gotrows = true;
		$i=0;
		$n=0;
		while($gotrows)
		{

			if (fm_value_in_array($mark,$i))  // got a hit!
			{
				for ($x=0;$x<$linecount;$x++)
				{
					$newrow[$n] = "0";
					$newcol[$n] = "0";
					$newlen[$n] = "0";
					$newdata[$n] = "X";
					$newformat[$n] = "X";
					$newcomment[$n] = "X";
					$newcolors[$n] = "#ff0000";  // show insert as red
					$n++;
				}

			}

			$newrow[$n] = $row[$i];
			$newcol[$n] = $col[$i];
			$newlen[$n] = $len[$i];
			$newdata[$n] = $data[$i];
			$newformat[$n] = $format[$i];
			$newcomment[$n] = $comment[$i];
			$newcolors[$n] = "#000000"; 
			$i++;
			$n++;

			//$display_buffer .= "$i $n <BR>";
			if ($i == $numrows)
				$gotrows = false;
		} // end gotrows

		$row = $newrow;
		$col = $newcol;
		$len = $newlen;
		$data = $newdata;
		$format = $newformat;
		$comment = $newcomment;
		$colors = $newcolors;
		return true;

	} // end do insert before

	function DoInsert(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount,&$colors) {
		global $display_buffer;
		// insert after 

		$numrows = count($row);
		if ($numrows == 0)
		{
			$row[0] = "0";
			$col[0] = "0";
			$len[0] = "0";
			$data[0] = "X";
			$format[0] = "X";
			$comment[0] = "X";
			$colors[0] = "#ff0000";  // show insert as red
			return true;
		}

		if ($linecount == 0)
			return false;
		

		//$display_buffer .= "numrows before $numrows<BR>";

		$gotrows = true;
		$i=0;
		$n=0;
		while($gotrows)
		{
			$newrow[$n] = $row[$i];
			$newcol[$n] = $col[$i];
			$newlen[$n] = $len[$i];
			$newdata[$n] = $data[$i];
			$newformat[$n] = $format[$i];
			$newcomment[$n] = $comment[$i];
			$newcolors[$n] = "#000000"; 

			if (fm_value_in_array($mark,$i))  // got a hit!
			{
				for ($x=0;$x<$linecount;$x++)
				{
					$n++;
					$newrow[$n] = "0";
					$newcol[$n] = "0";
					$newlen[$n] = "0";
					$newdata[$n] = "X";
					$newformat[$n] = "X";
					$newcomment[$n] = "X";
					$newcolors[$n] = "#ff0000";  // show insert as red
				}

			}
			$i++;
			$n++;
			//$display_buffer .= "$i $n <BR>";
			if ($i == $numrows)
				$gotrows = false;

		} // end gotrows
		$row = $newrow;
		$col = $newcol;
		$len = $newlen;
		$data = $newdata;
		$format = $newformat;
		$comment = $newcomment;
		$colors = $newcolors;

		//$numrows = count($row);
		//$display_buffer .= "numrows after $numrows<BR>";
		return;

	} //end doinsert

	function DoCopy(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount,&$colors) {
		global $display_buffer;
		
		$numrows = count($row);
		if ($numrows == 0)
			return false;

		$nummarks = count($mark);
		if ( ($nummarks < 2) OR ($nummarks > 3) )
			return false;

		$i=0;
		while (list($k,$v)=each($mark)) 
		{
			$locations[$i] = $v;
			$i++;
		}
		$from = $locations[0];
		$to = $locations[1];
		if ($nummarks == 3)
		{
			$after = $locations[2];
			$after++;
		}
		else
		{
			$after = $to+1;
		}

		$to = ($to - $from) + 1; // num to get
		
		//$display_buffer .= "from to after marks $from $to $after $nummarks<BR>";

		$newrow = array_slice($row,$from,$to);
		$newcol = array_slice($col,$from,$to);
		$newlen = array_slice($len,$from,$to);
		$newdata = array_slice($data,$from,$to);
		$newformat = array_slice($format,$from,$to);
		$newcomment = array_slice($comment,$from,$to);
		$newcolors = array_slice($colors,$from,$to);

		$numnew = count($newrow);
		//$display_buffer .= "newrows $numnew<BR>";
		for ($i=0;$i<$numnew;$i++)
			$newcolors[$i] = "#ff0000";

		array_splice($row,$after,0,$newrow);
		array_splice($col,$after,0,$newcol);
		array_splice($len,$after,0,$newlen);
		array_splice($data,$after,0,$newdata);
		array_splice($format,$after,0,$newformat);
		array_splice($comment,$after,0,$newcomment);
		array_splice($colors,$after,0,$newcolors);
		

	}

	function DoResq(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount,&$colors) {
		global $display_buffer;
		if ($linecount == 0)
			return false;

		$numrows = count($row);
		if ($numrows == 0)
			return false;

		$nummarks = count($mark);
		if ($nummarks == 0)
			return false;


		$i=0;
		while (list($k,$v)=each($mark)) 
		{
			$locations[$i] = $v;
			$i++;
		}
		$from = $locations[0];
		$to = $locations[1];
		$to++;
		//$display_buffer .= "from to marks $from $to $nummarks<BR>";

		// all colors black
		$numcolors = count($row);
		$colors[0] = "#000000";
		$colors = array_pad($colors,$numcolors,"#000000");

		for ($i=$from;$i<$to;$i++)
		{
			//$display_buffer .= "row before $row[$i]<BR>";
			$row[$i] += $linecount;
			$colors[$i] = "#ff0000";  // show red for copied lines
			//$display_buffer .= "row after $row[$i]<BR>";

		}	
		

	}

	function DoSaveScreen(&$row,&$col,&$len,&$data,&$format,&$comment,&$start,&$last) {
		global $display_buffer;
		global $drow,$dcol,$dlen,$ddata,$dformat,$dcomment;
		//$display_buffer .= "data start $ddata[$start]<BR>";
		$screen=0;
		for ($i=$start;$i<$last;$i++)
		{
			//$display_buffer .= "before $data[$i] $ddata[$screen]<BR>";
			$col[$i] = $dcol[$screen];
			$row[$i] = $drow[$screen];
			$len[$i] = $dlen[$screen];
			$data[$i] = $ddata[$screen];
			$format[$i] = $dformat[$screen];
			$comment[$i] = $dcomment[$screen];
			//$display_buffer .= "after $data[$i] $ddata[$screen]<BR>";
			$screen++;
		}
		
	}

	function add () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		global $ffrow,$ffcol,$fflength,$ffdata,$ffformat,$ffcomment;
		$ffrow     = fm_join_from_array ($row    );
		$ffcol     = fm_join_from_array ($col    );
		$fflength  = fm_join_from_array ($len    );
		$ffdata    = fm_join_from_array ($data   );
		$ffformat  = fm_join_from_array ($format );
		$ffcomment = fm_join_from_array ($comment);
		$this->_add();
	} // end function FixedFormsMaintenance->add

	function mod () {
		global $display_buffer;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;
		global $ffrow,$ffcol,$fflength,$ffdata,$ffformat,$ffcomment;
		$ffrow     = fm_join_from_array ($row    );
		$ffcol     = fm_join_from_array ($col    );
		$fflength  = fm_join_from_array ($len    );
		$ffdata    = fm_join_from_array ($data   );
		$ffformat  = fm_join_from_array ($format );
		$ffcomment = fm_join_from_array ($comment);
		$this->_mod();
	} // end function FixedFormsMaintenance->mod

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT * FROM $this->table_name ".
				"ORDER BY ffname, ffdescrip"),
			$this->page_name,
			array (
				_("Name")			=>	"ffname",
				_("Description")	=>	"ffdescrip"
			),
			array ( "", _("NO DESCRIPTION") )
		);  
	} // end function FixedFormsMaintenance->view     

} // end class FixedFormsMaintenance

register_module ("FixedFormsMaintenance");

?>
