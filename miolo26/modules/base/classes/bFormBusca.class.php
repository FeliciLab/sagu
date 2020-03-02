<?php

/**
 * <--- Copyright 2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
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
 * Formulário genérico de busca do Base.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 27/06/2012
 */
class bFormBusca extends bForm
{
    const GRID_DIV = 'labSearchGridDiv';
    const FILTROS_ID = 'labSearch_filtros';

    /**
     * @var MSpecialGrid Grid do formulário de busca.
     */
    protected $grid;

    /**
     * @var MContextMenu Menu da grid.
     */
    protected $menu;
    
    /**
     * @var boolean
     */
    protected $botaoEditar = true;

    /**
     * @var boolean
     */
    protected $botaoRemover = true;
    
    /**
     * @var boolean
     */
    protected $botaoExplorar = true;
    
    /**
     * @var boolean
     */
    protected $botaoNovo = true;

    /**
     * Método para criação de campos específicos dos formulários de busca.
     * 
     * @param boolean $barraDeFerramentas Flag booleana para mostrar ou não a barra de ferramentas.
     */
    public function definirCampos($barraDeFerramentas=TRUE)
    {
        parent::definirCampos($barraDeFerramentas);

        if ( $this->barraDeFerramentas )
        {
            $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_BUSCAR);
            $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_SALVAR);
            
            if ( !$this->botaoEditar )
            {
                $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
            }
            
            if ( !$this->botaoRemover )
            {
                $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
            }
            
