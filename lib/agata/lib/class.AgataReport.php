<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataReport
{
  var $FunctionNames = array('count' => 'Count', 'sum' => 'Sum', 'avg' => 'Average', 'min' => 'Minimal', 'max' => 'Maximal');

  function AgataReport($agataDB, $agataConfig, $FileName, $Query, $Breaks, $ShowBreakColumns, $ShowDataColumns, $ShowTotalLabel, $ReportName, $posAction = null)
  {
    $this->FileName    = $FileName;
    $AgataDir = $agataConfig['general']['AgataDir'];

    $this->Query       = $Query->Query;
    $this->MaxLen      = $Query->MaxLen;
    $this->Columns     = $Query->Columns;
    $this->ColumnTypes = $Query->ColumnTypes;
    
    $this->agataDB    = $agataDB;
    $this->agataConfig= $agataConfig;
    $this->ReportName = $ReportName;
    $this->Breaks      = $Breaks;
    $this->ShowBreakColumns = $ShowBreakColumns;
    $this->ShowDataColumns  = $ShowDataColumns;
    $this->ShowTotalLabel   = $ShowTotalLabel;
    $this->posAction = $posAction;

    return true;
  }

  function GetReportName()
  {
    $this->InputBox = new InputBox(Trans::Translate('Type the Report Name'), 200);
    $this->InputBox->button->connect_object('clicked', array(&$this,'Process'), true);
  }
  /**************************
  Returns a Formatted Number 
  ***************************/
  function FormatNumber($number)
  {
    $precision   = $this->agataConfig['general']['Precision'];
    $thousep     = $this->agataConfig['general']['ThouSep'];
    $decsep      = $this->agataConfig['general']['DecSep'];

    $result = number_format($number, $precision, $decsep, $thousep);
    //echo "$number - $precision - $decsep - $thousep \n";
    return $result;
  }


  /**************************
  Returns a Formatted String 
  ***************************/
  function FormatString($Expression, $Lenght, $alignKind = 'left')
  {
    $Brancos  = "                                             ";
    $Brancos .= $Brancos . $Brancos;

    if ($alignKind == 'left')
    {
      return $Expression . substr($Brancos,0,$Lenght-strlen($Expression));
    }
    else if ($alignKind == 'center')
    {
      return substr($Brancos,0,($Lenght-strlen($Expression)) /2) .
             $Expression .
             substr($Brancos,0,($Lenght-strlen($Expression)) /2);
    }
    else if ($alignKind == 'right')
    {
      return substr($Brancos,0,$Lenght-strlen($Expression) -5) . $Expression . substr($Brancos,0,5);
    }
  }


  /******************************
  Returns a Replicatted Character
  *******************************/
  function Replicate($Expression, $Lenght)
  {
    for ($n=1; $n<=$Lenght; $n++)
      $Return .= $Expression;

    return $Return;
  }


  /**************************
   Makes the Totalization    
  ***************************/
  function ProcessBreaks($QueryCell, $y)
  {
    if ($this->Breaks)
    {
      $CountBreak = 0;
      if ($this->Breaks['0'])
        $CountBreak = -1;
      foreach ($this->Breaks as $Break => $Formulafull)
      {
        $break = $Break;
	$Formulas = explode(',', $Formulafull);

	//var_dump($this->Maior);
	if ($break == $y)
	{
	  $ClearColumns = null;
	  //var_dump($Formulas);
	  foreach ($Formulas as $Formula)
	  {
	    $tmp = explode('(', $Formula);
	    $formula = trim($tmp[0]);
	    $tmp = explode(')', $tmp[1]);
	    $column = trim($tmp[0]);
	    $this->HasFormula[$break] = ($formula) ? true : false;

	    $this->Summary[$break]['ActualValue'] = $QueryCell;
	    if ($this->Summary[$break]['ActualValue'] != $this->Summary[$break]['LastValue'])
	    {
  	      if ($this->Summary[$break]['LastValue'])
	      {
		$result = ($formula == 'avg') ? $this->Summary[$break][$column]['sum'] / $this->Summary[$break][$column]['count'] : $this->Summary[$break][$column][$formula];
                //$result = $this->FormatNumber($result);

		$result = FormatField($this->agataDB, $this->agataConfig, $result, $this->ColumnTypes[$column - 1]);
		$result = $result[0];
		$cellBreakContent = Trans::Translate($this->FunctionNames[$formula]) . ": $result";
		//$plus = strlen(Trans::Translate($this->FunctionNames[$formula]) . ': ');

                if ($isRight)
                {
                  $this->BreakMatrix[$break][$column][] = $this->FormatString($cellBreakContent, $this->Maior[$column] +2, 'right');
                }
                else
                {
                  $this->BreakMatrix[$break][$column][] = $this->FormatString($cellBreakContent, $this->Maior[$column] +2);
                }

		$ClearColumns[] = $column;
	      }
	      $this->Headers[$CountBreak] = trim($this->Columns[$y -1]) . " : " . trim($QueryCell);
	      $this->Association[$break] = $CountBreak;
	    }
	  }

	  if ($ClearColumns)
	    foreach ($ClearColumns as $ClearColumn)
	      $this->Summary[$break][$ClearColumn] = null;

	  $this->Summary[$break]['BeforeLastValue'] = $this->Summary[$break]['LastValue'];
	  $this->Summary[$break]['LastValue'] = $QueryCell;
	}
	    
        if (strstr($Formulafull, "($y)"))
	{
          $thousep     = $this->agataConfig['general']['ThouSep'];
          //$a = str_replace($thousep,'',$QueryCell);
	  $a = $QueryCell;
          $this->Summary[$break][$y]['sum'] += $a;

	  $this->Summary[$break][$y]['count'] ++;

	  $this->Summary[$break][$y]['max'] = ($QueryCell > $this->Summary[$break][$y]['max']) ? $QueryCell : $this->Summary[$break][$y]['max'];

	  if (!$this->Summary[$break][$y]['min'])
	    $this->Summary[$break][$y]['min'] = $QueryCell;
	  $this->Summary[$break][$y]['min'] = ($QueryCell < $this->Summary[$break][$y]['min']) ? $QueryCell : $this->Summary[$break][$y]['min'];
	}
	$CountBreak ++;
      }
    } // end if Breaks

    return array($break);
  }



  /******************************************
   Makes the Totalization after the last line
  *******************************************/
  function ProcessLastBreak()
  {
    if ($this->Breaks)
    {
      $CountBreak = 0;
      foreach ($this->Breaks as $Break => $Formulafull)
      {
	$break = $Break;
	$Formulas = explode(',', $Formulafull);

	//if ($break == $y)
	//{
	  $ClearColumns = null;
	  foreach ($Formulas as $Formula)
	  {
	    $tmp = explode('(', $Formula);
	    $formula = trim($tmp[0]);
	    $tmp = explode(')', $tmp[1]);
	    $column = trim($tmp[0]);

	    $result = ($formula == 'avg') ? $this->Summary[$break][$column]['sum'] / $this->Summary[$break][$column]['count'] : $this->Summary[$break][$column][$formula];
	    $result = FormatField($this->agataDB, $this->agataConfig, $result, $this->ColumnTypes[$column - 1]);
  	    $result = $result[0];	    
	    $cellBreakContent = Trans::Translate($this->FunctionNames[$formula]) . ": $result";
	    //$plus = strlen(Trans::Translate($this->FunctionNames[$formula]) . ': ');
		    
            $this->BreakMatrix[$break][$column][] = $this->FormatString($cellBreakContent, $this->Maior[$column] +2);

	    $ClearColumns[] = $column;
                
	    $this->Headers[$CountBreak] = trim($this->Columns[$y -1]) . " : " . trim($QueryCell);
	  }

	  if ($ClearColumns)
	    foreach ($ClearColumns as $ClearColumn)
	      $this->Summary[$break][$ClearColumn] = null;

	$CountBreak ++;
      }
    } // end if Breaks
  }
  
  function ExecPosAction()
  {
    $obj = &$this->posAction[0];
    $att = &$this->posAction[1];
    $obj->{$att}();
  }


  /**********************************************************
   This Function Equilize the GroupResults
  ***********************************************************/
  function EqualizeBreak($chave)
  {
    $Biggest = 0;
    $FinalBreak = null;
    $linebreak = $this->BreakMatrix[$chave];

    foreach ($linebreak as $tmp)
    {
      $Len = count($tmp);
      if ($Len > $Biggest)
         $Biggest = $Len;
    }

    for ($w=1; $w<=count($this->Columns); $w++)
    {
      $contents = $linebreak[$w];
      if (!$contents)
        $contents = array('');

        $contents = array_pad ($contents, $Biggest, '');
        $wline = 0;
        foreach ($contents as $content)
        {
          $FinalBreak[$wline][] = $content;
	  $wline ++;
	}
    }
    return $FinalBreak;
  }
    
}

