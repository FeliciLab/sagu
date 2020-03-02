<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
$MIOLO->uses('tipos/caplancamento.class.php', 'contaspagar');
$MIOLO->uses('tipos/capsolicitacao.class.php', 'contaspagar');

class captitulo extends bTipo
{
    public $tituloid;
    public $solicitacaoparcelaid;
    public $valor;
    public $vencimento;
    public $numeroparcela;
    public $tituloaberto;
    public $accountschemeid;
    public $costcenterid;
    
    /**
     * Atributo definido no frmcaptitulo
     * 
     * @var double
     */
    public $valorpgto;
    
    /**
     * @var int
     */
    public $speciesid;
    
    /**
     * @var int
     */
    public $contabancariaid;
    
    /**
     * Atributo relacionado a inserção de lançamentos
     * 
     * @var Integer 
     */
    public $operationid;
    
    /**
     * Atributo relacionado a inserção de lançamentos
     * 
     * @var Integer 
     */
    public $operationtypeid;
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->adicionarTipoRelacionado('caplancamento');
    }
    
    /**
     * Adiciona os titulos referentes a solicitacao.
     * 
     * Para cada parcela da solicitacao, e adicionado um titulo e lancamento.
     */
    public function registrarTitulosDaSolicitacao($solicitacaoId)
    {
        $solicitacao = new capsolicitacao();
        $solicitacao->solicitacaoid = $solicitacaoId;
        $solicitacao->popular();
        
        if ( $solicitacao->solicitacaoestadoid != capsolicitacaoestado::AGUARDANDO_DEFERIMENTO )
        {
            throw new Exception(_M('O estado desta solicitação não está como AGUARDANDO DEFERIMENTO atualmente, então não pode ser deferido.'));
        }
        
        $parcelas = (array) $solicitacao->dadosTiposRelacionados['capsolicitacaoparcela'];

        if ( count($parcelas) == 0 )
        {
            throw new Exception(_M('Não foi possível deferir a solicitação pois ela não possui parcelas.'));
        }
        
        // Altera o estado para EM PAGAMENTO
        $solicitacao->solicitacaoestadoid = capsolicitacaoestado::AGUARDANDO_PAGAMENTO;
        $solicitacao->editar();
        
        // Busca as informações da operação de cobrança do CAP
        $operacaoCobranca = FinDefaultOperations::getInformacaoOperacaoPorOperacaoPadrao(caplancamento::COLUNA_REFERENTE_OPERACAO_COBRANCA);
                
        foreach ( $parcelas as $parcela )
        {
            $parcela instanceof capsolicitacaoparcela;

            // Insere titulo
            $titulo = new captitulo();
            $titulo->solicitacaoparcelaid = $parcela->solicitacaoparcelaid;
            $titulo->valor = $parcela->valor;
            $titulo->vencimento = $parcela->datavencimento;
            $titulo->numeroparcela = $parcela->parcela;
            $titulo->accountschemeid = $solicitacao->accountschemeid;
            $titulo->costcenterid = $solicitacao->costcenterid;
            $titulo->operationid = $operacaoCobranca->operationid;
            $titulo->operationtypeid = $operacaoCobranca->operationtypeid;
            
            $titulo->inserir();
        }
    }
    
    public function inserir()
    {
        // FIXME: Apos realizar o inserir() , perde o $this->valor, ver Jader.
        $valor = $this->valor;
        
        $ok = parent::inserir();

        if ( $ok )
        {
            // Insere lancamento
            $lancamento = new caplancamento();
            $lancamento->tituloid = $this->obterUltimoIdInserido();
            $lancamento->valor = $valor;
            $lancamento->tipolancamento = caplancamento::CREDITO;
            $lancamento->accountschemeid = $this->accountschemeid;
            $lancamento->costcenterid = $this->costcenterid;
            $lancamento->operationid = $this->operationid;
            $lancamento->tipolancamento = $this->operationtypeid;
            $lancamento->inserir();
        }
        
        return $ok;
    }
    
    public function editar()
    {
        // Deve ser feito o editar antes e o lancamento depois para a trigger do caplancamento atualizar corretamente
        $ok = parent::editar();
        
        // Se o valor que está para ser pago for maior do que está devido
        if ( $this->valorpgto > $this->valor )
        {
            throw new Exception(_M("Verifique o valor do lançamento que está sendo inserido, parece que ele está superior ao valor em aberto."));
        }

        // Insere o registro de pagamento
        if ( strlen($this->valorpgto) > 0 )
        {
            $lancamento = new caplancamento();
            $lancamento->tituloid = $this->tituloid;
            $lancamento->tipolancamento = caplancamento::DESCONTO;
            $lancamento->valor = $this->valorpgto;
            $lancamento->speciesid = $this->speciesid;
            $lancamento->contabancariaid = $this->contabancariaid;
            $lancamento->accountschemeid = $this->accountschemeid;
            $lancamento->costcenterid = $this->costcenterid;
            $lancamento->inserir();
        }
        
        // Verifica se todos os titulos da solicitacao nao estao mais abertos, caso sim, altera o estado da solicitacao para FECHADO.
        if ( !$this->existeTituloEmAbertoDaSolicitacao($this->tituloid) )
        {
            $solicitacaoId = $this->obterCodSolicitacao($this->tituloid);
            
            $solicitacao = new capsolicitacao();
            $solicitacao->solicitacaoid = $solicitacaoId;
            $solicitacao->solicitacaoestadoid = capsolicitacaoestado::FECHADO;
            $solicitacao->salvar();
        }
        
        return $ok;
    }
    
    /**
     * @return int
     */
    public function obterCodSolicitacao($tituloId)
    {
        $msql = new MSQL();
        $msql->setColumns('capsolicitacaoparcela.solicitacaoid');
        $msql->setTables('captitulo');
        $msql->addInnerJoin('capsolicitacaoparcela', 'captitulo.solicitacaoparcelaid = capsolicitacaoparcela.solicitacaoparcelaid');
        $msql->addEqualCondition('captitulo.tituloid', $tituloId);

        $result = bBaseDeDados::consultar($msql);

        return $result[0][0];
    }
    
    /**
     * Retorna se ainda existe algum titulo aberto para a solicitacao do tituloId passado.
     * 
     * @return boolean
     */
    public function existeTituloEmAbertoDaSolicitacao($tituloId)
    {
        $msql = new MSQL();
        $msql->setColumns('capsolicitacao.solicitacaoid');
        $msql->setTables('captitulo');
        $msql->addInnerJoin('capsolicitacaoparcela', 'captitulo.solicitacaoparcelaid = capsolicitacaoparcela.solicitacaoparcelaid');
        $msql->addInnerJoin('capsolicitacao', 'capsolicitacao.solicitacaoid = capsolicitacaoparcela.solicitacaoid');
        $msql->addEqualCondition('capsolicitacao.solicitacaoid', $this->obterCodSolicitacao($tituloId));
        $msql->addEqualCondition('captitulo.tituloaberto', DB_TRUE);

        $result = bBaseDeDados::consultar($msql);

        return count($result) > 0;
    }
    
    public function buscarNaReferencia($colunas, $valoresFiltrados = array( ))
    {
        $msql = parent::buscarNaReferencia($colunas, $valoresFiltrados);
        $msql->addLeftJoin('public.capsolicitacaoparcela', 'capsolicitacaoparcela.solicitacaoparcelaid = captitulo.solicitacaoparcelaid');
        $msql->addLeftJoin('public.capsolicitacao', 'capsolicitacao.solicitacaoid = capsolicitacaoparcela.solicitacaoid');
        $msql->addLeftJoin('ONLY basperson', 'basperson.personid = capsolicitacao.fornecedorid');
        
        $msql->setWhereAnd('tituloaberto = ?');
        if ( $valoresFiltrados->public__captitulo__tituloaberto == DB_FALSE )
        {
            $msql->addParameter(DB_FALSE);
        }
        else
        {
            $msql->addParameter(DB_TRUE);
        }

        return $msql;
    }
}

?>