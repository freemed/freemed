<?php

/***********************************************************/
/* PIXMAP'S
/***********************************************************/
global $Pixmaps;
global $vboxmenu;

	$color = &new GdkColor(0, 65535, 65535);


$Pixmaps['folder1'] =   'interface/ico_folder1.xpm';
$Pixmaps['folder2'] =   'interface/ico_folder2.xpm';
$Pixmaps['table']   =   'interface/ico_table.xpm';
$Pixmaps['field']   =   'interface/ico_field.xpm';
$Pixmaps['column']  =   'interface/ico_column.xpm';
$Pixmaps['home']    =   'interface/home.xpm';
$Pixmaps['TrueType']=   'interface/ico_tt.xpm';
$Pixmaps['Symbols'] =   'interface/ico_symbol.xpm';
$Pixmaps['sql']    =    'interface/ico_sql.xpm';
$Pixmaps['agt']    =    'interface/ico_agata.xpm';
$Pixmaps['agl']    =    'interface/ico_label.xpm';
$Pixmaps['agd']    =    'interface/ico_description.xpm';
$Pixmaps['html']   =    'interface/ico_html.xpm';
$Pixmaps['ps']     =    'interface/ico_ps.xpm';
$Pixmaps['pdf']    =    'interface/ico_pdf.xpm';
$Pixmaps['doc']    =    'interface/ico_doc.xpm';
$Pixmaps['png']    =    'interface/ico_png.xpm';
$Pixmaps['jpg']    =    'interface/ico_jpg.xpm';
$Pixmaps['gif']    =    'interface/ico_gif.xpm';
$Pixmaps['cdr']    =    'interface/ico_cdr.xpm';
$Pixmaps['xpm']    =    'interface/ico_xpm.xpm';
$Pixmaps['zip']    =    'interface/ico_zip.xpm';
$Pixmaps['txt']    =    'interface/ico_txt.xpm';
$Pixmaps['csv']    =    'interface/ico_csv.xpm';
$Pixmaps['dia']    =    'interface/ico_dia.xpm';
$Pixmaps['xml']    =    'interface/ico_xml.xpm';
$Pixmaps['sxw']    =    'interface/ico_sxw.xpm';
$Pixmaps['fun']    =    'interface/ico_php.xpm';
$Pixmaps['php']    =    'interface/ico_php.xpm';
$Pixmaps['del']    =    'interface/del.xpm';
$Pixmaps['db']     =     'interface/ico_db.xpm';
$Pixmaps['ok']     =     'interface/ico_ok.xpm';
$Pixmaps['cancel'] =     'interface/ico_cancel.xpm';
$Pixmaps['generic']=    'interface/ico_generic.xpm';
$Pixmaps['level']  =    'interface/ico_level.xpm';
$Pixmaps['require']=    'interface/ico_require.xpm';
$Pixmaps['connect']=    'interface/connect.xpm';
$Pixmaps['remote']=     'interface/ico_remote.xpm';
$Pixmaps['publish']=     'interface/ico_publish.xpm';
$Pixmaps['measures']=    'interface/ico_measures.xpm';

$Pixmaps['menu_connect'] =    'interface/menu_connect.xpm';
$Pixmaps['menu_config']  =    'interface/menu_config.xpm';
$Pixmaps['menu_schemas'] =    'interface/ico_schemas.xpm';
$Pixmaps['ico_colors']   =    'interface/ico_colors.xpm';
$Pixmaps['menu_replace'] =    'interface/menu_replace.xpm';
$Pixmaps['menu_del']     =    'interface/menu_del.xpm';
$Pixmaps['menu_close']   =    'interface/menu_close.xpm';
$Pixmaps['menu_new']     =    'interface/menu_new.xpm';
$Pixmaps['menu_open']    =    'interface/menu_open.xpm';
$Pixmaps['menu_save']    =    'interface/menu_save.xpm';
$Pixmaps['menu_saveas']  =    'interface/menu_saveas.xpm';
$Pixmaps['menu_page']    =    'interface/menu_page.xpm';
$Pixmaps['menu_quit']    =    'interface/menu_quit.xpm';
$Pixmaps['menu_execute'] =    'interface/menu_execute.xpm';
$Pixmaps['menu_txt']     =    'interface/menu_txt.xpm';
$Pixmaps['menu_pdf']     =    'interface/menu_pdf.xpm';
$Pixmaps['menu_html']    =   'interface/menu_html.xpm';
$Pixmaps['menu_ps']      =   'interface/menu_ps.xpm';
$Pixmaps['menu_dbf']     =   'interface/menu_dbf.xpm';
$Pixmaps['menu_csv']     =   'interface/menu_csv.xpm';
$Pixmaps['menu_dia']     =   'interface/menu_dia.xpm';
$Pixmaps['menu_function']=   'interface/menu_function.xpm';
$Pixmaps['menu_help']    =   'interface/menu_help.xpm';
$Pixmaps['menu_prop']    =    'interface/ico_properties.xpm';

