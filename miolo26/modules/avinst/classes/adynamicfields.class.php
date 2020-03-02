<?php 

$MIOLO->page->addScript('atooltip.js','avinst');

class ADynamicFields
{
    const TIPO_QUESTAO_ABERTA = '1';
    const TIPO_QUESTAO_MULTIPLA_ESCOLHA = '2';
    const TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA = '3';
    const TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO = '4';
    
    /**
     * Função que retorna os tipos de questão da avaliação institucional
     *
     * @return array
     */
    public static function getQuestionTypes()
    {
        $tipos[self::TIPO_QUESTAO_ABERTA] ='Questão aberta';
        $tipos[self::TIPO_QUESTAO_MULTIPLA_ESCOLHA] = 'Múltipla escolha por botões';
        $tipos[self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA] = 'Múltipla escolha Multi-resposta';
        $tipos[self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO] = 'Múltipla escolha por seleção';
        return $tipos;
    }
    
    /**
     * Retorna os campos para a criação do formulário correto para as questões
     *
     * @return array
     */
    public static function returnQuestionFields($tipoQuestao)
    {
        $module = MIOLO::getCurrentModule();
        
        switch ($tipoQuestao)
        {
            case self::TIPO_QUESTAO_ABERTA:
            {
                // Campo largura
                // Label
                $label1 = new MLabel('Largura:');
                $label1->setClass('mCaption');
                $sFields1[] = new MSpan('labelSize', $label1, 'label');
                // Field
                $tField1 = new MTextField('size', null, null, 10);
                $tField1->setClass('mTextField');
                $sFields1[] = new MSpan('fieldSize', $tField1, 'field');
                $fields[] = new MDiv(null, $sFields1, 'mFormRow');
                
                // Campo altura
                // Label
                $label2 = new MLabel('Altura:');
                $label2->setClass('mCaption');
                $sFields2[] = new MSpan('labelHeight', $label2, 'label');

                // Field
                $tField2 = new MTextField('height', null, null, 10);
                $tField2->setClass('mTextField');
                $sFields2[] = new MSpan('fieldHeight', $tField2, 'field');
                $fields[] = new MDiv(null, $sFields2, 'mFormRow');  

                // Campo limite de caracteres
                // Label
                $label3 = new MLabel('Limite de caracteres:');
                $label3->setClass('mCaption');
                $sFields3[] = new MSpan('labelcharLimit', $label3, 'label');                
                // Field
                $tField3 = new MTextField('charLimit', null,  null, 10);
                $tField3->setClass('mTextField');
                $sFields3[] = new MSpan('fieldcharLimit', $tField3, 'field');
                $fields[] = new MDiv(null, $sFields3, 'mFormRow');
                
                break;
            }
            
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO:                
            {
                // Campos da subdetail
                $stdFields[] = new MTextField('codigo', NULL, 'Código', 10);
                $stdFields[] = new MTextField('descricaoOpcao', NULL, 'Descrição', 50);
                $stdFields[] = new MSelection('opcaoDescritiva', null, 'Opção descritiva?', AVinst::listYesNo(AVinst::RETURN_TYPE_SINGLE_ARRAY));
                $stdFields1['importarOpcoesLkp'] = new MLookupContainer('importarOpcoesLkp', null, _M('Questão', $module), $module, 'Questoes');
                $stdFields1[] = new MButton('btnImportar', _M('Importar opções'));
                $stdFields[] = new MHContainer(null, $stdFields1);
                
                // Colunas da grid da Subdetail
                $stdFieldsColumns[] = new MGridColumn('Código', 'left', false, 0, true, 'codigo');
                $stdFieldsColumns[] = new MGridColumn('Descrição', 'left', false, '40%', true, 'descricaoOpcao');
                $stdFieldsColumns[] = new MGridColumn('Opção descritiva', 'left', false, '20%', true, 'opcaoDescritiva');
        
                                                      
                $sFields[] = new MSpan(null,'&nbsp','label');
                $sFields[] = $sdt = new MSubDetail('opcoes', _M('Opções'), $stdFieldsColumns, $stdFields);
                $sdt->setAttribute('style','width: 70%');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA:
            {
                // Campos da subdetail
                $stdFields[] = new MTextField('codigo', NULL, 'Código', 10);
                $stdFields[] = new MTextField('descricaoOpcao', NULL, 'Descrição', 50);
                $stdFields[] = new MSelection('opcaoDescritiva', null, 'Opção descritiva?', AVinst::listYesNo(AVinst::RETURN_TYPE_SINGLE_ARRAY));
                $stdFields1['importarOpcoesLkp'] = new MLookupContainer('importarOpcoesLkp', null, _M('Questão', $module), $module, 'Questoes');
                $stdFields1[] = new MButton('btnImportar', _M('Importar opções'));
                $stdFields[] = new MHContainer(null, $stdFields1);
                
                // Colunas da grid da Subdetail
                $stdFieldsColumns[] = new MGridColumn('Código', 'left', false, 0, true, 'codigo');
                $stdFieldsColumns[] = new MGridColumn('Descrição', 'left', false, '60%', true, 'descricaoOpcao');
                $stdFieldsColumns[] = new MGridColumn('Opção descritiva', 'left', false, '20%', true, 'opcaoDescritiva');
                                                      
                $sFields[] = new MSpan(null,'&nbsp','label');
                $sFields[] = $sdt = new MSubDetail('opcoes', _M('Opções'), $stdFieldsColumns, $stdFields);
                $sdt->setAttribute('style','width: 70%');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                unset($sFields);
                // Campo limite de seleções no campo
                // Label
                $label = new MLabel('Número máximo de itens selecionados:');
                $label->setClass('mCaption');
                $sFields[] = new MSpan('labelmaxSelected', $label, 'label');
                // Field
                $tField = new MTextField('maxSelected', null,  null, 10);
                $tField->setClass('mTextField');
                $sFields[] = new MSpan('fieldmaxSelected', $tField, 'field');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA:
            {
                // Campos da subdetail
                $stdFields[] = new MTextField('codigo', NULL, 'Código', 10);
                $stdFields[] = new MTextField('descricaoOpcao', NULL, 'Descrição', 5);
                $stdFields[] = new MTextField('legenda', NULL, 'Legenda', 50);
                $stdFields1['importarOpcoesLkp'] = new MLookupContainer('importarOpcoesLkp', null, _M('Questão', $module), $module, 'Questoes');
                $stdFields1[] = new MButton('btnImportar', _M('Importar opções'));
                $stdFields[] = new MHContainer(null, $stdFields1);        
                
                // Colunas da grid da Subdetail
                $stdFieldsColumns[] = new MGridColumn('Código', 'left', false, 0, true, 'codigo');
                $stdFieldsColumns[] = new MGridColumn('Descrição', 'left', false, '60%', true, 'descricaoOpcao');
                $stdFieldsColumns[] = new MGridColumn('Legenda', 'left', false, '20%', true, 'legenda');
                                                      
                $sFields[] = new MSpan(null,'&nbsp','label');
                $sFields[] = $sdt = new MSubDetail('opcoes', _M('Opções'), $stdFieldsColumns, $stdFields);
                $sdt->setAttribute('style','width: 70%');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                break;
            }
        }
        return $fields;
    }
    
    /*
     * Função para retornar o campo com os parses completos
     * 
     */
    public function returnQuestionField($questao, $nomeElemento, $countGraosBloco, $graoBloco, $cabecalho, $login)
    {
        $MIOLO = MIOLO::getInstance();
        $nomeElemento = stripslashes(str_replace(array(' ', '\''), array('_', ''), $nomeElemento));
        
        // Obtém as informações para geração da informação do tooltip
        if (is_array($cabecalho['cabecalhos']))
        {
            $cabecalhoInfo = array_shift($cabecalho['cabecalhos']);
            $tooltipInfo = $graoBloco->{$cabecalhoInfo->atributo};
            if (strlen($tooltipInfo) == 0)
            {
                unset($tooltipInfo);
            }
        }

        switch ($questao->__get('tipo'))
        {
            case self::TIPO_QUESTAO_ABERTA:
            {
                // Obtém valor
                $questionData = new stdClass();
                $questionData->questao = $nomeElemento;
                
                if ($login->loginType == ADynamicForm::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
                {
                    $avaRespostas = new avaRespostas($questionData);
                    $valor = $avaRespostas->obtemResposta(avaRespostas::TIPO_RESPOSTA_VALOR, $login->refPessoa);
                }
                
                // Cria o campo
                $options = unserialize($questao->__get('opcoes'));
                if ($countGraosBloco <=1)
                {
                    $field = new MMultiLineField($nomeElemento, $valor, null, $options->size, $options->height, $options->size);
                    if (strlen($options->charLimit)>0)
                    {
                        $field->addAttribute('maxlength', $options->charLimit);
                    }
                    $field->setClass('avinstTextArea');
                    $field->addAttribute('onKeyUp', 'if( this.value.length > 0 ) AvinstValidator.removeErrorFromField(this.id);');
                }
                else
                {
                    $field = new AMultiLineField($nomeElemento, $options, $valor);
                }
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO:
            {
                // Obtém valor
                $questionData = new stdClass();
                $questionData->questao = $nomeElemento;
                $avaRespostas = new avaRespostas($questionData);
                if ($login->loginType == ADynamicForm::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
                {
                    $valor = $avaRespostas->obtemResposta(avaRespostas::TIPO_RESPOSTA_VALOR, $login->refPessoa);
                }
               
		//
		// TODO: Transformar o campo em objeto, para padronizar como são construídos conforme todos os outros campos 
                //
		// Cria o campo
                $options = unserialize($questao->__get('opcoes'));
                $options = $options->opcoes;
                if (is_array($options))
                {
		    $descritiva = array();
                    foreach ($options as $option)
                    {
                        $optionsParsed[$option->codigo] = $option->descricaoOpcao;
			if ($option->opcaoDescritiva == DB_TRUE)
			{
	                    $descritiva[] = $option->codigo;
			    if ($valor == $option->codigo)
			    {
				if ($showField != true)
				{
			            // Obtém valor
		                    $questionData = new stdClass();
		                    $questionData->questao = $nomeElemento.'_descriptive';
		                    $avaRespostas = new avaRespostas($questionData);
		                    if ($login->loginType == ADynamicForm::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
		                    {
		                        $valorDescritivo = $avaRespostas->obtemResposta(avaRespostas::TIPO_RESPOSTA_VALOR, $login->refPessoa);
		                    }
				}
				$showField = true;
			    }
			}
                    }
                }
		if (is_array($descritiva))
		{
		    $javaParse = array();
		    foreach ($descritiva as $descr)
		    {
			$javaParse[] = '(this.options[this.selectedIndex].value == \''.$descr.'\')';
		    }
		    if (count($javaParse)>0)
		    {
			$nomeElementoDescritivo = $nomeElemento.'_descriptive';
			$javaParsed = ' if ('.implode(' || ', $javaParse).') { parentNode.lastChild.style.display =\'inherit\' } else { parentNode.lastChild.style.display =\'none\'; parentNode.lastChild.value = \'\';  }';
		    }
		}
		
                $fieldMS = new MSelection($nomeElemento, $valor, '', $optionsParsed);
                $fieldMS->setClass('avinstDynamicField');
                $fieldMS->addAttribute('onChange', 'if( this.value.length > 0 ) { AvinstValidator.removeErrorFromField(this.id); } '.$javaParsed);
 		$fieldT = new MTextField($nomeElemento.'_descriptive', $valorDescritivo, null, 14);
		if ($showField != true)
	 	{
		    $fieldT->addStyle('display', 'none'); 
		}
		$field[] = $fieldMS;
		$field[] = $fieldT;
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA:
            {
                // Obtém valor
                $questionData = new stdClass();
                $questionData->questao = $nomeElemento;
                $avaRespostas = new avaRespostas($questionData);
                if ($login->loginType == ADynamicForm::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
                {
                    $valor = $avaRespostas->obtemResposta(avaRespostas::TIPO_RESPOSTA_VALOR, $login->refPessoa);
                }

                // Cria o campo
                $options = unserialize($questao->__get('opcoes'));
                if (is_array($options))
                {
                    foreach ($options as $pos => $option)
                    {
                        $optionsParsed[$pos]->id = $option->codigo;
                        $optionsParsed[$pos]->label = $option->descricaoOpcao;
                        $optionsParsed[$pos]->color = constant("FORM_COLOR_OPTION_{$optionsParsed[$pos]->label}");
                        if (strlen($tooltipInfo)>0)
                        {
                            $tooltip[] = new MSpan('tooltipHeader'.$id, $tooltipInfo, 'avinstTooltipGranularity');
                            $tooltip[] = '<br />';
                        }
                        $tooltip[] = new MSpan('tooltipLegend'.$id, $option->legenda, 'avinstTooltipOption');
                        $optionsParsed[$pos]->tooltipMessage = new MSpan('tooltipMessage'.$id, $tooltip);
                        
                        if ( !strlen($tooltipInfo) > 0 && !strlen($option->legenda) > 0 )
                        {
                            unset($optionsParsed[$pos]->tooltipMessage);
                        }
                        unset($tooltip);
                    }
                }

                $field = new AButtonSelectGroup($nomeElemento, $optionsParsed, $valor);
                $field->setClass('avinstDynamicField1');
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA:
            {
                // Cria o campo
                $options = unserialize($questao->__get('opcoes'));
                
                if (is_array($options->opcoes))
                {
                    foreach ($options->opcoes as $pos => $option)
                    {
                        // Obtém valor                        
                        $questionData = new stdClass();
                        $questionData->questao = $nomeElemento.'_'.$option->codigo;
                        $avaRespostas = new avaRespostas($questionData);
                        if ($login->loginType == ADynamicForm::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
                        {
                            $valor = $avaRespostas->obtemResposta(avaRespostas::TIPO_RESPOSTA_VALOR, $login->refPessoa);
                        }
                        unset($questionData);
                        
                        $checked = ($option->codigo == $valor);
                        // Cria o campo descritivo
                        $opcaoDescritiva = ($option->opcaoDescritiva == DB_TRUE) ? true : false;
                        if ($opcaoDescritiva == true)
                        {
                            $questionData = new stdClass();
                            $questionData->questao = $nomeElemento.$option->codigo.'_descriptive';
                            $avaRespostas = new AvaRespostas($questionData);
                            if ($login->loginType == ADynamicForm::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
                            {
                                $valorOpcaoDescritiva = $avaRespostas->obtemResposta(avaRespostas::TIPO_RESPOSTA_VALOR, $login->refPessoa);
                            }
                        }
                        $optionsParsed[$pos] = new AOption($option->codigo, $option->codigo, $option->descricaoOpcao, $checked, $opcaoDescritiva, $valorOpcaoDescritiva);
                    }
                }
                $fieldM = new ACheckBoxGroup($nomeElemento.'[]', '', $optionsParsed);
                $fieldM->setDisposition('vertical');
                $fieldM->setClass('avinstCheckBoxGroup');
                $field[] = new MSpan($nomeElemento, $fieldM);
                if (strlen($tooltipInfo)>0)
                {
                    $tooltip = new MSpan('tooltipHeader'.$id, $tooltipInfo, 'avinstTooltipGranularity');
                    $MIOLO->page->onload("atooltip.setTooltip('$nomeElemento','$tooltip')");                
                }
                break;
            }
        }
        return $field;
    }
    
    public static function returnFieldValidator($questionName, $questao, $obrigatorio = false)
    {
        $opcoes = unserialize($questao->__get('opcoes'));
        if( $obrigatorio == DB_TRUE )
        {
            $obrigatorio = 'required';
        }
        else
        {
            $obrigatorio = 'optional';
        }
        switch ($questao->__get('tipo'))
        {
            case self::TIPO_QUESTAO_ABERTA:
            {
                if (strlen($opcoes->charLimit)>0)
                {
                    $validator[] = new AvinstLengthValidator($questionName, 0, $opcoes->charLimit, $obrigatorio);
                }
                break;
            }
            // TODO: Desenvolver validador de número máximo selecionado
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA:
            {
                $validator[] = new AVinstCheckBoxValidator($questionName, 0, $opcoes->maxSelected);
                
                if (is_array($opcoes->opcoes))
                {
                    $questionData = MIOLO::_REQUEST($questionName);
                    foreach ($opcoes->opcoes as $pos => $option)
                    {
                        if (is_array($questionData))
                        {
                            $validatorOption = in_array($option->codigo, $questionData);
                            if (($option->opcaoDescritiva == DB_TRUE) && ($validatorOption == true))
                            {
                                $validator[] = new AvinstRequiredDescriptiveValidator(ACheckBoxGroup::getDescriptiveFieldName($questionName, $option->codigo));
                            }
                        }
                    }
                }
                break;
            }
	    // TODO: Fazer com que a classe a ser criada para o MSelection retorne as informações padronizadas
	    case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO:
	    {
                if (is_array($opcoes->opcoes))
                {
                    $questionData = MIOLO::_REQUEST($questionName);
                    foreach ($opcoes->opcoes as $option)
                    {
			if ($option->opcaoDescritiva == DB_TRUE)
			{
			    if (($questionData == $option->codigo))
			    {
				$validator[] = new AvinstRequiredDescriptiveValidator($questionName.'_descriptive');
			    }
			}
                    }
                }
	    }
        }
        return isset($validator) ? $validator : false;
    }
    
    /**
     * Função para executar um parse na estrutura de dados vinda do formulário, retornando-a de uma forma
     * padrão para a interpretação e criação dos campos
     */
    public static function parseFields($data)
    {
        switch ($data->tipo)
        {
            case self::TIPO_QUESTAO_ABERTA:
            {
                $options = new stdClass();
                $options->size = $data->size;
                $options->height = $data->height;
                $options->charLimit = $data->charLimit;
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO:
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA:
            {
                $sdtData = MSubDetail::getData('opcoes');

                if (is_array($sdtData))
                {
                    foreach ( $sdtData as $sdtRow )
                    {
                        if( $sdtRow->dataStatus != MSubDetail::STATUS_REMOVE )
                        {
                            $optionObject = new stdClass();
                            $optionObject->codigo = $sdtRow->codigo;
                            $optionObject->descricaoOpcao = $sdtRow->descricaoOpcao;
                            $optionObject->opcaoDescritiva = $sdtRow->opcaoDescritiva;
                            $options->opcoes[] = $optionObject;
                        }
                    }
                }
                $options->maxSelected = $data->maxSelected;
                break;
            }
            case self::TIPO_QUESTAO_MULTIPLA_ESCOLHA:
            {
                $sdtData = MSubDetail::getData('opcoes');
                if (is_array($sdtData))
                {
                    foreach ( $sdtData as $sdtRow )
                    {
                        if( $sdtRow->dataStatus != MSubDetail::STATUS_REMOVE )
                        {
                            $optionObject = new stdClass();
                            $optionObject->codigo = $sdtRow->codigo;
                            $optionObject->descricaoOpcao = $sdtRow->descricaoOpcao;
                            $optionObject->legenda = $sdtRow->legenda;
                            $options[] = $optionObject;
                        }
                    }
                }
                break;
            }
        }
        return $options;
    }
}
?>
