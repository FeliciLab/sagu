<?php

class FrmIntegrationServerSearch extends GForm
{
    
    public $busIntegrationClient;
    
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->busIntegrationClient = $MIOLO->getBusiness($module, 'BusIntegrationClient');
        
        $this->setAllFunctions('IntegrationServer', 'integrationServerId');

        parent::__construct();
    }

     /*
     * Criado por Tcharles Silva
     * Em: 13/11/2013
     */
    public function mainFields()
    {
        parent::mainFields();
        $field[] = new MTextField('nameServer', $this->nameServer->value,  _M('Biblioteca virtual',$this->module), FIELD_DESCRIPTION_SIZE, null, null, FALSE);
        $field[] = new MTextField('hostServer', $this->hostServer->value, _M('Endereço',$this->module), FIELD_DESCRIPTION_SIZE, null, null, FALSE);
        
        $montarStatus = $this->busIntegrationClient->getStatusWorkflow(INTEGRATION);
        
        $field[] = new MSelection('status', NULL, 'Status', $montarStatus, TRUE);
        
        $field[] = new MCalendarField('dataSinc', '', 'Última sincronização');
        
        $this->setFields($field);
    }
}
?>