$Pixmaps['file_open']    =   'interface/fileopen.xpm';
$Pixmaps['file_del']     =   'interface/filedelete.xpm';
$Pixmaps['folder_new']   =   'interface/foldernew.xpm';

$Pixmaps['popdate']      =   'interface/popdate.xpm';
$Pixmaps['ico_cal']      =   'interface/ico_cal.xpm';
$Pixmaps['ico_chars']    =   'interface/ico_chars.xpm';
$Pixmaps['cor']          =   'interface/cor.xpm';
$Pixmaps['font']         =   'interface/font.xpm';
$Pixmaps['file']         =   'interface/filesel.xpm';
$Pixmaps['icon']         =   'icon.xpm';
$Pixmaps['ico_fun']      =   'interface/ico_fun.xpm';
$Pixmaps['ico_format']   =   'interface/ico_format.xpm';
$Pixmaps['ico_join']     =   'interface/ico_join.xpm';
$Pixmaps['ico_edit']     =   'interface/ico_edit.xpm';
$Pixmaps['ico_desc']     =   'interface/ico_description.xpm';

/***********************************************************/
/* PARAMETERS
/***********************************************************/ 
$Alert1        = 'You Have to build the SQL Query firstly';
$Alert2        = 'Require Tables, please';

$OutputDir     = $this->agataConfig['general']['OutputDir'];
$RptDir        = $this->agataConfig['general']['RptDir'];
$SqlDir        = $this->agataConfig['general']['SqlDir'];
$AgataDir      = $this->agataConfig['general']['AgataDir'];
$img_mimetypes = array('png', 'jpg', 'jpeg');
$file_mimetypes= array('txt');

$button1 = array('interface/output.xpm', _a('Output'), $OutputDir);
$button2 = array('interface/agata.xpm', _a('Reports'), $RptDir);
$button3 = array('interface/sql.xpm', _a('Queries'), $SqlDir);

$graph_ext = array('html', 'sxw');
$this->param['SaveSql'] =   array(array(&$this, 'SaveSQL'),    'sql',   $SqlDir,    _a('Save SQL'),    $button3);
$this->param['SaveRep'] =   array(array(&$this, 'SaveReport'), 'agt',   $RptDir,    _a('Save Report'), $button2);
$this->param['OpenRep'] =   array(array(&$this, 'OpenReport'), 'agt',   $RptDir,    _a('Open Report'), $button2);


/***********************************************************/
/* COMBOS
/***********************************************************/
$this->comboDBFunctions = $this->glade->get_widget('comboDBFunctions');
$this->comboLegend = $this->glade->get_widget('comboLegend');
$this->comboLabelFormat=$this->glade->get_widget('comboLabelFormat');


$db_functions = array(  _a('Sum'), _a('Count'), _a('Count distinct'),
                        _a('Average'), _a('Minimal'), _a('Maximal'));
//$this->comboDBFunctions->set_popdown_strings($db_functions);


		$item1= new GtkListStore(Gtk::TYPE_STRING);	
		$this->comboDBFunctions->clear();	
		$this->comboDBFunctions->set_model($item1);
		$cellRenderer = new GtkCellRendererText(); // note 2
		$this->comboDBFunctions->pack_start($cellRenderer);
		$this->comboDBFunctions->set_attributes($cellRenderer, 'text', 0); // note 3
		foreach($db_functions as $Element0)
		{ 
		    $item1->append(array($Element0));
		}

/***********************************************************/
/* TEXT'S
/***********************************************************/
$this->textExpression                   = $this->glade->get_widget('textExpression');
$this->textAreas['@ReportDescription']  = $this->glade->get_widget('textDescription');
$this->textAreas['@ManualQuery']        = $this->glade->get_widget('textFreeSelect');
$this->textAreas['@GraphIntroduction']  = $this->glade->get_widget('introduction');

