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
 * Formulário dinâmico de cadastro
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 22/08/2012
 */

$MIOLO->uses('tipos/cadastroDinamico.class.php', 'base');
class frmDinamico extends bFormCadastro
{
    /**
     * @var array Vetor de objetos com as colunas da tabela.. 
     */
    protected $colunas = null;
    
    /**
     * @var array Vetor com o nome dos campos em ordem. 
     */
    private $ordemDosCampos = null;
    
    public function __construct($parametros, $titulo=NULL)
    {
        parent::__construct(_M($titulo, MIOLO::getCurrentModule()), $parametros);
    }

    /**
     * Método reescrito para definir os campos dinâmicos.
     * 
     * @param boolean $montarCampos Verdadeiro caso seja necessário montar os campos dinâmicos.
     * @param boolean $barraDeFerramentas Verdadeiro caso for necessário montar a barra de ferramentas.
     */
    public function definirCampos($montarCampos=TRUE, $barraDeFerramentas=TRUE)
    {
        parent::definirCampos($barraDeFerramentas);
        
        if ( $montarCampos )
        {
            // Obtém os campos e validadores dor formulário.
            $camposEValidadores = $this->gerarCampos();

            $campos = $camposEValidadores[0];
            
            // Obtém as MSubDetail
            $camposSubDetail = $this->gerarCamposSubDetail();
            
            // Mescla os componentes MSubDetail com o restante dos campos.
            if ( is_array($camposSubDetail) )
            {
                $campos = array_merge($campos, $camposSubDetail);
            }
            
            // Realiza a ordenação dos campos caso necessário.
            if ( $this->ordemDosCampos )
            {
                $camposDesordenados = $campos;
                $campos = array();
                
                foreach ( $this->ordemDosCampos as $nomeCampo )
                {
                    $campos[$nomeCampo] = $camposDesordenados[$nomeCampo];
                }
            }
 
            $this->addFields($campos);
            $this->setValidators($camposEValidadores[1]);
        }
    }
    
    /**
     * Gera os filtros e as colunas.
     *
     * @return array Vetor com os filtros, a coluna da grid e as chaves a serem passadas ao form de edição.
     */
    protected function gerarCampos()
    {
        // Obtém as colunas da tabela.
        $colunas = $this->tipo->obterEstruturaDaTabela();
        
        $campos = array();
        $validadores = array();

        foreach ( $colunas as $coluna )
        {
            // Gera o campo e validador para a coluna.
            list($campo, $validador) = $this->gerarCampo($coluna);
            
            if ( $campo )
            {
                $campos[$coluna->nome] = $campo;

                if ( $validador )
                {
                    $validadores[$coluna->nome] = $validador;
                }
            }
        }

        return array( $campos, $validadores );
    }
    
    /**
     * Retorna os campos e validadores passados na lista na ordem.
     * Metodo alternativo criado para evitar o problema de um campo ser adicionado no formulario automaticamente ao ser adicionado na base de dados, podendo causar bugs.
     *
     * @return array
     */
    protected function gerarCamposEspecificos(array $lista)
    {
        list($campos, $validadores) = $this->gerarCampos();
     
        $campos = array_merge($campos, $this->gerarCamposSubDetail());
        $camposRet = array();
        $validadoresRet = array();
        
        foreach ( $lista as $campo )
        {
            $camposRet[$campo] = $campos[$campo];
            
            if ( isset($validadores[$campo]) )
            {
                $validadoresRet[$campo] = $validadores[$campo];
            }
        }
        
        return array($camposRet, $validadoresRet);
    }

