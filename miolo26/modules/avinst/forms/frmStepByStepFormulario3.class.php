<?php
$MIOLO->uses( "types/avaFormulario.class.php", $module );        
$MIOLO->uses( "types/avaCategoriaAvaliacao.class.php", $module );        
$MIOLO->uses( "types/avaCategoria.class.php", $module );        

class frmStepByStepFormulario3 extends AStepByStepForm
{
    public function __construct($steps = null)
    {
        $this->target = 'avaFormulario';
        parent::__construct(_M('Formulario', MIOLO::getCurrentModule()), $steps, 3);
    }

    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $function = MIOLO::_REQUEST('function');
        $blocos = MSubDetail::getData('sdtBlocos');
        
        if( $function == 'edit' )
        {
            $blocosCarregados = $this->getEditData();
            $blocosCarregados = $blocosCarregados->__get('blocos');    
        }        
          
        $stepData = $this->getStepData();
        
        $categorias = avaCategoriaAvaliacao::listarCategoriasDaAvaliacao($stepData->refAvaliacao);
        $possuiCategorias = count($categorias) > 0;
        
        foreach ( $blocos as $bloco )
        {
            if( $bloco->dataStatus != MSubDetail::STATUS_REMOVE )
            {
                // Cria a subDetail para controle das questões do bloco
                $idBlocoQuestoesField = new MTextField('idBlocoQuestoes', null);
                $idBlocoQuestoesField->addAttribute('style', 'display:none');
                $stdFields[] = $idBlocoQuestoesField;
                $stdFields[] = $idBlocoQuestoes;
                $stdFields[] = new MLookupContainer('refQuestao', null, 'Questão', $module, 'Questoes');
                $stdFields[] = new MIntegerField('ordem', null, 'Ordem');
                $stdFields[] = new MSelection('obrigatorio', null, 'Obrigatória?', AVinst::listYesNo(AVinst::RETURN_TYPE_SINGLE_ARRAY));
                $stdFields[] = new MSelection('ativo', null, 'Ativa?', AVinst::listYesNo(AVinst::RETURN_TYPE_SINGLE_ARRAY));
                
                $stdValidators[] = array();
                $stdValidators[] = new MRequiredValidator('refQuestao', null, 'Ordem');
                $stdValidators[] = new MRequiredValidator('ordem', null, 'Ordem');
                $stdValidators[] = new MRequiredValidator('obrigatorio', null, 'Ordem');
                $stdValidators[] = new MRequiredValidator('ativo', null, 'Ordem');
                
                if ( $possuiCategorias )
                {
                    $stdFields[] = $categoria = new MSelection('categoriaId', null, 'Categorias', $categorias);
                    $categoria->addAttribute('onchange', MUtil::getAjaxAction('obtemCategoria', $args));
                    
                    $stdFields[] = $categoriaDescricao = new MTextField('categoriaDescricao');
                    $categoriaDescricao->addBoxStyle('display', 'none');
                }
                
                // Colunas
                $stdFieldsColumns[] = new MGridColumn('Id bloco questoes', 'left', false, 0, false, 'idBlocoQuestoes', false);
                $stdFieldsColumns[] = new MGridColumn('Id', 'left', false, 0, false, 'refQuestao', false);
                $stdFieldsColumns[] = new MGridColumn('Questão', 'left', false, '23%', true, 'refQuestao_lookupDescription', false);
                $stdFieldsColumns[] = new MGridColumn('Ordem', 'right', false, '23%', true, 'ordem', false);
                $stdFieldsColumns[] = new MGridColumn('Obrigatoria?', 'center', false, '23%', true, 'obrigatorio', false);
                $stdFieldsColumns[] = new MGridColumn('Ativa?', 'center', false, '23%', true, 'ativo', false); 
                
                if ( $possuiCategorias )
                {
                    $stdFieldsColumns[] = new MGridColumn('Código categoria', 'center', false, '23%', false, 'categoriaId', false);
                    $stdFieldsColumns[] = new MGridColumn('Categoria', 'center', false, '23%', true, 'categoriaDescricao', false); 
                }
                                
                $sdtName = 'sdtQuestoes' . $bloco->arrayItem;
                $fields[] = $sub = new MSubDetail($sdtName, _M("Questões do bloco ({$bloco->nome})"), $stdFieldsColumns, $stdFields);
                $sub->setValidators($stdValidators);    
                
                if( $this->isFirstAccess(3) )
                {
                    MSubDetail::clearData($sdtName);

                    if( $function == 'edit' && is_object($blocosCarregados[$bloco->idBloco]) )
                    {
                        $dataSdt = $blocosCarregados[$bloco->idBloco]->__get('questoes');
                        foreach ( $dataSdt as $key => $data )
                        {
                            if ( strlen($data->categoriaId) > 0 )
                            {
                                $categorias = avaCategoriaAvaliacao::listarCategoriasDaAvaliacao($stepData->refAvaliacao, ADatabase::RETURN_OBJECT);
                                $dataSdt[$key]->categoriaDescricao = $categorias[$data->categoriaId];
                            }
                            
                            $dataSdt[$key]->refQuestao_lookupDescription = $dataSdt[$key]->questao->__get('descricao');
                        }
                        MSubDetail::setData($dataSdt,$sdtName);
                    }
                }
                unset($stdFields);
                unset($stdFieldsColumns);
                unset($dataSdt);
            }
        }
        $fields[] = new MDiv('divCategoria');
        $this->addFields($fields);
    }
    
    /**
     * Obtém as informações referentes a uma categoria
     * @param args $args
     */
    public function obtemCategoria($args)
    {
        $MIOLO = MIOLO::getInstance();
        
        foreach ( $args->mSubdetail as $blocos )
        {
            $categoria = $blocos . '_categoriaId';
            $categoriaDescricao = $blocos . '_categoriaDescricao';
            
            if ( strlen($args->$categoria) > 0 )
            {
                $data = new stdClass();
                $data->categoriaId = $args->$categoria;
                $categoria = new avaCategoria($data, true);
                
                $descricao = $categoria->descricao . '/' . $categoria->tipo;
                
                $jscode = " document.getElementById('{$categoriaDescricao}').value = '{$descricao}';";
            }
            else
            {
                $jscode = " document.getElementById('{$categoriaDescricao}').value = '';";
            }
            
            $MIOLO->page->onLoad($jscode);
        }
        
        $this->setResponse($fields, 'divCategoria');
    }
    
    //
    // Ação feita quando clicado no botão "Finalizar"
    //
    public function finalizeButton_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $MIOLO->uses( "types/{$this->target}.class.php", $module );
        $data = $this->getStepData();
        //$validate = unserialize($MIOLO->getSession()->getValue('sessionValidators'));
        /*if( ! $this->validate() )
        {
            new MMessageWarning('Verifique os dados informados');
            return;
        }*/
        
        $blocos = MSubDetail::getData('sdtBlocos');
        if (is_array($blocos))
        {
            foreach ( $blocos as $bloco )
            {
                $questoesBloco = MSubDetail::getData('sdtQuestoes' . $bloco->arrayItem);
                
                if (is_array($questoesBloco))
                {
                    foreach ( $questoesBloco as $questao )
                    {
                        $bloco->questoes[] = $questao;
                    }
                }
                $data->blocos[] = $bloco;            
            }
        }
        $type = new $this->target($data);
        $pk = $type->getPrimaryKeyAttribute();

        if( MIOLO::_REQUEST('function') == 'insert' )
        {
            try
            {
                if ( $type->insert() )
                {
                    $linkOpts[$pk] = $type->__get($pk);
                    $linkOpts['function'] = 'search';
                    $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                    new MMessageSuccess(_M(MSG_RECORD_INSERTED,avinst,$link->generate()));
                }
            }
            catch ( Exception $e )
            {
                new MMessageError(MSG_RECORD_UPDATE_ERROR . ' ' . $e);
            }
        }
        elseif ( MIOLO::_REQUEST('function') == 'edit' )
        {
            try
            {
                if ( $type->update() )
                {
                    $linkOpts[$pk] = $data->$pk;
                    $linkOpts['function'] = 'search';
                    $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                    new MMessageSuccess(_M(MSG_RECORD_UPDATED,avinst,$link->generate()));
                }
            }
            catch ( Exception $e )
            {
                new MMessageError(MSG_RECORD_UPDATE_ERROR . ' ' . $e);
            }
        }
        
        parent::finalizeStepByStep();
    }
    
    public function finalizeStepByStep($buttons = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getActionUrl($module, 'main:avaFormulario');
        $buttons[] = new MButton('closeButton', _M('Close'), $url);
        parent::finalizeStepByStep($buttons);
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
