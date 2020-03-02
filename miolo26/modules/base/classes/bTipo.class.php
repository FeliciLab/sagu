<?php

/**
 * <--- Copyright 2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
 *
 * O Base é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 *  Classe que manipula e representa uma tabela da base de dados.
 *          
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Class created on 25/06/2012
 */
class bTipo
{
    /**
     * @var Matriz Nomes das colunas e chave primária da tabela.
     */
    protected $colunas;

    /**
     * @var string Nome da tabela
     */
    protected $tabela;

    /**
     * @var string $funcao Função do formulário. 
     */
    protected $funcao;

    /**
     * @var array $estruturaTabela Vetor com a estrutura da tabela.
     */
    protected $estruturaTabela = array( );

    /**
     * @var array $chavesPrimarias Vetor com as chaves primárias da tabela. 
     */
    protected $chavesPrimarias = array( );

    /**
     * @var string $esquema Esquema em que a tabela se encontra. 
     */
    protected $esquema = 'public';

    /**
     * @var array $tiposRelacionados Vetor com os nomes dos tipos que são relcionados ao tipo principal.
     */
    protected $tiposRelacionados = array( );

    /**
     * @var array $dadosTiposRelacionados Vetor com os tipos relacionados.
     */
    protected $dadosTiposRelacionados = array( );

    /**
     * @var string $status Status em que se encontra o tipo. 
     */
    public $status;
    
    /**
     * @var String Comentário da tabela. 
     */
    private $comentarioDaTabela;
    
    /**
     * @var String Coluna descritiva da tabela
     */
    protected $colunaDescritiva;
    
    /**
     * @var Array Valores das colunas estrangeiras
     */
    private $valorDaColunaEstrangeira;
    
    /**
     * @var Array Relacionamentos desta tabela
     */
    private $relacionamentos;
    
    /**
     * @var String Ordenação padrão da tabela
     */
    protected $ordenacaoPadrao;
    
    /**
     * @var String Máscara padrão dos campos de timestamp
     */
    protected $mascaraTimeStamp = 'DD/MM/YYYY HH24:MI';

    /**
     * Construtor da classe.
     *
     * @param FormData $dados Dados do formulário.
     * @param boolean $popular Indica se a classe deve ser populada com os dados da base.
     */
    public function __construct($chave = null)
    {
        if ( !$chave )
        {
            $chave = get_class($this);
        }
        
        // Ticket #38602 - ajustando esquema
        $esquema = bCatalogo::listarEsquemas($chave);
        $this->esquema = (strlen($esquema[0][0]) > 0 ) ? $esquema[0][0] : 'public';
        
        $this->tabela = $chave;
        $this->definirEstruturaDaTabela();
        $this->definirComentarioDaTabela();
        $this->definirRelacionamentos();
    }
    
    /**
     * Retorna o nome da tabela que a grid está utilizando
     * 
     * @return string
     */
    public function getTabela()
    {
        return $this->tabela; 
    }
    
    /**
     *
     * @return string
     */
    public function obterValorColuna($coluna)
    {
        return $this->$coluna;
    }
    
    public function definirValorColuna($coluna, $valor)
    {
        $this->$coluna = $valor;
    }
    
    /**
     *
     * @return boolean
     */
    public function valorFoiDefinido($coluna)
    {
        return isset($this->$coluna);
    }

    /**
     * Método para buscar os registros na base de dados.
     * 
     * @param stdClass $filtros Filtros para condição da busca.
     * @param string $colunas Colunas em que se deseja efetuar a busca.
     * @return array de objetos Vetor com os resultados. 
     */
    public function buscar($filtros, $colunas = NULL)
    {
        $sql = $this->obterConsulta($filtros, $colunas);

        // Efetua a busca na base de dados.
        $resultado = bBaseDeDados::obterInstancia()->_db->query($sql, NULL, NULL, PostgresQuery::FETCH_OBJ);
        
        return is_array($resultado) ? $resultado : $resultado->result;
    }
    
    private function obterObjetoDeConsultaDescritiva($filtros=NULL, $colunas=NULL)
    {
        if ( !$colunas )
        {
            $colunaDescritiva = $this->obterColunaDescritiva() ? $this->obterColunaDescritiva() : $this->colunas[1];
            $colunas = $this->colunas[0] . ','. $colunaDescritiva;
        }
        
        $sql = $this->obterConsulta($filtros, $colunas);
        
        return $sql;
    }
    
    /**
     * Método para buscar resultados para um campo de seleção.
     * 
     * @param stdClass $filtros Filtros para condição da busca.
     * @param string $colunas Colunas em que se deseja efetuar a busca.
     * @return array Matriz com valores.  
     */
    public function buscarParaSelection($filtros=NULL, $colunas=NULL)
    {
        $sql = $this->obterObjetoDeConsultaDescritiva($filtros, $colunas);
        
        $query = bBaseDeDados::obterInstancia()->_db->query($sql);
        
        $retorno = is_array($query) ? $query : $query->result;
        
        // Caso não retorne registros, adiciona uma mensagem para aparecer no formulário.
        if ( !is_array($retorno) )
        {
            $retorno = array(_M('Registros não encontrados'));
        }
        
        return $retorno;
    }
    