/***********************************************************/
/* ENTRY'S
/***********************************************************/    
$this->entryTitle           = $this->glade->get_widget( 'title' );
$this->entryTitlex          = $this->glade->get_widget( 'xtitle' );
$this->entryTitley          = $this->glade->get_widget( 'ytitle' );
$this->entrySizeX           = $this->glade->get_widget( 'spinGraphWidth' );
$this->entrySizeY           = $this->glade->get_widget( 'spinGraphHeight' );    
$this->entryLeftMargin      = $this->glade->get_widget( 'entryLeftMargin' );
$this->entryTopMargin       = $this->glade->get_widget( 'entryTopMargin' );
$this->entrySpacing         = $this->glade->get_widget( 'entrySpacing' );
$this->entryDataDescription = $this->glade->get_widget( 'entryDataDescription' );


/***********************************************************/
/* LABEL'S
/***********************************************************/    
$this->Labels['labelf1'] = $this->glade->get_widget('labelf1');
$this->Labels['labelf2'] = $this->glade->get_widget('labelf2');
$this->Labels['labelf3'] = $this->glade->get_widget('labelf3');
$this->Labels['labelf4'] = $this->glade->get_widget('labelf4');
$this->Labels['labelSelect'] = $this->glade->get_widget('labelSelect');
$this->Labels['labelDescription'] = $this->glade->get_widget('labelDescription');
$this->Labels['labelFreeselect'] = $this->glade->get_widget('labelFreeselect');
$this->Labels['labelTable2'] = $this->glade->get_widget('labelTable2');
$this->Labels['labelBlock'] = $this->glade->get_widget('labelBlock');
$this->Labels['labelContent'] = $this->glade->get_widget('labelContent');
$this->Labels['labelTablestructure'] = $this->glade->get_widget('labelTableStructure');
$this->Labels['labelSavedqueries'] = $this->glade->get_widget('labelSavedQueries');
$this->Labels['labelRepository'] = $this->glade->get_widget('labelRepository');
$this->Labels['labelSubTotal'] = $this->glade->get_widget('labelSubTotal');
$this->Labels['labelPerLines'] = $this->glade->get_widget('labelPerLines');
$this->Labels['labelPerColumns'] = $this->glade->get_widget('labelPerColumns');
$this->Labels['labelConfig'] = $this->glade->get_widget('labelConfig');
$this->Labels['labelSpacing'] = $this->glade->get_widget('labelSpacing');
$this->Labels['labelLeftMargin'] = $this->glade->get_widget('labelLeftMargin');
$this->Labels['labelTopMargin'] = $this->glade->get_widget('labelTopMargin');
$this->Labels['labelHeaders'] = $this->glade->get_widget('labelHeaders');
$this->Labels['labelRequire'] = $this->glade->get_widget('labelRequire');
$this->Labels['labelJoin'] = $this->glade->get_widget('labelJoin');
$this->Labels['labelClear'] = $this->glade->get_widget('labelClear');
$this->Labels['labelApplyFunction'] = $this->glade->get_widget('labelApplyFunction');
$this->Labels['labelApplyOrder'] = $this->glade->get_widget('labelApplyOrder');
$this->Labels['labelSelectColumn'] = $this->glade->get_widget('labelSelectColumn');
$this->Labels['labelInsertParameter'] = $this->glade->get_widget('labelInsertParameter');
$this->Labels['labelClearExpr'] = $this->glade->get_widget('labelClearExpr');
$this->Labels['labelWriteItDown'] = $this->glade->get_widget('labelWriteItDown');
$this->Labels['labelGroupIt'] = $this->glade->get_widget('labelGroupIt');
$this->Labels['labelBreakIt'] = $this->glade->get_widget('labelBreakIt');
$this->Labels['labelPageHeader'] = $this->glade->get_widget('labelPageHeader');
$this->Labels['labelPageFooter'] = $this->glade->get_widget('labelPageFooter');
$this->Labels['labelImportSql'] = $this->glade->get_widget('labelImportSql');
$this->Labels['labelGraphWidth'] = $this->glade->get_widget('labelGraphWidth');
$this->Labels['labelGraphHeight'] = $this->glade->get_widget('labelGraphHeight');
$this->Labels['labelSubQuery'] = $this->glade->get_widget('labelSubQuery');
$this->Labels['labelTemplates'] = $this->glade->get_widget('labelTemplates');


