<?php

class frmStepByStepFormulario1 extends AStepByStepForm
{
    public function __construct($steps = null)
    {
        $this->target = 'avaFormulario';
        parent::__construct(_M('Formulario', MIOLO::getCurrentModule()), $steps, 1, 2);
    }

    public function createFields()
    {
        parent::createFields();
        $module = MIOLO::getCurrentModule();

        if ( MIOLO::_REQUEST('function')  ==  'edit' )
        {
            $fields[] = new MTextField('idFormulario', '','Código', 10, null, null, true);
            $validators[] = new MIntegerValidator('idFormulario', '', 'required');
        }

        $fields[] = new MLookupContainer('refAvaliacao', null, 'Avaliação', $module, 'Avaliacao');
        $fields[] = new MLookupContainer('refPerfil', null, _M('Perfil', $module), $module, 'Perfil');
        $fields[] = $refPerfil;
        $fields[] = new MTextField('nome', '', _M('Nome', $module), 50);
        $fields[] = new MMultiLineField('descritivo', null, 'Descritivo', 70, 5, 70);
        $fields[] = new MLookupContainer('refServico', null, 'Serviço', $module, 'Servico');
        
        // Retirando campo Serviço para email, ticket #38192
        // $fields[] = new MLookupContainer('refServicoEmail', null, 'Serviço para email', $module, 'Servico');
        // $validators[] = new MRequiredValidator('refServicoEmail');
        
        $this->addFields($fields);
        $validators[] = new MIntegerValidator('refAvaliacao', '', 'required');
        $validators[] = new MIntegerValidator('refPerfil', '', 'required');
        $validators[] = new MRequiredValidator('nome');
        $validators[] = new MRequiredValidator('refServico');
        
        $this->setValidators($validators);
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
