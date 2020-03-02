<?php

/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 07/11/2012
 */
$MIOLO->uses('classes/telaRegistroContato.class.php', 'relcliente');
$MIOLO->uses('classes/telaPessoa.class.php', 'relcliente');
$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
$MIOLO->uses('tipos/buscaDinamica.class.php', 'base'); 
$MIOLO->uses('tipos/rccMensagemOuvidoria2.class.php', 'relcliente'); 
$MIOLO->uses('classes/telaRespostaOuvidoria.class.php', 'relcliente');

class frmRccMensagemOuvidoria2Busca extends bFormBusca 
{
   protected $colunas;
    
    public function __construct($parametros)
    {
        parent::__construct(_M('Mensagem de Ouvidoria', MIOLO::getCurrentModule()), $parametros);
    }
    
    /**
     * Método reescrito para definir os botões padrões.
     */    
    protected function obterBotoes()
    {
        return NULL;
    }

    /**
     * Método reescrito para definir os campos da busca dinâmica.
     */
    public function definirCampos()
    {
        parent::definirCampos();
                
        $botoes = array();

        $botoes[] = $div = new MDiv('', new MButton('buscaTodos', 'Todos'));
        $div->addStyle('display', 'inline');
        $botoes[] = $div = new MDiv('', new MButton('buscaCancelados', 'Cancelados'));
        $div->addStyle('display', 'inline');
        $botoes[] = $div = new MDiv('', new MButton('buscaEncaminhados', 'Encaminhados'));
        $div->addStyle('display', 'inline');        
        $botoes[] = $div = new MDiv('', new MButton('buscaRespondidos', 'Respondidos'));
        $div->addStyle('display', 'inline');

        $campos[] = new MRowContainer('', $botoes);
        
        $this->adicionarFiltros($campos);
        
        $colunas[] = new MGridColumn(_M('Código da Mensagem', $this->modulo));
        $colunas[] = new MGridColumn(_M('Nome', $this->modulo));

        $this->criarGrid($colunas, TRUE);
        
        // Remove opções do menu de contexto.
        $this->menu->removeItemByLabel(_M('Editar'));
        $this->menu->removeItemByLabel(_M('Remover'));
        $this->menu->removeItemByLabel(_M('Explorar'));

        $this->menu->addCustomItem(_M('Cancelar'), $this->manager->getUI()->getAjax('bfCancelar:click'), MContextMenu::ICON_CANCEL);
        $this->menu->addCustomItem(_M('Responder mensagem'), $this->manager->getUI()->getAjax('bfResponderMensagem:click'), MContextMenu::ICON_WORKFLOW);
        $this->menu->addCustomItem(_M('Solicitar resposta'), $this->manager->getUI()->getAjax('bfSolicitarResposta:click'), MContextMenu::ICON_WORKFLOW);

        

        
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_INSERIR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
    }
    
     /**
     * Método reescrito para obter a pessoa que está logada.
     * 
     * @return stdClass Objeto com o código do usuário logado.
     */
    public function getData()
    {
        $dados = new stdClass();
        
        $MIOLO = MIOLO::getInstance();
        $user = $MIOLO->getLogin()->id;
        $tipo = bTipo::instanciarTipo('basperson', 'relcliente');
        $filtros = new stdClass();
        $filtros->miolousername = $user;
        $busca = $tipo->buscar($filtros, 'personid');
        $dados->public__rccrespostaouvidoria__respondente = $busca[0]->personid;
        //obter personid através do login para filtrar as mensagens de ouvidoria.
        
        return $dados;
    }
    
    /**
     * Método ajax para montar o popup de resposta de mensagem de ouvidoria.
     * 
     * @param stdClass Parametros do ajax.
     */
    public function bfResponderMensagem_click($args)
    {
        $selecionados = $args->selectbSearchGrid;
        
        if ( count($selecionados) > 1 )
        {
            new MMessageWarning('Selecione apenas um registro');
        }
        else
        {
            // Obtém a mensagem de ouvidoria selecionada.
            $chave = array_keys($args->selectbSearchGrid);
            $selecionado = explode("|", $args->selectbSearchGrid[$chave[0]]);
            $mensagemDeOuvidoriaId = substr($selecionado[1], 0, strlen($selecionado) -1);

            new telaRespostaOuvidoria($mensagemDeOuvidoriaId[0]);
        }
    }
    