function FormatMonetary($number, $precision, $thousep, $decsep)
{
  $zeros = '000000000000';

  if (strstr($number, '.'))
  {
    $a = explode('.', $number);
  }
  else if (strstr($number, ','))
  {
    $a = explode(',', $number);
  }
  else
  {
    $a[0] = $number;
  }
  $part1 = $a[0];
  $part2 = substr($a[1],0,$precision);
  if (!$part2)
    $part2 = substr($zeros, 0, $precision);

  $tmp = strrev($part1);

  for ($n=0; $n<strlen($tmp); $n++)
  {
    if ($i==3)
    {
      $resultpart1 .= $thousep;
      $i = 0;
    }
    $i ++;
    $resultpart1 .= substr($tmp,$n,1);
  }
  $part1 = strrev($resultpart1);
  $result = $part1 . $decsep . $part2;
  return $result;
}

  function FormatField($agataDB, $aAgataConfig, $data, $type)
  {
    //locale definition
    //float, currency
    $precision   = $aAgataConfig['general']['Precision'];
    $thousep     = $aAgataConfig['general']['ThouSep'];
    $decsep      = $aAgataConfig['general']['DecSep'];
    $datefmt     = $aAgataConfig['general']['DateFmt'];
    $datetimefmt = $aAgataConfig['general']['DateTimeFmt'];
    $DbType      = $agataDB['DbType'];
    $alRight     = false;

    $type = strtoupper($type);
    $res1 = $data;

    if ($DbType=='ifx')
    {
        switch ($type)
        {
            case "SQLSERIAL"   :
            case "SQLINT"      :
            case "SQLSMINT"    :
                //int
                $res1 = $data;
                break;
            case "SQLDECIMAL"  :
            case "SQLMONEY"    :
            case "SQLSMFLOAT"  :
            case "SQLFLOAT"    :
                //float
                $res1 = number_format($data, $precision, $decsep, $thousep );
                $alRight = true;
                break;
            case "SQLDATE"     :
            case "SQLINTERVAL" :
                $res1 = date($datefmt, strtotime($data));
		if (strlen(trim($data)) == 0) $res1 = "";
                break;		
            case "SQLDTIME"    :
                $res1 = date($datetimefmt, strtotime($data));
		if (strlen(trim($data)) == 0) $res1 = "";
                break;
            case "SQLCHAR" :
            case "SQLVCHAR" :
            case "SQLNCHAR" :
            case "SQLNVCHAR" :
            case "SQLTEXT" :
            case "SQLLVARCHAR" :
            case "SQLLVARCHAR" :
                //str
                $res1 = $data;
                break;
        }
    }
    else if ($DbType=='pgsql')
    {
        switch ($type)
        {
            case "NUMERIC"    :
	        $alRight = true;
		$res1 = FormatMonetary($res1, $precision, $thousep, $decsep);
	        /*$res1 = number_format($res1, $precision, '#', '^');
		$res1 = ereg_replace('#',  $decsep,  $res1);
		$res1 = ereg_replace('\^', $thousep, $res1);
		FormatMonetary($number, $precision, $thousep, $decsep)	
		*/
		break;
            
	    case "FLOAT8"     :
                $alRight = true;
		$res1 = FormatMonetary($res1, $precision, $thousep, $decsep);
		/*$res1 = number_format($res1, $precision, '#', '^');
		$res1 = ereg_replace('#',  $decsep,  $res1);
		$res1 = ereg_replace('\^', $thousep, $res1);
		FormatMonetary($number, $precision, $thousep, $decsep)*/
		break;
		
        }
    }
    else
    {
      $res1 = $data;
    }
    //ibase

    //fare qua:

    //"TEXT" "VARYING"
    //"SHORT" "LONG"
    //"FLOAT" "DOUBLE" "D_FLOAT" "INT64"
    //"TIMESTAMP" "DATE" "TIME"

    //mysql

    //fare qua:

    //"string" "int" "real" "timestamp" "year" "date" "time" "datetime" "blob" "null" "unknown"

    //mssql
    //"char" "datetime" "decimal" "float" "image" "int" "nvarchar" "smallint" "text" "tinyint" "varchar"

    //and so on...

    return array($res1, $alRight);
  }


?>