            if ( !$this->botaoNovo )
            {
                $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_INSERIR);
            }
        }

        // Desconecta evento de verificação de alterações nos formulários labSaveForm
        $this->page->onload('dojo.disconnect(window.labSaveFormVerificador);');
    }

    /**
     * Obtém botões do formulário de busca.
     * 
     * @return MDiv Componente MDiv com os botões do formulário de busca. 
     */
    protected function obterBotoes()
    {
        $botoes = array();
        
        $imagem = $this->manager->getUI()->getImageTheme(NULL, 'toolbar-reset.png');
        $botoes[] = new MButton('botaoLimpar', _M('Limpar'), ':bfLimpar_click', $imagem);
        
        // FIXME Avaliar o botão de busca, verificar o nome e imagem
        $imagem = $this->manager->getUI()->getImageTheme(NULL, 'toolbar-search.png');
        $botoes[] = new MButton('botaoBuscar', _M('Buscar'), ':botaoBuscar_click', $imagem);
       
        // Adiciona botão buscar no formulário
        return MUtil::centralizedDiv($botoes);
    }
    /**
     * Adicionar os filtros em um contêiner.
     *
     * @param array $filtros Array com os filtros.
     */
    protected function adicionarFiltros($filtros)
    {
        $filtros[] = $this->obterBotoes();
        
        $controles = new MFormContainer(NULL, $filtros);
        $basegroup = new MBaseGroup(self::FILTROS_ID, _M('Filtros'), array( $controles ), MFormControl::LAYOUT_VERTICAL);

        $this->addFields(array( $basegroup ));
    }

    /**
     * Cria a grid, definindo a sua consulta principal.
     *
     * @param array $colunas Array com instâncias da MGridColumn.
     */
    protected function criarGrid($colunas, $mostrarCheckBoxes=TRUE)
    {
        $chaves = array();

        $i = 0;
        
        foreach ( $this->tipo->obterChavesPrimarias() as $chave )
        {
            $chaves[$chave] = "%$i%";
            $i++;
        }
               
        $this->grid = new MSpecialGrid(NULL, $colunas, 'labSearchGrid', 15, $mostrarCheckBoxes, $chaves);
        $this->grid->setQuery($this->tipo->obterConsulta($this->getData()), $this->modulo);
                
        // Cria o menu de contexto.
        $this->criarMenuDeContexto();

        parent::addField(new MDiv(self::GRID_DIV, array( $this->grid, $this->menu )));
    }
    
        /**
     * Retorna um array associativo com os dados marcados nas checkboxs da grid.
     *
     * @return array
     */
    protected function obterDadosSelecionados()
    {
        if ( !$this->grid )
        {
            $this->definirCampos();
        }
        
        return (array) $this->grid->getSelectedData();
    }
    
    /**
     * Retorna lista com os codigos (chave primaria) selecionados na grid.
     *
     * @return array
     */
    protected function obterIdsSelecionados()
    {
        $ids = array();
        
        foreach ( $this->obterDadosSelecionados() as $subReg )
        {
            foreach ( $subReg as $chave => $codigo )
            {
                $ids[] = $codigo;
            }
        }
        
        return $ids;
    }
    
    /**
     * Retorna um id unico selecionado.
     * 
     * CUIDADO:
     *  Utilize apenas esta funcao quando sua acao permite apenas que um registro seja selecionado.
     *  Caso o usuario tenha selecionado mais de um registro na grid ou nenhum, uma excecao sera disparada.
     * 
     * @return int 
     */
    protected function obterIdSelecionado()
    {
        $ids = $this->obterIdsSelecionados();
        $total = count($ids);

        if ( $total > 1 )
        {
            throw new Exception(_M('Você deve selecionar apenas um registro.'));
        }
        else if ( $total == 0 )
        {
            throw new Exception(_M('Você deve selecionar um registro.'));
        }
        
        return $ids[0];
    }
    
    /**
     * Método protegido para criar o menu de contexto.
     * 
     * @return MContextMenu Menu de contexto. 
     */
    protected function criarMenuDeContexto()
    {
        $module = MIOLO::getCurrentModule();
        $this->menu = new MContextMenu('bSearchGridMenu', MContextMenu::TYPE_JS);

        if ( $this->botaoEditar )
        {
            $this->menu->addCustomItem(_M('Editar'), $this->manager->getUI()->getAjax('bfEditar:click'), MContextMenu::ICON_EDIT);
            if($module == SModules::MODULE_CONTASPAGAR)
            {
                $this->grid->addActionUpdate($this->manager->getUI()->getAjax('bfEditar:click'));
            }
        }
                
        if ( $this->botaoExplorar )
        {
            $this->menu->addCustomItem(_M('Explorar'), $this->manager->getUI()->getAjax('bfExplorar:click'), MContextMenu::ICON_VIEW);
            if($module == SModules::MODULE_CONTASPAGAR)
            {
                $this->grid->addActionIcon(_M('Explorar'), 'view', $this->manager->getUI()->getAjax('bfExplorar:click'));
            }
        }
        
        if ( $this->botaoRemover )
        {
            $this->menu->addCustomItem(_M('Excluir'), $this->manager->getUI()->getAjax('bfRemover:click'), MContextMenu::ICON_REMOVE);
            if($module == SModules::MODULE_CONTASPAGAR)
            {
                $this->grid->addActionIcon(_M('Excluir'), 'delete', $this->manager->getUI()->getAjax('bfRemover:click'));
            }
        }
                
        // Verifica se a tabela possui auditoria para montar a ação de consulta
        if ( $this->verificaAuditoria() )
        {
            $this->menu->addCustomItem(_M('Auditorias'), $this->manager->getUI()->getAjax('bfAuditoria:click'), MContextMenu::ICON_AUDITORIA);
            if($module == SModules::MODULE_CONTASPAGAR)
            {
                $this->grid->addActionIcon(_M('Auditorias'), 'auditoria', $this->manager->getUI()->getAjax('bfAuditoria:click'));
            }
        }

        $this->menu->setTarget($this->grid);
                    
        return $this->menu;
    }
    
    /**
     * Verifica se a tabela utilizada pela grid está recebendo auditoria e o 
     * se usuário tem permissão para acessar o processo de auditoria
     * e se o parâmetro de configuração da tabela de auditoria está preenchido
     * 
     * @return boolean
     */
    public function verificaAuditoria()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/auditoria.class', 'basic');
        $tabelaAuditada = false;
        
        if ( $this->tipo->getTabela() )
        {
            //Verifica se a tabela está recebendo auditoria(trigger)
            $ok = auditoria::verificaAuditoria(auditoria::obtemSchemaDaTabela($this->tipo->getTabela()), $this->tipo->getTabela());
            
            //Verifica se a auditoria está habilitada
            $auditoria = SAGU::getParameter('BASIC', 'MIOLO_AUDIT_DATABASE');
            
            //Verifica se a pessoa logada tem permissão na tela de auditoria
            $permissao = $MIOLO->checkAccess('FrmAuditoria', A_ACCESS);
            
            if ( ($ok == DB_TRUE) && (strlen($auditoria) > 0) && ($permissao == DB_TRUE) )
            {
                $tabelaAuditada = true;
            }
        }
        
        return $tabelaAuditada;
    }

    /**
     * Método que atualiza o conteúdo da grid de acordo com os filtros informados.
     */
    public function botaoBuscar_click()
    {
        $filtros = $this->getData();
        $this->grid->setQuery($this->tipo->obterConsulta($filtros), $this->modulo);
       
        // Tira a checagem nos checkbox da grid.
        $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);
    }

    /**
     * Método que redireciona o usuário para a tela de edição.
     */
    public function bfEditar_click()
    {
        $selecionados = $this->grid->getSelectedData();
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
            $args = array(
                'chave' => MIOLO::_REQUEST('chave'),
                'funcao' => FUNCAO_EDITAR,
            );

            foreach ( current($selecionados) as $chave => $valor )
            {
                $args[$chave] = $valor;
            }

            $url = $this->manager->getActionURL($this->modulo, $this->manager->getCurrentAction(), '', $args);
            $this->page->redirect($url);
        }
    }
    
    /**
     * Método que redireciona o usuário para a tela de exploração.
     */
    public function bfExplorar_click()
    {
        $selecionados = $this->grid->getSelectedData();
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
            $args = array(
                'chave' => MIOLO::_REQUEST('chave'),
                'funcao' => FUNCAO_EXPLORAR,
            );

            foreach ( current($selecionados) as $chave => $valor )
            {
                $args[$chave] = $valor;
            }

            $url = $this->manager->getActionURL($this->modulo, $this->manager->getCurrentAction(), '', $args);
            $this->page->redirect($url);
        }
    }
    
    /**
     * Método que redireciona o usuário para a tela de consulta de auditorias.
     */
    public function bfAuditoria_click()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/auditoria.class', 'basic');
                
        $selected = $this->page->request('selectbSearchGrid'); // labSearchGrid
        $selecionados = MSpecialGrid::getSelectedAsIndexedArray($selected);
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
            foreach ( current($selecionados) as $chave => $valor )
            {
                $pkey[$chave] = $valor;
            }

            $optsAuditFunction['function'] = SForm::FUNCTION_SEARCH;
            $optsAuditEvent['event'] = 'localizar_click';
            $optsEdit = array_merge($optsAuditFunction, $pkey, $optsAuditEvent);

            $optsEdit['table_name'] = strtolower($this->tipo->getTabela());
            $optsEdit['schema_name'] = strtolower(auditoria::obtemSchemaDaTabela($this->tipo->getTabela()));

            $url = $this->manager->getActionURL('basic', 'main:config:auditoria', '', $optsEdit);
            $this->page->window($url);
        }
    }
    
    /**
     * Método para limpar o formulário de busca.
     */
    public function bfLimpar_click()
    {
        $parametros = array (
            'chave' => MIOLO::_REQUEST('chave'),
            'funcao' => $this->funcao
        );
        
        $url = $this->manager->getActionURL($this->modulo, $this->manager->getCurrentAction(), '', $parametros);
        $this->page->redirect( $url );
    }

    /**
     * Método que exibe mensagem para confirmar a exclusão do registro.
     */
    public function bfRemover_click()
    {
        $selecionados = $this->grid->getSelectedData();
        $numSelecionados = count($selecionados);
        
        if ( $numSelecionados == 0 )
        {
            new MMessageWarning(_M('Você deve selecionar um registro.'));
        }
        else
        {
            if ( $numSelecionados > 1 )
            {
                $mensagem = _M('Tem certeza que deseja excluir os registros?');
            }
            else
            {
                $mensagem = _M('Tem certeza que deseja excluir o registro?');
            }
            
            MPopup::confirm($mensagem, _M('Confirmação da exclusão'), ':confirmarExclusao');
        }
    }

    /**
     * Método que exclui o regsitro selecionado. Chamado após a mensagem de confirmação.
     */
    public function confirmarExclusao()
    {
        $selecionados = $this->grid->getSelectedData();
        
        $numSelecionados = count($selecionados);

        if ( $numSelecionados > 1 )
        {
            $mensagem = _M('Registro removido com sucesso.');
        }
        else
        {
            $mensagem = _M('Registros removidos com sucesso.');
        }

        // Inicia transação na base de dados.
        bBaseDeDados::iniciarTransacao();

        $remocao = array();

        foreach ( $selecionados as $selecionado )
        {
           $dados = (object) $selecionado;
           $this->tipo->definir($dados);
           $remocao[] = $this->tipo->excluir();
        }

        // Testa se alguma das exclusões não funcionou.
        if ( in_array(false, $remocao) )
        {
            // Reverte a transação na base de dados.
            bBaseDeDados::reverterTransacao();
        }
        else
        {
            // Finaliza a transação atual na base de dados.
            bBaseDeDados::finalizarTransacao();
        }

        // Remove o popup.
        MPopup::remove();

        new MMessage($mensagem, MMessage::TYPE_SUCCESS);

        // Faz a busca novamente.
        $this->botaoBuscar_click();
    }
}

?>
