<?php
$MIOLO->uses('classes/adatabase.class.php','avinst');
$MIOLO->uses('types/avaMail.class.php','avinst');
$MIOLO->uses('types/avaMailLog.class.php', 'avinst');        

class AMail
{
    const HEADERS_HTML = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8;\r\n";
    
    public $avaMail;
    public $avaMailLog;
    
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     * @param $password (tipo) desc
     * @param $user (tipo) desc
     * @param $idkey (tipo) desc
     * @param $setor' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($idMail)
    {
        $data->idMail = $idMail;
        $this->avaMail = new avaMail($data,true);
        $this->avaMailLog = new avaMailLog();
    }
    
    public function enviarEmails()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $MIOLO->uses('classes/aservice.class.php', $module);
        $MIOLO->uses('types/avaPerfil.class.php', $module);
        $MIOLO->uses('types/avaServico.class.php', $module);
        $MIOLO->uses('types/avaFormulario.class.php', $module);
        $MIOLO->uses('types/avaFormLog.class.php', $module);
        $avaServico = new avaServico();
        $avaMailLog = new avaMailLog();
        $avaFormulario = new avaFormulario();
        $avaFormLog = new avaFormLog();
        $avaPerfil = new avaPerfil();
        $respondentes = $avaFormLog->obterAvaliadores( $this->avaMail->__get('refAvaliacao'), avaFormLog::FORM_LOG_SUCCESS, $this->avaMail->__get('refPerfil'), $this->avaMail->__get('refFormulario') );
        $mailCountLot = 0;
        
        // insere o log padrão para os emails com cópia oculta
        if( strlen(trim($this->avaMail->__get('cco'))) > 0 )
        {
            // Retira quebras de linha, espaços em branco e substitui a vírgula por ponto e vírgula
            $cco = str_replace(';', ',', str_replace(' ', '', str_replace("\n", '', $this->avaMail->__get('cco'))));
            $ccos = explode(',',$cco);

            if( count($ccos) > 0 )
            {
                foreach ($ccos as $ccoEmail)
                {
                    $this->avaMailLog->clearData();
                    $data->refMail = $this->avaMail->__get('idMail');
                    $data->destinatario = $ccoEmail;
                    $this->avaMailLog->defineData($data);
                    $this->avaMailLog->insert();
                }
            }
        }
        
