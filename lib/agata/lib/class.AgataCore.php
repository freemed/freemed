<?php
	// $Id$

LoadObjectDependency('Agata.Wait');

// Include utility functions, just once
include_once(dirname(__FILE__).'/util.php');

class AgataCore
{

  function ReadProject($AgataDir, $Project)
  {
    include "{$AgataDir}/projects/{$Project}.prj";
    $agataDB['Project'] =  $Project;
    $agataDB['DbHost'] =   $DbHost;
    $agataDB['DbName'] =   $DbName;
    $agataDB['DbUser'] =   $DbUser;
    $agataDB['DbPass'] =   $DbPass;
    $agataDB['DbType'] =   $DbType;  

    return $agataDB;
  }

  function ReadProjects()
  {
    $projects    = GetSimpleDirArray('projects', false, '.prj');
    if ($projects)
    {
      foreach ($projects as $project)
      {
        include "projects/$project";
        $agataDB[$Project]['Project'] =  $Project;
        $agataDB[$Project]['DbHost'] =   $DbHost;
        $agataDB[$Project]['DbName'] =   $DbName;
        $agataDB[$Project]['DbUser'] =   $DbUser;
        $agataDB[$Project]['DbPass'] =   $DbPass;
        $agataDB[$Project]['DbType'] =   $DbType;
      }
      asort($agataDB);
    }
    
    return $agataDB;
  }
  
  function ReadProjectDefinitions($project)
  {
    $families = "projects/{$project}.tbf";
    $linking = "projects/{$project}.tbl";
    $description = "projects/{$project}.dsd";
    
    if (file_exists($families))
    {
      $TableFamilies = null;
      $TableGroups = null;
      include $families;
      $agataTbFamilies = $TableFamilies;
      $agataTbGroups = $TableGroups;
    }
    
    if (file_exists($linking))
    {
      $TableLinks = null;
      include $linking;
      $agataTbLinks = $TableLinks;
    }
    
    if (file_exists($description))
    {
      $Description = null;
      include $description;
      $agataDataDescription = $Description;
    }
    return array($agataTbFamilies, $agataTbGroups, $agataTbLinks,  $agataDataDescription);
  }
  
  function Planification($agataTbFamilies, $agataTbLinks, $agataDataDescription)
  {
    $PlainTbFamilies = null;
    $PlainTbLinks = null;
    $PlainDataDescription = null;

    if ($agataTbFamilies)
    {
      foreach ($agataTbFamilies as $TbFamily => $tables)
      {
        if ($tables)
	{
          foreach ($tables as $table)
          {
            $PlainTbFamilies[] = array($TbFamily, $table);
          }
        }
      }
      sort($PlainTbFamilies);
    }

    if ($agataTbLinks)
    {
      foreach ($agataTbLinks as $table1 => $TbLinks)
      {
        if ($TbLinks)
	{
          foreach ($TbLinks as $field1 => $TbLink)
          {
            $PlainTbLinks[] = array("{$table1}.{$field1}", "{$TbLink[0]}.{$TbLink[1]}");
          }
        }
      }
      sort($PlainTbLinks);
    }

    if ($agataDataDescription)
    {
      foreach ($agataDataDescription as $datastructure => $datadescription)
      {
        if (($datastructure) && ($datadescription))
          $PlainDataDescription[] = array("$datastructure", "$datadescription");
      }
      sort($PlainDataDescription);
    }

    return array($PlainTbFamilies, $PlainTbLinks, $PlainDataDescription);
  }

  function WriteProject($project, $dbVars)
  {
    $fd = fopen ("projects/{$project}.prj", "w");
    if ($fd)
    {
      fwrite ($fd, "<?\n");
      fwrite ($fd, "\$Project   = '" . $dbVars['Project'] .  "';\n");
      fwrite ($fd, "\$DbHost    = '" . $dbVars['DbHost'] .   "';\n");
      fwrite ($fd, "\$DbName    = '" . $dbVars['DbName'] .   "';\n");
      fwrite ($fd, "\$DbUser    = '" . $dbVars['DbUser'] .   "';\n");
      fwrite ($fd, "\$DbPass    = '" . $dbVars['DbPass'] .   "';\n");
      fwrite ($fd, "\$DbType    = '" . $dbVars['DbType'] .   "';\n");
      fwrite ($fd, "?>");
    }
    fclose($fd);
  }
  
