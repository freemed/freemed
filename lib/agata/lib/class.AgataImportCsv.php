<?php
	// $Id$

class AgataImportCsv
{
  var $Query;
  var $Maior;
  var $Columns;
  var $FileName;
  var $ColumnTypes;

  function AgataImportCsv($agataConfig, $agataDB, $FileName, $Tables)
  {
    $this->FileName    = $FileName;
    $this->agataConfig = $agataConfig;
    $this->delimiter = $this->agataConfig['general']['Delimiter'];
    $this->agataDB = $agataDB;
    
    if (!file_exists($FileName))
    {
      return false;
    }
    if (!strstr(strtoupper($FileName), '.CSV'))
    {
      Dialog::Aviso(Trans::Translate('It is not a CSV File'));
      return false;
    }
    
    $fd = fopen ($FileName, "r");
    $buffer = fgets($fd, 500);
    fclose($fd);
    $columns = explode($this->delimiter, trim($buffer));
    $n=0;
    foreach ($columns as $column)
    {
      $column = trim($column);
      $this->columns[] = "[$n] $column";
      $n ++;
    }
    
    $this->ColumnWnd = CreateObject('Agata.Match', $agataDB);
    $this->ColumnWnd->PutInColumn1($this->columns);
    $this->ColumnWnd->PutInCombo($Tables);
    $this->ColumnWnd->ok->connect_object('clicked', array(&$this, 'ImportCSV'));
    $this->ColumnWnd->view->connect_object('clicked', array(&$this, 'View'));

    $this->ColumnWnd->clist1->set_column_title(0, Trans::Translate('CSV Columns'));
    $this->ColumnWnd->clist2->set_column_title(0, Trans::Translate('Destinated Columns'));
    $this->ColumnWnd->clist3->set_column_title(0, Trans::Translate('Result'));

    return true;
  }
  
  function View()
  {
    $list = $this->ColumnWnd->clist1;
    $n = 0;
    while ($text = @$list->get_text($n,0))
    {
      $selColumns[] = $text;
      $n ++;
    }
    
    if ($n ==0)
    {
      Dialog::Aviso(Trans::Translate('There are no columns to view'));
      return false;
    }
    
    $fd = fopen ($this->FileName, "r");
    $buffer = fgets($fd, 500);
    while (!feof ($fd))
    {    
      $buffer = fgets($fd, 500);
      if (strlen($buffer) > 2)
        $lines[] = explode($this->delimiter, trim($buffer));
    }
    fclose($fd);
     
    if ($lines)
    {
      $Grade = new AGrid(Trans::Translate('Query Result'), $selColumns,   700, 400);
      $Grade->AppendLineItems($lines);
      $Grade->Exibe();
    }  
  
  }

  function ImportCSV()
  {
    $list  = $this->ColumnWnd->clist3;
    $Combo = $this->ColumnWnd->comboTables;
    $Entry = $Combo->entry;
    $Table = $Entry->get_text();

    $n = 0;
    echo "1\n";
    while ($text = @$list->get_text($n,0))
    {
      $tmp = explode("=>", trim($text));
      $CSVColumn = trim(substr($tmp[0],1,1));
      $SQLColumn = trim($tmp[1]);

      $CSVColumns[] = $CSVColumn;
      $SQLColumns[] = $SQLColumn;

      $convert[$CSVColumn] = $SQLColumn;
      $n ++;
    }
    echo "2\n";
    if ($n == 0)
    {
      Dialog::Aviso(Trans::Translate('You have to link the columns'));
      return false;
    }
    echo "3\n";
    Wait::On();
    $conn = CreateObject('Agata.Connection');
    echo "4\n";
    if ($conn->Open($this->agataDB))
    {   
      $fd = fopen ($this->FileName, "r");
      $buffer = fgets($fd, 500);
      echo "5\n";

      while (!feof ($fd))
      {    
        $buffer = fgets($fd, 500);
        if (strlen($buffer) > 2)
          $line = explode($this->delimiter, trim($buffer));
      
        $sql = "insert into $Table (" . implode(",", $SQLColumns) . ') values (';
        $pre = $sql;

        $CSVlines = null;
        $Ghost = null;
        foreach ($CSVColumns as $column)
        {
         $CSVlines[] = trim($line[$column]);
	  $Ghost[] = '?';
        }
        $allData[] = $CSVlines;
        //$allData[] = $lines;
      
        $pre .= implode(",", $Ghost) . ')';
        $sql .= implode(",", $CSVlines) . ')';   
      }

      if ($conn->InsertAll($pre, $allData))
        Dialog::Aviso(Trans::Translate('OK'), false);

      $conn->Close();
    }
    Wait::Off();
  }
  
  function CloseFileSelection()
  {
    $this->FileSelection->hide();
  }
}
?>