/***********************************************************/
/* CONTAINERS
/***********************************************************/    
$this->Containers['vboxButtondel'] = $this->glade->get_widget('vboxButtondel');
$this->Containers['vboxButtonSubDel'] = $this->glade->get_widget('vboxButtonSubDel');
$this->Containers['vboxEisgenerate'] = $this->glade->get_widget('vboxEisgenerate');
$this->Containers['vboxEisbuttons'] = $this->glade->get_widget('vboxEisbuttons');
$this->Containers['vboxConstraints'] = $this->glade->get_widget('vboxConstraints');
$this->Containers['vboxApplyFunction'] = $this->glade->get_widget('vboxApplyFunction');
$this->Containers['vboxSelectButton'] = $this->glade->get_widget('vboxSelectButton');
$this->Containers['vboxSavedQueries'] = $this->glade->get_widget('vboxSavedQueries');
$this->Containers['vboxRepository'] = $this->glade->get_widget('vboxRepository');
$this->Containers['hboxFrame1'] = $this->glade->get_widget('hboxFrame1');
$this->Containers['hboxFrame2'] = $this->glade->get_widget('hboxFrame2');
$this->Containers['hboxFrame3'] = $this->glade->get_widget('hboxFrame3');
$this->Containers['hboxFrame4'] = $this->glade->get_widget('hboxFrame4');
$this->Containers['hboxParameter'] = $this->glade->get_widget('hboxParameter');
$this->Containers['hboxGroup'] = $this->glade->get_widget('hboxGroup');
$this->Containers['hboxFormula'] = $this->glade->get_widget('hboxFormula');
$this->Containers['hboxBreaks'] = $this->glade->get_widget('hboxBreaks');
$this->Containers['hboxConfigEis'] = $this->glade->get_widget('hboxConfigEis');
$this->Containers['hboxCreateDiagram'] = $this->glade->get_widget('hboxCreateDiagram');
$this->Containers['hboxPerLines'] = $this->glade->get_widget('hboxPerLines');
$this->Containers['hboxPlottedColumns'] = $this->glade->get_widget('hboxPlottedColumns');
$this->Containers['hboxTableStructure'] = $this->glade->get_widget('hboxTableStructure');
$this->Containers['hboxFunctions'] = $this->glade->get_widget('hboxFunctions');
$this->Containers['hboxMainMenu'] = $this->glade->get_widget('hboxMainMenu');
$this->Containers['boxConnection'] = $this->glade->get_widget('hboxConnection');
$this->Containers['vboxLabelButtons'] = $this->glade->get_widget('vboxLabelButtons');
$this->Containers['scrolledwindowMerge'] = $this->glade->get_widget('scrolledwindowMerge');
$this->Containers['scrolledwindowTables'] = $this->glade->get_widget('scrolledwindowTables');
$this->Containers['framePlottedColumns'] = $this->glade->get_widget('framePlottedColumns');
$this->Containers['frameLegend'] = $this->glade->get_widget('frameLegend');
$this->Containers['frameTitle'] = $this->glade->get_widget('frameTitle');
$this->Containers['frameX'] = $this->glade->get_widget('frameX');
$this->Containers['frameY'] = $this->glade->get_widget('frameY');
$this->Containers['frameDimension'] = $this->glade->get_widget('frameDimension');
$this->Containers['frameGenerate'] = $this->glade->get_widget('frameGenerate');
$this->Containers['frameIntroduction'] = $this->glade->get_widget('frameIntroduction');
$this->Containers['frameOutput'] = $this->glade->get_widget('frameOutput');
$this->Containers['frameSelection'] = $this->glade->get_widget('frameSelection');
$this->Containers['frameOrder'] = $this->glade->get_widget('frameOrder');
$this->Containers['frameLinks'] = $this->glade->get_widget('frameLinks');
$this->Containers['frameExpression'] = $this->glade->get_widget('frameExpression');
$this->Containers['frameFormulas'] = $this->glade->get_widget('frameFormulas');
$this->Containers['frameSelect'] = $this->glade->get_widget('frameSelect');
$this->Containers['frameGenerate'] = $this->glade->get_widget('frameGenerate');
$this->Containers['frameLevels'] = $this->glade->get_widget('frameLevels');
$this->Containers['frameOrientation'] = $this->glade->get_widget('frameOrientation');
$this->Containers['frameLabelConfiguration'] = $this->glade->get_widget('frameLabelConfiguration');
$this->Containers['frameCommonFunction'] = $this->glade->get_widget('frameCommonFunction');
//$this->Containers['frameOptions'] = $this->glade->get_widget('frameOptions');
$this->Containers['boxLabel'] = $this->glade->get_widget('boxLabel');
$this->Containers['boxHeader'] = $this->glade->get_widget('boxHeader');
$this->Containers['boxFooter'] = $this->glade->get_widget('boxFooter');
$this->Containers['boxFinalSummary'] = $this->glade->get_widget('boxFinalSummary');
$this->Containers['boxGroupFooter'] = $this->glade->get_widget('boxGroupFooter');
$this->Containers['boxGroupHeader'] = $this->glade->get_widget('boxGroupHeader');
$this->Containers['boxDetail'] = $this->glade->get_widget('boxDetail');
$this->Containers['hboxLevelsTitle'] = $this->glade->get_widget('hboxLevelsTitle');
$this->Containers['hboxHeadersTitle'] = $this->glade->get_widget('hboxHeadersTitle');
$this->Containers['hboxSelect'] = $this->glade->get_widget('hboxSelect');
$this->Containers['vboxAdjustments'] = $this->glade->get_widget('vboxAdjustments');
$this->Containers['vboxSubAdjustments'] = $this->glade->get_widget('vboxSubAdjustments');
$this->Containers['vboxTemplates'] = $this->glade->get_widget('vboxTemplates');
$this->Containers['hboxCloseSubWindow'] = $this->glade->get_widget('hboxCloseSubWindow');
$this->Containers['hboxMainQueryField'] = $this->glade->get_widget('hboxMainQueryField');
$this->Containers['viewportFixed'] = $this->glade->get_widget('viewportFixed');
$this->Containers['hboxOpenOffice'] = $this->glade->get_widget('hboxOpenOffice');
$this->Containers['hboxCrossTab'] = $this->glade->get_widget('hboxCrossTab');
$vboxmenu= $this->glade->get_widget('hboxMainMenu');