    /**
     * Método para buscar resultados para um campo de seleção.
     * 
     * @param stdClass $filtros Filtros para condição da busca.
     * @param array $colunas Colunas em que se deseja efetuar a busca.
     * @return array Matriz com valores.  
     */
    public function buscarParaEscolha($filtro=NULL, $colunas=NULL, $limit=NULL)
    {
        $colunaDescritiva = $this->obterColunaDescritiva();
        
        if ( !$colunas )
        {
            if ( strlen($colunaDescritiva) > 0 )
            {
                $colunas = array($colunaDescritiva, $this->colunas[0]);
            }
            else
            {
                $colunas = array($this->colunas[1], $this->colunas[0]);
            }
        }
        else
        {
            if ( strlen($colunaDescritiva) > 0 )
            {
                $colunas = array($colunaDescritiva, $colunas[0]);
            }
            else
            {
                $colunas = array($colunas[1], $colunas[0]);
            }
        }
        
        $filtros = new stdClass();
        $filtros->{$colunas[0]} = $filtro;

        $colunasBusca = implode(',', $colunas);

        $sql = $this->obterConsulta($filtros, $colunasBusca, $limit);
        $query = bBaseDeDados::obterInstancia()->_db->query($sql);

        $retorno = is_array($query) ? $query : $query->result;

        return $retorno;
    }
    
    /**
     * Método para buscar resultados para um campo de seleção.
     * 
     * @param stdClass $filtros Filtros para condição da busca.
     * @param array $colunas Colunas em que se deseja efetuar a busca.
     * @return array Matriz com valores.  
     */
    public function buscarParaAutoCompletarEscolha($codigo=NULL, $colunas=NULL)
    {
        $retorno = NULL;
        
        if ( strlen($codigo) )
        {
            if ( !$colunas )
            {
                $colunas = array($this->colunas[0], $this->colunas[1]);
            }

            $filtros = new stdClass();
            $filtros->{$colunas[0]} = $codigo;
            
            $colunasBusca = implode(',', $colunas);

            $sql = $this->obterConsulta($filtros, $colunasBusca);

            $query = bBaseDeDados::obterInstancia()->_db->query($sql);
            
            $retorno = is_array($query) ? $query : $query->result;
        }
        
        return $retorno;
    }

    /**
     * Método para definir os valores dos atributos da classe.
     * 
     * @param FormData $dados Dados do formulário.
     */
    public function definir($dados)
    {
        foreach ( $this->colunas as $coluna )
        {
            if ( isset($dados->$coluna) )
            {
                $this->$coluna = $dados->$coluna;
            }
        }

        // Define o status caso tiver.
        if ( strlen($dados->dataStatus) )
        {
            $this->status = $dados->dataStatus;
        }

        // Define os dados dos tipos relacionados.
        if ( is_array($this->tiposRelacionados) )
        {
            // Percorre os tipos relacionados para verificar se existem dados para ele nos dados vindos do formulário.
            foreach ( $this->tiposRelacionados as $tipoRelacionado )
            {
                $dados = (array) $dados;

                // Verifica se o tipo relacionado se encontra nos dados.
                if ( in_array($tipoRelacionado, array_keys($dados)) )
                {
                    // Instância os tipos relacionados.
                    foreach ( $dados[$tipoRelacionado] as $valores )
                    {
                        $tipo = self::instanciarTipo($tipoRelacionado);
                        $tipo->definir($valores);
                        $this->dadosTiposRelacionados[$tipoRelacionado][] = $tipo;
                    }
                }
            }
        }
    }

    /**
     * Método público para definir função do type.
     * 
     * @param string $funcao Função do type, pode ser inserir, atualizar ou remover. 
     */
    public function definirFuncao($funcao)
    {
        $this->funcao = $funcao;
    }
    
    /**
     * Obtém a função do tipo.
     * 
     * @return String Função do tipo. 
     */
    public function obterFuncao()
    {
        return $funcao;
    }

    /**
     * Método que edita o registro no banco.
     *
     * @return boolean Retorna positivo no caso de sucesso.
     */
    public function editar()
    {
        $sql = new MSQL();
        $colunas = $this->obterColunasInsercaoOuEdicao();

        $sql->setColumns(implode(',', $colunas));
        $sql->setTables($this->tabela);

        $parametros = array( );

        // Define os dados.
        foreach ( $colunas as $coluna )
        {
            $parametros[] = $this->$coluna;
        }

        // Define a condição.
        foreach ( $this->chavesPrimarias as $coluna )
        {
            $sql->setWhere("$coluna = ?");
            $parametros[] = $this->$coluna;
        }

        $retorno = bBaseDeDados::executar($sql->update($parametros));

        // Edita os dados dos tipos relacionados.
        if ( $retorno && is_array($this->dadosTiposRelacionados) )
        {
            foreach ( $this->dadosTiposRelacionados as $nomeTipo )
            {
                foreach ( $nomeTipo as $tipo )
                {
                    $tipo instanceof bTipo;
                    
                    $dados = $tipo->obter();

                    // Define a chave primária faltante.
                    foreach ( $this->chavesPrimarias as $chave )
                    {
                        $dados->$chave = $this->$chave;
                    }

                    $tipo->definir($dados);

                    // Insere o dado relacionado.
                    if ( $tipo->status == MSubDetail::STATUS_ADD )
                    {
                        $tipo->inserir();
                    }
                    elseif ( $tipo->status == MSubDetail::STATUS_EDIT )
                    {
                        // Edita o dado relacionado.
                        $tipo->editar();
                    }
                    elseif ( $tipo->status == MSubDetail::STATUS_REMOVE )
                    {
                        // Exclui o dado relacionado.
                        $tipo->excluir();
                    }
                }
            }
        }

        return $retorno;
    }

