<?php
 // $Id$
 // desc: fixed type forms editing engine
 // lic : GPL, v2

LoadObjectDependency('_FreeMED.MaintenanceModule');

class FixedFormsMaintenance extends MaintenanceModule {

	var $MODULE_NAME	= "Fixed Forms Maintenance";
	var $MODULE_AUTHOR	= "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION	= "0.2";
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
			'ffname' => SQL__VARCHAR(50),
			'ffdescrip' => SQL__VARCHAR(100),
			'fftype' => SQL__INT_UNSIGNED(0),
			'ffpagelength' => SQL__INT_UNSIGNED(0),
			'fflinelength' => SQL__INT_UNSIGNED(0),
			'ffloopnum' => SQL__INT_UNSIGNED(0),
			'ffloopoffset' => SQL__INT_UNSIGNED(0),
			'ffcheckchar' => SQL__CHAR(1),
			'ffrow' => SQL__TEXT,
			'ffcol' => SQL__TEXT,
			'fflength' => SQL__TEXT,
			'ffdata' => SQL__TEXT,
			'ffformat' => SQL__TEXT,
			'ffcomment' => SQL__TEXT,
			'id' => SQL__SERIAL
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
		 <tr>
		  <td ALIGN=\"RIGHT\">".__("Name of Form")." : </td>
		   <td ALIGN=\"LEFT\">
		   <INPUT TYPE=\"TEXT\" NAME=\"ffname\" SIZE=40 MAXLENGTH=50
			VALUE=\"".prepare($ffname)."\">
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Page Length")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffpagelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffpagelength)."\">
		  </td>
		 </tr><tr>
		  <td ALIGN=\"RIGHT\">".__("Description")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffdescrip\" SIZE=40 MAXLENGTH=100
			VALUE=\"".prepare($ffdescrip)."\">
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Line Length")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"fflinelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($fflinelength)."\">
		  </td>
		 </tr><tr>
		  <td ALIGN=\"RIGHT\">".__("Loop Repetitions")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffloopnum\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffloopnum)."\">
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Check Char")." : <BR>
						  ".__("(<I>example: \"X\"</I>)")."</td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffcheckchar\" SIZE=2 MAXLENGTH=1
			VALUE=\"".prepare($ffcheckchar)."\"> 
		  </td>
		 </tr><tr>
		  <td ALIGN=\"RIGHT\">".__("Loop Line Offset")." : <BR>
					 ".__("(<I>\"1\" skips to the next line</I>)")."</td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffloopoffset\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffloopoffset)."\">
		  </td>
		  <td ALIGN=RIGHT>".__("Type")." : </td>
		  <td ALIGN=LEFT>
		   <SELECT NAME=\"fftype\">
			<OPTION VALUE=\"0\" ".
			  ( ($fftype==0) ? "SELECTED" : "" ).">".__("Generic")."
			<OPTION VALUE=\"1\" ".
			  ( ($fftype==1) ? "SELECTED" : "" ).">".__("Insurance Claim")."
			<OPTION VALUE=\"2\" ".
			  ( ($fftype==2) ? "SELECTED" : "" ).">".__("Patient Bill")."
			<OPTION VALUE=\"3\" ".
			  ( ($fftype==3) ? "SELECTED" : "" ).">"."NSF Format"."
			<OPTION VALUE=\"4\" ".
			  ( ($fftype==4) ? "SELECTED" : "" ).">"."EMR Report"."
		   </SELECT>
		  </td>
		 </tr>
		 </table>
		 <p/>
	 	 <div align=\"CENTER\">
		 <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Add")."\"/>
		 <input class=\"button\" type=\"submit\" name=\"submit\" value=\"".__("Cancel")."\"/>
		 </div>
		 </form>
		";
	}

	function modform() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

	    	if ($id<1) {
			$display_buffer .= "
			 ".__("You must select a record to modify.")."
			";
			template_display();
		}
		//$display_buffer .= "modform<BR>";
	      	if ($been_here != "yes") {
         	// now we extract the data, since the record was given...
	        	$query  = "SELECT * FROM ".
				$this->table_name." ".
				"WHERE id='".addslashes($id)."'";
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

		if ($editaction==__("Cancel")) {
			$action = '';
			$this->view();
			return;
		}

		if ($editaction==__("Save"))
		{
			// replace file data with screen content
			$this->DoSaveScreen($row,$col,$len,$data,$format,$comment,$start,$last);
			$this->mod();
			return;
		}

		if ($editaction==__("Copy Form"))
		{
			$ffdescrip = $ffdescrip." Copy";
			$ffname = $ffname." Copy";
			$this->add();
			return;
		}
   		// set the fftype properly
   		for ($i=0;$i<=20;$i++) ${"_type_".$i} = "";
   			${"_type_".$fftype} = "SELECTED";

		//print "editaction = ".$editaction."<br/>\n";
		if ($editaction==__("Insert After"))
			$this->DoInsert($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction==__("Insert Before"))
			$this->DoInsertB($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction==__("Copy"))	
			$this->DoCopy($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction==__("Resequence"))	
			$this->DoResq($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);
		if ($editaction==_("Delete"))	
			$this->DoDelete($row,$col,$len,$data,$format,$comment,$mark,$linecount,$colors);

		//print "count(row) = ".count($row)."<br/>\n";
		$display_buffer .= "
		 <form ACTION=\"$this->page_name\" METHOD=\"POST\">
		  <INPUT TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"yes\"/>
		  <INPUT TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		  <INPUT TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\""."modform"."\"/>
		  <INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"$id\"/>\n";

		//$line_total = count($row); 
        //$display_buffer .= "total $line_total<br/>";

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
		 <tr>
		  <td ALIGN=\"RIGHT\">".__("Name of Form")." : </td>
		   <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffname\" SIZE=40 MAXLENGTH=50
			VALUE=\"".prepare($ffname)."\">
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Page Length")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffpagelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($ffpagelength)."\">
		  </td>
		 </tr><tr>
		  <td ALIGN=\"RIGHT\">".__("Description")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"ffdescrip\" SIZE=40 MAXLENGTH=100
			VALUE=\"".prepare($ffdescrip)."\">
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Line Length")." : </td>
		  <td ALIGN=\"LEFT\">
		   <INPUT TYPE=TEXT NAME=\"fflinelength\" SIZE=5 MAXLENGTH=5
			VALUE=\"".prepare($fflinelength)."\">
		  </td>
		 </tr><tr>
		  <td ALIGN=\"RIGHT\">".__("Loop Repetitions")." : </td>
		  <td ALIGN=\"LEFT\">
		   <input TYPE=\"TEXT\" NAME=\"ffloopnum\" SIZE=\"5\" ".
		   	"MAXLENGTH=\"5\" VALUE=\"".prepare($ffloopnum)."\"/>
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Check Char")." : <BR>
						  ".__("(<I>example: \"X\"</I>)")."</td>
		  <td ALIGN=\"LEFT\">
		   <input TYPE=\"TEXT\" NAME=\"ffcheckchar\" SIZE=\"2\" ".
		   	"MAXLENGTH=\"1\" VALUE=\"".prepare($ffcheckchar)."\"/> 
		  </td>
		 </tr><tr>
		  <td ALIGN=RIGHT>".__("Loop Line Offset")." : <BR>
					 ".__("(<I>\"1\" skips to the next line</I>)")."</td>
		  <td ALIGN=\"LEFT\">
		  ".html_form::text_widget('ffloopoffset', 5)."
		  </td>
		  <td ALIGN=\"RIGHT\">".__("Type")." : </td>
		  <td ALIGN=\"LEFT\">
		  ".html_form::select_widget(
		  	'fftype',
			array(
				__("Generic") => '0',
				__("Insurance Claim") => '1',
				__("Patient Bill") => '2',
				__("NSF Format") => '3',
				__("EMR Report") => '4'
			)
		  )."
		  </td>
		 </tr>
		 </table>
		 <p/>
		 <table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\"
		  BORDER=\"0\" VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
		  <tr CLASS=\"reverse\">
		   <td>#</td>
		   <td ALIGN=\"CENTER\"><b>".__("Mark")."</b></td>
		   <td><b>".__("Row/Line")."</b></td>
		   <td><b>".__("Column")."</b></td>
		   <td><b>".__("Length")."</b></td>
		   <td><b>".__("Data")."</b></td>
		   <td><b>".__("Format")."</b></td>
		   <td><b>".__("Comment")."</b></td>
		  </tr>
		 ";


		
   		$line_total = count($row); // previous # of lines
		if ($editaction==__("Refresh")) {
			//make all black
			for ($i=0;$i<$line_total;$i++) {
				$colors[$i] = "#000000";
			}
		}

		//$display_buffer .= "maxline $maxlines <BR>";
		if (!isset($start)) {
			$start=0;
			$last = ($line_total > $maxlines) ? $maxlines : $line_total;
			//$display_buffer .= "first end $last<BR>";
	
		}

		if ($editaction==__("Next")) {
			// replace file data with screen content
			$this->DoSaveScreen($row,$col,$len,$data,$format,$comment,$start,$last);

			//$display_buffer .= "last $last<BR>";
			$start = $start + $maxlines;
			$last = $last + $maxlines;
			//$display_buffer .= "last $last<BR>";
			if ($start > $line_total) {
				$start = 0;
				$last = ($line_total > $maxlines) ? $maxlines : $line_total;
			} else {
				if ($last > $line_total) {
					$last = $line_total;
				}
			}
		}

		if ($editaction==__("Previous"))
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
				 <tr CLASS=\"".freemed_alternate()."\">
				  <td><FONT COLOR=\"$colors[$i]\">".($i+1)."</FONT></td>
				  <td align=\"CENTER\"><input TYPE=\"CHECKBOX\" NAME=\"mark$brackets\" VALUE=\"$i\"/></td>
				  <td><INPUT TYPE=TEXT NAME=\"drow$brackets\" SIZE=5
					MAXLENGTH=3 VALUE=\"".prepare($row[$i])."\"></td>
				  <td><INPUT TYPE=TEXT NAME=\"dcol$brackets\" SIZE=5
					MAXLENGTH=3 VALUE=\"".prepare($col[$i])."\"></td>
				  <td><INPUT TYPE=TEXT NAME=\"dlen$brackets\" SIZE=5
					MAXLENGTH=3 VALUE=\"".prepare($len[$i])."\"></td>
				  <td><INPUT TYPE=TEXT NAME=\"ddata$brackets\" SIZE=20
					MAXLENGTH=100 VALUE=\"".prepare($data[$i])."\"></td>
				  <td><INPUT TYPE=TEXT NAME=\"dformat$brackets\" SIZE=5
					MAXLENGTH=100 VALUE=\"".prepare($format[$i])."\"></td>
				  <td><INPUT TYPE=TEXT NAME=\"dcomment$brackets\" SIZE=20
					MAXLENGTH=100 VALUE=\"".prepare($comment[$i])."\"></td>
				 </tr>
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
		  <p/>
		  <CENTER>
		  <small>".__("Line Count")." : </small>
		   <INPUT TYPE=TEXT NAME=\"linecount\" VALUE=\"0\"
			SIZE=2 MAXLENGTH=2>
		  </CENTER>
		  <div align=\"CENTER\">
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Previous")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Copy After")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Resequence")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Delete")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Insert Before")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Insert After")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Next")."\"/>
		  <br/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Save")."\"/>
		  <input class=\"button\" type=\"submit\" name=\"editaction\"
		   value=\"".__("Cancel")."\"/>
		  </div>
		";
	
	} // end modform

	function DoDelete(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount, &$colors) {
		global $display_buffer;
		$numrows = count($row);
		if ($numrows == 0) {
			return false;
		}
	
		//$display_buffer .= "numrows before $numrows<BR>";
		$n=0;	
		for ($i=0;$i<$numrows;$i++) {
			if (!fm_value_in_array($mark,$i)) {
				$newrow[$n] = $row[$i];
				$newcol[$n] = $col[$i];
				$newlen[$n] = $len[$i];
				$newdata[$n] = $data[$i];
				$newformat[$n] = $format[$i];
				$newcomment[$n] = $comment[$i];
				$newcolor[$n] = $colors[$i]; //"#000000";
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
		$colors = $newcolor;
		//$numrows = count($row);
		//$display_buffer .= "numrows after $numrows<BR>";
		return true;
	}

	function DoInsertB(&$row,&$col,&$len,&$data,&$format,&$comment,&$mark,&$linecount,&$colors) {
		global $display_buffer;
		// insert before
		$numrows = count($row);
		//print "numrows = $numrows<br/>\n";
		if ($numrows == 0) {
			$row[0] = "0";
			$col[0] = "0";
			$len[0] = "0";
			$data[0] = "X";
			$format[0] = "X";
			$comment[0] = "X";
			$colors[0] = "#ff0000"; // show inserts as red;
			return true;
		}

		if ($linecount == 0) {
			return false;
		}

		$gotrows = true;
		$i=0;
		$n=0;
		while($gotrows) {
			if (fm_value_in_array($mark,$i)) {  // got a hit!
				for ($x=0; $x<$linecount; $x++) {
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
			if ($i == $numrows) {
				$gotrows = false;
			}
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
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		global $ffrow,$ffcol,$fflength,$ffdata,$ffformat,$ffcomment;
		$ffrow     = fm_join_from_array ($row    );
		//print "row (".count($row).") : "; print_r($row); print "<br/>\n";
		//print "ffrow (".count($ffrow).") : "; print_r($ffrow); print "<br/>\n";
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
				__("Name")			=>	"ffname",
				__("Description")	=>	"ffdescrip"
			),
			array ( "", __("NO DESCRIPTION") )
		);  
	} // end function FixedFormsMaintenance->view     

} // end class FixedFormsMaintenance

register_module ("FixedFormsMaintenance");

?>
