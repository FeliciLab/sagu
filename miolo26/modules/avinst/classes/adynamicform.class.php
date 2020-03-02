<?php

/**
 *  Formulário herdado pelos formulários de pesquisa na avaliação institucional
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/16
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */
/*
 * Class AForm
 *
 */
class ADynamicForm extends MForm
{
    // Variável com o objeto da barra de ferramentas
    // Essa variável deve ser usada para desabilitar botões e para definir os links dos botões
    //public $toolbarField;
    // verificacao para ativar o eventHandler
    public static $doEventHandler;
    const ADYNAMICFORM_INDEX_TYPE_STRING = 1;
    const ADYNAMICFORM_INDEX_TYPE_ARRAY = 2;
    
    
    const ADYNAMICFORM_LOGIN_TYPE_NORMAL = 1;
    const ADYNAMICFORM_LOGIN_TYPE_SIMULATION = 2;
    
    public $login;
    
    public function __construct($title)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/agranularity.class.php', 'avinst');
        $MIOLO->uses('classes/avinstvalidator.class.php', 'avinst');
        $MIOLO->uses('classes/validators/avinstrequiredvalidator.class.php', 'avinst');
        $MIOLO->uses('classes/validators/avinstlengthvalidator.class.php', 'avinst');
        $MIOLO->uses('classes/validators/avinstcheckboxvalidator.class.php', 'avinst');
        $MIOLO->uses('classes/validators/avinstrequireddescriptivevalidator.class.php', 'avinst');
        $MIOLO->uses('types/avaServico.class.php', 'avinst');
        $MIOLO->uses('types/avaFormLog.class.php', 'avinst');
        $MIOLO->page->addScript('avinstvalidator.js', 'avinst');
        $refPessoa = MUtil::getAjaxActionArgs()->refPessoa;
        $this->registraUsuarioDoForm($refPessoa);
        parent::__construct($title);
        if ( !self::$doEventHandler )
        {
            $this->eventHandler();
            self::$doEventHandler = true;
        }
    }

    //
    // Função para criar os campos do dynamicForm
    //
    public function createFields()
    {
        $fields[] = MMessage::getMessageContainer();
        $fields[] = MPopup::getPopupContainer();
        $this->addFields($fields);
        $this->setShowPostButton(FALSE);
    }

    //
    // Cria o nome do campo a ser utilizado pelo form, permitindo a sua reutilização em outros locais
    // como exemplo, na validação
    //
    public function geraEstruturaCampo($blocoQuestoes, $opcoes, $graoBloco)
    {
        
        if (is_array($opcoes['indices']))
        {
            foreach ($opcoes['indices'] as $indice)
            {
                $atributo = $indice->atributo;
                $item = $graoBloco->$atributo;
                $indexIndex[] = $item;
            }
        }
        $questionString = 'question_' . $blocoQuestoes->idBlocoQuestoes;
        
        if (is_array($indexIndex))
        {
            $questionString = str_replace('.', '', $questionString . implode('_', $indexIndex));
        }
        return $questionString;
    }
    
    
    
    //
    // Gera estrutura da questão, conforme estrutura do bloco
    //
    public function geraEstruturaQuestao($granularidade, $blocoQuestao, $ordemBloco, $ordemQuestao, $graosBloco, $login)
    {
        $MIOLO = MIOLO::getInstance();
        $data['validators'] = array();
        $tipoGranularidade = $granularidade->tipo;
        // Dados do enunciado
        $opcoes = AGranularity::parseFormOptions($granularidade->opcoes);
        $enunciado[] = $ordemBloco . '.' . $ordemQuestao . '. ' . $blocoQuestao->questao->descricao;
        $obrigatorio = $blocoQuestao->obrigatorio;
        if ($obrigatorio == DB_TRUE)
        {
            $enunciadoO = new MLabel('*', 'red');
            $enunciadoO->setBold(true);
            $enunciado[] = $enunciadoO;
            $data['validators'] = array();
        }
        $indexData[] = new MSpan('enunciado' . rand(), $enunciado);
        $indexCols[] = $indexData;
        //
        if ( $tipoGranularidade == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS )
        {
            // Percorre os grãos
            if (is_array($graosBloco))
            {
                $countGraosBloco = count($graosBloco);
                foreach ( $graosBloco as $graoBloco )
                {
                    $questionName = $this->geraEstruturaCampo($blocoQuestao, $opcoes, $graoBloco);
                    $data['field'] = ADynamicFields::returnQuestionField($blocoQuestao->questao, $questionName, $countGraosBloco, $graoBloco, $opcoes, $login);
                    // Validadores exigidos pelo formulário
                    if ($obrigatorio == DB_TRUE)
                    {
                        $data['validators'][] = new AvinstRequiredValidator($questionName);
                    }
                    // Validadores exigidos pelo campo
                    $validador = ADynamicFields::returnFieldValidator($questionName, $blocoQuestao->questao, $obrigatorio);
                    if (is_array($validador))
                    {
                        foreach ($validador as $val)
                        {
                            $data['validators'][] = $val;
                        }
                    }
                    unset($validador);
                    $cols[] = $data;
                    unset($data);
                }
            }
            else
            {
                return null;
            }
        }
        elseif ($tipoGranularidade == AGranularity::GRANULARITY_RETURN_BOOLEAN)
        {
            $questionName = $this->geraEstruturaCampo($blocoQuestao, null, null);
            $data['field'] = ADynamicFields::returnQuestionField($blocoQuestao->questao, $questionName, null, $graoBloco, $opcoes, $login);
            
            // Validadores exigidos pelo formulário
            if ($obrigatorio == DB_TRUE)
            {
                $data['validators'][] = new AvinstRequiredValidator($questionName);
            }
            // Validadores exigidos pelo campo
            $validador = ADynamicFields::returnFieldValidator($questionName, $blocoQuestao->questao, $obrigatorio);
            if (is_array($validador))
            {
                foreach ($validador as $val)
                {
                    $data['validators'][] = $val;
                }
            }
            unset($validador);
            $cols[] = $data;
            unset($data);
        }
        $line = array_merge($indexCols, $cols);
        return $line;
    }

    //
    // Estrutura para a criação do cabeçalho do bloco
    //
    public function geraCabecalhoBloco($granularidade, $graosBloco)
    {
        $tipoGranularidade = $granularidade->tipo;
        if ( $tipoGranularidade == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS )
        {
            if ( is_array($graosBloco) )
            {
                $opcoes = AGranularity::parseFormOptions($granularidade->opcoes);

                if (is_array($opcoes['cabecalhos']))
                {
                    foreach ($opcoes['cabecalhos'] as $cabecalho)
                    {
                        $indexData[] = $cabecalho->descritivo;
                    }
                    $indexCols[] = $indexData;
                    unset($cabecalho);
                }

                // Se existir uma esrutura de elementos a ser mostrado, gera o cabeçalho
                if ( $opcoes != null )
                {
                    // Bloco para geração dos cabeçalhos
                    foreach ( $graosBloco as $graoBloco )
                    {
                        foreach ($opcoes['cabecalhos'] as $cabecalho)
                        {
                            $atributo = $cabecalho->atributo;
                            $data[] = $graoBloco->$atributo;
                        }
                        $cols[] = $data;
                        unset($data);
                    }
                }

            }
            $line = array_merge($indexCols, $cols);
            return $line;
        }
        return null;
    }
    
    
    /*
     *  Gera a resposta da questão a partir da estrutura do form
     * 
     */
    public function geraResposta($blocoQuestao, $granularidade, $graoBloco, $data)
    {
        
        $MIOLO = MIOLO::getInstance();
        $tipoGranularidade = $granularidade->tipo;
        // Dados do enunciado
        $opcoes = AGranularity::parseFormOptions($granularidade->opcoes);
        $questionName = str_replace(' ', '_', $this->geraEstruturaCampo($blocoQuestao, $opcoes, $graoBloco));
        
        $atributos = array();
        if ( $tipoGranularidade == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS )
        {
            // Obtém os atributos
            if (is_array($opcoes['indices']))
            {
                foreach ($opcoes['indices'] as $indice)
                {
                    $atributo = new stdClass();
                    $atributo->chave = $indice->atributo;
                    $atributo->valor = $graoBloco->{$indice->atributo};
                    $atributos[] = $atributo;
                    unset($atributo);
                }
            }
            if (is_array($opcoes['atributos']))
            {
                foreach ($opcoes['atributos'] as $atributo_)
                {
                    $atributo = new stdClass();
                    $atributo->chave = $atributo_->atributo;
                    $atributo->valor = $graoBloco->{$atributo_->atributo};
                    $atributos[] = $atributo;
                    unset($atributo);
                }
            }
        }
        if (is_array($data->$questionName))
        {
            $tipoQuestao = $blocoQuestao->questao->tipo;
            if ($tipoQuestao == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA)
            {
                $opcoes = unserialize($blocoQuestao->questao->opcoes);
            }
            if (is_array($opcoes->opcoes))
            {
                foreach ($opcoes->opcoes as $opcao)
                {
                    $resposta = new stdClass();
                    $resposta->refBlocoQuestoes = $blocoQuestao->idBlocoQuestoes;
                    $resposta->refAvaliador = $MIOLO->getLogin()->id;
                    if (in_array($opcao->codigo, $data->$questionName))
                    {
                        $resposta->valor = $opcao->codigo;
                    }
                    else
                    {
                        $resposta->valor = null;
                    }
                    $resposta->questao = $questionName.'_'.$opcao->codigo;
                    $resposta->atributos = $atributos;
                    $resp[] = $resposta;
                    unset($reposta);
                    
                    if ($opcao->opcaoDescritiva == DB_TRUE)
                    {
			if ($tipoQuestao == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO || $tipoQuestao == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA )
			{
			    $resposta = new stdClass();
			    $resposta->refBlocoQuestoes = $blocoQuestao->idBlocoQuestoes;
			    $resposta->refAvaliador = $MIOLO->getLogin()->id;
                            $descriptiveFieldName = ACheckBoxGroup::getDescriptiveFieldName($questionName, $opcao->codigo);
			    $resposta->questao = $descriptiveFieldName;
                            $resposta->valor = $data->{$descriptiveFieldName};
			    $resposta->atributos = $atributos;
			    $resp[] = $resposta;
			    unset($resposta);
			}	
                    }
                }
            }
            foreach ($data->$questionName as $respostaData)
            {
                $resposta = new stdClass();
                $resposta->refBlocoQuestoes = $blocoQuestao->idBlocoQuestoes;
                $resposta->refAvaliador = $MIOLO->getLogin()->id;
                $resposta->valor = $respostaData;
                $resposta->questao = $questionName.'_'.$respostaData;
                $resposta->atributos = $atributos;
                $resp[] = $resposta;
                unset($reposta);
            }
        }
        else
        {
            $tipoQuestao = $blocoQuestao->questao->tipo;
            $resposta = new stdClass();
            $resposta->refBlocoQuestoes = $blocoQuestao->idBlocoQuestoes;
            $resposta->refAvaliador = $MIOLO->getLogin()->id;
            $resposta->valor = $data->$questionName;
            $resposta->questao = $questionName;
            $resposta->atributos = $atributos;
            $resp[] = $resposta;
            if ($tipoQuestao == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO)
            {
                $opcoes = unserialize($blocoQuestao->questao->opcoes);
            }
            if (is_array($opcoes->opcoes))
            {
                foreach ($opcoes->opcoes as $opcao)
                {
                    if ($opcao->opcaoDescritiva == DB_TRUE)
                    {
                        $resposta = new stdClass();
                        $resposta->refBlocoQuestoes = $blocoQuestao->idBlocoQuestoes;
                        $resposta->refAvaliador = $MIOLO->getLogin()->id;
                        $descriptiveFieldName = $questionName.'_descriptive';
                        $resposta->valor = $data->{$descriptiveFieldName};
                        $resposta->questao = $descriptiveFieldName;
                        $reposta->atributos = $atributos;
                        $resp[] = $resposta;
                        unset($resposta);
                    }
                }
            }
        }
        return $resp;
    }
    
    /**
     * Validates the form input.
     * Check if form data is valid according to validator components added to 
     * form. (Recreated there to modify the default mode to show the nonvalidated fields)
     *
     * @return boolean Return whether data is valid.
     */
    public function validate()
    {
        $MIOLO = MIOLO::getInstance();
        $this->errors = array();
        $data = MUtil::getAjaxActionArgs();
        
        if (is_array($this->validations))
        {
            $js = '';
            foreach ( $this->validations as $validator )
            {
                $validator->field = stripslashes(str_replace(array(' ', '\''), array('_', ''), $validator->field));
                $field = $validator->field;
                
                if ( !$validator->validate($data->{$validator->field}) )
                {
                    
                    $this->errors[$validator->field] = $validator->getError();
                    $error = $this->errors[$validator->field];
                    // Add the error message via JS call
                    $error = str_replace("\n", '\n', $error); //troca linha nova do php para javascript
                    $error = str_replace("'", "\'", $error); // retira ' para evitar erros de sintaxe js
                    if ($validator instanceof AvinstRequiredDescriptiveValidator)
                    {
                        $js .= "AvinstValidator.addErrorToDescriptiveField('$error', '$field');";
                    }
                    else
                    {
                        $js .= "AvinstValidator.addErrorToField('$error', '$field');";
                    }
                }
                else
                {
                    if ($validator instanceof AvinstRequiredDescriptiveValidator)
                    {
                        $js .= "AvinstValidator.removeErrorFromDescriptiveField('$field');";
                    }
                    else
                    {
                        $js .= "AvinstValidator.removeErrorFromField('$field');";
                    }
                }
            }
        }
        $MIOLO->page->onload($js);
        return count($this->errors) == 0;
    }
    
    /**
     * Grava uma resposta registrada no formulário
     */
    public function gravaResposta($respostaData)
    {
        $respostaNova = new avaRespostas($respostaData);
        $respostaAntigaData = new stdClass();
        $respostaAntigaData->questao = $respostaData->questao;
        $respostaAntiga = new avaRespostas($respostaAntigaData);
        $valor = $respostaAntiga->obtemResposta(avaRespostas::TIPO_RESPOSTA_OBJETO, $respostaData->refAvaliador);
        unset($respostaAntigaData);

        if (!is_null($valor))
        {
            $respostaNova->idRespostas = $valor->idRespostas;
            $return = $respostaNova->update();
        }
        else
        {
            $return = $respostaNova->insert();
        }
        return $return;
    }

    /**
     * Apaga as respostas que não está na lista de respostas registradas
     *  
     */
    public function apagaRespostasNaoRegistradas($idFormulario, $respostasNaoRegistradas)
    {
        $respostas = new avaRespostas();
        $respostas->limpaRegistrosRemanescentes($idFormulario, $respostasNaoRegistradas);
    }

    //
    //
    //
    public function registraUsuarioDoForm($refPessoa = null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/amanagelogin.class.php', 'avinst');

        $rootPerms = $MIOLO->getPerms()->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT);
        $adminPerms = $MIOLO->getPerms()->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN);
        //
        // Se existir código do ref_pessoa E houver (Permissão de ROOT "OU" Permissão de Admin)
        // então continua...
        //
        if ((strlen($refPessoa) > 0) && ( ($rootPerms === true) || ($adminPerms === true) ))
        {
            $loginType = self::ADYNAMICFORM_LOGIN_TYPE_SIMULATION;
            // Prepara o ambiente para a chamada do webservice
            $ws = $MIOLO->getWebServices($module, 'wsCoreAvinst');

            if (defined('WS_GET_PERFIS_PESSOA_ID_SERVICE'))
            {
                $filter = new stdClass();
                $filter->idServico = WS_GET_PERFIS_PESSOA_ID_SERVICE;
                $avaServico = new avaServico($filter, true);
                $perfis = $avaServico->chamaServico(array('$login'=>$refPessoa), true);
            
                // Compara os perfis do usuário com os perfis da avaliação                
                $MIOLO->uses( "types/avaPerfil.class.php", $MIOLO->getConf('login.sourceModule') );
                $objPerfil = new avaPerfil();
                $perfisDb = $objPerfil->search();
                $perfisDb = AVinst::getArrayOfTypes($perfisDb, 'avaPerfil', 'tipo');
            }
            else
            {
                new MMessageError('Não foi encontrada a configuração de verificação de perfis da pessoa, por favor, contate o administrador do sistema');
            }

            $perfisPessoa = AManageLogin::parsePerfis($perfis);
        }
        else // Se não, pega o ambiente da pessoa logada
        {
            $refPessoa = $MIOLO->getLogin()->id;
            $perfisPessoa = AManageLogin::getLoginProfiles($refPessoa);
       //   $perfisPessoa = $MIOLO->getLogin()->perfis;
            $loginType = self::ADYNAMICFORM_LOGIN_TYPE_NORMAL;
        }
        $this->login = new stdClass();
        $this->login->refPessoa = $refPessoa;
        $this->login->perfis = $perfisPessoa;
        $this->login->loginType = $loginType;
    }
}
?>
