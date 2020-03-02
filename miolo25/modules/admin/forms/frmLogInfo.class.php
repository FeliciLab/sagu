<?php

class frmLogInfo extends MForm
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        parent::__construct( _M('Log Info', $module) );
        $this->eventHandler();
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $MIOLO->uses('classes/logReader.class', $module);
        $log = new logReader(MIOLO::_REQUEST('mod'));
        $data = $log->getLog(MIOLO::_REQUEST('pointer'));
        $cont1[]  = new MTextLabel('tlIP', null, _M('IP', $module).':');
        $cont1[]  = new MTextField('IP', $data[1], null);
        $cont1[]  = new MTextLabel('tlModule', null, _M('Module', $module).':');
        $cont1[]  = new MTextField('logModule', $data[4]);
        $cont1[]  = new MTextLabel('tlUser', null, _M('User', $module).':');
        $cont1[]  = new MTextField('user', $data[5]);
        $fields[] = new MHContainer('cont1', $cont1);
        $cont2[]  = new MTextLabel('tlDate', null, _M('Date', $module).':');
        $cont2[]  = new MTextField('date', $data[2]);
        $cont2[]  = new MTextLabel('tlTime', null, _M('Time', $module).':');
        $cont2[]  = new MTextField('time', $data[3]);
        $fields[] = new MHContainer('cont2', $cont2);
        $cont6[]  = new MMultiLineField('SQL', $data[6], null, 40, 5, 60); 
        $fields[] = new MHContainer('cont6', $cont6);
        $this->defaultButton = false;
        
        $this->setFields($fields);
        $this->tlIP     ->width = '35px';
        $this->tlDate   ->width = '35px';
        $this->tlTime   ->width = '40px';
        $this->tlModule ->width = '40px';
        $this->tlUser   ->width = '70px';
        $this->date     ->width = '90px';
        $this->IP       ->width = '90px';
        $this->date     ->setReadOnly(true);
        $this->IP       ->setReadOnly(true);
        $this->logModule->setReadOnly(true);
        $this->time     ->setReadOnly(true);
        $this->user     ->setReadOnly(true);
        $this->SQL      ->setReadOnly(true);
//        $this->tlSQL   ->width = '100px';
//        $this->SQL     ->width = '80%';
        $this->setLabelWidth('100');
        
    }

}
?>
