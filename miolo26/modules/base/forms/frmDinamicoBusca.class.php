<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
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
 * Formulário dinâmico de busca, a busca deve estar previamente cadastrada.
 * 
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 22/08/2012
 */
$MIOLO->uses('tipos/cadastroDinamico.class.php', 'base');
class frmDinamicoBusca extends bFormBusca
{
    /**
     * @var array Vetor de objetos com as colunas da busca. 
     */
    protected $colunas;
    
    public function __construct($parametros, $titulo=NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('tipos/buscaDinamica.class.php', 'base');
        
        // Obtém as colunas da tabela.
        $this->colunas = buscaDinamica::buscarDadosDasColunas($parametros['modulo'], MIOLO::_REQUEST('chave'));
        
        parent::__construct(_M($titulo, MIOLO::getCurrentModule()), $parametros);
    }

    /**
     * Método reescrito para definir os campos da busca dinâmica.
     */
    public function definirCampos($montarCampos=TRUE)
    {
        parent::definirCampos();
        
        $campos = array();
        
        if ( $montarCampos )
        {
            $campos[] = $generico = new MTextField('generico', NULL, _M('Todos os campos'), 150);            
            $generico->addStyle('width', '60%');
            
            $this->adicionarEventoEnter('generico');
        }
        
        // Se tiver colunas definidas, monta o formulário dinamicamente.
        if ( count($this->colunas) > 0 )
        {
            // Busca as tabelas relacionadas com o tipo dinâmico e seta no tipo.
            if ( cadastroDinamico::verificarIdentificador($this->modulo, MIOLO::_REQUEST('chave')) )
            {
                $cadastroDinamico = bTipo::instanciarTipo('cadastroDinamico', 'base');
                $cadastroDinamico->popularPorIdentificador($this->modulo, MIOLO::_REQUEST('chave'));
                $this->tipo->definirTiposRelacionados( $cadastroDinamico->obterTabelasRelacionadas() );
            }

            // Obtém os campos, colunas e chaves da busca.
            list($camposBuscaDinamica, $colunas, $chaves) = $this->gerarFiltrosEColunas();
            
            // Verifica se existe filtros configurados.
            if ( count($camposBuscaDinamica) > 0 && $montarCampos )
            {
                foreach( $camposBuscaDinamica as $campoBusca )
                {
                    $this->adicionarEventoEnter($campoBusca->name);
                }
                $camposBuscaDinamica = new MFormContainer(NULL, $camposBuscaDinamica);

                $filtrosBusca = new MBaseGroup(self::FILTROS_ID, NULL, array( $camposBuscaDinamica ), MFormControl::LAYOUT_VERTICAL);
                $filtrosBusca = str_replace("\n", '', $filtrosBusca->generate());

                $label = new MLabel(_M('Busca avançada'));
                $filtrosBusca = new MExpandDiv('', $label->generate() . $filtrosBusca);

                $campos = array_merge($campos, array( $filtrosBusca ));
            }
            
        }
        else
        {
            throw new Exception(_M('Não foi encontrada uma busca dinâmica para este formulário'));
        }
        
        $this->adicionarFiltros($campos);
        
        $this->criarGrid($colunas, TRUE, $chaves);
    }
    
    /**
     * Gera os filtros e as colunas.
     *
     * @return array Vetor com os filtros, a coluna da grid e as chaves a serem passadas ao form de edição.
     */
    public function gerarFiltrosEColunas()
    {
        $filtros = array();
        $colunasGrid = array();
        $chaves = array();
        $i = 0;

        foreach ( $this->colunas as $coluna )
        {
            // Gera o filtro e a coluna.
            list($filtro, $colunaGrid) = $this->gerarFiltroEColuna($coluna);

            if ( $filtro )
            {
                $filtros[] = $filtro;
            }

            $colunasGrid[] = $colunaGrid;
            
            // Se for chave primária, valor da coluna deve ser passado ao formulário de edição.
            if ( $coluna->restricao == 'p' || $coluna->chave == DB_TRUE )
            {
                $chaves[$coluna->nome] = "%$i%";
            }

            $i++;
        }
        
        return array( $filtros, $colunasGrid, $chaves );
    }

