<?php
 // $Id$
 // desc: Adhoc query generator Taken from MySQL Query Maker
 // Jay P. Narain
 // narain2@yahoo.com
 // converted to freemed by fforest
 // lic : LGPL

LoadObjectDependency('FreeMED.ReportsModule');

class QmakerReport extends ReportsModule {

	var $MODULE_NAME = "Query Maker";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function QmakerReport () {
		$this->ReportsModule();
	} // end constructor QmakerReport

	function view() {
		global $display_buffer;
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

	
		if ( (empty($btnSubmit)) OR ($btnSubmit=="PickQuery") )
		{  	

			if ($btnTable1 == "SaveQuery")
			{
				$display_buffer .= "<CENTER>";
				if ( (isset($loadas)) AND ($loadas > 0))
				{
					$display_buffer .= "Updating Query $saveas<BR>";
					$qry = "UPDATE queries SET qquery='$cquery' WHERE id='$loadas'";
					$res = $sql->query($qry);
				}
				else
				{
					$display_buffer .= "Saving Query $saveas<BR>";
					$qry = "INSERT INTO queries (qquery,qdatabase,qtitle) VALUES ('$cquery','$database','$saveas')";
					$res = $sql->query($qry);
				}
				if (!$res)
					$display_buffer .= "Error adding $saveas<BR>";
				else
					$display_buffer .= "The query has been saved as $saveas<BR>";

				$display_buffer .= "
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?patient=$patient&module=$module\">
				"._("Back")."</A>
				</CENTER>
				<P>
				";	
				$display_buffer .= "</CENTER>";
				return;
			}

			$display_buffer .= "<CENTER>";
			$display_buffer .= "<p>";
			$display_buffer .= "Pick a Query";
			$display_buffer .= "</p>";
			$display_buffer .= "<FORM METHOD=\"POST\" ACTION=\"$this->page_name\"><SELECT  NAME=\"loadas\">\n";
			$res = $sql->query("SELECT * FROM queries");
			while($row = $sql->fetch_array($res))
			   $display_buffer .= "<OPTION VALUE=\"$row[id]\">$row[qtitle]</OPTION>\n";
			$display_buffer .= "</select>";
			$display_buffer .= "<P>";
			$display_buffer .= "<TABLE><TR>";
			$display_buffer .= "<TD><INPUT TYPE=SUBMIT NAME=\"btnSubmit\" VALUE=\""._("Load Query")."\"></TD>\n";
			$display_buffer .= "<TD><INPUT TYPE=SUBMIT NAME=\"btnSubmit\" VALUE=\""._("Execute Query")."\"></TD>\n";
			$display_buffer .= "<TD><INPUT TYPE=SUBMIT NAME=\"btnSubmit\" VALUE=\""._("Create")."\"></TD>\n";
			$display_buffer .= "</TR></TABLE>";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
			$display_buffer .= "</FORM>\n";
			$display_buffer .= "</CENTER>";
			$display_buffer .= "
			<P>
			";	
			return;

		}


		if ($btnSubmit=="Create")
		{
			$result = $sql->listtables($database);
			$display_buffer .= "<CENTER>";
			$display_buffer .= "<FORM method=\"post\" action=\"$this->page_name\"><select multiple size=5 name=\"table[]\">\n";

			$i = 0;
			while ($i < $sql->num_rows ($result))
			{   $tb_names[$i] = $sql->tablename ($result, $i);
				$display_buffer .= "<option value=\"$tb_names[$i]\">$tb_names[$i]</option>\n";
				$i++;
			}

			$display_buffer .= "</select>\n";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
			$display_buffer .= "<INPUT TYPE=SUBMIT NAME=\"btnSubmit\" VALUE=\""._("Select Fields")."\">";
			$display_buffer .= "</FORM>\n";
			$display_buffer .= "</CENTER>";
			return;
		}

	
		if ($btnSubmit == _("Select Fields"))
		{

		if (isset ($table) )
		  {
				$xz = $this->matchset($table);
				$cnty = count($xz);
			$fldy  = "";
				$j = 0;
				for($j = 0; $j<$cnty; $j++)
			   {
				$fldy .=  $xz[$j].":"."t".$j. ($j<$cnty-1?",":"");
			   }
		  }


				$xy = $this->matchset($table);
				$cnt = count($xy);

				$display_buffer .= "<h3>Select Fields from tables:</h3>\n";	
				$display_buffer .= "<table border=1 cellpadding=3><tr>"; 
				for($j = 0; $j<$cnt; $j++)
			   {
				$tabz = $xy[$j];
				$display_buffer .= "<th><font color =red>$tabz</font></th>\n";
			   }
				$display_buffer .= "</tr>";
				$display_buffer .= "</table>\n";

			$display_buffer .= "<FORM method=\"post\" action=\"$this->page_name\">\n";
				for($j = 0; $j<$cnt; $j++)
			   {
				$tabz = $xy[$j];

			$SQL = "select * from $tabz";
			$result = $sql->query($SQL);
			
			$x = $sql->num_fields($result);

				$display_buffer .= "<select multiple size=5 name=\"fields[]\">\n";
			$i = 0;
			while ($i < $x) 
			{
				$fx  = $sql->field_name($result, $i);
					$fields[$i] = "t".$j.".".$fx;
				$display_buffer .= "<option value=\"$fields[$i]\">$fields[$i]</option>\n";

				$i++;
			}
					$fields[$i] = "t".$j."."."*";
					$display_buffer .= "<option value=\"$fields[$i]\">$fields[$i]</option>\n";

			$display_buffer .= "</select>\n";

				//mysql_free_result($result);
			}

		$display_buffer .= "<h4><b>Aggregate Functions</b></h4>
		<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Operator</b></td>
		<td><b>Expr</b></td>
		</tr>";
		$display_buffer .= "<tr>";
		for ($i=0; $i<5; $i++)
		{
		$display_buffer .= "<td>
		<SELECT name=\"agg_op[$i]\">
		<option value=\"None\">None</option>
		<OPTION VALUE=\"AVG\">AVG(expr)</OPTION>
		<OPTION VALUE=\"COUNT\">COUNT(expr)</OPTION>
		<OPTION VALUE=\"MAX\">MAX(expr)</OPTION>
		<OPTION VALUE=\"MIN\">MIN(expr)</OPTION>
		<OPTION VALUE=\"STD\">STD(expr)</OPTION>
		<OPTION VALUE=\"SUM\">SUM(expr)</OPTION>
		</SELECT>
		</td>
		<td><input type=\"text\" size=\"60\" name=\"agg_val[$i]\"></td>\n</tr>";
		}
		$display_buffer .= "</table>";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
			$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"fldy\"  VALUE=\"".prepare($fldy)."\">\n";	
			$display_buffer .= "<INPUT TYPE=hidden NAME=\"cnty\"  VALUE=\"".prepare($cnty)."\">\n";	
			$display_buffer .= "<INPUT TYPE=RESET VALUE=\"Clear All\">\n";
			$display_buffer .= "<INPUT TYPE=SUBMIT NAME=\"btnSubmit\"  value=\""._("Select Options")."\">\n";
			$display_buffer .= "</FORM>\n";
				//mysql_close($ConID);

			return;
		} // end SelectFields


		if ($btnSubmit == _("Select Options"))
		{


				$xy = $this->matchset($fields);
				$cnt = count($xy);

				$display_buffer .= _("The current Select statement is:");	
			$fldx  = "";
				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
				$fldx = $fldx . $xy[$j] . ($j<$cnt-1?",":"");
			   }

			$mysql = "SELECT  $fldx ";
		//Aggregation functions

		   $asql = "";
			 for ($i=0; $i<5; $i++)
			 {
			 if ($agg_val[$i] != "")
			  {
				if (strstr($agg_val[$i],"*"))
				{
				   $agg_val[$i] = "*";
				}
					$asql .= $agg_op[$i]."(".$agg_val[$i].")"." "; 
			   }
			 }

			  if ( trim($mysql) == "SELECT" && $asql !== "")
				$mysql .= " ".$asql." From ";
			  elseif ( $mysql != "" && $asql == "")
				$mysql .= " From ";
			  else
				$mysql .= ",".$asql." From ";

				$flex = explode(":", $fldy);
			$fldz  = "";
				for($j = 0; $j<$cnty; $j++)
			   {
				$fldz .=  $flex[2*$j]." ".$flex[2*$j+1]. ($j<cnty-1?",":" ");
			   }
				
			$mysql .= $fldz;

			   $display_buffer .= "<p>";
			   $display_buffer .= "<SPAN class=query>$mysql</SPAN><BR><BR>\n"; 
			   $display_buffer .= "</p>";
				
		$display_buffer .= "<FORM method=\"post\" action=\"$this->page_name\">\n";
		$display_buffer .= "<input type=hidden name=mysql value=\"$mysql\">";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>WHERE CLAUSE</h3>
		<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>"._("Field")."</b></td>
		<td><b>"._("Operator")."</b></td>
		<td><b>"._("Value")."</b></td>
		<td><b>"._("Condition")."</b></td>
		</tr>";
		for ($i=0; $i<5; $i++)
		{
		$display_buffer .= "<td>";
		$display_buffer .= "<SELECT name=\"qfields[$i]\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			$display_buffer .= "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			$display_buffer .= "</select></td>\n";
		$display_buffer .= "<td>
		<SELECT NAME=\"fields_op[$i]\">
		<OPTION VALUE=\"=\">=</OPTION>
		<OPTION VALUE=\"&lt;&gt;\">&lt;&gt;</OPTION>
		<OPTION VALUE=\"&gt;\">></OPTION>
		<OPTION VALUE=\"&gt;=\">&gt;=</OPTION>
		<OPTION VALUE=\"&lt;\">&lt;</OPTION>
		<OPTION VALUE=\"&lt;=\">&lt;=</OPTION>
		<OPTION VALUE=\"LIKE\">LIKE</OPTION>
		</SELECT>
		</td>
		<td><input type=\"text\" size=\"60\" name=\"fields_val[$i]\"></td>
		<td><select  name=\"fields_enab[$i]\">
		<OPTION VALUE=\" \">  </OPTION>
		<OPTION VALUE=\"AND\">AND</OPTION>
		<OPTION VALUE=\"OR\">OR</OPTION>
		</SELECT>";
		$display_buffer .= "</td>\n</tr>";
		}
		$display_buffer .= "</table>";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>GROUP BY CLAUSE</h3>";
		$display_buffer .= "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>"._("Field")."</b></td>
		</tr>";
		$display_buffer .= "<td>";
		$display_buffer .= "<SELECT NAME=\"gfields\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

			$display_buffer .= "<option value=\"None\">"._("NONE")."</option>\n";
				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			$display_buffer .= "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			$display_buffer .= "</select></td>\n";

		$display_buffer .= "</table>";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>HAVING  CLAUSE</h3>";
		$display_buffer .= "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Expr Field</b></td>
		</tr>";
		$display_buffer .= "<td><input type=\"text\" size=\"60\" name=\"hfields_val\">
		</td>\n";
		$display_buffer .= "</table>";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>ORDER BY CLAUSE</h3>";
		$display_buffer .= "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Field</b></td>
		<td><b>Expr Field</b></td>
		<td><b>SORT</b></td>
		</tr>";
		$display_buffer .= "<td>";
		$display_buffer .= "<SELECT name=\"ofields\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

			$display_buffer .= "<option value=\"None\">None</option>\n";
				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			$display_buffer .= "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			$display_buffer .= "</select></td>\n";
		$display_buffer .= "<td><input type=\"text\" size=\"60\" name=\"ofields_val\"></td>\n";
		$display_buffer .= "<td><select  name=\"ofields_enab\">
		<OPTION VALUE=\"ASC\">ASC</OPTION>
		<OPTION VALUE=\"DESC\">DESC</OPTION>
		</select>";
		$display_buffer .= "</td>\n";
		$display_buffer .= "</table>";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>LIMIT  CLAUSE</h3>";
		$display_buffer .= "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>OFFSET BY</b></td>
		<td><b>No.Of ROWS</b></td>
		</tr>";
		$display_buffer .= "<td><input type=\"text\" size=\"30\" name=\"lfields_off\">
		</td>\n";
		$display_buffer .= "<td><input type=\"text\" size=\"30\" name=\"lfields_row\">
		</td>\n";
		$display_buffer .= "</table>";
		$display_buffer .= "<INPUT TYPE=reset value=\"Clear All\">\n";
		$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
		$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
		$display_buffer .= "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
		$display_buffer .= "<input type=submit name=\"btnSubmit\" value=\""._("Assemble Query")."\">\n";
		$display_buffer .= "</FORM>\n";
			 //mysql_close($ConID);
		return;

		} // end SelectOptions


