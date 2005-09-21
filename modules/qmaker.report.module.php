<?php
	// $Id$
	// desc: Adhoc query generator Taken from MySQL Query Maker
	// Jay P. Narain (narain2@yahoo.com)
	// converted to freemed by fforest
	// Additional code changes by Jeff (jeff@freemedsoftware.com)

LoadObjectDependency('_FreeMED.ReportsModule');

class QmakerReport extends ReportsModule {

	var $MODULE_NAME = "Query Maker";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_DESCRIPTION = "SQL query formation and execution environment.";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name = 'queries';

	function QmakerReport () {
		// Since this needs its own table for queries, lets
		// create a table definition.
		$this->table_definition = array (
			'qdatabase' => SQL__NOT_NULL(SQL__VARCHAR(250)),
			'qquery' => SQL__TEXT,
			'qtitle' => SQL__NOT_NULL(SQL__VARCHAR(255)),
			'id' => SQL__SERIAL
		);
		// Define keys for this table (id is automagically
		// merged into this).
		$this->table_keys = array ('qtitle');
	
		// Run parent constructor
		$this->ReportsModule();
	} // end constructor QmakerReport

	function view() {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
	
		if ( (empty($btnSubmit)) OR ($btnSubmit=="PickQuery") )
		{  	

			if ($btnTable1 == "SaveQuery")
			{
				$display_buffer .= "<CENTER>";
				if ( (isset($loadas)) AND ($loadas > 0))
				{
					$display_buffer .= "Updating Query $saveas<BR>";
					$qry = "UPDATE queries SET qquery='".addslashes($cquery)."' WHERE id='".addslashes($loadas)."'";
					$res = $sql->query($qry);
				}
				else
				{
					$display_buffer .= "Saving Query $saveas<BR>";
					$qry = $sql->insert_query(
						'queries',
						array(
						'qquery' => $cquery,
						'qdatabase' => $database,
						'qtitle' => $saveas
						)
					);
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
				".__("Back")."</A>
				</CENTER>
				<P>
				";	
				$display_buffer .= "</CENTER>";
				return;
			}

			$display_buffer .= "<CENTER>";
			$display_buffer .= "<p>";
			$display_buffer .= __("Pick a Query");
			$display_buffer .= "</p>";
			$display_buffer .= "<FORM METHOD=\"POST\" ACTION=\"$this->page_name\"><SELECT NAME=\"loadas\">\n";
			$res = $sql->query("SELECT * FROM queries ORDER BY qtitle");
			while($row = $sql->fetch_array($res))
			   $display_buffer .= "<OPTION VALUE=\"$row[id]\">".$row['qtitle']."</OPTION>\n";
			$display_buffer .= "</select>";
			$display_buffer .= "<p/>";
			$display_buffer .= "<table><tr>";
			$display_buffer .= "<td><input TYPE=\"SUBMIT\" NAME=\"btnSubmit\" VALUE=\"".__("Load Query")."\" class=\"button\" /></td>\n";
			$display_buffer .= "<td><input TYPE=\"SUBMIT\" NAME=\"btnSubmit\" VALUE=\"".__("Execute Query")."\" class=\"button\" /></td>\n";
			$display_buffer .= "<td><input TYPE=\"SUBMIT\" NAME=\"btnSubmit\" VALUE=\"".__("Export Query to CSV")."\" class=\"button\" /></td>\n";
			$display_buffer .= "<td><input TYPE=\"SUBMIT\" NAME=\"btnSubmit\" VALUE=\"".__("Create")."\" class=\"button\" /></td>\n";
			$display_buffer .= "</tr></table>";
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"view\"/>";
	 		$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>";
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>";
			$display_buffer .= "</form>\n";
			$display_buffer .= "</div>";
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
			$display_buffer .= "<INPUT TYPE=SUBMIT NAME=\"btnSubmit\" VALUE=\"".__("Select Fields")."\">";
			$display_buffer .= "</FORM>\n";
			$display_buffer .= "</CENTER>";
			return;
		}

	
		if ($btnSubmit == __("Select Fields"))
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
		<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
		<tr>
		<td><b>Operator</b></td>
		<td><b>Expr</b></td>
		</tr>";
		$display_buffer .= "<tr>";
		for ($i=0; $i<5; $i++)
		{
		$display_buffer .= "<td>
		<select name=\"agg_op[$i]\">
		<option value=\"None\">None</option>
		<option VALUE=\"AVG\">AVG(expr)</option>
		<option VALUE=\"COUNT\">COUNT(expr)</option>
		<option VALUE=\"MAX\">MAX(expr)</option>
		<option VALUE=\"MIN\">MIN(expr)</option>
		<option VALUE=\"STD\">STD(expr)</option>
		<option VALUE=\"SUM\">SUM(expr)</option>
		</select>
		</td>
		<td><input type=\"text\" size=\"60\" name=\"agg_val[$i]\"/></td>\n</tr>\n";
		}
		$display_buffer .= "</table>\n";
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"view\"/>\n";
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>\n";
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>";
			$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"fldy\"  VALUE=\"".prepare($fldy)."\"/>\n";	
			$display_buffer .= "<input TYPE=\"hidden\" NAME=\"cnty\"  VALUE=\"".prepare($cnty)."\"/>\n";	
			$display_buffer .= "<input class=\"button\"TYPE=\"RESET\" VALUE=\"Clear All\"/>\n";
			$display_buffer .= "<input class=\"button\" TYPE=\"SUBMIT\" NAME=\"btnSubmit\" value=\"".__("Select Options")."\"/>\n";
			$display_buffer .= "</form>\n";
				//mysql_close($ConID);

			return;
		} // end SelectFields

		if ($btnSubmit == __("Select Options"))
		{
				$xy = $this->matchset($fields);
				$cnt = count($xy);

				$display_buffer .= __("The current Select statement is:");	
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

			   $display_buffer .= "<p/>";
			   $display_buffer .= "<span class=\"query\">$mysql</span><br/><br/>\n"; 
			   $display_buffer .= "<p/>";
				
		$display_buffer .= "<form method=\"post\" action=\"$this->page_name\">\n";
		$display_buffer .= "<input type=\"hidden\" name=\"mysql\" value=\"$mysql\"/>\n";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>WHERE CLAUSE</h3>
		<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
		<tr>
		<td><b>".__("Field")."</b></td>
		<td><b>".__("Operator")."</b></td>
		<td><b>".__("Value")."</b></td>
		<td><b>".__("Condition")."</b></td>
		</tr>";
		for ($i=0; $i<5; $i++)
		{
		$display_buffer .= "<td>";
		$display_buffer .= "<select name=\"qfields[$i]\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			$display_buffer .= "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			$display_buffer .= "</select></td>\n";
		$display_buffer .= "<td>
		<select NAME=\"fields_op[$i]\">
		<option VALUE=\"=\">=</option>
		<option VALUE=\"&lt;&gt;\">&lt;&gt;</option>
		<option VALUE=\"&gt;\">></option>
		<option VALUE=\"&gt;=\">&gt;=</option>
		<option VALUE=\"&lt;\">&lt;</option>
		<option VALUE=\"&lt;=\">&lt;=</option>
		<option VALUE=\"LIKE\">LIKE</option>
		</select>
		</td>
		<td><input type=\"text\" size=\"60\" name=\"fields_val[$i]\"/></td>
		<td><select name=\"fields_enab[$i]\">
		<option VALUE=\" \">  </option>
		<option VALUE=\"AND\">AND</option>
		<option VALUE=\"OR\">OR</option>
		</select>";
		$display_buffer .= "</td>\n</tr>";
		}
		$display_buffer .= "</table>";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>GROUP BY CLAUSE</h3>";
		$display_buffer .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
		<tr>
		<td><b>".__("Field")."</b></td>
		</tr>";
		$display_buffer .= "<td>";
		$display_buffer .= "<select NAME=\"gfields\">\n";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

			$display_buffer .= "<option value=\"None\">".__("NONE")."</option>\n";
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
		$display_buffer .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
		<tr>
		<td><b>Expr Field</b></td>
		</tr>";
		$display_buffer .= "<td><input type=\"text\" size=\"60\" name=\"hfields_val\"/>
		</td>\n";
		$display_buffer .= "</table>";
		$display_buffer .= "<p>";
		$display_buffer .= "</p>";
		$display_buffer .= "<h3>ORDER BY CLAUSE</h3>";
		$display_buffer .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\">
		<tr>
		<td><b>Field</b></td>
		<td><b>Expr Field</b></td>
		<td><b>SORT</b></td>
		</tr>";
		$display_buffer .= "<td>";
		$display_buffer .= "<select name=\"ofields\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

			$display_buffer .= "<option value=\"None\">None</option>\n";
				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			$display_buffer .= "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			$display_buffer .= "</select></td>\n";
		$display_buffer .= "<td><input type=\"text\" size=\"60\" name=\"ofields_val\"/></td>\n";
		$display_buffer .= "<td><select name=\"ofields_enab\">
		<option VALUE=\"ASC\">ASC</option>
		<option VALUE=\"DESC\">DESC</option>
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
		$display_buffer .= "<td><input type=\"text\" size=\"30\" name=\"lfields_off\"/>
		</td>\n";
		$display_buffer .= "<td><input type=\"text\" size=\"30\" name=\"lfields_row\"/>
		</td>\n";
		$display_buffer .= "</table>";
		$display_buffer .= "<input class=\"button\" TYPE=\"RESET\" value=\"Clear All\"/>\n";
		$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"view\"/>";
		$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>";
		$display_buffer .= "<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>";
		$display_buffer .= "<input class=\"button\" type=\"SUBMIT\" name=\"btnSubmit\" value=\"".__("Assemble Query")."\"/>\n";
		$display_buffer .= "</form>\n";
			 //mysql_close($ConID);
		return;

		} // end SelectOptions


		if ($btnSubmit == __("Assemble Query"))
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


		if ($btnSubmit == __("Execute Query") or $btnSubmit == __("Export Query to CSV"))
		{
			if (empty($cquery)) // running from pick menu?
			{
				if ($loadas > 0) // yes running from pick menu
				{
					$res = $sql->query("SELECT qtitle,qquery FROM queries WHERE id='".addslashes($loadas)."'");
					$rec = $sql->fetch_array($res);
					$cquery = $rec['qquery'];
					$saveas = $rec['qtitle'];

				}

			}

			if ($btnSubmit == __("Export Query to CSV")) {
				$csv = CreateObject('_FreeMED.CSV');
				$csv->ImportSQLQuery($cquery);
				$csv->Export();
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
			 	$display_buffer .= __("No Information available")."<br>\n";
			 continue;
			 }
			  $display_buffer .= sprintf( "<th>%s</th>\n",htmlspecialchars ($hdr) );
			
		   }
			$display_buffer .= "</tr>\n";

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

		  $GLOBALS['page_title'] = $saveas;
		  $GLOBALS['__freemed']['no_template_display'] = true;
			return;

		} // end ExecQuery

	
		if ($btnSubmit == __("Load Query") )
		{
			$saveas = "";
			$res = $sql->query("SELECT qtitle,qquery FROM queries WHERE id='$loadas'");
			$rec = $sql->fetch_array($res);
			$query = $rec['qquery'];
			$saveas = $rec['qtitle'];
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

		$buffer =  "<div align=\"CENTER\">\n";
		$buffer .=  "<h3>The Selected Query is \"$saveas\"</h3>";
		$buffer .=  "<form METHOD=\"POST\" ACTION=\"$this->page_name\">\n";
		$buffer .=  "<textarea ROWS=\"10\" COLS=\"100\" WRAP=\"virtual\" NAME=\"cquery\">$query</textarea>\n";
		$buffer .=  "<table><tr>\n";
		$buffer .=  "<td>\n";
		$buffer .=  "<td><input class=\"button\" TYPE=\"SUBMIT\" NAME=\"btnSubmit\" VALUE=\"".__("Execute Query")."\"/></td>\n";
		$buffer .=  "<td><input class=\"button\" TYPE=\"SUBMIT\" NAME=\"btnSubmit\" VALUE=\"".__("Pick Query")."\"/></td>\n";
		$buffer .=  "<td><input class=\"button\" TYPE=\"RESET\" VALUE=\"".__("Clear All")."\"/></td>\n";
		$buffer .=  "<td><input class=\"button\" TYPE=\"SUBMIT\" NAME=\"btnTable1\" VALUE=\"SaveQuery\"/></td>\n";
		$buffer .=  "</tr></table>\n";
		$buffer .=  "Save Query As &nbsp;";
		$buffer .=  "<input TYPE=\"TEXT\" NAME=\"saveas\" VALUE=\"$saveas\"/>\n";
		$buffer .=  "<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"view\">\n";
		$buffer .=  "<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>\n";
		$buffer .=  "<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>\n";
		$buffer .=  "<input TYPE=\"HIDDEN\" NAME=\"loadas\" VALUE=\"".prepare($queryid)."\"/>\n";
		$buffer .=  "</form>\n";
		$buffer .=  "</div>\n";
		$buffer .= "<p/>
			<div align=\"CENTER\">
			".template::link_button(
			__("Back"),
			$this->page_name."?patient=$patient&module=$module"
			)."
			</div>
			<p/>
			";	
		return $buffer;
	}

} // end class QmakerReport

register_module ("QmakerReport");

?>