  function WriteTableFamilies($project, $TbGroups, $TbFamilies)
  {
    $fd = fopen ("projects/{$project}.tbf", "w");
    if ($fd)
    {
      fwrite ($fd, "<?\n");
      fwrite ($fd, "\$Project   = '" . $project .  "';\n");
      
      if ($TbGroups)
      {
        foreach($TbGroups as $TbGroup)
        {
	  fwrite ($fd, "\$TableGroups[]   = '" . $TbGroup .  "';\n");
        }
      }
      if ($TbFamilies)
      {
        foreach($TbFamilies as $TbFamily)
        {
	  $a = $TbFamily[0];
	  $b = $TbFamily[1];
          fwrite ($fd, "\$TableFamilies['$a'][]   = '" . $b .  "';\n");
        }
      }
      fwrite ($fd, "?>");

    }
    fclose($fd);  
  }
  
  function WriteTableLinks($project, $TbLinks)
  {
    $fd = fopen ("projects/{$project}.tbl", "w");
    if ($fd)
    {
      fwrite ($fd, "<?\n");
      fwrite ($fd, "\$Project   = '" . $project .  "';\n");

      if ($TbLinks)
      {
        foreach($TbLinks as $table1 => $table1Links)
        {
	  foreach ($table1Links as $field1 => $TbLink)
	  {
	    $table2 = $TbLink[0];
	    $field2 = $TbLink[1];
            fwrite ($fd, "\$TableLinks['$table1']['$field1']   = array('$table2', '$field2');\n");
	  }
        }
      }
      fwrite ($fd, "?>");

    }
    fclose($fd);  
  }
  
  function WriteDataDescription($project, $DataDescription)
  {
    $fd = fopen ("projects/{$project}.dsd", "w");
    if ($fd)
    {
      fwrite ($fd, "<?\n");
      fwrite ($fd, "\$Project   = '" . $project .  "';\n");

      if ($DataDescription)
      {
        foreach($DataDescription as $datastructure => $datadescription)
        {
          fwrite ($fd, "\$Description['$datastructure'] = '$datadescription';\n");
        }
      }
      fwrite ($fd, "?>");

    }
    fclose($fd);  
  }  
  
  function WriteConfig()
  {
    $fd = fopen ("lib/config.php", "w");
    if ($fd)
    {
      fwrite ($fd, "<?\n");

      foreach($this->agataConfig as $key => $Content)
      {
        foreach ($Content as $Config => $Value)
        {
          fwrite($fd, str_pad('$agataConfig' . "['$key']['$Config'] ",40, ' ', STR_PAD_RIGHT) .  "= '$Value';\n");
	}
	fwrite($fd, "\n");
      }
    
      fwrite($fd, "\n\n");
      
      foreach($this->aDescription as $key => $Config)
      {
        $Description = $this->aDescription[$key];
        fwrite($fd, '$aDescription' . "['$key'] = '$Description';\n");
      }
      fwrite ($fd, "?>\n");
      fclose($fd);  
    }
  }  

  function HandlerFile($extraFunction = null )
  {
    $Verify = $extraFunction[0];
    $Function = $extraFunction[1];
    $message = $extraFunction[2];
    $Mask = $extraFunction[3];
    $DefaultPath = $extraFunction[4];

    eval("\$a = $Verify;");
    if ($a)
    {
      $this->FileSelection = &new GtkFileSelection(Trans::Translate( $Message ));
      $this->FileSelection->hide_fileop_buttons();
      $this->FileSelection->set_position(GTK_WIN_POS_MOUSE);
      $this->FileSelection->show_fileop_buttons();

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        $bar = '\\';
      else
        $bar = '/';
	
      if ($DefaultPath)
      {
        if ((substr($DefaultPath, -1) != '\\') && (substr($DefaultPath, -1) != '/'))
	  $DefaultPath .= $bar;
        $this->FileSelection->set_filename($DefaultPath);
      }

      $this->FileSelection->complete($Mask);

      $button_ok = $this->FileSelection->ok_button;
      $button_ok->connect_object('clicked', array(&$this, $Function), $this->FileSelection);

      $button_cancel = $this->FileSelection->cancel_button;
      $button_cancel->connect_object('clicked', array(&$this,'CloseFileSelection'));

      $action_area = $this->FileSelection->action_area;

      $this->FileSelection->show();
    }
    else
    {
      Dialog::Aviso(Trans::Translate($message));
    }
  }
  
  function CloseFileSelection()
  {
    $this->FileSelection->hide();
  }

