<?php

class Business#ModuleLookup
{
    /**
     * @var MIOLO Main class instance.
     */
    private $manager;
    
    /**
     * @var string Current module name.
     */
    private $module;
    
    /**
     * Método construtor da classe.
     */
    public function __construct() 
    {
        $this->manager = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
    }

    public function LookupBrowser(&$lookup)
    {
        $filters = new stdClass();
        $filters->identifier = $lookup->getFilterValue('identifier');
        $filters->description = $lookup->getFilterValue('description');

        $lookup->addFilterField(new MIntegerField('identifier', NULL, _M('Identificador', $this->module), 10));
        $lookup->addFilterField(new MTextField('description', NULL,_M('Descrição', $this->module), 30));

        $columns[] = new MDataGridColumn('identifier',_M('Identificador', $this->module),'right', true,'5%',true);
        $columns[] = new MDataGridColumn('description',_M('Descrição'),'left', true,'95%',true);

        $browser = $this->manager->getBusiness($this->module, 'browser');
        $query = $browser->getSearch($filters);

        $lookup->setQueryGrid($query, $columns, _M('Navegador', $this->module), 15, 0);
    }

    public function AutoCompleteBrowser(&$lookup)
    {
        $filters = new stdClass();
        $filters->identifier = $lookup->getFilterValue();
        
        if ( strlen($filters->identifier) )
        {
            $browser = $this->manager->getBusiness($this->module, 'browser');
            $result = $browser->search($filters);

            $lookup->setAutoComplete($result[0]);
            return $result[0];
        }
    }
}

?>