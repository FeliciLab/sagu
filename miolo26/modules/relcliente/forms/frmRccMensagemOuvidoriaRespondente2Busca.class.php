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
$MIOLO->uses('tipos/rccMensagemOuvidoriaRespondente2.class.php', 'relcliente'); 
$MIOLO->uses('classes/telaRespostaOuvidoria.class.php', 'relcliente');

class frmRccMensagemOuvidoriaRespondente2Busca extends bFormBusca 
{
   protected $colunas;
    
    public function __construct($parametros)
    {
        parent::__construct(_M('Ouvidoria Respondente', MIOLO::getCurrentModule()), $parametros);
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
        $botoes[] = $div = new MDiv('', new MButton('buscaSolicitados', 'Apenas solicitados'));
        $div->addStyle('display', 'inline');
        $botoes[] = $div = new MDiv('', new MButton('buscaRespondidos', 'Respondidos'));
        $div->addStyle('display', 'inline');

        $campos[] = new MRowContainer('', $botoes);
        
        $this->adicionarFiltros($campos);
        
        $colunas[] = new MGridColumn(_M('Código da Resposta', $this->modulo));
        $colunas[] = new MGridColumn(_M('Nome', $this->modulo));
        $colunas[] = new MGridColumn(_M('Orientação', $this->modulo));
        $colunas[] = new MGridColumn(_M('Data/hora prevista', $this->modulo));
        $colunas[] = new MGridColumn(_M('Data/hora de resposta', $this->modulo));

        $this->criarGrid($colunas, TRUE);
        
        // Remove opções do menu de contexto.
        $this->menu->removeItemByLabel(_M('Editar'));
        $this->menu->removeItemByLabel(_M('Remover'));
        $this->menu->removeItemByLabel(_M('Explorar'));

        $this->menu->addCustomItem(_M('Responder mensagem'), $this->manager->getUI()->getAjax('bfResponderMensagem:click'), MContextMenu::ICON_WORKFLOW);

        

        
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
    
    public function buscaSolicitados_click($args)
    {

        $this->definirCampos();
        $filtros =  $this->getData();
        $filtros->solicitados = 1;

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
}
?>