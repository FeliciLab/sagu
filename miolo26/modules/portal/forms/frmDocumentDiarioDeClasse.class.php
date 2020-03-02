<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);

class frmDocumentDiarioDeClasse extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Diário de classe', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
        $fields[] = MMessage::getStaticMessage('msgInfo', _M('Se o documento não for gerado, verifique se o seu navegador não está bloqueando popups.'), MMessage::TYPE_WARNING);
        
        $check[] = $this->checkbox('reportModel', _M('Exibir foto dos alunos', $module), NULL, DB_FALSE);
        $check[] = $this->checkbox('completed', _M('Preenchido', $module), NULL, DB_TRUE);
        $check[] = $this->checkbox('cancelled', _M('Exibir alunos cancelados', $module), NULL, DB_TRUE);
        $campos[] = new MBaseGroup('grpCheck', NULL, $check);
        
        $beginDate = new MCalendarMobileField('beginDate', NULL, _M('De'));
        $endDate = new MCalendarMobileField('endDate', NULL, _M('Até'));
        $periodo[] = new MHContainer('dateContainer', array($beginDate, $endDate));
        $campos[] = new MBaseGroup('grpPeriodo', _M('Período'), $periodo);
        
        $fields[] = new MBaseGroup('grpFields', _M('Diário de classe'), $campos, 'vertical');
        
        
        $buttons[] = new MButton('btnImprimir', _M('Gerar relatório'));
        $fields[] = MUtil::centralizedDiv($buttons);
        
        $fields[] = $group = new MTextField('groupId', MIOLO::_REQUEST('groupId'));
        $group->setVisibility(false);
        $fields[] = $professor = new MTextField('professorId', MIOLO::_REQUEST('professorId'));
        $professor->setVisibility(false);
        
        parent::addFields($fields);
    }
    
    public function btnImprimir_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        
        $url = $MIOLO->getActionURL('academic', 'main:document:attendanceReport', NULL, array(
            'groupId' => $args->groupId,
            'professorId' => $args->professorId,
            'emissionDate' => date(SAGU::getParameter('BASIC', 'MASK_DATE_PHP')),
            'beginDate' => $args->beginDate,
            'endDate' => $args->endDate,
            'reportModel' => $args->reportModel == 'on' ? DB_TRUE : DB_FALSE,
            'completed' => $args->completed == 'on' ? DB_TRUE : DB_FALSE,
            'cancelled' => $args->cancelled == 'on' ? DB_TRUE : DB_FALSE,
            'function'=>'print',
            'event'=>'tbBtnPrint_click',
            'reportFormat' => 'pdf'
        ));
        
        $url = str_replace('/miolo26/', '/miolo20/', $url);
        $url = str_replace('&amp;', '&', $url);

        $MIOLO->page->addJsCode("window.location = '{$url}';");
        
        $this->setNullResponseDiv();
    }    
    
}

?>
