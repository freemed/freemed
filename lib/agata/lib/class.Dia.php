<?php
	// $Id$

LoadObjectDependency('Agata.AgataCore');
	
class Dia extends AgataCore 
{
  function Dia($agataDB, $agataConfig, $FileName, $aTables, $agataTbLinks, $project, $posAction)
  {
    $this->FileName     = $FileName;
    $this->agataDB      = $agataDB;
    $this->agataConfig  = $agataConfig;
    $this->aTables      = $aTables;
    $this->agataTbLinks = $agataTbLinks;
    $this->project = $project;
    $this->posAction = $posAction;

    $this->ProcessDia();
  }

  function CheckColumn($column, $aColumns, $LineDistance)
  {
    $a = $column - 0.1;
    $b = $column + 0.1;
    if (($aColumns["$column"]) || ($aColumns["$a"]) || ($aColumns["$b"]))
    {
      return $this->CheckColumn($column + $LineDistance, $aColumns, $LineDistance);
    }
    return $column;
  }

  function ProcessDia()
  {
    $DiaSoft = 'dia';
    include_once 'classes/include/FormatMonetary.inc';

    $FileName = $this->FileName;
    $aTables  = $this->aTables;
    $project  = $this->project;
    $levels = $this->agataConfig['dia']['Levels'];
    $LineDistance = $this->agataConfig['dia']['LineDistance'];
    $ColumnDistance = $this->agataConfig['dia']['ColumnDistance'];
    $LevelDistance = $this->agataConfig['dia']['LevelDistance'];
    $TableOffSet = $this->agataConfig['dia']['TableOffSet'];

    $fd = fopen($FileName, "w");
    if (!$fd)
    {
      Dialog::Aviso(Trans::Translate('File Error'));
      return false;
    }

    $fx = fopen(dirname(dirname(__FILE__)).'/dia.template','r');
    while (!feof ($fx))
    {
      $buffer = fgets($fx, 500);
      fwrite($fd,$buffer);
    }
    fclose($fx);
    $aColors = array('#000000', '#0000FF', '#FF0000', '#009e00', '#3c4248', '#730b60');
    fwrite($fd, "  <dia:layer name=\"Segundo Plano\" visible=\"true\">\n");
    fwrite($fd, "    <dia:object type=\"Standard - Text\" version=\"0\" id=\"T1\">\n");
    fwrite($fd, "      <dia:attribute name=\"obj_pos\">\n");
    fwrite($fd, "        <dia:point val=\"38.85,-2.4\"/>\n");
    fwrite($fd, "      </dia:attribute>\n");
    fwrite($fd, "    <dia:attribute name=\"obj_bb\">\n");
    fwrite($fd, "      <dia:rectangle val=\"33.834,-5.37345;43.866,-1.37345\"/>\n");
    fwrite($fd, "    </dia:attribute>\n");
    fwrite($fd, "    <dia:attribute name=\"text\">\n");
    fwrite($fd, "      <dia:composite type=\"text\">\n");
    fwrite($fd, "        <dia:attribute name=\"string\">\n");
    fwrite($fd, "          <dia:string>#$project#</dia:string>\n");
    fwrite($fd, "        </dia:attribute>\n");
    fwrite($fd, "        <dia:attribute name=\"font\">\n");
    fwrite($fd, "            <dia:font name=\"Courier\"/>\n");
    fwrite($fd, "          </dia:attribute>\n");
    fwrite($fd, "          <dia:attribute name=\"height\">\n");
    fwrite($fd, "            <dia:real val=\"4\"/>\n");
    fwrite($fd, "          </dia:attribute>\n");
    fwrite($fd, "          <dia:attribute name=\"pos\">\n");
    fwrite($fd, "            <dia:point val=\"38.85,-2.4\"/>\n");
    fwrite($fd, "          </dia:attribute>\n");
    fwrite($fd, "          <dia:attribute name=\"color\">\n");
    fwrite($fd, "            <dia:color val=\"#000000\"/>\n");
    fwrite($fd, "          </dia:attribute>\n");
    fwrite($fd, "          <dia:attribute name=\"alignment\">\n");
    fwrite($fd, "            <dia:enum val=\"1\"/>\n");
    fwrite($fd, "          </dia:attribute>\n");
    fwrite($fd, "        </dia:composite>\n");
    fwrite($fd, "      </dia:attribute>\n");
    fwrite($fd, "    </dia:object>\n");

    $x = (int) count($aTables)/$levels;
    $a = 0;
    $b = 0;
    $lin =0;
    $passed1 = false;
    $passed2 = false;
    foreach ($aTables as $table)
    {
      $conn = CreateObject('Agata.Connection');
      $conn->Open($this->agataDB);
      $Fields = $conn->LoadFields($table);
      
      $compl = $conn->ShortQuery();
      $query = $conn->CreateQuery("select * from $table $compl");
      $ColumnTypes = $query->GetColumnTypes2();
      $conn->Close();
      $zeros = '0000';
      $count = count($Fields);
      $count = substr($zeros, 0, 4 - strlen($count)) . $count;
      $aTables_[$count . '-' . $table] = array(trim($table), $Fields, $ColumnTypes, count($Fields));
    }
    ksort($aTables_);
    foreach ($aTables_ as $key => $Vector)
    {
      $table = $Vector[0];
      $Fields = $Vector[1];
      $ColumnTypes = $Vector[2];
      $count = $Vector[3];
      $aResult[] = $table;

      for ($levelcount=1; $levelcount<=$levels; $levelcount ++)
      {
        if (($lin > ($levelcount * $x)) && (!$passed[$levelcount]))
        {
	  $a = 0;
	  //$b = 20 * $levelcount;
	  $b += ($LevelDistance + $count);
	  $passed[$levelcount] = true;
        }
      }
      //if ($b)
        //$lines[$table] = $b - $LevelDistance + 4;
        $lines[$table] = $b - $LevelDistance + 4;
      //else
        //$lines[$table] =  $count + 4;
      $ids[$table] = $lin;
      $points[$table] = array($a,  $b);

      fwrite($fd, "    <dia:object type=\"UML - Class\" version=\"0\" id=\"$lin\">\n");
      fwrite($fd, "      <dia:attribute name=\"obj_pos\">\n");
      fwrite($fd, "        <dia:point val=\"$a,$b\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"elem_corner\">\n");
      fwrite($fd, "        <dia:point val=\"$a,$b\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"name\">\n");
      fwrite($fd, "        <dia:string>#$table#</dia:string>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"stereotype\">\n");
      fwrite($fd, "        <dia:string/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"abstract\">\n");
      fwrite($fd, "        <dia:boolean val=\"false\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"suppress_attributes\">\n");
      fwrite($fd, "        <dia:boolean val=\"false\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"suppress_operations\">\n");
      fwrite($fd, "        <dia:boolean val=\"false\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"visible_attributes\">\n");
      fwrite($fd, "        <dia:boolean val=\"true\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"visible_operations\">\n");
      fwrite($fd, "        <dia:boolean val=\"true\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"foreground_color\">\n");
      fwrite($fd, "        <dia:color val=\"#000000\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"background_color\">\n");
      fwrite($fd, "        <dia:color val=\"#ffffff\"/>\n");
      fwrite($fd, "      </dia:attribute>\n");
      fwrite($fd, "      <dia:attribute name=\"attributes\">\n");

      $lin ++;
      $a += $ColumnDistance;
      $FieldNumber = 0;
      foreach($Fields as $Field)
      {
        if (trim($Field))
        {
          $Type = $ColumnTypes[$Field];
	  $fieldcount[$table][$Field] = $FieldNumber;
	  $FieldNumber ++;

          fwrite($fd,   "        <dia:composite type=\"umlattribute\">\n");
          fwrite($fd,   "          <dia:attribute name=\"name\">\n");
          fwrite($fd,   "            <dia:string>#". $Field ."#</dia:string>\n");
          fwrite($fd,   "          </dia:attribute>\n");
          fwrite($fd,   "          <dia:attribute name=\"type\">\n");
          fwrite($fd,   "            <dia:string>#" . $Type . "#</dia:string>\n");
          fwrite($fd,   "          </dia:attribute>\n");
          fwrite($fd,   "          <dia:attribute name=\"value\">\n");
          fwrite($fd,   "            <dia:string/>\n");
          fwrite($fd,   "          </dia:attribute>\n");
          fwrite($fd,   "          <dia:attribute name=\"visibility\">\n");
          fwrite($fd,   "            <dia:enum val=\"0\"/>\n");
          fwrite($fd,   "          </dia:attribute>\n");
          fwrite($fd,   "          <dia:attribute name=\"abstract\">\n");
          fwrite($fd,   "            <dia:boolean val=\"false\"/>\n");
          fwrite($fd,   "          </dia:attribute>\n");
          fwrite($fd,   "          <dia:attribute name=\"class_scope\">\n");
          fwrite($fd,   "            <dia:boolean val=\"false\"/>\n");
          fwrite($fd,   "          </dia:attribute>\n");
          fwrite($fd,   "        </dia:composite>\n");
        }
      }

      fwrite($fd,     "      </dia:attribute>\n");
      fwrite($fd,     "      <dia:attribute name=\"operations\"/>\n");
      fwrite($fd,     "      <dia:attribute name=\"template\">\n");
      fwrite($fd,     "        <dia:boolean val=\"false\"/>\n");
      fwrite($fd,     "      </dia:attribute>\n");
      fwrite($fd,     "      <dia:attribute name=\"templates\"/>\n");
      fwrite($fd,     "    </dia:object>\n");
    }
/*
    $fieldcount[$table][$Field] = $FieldNumber;
    $ids[$table] = $lin;
    $points[$table] = array($a,  $b);
*/

    if ($this->agataConfig['dia']['ShowLines'])
    {
      $assoc = $lin+1;
      $iColor = 0;
      $aTables = $aResult;
      if ($aTables)
      {
        foreach ($aTables as $table)
        {
          $table = trim($table);
	  $links = $this->agataTbLinks[$table];
	  if ($links)
	  {
	    foreach ($links as $field => $link)
	    {
              if (in_array($link[0], $aTables))
	      {
  	        $assoc ++;
		$color = $aColors[$iColor];
	        $table2 = $link[0];
	        $field2 = $link[1];
	        $text = $table . '.'. $field . ' = '. $table2 . '.'. $field2;

	        $a = $points[$table][0];
	        $b = $points[$table][1] + ($fieldcount[$table][$field] * (0.8)) + 1.9;
	      
	        $c = $points[$table2][0];
	        $d = $points[$table2][1] + ($fieldcount[$table2][$field2] * (0.8)) + 1.9;

                $linkcount[$table] ++;
		$conna = 8 + ($fieldcount[$table][$field] * 2);
		$connb = 8 + ($fieldcount[$table2][$field2] * 2);
		$c1 = $a -$TableOffSet + ($linkcount[$table] /4);
		$c2 = $c -$TableOffSet + ($linkcount[$table2] /4);
                $c1 = FormatMonetary($c1, 1, '', '.');
                $c2 = FormatMonetary($c2, 1, '', '.');
		$c1 = $this->CheckColumn($c1, $columns, $LineDistance);
		$c2 = $this->CheckColumn($c2, $columns, $LineDistance);
		$columns["$c1"] = true;
		$columns["$c2"] = true;
		//echo $LineDistance . "\n";
		//var_dump($columns);
	        //$line = $lines[$table] + $add[$line];
		//
		$tmp1 = $lines[$table];
		$tmp2 = $lines[$table2];
		$line = max($tmp1, $tmp2);
		$add[$line] += $LineDistance;
		$line += $add[$line];

		fwrite($fd,     "      <dia:object type=\"Standard - ZigZagLine\" version=\"0\" id=\"$assoc\">\n");
                fwrite($fd,     "        <dia:attribute name=\"obj_pos\">\n");
                fwrite($fd,     "          <dia:point val=\"$a,$b\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"orth_points\">\n");
                fwrite($fd,     "          <dia:point val=\"$a,$b\"/>\n");
                fwrite($fd,     "          <dia:point val=\"$c1,$b\"/>\n");
                fwrite($fd,     "          <dia:point val=\"$c1,$line\"/>\n");
                fwrite($fd,     "          <dia:point val=\"$c2,$line\"/>\n");
                fwrite($fd,     "          <dia:point val=\"$c2,$d\"/>\n");
                fwrite($fd,     "          <dia:point val=\"$c,$d\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"line_color\">\n");
                fwrite($fd,     "          <dia:color val=\"$color\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"start_arrow\">\n");
                fwrite($fd,     "          <dia:enum val=\"16\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"end_arrow\">\n");
                fwrite($fd,     "          <dia:enum val=\"16\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"orth_orient\">\n");
                fwrite($fd,     "          <dia:enum val=\"0\"/>\n");
                fwrite($fd,     "          <dia:enum val=\"1\"/>\n");
                fwrite($fd,     "          <dia:enum val=\"0\"/>\n");
                fwrite($fd,     "          <dia:enum val=\"1\"/>\n");
                fwrite($fd,     "          <dia:enum val=\"0\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"name\">\n");
                fwrite($fd,     "          <dia:string/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"direction\">\n");
                fwrite($fd,     "          <dia:enum val=\"0\"/>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:attribute name=\"ends\">\n");
                fwrite($fd,     "          <dia:composite>\n");
                fwrite($fd,     "            <dia:attribute name=\"role\">\n");
                fwrite($fd,     "              <dia:string/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "            <dia:attribute name=\"multiplicity\">\n");
                fwrite($fd,     "              <dia:string/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "            <dia:attribute name=\"arrow\">\n");
                fwrite($fd,     "              <dia:boolean val=\"true\"/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "            <dia:attribute name=\"aggregate\">\n");
                fwrite($fd,     "              <dia:enum val=\"0\"/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "          </dia:composite>\n");
                fwrite($fd,     "          <dia:composite>\n");
                fwrite($fd,     "            <dia:attribute name=\"role\">\n");
                fwrite($fd,     "               <dia:string/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "            <dia:attribute name=\"multiplicity\">\n");
                fwrite($fd,     "              <dia:string/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "            <dia:attribute name=\"arrow\">\n");
                fwrite($fd,     "              <dia:boolean val=\"true\"/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "            <dia:attribute name=\"aggregate\">\n");
                fwrite($fd,     "              <dia:enum val=\"0\"/>\n");
                fwrite($fd,     "            </dia:attribute>\n");
                fwrite($fd,     "          </dia:composite>\n");
                fwrite($fd,     "        </dia:attribute>\n");
                fwrite($fd,     "        <dia:connections>\n");
                fwrite($fd,     "          <dia:connection handle=\"0\" to=\"{$ids[$table]}\" connection=\"$conna\"/>\n");
                fwrite($fd,     "          <dia:connection handle=\"1\" to=\"{$ids[$table2]}\" connection=\"$connb\"/>\n");
                fwrite($fd,     "        </dia:connections>\n");
                fwrite($fd,     "      </dia:object>\n");
		$iColor ++;
		if ($iColor==6)
		  $iColor = 0;
	      }
	    }
          }
        }
      }
    }
    fwrite($fd,   "  </dia:layer>\n");
    fwrite($fd, "</dia:diagram>\n");    

    fclose($fd);
    $obj = &$this->posAction[0];
    $att = &$this->posAction[1];
    $obj->{$att}();

    OpenReport($FileName, $this->agataConfig);

    return true;
  }
}