  /*******************************************************************************/
  /* Creates a query object
  /*******************************************************************************/ 
  function CreateQuery($agataDB, $sql, $ParametersContent, $isGui,  $type = 'report')
  {
    $CurrentQuery = CreateObject('Agata.AgataQueryData');

    if ($ParametersContent)
    {
      foreach ($ParametersContent as $Parameter => $Content)
      {
        $sql = str_replace($Parameter, $Content, $sql);
      }
    }
    
    if ($sql)
    {
      $conn = CreateObject('Agata.Connection');
      if ($conn->Open($agataDB, $isGui))
      {
        Wait::On($isGui);
        $query = $conn->CreateQuery($sql);
        $Processed = $query->result; 

        if (!$Processed)
        {
          $conn->Close();
          Wait::Off($isGui);
          return false;
        }

        $ColCount = $query->GetColumnCount();

        $Brancos = '                                             ';
        $Brancos .= $Brancos;

        $result = $query->result;
        $line=0;

        $CurrentQuery->Columns = null;
        $CurrentQuery->MaxLen = null;

        if ($type == 'report')
	{
	  while($row=$result->fetchRow())
          {
	    for ($col=1; $col<=$ColCount; $col++)
            {
              $extra = 0;
	      $Conteudo = trim($row[$col-1]);
              $CurrentQuery->Query[$line][$col] = $Conteudo;
	  
	      if ($Conteudo>0)
	        $extra = (int) ((strlen($Conteudo)-1) /3 -1);

              $CurrentQuery->MaxLen[$col] = ($CurrentQuery->MaxLen[$col] > strlen("$Conteudo") + $extra) ?
	                                     $CurrentQuery->MaxLen[$col] : strlen("$Conteudo") + $extra;
            }
            $line ++;
          }
	}
	else if ($type =='graph') // Graphs, less overhead
	{
	  while($row=$result->fetchRow())
          {
	    for ($col=1; $col<=$ColCount; $col++)
            {
              $CurrentQuery->Query[$line][$col] = $row[$col-1];
	      $CurrentQuery->InvQuery[$col][$line] = $row[$col-1];
            }
            $line ++;
          }	
	}
	else if ($type =='merge') // Graphs, less overhead
	{
	  while($row=$result->fetchRow())
          {
	    for ($col=1; $col<=$ColCount; $col++)
            {
              $CurrentQuery->Query[$line][$col] = $row[$col-1];
            }
            $line ++;
          }	
	}	

        $CurrentQuery->ColumnNames = $ColumnNames = $query->GetColumnNames();
        $CurrentQuery->ColumnTypes = $ColumnTypes = $query->GetColumnTypes();

        if ($type == 'report')
	{
	  for ( $x=1; $x<=$ColCount; $x++ )
          {
            $Coluna = $ColumnNames[$x -1];
            $QtdeColunas = $CurrentQuery->MaxLen[$x] - strlen($Coluna);
            $Coluna = $Coluna . (($QtdeColunas>0) ? substr($Brancos, 0, $QtdeColunas *1) : '');
            $CurrentQuery->Columns[] = $Coluna;
            $CurrentQuery->MaxLen[$x] = strlen($Coluna) +3;
          }
	}

        $conn->Close();
        Wait::Off($isGui);
      
        $this->Parameters = null;
	return $CurrentQuery;
      }
    }
  }

  function BlockToSql($Block)
  {
    $sql = '';
    foreach ($Block as $Clause)
    {
      if ($Clause[1])
        $sql .= $Clause[0] . ' ' . $Clause[1] . ' ';
    }
    
    return $sql;
  }

	// Method: ReadSqlFile
	//
	//	Reads Agata formatted sql query file. (The FreeMED
	//	forked version also parses the Merge and SubQuery
	//	fields)
	//
	// Parameters:
	//
	//	$SqlFile - File to read
	//
	// Returns:
	//
	//	Array of values found:
	//	* [0] - Block
	//	* [1] - Breaks
	//	* [2] - Merge
	//	* [3] - SubQuery
	//
  function ReadSqlFile($SqlFile)
  {
    $Clause = array('Select', 'From', 'Where',  'Group by', 'Order by');
  
    if (file_exists($SqlFile))
    {
      $fd = fopen ($SqlFile, "r");
      while (!feof ($fd))
      {
        $buffer = fgets($fd, 5000);
        $buffer = ereg_replace("\n", '', $buffer);
        if ($buffer!='')
        {
          $_Linha = explode(":", trim($buffer));
	  $Linha[0] = $_Linha[0];
	  unset ($_Linha[0]); $Linha[1] = join('', $_Linha);
	  if (substr($buffer,0,1) == ';') { // commenting
	    // do nothing ... comment
          } elseif (in_array($Linha[0], $Clause)) {
            $Block[$Linha[0]] = array($Linha[0],$Linha[1]);
	  } elseif (ereg('@MergeText', $Linha[0])) {
	    $Merge[] = $Linha[1]; 
	  } elseif (ereg('@SubQuery', $Linha[0])) {
	    $SubQuery[] = $Linha[1]; 
	  } elseif (substr($Linha[0],0,1) == '#') {  // break
	      $break = trim(substr($Linha[0],1));
	      $Breaks[$break] = trim($Linha[1]);
	  }
        }
      }

      return array($Block, $Breaks, $Merge, $SubQuery);

      fclose($fd);
    }
    else
    {
      return false;
    }
  }
  


}
?>