        foreach ($avaPerfil->search(ADatabase::RETURN_TYPE) as $perfil) // Percorre todos os perfis cadastrados
        {
            if( $this->avaMail->__get('refPerfil') == $perfil->__get('idPerfil') ) // Quando for o perfil cadastrado no lote de emails
            {
                // Pega o(s) formulario(s) da avaliação e perfil cadastrados no lote de email
                $avaFormulario->__set('refAvaliacao', $this->avaMail->__get('refAvaliacao'));
                $avaFormulario->__set('refPerfil', $this->avaMail->__get('refPerfil'));
                $avaFormulario->__set('idFormulario', $this->avaMail->__get('refFormulario'));
                $formularios = $avaFormulario->search(ADatabase::RETURN_TYPE);
                
                if( count($formularios) > 0 )
                {
                    foreach ($formularios as $formulario)
                    {
                        if( strlen($formulario->__get('refServicoEmail')) > 0 ) // Sem tem serviço de email associado ao formulário
                        {
                            // Busca as pessoas aptas a responder a avaliação para o perfil da interação do foreach
                            $avaServico->__set('idServico',$formulario->__get('refServicoEmail'));
                            $avaServico->populate();
                            $pessoasAptas = $avaServico->chamaServico();
                            $nroPessoasAptas = count($pessoasAptas);
                            
                            // Faz o parse dos atributos do sistema com os atributos do serviço
                            $atributosSistema = unserialize($avaServico->__get('atributos'));
                            foreach ( $atributosSistema as $atributoSistema )
                            {
                                if( AService::MAIL_PERSON_ID_ATTRIBUTE == $atributoSistema->atributoSistema )
                                {
                                    $atributosServico->{AService::MAIL_PERSON_ID_ATTRIBUTE} = $atributoSistema->atributoServico;
                                }
                                if( AService::MAIL_PERSON_NAME_ATTRIBUTE == $atributoSistema->atributoSistema )
                                {
                                    $atributosServico->{AService::MAIL_PERSON_NAME_ATTRIBUTE} = $atributoSistema->atributoServico;
                                }
                                if( AService::MAIL_PERSON_EMAIL_ATTRIBUTE == $atributoSistema->atributoSistema )
                                {
                                    $atributosServico->{AService::MAIL_PERSON_EMAIL_ATTRIBUTE} = $atributoSistema->atributoServico;
                                }
                            }
                            
                            // Define para quem (grupo de envio) deve ser enviado os email (Respondentes, Não respondentes, Ambos)
                            foreach ( avaMail::getSendGroups() as $key => $sendGroup )
                            {
                                if( $key == $this->avaMail->__get('grupoEnvio') )
                                {
                                    foreach ( $pessoasAptas as $pessoaApta ) // Percorre todas as pessoas aptas para criação dos logs para contagem
                                    {
                                        // Se a pessoa apta é respondente
                                        $respondente = isset($respondentes[$pessoaApta->{$atributosServico->{AService::MAIL_PERSON_ID_ATTRIBUTE}}]);
                                        
                                        if( $this->avaMail->__get('grupoEnvio') == avaMail::GRUPO_ENVIO_NAO_RESPONDENTES )
                                        {
                                            $respondente = !$respondente; // Se o envio for para não respondentes
                                        }
                                        if( $this->avaMail->__get('grupoEnvio') == avaMail::GRUPO_ENVIO_AMBOS )
                                        {
                                            $respondente = true; // Se o envio for para ambos
                                        }                            
                                        
                                        // Se a pessoa apta possui email, insere o log padrão
                                        if( $respondente && strlen($pessoaApta->{$atributosServico->{AService::MAIL_PERSON_EMAIL_ATTRIBUTE}}) > 0 )
                                        {
                                            $this->avaMailLog->clearData();
                                            $data->refMail = $this->avaMail->__get('idMail');
                                            $data->refDestinatario = $pessoaApta->{$atributosServico->{AService::MAIL_PERSON_ID_ATTRIBUTE}};
                                            $data->destinatario = $pessoaApta->{$atributosServico->{AService::MAIL_PERSON_EMAIL_ATTRIBUTE}};
                                            $data->refFormulario = $formulario->__get('idFormulario');
                                            $this->avaMailLog->defineData($data);
                                            $this->avaMailLog->insert();                                
                                        }
                                    }
                                    
                                    foreach ( $pessoasAptas as $chave => $pessoaApta ) // Percorre todos as pessoas aptas para atualização dos log para envio = true
                                    {
                                        // Se a pessoa apta é respondente
                                        $respondente = isset($respondentes[$pessoaApta->{$atributosServico->{AService::MAIL_PERSON_ID_ATTRIBUTE}}]);
                                        
                                        if( $this->avaMail->__get('grupoEnvio') == avaMail::GRUPO_ENVIO_NAO_RESPONDENTES )
                                        {
                                            $respondente = !$respondente; // Se o envio for para não respondentes
                                        }
                                        if( $this->avaMail->__get('grupoEnvio') == avaMail::GRUPO_ENVIO_AMBOS )
                                        {
                                            $respondente = true; // Se o envio for para ambos
                                        }
                                        
                                        // Se a pessoa apta possui email
                                        if( $respondente && strlen($pessoaApta->{$atributosServico->{AService::MAIL_PERSON_EMAIL_ATTRIBUTE}}) > 0 )
                                        {
                                            $obj = new stdClass();
                                            $obj->refFormulario = $formulario->__get('idFormulario');
                                            $obj->refDestinatario = $pessoaApta->{$atributosServico->{AService::MAIL_PERSON_ID_ATTRIBUTE}};
                                            $obj->destinatario = 'andre@solis.coop.br'; 
                                            $emails[] = $obj;
                                            $mailCountLot++;
                                        }
                                        
                                        // Se o contador de emails atingiu o número máximo por lote ou for o ultimo emails da lista
                                        if( $mailCountLot == MAIL_NUMBER_DISPATCH_LOT || $chave == ($nroPessoasAptas-1) ) // Número de emails por lote
                                        {
                                            $this->enviarLoteEmails($emails);
                                            $mailCountLot = 0;
                                            unset($emails);                                            
                                        }
                                        
                                    }
                                }                    
                            }
                        }
                    }
                }
            }   
        }