//------BOTON QUE PERMITE SELECCIONAR  LAS TABLA DE BASE DE  DATOS
$this->Buttons['RequireTables'] = 	$this->glade->get_widget('buttonRequire');

$this->Buttons['Clear'] = 		    $this->glade->get_widget('buttonClear');
$this->Buttons['ApplyFunction'] = 	$this->glade->get_widget('buttonApplyFunction');
$this->Buttons['ApplyOrder'] =	    $this->glade->get_widget('buttonApplyOrder');

//-----BOTON QUE PERMITE SELECCIONAR EL CAMPO DE LA TABLA PARA ARMAR QUERY
$this->Buttons['SelectColumn'] =	$this->glade->get_widget('buttonSelectColumn');
$this->Buttons['InsertParameter'] = $this->glade->get_widget('buttonInsertParameter');
$this->Buttons['ClearExpr'] =       $this->glade->get_widget('buttonClearExpr');
$this->Buttons['WriteItDown'] =     $this->glade->get_widget('buttonWriteItDown');
$this->Buttons['GraphLines'] =      $this->glade->get_widget('buttonGraphLines');
$this->Buttons['GraphBars'] =       $this->glade->get_widget('buttonGraphBars');
$this->Buttons['SaveMerge'] =       $this->glade->get_widget('buttonSaveMerge');
$this->Buttons['BarCode'] =         $this->glade->get_widget('buttonBarCode');
$this->Buttons['ImportSql'] =       $this->glade->get_widget('buttonImportSql');
$this->Buttons['Join'] =            $this->glade->get_widget('buttonJoin');
$this->Buttons['ReloadReports'] =   $this->glade->get_widget('buttonReloadReports');
$this->Buttons['ReloadRepository'] =$this->glade->get_widget('buttonReloadRepository');
$this->Buttons['DownSql'] =         $this->glade->get_widget('buttonDownSql');
$this->Buttons['UpSql'] =           $this->glade->get_widget('buttonUpSql');



$this->tooltips->set_tip($this->Buttons['ReloadReports'], _a('Reload'));
$this->tooltips->set_tip($this->Buttons['ReloadRepository'], _a('Reload'));

