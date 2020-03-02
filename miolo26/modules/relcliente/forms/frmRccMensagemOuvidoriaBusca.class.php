<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Fermilab é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Formulário de busca de mensagem de ouvidoria.
 * 
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 12/09/2012
 */
$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
$MIOLO->uses('classes/telaRespostaOuvidoria.class.php', 'relcliente');
class frmRccMensagemOuvidoriaBusca extends frmDinamicoBusca
{
    public function definirCampos()
    {
        parent::definirCampos();
        
        // Remove opções do menu de contexto.
        $this->menu->removeItemByLabel(_M('Editar'));
        $this->menu->removeItemByLabel(_M('Remover'));
        
        // Adiciona novo item ao menu de contexto para cancelar a mensagem.
        $this->menu->addCustomItem(_M('Cancelar'), $this->manager->getUI()->getAjax('bfCancelar:click'), MContextMenu::ICON_CANCEL);

        // TODO: adicionar imagem na ação de solicitar resposta de ouvidoria.
        // Ação para solicitar resposta de ouvidoria.
        $this->menu->addCustomItem(_M('Solicitar resposta'), $this->manager->getUI()->getAjax('bfSolicitarResposta:click'), MContextMenu::ICON_WORKFLOW);
        
        // TODO: adicionar imagem na ação de responder a mensagem de ouvidoria.
        // Ação para responder mensagem de ouvidoria.
        $this->menu->addCustomItem(_M('Responder mensagem'), $this->manager->getUI()->getAjax('bfResponderMensagem:click'), MContextMenu::ICON_WORKFLOW);
         
        // Desabilita opções na toolbar.
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
    }
    
    /**
     * Cancelar a mensagem de ouvidoria.
     */
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
    
    /**
     * Método ajax para montar o popup de solicitação de mensagem. 
     * 
     * @args stdClass Parâmetros do ajax.
     */
    public function bfSolicitarResposta_click($args)
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
            
            // Verifica se já existe algum registro.
            $tipoRespostaOuvidoria = bTipo::instanciarTipo('rccRespostaOuvidoria', 'relcliente');
            $filtro = new stdClass();
            $filtro->mensagemouvidoriaid = $mensagemDeOuvidoriaId;
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
    
    /**
     * Método ajax para salvar a solicitação de resposta.
     * 
     * @param int $mensagemDeOuvidoriaId Código da mensagem de ouvidoria.
     */
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

            new telaRespostaOuvidoria($mensagemDeOuvidoriaId);
        }
    }
}

?>