    /**
     * Gera objetos de filtro do formulário e coluna da grid.
     *
     * @param SInfoColuna $coluna Objeto com os dados da coluna.
     * @return array Vetor com o componente de filtro criado de acordo com o tipo da coluna e uma instância de MGridColumn.
     */
    public function gerarFiltroEColuna(bInfoColuna $coluna)
    {
        $filtro = NULL;

        $valor = $coluna->valorPadrao;
        $rotulo = _M($coluna->titulo);

        if ( $coluna->filtravel == DB_TRUE )
        {
            switch ( $coluna->tipo )
            {
                case bInfoColuna::TIPO_BOOLEAN:
                    $filtro = new MSelection($coluna->campo, $valor, $rotulo, bBooleano::obterVetorSimNao());
                    break;

                case bInfoColuna::TIPO_DATA:
                    $filtro = new MCalendarField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;
                
                case bInfoColuna::TIPO_TIMESTAMP:
                    $filtro = new MCalendarField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;

                case bInfoColuna::TIPO_DECIMAL:
                    $filtro = new MFloatField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;
                
                case bInfoColuna::TIPO_NUMERIC:
                    $campo = new MFloatField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;

                case bInfoColuna::TIPO_INTEIRO:
                    $filtro = new MIntegerField($coluna->campo, $valor, $rotulo, T_CODIGO);
                    $validator = new MIntegerValidator($coluna->campo, $rotulo);
                    break;

                case bInfoColuna::TIPO_LISTA:
                    
                    // Verifica se existe valores possíveis, caso contrário, obtém da base.
                    if ( strlen($coluna->valoresPossiveis) )
                    {
                        $possibleValues = explode("\n", trim($coluna->valoresPossiveis));

                        // Usa os valores como chaves
                        $possibleValues = array_combine($possibleValues, $possibleValues);
                    }
                    else
                    {
                        $tipoChaveEstrangeira = bTipo::instanciarTipo($coluna->tabela, $this->modulo);
            
                        // Monta um campo do tipo MSelection com os valores da tabela.
                        if ( $tipoChaveEstrangeira instanceof bTipo )
                        {
                            $possibleValues = $tipoChaveEstrangeira->buscarParaSelection();
                        }
                    }

                    $filtro = new MSelection($coluna->campo, $valor, $rotulo, $possibleValues);
                    break;

                case bInfoColuna::TIPO_TEXTO_LONGO:
                case bInfoColuna::TIPO_TEXTO:
                    $filtro = new MTextField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;
            }
            
            if ( $filtro )
            {
                if ( !$coluna->editavel == DB_TRUE )
                {
                    $filtro->setReadOnly(true);
                    $validator = NULL;
                }

                if ( !$coluna->visivel == DB_TRUE )
                {
                    $filtro->addBoxStyle('display', 'none');
                    $validator = NULL;
                }
            }

            if ( $validator != NULL )
            {
                $this->addValidator($validator);
            }
        }
        
        $alinhamento = $this->obterAlinhamentoPadrao($coluna);

        // Gera a coluna para Grid.
        if ( $coluna->tipo == bInfoColuna::TIPO_BOOLEAN )
        {
            $colunaGrid = new MGridColumn($rotulo, $alinhamento, false, NULL, $coluna->exibirNaGrid == DB_TRUE, bBooleano::obterVetorSimNao(), TRUE);
        }
        else if ( $coluna->tipo == bInfoColuna::TIPO_NUMERIC ) 
        {
            $colunaGrid = new MGridColumn($rotulo, $alinhamento, false, NULL, $coluna->exibirNaGrid == DB_TRUE, NULL, TRUE, '', TRUE);
        }
        else
        {
            $colunaGrid = new MGridColumn($rotulo, $alinhamento, false, NULL, $coluna->exibirNaGrid == DB_TRUE, NULL, TRUE);
        }
        
        return array( $filtro, $colunaGrid );
    }
    
    /**
     *
     * @return string
     */
    public function obterAlinhamentoPadrao(bInfoColuna $coluna)
    {
        $alinhamentos = array(
            bInfoColuna::TIPO_BOOLEAN => 'center',
            bInfoColuna::TIPO_DATA => 'center',
            bInfoColuna::TIPO_TIMESTAMP => 'center',
            bInfoColuna::TIPO_DECIMAL => 'right',
            bInfoColuna::TIPO_NUMERIC => 'right',
            bInfoColuna::TIPO_INTEIRO => 'right',
        );
        
        return MUtil::NVL($alinhamentos[$coluna->tipo], 'left');
    }
    
    /**
     * Método reescrito para tratar a busca dinâmica.
     */
    public function botaoBuscar_click()
    {
        if ( is_array($this->colunas) )
        {
            $sqlConsulta = $this->obterObjetoConsulta();
            
            $this->grid->setQuery($sqlConsulta, $this->modulo);
            
            // Tira a checagem nos checkbox da grid.
            $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        }
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);
    }
    
    /**
     * Método reescrito para definir o SQL de busca dinâmica.
     *
     * @param array $colunas Array com instâncias da MGridColumn.
     */
    protected function criarGrid($colunas, $mostrarCheckBoxes=TRUE, $chaves)
    {
        // Obtém SQL da busca.
        $sqlConsulta = $this->obterObjetoConsulta();
        
        $this->grid = new MSpecialGrid(NULL, $colunas, 'bSearchGrid', 15, $mostrarCheckBoxes, $chaves);
        $this->grid->setQuery($sqlConsulta, $this->modulo);
        for($a = 1; $a<count($colunas);$a++)
        {
            $this->grid->setColumnAttr($a, 'width', "30%");
        }
        $this->grid->setRowMethod(__CLASS__, 'myRowMethod');
        
        // Cria o menu de contexto.
        $this->criarMenuDeContexto();

        parent::addField(new MDiv(self::GRID_DIV, array( $this->grid, $this->menu )));
    }
    
    public function myRowMethod($i, $row, $actions, $columns)
    {
        $MIOLO = MIOLO::getInstance();
        foreach ($actions as $cod => $act)
        {
            $href = explode(' ',$act->href);

            $act->href = "mspecialgrid.uncheckAll('bSearchGrid');"." document.getElementById('selectbSearchGrid[".$i."]').click(); ".SAGU::NVL($href[2],$href[0]);
        }        
    }
    
    /**
     * @return MSQL
     */
    protected function obterObjetoConsulta()
    {
        return $this->tipo->buscarNaReferencia($this->colunas, $this->getData());
    }
    
    /**
     * Método reescrito para não adicionar MBaseGroup nos filtros de busca.
     * 
     * @param array $filtros Vetor de campos de filtros.
     */
    protected function adicionarFiltros($filtros)
    {
        $filtros[] = $this->obterBotoes();

        $this->addFields($filtros);
    }
    
    private function adicionarEventoEnter($campo)
    {
        // Buscar quando teclar 'Enter'
        $eventoBusca = MUtil::getAjaxAction('botaoBuscar_click');
        $this->page->onload("
        handleEnterSearch = 
            dojo.connect(dojo.byId('$campo'),
            'onkeypress',
            function (event) {
            if ( event.keyCode == dojo.keys.ENTER )
            {
                event.preventDefault();
                dojo.disconnect(handleEnterSearch);
                {$eventoBusca};
            }
        });");
    }
}

?>