    public function bfSolicitarResposta_click($args)
    {        
        $selecionados = $args->selectlabSearchGrid;
        
        if ( count($selecionados) > 1 )
        {
            new MMessageWarning('Selecione apenas um registro');
        }
        else
        {
            // Obtém a mensagem de ouvidoria selecionada.
            $chave = array_keys($args->selectlabSearchGrid);
            $selecionado = explode("|", $args->selectlabSearchGrid[$chave[0]]);
            $mensagemDeOuvidoriaId = substr($selecionado[1], 0, strlen($selecionado) -1);

            // Verifica se já existe algum registro.
            $tipoRespostaOuvidoria = bTipo::instanciarTipo('rccRespostaOuvidoria', 'relcliente');
            $filtro = new stdClass();
            $filtro->personid = $mensagemDeOuvidoriaId;
            $solicitacaoDeResposta = $tipoRespostaOuvidoria->buscar($filtro);
           
            $campos = array();
            $campos[] = telaRespostaOuvidoria::obterInformacoesDoContato($mensagemDeOuvidoriaId);
            
            // Obtém os valores da solicitação de resposta já incluída na base de dados.
            if ( is_array($solicitacaoDeResposta) )
            {
                // Verifica se mensagem de ouvidoria já foi respondida.
                if ( strlen($solicitacaoDeResposta[0]->resposta) )
                {
                    new MMessageInformation(_M('A mensagem já foi respondida'));
                    return;
                }
                
                $respostaOuvidoriaId = $solicitacaoDeResposta[0]->respostaouvidoriaid;
                $tipoRespostaOuvidoria->popularPorMensagemOuvidoriaId($mensagemDeOuvidoriaId);
                
                $campos[] = $respostaId = new MTextField('respostaouvidoriaid', $respostaOuvidoriaId);
                $respostaId->addStyle('display', 'none');
                $dataPrevista = $tipoRespostaOuvidoria->datahoraprevista;
                $orientacao = $tipoRespostaOuvidoria->orientacao;
                $respondente = $basEmployee->name;
            }
            $dataPrevista = date("d/m/y H:m:s");
            $campos[] = new MDiv('divMensagemDialogSolicitacao');
            $campos[] = new MTimestampField('datahoraprevista', null, _M('Data prevista'));
            $campos[] = new MMUltiLineField('orientacao', $orientacao, _M('Orientação'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
            
            $tipoPessoa = bTipo::instanciarTipo('basphysicalpersonemployee');
            $pessoas = $tipoPessoa->buscarParaSelection(NULL, 'personid,name');
            $campos[] = new MSelection('respondente', $respondente, _M('Respondente'), $pessoas);

            $botoes = array();

            $imagem = $this->manager->getUI()->getImageTheme(NULL, 'botao_salvar.png');
            $botoes = new MButton('salvarSolicitacao', _M('Salvar'),  MUtil::getAjaxAction('solicitarRespostaOuvidoria', $mensagemDeOuvidoriaId), $imagem);
            $campos[] = MUtil::centralizedDiv($botoes);

            // Mostra o Popup em tela.
            $caixaDialogo = new MDialog('popupSolicitarResposta', _M('Solicitar resposta de ouvidoria'), $campos);
            $caixaDialogo->show();
        }
    }    
    
    public function bfCancelar_click()
    {
        $selecionados = $this->grid->getSelectedData();
        
        if ( count($selecionados) > 1 )
        {
            new MMessageWarning('Selecione apenas um registro');
        }
        else
        {
            $mensagemDeOuvidoriaId = $selecionados[0]['mensagemouvidoriaid'];
            $filtro = new stdClass();
            $filtro->mensagemouvidoriaid = $mensagemDeOuvidoriaId;

            $mensagemOuvidoria = bTipo::instanciarTipo('rccMensagemOuvidoria');
            $mensagemOuvidoria->definir($filtro);
            $mensagemOuvidoria->popular();
            
            // Testa se mensagem já está cancelada.
            if ( strlen($mensagemOuvidoria->estacancelada == DB_TRUE) )
            {
                new MMessage(_M('Esta mensagem já está cancelada.'));
            }
            else
            {
                $campos = array();
                $campos[] = new MDiv('divMensagemDialog');
                $campos[] = new MMUltiLineField('motivocancelamento', NULL, NULL, NULL, T_VERTICAL_TEXTO, 65);

                $botoes = array();

                $imagem = $this->manager->getUI()->getImageTheme(NULL, 'botao_salvar.png');
                $botoes = new MButton('cancelarMensagem', _M('Salvar'),  MUtil::getAjaxAction('salvarCancelamentoMensagem', $mensagemDeOuvidoriaId), $imagem);
                $campos[] = MUtil::centralizedDiv($botoes);

                // Mostra o Popup em tela.
                $caixaDialogo = new MDialog('popupCancelarMensagem', _M('Motivo do cancelamento'), $campos);
                $caixaDialogo->show();
            }
        }
        
    }
    
    
    public function buscaTodos_click($args)
    {

        $this->definirCampos();
        $filtros =  $this->getData();
        $filtros->todos = 1;

        $sqlSemana = $this->tipo->obterConsulta($filtros);
        $this->grid->setQuery($sqlSemana, $this->modulo);

        // Tira a checagem nos checkbox da grid.
        $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);

    }
    
    public function buscaEncaminhados_click($args)
    {

        $this->definirCampos();
        $filtros =  $this->getData();
        $filtros->encaminhados = 1;

        $sqlSemana = $this->tipo->obterConsulta($filtros);
        $this->grid->setQuery($sqlSemana, $this->modulo);

        // Tira a checagem nos checkbox da grid.
        $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);

    }
    
