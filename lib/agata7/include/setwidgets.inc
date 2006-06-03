<?php
    /***********************************************************/
    /* PARAMETERS FOR BUTTONS
    /***********************************************************/

    $this->Labels['labelf1']->set_text(' ' . _a('Tables') . ' ');
    $this->Labels['labelf2']->set_text(' ' . _a('Selection') . ' ');
    $this->Labels['labelf3']->set_text(' ' . _a('Constraints') . ' ');
    $this->Labels['labelf4']->set_text(' ' . _a('Adjustments') . ' ');
    $this->Labels['labelDescription']->set_text(_a('Report Description'));
    $this->Labels['labelFreeselect']->set_text(_a('Here you may type your free select'));
    $this->Labels['labelSelect']->set_text(_a('Click on line to edit the content'));
    $this->Labels['labelTablestructure']->set_text(_a('Tables Structure'));
    $this->Labels['labelSavedqueries']->set_text(_a('Reports'));
    $this->Labels['labelRepository']->set_text(_a('Repository'));
    $this->Labels['labelSubTotal']->set_text(' ' . _a('Groups') . ' ');
    $this->Labels['labelPerLines']->set_text(_a('Per lines'));
    $this->Labels['labelPerColumns']->set_text(_a('Per columns'));
    $this->Labels['labelHeaders']->set_text(_a('Headers and Footers'));
    $this->Labels['labelRequire']->set_text(' ' . _a('Require Tables') . ' ');
    $this->Labels['labelJoin']->set_text(' ' . _a('Join') . ' ');
    $this->Labels['labelClear']->set_text(' ' . _a('Clear') . ' ');
    $this->Labels['labelApplyFunction']->set_text(_a('Apply'));
    //$this->Labels['labelApplyRepository']->set_text(_a('Apply'));
    $this->Labels['labelApplyOrder']->set_text(_a('Apply'));
    $this->Labels['labelSelectColumn']->set_text(_a('Select this column'));
    //$this->Labels['labelCrossDB']->set_text(_a('Cross DataBase'));
    $this->Labels['labelInsertParameter']->set_text(_a('Insert Parameter'));
    $this->Labels['labelClearExpr']->set_text(' ' . _a('Clear') . ' ');
    $this->Labels['labelWriteItDown']->set_text(' ' . _a('Apply') .  ' ');
    $this->Labels['labelGroupIt']->set_text(_a('Apply'));
    $this->Labels['labelBreakIt']->set_text(_a('Apply'));
    $this->Labels['labelPageHeader']->set_text(_a('Page Header'));
    $this->Labels['labelPageFooter']->set_text(_a('Page Footer'));
    $this->Labels['labelImportSql']->set_text(' ' . _a('Import') .  ' ');
    $this->Labels['labelGraphWidth']->set_text(_a('Width') . ': ');
    $this->Labels['labelGraphHeight']->set_text(_a('Height') . ': ');
    $this->Labels['labelSubQuery']->set_text(_a('SubQuery'));
    $this->Labels['labelTemplates']->set_text(_a('Query Templates'));

    /***********************************************************/
    /* FRAMES SET LABEL
    /***********************************************************/
    $this->Containers['hboxFrame1']->pack_start(darktxt(_a('Require tables to proceed the selection')));
    $this->Containers['hboxFrame2']->pack_start(darktxt(_a('Type of Selection on Field')));
    $this->Containers['hboxFrame3']->pack_start(darktxt(_a('Rules, Constraints of Report')));
    $this->Containers['hboxFrame4']->pack_start(darktxt(_a('Adjustments')));
    $this->Containers['hboxLevelsTitle']->pack_start(darktxt(_a('Report Levels')));
    $this->Containers['hboxHeadersTitle']->pack_start(darktxt(_a('Headers and Footers')));
    $this->Containers['hboxOpenOffice']->pack_start(darktxt(_a('Parse OpenOffice Document')));
    $this->Containers['frameLinks']->set_label(_a('Links'));
    $this->Containers['frameExpression']->set_label(_a('Expression'));
    $this->Containers['frameSelect']->set_label(_a('Query'));
    $this->Containers['frameSelection']->set_label(_a('Selection'));
    $this->Containers['frameOrder']->set_label(_a('Ordering'));
    $this->Containers['frameLevels']->set_label(_a('Report Levels'));
    $this->Containers['frameTitle']->set_label(_a('Title'));
    $this->Containers['frameX']->set_label('X ' . _a('Title'));
    $this->Containers['frameY']->set_label('Y ' . _a('Title'));
    $this->Containers['frameDimension']->set_label(_a('Dimension'));
    $this->Containers['frameGenerate']->set_label(_a('Generate'));
    $this->Containers['frameIntroduction']->set_label(_a('Introduction'));
    $this->Containers['frameOutput']->set_label(_a('Output'));    
    $this->Containers['frameOrientation']->set_label(_a('Orientation'));
    $this->Containers['framePlottedColumns']->set_label(_a('Plotted Columns'));
    $this->Containers['frameLegend']->set_label(_a('Legend'));
	$this->Containers['frameLabelConfiguration']->set_label(_a('Configuration'));
	$this->Containers['frameCommonFunction']->set_label(_a('Apply Function'));

    /***********************************************************/
    /* BUTTONS SET LABEL
    /***********************************************************/
    $labelShowGroup = $this->radios['checkShowGroup']->child;
    $labelShowTotal = $this->radios['checkShowTotal']->child;
    $labelShowDetail = $this->radios['checkShowDetail']->child;
    $labelShowNumber = $this->radios['checkShowNumber']->child;
    $labelShowIndent = $this->radios['checkShowIndent']->child;

    $labelradioLines = $this->radios['radioLines']->child;
    $labelradioColumns = $this->radios['radioColumns']->child;

    $labelradioAscendantOrder = $this->radios['AscendantOrder']->child;
    $labelradioDescendantOrder = $this->radios['DescendantOrder']->child;
    $labelShowValues = $this->radios['showValues']->child;
    $labelCheckData = $this->radios['checkData']->child;

    $labelShowGroup->set_text(_a('Show Group Columns'));
    $labelShowTotal->set_text(_a('Show total labels'));
    $labelShowDetail->set_text(_a('Show Detail'));
    $labelShowNumber->set_text(_a('Show Index Number'));
    $labelShowIndent->set_text(_a('Show Indentation'));
    $labelradioAscendantOrder->set_text(_a('Ascendant Order'));
    $labelradioDescendantOrder->set_text(_a('Descendant Order'));
    $labelradioLines->set_text(_a('Per lines'));
    $labelradioColumns->set_text(_a('Per columns'));
    $labelCheckData->set_text(_a('Graph+data'));
    $labelShowValues->set_text(_a('Show Values'));
    
    # CONSTRAINT Butons
    for ($n=1; $n<=12; $n++)
    {
      $buttonRel = $this->glade->get_widget('buttonRel'.$n);
      $label = $buttonRel->child;
      $text = $label->get();
      $buttonRel->connect_object('clicked', array(&$this,'InsertOnBlock'), 'Where', $text);
    }
?>
