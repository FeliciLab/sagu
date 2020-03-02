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
 * Formulário de busca de mensagem de ouvidoria respodente.
 * 
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 12/09/2012
 */
$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
$MIOLO->uses('classes/telaRespostaOuvidoria.class.php', 'relcliente');
class frmRccMensagemOuvidoriaRespondenteBusca extends frmDinamicoBusca
{
    /**
     * Método reescrito para definir título do formulário.
     * 
     * @param type $parametros 
     */
    public function __construct($parametros)
    {
        parent::__construct($parametros, _M('Responder mensagens de ouvidoria'));
    }
    
    public function definirCampos()
    {
        // Obtém as colunas da tabela.
        $this->colunas = buscaDinamica::buscarDadosDasColunas('relcliente', 'rccRespostaOuvidoria');

        parent::definirCampos(FALSE);
                
        // Remove opções do menu de contexto.
        $this->menu->removeItemByLabel(_M('Editar'));
        $this->menu->removeItemByLabel(_M('Explorar'));
        $this->menu->removeItemByLabel(_M('Remover'));
        
        // TODO: adicionar imagem na ação de responder a mensagem de ouvidoria.
        // Ação para responder mensagem de ouvidoria.
        $this->menu->addCustomItem(_M('Responder mensagem'), $this->manager->getUI()->getAjax('bfResponderMensagem:click'), MContextMenu::ICON_WORKFLOW);
         
        // Desabilita opções na toolbar.
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_INSERIR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
    }
    
    /**
     * Método reescrito para não gerar botões de busca.
     * 
     * @return null.
     */
    protected function obterBotoes()
    {
        return NULL;
    }
    
    /**
     * Método reescrito para obter a pessoa que está logada.
     * 
     * @return stdClass Objeto com o código do usuário logado.
     */
    public function getData()
    {
        $dados = new stdClass();
        
        // TODO: obter usuário logado.
//        $dados->public__rccmensagemouvidoria__respondente = 4;
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
            $selecionado = explode("&", $mensagemDeOuvidoriaId);
            new telaRespostaOuvidoria($mensagemDeOuvidoriaId[0]);
        }
    }
    
}

?>