        // Envia emails como cópia oculta se tiver
        if( count($ccos) > 0 )
        {
            $this->enviarLoteEmails(null,$ccos);
        }
    }
    
    public function enviarLoteEmails($destinatarios = null, $ccos = null)
    {
        $headers = self::HEADERS_HTML;

        if( count($destinatarios) > 0 ) // Prepara a string de destinatários para a função mail
        {
            foreach ($destinatarios as $destinatario)
            {
                $destinatariosMail[] = $destinatario->destinatario;
            }
            
            $destinatariosMail = implode(',', $destinatarios); 
        }
        
        if( count($ccos) > 0 ) // Prepara a string de cópias ocultas para a função mail
        {
            foreach ($ccos as $cco)
            {
                $ccosMail[] = $destinatario->destinatario;
            }
            
            $ccosMail = implode(',', $ccosMail);
            $headers .= "Bcc:$ccosMail\r\n";             
        }        

        if( MAIL_DISPATCH_ENABLE == DB_TRUE ) // Se o envio de emails estiver habilitado
        {
            // Evia o lote de emails
            //mail( $destinatariosMail, $this->avaMail->__get('assunto'), $this->avaMail->__get('conteudo'), $headers);

            if( count($destinatarios) > 0 ) // Atualiza os logs para destinatários após mandar os emails
            {
                foreach ($destinatarios as $destinatario)
                {
                    $this->avaMailLog->clearData();
                    $this->avaMailLog->__set('refMail',$this->avaMail->__get('idMail'));
                    $this->avaMailLog->__set('refDestinatario',$destinatario->refDestinatario);
                    $this->avaMailLog->__set('refFormulario',$destinatario->refFormulario);
                    $log = $this->avaMailLog->search(ADatabase::RETURN_TYPE);
                    
                    if( is_object($log[0]) ) // Se tiver log, atualiza o envio como true
                    {
                        $log[0]->__set('envio',DB_TRUE);
                        $log[0]->__set('datahora',date('d/m/Y G:H'));
                        $log[0]->update();
                    }
                }
            }

            if( count($ccos) > 0 ) // Atualiza os logs para cópias ocultas após mandar os emails
            {
                foreach ($ccos as $cco)
                {
                    $this->avaMailLog->clearData();
                    $this->avaMailLog->__set('refMail',$this->avaMail->__get('idMail'));
                    $this->avaMailLog->__set('destinatario',$cco);
                    $log = $this->avaMailLog->search(ADatabase::RETURN_TYPE);

                    if( is_object($log[0]) ) // Se tiver log, atualiza o envio como true
                    {
                        $log[0]->__set('envio',DB_TRUE);
                        $log[0]->__set('datahora',date('d/m/Y G:H'));
                        $log[0]->update();
                    }
                }
            }
            
            if( (count($destinatarios) + count($ccos)) >= MAIL_NUMBER_DISPATCH_LOT )
            {
                //sleep(MAIL_DELAY_TIME_LOT); // Espera X segundos para o servidor de emails não interpretar como spam
            }
        }
    }
}

?>