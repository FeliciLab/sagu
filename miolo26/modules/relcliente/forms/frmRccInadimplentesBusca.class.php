<?php

/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 * 
 * @mainteners
 * Bruno Edgar Fuhr [bruno@solis.com.br]
 *
 * @since
 * Class created on 07/11/2012
 */
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
$MIOLO->uses('classes/telaEnviaEmail.class.php', 'relcliente');
$MIOLO->uses('classes/telaEnviaCarta.class.php', 'relcliente');
$MIOLO->uses('classes/telaRegistroContato.class.php', 'relcliente');
$MIOLO->uses('classes/telaPessoaInadimplente.class.php', 'relcliente');
$MIOLO->uses('tipos/rccInadimplentes.class.php', 'relcliente');
$MIOLO->uses('db/BusMailServer.class', 'basic');

class frmRccInadimplentesBusca extends bFormBusca 
{
    protected $colunas;
    
    public function __construct($parametros)
    {
        parent::__construct(_M('Inadimplentes', MIOLO::getCurrentModule()), $parametros);
    }
    
    /**
     * Método reescrito para definir os campos da busca dinâmica.
     */
    public function definirCampos()
    {
        parent::definirCampos();
        
        $filtros = array();
        //Pessoa
        $filtros[] = new bEscolha('personid', 'basperson', 'relcliente', '', _M('Pessoa'), FALSE, 'personid,name');

        //Atraso
        $campo = new MTextField('atraso', '', _M('Atraso'), T_CODIGO);
        $rotulo = new MLabel(_M('dias'));
        $filtros[] = new MRowContainer('', array($campo, $rotulo));
    
        //Comunicado
        $campo = new MTextField('comunicado', '', _M('Não comunicado nos últimos'), T_CODIGO);
        $filtros[] = new MRowContainer('', array($campo, $rotulo));
   
        //Contato realizado
        $filtros[] = new MSelection('foiComunicado', '', _M('Contato realizado'), bBooleano::obterVetorSimNao());

        //Exibir apenas último contato
        $filtros[] = new MSelection('ultimoContato', SAGU::NVL(MIOLO::_REQUEST('ultimoContato'), DB_TRUE), _M("Exibir apenas último contato"), bBooleano::obterVetorSimNao(), null, null, null, false);
        
        $this->adicionarFiltros($filtros);
        
        $colunas = array();
        $colunas[] = new MGridColumn(_M('Código da Pessoa', $this->modulo), 'right', FALSE, '10%');
        $colunas[] = new MGridColumn(_M('Nome', $this->modulo), 'left', FALSE, '55%');
        $colunas[] = new MGridColumn(_M('Saldo Devedor', $this->modulo), 'right', FALSE, '15%');
        $colunas[] = new MGridColumn(_M('Dias do último contato', $this->modulo), 'right', FALSE, '10%');
        $colunas[] = new MGridColumn(_M('Dias de Atraso', $this->modulo), 'right', FALSE, '10%');

        $this->criarGrid($colunas);

        // Remove opções do menu de contexto.
        $this->menu->removeItemByLabel(_M('Editar'));
        $this->menu->removeItemByLabel(_M('Remover'));
        $this->menu->removeItemByLabel(_M('Explorar'));

        $this->menu->addCustomItem(_M('Registrar Contato'), $this->manager->getUI()->getAjax('bfRegistrarContato:click'), MContextMenu::ICON_WORKFLOW);
        $this->menu->addCustomItem(_M('Enviar e-mail'), $this->manager->getUI()->getAjax('bfEmail:click'), MContextMenu::ICON_WORKFLOW);
        $this->menu->addCustomItem(_M('Gerar Carta'), $this->manager->getUI()->getAjax('bfCartaCobranca:click'), MContextMenu::ICON_WORKFLOW);
        $this->menu->addCustomItem(_M('Visualizar informações da pessoa'), $this->manager->getUI()->getAjax('bfInfoPessoa:click'), MContextMenu::ICON_VIEW);
        
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_INSERIR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
        
        $this->grid->setRowMethod('frmRccInadimplentesBusca', 'calcularSaldoDevedor');
        
    }
           
