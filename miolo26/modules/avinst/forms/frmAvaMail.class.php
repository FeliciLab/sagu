<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_mail.
 *
 * @author Name [name@solis.coop.br]
 *
 * \b Maintainers: \n
 * Name [name@solis.coop.br]
 *
 * @since
 * Creation date 25/01/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmAvaMail extends AManagementForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaMail';
        parent::__construct(_M('Envio de emails', MIOLO::getCurrentModule()));
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/avaPerfil.class.php',$module);
        $MIOLO->uses('types/avaAvaliacao.class.php',$module);
        $MIOLO->uses('types/avaFormulario.class.php',$module);
        $perfil = new avaPerfil();
        $perfil->__set('avaliavel',DB_TRUE); // Seta apenas os prefis que podem ser avaliados
        $avaliacao = new avaAvaliacao(); 
        $formulario = new avaFormulario();       
        
        if ( MIOLO::_REQUEST('function')  ==  'edit' )
        {
            $avaMail = new $this->target();
            $avaMail->__set($avaMail->getPrimaryKeyAttribute(),MUtil::getAjaxActionArgs()->item);
            $avaMail->populate();
            $fields[] = new MTextField('idMail', '', _M('Código', $module), 10, null, null, true);
            $validators[] = new MIntegerValidator('idMail', '', 'required');
        }

        $tempoMesesDivulgacaoResultados = TIME_MONTHS_RELEASE_RESULTS > 0 ? TIME_MONTHS_RELEASE_RESULTS : 0;
        $avaliacoes = $avaliacao->getAvaliacoesAbertas(ADatabase::RETURN_ARRAY, null, date('d/m/Y', strtotime(date('m/d/Y')." - $tempoMesesDivulgacaoResultados month")));
        $fields['refAvaliacao'] = new MSelection('refAvaliacao', null, _M('Avaliação', $module), $avaliacoes);
        $fields['refAvaliacao']->addAttribute('onChange',MUtil::getAjaxAction('carregarFormularios',null));
        $fields['refPerfil'] = new MSelection('refPerfil', null, _M('Perfil', $module), $perfil->search());
        $fields['refPerfil']->addAttribute('onChange',MUtil::getAjaxAction('carregarFormularios',null));
        $label = new MLabel(_M('Formulario', $module).':');
        $label->setClass('mCaption');
        $subFields[] = new MSpan(null,$label,'label');
        $subFields[] = new MDiv('refFormularioDiv', $this->carregarFormularios($avaMail), 'field');
        $fields[] = new MSpan(null, $subFields);
        unset($subFields);
        $label = new MLabel(_M('Enviar para', $module).':');
        $label->setClass('mCaption');
        $subFields[] = new MSpan(null,$label,'label');
        $subFields[] = new MDiv('grupoEnvioDiv', $this->carregarGruposEnvio($avaMail), 'field');
        $fields[] = new MSpan(null, $subFields);
        //$fields[] = new MSelection('grupoEnvio', '', _M('Enviar para', $module), avaMail::getSendGroups() );
        $fields[] = new MMultiLineField('cco', null, _M('Cco'), null, 3, 50, _M('Utilize  ;  como separador de e-mails'));
        $fields['tipoEnvio'] = new MSelection('tipoEnvio', '', _M('Envio', $module), avaMail::getSendTypes() );
        $fields['tipoEnvio']->addAttribute('onChange',MUtil::getAjaxAction('carregarHorarios'));
        $fields[] = new MDiv('datahoraDiv', $this->carregarHorarios($avaMail));
        unset($subFields);
        $label = new MLabel(_M('Assunto', $module).':');
        $label->setClass('mCaptionRequired');
        $subFields[] = new MSpan(null,$label,'label');
        $subFields[] = new MTextField('assunto', '', null, 60);
        $subFields[] = new MButton('carregarEmailConfs', _M('Carregar email padrão', $module)); 
        $fields[] = new MDiv(null,$subFields);
        $fields[] = new MEditor('conteudo', '', _M('Conteúdo', $module));
        $fields[] = $this->getButtons();
        $this->addFields($fields);
        $validators[] = new MIntegerValidator('refAvaliacao', '', 'required');
        $validators[] = new MIntegerValidator('refPerfil', '', 'required');
        //$validators[] = new MIntegerValidator('grupoEnvio', '', 'required');
        $validators[] = new MIntegerValidator('tipoEnvio', '', 'required');
        //$validators[] = new MRequiredValidator('datahora');
        $validators[] = new MRequiredValidator('assunto');
        $validators[] = new MRequiredValidator('conteudo');        
        $this->setValidators($validators);
    }
    
    public function carregarEmailConfs_click()
    {
        $this->assunto->setValue(MAIL_DEFAULT_SUBJECT);
        $this->conteudo->setValue(MAIL_DEFAULT_BODY);
        //$this->page->setElementValue('refFormulario', MUtil::getAjaxActionArgs()->refFormulario);
    }
    
	/**
     * Ação do botão salvar.
     */
    public function saveButton_click()
    {
        if( ! $this->validate() )
        {
            new MMessageWarning('Verifique os dados informados');
            return;
        }
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $args = MUtil::getAjaxActionArgs();
        
        if( strlen($args->refFormulario) == 0 )
        {
            unset($args->refFormulario);    
        }
        
        if( strlen(trim($args->datahora)) == 0 )
        {
            $args->datahora = date('d/m/Y G:H');    
        }
        
        $type = new $this->target($args);
        $pk = $type->getPrimaryKeyAttribute();

        switch ( MIOLO::_REQUEST('function') )
        {
            case 'insert':
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
                    new MMessageError(MSG_RECORD_INSERT_ERROR . ' ' . $e);
                }

                break;
            case 'edit':
                if ( $type->update() )
                {
                    $linkOpts[$pk] = $type->__get($pk);
                    $linkOpts['function'] = 'search';
                    $link = new MLinkButton(null,"($linkOpts[$pk])",$MIOLO->getActionUrl($module, $action, null, $linkOpts));
                    new MMessageSuccess(_M(MSG_RECORD_UPDATED,avinst,$link->generate()));
                }
                else
                {
                    new MMessageError(MSG_RECORD_UPDATE_ERROR . ' ' . $e);
                }

                break;
        }
    }
    
    public function carregarFormularios($avaMail)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();        
        $MIOLO->uses('types/avaFormulario.class.php',$module);
        $args = MUtil::getAjaxActionArgs();
        
        if( $args->function == 'edit' && is_null($args->refAvaliacao) )
        {
            $args->refAvaliacao = $avaMail->__get('refAvaliacao');
        }
        if( $args->function == 'edit' && is_null($args->refPerfil) )
        {
            $args->refPerfil = $avaMail->__get('refPerfil');
        }
        
        $avaFormulario = new avaFormulario();
        $avaFormulario->__set('refAvaliacao',$args->refAvaliacao);
        $avaFormulario->__set('refPerfil',$args->refPerfil);
        $avaForms = $avaFormulario->search();
        
        if( ! empty($avaForms) )
        {
            foreach ( $avaForms as $formulario )
            {
                $formularios[] = array($formulario[0],$formulario[3]);
            }
        }
        
        $fields['refFormulario'] = new MSelection('refFormulario', $args->refFormulario, _M('Formulario', $module), $formularios, null, _M('Para mandar emails para um formulario apenas, informe este campo.'));
        $fields['refFormulario']->setOption(null,_M('Todos'));
        $fields['refFormulario']->addAttribute('onChange',MUtil::getAjaxAction('carregarGruposEnvio',null));
        
        if( (strlen($args->refAvaliacao) == 0 && strlen($args->refPerfil) == 0) || empty($formularios) )
        {
            $fields['refFormulario']->options = array(0=>'Todos');    
        }        
        
        if( MUtil::getDefaultEventValue() == __FUNCTION__ )
        {
            $this->setResponse($fields, 'refFormularioDiv');
        }
        
        return $fields;
    }
    
    public function carregarGruposEnvio($avaMail)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();        
        $MIOLO->uses('types/avaFormulario.class.php',$module);
        $MIOLO->uses('types/avaGranularidade.class.php',$module);
        $MIOLO->uses('classes/aservice.class.php',$module);
        $args = MUtil::getAjaxActionArgs();
        $options = avaMail::getSendGroups();
                
        if( $args->function == 'edit' && is_null($args->refFormulario) )
        {
            $args->refFormulario = $avaMail->__get('refFormulario');
        }
        
        if( $args->function == 'edit' && is_null($args->grupoEnvio) )
        {
            $args->grupoEnvio = $avaMail->__get('grupoEnvio');
        }
        
        if( strlen($args->refFormulario) > 0 && $args->refFormulario != 0 && MUtil::getDefaultEventValue() == __FUNCTION__ )
        {
            $avaFormulario = new avaFormulario();
            $avaFormulario->__set('idFormulario',$args->refFormulario);
            $avaFormulario->__set('refAvaliacao',$args->refAvaliacao);
            $avaFormulario->__set('refPerfil',$args->refPerfil);        
            $avaFormulario->populate();
            
            foreach ( $avaFormulario->__get('blocos') as $bloco )
            {
                $avaGranularidade = new avaGranularidade();
                $avaGranularidade->__set('idGranularidade',$bloco->__get('refGranularidade'));
                $avaGranularidade->populate();
                $opcoesEmail = unserialize($avaGranularidade->__get('opcoes'))->opcoesEmail;
                
                // FIXME: Ver com o pablo como cadastrar a descrição que vai aparecer na tela de envio de emails para "Lideres de turma"
                // que antes estava como opcoesEmail na granularidade
                if( count($opcoesEmail) >= 4 ) // Se possui as 4 configurações de email
                {
                    foreach ( $opcoesEmail as $opcaoEmail )
                    {
                        if( $opcaoEmail->tipoDeTratamento == AService::MAIL_CADASTRE_SEND_GROUP_DESCRIPTIVE )
                        {
                            $options[] = array($opcaoEmail->atributo,$opcaoEmail->descritivo);
                        }
                    }
                }
            }    
        }
        $options = array_unique($options); // Remove os valores duplicados do array
        $fields['grupoEnvio'] = new MSelection('grupoEnvio', $args->grupoEnvio, _M('Enviar para', $module), $options);
        $fields['grupoEnvio']->options = $options;
           
        if( in_array( MUtil::getDefaultEventValue(), array(__FUNCTION__,'carregarFormularios')) )
        {
            $this->setResponse($fields, 'grupoEnvioDiv');
        }
        
        return $fields;
    }
    
    public function carregarHorarios($avaMail)
    {
        $fields['datahora'] = new MTimestampField('datahora', '', _M('Horário'));
        $fields['datahora']->getTimeField()->setClickableIncrement('01:00:00');
        $fields['datahora']->getTimeField()->setVisibleIncrement('01:00:00');
        $container = new MFormContainer(NULL, $fields);
        $args = MUtil::getAjaxActionArgs();
        
        if( $args->function == 'edit' && is_null($args->tipoEnvio) )
        {
            $args->tipoEnvio = $avaMail->__get('tipoEnvio');
        }
        
        if( $args->tipoEnvio != avaMail::TIPO_ENVIO_AGENDADO )
        {
            $container->addStyle('display', 'none');
        }

        if( MUtil::getDefaultEventValue() == __FUNCTION__ )
        {
            $this->setResponse($container, 'datahoraDiv');
        }
        
        return $container;
    }
        
}


?>
