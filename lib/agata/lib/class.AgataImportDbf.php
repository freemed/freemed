<?php
	// $Id$

class AgataImportDbf
{
  var $Query;
  var $Maior;
  var $Columns;
  var $FileName;
  var $ColumnTypes;

  function AgataImportDbf($agataConfig, $agataDB, $FileName, $Tables)
  {
    $this->FileName     = $FileName;
    $this->agataConfig = $agataConfig;
    $this->agataDB = $agataDB;
    
    if (!file_exists($FileName))
    {
      return false;
    }

    if (!strstr(strtoupper($FileName), '.DBF'))
    {
      Dialog::Aviso(Trans::Translate('It is not a DBF File'));
      return false;
    }

    $this->fd = dbase_open("$FileName", 0);
    $this->numRec = dbase_numrecords($this->fd);
    $this->columns = array_keys(dbase_get_record_with_names ( $this->fd, 1));
    
    $this->ColumnWnd = CreateObject('Agata.Match', $agataDB);
    $this->ColumnWnd->PutInColumn1($this->columns);
    $this->ColumnWnd->PutInCombo($Tables);
    $this->ColumnWnd->ok->connect_object('clicked', array(&$this, 'ImportDBF'));
    $this->ColumnWnd->view->connect_object('clicked', array(&$this, 'View'));

    $this->ColumnWnd->clist1->set_column_title(0, Trans::Translate('DBF Columns'));
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
      if ($text != 'deleted')
        $selColumns[] = $text;
      $n ++;
    }
    
    if ($n ==0)
    {
      Dialog::Aviso(Trans::Translate('There are no columns to view'));
      return false;
    }    
    
    for ($n=1; $n<=$this->numRec; $n++)
    {
      $line = dbase_get_record_with_names ( $this->fd, $n );
      foreach ($selColumns as $column)
      {
        $lines[$n-1][] = $line[$column];
      }
    }
      
    if ($lines)
    {    
      $Grade = new AGrid(Trans::Translate('Query Result'), $selColumns,   700, 400);
      $Grade->AppendLineItems($lines);
      $Grade->Exibe();
    }  
  
  }

  function ImportDBF()
  {
    $list  = $this->ColumnWnd->clist3;
    $Combo = $this->ColumnWnd->comboTables;
    $Entry = $Combo->entry;
    $Table = $Entry->get_text();

    $n = 0;
    while ($text = @$list->get_text($n,0))
    {
      $tmp = explode("=>", trim($text));
      $DBFColumn = trim($tmp[0]);
      $SQLColumn = trim($tmp[1]);

      $DBFColumns[] = $DBFColumn;
      $SQLColumns[] = $SQLColumn;

      $convert[$DBFColumn] = $SQLColumn;
      $n ++;
    }
    
    if ($n == 0)
    {
      Dialog::Aviso(Trans::Translate('You have to link the columns'));
      return false;
    }    
    
    $fd = fopen ("transition.txt", "w");
    
    Wait::On();
    $conn = CreateObject('Agata.Connection');
    if ($conn->Open($this->agataDB))
    {
      for ($n=1; $n<=$this->numRec; $n++)
      {
        $line = dbase_get_record_with_names ( $this->fd, $n );

        $sql = "insert into $Table (" . implode(",", $SQLColumns) . ') values (';
        $pre = $sql;

        $DBFlines = null;
        $Ghost = null;
        foreach ($DBFColumns as $column)
        {
          $DBFlines[] = trim($line[$column]);
	  $Ghost[] = '?';
        }
        $allData[] = $DBFlines;

        $pre .= implode(',', $Ghost) . ')';
        $sql .= implode(',', $DBFlines) . ')';

        //aqui
        fwrite($fd, $sql . "\n");
      
      }
      if ($conn->InsertAll($pre, $allData))
        Dialog::Aviso(Trans::Translate('OK'), false);

      $conn->Close();
    }
    fclose($fd);
    Wait::Off();
}
  
  function CloseFileSelection()
  {
    $this->FileSelection->hide();
  }
}
?>