    public function calcularSaldoDevedor($i, $row, $actions, $columns)
    {
        $tipo = bTipo::instanciarTipo('rccInadimplentes', 'relcliente');
        
        $columns[2]->control[$i]->value = $tipo->obterSaldoDevedor($row[0]);
    }
    
    public function bfInfoPessoa_click($args)
    {
    
        $selecionados = $args->selectlabSearchGrid;
        $numSelecionados = count($selecionados);
        
        if ( $numSelecionados > 1 )
        {
            new MMessageWarning(_M('Você deve selecionar apenas um registro.'));
        }
        elseif ( $numSelecionados == 0 )
        {
            new MMessageWarning(_M('Você deve selecionar um registro.'));
        }
        else
        {
            $chave = array_keys($args->selectlabSearchGrid);
            $selecionado = explode("|", $args->selectlabSearchGrid[$chave[0]]);
            $personid = substr($selecionado[1], 0, strlen($selecionado) -1);
            
            new telaPessoaInadimplente($personid);
        }
    }
     
    public function bfRegistrarContato_click($args)
    {
        $selecionados = $args->selectlabSearchGrid;
        if ( count($selecionados) > 1 )
        {
            new MMessageWarning(_M('Selecione apenas um inadimplente para registrar o contato.'));
        }
        else
        {
            $chave = array_keys($selecionados);
            $selecionado = explode("|", $selecionados[$chave[0]]);
            $personid = substr($selecionado[1], 0, strlen($selecionado) -1);

            new telaRegistroContato($personid);
        }
    }
    
    public function bfEmail_click($args)
    {
        $selecionados = $args->selectlabSearchGrid;

        if ( is_array($selecionados) )
        {
            // Obtém vários personid
            $chave = array_keys($selecionados);
            
            foreach ($chave as $key)
            {
            
                $selecionado = explode("|", $selecionados[$key]);
                $personid[] = substr($selecionado[1], 0, strlen($selecionado) -1);
            }
        }

        new telaEnviaEmail($personid);
    }
    
    public function bfCartaCobranca_click($args)
    {
        $selecionados = $args->selectlabSearchGrid;

        if ( is_array($selecionados) )
        {
            // Obtém vários personid
            $chave = array_keys($selecionados);
            
            foreach ($chave as $key)
            {
            
                $selecionado = explode("|", $selecionados[$key]);
                $personid[] = substr($selecionado[1], 0, strlen($selecionado) -1);
            }
        }

        new telaEnviaCarta($personid);
    }
    
