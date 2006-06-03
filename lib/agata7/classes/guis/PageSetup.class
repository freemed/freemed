<?php
/***********************************************************/
/* Page Setup Dialog
/* by Pablo Dall'Oglio 2001-2006
/***********************************************************/
class PageSetup
{
    /***********************************************************/
    /* Constructor Method
    /***********************************************************/
    function PageSetup($callback)
    {
        $glade = new GladeXML(images . 'pagesetup.glade');

        $this->window = $window = $glade->get_widget('window');
        $window->set_title(_a('Page Setup'));
        $window->connect_object('key_press_event', array(&$this, 'KeyTest'));
        $window->connect_object('delete-event', array(&$this, 'Close'));

        $labelPage          = $glade->get_widget('labelPage');
        $labelPaperFormat   = $glade->get_widget('labelPaperFormat');
        $labelFormat        = $glade->get_widget('labelFormat');
        $labelWidth         = $glade->get_widget('labelWidth');
        $labelHeight        = $glade->get_widget('labelHeight');
        $labelOrientation   = $glade->get_widget('labelOrientation');
        $labelMargins       = $glade->get_widget('labelMargins');
        $labelLeft          = $glade->get_widget('labelLeft');
        $labelRight         = $glade->get_widget('labelRight');
        $labelTop           = $glade->get_widget('labelTop');
        $labelBottom        = $glade->get_widget('labelBottom');
        $labelLineSpace     = $glade->get_widget('labelLineSpace');
        $labelFooterHeight  = $glade->get_widget('labelFooterHeight');
        $labelConfig        = $glade->get_widget('labelConfig');
        $this->comboFormat  = $glade->get_widget('comboFormat');
        $this->rbPortrait   = $glade->get_widget('radiobuttonPortrait');
        $this->rbLandscape  = $glade->get_widget('radiobuttonLandscape');
        $buttonOK           = $glade->get_widget('buttonOK');

        $this->entryFormat  = $glade->get_widget('entryFormat');
        $this->spinWidth    = $glade->get_widget('spinWidth');
        $this->spinHeight   = $glade->get_widget('spinHeight');
        $this->spinLeft     = $glade->get_widget('spinLeft');
        $this->spinRight    = $glade->get_widget('spinRight');
        $this->spinTop      = $glade->get_widget('spinTop');
        $this->spinBottom   = $glade->get_widget('spinBottom');
        $this->spinLineSpace= $glade->get_widget('spinLineSpace');

        $this->spinWidth->set_editable(false);
        $this->spinHeight->set_editable(false);

        $list = $this->comboFormat->list;
        $list->connect_object('button_press_event', array(&$this, 'ChangeFormat'));

        $this->rbPortrait->connect_object('toggled', array(&$this, 'ToggleOrientation'));
        $this->rbLandscape->connect_object('toggled', array(&$this, 'ToggleOrientation'));

        $buttonOK->set_relief(GTK_RELIEF_NONE);
        $labelPage->set_text(_a('Page'));
        $labelPaperFormat->set_text(_a('Page Format'));
        $labelFormat->set_text(_a('Format'));
        $labelWidth->set_text(_a('Width'));
        $labelHeight->set_text(_a('Height'));
        $labelOrientation->set_text(_a('Orientation'));
        $labelMargins->set_text(_a('Margins'));
        $labelLeft->set_text(_a('Left Margin'));
        $labelRight->set_text(_a('Right Margin'));
        $labelTop->set_text(_a('Top Margin'));
        $labelBottom->set_text(_a('Bottom Margin'));
        $labelLineSpace->set_text(_a('Line Space'));
        $labelFooterHeight->set_text(_a('Footer Height'));
        $labelConfig->set_text(_a('Configuration'));
        $labelPortrait = $this->rbPortrait->child;
        $labelLandscape= $this->rbLandscape->child;
        $labelPortrait->set_text(_a('Portrait'));
        $labelLandscape->set_text(_a('Landscape'));
        $this->comboFormat->set_popdown_strings(array('A3', 'A4', 'A5', 'Letter', 'Legal'));
        $buttonOK->connect_object('clicked', array(&$this, 'OK'), $callback);
    }
    
    function Show()
    {
        $this->window->show_all();
    }

    /***********************************************************/
    /* Changes Page Format
    /***********************************************************/
    function ChangeFormat()
    {
        $this->Pages['A3']        = array(841, 1190);
        $this->Pages['A4']        = array(595, 841);
        $this->Pages['A5']        = array(419, 595);
        $this->Pages['Letter']    = array(612, 790);
        $this->Pages['Legal']     = array(612, 1009);
    
        $entry = $this->comboFormat->entry;
        $format = $entry->get_text();

        if ($this->rbPortrait->get_active())
        {
            $this->spinWidth->set_text($this->Pages[$format][0]);
            $this->spinHeight->set_text($this->Pages[$format][1]);
        }
        else
        {
            $this->spinWidth->set_text($this->Pages[$format][1]);
            $this->spinHeight->set_text($this->Pages[$format][0]);
        }
    }

    /***********************************************************/
    /* Changes the Orientation
    /***********************************************************/
    function ToggleOrientation()
    {
        $this->ChangeFormat();
    }

    /***********************************************************/
    /* Calls the Callback function
    /***********************************************************/
    function OK($callback)
    {
        $this->window->hide();
        $return['Format']       = $this->entryFormat->get_text();
        $return['Orientation']  = $this->rbPortrait->get_active() ? 'portrait' : 'landscape';
        $return['LeftMargin']   = $this->spinLeft->get_text();
        $return['RightMargin']  = $this->spinRight->get_text();
        $return['TopMargin']    = $this->spinTop->get_text();
        $return['BottomMargin'] = $this->spinBottom->get_text();
        $return['LineSpace']    = $this->spinLineSpace->get_text();
        
        call_user_func($callback, $return);
    }
    
    function SetValues($values)
    {
        $this->entryFormat->set_text($values['Format'] ? $values['Format'] : 'A4');
        $this->spinLeft->set_text($values['LeftMargin'] ? $values['LeftMargin'] : 0);
        $this->spinRight->set_text($values['RightMargin'] ? $values['RightMargin'] : 0);
        $this->spinTop->set_text($values['TopMargin'] ? $values['TopMargin'] : 0);
        $this->spinBottom->set_text($values['BottomMargin'] ? $values['BottomMargin'] : 0);
        $this->spinLineSpace->set_text($values['LineSpace'] ? $values['LineSpace'] : 14);
        $this->rbPortrait->set_active($values['Orientation'] == 'portrait');
        $this->rbLandscape->set_active($values['Orientation'] == 'landscape');
        $this->ChangeFormat();
    }

    /***********************************************************/
    /* Test the key pressed
    /***********************************************************/
    function KeyTest($p1)
    {
        if ($p1->keyval == K_ESC)
        {
            $this->window->Hide();
        }
    }
    
    function Close()
    {
        $this->window->hide();
        return true;
    }
}
?>