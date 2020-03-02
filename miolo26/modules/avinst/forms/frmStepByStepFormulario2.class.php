<?php
$MIOLO->uses( "types/avaFormulario.class.php", $module );        

class frmStepByStepFormulario2 extends AStepByStepForm
{
    public function __construct($steps = null)
    {
        $this->target = 'avaFormulario';
        parent::__construct(_M('Formulario', MIOLO::getCurrentModule()), $steps, 2, 3);
    }

    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        // Cria a subDetail para controle dos blocos
        $stdFields['fieldid'] = new MTextField('nome', NULL, _M('Nome'), 40);
        
        $MIOLO->uses( "types/avaGranularidade.class.php", $module );
        $typeGranularidade = new avaGranularidade();
        $idBlocoField = new MTextField('idBloco', null);
        $idBlocoField->addAttribute('style', 'display:none');
        $stdFields[] = $idBlocoField;
        $stdFields[] = new MSelection('refGranularidade', NULL, 'Granularidade', $typeGranularidade->search());
        $stdFields[] = new MIntegerField('ordem', null, 'Ordem');
        
        $stdValidators[] = array();
        $stdValidators[] = new MRequiredValidator('nome', null, 'Ordem');
        $stdValidators[] = new MRequiredValidator('refGranularidade', null, 'Ordem');
        $stdValidators[] = new MRequiredValidator('ordem', null, 'Ordem');
        
        // Colunas
        $stdFieldsColumns[] = new MGridColumn('idBloco', 'left', false, 0, false, 'idBloco', false);
        $stdFieldsColumns[] = new MGridColumn('Nome', 'left', false,'80%', true, 'nome', false);
        $stdFieldsColumns[] = new MGridColumn('Granularidade', 'left', false, '20%', true, 'refGranularidade', false);
        $stdFieldsColumns[] = new MGridColumn('Ordem', 'left', false, '20%', true, 'ordem', false);
        
        $fields[] = $sub = new MSubDetail('sdtBlocos', _M('Blocos'), $stdFieldsColumns, $stdFields);
        $sub->setValidators($stdValidators);
        
        if( $this->isFirstAccess(2) )
        {
            MSubDetail::clearData('sdtBlocos');
            
            if( MIOLO::_REQUEST('function') == 'edit' )
            {
                $loadData = $this->getEditData();
                MSubDetail::setData($loadData->__get('blocos'),'sdtBlocos');
            }            
        }
        
        $this->addFields($fields);
    }
    
	/**
     * Função que checa dependências para habilitar/desabilitar o botão excluir.
     */
    public function checkDeleteButtton()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaFormLog.class.php', 'avinst');
        $data = new stdClass();
        $data->refFormulario = MUtil::getAjaxActionArgs()->item;
        $avaFormLog = new avaFormLog($data);
        $tentativas = $avaFormLog->contaTentativasPorFormulario();
        if ($tentativas>0)
        {
            $this->toolbar->hideButtons(MToolBar::BUTTON_DELETE);
        }        
    }
}
?>
