<?php
	// $Id$

LoadObjectDependency('Agata.AgataError');

class Connection extends AgataError
{
  var $db;         // the connection identifier
  var $traceback;  // a list of transaction errors
  var $level;      // a counter for the transaction level

  function Open($agataConfig, $isGui = true)
  {
    $this->agataConfig = $agataConfig;
    $this->isGui = $isGui;

    $DbUser  = $agataConfig['DbUser'];
    $DbPass  = $agataConfig['DbPass'];
    $DbName  = $agataConfig['DbName'];
    $DbHost  = $agataConfig['DbHost'];
    $DbType  = $agataConfig['DbType'];

    LoadObjectDependency('PEAR.DB');
    $dsn="$DbType://$DbUser:$DbPass@$DbHost/$DbName";
    $this->db = DB::connect($dsn);

    if (DB::isError($this->db))
    {
      $this->ShowError($this->db->userinfo, $this->isGui);
      $this->db = null;
      return false;
    }

    return $this->db;
  }

  function InsertAll($pre, $allData)
  {
    $dbh = $this->db;
    $sth = $dbh->prepare($pre);
    $x = $dbh->executeMultiple($sth,$allData);
    if (DB::isError($x))
    {
      $this->ShowError($x->userinfo, $this->isGui);
      return false;
    }
    return true;
  }
  
  function ShortQuery()
  {
    $agataConfig = $this->agataConfig;
    $DbType  = $agataConfig['DbType'];

    if ($DbType=='oci8')
    {
      return 'where rownum=1';
    }
    else
    {
      return 'limit 1';
    }
  }

  function Close()
  {
    if ( $this->db )
    {
      $this->db = null;
    }
  }

  function CreateQuery($sql="")
  {
    $q = CreateObject('Agata.Query', $this->agataConfig);

    $q->conn   = $this->db;
    $q->sql    = $sql;
    $q->result = 0;
    $q->row    = -1;
    $q->isGui  = $this->isGui;
    if ( $sql != "" )
      $q->Open($this->db);

    return $q;
  }
  
  function LoadFields($table)
  {
    $agataConfig = $this->agataConfig;
    $DbType = $agataConfig['DbType'];
    $table = trim($table);

    $sql['pgsql']  = "SELECT * FROM $table limit 1";
    $sql['mysql']  = "SELECT * FROM $table limit 1";
    $sql['oci8'] = "SELECT * FROM $table where rownum=1";
    $sql['sybase'] = "SELECT * FROM $table limit 1";
    //$sql['mssql']  = "SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' ORDER BY COLUMN_NAME";
    $sql['mssql']  = "SELECT c.name FROM sysobjects o INNER JOIN syscolumns c ON o.id=c.id WHERE o.name = '$table' ORDER BY c.name";
    $sql['fbsql']  = "SELECT * FROM $table limit 1";
    $sql['ibase']  = "select RDB\$FIELD_NAME as name from RDB\$RELATION_FIELDS " .
                     "where RDB\$RELATION_NAME = '$table' order by RDB\$FIELD_NAME ";
    $sql['ifx']    = "select c.colname from systables t, syscolumns c " .
                     "where t.tabid = c.tabid and t.tabname = '$table' order by colname ";
    $sql['odbc']   = "select name from sysibm.syscolumns where tbname='$table'";

    $sql = $sql[$DbType];
    $Results = $this->db->query($sql);
    if (DB::isError($Results) || (!$Results))
    {
      //$this->ShowError($Results->userinfo, $this->isGui);
      return false;      
    }

    if (($DbType == "ibase") || ($DbType == "mssql") || ($DbType == "ifx")  || ($DbType == "odbc"))
    {
      while ($Row = $Results->fetchRow())
      {
        $strings[] = trim($Row[0]);
      }
    }
    else
    {
      foreach ($Results->TableInfo() as $Result)
      {
        $strings[] = $Result['name'];
      }
    }

    return $strings;
  }

  function LoadTables()
  {
    $agataConfig = $this->agataConfig;
    $DbType = $agataConfig['DbType'];

    $TableNamesQuery['pgsql']  = "select tablename from pg_tables where tablename not like 'pg%' order by tablename";
    $TableNamesQuery['mysql']  = "SHOW TABLES";
    $TableNamesQuery['oci8'] = "SELECT table_name FROM user_tables";
    $TableNamesQuery['sybase'] = "select name from sysobjects where type = 'U' order by name";
    $TableNamesQuery['mssql']  = "select name from sysobjects where (type = 'U' or type='V') order by name";
    $TableNamesQuery['fbsql']  = "select \"table_name\" from information_schema.tables";
    $TableNamesQuery['ibase']  = "select RDB\$RELATION_NAME from RDB\$RELATIONS where RDB\$SYSTEM_FLAG=0 and RDB\$VIEW_BLR is null order by RDB\$RELATION_NAME";
    $TableNamesQuery['ifx']    = "select tabname from systables where tabid > 99 order by tabname";
    $TableNamesQuery['odbc']    = "select name from sysibm.systables where name not like 'SYS%' and type='T'";

    $sql = $TableNamesQuery[$DbType];
    if ($sql)
    {
      $Result = $this->db->query($sql);
      while ($Row = $Result->fetchRow())
      {
        $strings[] = trim($Row[0]);
      }
      return $strings;
    }
    else
    {
      return null;
    }
  }

};

?>