		if ($btnSubmit == _("Assemble Query"))
		{

		//Construct Query from the inputs

		//Where  Constructs

			 $whsql = "";
			   $ssql = "";
			 for ($i=0; $i<5; $i++)
			 {
			 if ($fields_val[$i] != "")
					$ssql .= stripslashes($qfields[$i])." ".$fields_op[$i]." ".
							stripslashes($fields_val[$i])." ".$fields_enab[$i]." "; 
			 }

			 if ( $ssql != "")
			 {
				$whsql .= "Where". " ". $ssql; 
			 }

				$mysql .= " ".$whsql;

		//Group By  Constructs

			 $gpsql = "";
			 if ( $gfields != "None")
			  {
				$gpsql = " Group By"." ".$gfields;
			  }
				$mysql .= " ".$gpsql;

		//Having    Constructs

			 $hvsql = "";
			 if ( $hfields_val != "")
			  {
				$hvsql = " Having"." ".$hfields_val;
			  }
				$mysql .= " ".$hvsql;

		//Order by  Constructs

			 $obsql = "";
			 if ( $ofields != "None")
			  {
				$obsql = " Order by"." ".$ofields." ".$ofields_val." ".$ofields_enab;
			  }
				$mysql .= " ".$obsql;

		//Limit Constructs

			 $lmsql = "";
			 if ( $lfields_off != "" &&  $lfields_row != "" )
				$lmsql .= "Limit"." ".$lfields_off.", ".$lfields_row;
			 elseif($lfields_off == "" &&  $lfields_row != "" )
				$lmsql = " Limit"." ".$lfields_row;

				$mysql .= " ".$lmsql;
			$display_buffer .= $this->getqueryform($mysql,$saveas);
			return;
		} // end AssembleQuery


