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
class frmDashboard extends ADynamicForm
{

    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
        $MIOLO->uses('types/avaFormulario.class.php', 'avinst');
        $MIOLO->uses('types/avaFormLog.class.php', 'avinst');
        $MIOLO->uses('types/avaPerfil.class.php', 'avinst');
        $MIOLO->uses('classes/adynamicformmessage.class.php', 'avinst');
        $MIOLO->uses('classes/awidgetcontrol.class.php', 'avinst');
        parent::__construct(null);
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $fields[] = new MDiv(); 
        parent::createFields();
        if ($this->login->loginType == self::ADYNAMICFORM_LOGIN_TYPE_SIMULATION)
        {
            if ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT) || $MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN))
            {
                $link = new MLink('mainLink', 'clique aqui', $MIOLO->getActionURL('avinst', 'main'), 'clique aqui');
                $message = 'AVISO: Você está em modo de simulação de ambiente do usuário '.$this->login->refPessoa.', por meio de permissões administrativas. Abaixo, a tela tem a mesma aparência ao qual é apresentada ao usuário em uma situação normal de acesso, com exceção da coluna de status de acesso na(s) lista(s) de formulários disponíveis para avaliar, ao qual, está disponível somente para usuários administrativos. Caso queira retornar à tela de acompanhamento administrativo, '.$link->generate().'.';
                $fields[] = MMessage::getStaticMessage(NULL, $message, MMessage::TYPE_INFORMATION);
            }
            else
            {
                new MMessageError('Você não tem permissões para acessar o modo de simulação. Por favor, contate o administrador do sistema');
            }
        }
        
        $fields[] = new MDiv();
        $avaliacoes = $this->getPanels();
        
        if (is_array($avaliacoes))
        {
            $avaAvaliacao = new avaAvaliacao();  
            foreach ($avaliacoes as $avaliacao)
            {
                if ($avaAvaliacao->checkAvaliacaoAtiva($avaliacao->data->idAvaliacao))
                {
                    $formData = $this->getForms($avaliacao->data);
                }

                $dashboardData = $this->getDashboard($avaliacao->data);
                if ($formData || $dashboardData)
                {
                    $fieldsi = array();
                    if (is_array($formData))
                    {
                        foreach ($formData as $fd)
                        {
                            $fieldsi[] = $fd;
                        }
                        $fieldsi[] = new MSeparator('');
                    }
                    if (is_array($dashboardData))
                    {
                        foreach ($dashboardData as $dd)
                        {
                            $fieldsi[] = $dd;
                        }
                    }
                    $avaliacao->field->setControls($fieldsi);
                    $fields[] = $avaliacao->field;
                }
            }
        }
        else
        {
            // Aqui colocar avaliações antigas
        }
        $this->addFields($fields);
    }
    

    //
    // Obtém as avaliações abertas e retorna para exibição em tela os links de acesso
    //
    public function getPanels($args = null)
    {
        $MIOLO = MIOLO::getInstance();

        // Obtém as avaliações
        $avaliacao = new avaAvaliacao();  
        if ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT) || $MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN))
        {
            $avaliacoes = $avaliacao->getAvaliacoesAbertas(ADatabase::RETURN_OBJECT, false, null, true);
        }
        else
        {
            $avaliacoes = $avaliacao->getAvaliacoesAbertas(ADatabase::RETURN_OBJECT, null, null, true);
        }
        
        if (is_array($avaliacoes))
        {
            foreach ($avaliacoes as $avaliacao)
            {
                $evaluation = new stdClass();
                $evaluation->field = new MBaseGroup('avaliacao'.$avaliacao->idAvaliacao, $avaliacao->nome);
                $evaluation->data = $avaliacao;
                $data[] = $evaluation;
            }
        }
        else
        {
            $data = false;
        }
        return $data;
    }
    
    //
    // Obtém os formulários disponíveis a avaliar
    //
    public function getForms($avaliacao)
    {
        $MIOLO = MIOLO::getInstance();
        
        $formsValidos = $this->checkForms($avaliacao);
        
        if ($formsValidos>=1)
        {
            $fields = array();
            //
            // Criação da área de formulários
            //
            if (count($avaliacao->formularios)>0)
            {
                $imageSendSuccess = new MImage(null,null,$MIOLO->getUI()->getImageTheme('avinst','success-16x16.png'),'style="vertical-align:bottom;padding-right:10px;"');
                $imageSendUnsuccess = new MImage(null,null,$MIOLO->getUI()->getImageTheme('avinst','error-16x16.png'),'style="vertical-align:bottom;padding-right:10px;"');
                
                foreach ($avaliacao->formularios as $pos => $formulario)
                {
                    $fieldForm = $formulario->nome;
                    $fieldsForm[] = $fieldForm;
                    $fieldsForm[] = '&nbsp'.$formulario->descritivo;
                    $formLogFilter = new stdClass();
                    $formLogFilter->refAvaliador = $this->login->refPessoa;
                    $formLogFilter->refFormulario = $formulario->idFormulario;
                    $avaFormLog = new avaFormLog($formLogFilter);
                    $statusRespondente = $avaFormLog->obtemStatusRespondente();
                    
                    if ( $this->login->loginType == self::ADYNAMICFORM_LOGIN_TYPE_SIMULATION ) // Simulação
                    {
                        if ($statusRespondente == false)
                        {
                            $status = 'Sem tentativas de acesso para este formulário';
                        }
                        else
                        {
                            if (is_object($statusRespondente))
                            {
                                $tiposLog = $avaFormLog->retornaTiposLog();
                                if ($statusRespondente->tipoAcao == avaFormLog::FORM_LOG_SUCCESS)
                                {
                                    $status = '"'.$tiposLog[avaFormLog::FORM_LOG_SUCCESS].'" em '.$statusRespondente->data;
                                }
                                else
                                {
                                    $status = 'A última ação foi "'.$tiposLog[$statusRespondente->tipoAcao].'" em '.$statusRespondente->data;
                                }
                            }
                            else
                            {
                                $status = 'Sem tentativas de acesso para este formulário';
                            }
                        }
                        $fieldsForm[] = new MDiv('divDescription'.$formulario->idFormulario, 'Status: '.$status, 'divStatusAvaliacao');
                    }
                    else // Acesso normal
                    {
                        if (is_object($statusRespondente))
                        {
                            $tiposLog = $avaFormLog->retornaTiposLog();
                            if ($statusRespondente->tipoAcao == avaFormLog::FORM_LOG_SUCCESS)
                            {
                                $status = $imageSendSuccess->generate() . 'Respondido com sucesso em '.$statusRespondente->data;
                            }
                            else
                            {
                                $status = $imageSendUnsuccess->generate() . 'Não respondido';
                            }                            
                        }
                        else
                        {
                            $status = $imageSendUnsuccess->generate() . 'Não respondido';
                        }
                        $fieldsForm[] = new MDiv('divDescription'.$formulario->idFormulario, $status, 'divStatusAvaliacao');
                    }
                    unset($fieldLink);
                    // Cria argumentos para acesso
                    $args['idFormulario'] = $formulario->idFormulario;
                    $formArgs[] = $args;
                    $fieldsLine[] = $fieldsForm;
                    unset($fieldsForm);
                    unset($args);
                }
                $tableRaw = new MTableRaw('Formulários disponíveis para responder', $fieldsLine);
                $tableRaw->setClass('avinstTableRawSelectForm', false);

                foreach ($fieldsLine as $pos => $fline)
                {
                    $tableRaw->setRowAttribute($pos, 'onClick', MUtil::getAjaxAction('redirectToForm', $formArgs[$pos]));
                    $tableRaw->setCellClass($pos, 0, 'avinstLinkToForm');
                    $tableRaw->setCellClass($pos, 1,  'avinstFormDescription');
                    $tableRaw->setRowClass($pos, 'avinstTableRawRowSelectForm');
                }

                $fieldsbg[] = $tableRaw;
                $fieldB = new MVContainer('vctavaliacao'.$avaliacao->idAvaliacao, $fieldsbg);
                $fieldB->setWidth('100%');
                $fields[] = $fieldB;
                unset($fieldsbg);
            }
            else
            {
                $msg = _M('Não foram encontrados vínculos para o usuário logado. Quaisquer dúvidas, por favor, contate o setor da avaliação. <br/>Obrigado.');
                if ( defined('ALREADY_FILLED_MESSAGE') )
                {
                    $msg = ALREADY_FILLED_MESSAGE;                    
                }
                $fields[] = new adynamicformmessage('alreadyfilledmsg', $msg, false);
            }
        }
        else
        {
            $msg = _M('Não foram encontrados vínculos para o usuário logado. Quaisquer dúvidas, por favor, contate o setor da avaliação. <br/>Obrigado.');
            if ( defined('ALREADY_FILLED_MESSAGE') )
            {
                $msg = ALREADY_FILLED_MESSAGE;                    
            }
            $fields[] = new adynamicformmessage('alreadyfilledmsg', $msg, false);
        }
        return $fields;
    }
    
    /**
     * Valida os forms válidos
     * 
     * @param avaAvaliacao $avaliacao
     * @return int
     */
    public function checkForms($avaliacao)
    {
        $usuarioObj = array();
        $usuarioObj['$login'] = $this->login->refPessoa;
        $usuarioObj['$perfis'] = is_array($this->login->perfis) ? array_keys($this->login->perfis) : $this->login->perfis;
        $formsValidos = 0;        
        //
        // Se o login fornecido conter vÃ­nculos, entÃ£o, continua o processo
        //                
        if (is_array($this->login->perfis))
        {   
            if (count($this->login->perfis)>0)
            {
                // Verificar forms
                $avaliacao->getFormularios(array_keys($this->login->perfis));                
                
                if (count($avaliacao->formularios)>0)
                {
                    foreach ($avaliacao->formularios as $keyF => $formulario)
                    {
                        if (!$formulario->verificaRegra($usuarioObj))
                        {
                            unset($avaliacao->formularios[$keyF]);
                        }
                        else
                        {
                            $formsValidos++;
                        }
                    }
                    unset($formulario);
                }
            }
        }
        
        return $formsValidos;
    }
    
    //
    // Obtém os widgets das tabs carregadas
    //
    public function getTabWidgets($avaliacao, $perfil, $returnWidgets = false)
    {
        // Cria os filtros
        $formularios = $avaliacao->getFormularios();
        $params->idAvaliacao = $avaliacao->idAvaliacao;
        $params->formularios = $formularios;
        $params->login = $this->login;
        $params->perfil = $perfil->dadosPerfil;

        // Estrutura os widgets para serem apresentados
        $widgetControl = new AWidgetControl($params->idAvaliacao, $perfil->idPerfil, $params);
        if ($widgetControl->hasWidgets())
        {
            if ($returnWidgets == true)
            {
                $widgets = $widgetControl->getContent();
                if (is_array($widgets))
                {
                    foreach ($widgets as $widget)
                    {
                        $row = $widget->linha;
                        $column = $widget->coluna;
                        if (!isset($orderedWidgets[$row]))
                        {
                            $orderedWidgets[$row] = array();
                        }
                        while (isset($orderedWidgets[$row][$column]))
                        {
                            $column++;
                        }
                        $orderedWidgets[$row][$column] = $widget;
                    }
                }
                if (is_array($orderedWidgets))
                {
                    $elements = array();
                    foreach ($orderedWidgets as $line)
                    {
                        $elements[] = new MHContainer(null, $line);
                    }
                    $panel = array();
                    $panel[] = new MVContainer(null, $elements);
                }
                $fieldsC[] = new MHContainer('panel'.$params->idAvaliacao.'_'.$formulario->idFormulario, $panel);
            }
            else
            {
                $fieldsC = true;
            }
        }
        else
        {
            $fieldsC = false;
        }
        $fieldsC[] = new MDiv('divResponseWidget', null);
        return $fieldsC;
    }
    
    //
    //
    //
    public function dashboardTabLoad($args)
    {
        
        // Como a tab não suporta utilização de args, mandamos via ajaxAction nativo,
        // precisando separar aqui os elementos
        $args = MUtil::getAjaxActionArgs();
        
        $idAvaliacao = $args->idAvaliacao;
        $idPerfil = $args->idPerfil;
        $idTab = $args->idTab;
        // Cria o objeto da avaliacao
        $filters = new stdClass();
        $filters->idAvaliacao = $idAvaliacao;
        $avaAvaliacao = new avaAvaliacao($filters, true);
        // Cria o objeto do perfil
        $filters = new stdClass();
        $filters->idPerfil = $idPerfil;
        $avaPerfil = new avaPerfil($filters, true);

        $perfil = new stdClass();
        $perfil->idPerfil = $idPerfil;
        $perfil->avaPerfil = $avaPerfil;
        $perfil->dataPerfil = $this->login->perfis[$idPerfil];
        $fields = $this->getTabWidgets($avaAvaliacao, $perfil, true);
        if (isset($fields))
        {
            MTabbedBaseGroup::updateTab($idTab, $fields);
        }
    }
    
    //
    // Função que retorna o dashboard específico
    //
    public function getDashboard($avaliacao)
    {
        $tabbed = new MTabbedBaseGroup('tabbed'.$avaliacao->idAvaliacao, $tabs, false);
        //
        // Agora verifica pelos perfis da pessoa, para verificar quais são os widgets específicos liberados
        //
        if (is_array($this->login->perfis))
        {
            $openTab = true;
            // O openTab indica se a tab selecionada é a aberta, portanto, sempre a primeira tab será a selecionada
            // no carragemento, fazendo com que deva ser carregado os elementos dentro da tela.
            foreach ($this->login->perfis as $idPerfil => $dadosPerfil)
            {
                // Obtém as informações do perfil
                $perfilData = new stdClass();
                $perfilData->idPerfil = $idPerfil;
                $avaPerfil = new avaPerfil($perfilData, true);

                //
                // Estrutura a classe para manipulação de standards
                //
                $perfil = new stdClass();
                $perfil->idPerfil = $idPerfil;
                $perfil->dadosPerfil = $dadosPerfil;
                $perfil->avaPerfil = $avaPerfil;
                $fieldsC = $this->getTabWidgets($avaliacao, $perfil, $openTab);
                $openTab = false;
                if ($fieldsC)
                {
                    // Se $openTab == true, então, o retorno da getTabWidgets devolve os widgets
                    // Caso contrário, retorna true ou false, para poder exibir a aba, indicando ou não a existência de
                    // widgets habilitados
                    if ($fieldsC === true)
                    {
                        $args = array();
                        $args['idPerfil'] = $perfil->idPerfil;
                        $args['idAvaliacao'] = $avaliacao->idAvaliacao;
                        $fieldTip = new MSpan(null, 'Carregando, por favor, aguarde...');
                        $tabName = 'tabDashboard'.$avaliacao->idAvaliacao.'_'.$idPerfil;
                        $ajaxString = 'dashboardTabLoad;idAvaliacao='.$avaliacao->idAvaliacao.'&idPerfil='.$idPerfil.'&idTab='.$tabName;
                        $tab = new MTab($tabName, $avaPerfil->descricao, $fieldTip, $ajaxString);
                        unset($args);
                    }
                    else
                    {
                        $tab = new MTab('tabDashboard'.$avaliacao->idAvaliacao.'_'.$idPerfil, $avaPerfil->descricao, $fieldsC);
                    }
                    $tab->setwidth('100%');
                    $tabs[] = $tab;
                    unset($tab);
                }
                unset($fieldsC);
            }
            // Se tiver tabs (ou seja, há algum widget), então coloca a tab
            if(is_array($tabs))
            {
                $tabbed->setTabs($tabs);
                $fields[] = $tabbed;
            }
        }
        else
        {
            $fields = false;
        }
        return $fields;
    }
    
    //
    //
    //
    public function dashboardCallPopupWidget($args = null)
    {
        $urlArgs = Avinst::getAjaxURLArgs();
        if (strlen($urlArgs->idAvaliacao)>0)
        {
            $title = 'Detalhes';
            try
            {
                $widget = new AWidgetControl($urlArgs->idAvaliacao, $urlArgs->refPerfil, $urlArgs);
                $fields = $widget->getContent($urlArgs->idWidget);
                MPopup::show('dashboardPopup', $fields, $title, true);
            }
            catch (Exception $e)
            {
                MPopup::show('errorDashboard', new MDiv(null, $e->getMessage()), 'Erro!', true);
            }
           
        }
        else
        {
            $fields[] = new MSpan(null, 'Avaliação não encontrada, por favor, contate o administrador do sistema');
            $title = 'Aviso!';
            MPopup::show('dashboardPopup', $fields, $title, true);
        }
    }
   
    public function dashboardCallMessageWidget($args = null)
    {
        $urlArgs = Avinst::getAjaxURLArgs();
        if (strlen($urlArgs->idAvaliacao)>0)
        {
            try
            {
                $widget = new AWidgetControl($urlArgs->idAvaliacao, $urlArgs->refPerfil, $urlArgs);
                $fields = $widget->getContent($urlArgs->idWidget);
                $this->setResponse($fields, 'divResponseWidget');
            }
            catch (Exception $e)
            {
                MPopup::show('errorDashboard', new MDiv(null, $e->getMessage()), 'Erro!', true);
            }
           
        }
        else
        {
            $fields[] = new MSpan(null, 'Avaliação não encontrada, por favor, contate o administrador do sistema');
            $title = 'Aviso!';
            MPopup::show('dashboardPopup', $fields, $title, true);
        }

       
    }
    
    //
    // Redireciona para um formulário em específico
    //
    public function redirectToForm($argsForm = null)
    {
        $MIOLO = MIOLO::getInstance();
        $argsData = MUtil::getAjaxActionArgs();

        $args['idFormulario'] = strlen($argsData->idFormulario)>0 ? $argsData->idFormulario : $argsForm['idFormulario'];
        if( $this->login->loginType == self::ADYNAMICFORM_LOGIN_TYPE_SIMULATION )
        {
            $args['refPessoa'] = $this->login->refPessoa;
        }
        $url = $MIOLO->getActionURL('avinst', 'main:avaRespondeFormulario', null, $args);
        $this->page->redirect($url);
    }
    
}
?>
