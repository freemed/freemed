<?php
	// $Id$

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