    /**
     * Método que exclui o registro do banco.
     *
     * @return boolean Retorna positivo no caso de sucesso.
     */
    public function excluir()
    {
        $exclusaoRelacionado = array( );
        $filtros = new stdClass();

        foreach ( $this->chavesPrimarias as $coluna )
        {
            $filtros->$coluna = $this->$coluna;
        }
        
        if ( is_array($this->tiposRelacionados) )
        {
            foreach ( $this->tiposRelacionados as $tabela )
            {
                $exclusao[] = self::excluirDadosDaTabela($tabela, $filtros);
            }
        }

        if ( !in_array(FALSE, $exclusaoRelacionado) )
        {
            $sql = new MSQL();
            $sql->setTables($this->tabela);

            $parametros = array( );

            foreach ( $filtros as $coluna => $filtro )
            {
                $sql->setWhere("$coluna = ?");
                $parametros[] = $filtro;
            }

            return bBaseDeDados::executar($sql->delete($parametros));
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Método protegido e estático para excluir dados de qualquer tabela. Muito útil para excluir dados dos tipos relacionados.
     * 
     * @param string $tabela Nome da tabela que será excluída.
     * @param stdClass $condicao Objeto com os campos que são condição na exclusão.
     * @return boolean Retorna positivo caso tenha excluído os dados.
     */
    protected static function excluirDadosDaTabela($tabela, stdClass $condicao)
    {
        $sql = new MSQL();
        $sql->setTables($tabela);

        $parametros = array( );

        foreach ( $condicao as $campo => $valor )
        {
            $sql->setWhere("$campo = ?");
            $parametros[] = $valor;
        }

        if ( !count($parametros) )
        {
            throw new Exception(_M('Informe o menos uma condição para a exclusão.'));
            return FALSE;
        }

        return bBaseDeDados::executar($sql->delete($parametros));
    }

    /**
     * Método que insere o registro na base dados, testando se é chave sequêncial ou não sequencial.
     * 
     * @return boolean Retorna positivo no caso de sucesso.
     */
    public function inserir()
    {
        // Testa se o type possui mais de uma chave primária.
        if ( strlen($this->chavesPrimarias['sequencial'])  )
        {
            // Caso seja apenas uma chave, faz uma inserção de chave sequêncial.
            $retorno = $this->inserirChaveSequencial();
        }
        else
        {
            // Caso tenha, faz uma inserção de chave composta.
            $retorno = $this->inserirChaveNaoSequencial();
        }

        // Inserir os dados relacionados.
        if ( $retorno && is_array($this->tiposRelacionados) )
        {
            // Percorre os tipos relacionados para verificar se existem dados para ele nos dados vindos do formulário.
            foreach ( $this->tiposRelacionados as $tipoRelacionado )
            {
                foreach ( $this->dadosTiposRelacionados[$tipoRelacionado] as $tipo )
                {
                    // Somente insere o dado, caso o status seja para inserir.
                    if ( $tipo->status == MSubDetail::STATUS_ADD )
                    {
                        $dados = $tipo->obter();
                        
                        // Define a chave primária faltante.
                        foreach ( $this->chavesPrimarias as $chave )
                        {
                            $dados->$chave = $this->$chave;
                        }

                        $tipo->definir($dados);
                        $tipo->inserir();
                    }
                }
            }
        }

        return $retorno;
    }

    /**
     * Método que insere o registro no banco com chave sequencial.
     *
     * @return boolean Retorna positivo no caso de sucesso.
     */
    private function inserirChaveSequencial()
    {
        $sql = new MSQL();
        $colunasSemChavePrimaria = $this->obterColunasInsercaoOuEdicao();

        $sql->setTables($this->tabela);
        $sql->setColumns(implode(',', $colunasSemChavePrimaria));

        $parametros = array( );

        // Colunas que não são chave primária.
        foreach ( $colunasSemChavePrimaria as $coluna )
        {
            $parametros[] = $this->$coluna;
        }

        $retorno = bBaseDeDados::inserir($sql, $parametros);
        
        if ( is_array($retorno) )
        {
            $inherits = strtolower(bCatalogo::obterHeranca($this->esquema, $this->tabela)->table);
            
            // Se estende a baslog, é necessário desconsiderar os campos estendidos
            $contador = $inherits === "baslog" ? 3 : 0;

            foreach ( $this->colunas as $coluna )
            {
                $this->$coluna = $retorno[$contador];
                $contador++;
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Método que insere o registro no banco com chave composta.
     *
     * @return boolean Retorna positivo no caso de sucesso.
     */
    private function inserirChaveNaoSequencial()
    {
        $sql = new MSQL();
        $sql->setTables($this->tabela);
        $sql->setColumns(implode(',', $this->colunas));

        $parametros = array( );

        foreach ( $this->colunas as $coluna )
        {
            $parametros[] = $this->$coluna;
        }

        return bBaseDeDados::executar($sql->insert($parametros));
    }

    /**
     * Obter dados do tipo.
     * 
     * @return stdClass Objeto com dados do tipe. 
     */
    public function obter()
    {
        return $this;
    }

    /**
     * Método público para obter as chaves primárias do tabela.
     * 
     * @return array Vetor com as chaves primárias da tabela. 
     */
    public function obterChavesPrimarias()
    {
        return $this->chavesPrimarias;
    }
    
    /**
     * Retorna valores das chaves primarias
     *
     * @return array
     */
    public function obterValorChavesPrimarias()
    {
        $valores = array();
        
        foreach ( $this->obterChavesPrimarias() as $chave )
        {
            $valores[$chave] = $this->obterValorColuna($chave);
        }
        
        return $valores;
    }
    
    /**
     * Retorna TRUE caso todas as chaves primarias foram populadas com valor.
     *
     * @return boolean
     */
    public function foiPopuladoChavesPrimarias()
    {
        return count(array_filter($this->obterValorChavesPrimarias())) == count($this->obterChavesPrimarias());
    }

    /**
     * Método protegido que obtém as colunas da tabela que não são chave primária.
     * 
     * @return array Vetor com as colunsa que não são chave primária. 
     */
    public function obterColunasSemChavePrimaria()
    {
        $colunas = array( );

        foreach ( $this->colunas as $coluna )
        {
            if ( !in_array($coluna, $this->chavesPrimarias) )
            {
                $colunas[] = $coluna;
            }
        }

        return $colunas;
    }
    
    /**
     * Obtem colunas que devem entrar na instrucao UPDATE ou INSERT da consulta.
     * Exclui colunas que nao tenham valor definido no Tipo (NULL conta como sendo valor definido).
     * 
     * @return array
     */
    public function obterColunasInsercaoOuEdicao()
    {
        $colunas = $this->obterColunasSemChavePrimaria();
        
        foreach ( $colunas as $chave => $coluna )
        {
            if ( !isset($this->$coluna) )
            {
                unset($colunas[$chave]);
            }
        }
        
        return $colunas;
    }

    /**
     * Obtém consulta SQL, usado na busca, busca para lookup e Grid de dados.
     *
     * @param FormData $filtros Valores a serem usados como filtros.
     * @param string Colunas da tabela separadas por vírgula.
     * @return string Consulta SQL.
     */
    public function obterConsulta($filtros = NULL, $colunas=NULL, $limit=NULL)
    {
        $sql = $this->obterObjetoConsulta($filtros, $colunas, $limit);

        return $sql->select();
    }
    
    /**
     * Permite sobrescrever o objeto padrao de consulta do Tipo.
     * 
     * @see Veja o metodo obterConsulta()
     * 
     * @return MSQL 
     */
    public function obterObjetoConsulta($filtros = NULL, $colunas=NULL, $limit=NULL)
    {
        $sql = new MSQL();
        $sql->setTables('ONLY ' . $this->esquema . '.' . $this->tabela);
        
        $colunas = $this->formatarColunas($colunas);

        $sql->setColumns(implode(',', $colunas));

        $parametros = array( );

        if ( is_object($filtros) )
        {
            foreach ( $filtros as $idFiltro => $filtro )
            {
                $tipoColuna = $this->estruturaTabela[$idFiltro]->tipo;

                if ( strlen($tipoColuna) && strlen($filtro) )
                {
                    switch ( $tipoColuna )
                    {
                        case bInfoColuna::TIPO_TEXTO_LONGO:
                            $sql->setWhere("unaccent(lower($idFiltro)) LIKE unaccent(lower(?))");
                            $parametros[] = '%' . $filtro . '%';
                            break;

                        case bInfoColuna::TIPO_TEXTO:
                            $sql->setWhere("unaccent(lower($idFiltro)) LIKE unaccent(lower(?))");
                            $parametros[] = $filtro . '%';
                            break;
                        
                        case bInfoColuna::TIPO_CHAR:
                            $sql->setWhere("unaccent(lower($idFiltro)) LIKE unaccent(lower(?))");
                            $parametros[] = $filtro . '%';
                            break;
                        
                        default:
                            $sql->setWhere("$idFiltro = ?");
                            $parametros[] = $filtro;
                            break;
                    }
                }
            }
        }
        
        if ( strlen($this->ordenacaoPadrao) > 0 )
        {
            $sql->setOrderBy($this->ordenacaoPadrao);
        }
        
        $sql->setParameters($parametros);
        
        if ( $limit )
        {
            $sql->setLimit($limit);
        }
        
        return $sql;
    }
    
    /**
     * Função para adicionar máscaras às colunas, conforme o seu tipo.
     * 
     * @param Array $colunas
     * @return Array
     */
    private function formatarColunas($colunas)
    {
        if ( strlen($colunas) )
        {
            $columns = explode(',', $colunas);
        }
        else
        {
            $columns = $this->colunas;
        }
        
        foreach($columns as $key => $column)
        {
            $dadosDaColuna = bCatalogo::buscarDadosDaColuna($column, $this->tabela);
            
            // Se a coluna for timestamp, aplica a máscara definida no tipo.
            if ( $dadosDaColuna->tipo == bInfoColuna::TIPO_TIMESTAMP )
            {
                $columns[$key] = "to_char($column, '{$this->mascaraTimeStamp}') as $column";
            }
        }
        
        return $columns;
    }

    /**
     * Método que preenche o objeto com os dados do banco.
     */
    public function popular()
    {
        $sql = new MSQL();
        $colunasSemChavePrimaria = $this->obterColunasSemChavePrimaria();

        $sql->setTables($this->tabela);
        $sql->setColumns(implode(',', $colunasSemChavePrimaria));

        $parametros = array( );

        foreach ( $this->chavesPrimarias as $chavePrimaria )
        {
            $sql->setWhere("$chavePrimaria = ?");
            $parametros[] = $this->$chavePrimaria;
        }

        $consulta = bBaseDeDados::consultar($sql, $parametros);

        $contador = 0;

        foreach ( $colunasSemChavePrimaria as $coluna )
        {
            $this->$coluna = $consulta[0][$contador];
            $contador++;
        }

        // Popula dados relacionados.
        if ( is_array($this->tiposRelacionados) )
        {
            foreach ( $this->tiposRelacionados as $tipo )
            {
                // Monta o filtro através
                $filtro = new stdClass();

                foreach ( $this->chavesPrimarias as $chave )
                {
                    $filtro->$chave = $this->$chave;
                }
                
                // Obtém uma instância do tipo para poder buscar todos os tipos relacionados.
                $intanciaTipo = bTipo::instanciarTipo($tipo);
                $resultado = $intanciaTipo->buscar($filtro);
                
                if ( is_array($resultado) )
                {
                    foreach ( $resultado as $valor )
                    {
                        $tipoObjeto = bTipo::instanciarTipo($tipo);
                        
                        $relacionamentos = bCatalogo::obterRelacionamentos($tipo);
                        foreach($relacionamentos as $relacionamento)
                        {
                            $tipoRelacionado = self::instanciarTipo($relacionamento->tabela_ref);
                            
                            if ( strlen($tipoRelacionado->obterColunaDescritiva()) > 0 )
                            {
                                $descricaoItem = $tipoRelacionado->obterDescricaoDoItem($relacionamento->atributo_ref, $valor->{$relacionamento->atributo});
                                $tipoObjeto->adicionarValorDaColunaEstrangeira($relacionamento->atributo, $descricaoItem);
                            }
                        }                        
                        
                        $tipoObjeto->definir($valor);

                        // Adicionando chave da descrição dos relacionamentos
                        foreach ( $tipoObjeto->estruturaTabela as $rel )
                        {                            
                            $idDescription = $rel->nome . 'Descricao';
                            $tipoObjeto->$idDescription = $tipoObjeto->valorDaColunaEstrangeira[$rel->nome];
                        }
                        
                        $this->dadosTiposRelacionados[$tipo][] = $tipoObjeto;
                    }
                }
            }
        }
    }

    /**
     * Método que salva o registro no banco, editando ou inserindo conforme o caso.
     *
     * @return boolean Retorna positivo no caso de sucesso.
     */
    public function salvar()
    {
        $resultado = FALSE;
        
        if ( $this->funcao == FUNCAO_INSERIR || !$this->foiPopuladoChavesPrimarias() )
        {
            $resultado = $this->inserir();
        }
        else
        {
            $resultado = $this->editar();
        }
        
        return $resultado;
    }

    /**
     * Método que define a estrutura dos campos da tabela que estão mapeados no tipo.
     */
    private function definirEstruturaDaTabela()
    {
        // Obtém colunas que estão na tabela.
        $colunas = bCatalogo::obterColunasDaTabela($this->esquema, $this->tabela);
        if ( is_array($colunas) )
        {
            foreach ( $colunas as $coluna => $dados )
            {
                if ( $dados->restricao == 'p' )
                {
                    if ( substr($dados->valorPadrao, 0, 7) == 'nextval' )
                    {
                        $this->chavesPrimarias['sequencial'] = $coluna;
                    }
                    else
                    {
                        $this->chavesPrimarias[] = $coluna;
                    }
                }
            }
            
            // Define as colunas da tabela.
            $this->colunas = array_keys($colunas);
        }
        else
        {
            throw new Exception(_M('A tabela especificada não existe.'));
        }

        // Define a estrutura da tabela.
        $this->estruturaTabela = $colunas;
    }
    
    /**
     * Método publico para obter a estrutura da tabela.
     * 
     * @return array Vetor com os campos da tabela. 
     */
    public function obterEstruturaDaTabela()
    {
        return $this->estruturaTabela;
    }
    
    /**
     * @return string
     */
    public function obterTipoColuna($coluna)
    {
        $estrutura = $this->obterEstruturaDaTabela();
        
        return $estrutura[$coluna]->tipo;
    }
    
    /**
     * Retorna se tipo de coluna e numerico (int, bigint, numeric..).
     * 
     * @return boolean
     */
    public function colunaTipoNumerico($coluna)
    {
        return in_array($this->obterTipoColuna($coluna), array(bInfoColuna::TIPO_INTEIRO, bInfoColuna::TIPO_INTEIRO_LONGO, bInfoColuna::TIPO_NUMERIC));
    }
    
    /**
     * Método público e estático que instância um tipo ou um objeto bTipo com dados da tabela desejada.
     * 
     * @param string $nomeDoTipo Nome/chave do tipo desejado.
     * @return bTipo Instância do objeto bTipo.
     */
    public static function instanciarTipo($nomeDoTipo, $modulo=NULL)
    {
        if ( !strlen($nomeDoTipo) )
        {
            return;
        }
        
        $MIOLO = MIOLO::getInstance();
        
        if ( !$modulo )
        {
            $modulo = MIOLO::getCurrentModule();
        }
        
        $tipo = $MIOLO->getModulePath($modulo, 'tipos/' . $nomeDoTipo . '.class.php');
        
        // Verifica se o código do tipo existe.
        if ( file_exists($tipo) )
        {
            $MIOLO->uses("tipos/$nomeDoTipo.class.php", $modulo);
            $tipo = new $nomeDoTipo($nomeDoTipo);
        }
        else
        {
            // Instância um tipo dinamicamente.
            $tipo = new bTipo($nomeDoTipo);
        }

        return $tipo;
    }
   
    /**
     * Método público para adicionar um tipo relacionado.
     * 
     * @param string $tipoRelacionado Nome do tipo que está relacionado.
     */
    public function adicionarTipoRelacionado($tipoRelacionado)
    {
        $this->tiposRelacionados[] = $tipoRelacionado;
    }
    
    /**
     * Método público para remover um tipo relacionado.
     * 
     * @param int $indice Posição onde está o tipo relacionado que será removido.
     */
    public function removerTipoRelacionado($indice)
    {
        unset($this->tiposRelacionados[$indice]);
    }
    
    /**
     * Método público para definir um tipo relacionado.
     * 
     * @param array $tiposRelacionados Vetor com os tipos relacionados.
     */
    public function definirTiposRelacionados(array $tiposRelacionados)
    {
        $this->tiposRelacionados = $tiposRelacionados;
    }
    
    /**
     * Método público para obter os tipos relacionados.
     * 
     * @return array Vetor com os tipos relacionados. 
     */
    public function obterTiposRelacionados()
    {
        return $this->tiposRelacionados;
    }
    
    /**
     * Define o comentário da tabela do tipo.
     * 
     * @param String $comentario Comentário da tabela.
     */
    public function definirComentarioDaTabela($comentario=NULL)
    {
        if ( !strlen($comentario) )
        {
            $comentario = bCatalogo::obterComentarioDaTabela($this->tabela);
        }
        
        $this->comentarioDaTabela = $comentario;
    }
    
    /**
     * Obtém o comentário da tabela.
     * 
     * @return String comentário da tabela. 
     */
    public function obterComentarioDaTabela()
    {
        return $this->comentarioDaTabela ? $this->comentarioDaTabela : ucfirst($this->tabela);
    }
    
    /**
     * Define os dados dos tipos relacionados.
     * 
     * @param array $dadosTiposRelacionados Vetor com os dados de tipos relacionados.
     */
    public function definirDadosTiposRelacionados($dadosTiposRelacionados)
    {
        $this->dadosTiposRelacionados = $dadosTiposRelacionados;
    }
    
    /**
     * Obtém os dados dos tipos relacionados.
     * 
     * @return array Vetor com os dados de tipos relacionados. 
     */
    public function obterDadosTiposRelacionados()
    {
        return $this->dadosTiposRelacionados;
    }
    
    /**
     * Método público para validar os dados do tipo.
     * 
     * @return boolean Retorna positivo caso tenha passado na validação.
     */
    public function validar()
    {
        if ( is_array($this->estruturaTabela) )
        {
            $camposInvalidos = array();
            
            foreach ( $this->estruturaTabela as $campo => $estrutura )
            {
                $estrutura instanceof bInfoColuna;
                
                if ( $this->chavesPrimarias['sequencial'] == $campo )
                {
                    continue;
                }
                
                // Verifica se campo obrigatório foi preenchido.
                if ( ($estrutura->obrigatorio == DB_TRUE) && (strlen($this->$campo) == 0) && ( $estrutura->valorPadrao == DB_FALSE ) )
                {
                    $camposInvalidos[$campo] = _M('Este campo é obrigatório');
                }
                
                // Verifica se a quantidade de caracteres excede o tamanho do campo na base de dados.
                if ( $estrutura->tamanho && (strlen($this->$campo) > $estrutura->tamanho) )
                {
                    $camposInvalidos[$campo] = _M('O campo excede o número de caracteres permitido', NULL, $estrutura->titulo);
                }
            }
            
            if ( count($camposInvalidos) )
            {
                throw new MValidationException($camposInvalidos, array_keys($camposInvalidos));
            }
            
        }
        
        return TRUE;
    }

    /**
     * Realiza consulta baseada nas colunas de referência.
     *
     * @param array $colunas Vetor de objetos SInfoColuna.
     * @param array $filtros Filtros a serem aplicados na consulta.
     * @return MSQL Objeto MSQL do resultado da consulta.
     */
    public function buscarNaReferencia($colunas, $valoresFiltrados=array())
    {
        $parametros = array();
        $condicao = '';
        $joins = array();

        $colunasString = array();
        $tabelas = array();
        $tabelasString = '';
        $esquemaAnterior = '';
        $tabelaAnterior = '';
        $filtros = new stdClass();
        $filtros->generico = $valoresFiltrados->generico;
        
        $orderBy = $this->ordenacaoPadrao;
        // $chave é esquema.tabela.coluna
        foreach ( $colunas as $chave => $coluna )
        {
            $coluna instanceof bInfoColuna;
            
            if ( $orderBy == NULL || strlen($orderBy) == 0 )
            {
                $orderBy = $coluna->ordenar;
            }
            
            // Ajusta os filtros vindos do formulário.
            if ( $coluna->filtravel )
            {
                $filtros->{$coluna->campo} = $valoresFiltrados->{$coluna->campo};
            }
            
            if ( !in_array("$coluna->esquema.$coluna->tabela", $tabelas) )
            {
                $tabelas[] = "$coluna->esquema.$coluna->tabela";
                
                $colunasString[] = "$coluna->esquema.$coluna->tabela.$coluna->nome";
                
                if ( $tabelaAnterior == '' )
                {
                    $esquemaAnterior = $coluna->esquema;
                    $tabelaAnterior = $coluna->tabela;
                    
                    // Obtém dados da tabela anterior.
                    $dadosDaTabelaAnterior = bCatalogo::buscarChavesEstrangeirasDaTabela($tabelaAnterior, $esquemaAnterior);
                }

                if ( $tabelasString == '' )
                {
                    $tabelasString .= "$coluna->esquema.$coluna->tabela ";
                }
                else
                {
                    $dadosDaTabela = bCatalogo::buscarChavesPrimariasDaTabela($coluna->tabela, $coluna->esquema);
                    
                    foreach ( $dadosDaTabela as $pk )
                    {
                        list($pkColuna, $pkTipo) = $pk;

                        foreach ( $dadosDaTabelaAnterior as $fk )
                        {
                            list($fkFromSchema, $fkFromTable, $fkFromColumn, $fkToSchema, $fkToTable, $fkToColumn, $fkObrigatorio) = $fk;
//        var_dump($coluna->esquema.' - '.$coluna->tabela );
                            
                            $join = $fkToSchema.$fkToTable;
                            
                            if ( $fkToColumn == $pkColuna && !in_array($join, $joins) )
                            {  
                                $join = $fkObrigatorio == DB_TRUE ? 'INNER' : 'LEFT';
                                $tabelasString .= " $join JOIN ONLY $fkToSchema.$fkToTable ON $fkToSchema.$fkToTable.$fkToColumn = $fkFromSchema.$fkFromTable.$fkFromColumn";
                                
                                $joins[] = $fkToSchema.$fkToTable;
                            }
                        }
                    }

                }
            }
            else
            {
                $colunasString[] = "$coluna->esquema.$coluna->tabela.$coluna->nome";
            }
        }
        
        $colunasString = implode(',', $colunasString);

        $msql = new MSQL();
        $msql->setTables($tabelasString);
        $msql->setColumns($colunasString);
        if ( strlen($orderBy) > 0 )
        {
            $msql->setOrderBy($orderBy);
        }

        $fazerSubCondicao = false;
        foreach ($filtros as $filtro )
        {
            if ( strlen($filtro) > 0 )
            {
                $fazerSubCondicao = true;
                break;
            }
        }
        
        if ( $fazerSubCondicao )
        {
            $msql->startSubCondition();
        }
        
        foreach ( $filtros as $chave => $valor )
        {
            if ( $chave == 'generico' )
            {
                continue;
            }

            $chave = str_replace('__', '.', $chave);
            
            switch( $colunas[$chave]->tipo )
            {
                case bInfoColuna::TIPO_TEXTO:
                case bInfoColuna::TIPO_TEXTO_LONGO:
                    
                    if ( strlen($valor) )
                    {
                        $msql->setWhere("UNACCENT($chave) ILIKE UNACCENT(?)");
                        $parametros[] = $valor . '%';
                    }

                    // Busca pelo valor do campo genérico.
                    if ( strlen($filtros->generico) )
                    {
                        $msql->setWhereOr("UNACCENT($chave) ILIKE UNACCENT(?)");
                        $parametros[] = $filtros->generico . '%';
                    }

                    break;
                    
                case bInfoColuna::TIPO_LISTA:
                    
                    if (strlen($valor) )
                    {
                        $relacionamentos = bCatalogo::obterRelacionamentos($this->tabela, $colunas[$chave]->tabela);
                        if ( $relacionamentos[0] )
                        {
                            $chaveEstrangeira = "{$relacionamentos[0]->esquema}.{$relacionamentos[0]->tabela_ref}.{$relacionamentos[0]->atributo_ref}";
                            $msql->setWhere("$chaveEstrangeira = ?");
                            $parametros[] = $valor;
                        }
                        else
                        {
                            $msql->setWhere("$chave = ?");
                            $parametros[] = $valor;
                        }
                    }
                    
                    break;
                    
                case bInfoColuna::TIPO_INTEIRO:
                case bInfoColuna::TIPO_INTEIRO_LONGO:
                case bInfoColuna::TIPO_NUMERIC:
                case bInfoColuna::TIPO_DECIMAL:
                    
                    if ( strlen($valor) && is_numeric($valor) )
                    {
                        $msql->setWhere("$chave = ?");
                        $parametros[] = $valor;
                    }
                    
                    break;

                default:
                    
                    if ( strlen($valor) )
                    {
                        $msql->setWhere("$chave = ?");
                        $parametros[] = $valor;
                    }
                    
                    // Busca pelo valor do campo genérico.
                    if ( strlen($filtros->generico) )
                    {
                        $msql->setWhereOr("UNACCENT($chave::varchar) ILIKE (?)");
                        $parametros[] = $filtros->generico . '%';
                    }
                    break;
            }
        }
        
        if ( $fazerSubCondicao )
        {
            // Previne erros se não houver nenhum filtro. Não remover.
            if ( count($parametros) == 0 )
            {
                $msql->setWhere('1=1');
            }
            $msql->endSubCondition();
        }
        
        $msql->setParameters($parametros);
        return $msql;
    }
    
    /**
     * Retorna o id incremental da ultima insercao
     *
     * @return int
     */
    public function obterUltimoIdInserido()
    {
        return bBaseDeDados::obterUltimoIdInserido($this->tabela);
    }
    
    public function obterColunaDescritiva()
    {
        $colunaDescritiva = $this->colunaDescritiva;
        
        if ( strlen($colunaDescritiva) == 0 )
        {
            $nomesPossiveis = array('nome', 'descricao', 'name', 'description');
            foreach( $this->colunas as $coluna )
            {
                if ( in_array($coluna, $nomesPossiveis) )
                {
                    $colunaDescritiva = $coluna;
                    break;
                }
            }
        }
        
        return $colunaDescritiva;
    }

    public function setColunaDescritiva($colunaDescritiva)
    {
        $this->colunaDescritiva = $colunaDescritiva;
    }
    
    public function adicionarValorDaColunaEstrangeira($colunaEstrangeira, $valor)
    {
        $this->valorDaColunaEstrangeira[$colunaEstrangeira] = $valor;
    }

    public function obterDescricaoDoItem($chavePrimaria, $valor)
    {
        $descricao = null;
        $coluna = $this->obterColunaDescritiva();
        
        if ( strlen($coluna) > 0 && strlen($chavePrimaria) > 0 )
        {
            $sql = new MSQL();
            $sql->setTables($this->tabela);
            $sql->setColumns($coluna);
            $sql->setWhere("$chavePrimaria = ?");
            $sql->addParameter($valor);
            
            $resultado = bBaseDeDados::consultar($sql);
            $descricao = $resultado[0][0];
        }
        
        return $descricao;
    }
    
    public function definirRelacionamentos()
    {
        $this->relacionamentos = bCatalogo::obterRelacionamentos($this->tabela);
    }
    
    public function obterRelacionamentos()
    {
        return $this->relacionamentos;
    }
    
    /**
     * @param array $valoresRestritivos - Esses valores vão restringer a consulta (estão relacionados à chave primária).
     * @return array
     */
    public function obterArrayAssociativo($valoresRestritivos = null)
    {
        $retorno = array();
        $sql = $this->obterObjetoDeConsultaDescritiva();

        if ( is_array($valoresRestritivos) && strlen($valoresRestritivos[0]) > 0 || !(is_array($valoresRestritivos)) )
        {
            if ( is_array($valoresRestritivos) )
            {
                foreach ( $valoresRestritivos as $valor )
                {
                    $whereIn .= "'" . $valor . "',";
                }

                $sql .= ' WHERE ' . current($this->chavesPrimarias) . ' IN (' . rtrim($whereIn, ','). ')';
            }
            
            $query = bBaseDeDados::obterInstancia()->_db->query($sql);

            $query = is_array($query) ? $query : $query->result;

            $retorno = array();
            foreach( $query as $linha )
            {
                $retorno[$linha[0]] = $linha[1];
            }
        }
        
        return $retorno;
    }
    
    public function getOrdenacaoPadrao()
    {
        return $this->ordenacaoPadrao;
    }

    public function setOrdenacaoPadrao(String $ordenacaoPadrao)
    {
        $this->ordenacaoPadrao = $ordenacaoPadrao;
    }
    
    /**
     * Obtem valores relacionados com o tipo.
     * 
     * @param string $coluna
     * @param string $colunaDeComparacao
     * @param string $valorColunaDeComparacao
     * @return array
     */
    public function obterIdsRelacionados($coluna, $colunaDeComparacao, $valorColunaDeComparacao)
    {
        $where = ' WHERE ' . $colunaDeComparacao . ' = ' . $valorColunaDeComparacao;
        if ( is_array($valorColunaDeComparacao) )
        {
            $where = ' WHERE ' . $colunaDeComparacao . ' IN ( ' . implode(', ', $valorColunaDeComparacao) . ')';
        }
        
        $sql = ' SELECT ' . $coluna . ' FROM ONLY ' . $this->esquema . '.' . $this->tabela . $where;

        $result = bBaseDeDados::obterInstancia()->_db->query($sql);
        
        foreach ( $result as $r )
        {
            $return[] = $r[0];
        }
        
        return $return;
    }
}

?>
