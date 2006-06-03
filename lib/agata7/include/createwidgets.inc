<?php
/***********************************************************/
/* Reports File Tree
/***********************************************************/
$extensions = array('.agt');
$this->ctreeReports = &new FileTree();
$this->ctreeReports->set_column_width(0,400);
$this->ctreeReports->DefineExtensions($extensions);
$this->ctreeReports->AbreDiretorio($this->agataConfig['general']['RptDir'], null);
$this->ctreeReports->set_column_width(0, 600);

$ReportsHandler = &new TreeHandler($this->ctreeReports);
$ItemOpen = $ReportsHandler->OpenAction;
$ItemOpen->connect_object('clicked', array(&$this, 'OpenReportFromTree'));
$this->Containers['vboxSavedQueries']->pack_start($ReportsHandler);
$ReportsHandler->CreateToolBar();

/***********************************************************/
/* SQL File Tree
/***********************************************************/
$extensions = array('.sql');
$this->ctreeTemplates = &new FileTree();
$this->ctreeTemplates->set_column_width(0,400);
$this->ctreeTemplates->DefineExtensions($extensions);
$this->ctreeTemplates->SetShowExtension(false);
$this->ctreeTemplates->AbreDiretorio($this->agataConfig['general']['SqlDir'], null);
$this->ctreeTemplates->set_column_width(0, 600);

$TemplatesHandler = &new TreeHandler($this->ctreeTemplates, null, true);
$ItemOpen = $TemplatesHandler->OpenAction;
$ItemOpen->connect_object('clicked', array(&$this, 'OpenSQLFromTree'));
$this->Containers['vboxTemplates']->pack_start($TemplatesHandler);
$TemplatesHandler->CreateToolBar();


/***********************************************************/
/* Repository File Tree
/***********************************************************/
$extensions = array('.html', '.ps', '.pdf', '.txt', '.dia', '.xml', '.csv', '.sxw');
$this->ctreeRepository = &new FileTree();
$this->ctreeRepository->DefineExtensions($extensions);
$this->ctreeRepository->AbreDiretorio($this->agataConfig['general']['OutputDir'], null);
$this->ctreeRepository->set_column_width(0, 600);

$RepositoryHandler = &new TreeHandler($this->ctreeRepository);
$ItemOpen = $RepositoryHandler->OpenAction;
$ItemOpen->connect_object('clicked', array(&$this, 'OpenRepository'));
$this->Containers['vboxRepository']->pack_start($RepositoryHandler);
$RepositoryHandler->CreateToolBar();


/***********************************************************/
/* Plotted Columns
/***********************************************************/
$this->PlottedColumns = new Alist(&$this->Containers['hboxPlottedColumns'], $this->window, _a('Fields'),
                        array(_a('Fields')), null, 400, 200, false, array(null),
                        GTK_SELECTION_EXTENDED, array('interface/ico_field.xpm'));

$this->PlottedColumns->clist->set_column_width(0, 400);


/***********************************************************/
/* Tree's
/***********************************************************/
$this->ctreeTables          = new SimpleTree(_a('Tables'));
$this->ctreeTablesDia       = new SimpleTree(_a('Tables'));
$this->Containers['scrolledwindowTables']->add($this->ctreeTables);

$this->ctreeTableStructure  = &new TableTree('', true);
$this->ctreeTableStructure->set_selection_mode(GTK_SELECTION_EXTENDED);
$this->ctreeTableStructure->set_column_width(0, 600);
$this->ctreeTableStructure->column_titles_hide();
$this->ctreeTableStructure->connect_object('tree-select-row', array(&$this, 'ChangePageSQL'));
$this->ctreeTableStructure->connect_object('tree-select-row', array(&$this, 'PopTableStructure'));
$this->Containers['hboxTableStructure']->add($this->ctreeTableStructure);

/***********************************************************/
/* Select and SubSelect List's
/***********************************************************/
$this->SelectList    = &new SelectList(null);
$this->SelectList->connect_object('return-select', array(&$this, 'ReturnSelectList'));
$this->SelectList->connect_object('del-block',     array(&$this, 'DelBlock'));

$this->Containers['hboxSelect']->add($this->SelectList->widget);



/***********************************************************/
/* Adjustments
/***********************************************************/
$hbox = &new GtkHBox;
$this->Containers['vboxAdjustments']->add($hbox);
$this->AdjustmentsConfig = &new Preferences(&$hbox);
$this->AdjustmentsConfig->SetTitle(_a('Adjustments'));
$this->AdjustmentsConfig->SetOptions(false);
$this->AdjustmentsConfig->SetPlus($Plus);
$this->AdjustmentsConfig->SetOk($slot, _a('Save'));
$this->AdjustmentsConfig->SetSimpleColumn(true);
$this->AdjustmentsConfig->SetSaveButton(false);
$this->AdjustmentsConfig->SetPixmap(images . 'ico_field.xpm');
$this->AdjustmentsConfig->SetListTitle(_a('Column'));

$this->AdjustmentsConfig->BuildForms($Vars);
$this->AdjustmentsConfig->clistProjects->set_usize(180,-1);
$this->AdjustmentsConfig->MultiPage->set_usize(220,-1);
$this->Containers['vboxAdjustments']->show_all();

$hbox = &new GtkHBox;
$this->Containers['vboxSubAdjustments']->add($hbox);
$this->SubAdjustmentsConfig = &new Preferences(&$hbox);
$this->SubAdjustmentsConfig->SetTitle(_a('Adjustments'));
$this->SubAdjustmentsConfig->SetOptions(false);
$this->SubAdjustmentsConfig->SetPlus($Plus);
$this->SubAdjustmentsConfig->SetOk($slot, _a('Save'));
$this->SubAdjustmentsConfig->SetSimpleColumn(true);
$this->SubAdjustmentsConfig->SetSaveButton(false);
$this->SubAdjustmentsConfig->SetPixmap(images . 'ico_field.xpm');
$this->SubAdjustmentsConfig->SetListTitle(_a('Column'));

$this->SubAdjustmentsConfig->BuildForms($Vars);
$this->SubAdjustmentsConfig->clistProjects->set_usize(180,-1);
$this->SubAdjustmentsConfig->MultiPage->set_usize(220,-1);
$this->Containers['vboxSubAdjustments']->show_all();
?>