    /**
     * Gera objeto do campo do formulário.
     *
     * @param bInfoColuna $coluna Objeto com os dados da coluna.
     * @return array Vetor com o componente do campo criado de acordo com o tipo da coluna e validador.
     */
    protected function gerarCampo(bInfoColuna $coluna)
    {
        $campo = NULL;

        $idColuna = explode('__', $coluna->campo);
        $coluna->campo = end($idColuna);
        
        $atributosReservados = array_keys(get_object_vars($this));
        
        // Caso o id do campo já estiver sendo usado no formulário, concatena '_' no final do id.
        if ( in_array($coluna->campo, $atributosReservados) )
        {
            $coluna->campo .= '_';
        }
        
        if ( substr($coluna->valorPadrao, 0, 7) != 'nextval')
        {
            $valor = $coluna->valorPadrao;
        }

        $rotulo = _M($coluna->titulo, $this->modulo);

        if ( $coluna->obrigatorio == DB_TRUE )
        {
            $validador = new MRequiredValidator($coluna->campo, '', $coluna->tamanho);
        }

        // Verifica se campo é chave estrangeira.
        if ( strlen($coluna->fkTabela) )
        {
            $campo = new bEscolha($coluna->campo, $coluna->fkTabela, $this->modulo, NULL, $coluna->titulo );
        }
        
        if ( !$campo )
        {
            switch ( $coluna->tipo )
            {
                case bInfoColuna::TIPO_BOOLEAN:
                    if ( $valor === NULL )
                    {
                        $valor = DB_FALSE;
                    }
                    
                    $campo = new MSelection($coluna->campo, $valor, $rotulo, bBooleano::obterVetorSimNao(), NULL, '', '', FALSE);
                    $validador = NULL;
                    break;

                case bInfoColuna::TIPO_DATA:
                    $campo = new MCalendarField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;
                
                case bInfoColuna::TIPO_TIMESTAMP:
                    // FIXME: adicionar o componente MTimestampField após a resolução do #15440.
                    $campo = new MTimestampField($coluna->campo, NULL, $rotulo);
                    
                    break;

                case bInfoColuna::TIPO_DECIMAL:
                    $campo = new MFloatField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;
                
                case bInfoColuna::TIPO_NUMERIC:
                    $campo = new MFloatField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;

                case bInfoColuna::TIPO_INTEIRO:
                case bInfoColuna::TIPO_INTEIRO_LONGO:
                    $campo = new MIntegerField($coluna->campo, $valor, $rotulo, T_CODIGO);
                    $validador = new MIntegerValidator($coluna->campo, $rotulo);

                    if ( $coluna->obrigatorio == DB_TRUE )
                    {
                        $validador->type = 'required';
                    }

                    break;

                case bInfoColuna::TIPO_TEXTO_LONGO:
                    $campo = new MMultiLineField($coluna->campo, $valor, $rotulo, NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
                    break;
                    
                case bInfoColuna::TIPO_TEXTO:
                default:
                    $campo = new MTextField($coluna->campo, $valor, $rotulo, T_DESCRICAO);
                    break;
            }
        }

        if ( $coluna->restricao == 'p' && substr($coluna->valorPadrao, 0, 7) == 'nextval' )
        {
            $validador = NULL;
        }

        return array( $campo, $validador );
    }
    
    /**
     * Gera os componentes MSubDetail relacionadas ao formulário.
     * 
     * @return array Vetor com componentes MSubDetail.
     */
    protected function gerarCamposSubDetail()
    {
        // Busca as tabelas relacionadas com o tipo dinâmico e seta no tipo.
        if ( cadastroDinamico::verificarIdentificador($this->modulo, MIOLO::_REQUEST('chave')) )
        {            
            $cadastroDinamico = bTipo::instanciarTipo('cadastroDinamico', 'base');
            $cadastroDinamico->popularPorIdentificador($this->modulo, MIOLO::_REQUEST('chave'));
            $this->tipo->definirTiposRelacionados( $cadastroDinamico->obterTabelasRelacionadas() );
        }
        
        // Obtém o nome dos tipos que estão relacionados ao tipo principal.
        $tiposRelacionados = $this->tipo->obterTiposRelacionados();
        $chavesPrimarias = $this->tipo->obterChavesPrimarias();
        
        if ( !is_array($tiposRelacionados) )
        {
            return NULL;
        }
        else
        {
            $subDetail = array();
            
            foreach ( $tiposRelacionados as $tipo )
            {
                $tipoObjeto = bTipo::instanciarTipo($tipo, $this->modulo);
                $estruturaTabela = $tipoObjeto->obterEstruturaDaTabela();

                if ( is_array($estruturaTabela) )
                {
                    $campos = array();
                    $validadores = array();
                    $colunas = array();

                    foreach ( $estruturaTabela as $campoId => $campo )
                    {
                        $campo instanceof bInfoColuna;
                        $chaveRelacionada = in_array($campo->nome, $chavesPrimarias);

                        // Verifica se o campo tem mesmo id que a chave primária do formulário, caso seja, não monta o campo e coluna.
//                        if ( !($campo->restricao == 'p' && substr($campo->valorPadrao, 0, 7) == 'nextval' ) )
                        
                        if ( !$chaveRelacionada )
                        {
                            list($campos[$campoId], $validadores[$campoId]) =  $this->gerarCampo($campo);
                            
                            // Esconde chave primaria na subdetail
                            if ( $campo->eChavePrimaria() )
                            {
                                $campos[$campoId] = new MTextField($campoId, $campos[$campoId]->value);
                                $campos[$campoId]->addBoxStyle('display', 'none');
                            }
                            
                            // Define o alinhamento da coluna da grid da subdetail.
                            if ( in_array($campo->tipo, array(bInfoColuna::TIPO_BOOLEAN, bInfoColuna::TIPO_DATA, bInfoColuna::TIPO_TIMESTAMP)) )
                            {
                                $alinhamento = 'center';
                            }
                            elseif ( in_array($campo->tipo, array(bInfoColuna::TIPO_TEXTO_LONGO, bInfoColuna::TIPO_TEXTO, bInfoColuna::TIPO_INTEIRO, bInfoColuna::TIPO_NUMERIC) ) )
                            {
                                $alinhamento = 'right';
                            }
                            else
                            {
                                $alinhamento = 'left';
                            }
                            
                            if ( !$chaveRelacionada && !$campo->eChavePrimaria() )
                            {
                                $colunas[$campoId] = new MGridColumn( $campo->titulo, $alinhamento, true, null, true, $campoId );
                                
                                // Só precisa fazer quando é diferente de inserir
                                $relacionamentos = $tipoObjeto->obterRelacionamentos(); 

                                foreach( $relacionamentos as $relacionamento )
                                {
                                    if ( $campoId == $relacionamento->atributo )
                                    {
                                        $tipoRelacionado = bTipo::instanciarTipo($relacionamento->tabela_ref);
                                        $chavesPKRelacionado = $tipoRelacionado->obterChavesPrimarias();

                                        // Senão tiver PKs relacionadas, busca os valores para replace direto na coluna
                                        if ( !(count($chavesPKRelacionado) > 0) )
                                        {
                                            $valores = $tipoRelacionado->obterArrayAssociativo();
                                            $colunas[$campoId]->setReplace($campoId, $valores);
                                        }

                                        // Se tiver esse atributo é montado um bescolha, e consequentemente criada
                                        // uma nova coluna para esse valor
                                        if ( strlen($campo->fkTabela) && 
                                             count($chavesPKRelacionado) > 0 && 
                                             strlen($tipoRelacionado->obterColunaDescritiva()) > 0 )
                                        {
                                            // Somente buscar valores relacionados, não todos
                                            $colunas[$campoId . 'Descricao'] = new MGridColumn( $campo->titulo, 'left', true, null, true, $campoId . 'Descricao');
                                          
//                                          $colunas[$campoId]->setTitle("Código " . strtolower($colunas[$campoId]->getTitle()));
                                            $colunas[$campoId]->visible = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    $subDetail[$tipo] = $campoSubDetail = new MSubDetail($tipo, $tipoObjeto->obterComentarioDaTabela());
                    $campoSubDetail->setFields( $campos );
                    $campoSubDetail->setValidators( $validadores );
                    $campoSubDetail->setColumns($colunas);
                    
                    // Limpa a subdetail.
                    if ( MUtil::isFirstAccessToForm() )
                    {
                        MSubDetail::clearData($tipo);
                    }
                }
            }
            
            return $subDetail;
        }
    }
    
    /**
     * Define a ordem que os vão aparecer no formulário.
     * 
     * @param array $ordemDosCampos Vetor com a ordem dos campos.
     */
    protected function definirOrdemDosCampos(array $ordemDosCampos)
    {
        $this->ordemDosCampos = $ordemDosCampos;
    }
    
    /**
     * Obtém a ordem dos campos.
     * 
     * @return array Vetor com a ordem dos campos. 
     */
    protected function obterOrdemDosCampos()
    {
        return $this->ordemDosCampos;
    }    
    
}

?>