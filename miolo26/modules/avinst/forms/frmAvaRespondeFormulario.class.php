<?php

/**
 * Formulário para selecionar um formulário da avaliação
 *
 * @author William Prigol Lopes [william@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Creation date 18/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmAvaRespondeFormulario extends ADynamicForm
{
    const tipoUsuarioNormal = 1;
    const tipoUsuarioSimulado = 2;
    
    private $formulario;
    private $blocos;
    private $graosBloco;
    private $registradas;
    private $tipoAvaliacao;
    public $validationCheck;

    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses("types/avaFormulario.class.php", 'avinst');
        $MIOLO->uses('types/avaRespostas.class.php', 'avinst');
        $MIOLO->uses('classes/adynamicformmessage.class.php', 'avinst');
        $idFormulario = MIOLO::_REQUEST('idFormulario');

        $data = new stdClass();
        $data->idFormulario = $idFormulario;
        $this->formulario = new avaFormulario($data, true);
        parent::__construct(null);
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->blocos = $this->formulario->blocos;
        $quantidadeQuestoes = 0;
        
        if ($this->login->loginType == self::ADYNAMICFORM_LOGIN_TYPE_SIMULATION)
        {
            $link = new MLink('mainLink', 'clique aqui', $MIOLO->getActionURL('avinst', 'main'), 'clique aqui');
            $message = 'AVISO: Você está em modo de simulação de ambiente do usuário '.$this->login->refPessoa.', por meio de permissões administrativas. Abaixo, a tela tem a mesma aparência ao qual é apresentada ao usuário em uma situação normal de acesso, com exceção de que o administrador não pode visualizar as respostas e, também, enviar o formulário. Caso queira retornar à tela de acompanhamento administrativo, '.$link->generate().'.';
            $fields[] = MMessage::getStaticMessage(NULL, $message, MMessage::TYPE_INFORMATION);
        }
        
        if ( $this->validaRespondente() )
        {

            if ( MUtil::isFirstAccessToForm() && ($this->login->loginType == self::tipoUsuarioNormal) )
            {
                $hash = md5(rand());
                $MIOLO->getSession()->setValue('idAccessForm', $hash);
                $this->registraLog(avaFormLog::FORM_LOG_BEGIN);
            }
            //
            // Obtém os blocos do formulário
            //
            $fields[] = new MDiv('formTitle', $this->formulario->nome, 'formTitle');
            $ordemBloco = 0;
            if ( is_array($this->blocos) )
            {
                foreach ( $this->blocos as $bloco )
                {
                    $ordemBloco++;
                    unset($cabecalho);
                    unset($questoes);
                    unset($granularidades);
                    $line = 0;
                    //
                    // Percorre as questões caso existam questões
                    //
                    $ordemQuestao = 0;

                    if ( is_array($bloco->questoes) )
                    {
                        $fields[] = new MDiv('bloco' . rand(), $ordemBloco . '. ' . $bloco->nome, 'avinstBlocoTitle');
                        $granularidade = $bloco->getGranularidade();

                        if ( $granularidade->tipo == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS )
                        {
                            if ( is_null($this->graosBloco[$granularidade->idGranularidade]) )
                            {
                                //
                                // Prepara parâmetros para o webservice
                                //
                                $parametros['$login'] = $this->login->refPessoa;
                                $parametros['$perfis'] = $this->login->perfis;
                                $this->graosBloco[$granularidade->idGranularidade] = $granularidade->obtemGraos($parametros);
                            }
                        }
                        else
                        {
                            $this->graosBloco[$granularidade->idGranularidade] = null;
                        }

                        $table = new MSimpleTable('bloco_' . rand(), null, 3, 4);
                        $table->setClass('avinstFormTableMain');
                        //
                        // Geração do cabeçalho
                        //
                        // Obtém as informações do cabecalho
                        $cabecalho = $this->geraCabecalhoBloco($granularidade, $this->graosBloco[$granularidade->idGranularidade]);
                        // Se retornou um array, é porque existe cabecalho
                        if ( is_array($cabecalho) )
                        {
                            // Para cada coluna do cabecalho, percorre suas informações
                            foreach ( $cabecalho as $pos => $cabeca )
                            {
                                // Verifica se existe linhas de informação do cabecalho
                                if ( is_array($cabeca) )
                                {
                                    // Faz a contagem para controlar as linhas para colocar as questões
                                    $countCabeca = $countCabeca == null ? count($cabeca) : $countCabeca;
                                    foreach ( $cabeca as $line => $itemCabeca )
                                    {
                                        // Para cada item, coloca na tabela
                                        $table->setCell($line, $pos, $itemCabeca);

                                        // Configura o tema
                                        if ( $pos == 0 )
                                        {
                                            // Na coluna 1 são as legendas, coloca diferente
                                            $table->setCellClass($line, $pos, 'avinstHeaderCellLegend avinstHeaderCellLegend' . $line);
                                        }
                                        else
                                        {
                                            // Nas outras colunas são as informações da granularidade, coloca padrão
                                            $table->setCellClass($line, $pos, 'avinstHeaderCell avinstHeaderCell' . $line);
                                        }
                                    }
                                }
                            }
                        }
                        unset($pos);

                        
                        $contLine = 0;
                        //
                        // Geração das questões
                        //
                        foreach ( $bloco->questoes as $lineQ => $blocoQuestao )
                        {
                            if ( $blocoQuestao->__get('ativo') == DB_TRUE )
                            {
                                $ordemQuestao++;
                                $zebra = $ordemQuestao % 2;
                                $questoes = $this->geraEstruturaQuestao($granularidade, $blocoQuestao, $ordemBloco, $ordemQuestao, $this->graosBloco[$granularidade->idGranularidade], $this->login);
                                if ( is_array($questoes) )
                                {
                                    $countGranularidade = count($questoes);
                                    $wGranularidade = ceil(100 / ($countGranularidade + 1));
                                    foreach ( $questoes as $pos => $questao )
                                    {
                                        // Soma a quantidade de questões.
                                         $quantidadeQuestoes++;
                                         
                                        // A posição da linha é a linha da questão mais a 
                                        // linha do cabecalho mais 1
                                        if ( $line == 0 )
                                        {
                                            if (is_array($cabecalho))
                                            {
                                                if (count($cabecalho[0])>1)
                                                {
                                                    $line = -1;
                                                }
                                            }
                                            else
                                            {
                                                $line = -1;
                                            }
                                        }
                                        $lineQuestao = $contLine + $line + 1;


                                        if ( $pos == 0 )
                                        {
                                            $table->setCell($lineQuestao, $pos, $questao);
                                            // Na coluna 1 são as legendas, coloca diferente
                                            $table->setCellClass($lineQuestao, $pos, 'avinstQuestionCellLegend');
                                        }
                                        else
                                        {
                                            $table->setCell($lineQuestao, $pos, $questao['field']);
                                            // Nas outras colunas são as informações da granularidade, coloca padrão
                                            $table->setCellClass($lineQuestao, $pos, 'avinstQuestionCell');
                                            $table->setCellAttribute($lineQuestao, $pos, 'style="width:' . $wGranularidade . '%"');
                                        }

                                        if ( is_array($questao['validators']) )
                                        {
                                            $validators = is_array($validators) ? array_merge($validators, $questao['validators']) : $questao['validators'];
                                        }
                                    }
                                    // Incrementa contador de linhas.
                                    $contLine++;
                                    $table->setRowClass($lineQuestao, 'avinstQuestionRow' . $zebra);
                                    unset($pos);
                                    unset($questao);
                                }
                            }
                        }
                        unset($blocoQuestao);
                        unset($lineQ);
                        unset($line);
                        unset($lineQuestao);
                        $fields[] = $table;
                        unset($table);
                    }
                }
                $fields[] = new MSeparator();

                
                if ( $this->login->loginType == self::ADYNAMICFORM_LOGIN_TYPE_SIMULATION)
                {
                    $url = $MIOLO->getActionURL($MIOLO->getCurrentModule(), 'main:avaAnalisaFormulario');
                    $button = new MButton('backButton', _M('Sair da simulação'), $url);
                }
                else
                {
                    if ( $quantidadeQuestoes > 0 )
                    {
                        $button = new MButton('submitEvaluation', 'ENVIAR AVALIAÇÃO');
                    }
                    else
                    {
                        $url = $MIOLO->getActionURL($MIOLO->getCurrentModule(), 'main');
                        $button = new MButton('backButton', _M('VOLTAR'), $url);
                    }
                }
                $button->insertClass('avinstSubmitEvaluation');
                $divButtons = new MDiv('sendDiv', $button);
                $divButtons->addAttribute('align', 'center');
                $fields[] = $divButtons;

                $this->addFields($fields);
                if ( is_array($validators) )
                {
                    $this->setValidators($validators);
                }
                $this->setJsValidationEnabled(false);
            }
            else
            {
                new MMessageError('Este formulário está com o cadastro incompleto (não há blocos cadastrados), por favor, contate o administrador do sistema');
            }
        }
        else
        {
            if ($this->validationCheck == 1)
            {
                $fields[] = new adynamicformmessage('alreadyfilledmsg', 'Você já preencheu o formulário selecionado, obrigado.');
                $this->addFields($fields);
            }
            elseif ($this->validationCheck == 2)
            {
                $fields[] = new adynamicformmessage('alreadyfilledmsg', 'Você não tem os vínculos necessários para preencher o formulário selecionado. Obrigado.');
                $this->addFields($fields);
            }
            else
            {
                $fields[] = new adynamicformmessage('alreadyfilledmsg', 'Você não tem os vínculos necessários para preencher o formulário selecionado! Obrigado.');
                $this->addFields($fields);
            }
        }
    }

    //
    // Botão utilizado para enviar dados do formulário
    //
    public function submitEvaluation_click()
    {
        if ( $this->validate() )
        {
            if ($this->login->loginType == self::ADYNAMICFORM_LOGIN_TYPE_NORMAL)
            {
                try
                {
                    $data = MUtil::getAjaxActionArgs();
                    if ( is_array($this->blocos) )
                    {
                        ADatabase::execute('begin');
                        // Percorre os blocos
                        foreach ( $this->blocos as $bloco )
                        {
                            if ( is_array($bloco->questoes) )
                            {
                                $granularidade = $bloco->getGranularidade();
                                foreach ( $bloco->questoes as $pos => $blocoQuestao )
                                {
                                    if ( $granularidade->tipo == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS )
                                    {
                                        if ( $blocoQuestao->ativo == DB_TRUE )
                                        {
                                            $graosBloco = $this->graosBloco[$granularidade->idGranularidade];
                                            if ( is_array($graosBloco) )
                                            {
                                                foreach ( $graosBloco as $graoBloco )
                                                {
                                                    $respostaData = $this->geraResposta($blocoQuestao, $granularidade, $graoBloco, $data);
                                                    if ( is_array($respostaData) )
                                                    {
                                                        foreach ( $respostaData as $resposta )
                                                        {
                                                            $this->gravaResposta($resposta);
                                                            $this->registradas[] = $resposta->questao;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    elseif ( $granularidade->tipo == AGranularity::GRANULARITY_RETURN_BOOLEAN )
                                    {
                                        $respostaData = $this->geraResposta($blocoQuestao, $granularidade, $graoBloco, $data);
                                        if ( is_array($respostaData) )
                                        {
                                            foreach ( $respostaData as $resposta )
                                            {
                                                $this->gravaResposta($resposta);
                                                $this->registradas[] = $resposta->questao;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $this->apagaRespostasNaoRegistradas($this->formulario->idFormulario, $this->registradas);
                        $this->registraLog(avaFormLog::FORM_LOG_SUCCESS);
                        $this->exibeMensagemSucesso();
                        ADatabase::execute('COMMIT');
                    }
                    else
                    {
                        new MMessageError('Não foram encontradas informações para efetuar a gravação, por favor, contate o administrador do sistema');
                    }
                }
                catch ( Exception $e )
                {
                    ADatabase::execute('ROLLBACK');
                    new MMessageError(MSG_RECORD_INSERT_ERROR . ' ' . $e);
                }
            }
            else
            {
                new MMessageWarning('Não foi possível enviar a avaliação por meio da simulação, por favor, contate o administrador do sistema');
            }
        }
        else
        {
            $this->registraLog(avaFormLog::FORM_LOG_NO_VALIDATED);
            new MMessageWarning(MESSAGE_FORM_VALIDATION_DENIED);
        }
    }

    //
    // Efetua o registro do log do formulário
    //
    public function registraLog($tipoLog)
    {
        $MIOLO = MIOLO::getInstance();
        $logData = new stdClass();
        $logData->refAvaliador = $this->login->refPessoa;
        $logData->refFormulario = $this->formulario->idFormulario;
        $logData->tipoAcao = $tipoLog;
        $logData->sessao = $MIOLO->getSession()->id;
        $logData->tentativa = $MIOLO->getSession()->getValue('idAccessForm');
        $avaFormLog = new avaFormLog($logData);
        $avaFormLog->insert();
    }

    //
    // Função para efetuar a validação do respondente
    //
    public function validaRespondente()
    {
	// Validação 1 -> Verifica se o usuário já respondeu
        $MIOLO = MIOLO::getInstance();
        $filtroAvaliacao = new stdClass();
        $filtroAvaliacao->idAvaliacao = $this->formulario->refAvaliacao;
        $avaAvaliacao = new avaAvaliacao($filtroAvaliacao, true);

        $this->validationCheck = 1;
        // Se for do tipo pontual, verifica se o usuário já respondeu a avaliação
        if ( $avaAvaliacao->tipoProcesso == avaAvaliacao::AVALIACAO_TIPO_PROCESSO_PONTUAL )
        {
            $filtroFormLog = new stdClass();
            $filtroFormLog->refFormulario = $this->formulario->idFormulario;
            $filtroFormLog->refAvaliador = $this->login->refPessoa;
            $filtroFormLog->tipoAcao = avaFormLog::FORM_LOG_SUCCESS;
            $avaFormLog = new avaFormLog($filtroFormLog);
            $formLogResult = $avaFormLog->search(ADatabase::RETURN_TYPE);

            //
            // Se existir uma tentativa de gravação com sucesso, então retorna falso, indicando que a pessoa já respondeu
            //
            if ( is_object($formLogResult[0]) )
            {
                return false;
            }
        }
	// Validação 2 - Verifica se o usuário tem permissões
        $this->validationCheck = 2;
        $usuarioObj = array();
        $usuarioObj['$login'] = $this->login->refPessoa;
        $usuarioObj['$perfis'] = is_array($this->login->perfis) ? array_keys($this->login->perfis) : $this->login->perfis;

        if (is_array($this->login->perfis))
        {
            $avaAvaliacao->getFormularios(array_keys($this->login->perfis));
            if (count($avaAvaliacao->formularios)>0)
            {
                $checkForm = false;
		foreach ($avaAvaliacao->formularios as $keyF => $formulario)
                {
                    if ($formulario->idFormulario == $this->formulario->idFormulario)
                    {
                        $checkForm = true;
                    }
                }
                if (!$checkForm)
                {
                    return false;
                }
                if (count($this->login->perfis)>0)
                {
                    if (!$this->formulario->verificaRegra($usuarioObj))
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
        return true;
    }
    
    public function exibeMensagemSucesso()
    {
        $fields[] = new adynamicformmessage('alreadyfilledmsg', 'Parabéns, a sua avaliação foi enviada com sucesso.');
        $this->setFields($fields);
    }
}

?>