		if ($btnSubmit == _("Execute Query"))
		{
			if (empty($cquery)) // running from pick menu?
			{
				if ($loadas > 0) // yes running from pick menu
				{
					$res = $sql->query("SELECT qtitle,qquery FROM queries WHERE id='$loadas'");
					$rec = $sql->fetch_array($res);
					$cquery = $rec[qquery];
					$saveas = $rec[qtitle];

				}

			}

		   $qid = $sql->query(stripslashes($cquery));

		  $display_buffer .= "<table>\n";
			$display_buffer .= "<table border=1 cellpadding=3>\n";
			$display_buffer .= "<caption>$saveas</caption>\n";
			$display_buffer .= "<tr>\n";
			for ($i=0; $i< $sql->num_fields($qid); $i++)
		   {
			 $hdr = $sql->field_name($qid,$i);
			 if (!$hdr) {
			 	$display_buffer .= _("No Information available")."<br>\n";
			 continue;
			 }
			  $display_buffer .= sprintf( "<th>%s</th>\n",htmlspecialchars ($hdr) );
			
		   }
			$display_buffer .= "</<tr>\n";

		   while ($row = $sql->fetch_array($qid))
		   {
			$display_buffer .= "<tr CLASS=\"".freemed_alternate()."\">\n";
			for ($i=0; $i< $sql->num_fields($qid); $i++)
			 {
			  $display_buffer .= sprintf( "<td>%s</td>\n",htmlspecialchars ($row[$i]) );
			 }
			$display_buffer .= "</<tr>\n";
		   }

		  $display_buffer .= "</table>\n";
			return;

		} // end ExecQuery

	
		if ($btnSubmit == _("Load Query") )
		{
			$saveas = "";
			$res = $sql->query("SELECT qtitle,qquery FROM queries WHERE id='$loadas'");
			$rec = $sql->fetch_array($res);
			$query = $rec[qquery];
			$saveas = $rec[qtitle];
			$display_buffer .= $this->getqueryform($query,$saveas,$loadas);

		} // end LoadQuery




	} // end form



	function matchset($xx) {
		$arrx = array_values($xx);
		$i = 0;
		while (list ($key, $val) = each ($arrx)) {
			$xy[$i]  = $val;
			$i++;
		}
		$cnt = $i;
		return $xy;
	}

	function getqueryform($query,$name,$queryid=0) {
		global $display_buffer;
		global $patient,$module;

		if (empty($name)) {
			$saveas = "Unnamed";
		} else {
			$saveas = $name;
		}

		$buffer =  "<CENTER>\n";
		$buffer .=  "<H3>The Selected Query is $saveas</H3>";
		$buffer .=  "<FORM METHOD=\"POST\" ACTION=\"$this->page_name\">\n";
		$buffer .=  "<TEXTAREA ROWS=10 COLS=100 WRAP=virtual NAME=\"cquery\">$query</TEXTAREA>\n";
		$buffer .=  "<TABLE><TR>\n";
		$buffer .=  "<TD>\n";
		$buffer .=  "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=\""._("Execute Query")."\"></TD>\n";
		$buffer .=  "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=\""._("Pick Query")."\"></TD>\n";
		$buffer .=  "<TD><INPUT TYPE=RESET VALUE=\""._("Clear All")."\"></TD>\n";
		$buffer .=  "<TD><INPUT TYPE=SUBMIT NAME=btnTable1 VALUE=SaveQuery></TD>\n";
		$buffer .=  "</TR></TABLE>\n";
		$buffer .=  "Save Query As &nbsp;";
		$buffer .=  "<INPUT TYPE=TEXT NAME=\"saveas\" VALUE=\"$saveas\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"loadas\" VALUE=\"".prepare($queryid)."\">\n";
		$buffer .=  "</FORM>\n";
		$buffer .=  "</CENTER>\n";
		$buffer .= "<P>
				<CENTER>
				<A HREF=\"$this->page_name?patient=$patient&module=$module\">
				"._("Back")."</A>
				</CENTER>
				<P>
				";	
		return $buffer;


	}



} // end class QmakerReport

register_module ("QmakerReport");

?>