    public function buscaRespondidos_click($args)
    {   
        $this->definirCampos();
        $filtros =  $this->getData();
        $filtros->respondidos = 1; 

        $sqlSemana = $this->tipo->obterConsulta($filtros);
        $this->grid->setQuery($sqlSemana, $this->modulo);

        // Tira a checagem nos checkbox da grid.
        $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);

    }
    
    public function buscaCancelados_click($args)
    {
        $this->definirCampos();
        $filtros =  $this->getData();
        $filtros->cancelados = 1; 

        $sqlSemana = $this->tipo->obterConsulta($filtros);
        $this->grid->setQuery($sqlSemana, $this->modulo);

        // Tira a checagem nos checkbox da grid.
        $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);
    }
    
    
    public function solicitarRespostaOuvidoria($mensagemDeOuvidoriaId)
    {
        $argumentos = MUtil::getAjaxActionArgs();
        $mensagemValidacao = array();
        
        // Corrige a data e hora prevista.
        if ( strlen($argumentos->datahoraprevistaDate) && strlen($argumentos->datahoraprevistaTime) )
        {
            $argumentos->datahoraprevista = $argumentos->datahoraprevistaDate . ' ' . substr($argumentos->datahoraprevistaTime, 1); 
        }
        
        // Valida a data e hora prevista.
        if ( strlen($argumentos->datahoraprevista) == 0 )
        {
            $validado = FALSE;
            $mensagemValidacao[] = _M('É necessário preencher o a data e hora prevista.'); 
        }
        
        // Valida a orientação.
        if ( strlen($argumentos->orientacao) == 0 )
        {
            $validado = FALSE;
            $mensagemValidacao[] = _M('É necessário preencher a orientação.');
        }
        
        // Valida o respondente.
        if ( strlen($argumentos->respondente) == 0 )
        {
            $mensagemValidacao[] = _M('É necessário preencher o respondente.');
        }
        
        // Salva a orientação de resposta caso tenha passado na validação.
        if ( count($mensagemValidacao) == 0 )
        {
            $tipoRespostaOuvidoria = bTipo::instanciarTipo('rccRespostaOuvidoria', 'relcliente');
            $rccMensagemOuvidoria = bTipo::instanciarTipo('rccMensagemOuvidoria', 'relcliente');
            $filtros = new stdClass();
            $filtros->mensagemouvidoriaid = $mensagemDeOuvidoriaId;

            $argumentos->mensagemouvidoriaid = $mensagemDeOuvidoriaId;
            $argumentos->datahoradasolicitacao = date("d/m/Y H:m:s");
            $origemdecontatoid = $rccMensagemOuvidoria->buscar($filtros, 'origemdecontatoid');
            $argumentos->origemdecontatoid = $origemdecontatoid[0]->origemdecontatoid;      
                                    
            $tipoRespostaOuvidoria->definir($argumentos);
 
            // Verifica se é o registro será inserido ou editado.
            if ( strlen($argumentos->respostaouvidoriaid) )
            {
                $salvou = $tipoRespostaOuvidoria->editar();
            }
            else
            {
                $salvou = $tipoRespostaOuvidoria->inserir();
            }
            
            // Verica se registro foi salvo.
            if ( $salvou )
            {
                new MMessageSuccess(_M('Resposta solicitada com sucesso.'));
                
                // Fecha a caixa de dialogo.
                MDialog::close('popupSolicitarResposta');
            }
            else
            {
                new MMessageError(_M('Ocorreu um erro ao solicitar a resposta'));
            }
        }
        else
        {
            new MMessage(implode("<br/>", $mensagemValidacao), MMessage::TYPE_WARNING, true, 'divMensagemDialogSolicitacao');
        }
    }
    
    /**
     * Método ajax para cancelar uma mensagem de ouvidoria.
     * 
     * @param int Código da mensagem de ouvidoria que será cancelado.
     */
    public function salvarCancelamentoMensagem($mensagemDeOuvidoriaId)
    {
        $argumentos = MUtil::getAjaxActionArgs();
        $motivoDeCancelamento = $argumentos->motivocancelamento;

        // Verifica se existe motivo de cancelamento.
        if ( strlen($motivoDeCancelamento) )
        {
            // Cancela a mensagem de ouvidoria.
            $cancelar = rccMensagemOuvidoria::cancelarMensagemDeOuvidoria($mensagemDeOuvidoriaId, $motivoDeCancelamento);

            if ( $cancelar )
            {
                new MMessage(_M('Mensagem de ouvidoria cancelada com sucesso.'));
            }
            else
            {
                new MMessageError(_M('Ocorreu um erro ao cancelar a mensagem.'));
            }

            // Fecha a caixa de dialogo.
            MDialog::close('popupCancelarMensagem');
        }
        else
        {
            new MMessage(_M('É necessário preencher o motivo do cancelamento.'), MMessage::TYPE_WARNING, true, 'divMensagemDialog');
        }
    }
}
?>