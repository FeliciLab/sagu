<?php
class MLookup
{
    public $manager;
    public $listingTitle = 'Resultado da pesquisa';
    public $dbconf;
    public $sql;
    public $labels;
    public $formName;
    public $module;
    public $item;
    public $lheight;
    public $lwidth;
    public $event;
    public $filterValue = '';
    public $filterFields;
    public $type;
    public $form;
    public $pageLength = 20;
    public $keyColumn;
    public $title;
    public $grid;
    public $related;
    public $baseModule;
    public $autocomplete;
    public $autocompleteresponse;
    public $gridClass = 'MLookupGridSQL';

    public function __construct($baseModule = 'admin')
    {
        $this->manager = MIOLO::getInstance();
        $this->baseModule   = $baseModule;
        $this->formName     = $_GET['name'];
        $this->module       = $_GET['lmodule'];
        $this->item         = $_GET['item'];
        $this->event        = $_GET['event'];
        $this->lheight      = $_GET['lheight'];
        $this->lwidth       = $_GET['lwidth'];
        $this->related      = $_GET['related'];
        $this->filterValue  = $_GET['filter'];
        $this->type         = $_GET['type'];
        $this->title        = $_GET['title'];
        $this->autocomplete = $_GET['autocomplete'];
        $params = array(
            'name' => urlencode($this->formName),
            'lmodule' => urlencode($this->module),
            'event' => urlencode($this->event),
            'type' => urlencode($this->type),
            'related' => urlencode($this->related)
        );
        $url = $this->manager->getActionURL($this->baseModule, 'lookup', urlencode($this->item), $params , NULL, false);
        $this->href = $url;
    }

    public function getFilterValue($name = '')
    {
        $value = ($name != '') ? $this->manager->_request($name) : $this->filterValue;
        $value = str_replace("'", '', $value);
        $value = str_replace("\"", '', $value);
        $value = str_replace("#", '', $value);
        return $value ? $value : MIOLO::_REQUEST('filter');
    }

    public function getHRef()
    {
        return $this->href;
    }

    public function getQuery($dbconf, $sql)
    {
        $db = $this->manager->getDatabase($dbconf);
        return $db->getQuery($sql);
    }

    public function addFilterField(&$field)
    {
        $this->filterFields[] = &$field;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    function setCursorGrid($cursor, $columns, $title = 'Pesquisa', $pageLength = 15, $indexColumn = 0)
    {
        $objects = $cursor->getObjects();
        $this->grid = new MLookupObjectGrid($objects, $columns, $this->href, $pageLength, $indexColumn);

        for ($i = 0; $i < count($this->filterFields); $i++)
            $this->grid->AddFilterControl($i, $this->filterFields[$i]);

//        $this->grid->setId($this->formName);
        $this->grid->setFilter(true);
        $this->grid->setLinkType('linkbutton');
        $this->grid->setTitle($title);
        $data = $this->grid->GetData();

        for ($i = 0; $i < count($cursor->getQuery()->result[0]); $i++)
        {
            $args .= ($i ? '|' : '') . "#$i#";
        }

        $this->grid->setActionDefault("{$this->formName}.deliver('$this->formName', {$indexColumn}, '$args');");
        $this->grid->setButtonSelectClass('grid_select');
    }

    function setQueryGrid($query, $columns, $title = 'Pesquisa', $pageLength = 15, $indexColumn = 0)
    {
        $this->grid = new MLookupQueryGrid($query, $columns, $this->href, $pageLength, $indexColumn);

        for ($i = 0; $i < count($this->filterFields); $i++)
            $this->grid->AddFilterControl($i, $this->filterFields[$i]);

//        $this->grid->setId($this->formName);
        $this->grid->setFilter(true);
        $this->grid->setLinkType('linkbutton');
        $this->grid->setTitle($title);
        $data = $this->grid->GetData();

        for ($i = 0; $i < count($query->result[0]); $i++)
        {
            $args .= ($i ? '|' : '') . "#$i#";
        }

        $this->grid->setActionDefault("{$this->formName}.deliver('$this->formName', {$indexColumn}, '$args');");
        $this->grid->setButtonSelectClass('grid_select');
    }

    function setGrid($dbconf, $sql, $columns, $title = 'Pesquisa', $pageLength = 15, $indexColumn = 0)
    {
        $query = &$this->GetQuery($dbconf, $sql);
        $this->setQueryGrid($query, $columns, $title, $pageLength, $indexColumn);
    }

    function setAutoComplete($array)
    {
        for ($i = 0; $i < count($array); $i++)
        {
            $args .= ($i ? '|' : '') . $array[$i];
        }
        $this->autocompleteresponse =  $args;
    }
    
    public function execute()
    {
        $MIOLO = MIOLO::getInstance();

        $fileName = $MIOLO->getConf('namespace.business').'/lookup.class' . $MIOLO->php;
        $file = $MIOLO->getModulePath($this->module, $path);
        if ( file_exists( $file ) )
        {
           $ok = $MIOLO->uses($fileName,$this->module);
        }
        $MIOLO->assert($ok,_M('File modules/@1/db/lookup.class.php not found!<br>'.
                      'This file must implement Business@1Lookup class '.
                      'which must have a method called Lookup@2.',
                      'miolo',$this->module, $this->item));

        $businessClass = "Business{$this->module}Lookup";
        $lookupMethod = $this->autocomplete ? "AutoComplete{$this->item}" : "Lookup{$this->item}";
        $object = new $businessClass();
        $object->$lookupMethod($this);
    }

    function setContent()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->getPage()->addScript('m_lookup.js');
        $theme = $MIOLO->getTheme();
        if ($this->autocomplete)
        {
           $theme->setAjaxContent(new MRawText($this->autocompleteresponse)); 
        }
        else
        {
           $MIOLO->getPage()->setAction($this->href);
           $MIOLO->getPage()->setTitle($this->title);
           $theme->setLayout('lookup');
           $theme->setContent($this->grid);
        }
    }

}
?>