$this->Buttons['RequireTables']->connect_simple('clicked', array(&$this, 'RequireTables'));;
$this->Buttons['Clear']->connect_simple('clicked', array(&$this, 'ClearTables'));
$this->Buttons['ApplyFunction']->connect_simple('clicked', array(&$this, 'ApplyFunction'));
$this->Buttons['ApplyOrder']->connect_simple('clicked', array(&$this, 'InsertOnBlock'), 'Order by');
$this->Buttons['SelectColumn']->connect_simple('clicked', array(&$this, 'InsertOnBlock'), 'Select');
$this->Buttons['InsertParameter']->connect_simple('clicked', array(&$this, 'PopParameters'));
$this->Buttons['ClearExpr']->connect_simple('clicked', array(&$this, 'ClearExpr'));
$this->Buttons['WriteItDown']->connect_simple('clicked', array(&$this, 'WriteItDown'));
$this->Buttons['GraphLines']->connect_simple('clicked', array(&$this, 'PreReport'), 'lines');
$this->Buttons['GraphBars']->connect_simple('clicked', array(&$this, 'PreReport'), 'bars');
$this->Buttons['Join']->connect_simple('clicked', array(&$this, 'JoinTables'));
$this->Buttons['ImportSql']->connect_simple('clicked', array(&$this, 'ImportSql'));
$this->Buttons['ReloadReports']->connect_simple('clicked', array(&$this, 'RefreshReports'));
$this->Buttons['ReloadRepository']->connect_simple('clicked', array(&$this, 'RefreshRepository'));
$this->Buttons['UpSql']->connect_simple('clicked', array(&$this, 'UpSql'));
$this->Buttons['DownSql']->connect_simple('clicked', array(&$this, 'DownSql'));

$this->tooltips->set_tip($this->Buttons['UpSql'],   _a('Copy')  . ' ' . _a('Query'));
$this->tooltips->set_tip($this->Buttons['DownSql'], _a('Paste') . ' ' . _a('Query'));
$this->Buttons['ClearExpr']->set_relief(GTK_RELIEF_NONE);
$this->Buttons['WriteItDown']->set_relief(GTK_RELIEF_NONE);
$this->Buttons['BarCode']->set_relief(GTK_RELIEF_NONE);
$this->Buttons['ImportSql']->set_relief(GTK_RELIEF_NONE);

/***********************************************************/
/* CHECK AND RADIO BUTTONS
/***********************************************************/    
$this->radios['checkShowGroup']  = $this->glade->get_widget('checkShowGroup');
$this->radios['checkShowTotal']  = $this->glade->get_widget('checkShowTotal');
$this->radios['checkShowDetail'] = $this->glade->get_widget('checkShowDetail');
$this->radios['checkShowNumber'] = $this->glade->get_widget('checkShowNumber');
$this->radios['checkShowIndent'] = $this->glade->get_widget('checkShowIndent');

$this->radios['checkShowTotal']->connect_simple('toggled', array(&$this, 'ToggleDetail'));

$this->radios['AscendantOrder'] = $this->glade->get_widget('radioAscendantOrder');
$this->radios['DescendantOrder'] = $this->glade->get_widget('radioDescendantOrder');

$this->radios['radioLines']   = $this->glade->get_widget('radioLines');
$this->radios['radioColumns'] = $this->glade->get_widget('radioColumns');
$this->radios['radioSXW']     = $this->glade->get_widget('radioSXW');
$this->radios['radioHTML']    = $this->glade->get_widget('radioHTML');

$this->radios['checkData'] = $this->glade->get_widget('checkData');
$this->radios['showValues'] = $this->glade->get_widget('showValues');
$this->radios['radioColumns']->connect_simple('pressed', array(&$this, 'ChangePageGraph'), 0);
$this->radios['radioLines']->connect_simple('pressed', array(&$this, 'ChangePageGraph'), 1);

$this->radios['checkShowGroup']->set_active(false);
$this->radios['checkShowDetail']->set_active(true);
$this->radios['checkShowNumber']->set_active(true);
$this->radios['checkShowIndent']->set_active(true);
$this->radios['checkData']->set_active(true);
$this->radios['showValues']->set_active(true);

/***********************************************************/
/* PAGES, LISTS AND TREES
/***********************************************************/    
$this->QueryNotebook        = $this->glade->get_widget('QueryNotebook');
$this->GraphNotebook        = $this->glade->get_widget('GraphNotebook');
$this->AgataNotebook        = $this->glade->get_widget('AgataNotebook');

//$this->AgataNotebook->connect('key_press_event', array($this, 'nextTab'));

//$this->AgataNotebook->connect('button_press_event', array($this, 'nextTab'));


/***********************************************************/
/* TREEVIEW
/*        MODIFICADO POR MIGUEL CASANOVA                    */
/***********************************************************/    
$this->TreeTablestructure        = $this->glade->get_widget('treeviewTablestructure');
$this->TreeSavedqueries          = $this->glade->get_widget('treeviewSavedqueries');
$this->TreeSelect        	 = $this->glade->get_widget('treeviewSelect');
$this->TreeRepository            = $this->glade->get_widget('treeviewRepository');


?>