    public function enviarEmail($args)
    {       
        //TODO: registrar contato depois de enviar e-mail
        $selecionados = $args->selectlabSearchGrid;
        $MIOLO = MIOLO::getInstance();
        
        // Obtém a configuração do servidor de e-mail
        $busMailServer = new BusinessBasicBusMailServer();
        $mailServerConf = $busMailServer->getMailServer();
        $SMTPSecure = $mailServerConf->secureAuth;
        
        if ( is_array($selecionados) )
        {
            // Obtém vários personid
            $chave = array_keys($selecionados);

            foreach ($chave as $key)
            {
                $selecionado = explode("|", $selecionados[$key]);

                $personid = substr($selecionado[1], 0, strlen($selecionado) -1);
                
                $tipoPessoa = new bTipo('basperson');
                $filtros = new stdClass();
                $filtros->personid = $personid;
                $pessoa = $tipoPessoa->buscar($filtros, 'personid, name, email');
                $pessoa = $pessoa[0];
                $filtros2 = new stdClass();
                $filtros2->miolousername = $MIOLO->getLogin()->id;
                $firma = $tipoPessoa->buscar($filtros2, 'name');
                $firma = $firma[0];
                
                if ($firma->name == null)
                {
                    $firma->name = $filtros2->miolousername;
                }
                
                if ( $pessoa->personid )
                {
                    $parameters = array();
                    $parameters['personId'] = $pessoa->personid;
                    $parameters['SAGU_PATH'] = $MIOLO->getConf("home.modules");
                    
                    $report = new MJasperReport('relcliente');
                    $relatorio = $report->getReportFilePath('relcliente', 'emailInadimplentes', $parameters, 'HTML');
                    $relatorio = file_get_contents($relatorio);
                    
                    $mail = new rccEmail();
                    $mail->adicionarDestinatario($pessoa->email);
                    $mail->definirAssunto('Financeiro');
                    
                    $tipoInstituicao = new bTipo('bascompanyconf');
                    $instituicao = $tipoInstituicao->buscar();
                    $instituicao = $instituicao[0];
                    $mail->definirNomeRemetente($instituicao->name);
                    
                    $mail->definirSMTPSecure($SMTPSecure);
                    
                    $mail->definirConteudo($relatorio);
                    if ( $mail->enviar() )
                    {
                        $enviados[] = $pessoa->name;
                        
                        // Registra o contato.
                        $args->viaEmail = true;
                        $this->registraContatoEmail($pessoa->personid, $relatorio);
                    }
                    else
                    {
                        $naoEnviados[] = $pessoa->name;
                    }
                }
            }
            
            $message = _M('Emails enviados com sucesso para: ') . implode(', ', $enviados);
            if ( count($naoEnviados) > 0 )
            {
                $message .= '<br>' . _M('Falha no envio do email para: ') . implode(', ', $naoEnviados);
            }
            
            new MMessageInformation($message);

        }
        
        MDialog::close('popupResponderMensagem');
    }
    
    public function enviarCarta($args)
    {       
        //TODO: registrar contato depois de enviar e-mail
        $selecionados = $args->selectlabSearchGrid;
        $MIOLO = MIOLO::getInstance();

        if ( is_array($selecionados) )
        {
            // Obtém vários personid
            $chave = array_keys($selecionados);

            foreach ($chave as $key)
            {
                $selecionado = explode("|", $selecionados[$key]);

                $personid = substr($selecionado[1], 0, strlen($selecionado) -1);
                
                $tipoPessoa = new bTipo('basperson');
                $filtros = new stdClass();
                $filtros->personid = $personid;
                $pessoa = $tipoPessoa->buscar($filtros, 'personid, name, email');
                $pessoa = $pessoa[0];
                $filtros2 = new stdClass();
                $filtros2->miolousername = $MIOLO->getLogin()->id;
                $firma = $tipoPessoa->buscar($filtros2, 'name');
                $firma = $firma[0];
                
                if ($firma->name == null)
                {
                    $firma->name = $filtros2->miolousername;
                }

                if ( $pessoa->personid )
                {
                    $parameters = array();
                    $parameters['personId']  = $pessoa->personid;                    
                    $parameters['SAGU_PATH'] = $MIOLO->getConf("home.modules");
                    
                    $report = new MJasperReport('relcliente');
                    $report->executeJRXML('relcliente', 'cartaInadimplentes', $parameters);                    
                }
            }
        }
        
        MDialog::close('popupGeraCarta');
        $this->setNullResponseDiv();
    }
    
    public function registraContatoEmail($personId, $mensagem)
    {
        $MIOLO = MIOLO::getInstance();
        
        $contato = new stdClass();
        $contato->pessoa = $personId;
        $contato->mensagem = $this->formataMensagem($mensagem); 
        $contato->datahoradocontato = date('d/m/y H:m:s');
        $contato->datahoraprevista = date('d/m/y H:m:s');
        // origemdecontatoid HARDCODE?
        $contato->origemdecontatoid = 3;
        // assuntodecontato HARDCODE?
        $contato->assuntodecontato = 4;
        // tipodecontatoid HARDCODE?
        $contato->tipodecontatoid = 5;
        $contato->operador = $MIOLO->getLogin()->id;
        
        $tipoContato = new bTipo('rcccontato');
        $tipoContato->definir($contato);

        return $tipoContato->inserir();
    }
    
