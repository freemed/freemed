<?php
	// $Id$

LoadObjectDependency('Agata.AgataError');

class Query extends AgataError
{
  var $conn;     // the connection id
  var $sql;      // the SQL command string
  var $result;   // the SQL command result set
  var $row;      // the current row index
  var $db;
  var $TableInfo;

  function Query($agataConfig)
  {
    $this->agataConfig = $agataConfig;
  }

  function Open($db)
  {
    $this->result=$db->query($this->sql);
    $this->TableInfo = null;

    if (DB::isError($this->result) || (!$this->result))
    {
      $this->ShowError($this->result->userinfo, $this->isGui);
      $this->result =0;
      return false;
    }

    return true;
  }

  function GetRowCount()
  {
    return $this->result->numRows();
  }

  function GetColumnCount()
  {
    if (!$this->result)
      echo "error \n";

    return $this->result->numCols();
  }

  function GetColumnNames()
  {
    if (!$this->TableInfo)
    {
      $this->TableInfo = $this->result->TableInfo();
    }
    $Results = $this->TableInfo;
    
    foreach ($Results as $Result)
    {
      $strings[] = $Result['name'];
    }
    return $strings;
  }

  function GetColumnTypes()
  {
    if (!$this->TableInfo)
    {
      $this->TableInfo = $this->result->TableInfo();
    }
    $Results = $this->TableInfo;

    foreach ($Results as $Result)
    {
      $strings[] = $Result['type'];
    }
    return $strings;
  }
  
  function GetColumnTypes2()
  {
    if (!$this->TableInfo)
    {
      $this->TableInfo = $this->result->TableInfo();
    }
    $Results = $this->TableInfo;

    foreach ($Results as $Result)
    {
      $strings[$Result['name']] = $Result['type'];
    }
    return $strings;
  }  

};

?>
