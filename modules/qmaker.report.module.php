<?php
 // $Id$
 // desc: Adhoc query generator Taken from MySQL Query Maker
 // Jay P. Narain
 // narain2@yahoo.com
 // converted to freemed by fforest

 // lic : LGPL

if (!defined("__QMAKER_REPORT_MODULE_PHP__")) {

class qmakerReport extends freemedReportsModule {

	var $MODULE_NAME = "Query Maker";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";

	function qmakerReport () {
		$this->freemedReportsModule();
	} // end constructor qmakerReport

	function view()
	{
		reset ($GLOBALS);
        while (list($k,$v)=each($GLOBALS)) global $$k;

	
		if ( (empty($btnSubmit)) OR ($btnSubmit=="PickQuery") )
		{  	

			if ($btnTable1 == "SaveQuery")
			{
				echo "<CENTER>";
				if ( (isset($loadas)) AND ($loadas > 0))
				{
					echo "Updating Query $saveas<BR>";
					$qry = "UPDATE queries SET qquery='$cquery' WHERE id='$loadas'";
					$res = $sql->query($qry);
				}
				else
				{
					echo "Saving Query $saveas<BR>";
					$qry = "INSERT INTO queries (qquery,qdatabase,qtitle) VALUES ('$cquery','$database','$saveas')";
					$res = $sql->query($qry);
				}
				if (!$res)
					echo "Error adding $saveas<BR>";
				else
					echo "The query has been saved as $saveas<BR>";

				echo "
				<P>
				<CENTER>
				<A HREF=\"$this->page_name?_auth=$_auth&patient=$patient&module=$module\">
				<$STDFONT_B>"._("Back")."<$STDFONT_E></A>
				</CENTER>
				<P>
				";	
				echo "</CENTER>";
				return;
			}

			echo "<CENTER>";
			echo "<p>";
			echo "Pick a Query";
			echo "</p>";
			echo "<FORM METHOD=\"POST\" ACTION=\"$this->page_name\"><SELECT  NAME=\"loadas\">\n";
			$res = $sql->query("SELECT * FROM queries");
			while($row = $sql->fetch_array($res))
			   echo "<OPTION VALUE=\"$row[id]\">$row[qtitle]</OPTION>\n";
			echo "</select>";
			echo "<P>";
			echo "<TABLE><TR>";
			echo "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=LoadQuery></TD>\n";
			echo "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=ExecQuery></TD>\n";
			echo "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=Create></TD>\n";
			echo "</TR></TABLE>";
			echo "<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
			echo "</FORM>\n";
			echo "</CENTER>";
			echo "
			<P>
			<CENTER>
			<A HREF=\"reports.php?$_auth\">"._("Reports")."</A>
			</CENTER>
			<P>
			";	
			return;

		}


		if ($btnSubmit=="Create")
		{
			$result = $sql->listtables($database);
			echo "<CENTER>";
			echo "<FORM method=\"post\" action=\"$this->page_name\"><select multiple size=5 name=\"table[]\">\n";

			$i = 0;
			while ($i < $sql->num_rows ($result))
			{   $tb_names[$i] = $sql->tablename ($result, $i);
				echo "<option value=\"$tb_names[$i]\">$tb_names[$i]</option>\n";
				$i++;
			}

			echo "</select>\n";
			echo "<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
			echo "<INPUT TYPE=SUBMIT NAME=\"btnSubmit\" VALUE=\"SelectFields\">";
			echo "</FORM>\n";
			echo "</CENTER>";
			return;
		}

	
		if ($btnSubmit == "SelectFields")
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

				echo "<h3>Select Fields from tables:</h3>\n";	
				echo "<table border=1 cellpadding=3><tr>"; 
				for($j = 0; $j<$cnt; $j++)
			   {
				$tabz = $xy[$j];
				echo "<th><font color =red>$tabz</font></th>\n";
			   }
				echo "</tr>";
				echo "</table>\n";

			echo "<FORM method=\"post\" action=\"$this->page_name\">\n";
				for($j = 0; $j<$cnt; $j++)
			   {
				$tabz = $xy[$j];

			$SQL = "select * from $tabz";
			$result = $sql->query($SQL);
			
			$x = $sql->num_fields($result);

				echo "<select multiple size=5 name=\"fields[]\">\n";
			$i = 0;
			while ($i < $x) 
			{
				$fx  = $sql->field_name($result, $i);
					$fields[$i] = "t".$j.".".$fx;
				echo "<option value=\"$fields[$i]\">$fields[$i]</option>\n";

				$i++;
			}
					$fields[$i] = "t".$j."."."*";
					echo "<option value=\"$fields[$i]\">$fields[$i]</option>\n";

			echo "</select>\n";

				//mysql_free_result($result);
			}

		echo "<h4><b>Aggregate Functions</b></h4>
		<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Operator</b></td>
		<td><b>Expr</b></td>
		</tr>";
		echo "<tr>";
		for ($i=0; $i<5; $i++)
		{
		echo "<td>
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
		echo "</table>";
			echo "<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
			echo "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
			echo "<input type=hidden name=fldy  value=$fldy>\n";	
			echo "<input type=hidden name=cnty  value=$cnty>\n";	
				echo "<INPUT TYPE=reset value=\"Clear All\">\n";
			echo "<input type=submit name=btnSubmit  value=SelectOptions>\n";
			echo "</FORM>\n";
				//mysql_close($ConID);

			return;
		} // end SelectFields


		if ($btnSubmit == "SelectOptions")
		{


				$xy = $this->matchset($fields);
				$cnt = count($xy);

				echo "The current Select statement is:";	
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

			   echo "<p>";
			   echo "<SPAN class=query>$mysql</SPAN><BR><BR>\n"; 
			   echo "</p>";
				
		echo "<FORM method=\"post\" action=\"$this->page_name\">\n";
		echo "<input type=hidden name=mysql value=\"$mysql\">";
		echo "<p>";
		echo "</p>";
		echo "<h3>WHERE CLAUSE</h3>
		<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Field</b></td>
		<td><b>Operator</b></td>
		<td><b>Value</b></td>
		<td><b>Condition</b></td>
		</tr>";
		for ($i=0; $i<5; $i++)
		{
		echo "<td>";
		echo "<SELECT name=\"qfields[$i]\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			echo "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			echo "</select></td>\n";
		echo "<td>
		<SELECT name=\"fields_op[$i]\">
		<OPTION VALUE=\"=\">=</OPTION>
		<OPTION VALUE=\"<>\"><></OPTION>
		<OPTION VALUE=\">\">></OPTION>
		<OPTION VALUE=\">=\">>=</OPTION>
		<OPTION VALUE=\"<\"><</OPTION>
		<OPTION VALUE=\"<=\"><=</OPTION>
		<OPTION VALUE=\"LIKE\">LIKE</OPTION>
		</SELECT>
		</td>
		<td><input type=\"text\" size=\"60\" name=\"fields_val[$i]\"></td>
		<td><select  name=\"fields_enab[$i]\">
		<OPTION VALUE=\" \">  </OPTION>
		<OPTION VALUE=\"AND\">AND</OPTION>
		<OPTION VALUE=\"OR\">OR</OPTION>
		</SELECT>";
		echo "</td>\n</tr>";
		}
		echo "</table>";
		echo "<p>";
		echo "</p>";
		echo "<h3>GROUP BY CLAUSE</h3>";
		echo "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Field</b></td>
		</tr>";
		echo "<td>";
		echo "<SELECT name=\"gfields\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

			echo "<option value=\"None\">None</option>\n";
				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			echo "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			echo "</select></td>\n";

		echo "</table>";
		echo "<p>";
		echo "</p>";
		echo "<h3>HAVING  CLAUSE</h3>";
		echo "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Expr Field</b></td>
		</tr>";
		echo "<td><input type=\"text\" size=\"60\" name=\"hfields_val\">
		</td>\n";
		echo "</table>";
		echo "<p>";
		echo "</p>";
		echo "<h3>ORDER BY CLAUSE</h3>";
		echo "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>Field</b></td>
		<td><b>Expr Field</b></td>
		<td><b>SORT</b></td>
		</tr>";
		echo "<td>";
		echo "<SELECT name=\"ofields\">";
				$xy = $this->matchset($fields);
				$cnt = count($xy);

			echo "<option value=\"None\">None</option>\n";
				$j = 0;
				for($j = 0; $j<$cnt; $j++)
			   {
			echo "<option value=\"$xy[$j]\">$xy[$j]</option>\n";
			}

			echo "</select></td>\n";
		echo "<td><input type=\"text\" size=\"60\" name=\"ofields_val\"></td>\n";
		echo "<td><select  name=\"ofields_enab\">
		<OPTION VALUE=\"ASC\">ASC</OPTION>
		<OPTION VALUE=\"DESC\">DESC</OPTION>
		</select>";
		echo "</td>\n";
		echo "</table>";
		echo "<p>";
		echo "</p>";
		echo "<h3>LIMIT  CLAUSE</h3>";
		echo "<table cellspacing=0 cellpadding=1 border=1>
		<tr>
		<td><b>OFFSET BY</b></td>
		<td><b>No.Of ROWS</b></td>
		</tr>";
		echo "<td><input type=\"text\" size=\"30\" name=\"lfields_off\">
		</td>\n";
		echo "<td><input type=\"text\" size=\"30\" name=\"lfields_row\">
		</td>\n";
		echo "</table>";
		echo "<INPUT TYPE=reset value=\"Clear All\">\n";
		echo "<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">";
		echo "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">";
		echo "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">";
		echo "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">";
		echo "<input type=submit name=btnSubmit value=AssembleQuery>\n";
		echo "</FORM>\n";
			 //mysql_close($ConID);
		return;

		} // end SelectOptions


		if ($btnSubmit == "AssembleQuery")
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
			echo $this->getqueryform($mysql,$saveas);
			return;
		} // end AssembleQuery


		if ($btnSubmit == "ExecQuery")
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

		  echo "<table>\n";
			echo "<table border=1 cellpadding=3>\n";
			echo "<caption>$saveas</caption>\n";
			echo "<tr>\n";
			for ($i=0; $i< $sql->num_fields($qid); $i++)
		   {
			 $hdr = $sql->field_name($qid,$i);
			 if (!$hdr) {
			 print ("No Information available<br>\n");
			 continue;
			 }
			  printf( "<th>%s</th>\n",htmlspecialchars ($hdr) );
			
		   }
			echo "</<tr>\n";

		   while ($row = $sql->fetch_array($qid))
		   {
			$color = freemed_bar_alternate_color($color);
			echo "<tr bgcolor=\"".$color."\">\n";
			for ($i=0; $i< $sql->num_fields($qid); $i++)
			 {
			  printf( "<td>%s</td>\n",htmlspecialchars ($row[$i]) );
			 }
			echo "</<tr>\n";
		   }

		  echo "</table>\n";
			return;

		} // end ExecQuery

	
		if ($btnSubmit == "LoadQuery" )
		{
			$saveas = "";
			$res = $sql->query("SELECT qtitle,qquery FROM queries WHERE id='$loadas'");
			$rec = $sql->fetch_array($res);
			$query = $rec[qquery];
			$saveas = $rec[qtitle];
			echo $this->getqueryform($query,$saveas,$loadas);

		} // end LoadQuery




	} // end form



	function matchset($xx)
	{
		$arrx = array_values($xx);
		$i = 0;
		while (list ($key, $val) = each ($arrx)) {
		$xy[$i]  = $val;
		$i++;
		}
		$cnt = $i;
		return $xy;
	}

	function getqueryform($query,$name,$queryid=0)
	{
		global $_auth,$patient,$module,$STDFONT_B,$STDFONT_E;

		if (empty($name))
			$saveas = "Unnamed";
		else
			$saveas = $name;

		$buffer =  "<CENTER>\n";
		$buffer .=  "<H3>The Selected Query is $saveas</H3>";
		$buffer .=  "<FORM METHOD=\"POST\" ACTION=\"$this->page_name\">\n";
		$buffer .=  "<TEXTAREA ROWS=10 COLS=100 WRAP=virtual  NAME=cquery> $query</TEXTAREA>\n";
		$buffer .=  "<TABLE><TR>\n";
		$buffer .=  "<TD>\n";
		$buffer .=  "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=ExecQuery></TD>\n";
		$buffer .=  "<TD><INPUT TYPE=SUBMIT NAME=btnSubmit VALUE=PickQuery></TD>\n";
		$buffer .=  "<TD><INPUT TYPE=RESET VALUE=\"Clear All\"></TD>\n";
		$buffer .=  "<TD><INPUT TYPE=SUBMIT NAME=btnTable1 VALUE=SaveQuery></TD>\n";
		$buffer .=  "</TR></TABLE>\n";
		$buffer .=  "Save Query As &nbsp;";
		$buffer .=  "<INPUT TYPE=TEXT NAME=\"saveas\" VALUE=\"$saveas\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"_auth\"  VALUE=\"".prepare($_auth)."\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"view\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">\n";
		$buffer .=  "<INPUT TYPE=HIDDEN NAME=\"loadas\" VALUE=\"".prepare($queryid)."\">\n";
		$buffer .=  "</FORM>\n";
		$buffer .=  "</CENTER>\n";
		$buffer .= "<P>
				<CENTER>
				<A HREF=\"$this->page_name?_auth=$_auth&patient=$patient&module=$module\">
				<$STDFONT_B>"._("Back")."<$STDFONT_E></A>
				</CENTER>
				<P>
				";	
		return $buffer;


	}



} // end class qmakerReport

register_module ("qmakerReport");

} // end if not defined

?>