    public function registraContato($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $valido = true;
        if ( !$args->origem )
        {
            $valido = false;
            new MMessage(_M('Selecione o campo \'Origem\'.'), MMessage::TYPE_WARNING, true, 'divRegistroContatoAlert');
        }
        else
        {
            $contato = new stdClass();
            $contato->pessoa = $args->personid;
            $contato->datahoradocontato = date('d/m/Y H:m:s');
            $contato->origemdecontatoid = $args->origem;
            // assuntodecontato HARDCODE?
            $contato->assuntodecontato = 4;
            // tipodecontatoid HARDCODE?
            $contato->tipodecontatoid = 5;
            $contato->operador = $MIOLO->getLogin()->id;

            if ( MUtil::getBooleanValue($args->agendar) )
            {
                if ( strlen($args->orientacao) > 0 && strlen($args->datahoraprevista) > 0 )
                {
                    $contato->orientacao = $args->orientacao;
                    $contato->datahoraprevista = $args->datahoraprevista;
                }
                else
                {
                    $valido = false;
                    new MMessage(_M('Os campos \'Data\' e \'Orientação\' são obrigatórios.'), MMessage::TYPE_WARNING, true, 'divRegistroContatoAlert');
                }
            }
            else
            {
                if ( strlen($args->mensagem) > 0 )
                {
                    $contato->mensagem = $args->mensagem;
                }
                else
                {
                    $valido = false;
                    new MMessage(_M('O campo \'Mensagem\' é obrigatório.'), MMessage::TYPE_WARNING, true, 'divRegistroContatoAlert');
                }
            }

            if ( $valido )
            {
                $rccContato = bTipo::instanciarTipo('rcccontato');
                $rccContato->definir($contato); 

                if ( $rccContato->inserir() )
                {
                    new MMessageSuccess(_M('Contato registrado com sucesso.'));                    
                }
                else
                {
                    new MMessageError(_M('Houve um erro ao registrar o contato.'));
                }
            }
            
            MDialog::close('popupRegistroContato');
        }
    }
    
    /**
     * Ação AJAX do evento change da checkbox 'Agendar' do diálogo de registro de contato.
     * 
     * @param type $args
     */
    public function agendarClick($args)
    {
        if ( MUtil::getBooleanValue($args->agendar) )
        {
            $dataPrevista = date("d/m/Y H:m:s");
            $campo['data'] = new MTimestampField('datahoraprevista', $dataPrevista, 'Data');
            $this->setResponse(new MFormContainer('dataDiv', $campo), 'dataDiv');
            unset($campo);
            
            $this->setResponse(null, 'respostaDiv');
            
            $campo['orientacao'] = new MMUltiLineField('orientacao', null, _M('Orientação'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
            $this->setResponse(new MFormContainer('orientacaoDiv', $campo), 'orientacaoDiv');
        }
        else
        {
            $this->setResponse(null, 'dataDiv');
            $this->setResponse(null, 'orientacaoDiv');
            
            $campo[] = new MMUltiLineField('mensagem', $resposta, _M('Mensagem'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
            $this->setResponse(new MFormContainer('respostaDiv', $campo), 'respostaDiv');
        }
    }
    
     /**
     * Formata a mensagem do Ireport para salvá-la na base. Tirando as tags e um eventual excesso de espaços e line feeds.
     * 
     * @param String Mensagem gerada no iReport
     * 
     * @return String Mensagem sem as tags e formatada
     */
    public function formataMensagem($mensagem)
    {
        $aux = strip_tags($mensagem);
        
        //retira um cdd que fica perdido no corpo da mensagem estirpada das tags
        $aux = substr($aux, 53, -1); 
        $aux = str_replace(chr( 10 ), '', $aux);
        $mensagemFormatada = str_replace('      ', chr( 10 ), $aux);
        
        return $mensagemFormatada;
    }

}
?>
