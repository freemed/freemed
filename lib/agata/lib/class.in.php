<?php
class in extends AgataCore
{
  function in($aAgataConfig)
  {
    $this->aAgataConfig = $aAgataConfig;
 
    $glade  = &new GladeXML('in.glade');
    $this->vbox2  = $glade->get_widget( 'vbox2' );

    $this->window   = $glade->get_widget( 'window1' );
    $this->textinfo = $glade->get_widget( 'textinfo' );
    $this->content  = $glade->get_widget( 'content' );
    
    $this->window->set_title(Trans::Translate('SubSql Edition'));
    
    $a = 'In this box below, you can type any query (select...).';
    $b = 'You can also load another queries (.sql) built with Agata.';

    $this->tooltips = &new GtkTooltips();
    
    $this->textinfo->insert(null, null, null, '     ' . Trans::Translate($a));
    $this->textinfo->insert(null, null, null, "\n");
    $this->textinfo->insert(null, null, null, '     ' . Trans::Translate($b));
    
    $this->bimport = $this->Button(&$this, $this->window, $this->vbox2, 'HandlerFile',   Trans::Translate('Import SQL'), 'interface/pixmaps/import.xpm', array(true, 'Import', 'xxx', '*.sql'));
    

    $this->window->show_all();
  }
  
  function Import($fs)
  {
  
    if ($fs)
    {
      $FileName = $fs->get_filename();
      $fs->hide();
      
      if (!file_exists($FileName))
      {
        return false;
      }       
    }
    
    $fd = fopen ("$FileName", "r");

    while (!feof ($fd))
    {
      $buffer = fgets($fd, 500);
      $buffer = ereg_replace("\n", '', $buffer);
      $buffer = ereg_replace(";", '', $buffer);

      $Elementos = explode(":", trim($buffer));
      if ($Elementos[1])
      {
        $sql .= $Elementos[0] . ' ' . $Elementos[1] . ' ';
      }
    }
    fclose($fd);
    $a = gdk::font_load ("-*-courier-medium-r-normal-*-*-148-*-*-*-*-*-*");
    $this->content->insert(null, null, null, "$sql\n\n");

    $this->window->set_focus($this->content);
    $this->bimport->hide();
  }    
  
  function Clear()
  {
    $length = $this->content->get_length();
    $this->content->delete_text(0, $length);
    $this->content->freeze();
    $this->content->thaw(); 
  }  
  
  function CloseFileSelection()
  {
    $this->FileSelection->hide();
  }
  
  function Close()
  {
    $this->window->hide();
  }
}
?>
