<?php
class MTabbedFormPage extends MControl
{
    public $tabbedform; // em qual tabbedform esta pÃ¡gina estÃ¡ inserida
    public $form; // form a ser renderizado na pagina
    public $index; // indice desta pagina dentro do tabbedform (0-based)
    public $title; // titulo da pagina

    public function __construct($form)
    {
        parent::__construct();
        $this->form = $form;
        $this->id = $this->form->id;
        $this->visible = true;
        $this->enabled = true;
        $this->title = $form->title;
    }
}

class MTabbedForm extends MForm
{
    static  $order = 0; // nÃºmero de ordem do form
    public $nOrder; // nÃºmero de ordem do form
    public $pages; // array de TabbedFormPages
    public $activepage; // referencia a TabbedFormPage sendo mostrada
    public $currentpage; // id do form da TabbedFormPage sendo exibida 
    public $pagecount; // quantas TabbedFormPage associadas a este form
    public $pagewidth = 100;
    public $pageheight = 250;
    public $header;
    public $footer;
    public $painterMethod;

    public function __construct($title = '', $action = '')
    {
        parent::__construct($title, $action);
        $this->nOrder = MTabbedForm::$order++;
        $this->fields = array();
        $this->setCurrentPage($this->page->request('frm_currpage_') + 0);
        $this->pagecount = 0;
        $this->pages = array();
        $this->painterMethod = 'javascript'; 
    }

    public function addField()
    {
        $this->manager->assert(false, _M("Tabbed form doesn't yet support AddField Function!!!"));
    }

    public function addPage($form)
    {
        $form->id = $this->id . '_' . $this->pagecount;
        $page = new MTabbedFormPage($form);
        $page->tabbedform = $this;
        $form->tabbedform = $this;
        $page->index = $this->pagecount;
        $this->pages[$page->index] = $page;
        ++$this->pagecount;
        $this->fields = array_merge($this->fields, $form->fields);

        foreach ($form->fields as $field)
        {
            if (is_array($field))
            {
                $namefield = uniqid('frm_array');
            }
            else
            {
                $namefield = $field->name;
            }

            $this->manager->assert(!isset($this->$namefield),
                                   _M("Err: field [$namefield] already defined in form [$this->title]!"));
            $this->fields[$namefield] = $field;
        }
        $this->defaultButton = false;
    }

    public function setPages($forms)
    {
        if (is_array($forms))
        {
            foreach ($forms as $form)
            {
                $this->addPage($form);
            }
        }
    }

    public function goForward()
    {
        return "dijit.byId('{$this->id}').forward();";
    }

    public function goBack()
    {
        return "dijit.byId('{$this->id}').back();";
    }

    public function getPage($index)
    {
        return $this->pages[$index];
    }

    public function getCurrentPage()
    {
        return $this->currentpage;
    }

    public function setCurrentPage($index)
    {
        $_POST['frm_currpage_'] = $index;
        $this->currentpage = $index;
    }

    //
    // returns a plain list of all fields contained in the tabbedform
    //
    public function getFieldList()
    {
        $fields = array();

        foreach ($this->pages as $page)
        {
            $form = $page->form;
            $fields = array_merge($fields, $form->getFieldList());
        }

        return $fields;
    }

    public function eventHandler()
    {
        $page = $this->getPage($this->getCurrentPage());
        $form = $page->form;
        $form->eventHandler();
        parent::eventHandler();
    }

    public function setPainterMethod($method)
    {
        $this->painterMethod = $method; 
    }
    /*
        Renderize
    */
    public function generateHeader()
    {
        return ($this->header != NULL) ? new MDiv('',$this->header,'mTabFormText') : NULL;
    }

    public function generateFooter()
    {
        return ($this->footer != NULL) ? new MDiv('',$this->footer,'mTabFormText') : NULL;
    }


    public function generateJavascript()
    {
//        $MIOLO = MIOLO::getInstance();

        if (!isset($this->buttons))
        {
            if ($this->defaultButton)
            {
                $this->buttons[] = new MButton(FORM_SUBMIT_BTN_NAME, _M('Send'), 'SUBMIT');
            }
        }
        $id = $this->name . '_tab' . $this->nOrder;
        $w = ($this->pagecount * $this->pagewidth) . 'px';
        $h = ($this->pageheight) . 'px';

        $hidden = null;
        $currentPage = $this->getCurrentPage();
        $width = '100%';
        $row = 0;
        $body = array();

        // tabs

        // pages
        $header = $this->generateHeader();
        $hidden = array();
        foreach ($this->pages as $page)
        {
            $pgs = array(); 
            if ( $page->form->hasErrors() )
            {
                $pgs[] = $page->form->generateErrors();
            }
            if ( $this->hasInfos() )
            {
                $pgs[] = $page->form->generateInfos();
            }
            $pgs[] = $page->form->generateLayoutFields($hidden);
            $buttons = $page->form->generateButtons();
            $script[] = $page->form->generateScript();
            if (count($buttons))
            {
               $pgs[] = new MDiv('', $buttons, '');
            }
            $panels[] = $div = new MDiv($page->id, $pgs, 'mTabFormPanel');

            $div->addAttribute("title", $page->title);
            $div->addAttribute("dojoType", "dijit.layout.ContentPane");

        }

        $this->page->addDojoRequire("dijit.layout.ContentPane");
        $this->page->addDojoRequire("dijit.layout.TabContainer");

        $div = new MDiv($this->id, $panels);
        $div->addStyle('width',$w);
        $div->addStyle('height',$h);

        $div->setAttributes("dojoType=\"dijit.layout.TabContainer\"");
        $this->page->onload("dojo.parser.parse(\"dojo.byId('{$this->id}')\");");
        $id = $this->getPage($currentPage)->id;

        $this->page->onload("dijit.byId('{$this->id}').selectChild('{$id}');");
        $this->page->onsubmit("((dojo.byId('frm_currpage_').value = dijit.byId('{$this->id}').getIndexOfChild(dijit.byId('{$this->id}').selectedChildWidget)) > -1)");

        $body[] = $div;

        $buttons = $this->generateButtons();
        if (count($buttons))
        {
           $body[] = new MDiv('', $buttons, '');
        }
        $hidden[] = new MHiddenField('frm_currpage_', $this->getCurrentPage());
        $body[] = $this->generateHiddenFields($hidden); 

        $b = new MDiv($id,$body, 'mTabFormPanelGroup');
        $f = new MDiv('', array($header, $b, $script),'mCollapsible');
        return $f->generate();
    }

    public function generate()
    {
        $method = 'Generate' . $this->painterMethod;
        return $this->$method();
    }
}
?>