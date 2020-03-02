<?php

/**
 * @author moises
 *
 * @since
 * Class created on 03/04/2013
 *
 */
$MIOLO->uses('tipos/capconfiguracao.class.php', 'contaspagar');
$MIOLO->uses('tipos/capsolicitacaoestado.class.php', 'contaspagar');
$MIOLO->uses('tipos/captitulo.class.php', 'contaspagar');
$MIOLO->uses('tipos/caphistorico.class.php', 'contaspagar');

class capsolicitacao extends bTipo
{
    public $solicitacaoid;
    public $solicitacaoestadoid;
    public $dadoscompra;
    public $fornecedor;
    public $costcenterid;
    public $datasolicitacao;
    public $personid;
    public $justificativa;
    public $accountschemeid;
    public $numerodanotafiscal;
    
    public function __construct() 
    {
        parent::__construct('capsolicitacao');
        
        $this->adicionarTipoRelacionado('capsolicitacaoparcela');
        $this->adicionarTipoRelacionado('caphistorico');
    }
    
    /**
     * @return caphistorico
     */
    public function obterUltimoHistorico()
    {
        return end($this->obterHistoricos());
    }
    
    /**
     * @return array
     */
    public function obterHistoricos()
    {
        return (array) $this->dadosTiposRelacionados['caphistorico'];
    }
    
    public function salvar()
    {
        $ok = parent::salvar();
        $historico = null;
        $solicitacao = null;
        
        if ( strlen($this->solicitacaoid) > 0 )
        {
            $solicitacao = new capsolicitacao();
            $solicitacao->solicitacaoid = $this->solicitacaoid;
            $solicitacao->popular();
            
            $historico = $solicitacao->obterUltimoHistorico();
        }

        // Insere historico caso necessario
        $mudouEstado = $solicitacao->solicitacaoestadoid != $historico->solicitacaoestadoid;
        $mudouJustificativa = $this->justificativa != $historico->justificativa;
        
        if ( !(strlen(SAGU::getUsuarioLogado()->personId) > 0) )
        {
            throw new Exception(_M("Para poder emitir um lançamento é necessário acessar com um usuário vinculado à uma pessoa física."));
        }
        
        if ( $ok && ( !$historico || $mudouEstado || $mudouJustificativa ) )
        {
            $novoHist = new caphistorico();
            $novoHist->solicitacaoid = MUtil::NVL($this->solicitacaoid, $this->obterUltimoIdInserido());
            $novoHist->personid = SAGU::getUsuarioLogado()->personId;
            $novoHist->solicitacaoestadoid = MUtil::NVL($this->solicitacaoestadoid, $solicitacao->solicitacaoestadoid);
            $novoHist->justificativa = $this->justificativa;
            $novoHist->inserir();
        }

        return $ok;
    }

    public function inserir()
    {
        $parcelas = (array) $this->dadosTiposRelacionados['capsolicitacaoparcela'];
        
        if ( count($parcelas) == 0 )
        {
            throw new Exception(_M('Deve ser adicionado pelo menos uma parcela.'));
        }
        
        // Se for forma de pagamento a vista, permite somente 1 parcela.
        if ( $this->formadepagamentoid == 1 && count($parcelas) > 1 )
        {
            throw new Exception(_M('Para pagamento à vista é permitida somente 1 parcela.'));
        }
        
        if ( strlen($this->solicitacaoestadoid) == 0 )
        {
            $this->solicitacaoestadoid = capsolicitacaoestado::AGUARDANDO_DEFERIMENTO; // O metodo $titulo->registrarTitulosDaSolicitacao() ira alterar o estado caso necessario
        }
        
        if ( strlen($this->personid) == 0 )
        {
            $this->personid = SAGU::getUsuarioLogado()->personId;
        }

        $ok = parent::inserir();
        
        if ( $ok )
        {
            // Segue fluxo de regra de negocio, caso NAO NECESSITA DEFERIMENTO ja insere titulos/lancamentos
            if ( capconfiguracao::obterTipoSolicitacaoPagto() == capconfiguracao::NAO_NECESSITA_DEFERIMENTO )
            {
                $titulo = new captitulo();
                $titulo->registrarTitulosDaSolicitacao( $this->obterUltimoIdInserido() );
            }
        }
        
        return $ok;
    }
    
    /**
     * @return boolean
     */
    public function possuiInformacaoDeferimento()
    {
        $parcelas = (array) $this->dadosTiposRelacionados['capsolicitacaoparcela'];
        $ids = array();
        
        foreach ( $parcelas as $parcela )
        {
            $parcela instanceof capsolicitacaoparcela;
            $ids[] = $parcela->solicitacaoparcelaid;
        }
        
        if ( count($ids) > 0 )
        {
            $msql = new MSQL();
            $msql->setTables('captitulo');
            $msql->setColumns('TRUE');
            $msql->setWhere('solicitacaoparcelaid IN ' . $msql->convertArrayToIn($ids));

            $result = bBaseDeDados::consultar($msql);

            return $result[0][0] == DB_TRUE;
        }
        
        return false;
    }
    
    public function verificaSePodeEditar()
    {
        if ( in_array($this->solicitacaoestadoid, array(capsolicitacaoestado::AGUARDANDO_PAGAMENTO, capsolicitacaoestado::CANCELADO)) )
        {
            $est = new capsolicitacaoestado();
            $est->solicitacaoestadoid = $this->solicitacaoestadoid;
            $est->popular();

            throw new Exception(_M('Solicitações no estado "@1" não podem ser editadas.', null, $est->nome));
        }

        if ( $this->possuiInformacaoDeferimento() )
        {
            throw new Exception(_M('Esta solicitação já possui informações de títulos ou parcelas, portando não pode ser alterada.'));
        }
    }
    
    public function verificaSePodeCancelar()
    {
        if ( !in_array($this->solicitacaoestadoid, array(capsolicitacaoestado::AGUARDANDO_DEFERIMENTO)) )
        {
            $est = new capsolicitacaoestado();
            $est->solicitacaoestadoid = $this->solicitacaoestadoid;
            $est->popular();
            
            throw new Exception(_M('Solicitações no estado "@1" não podem ser canceladas.', null, $est->nome));
        }
        
        if ( $this->possuiInformacaoDeferimento() )
        {
            throw new Exception(_M('Esta solicitação já possui informações de títulos ou parcelas, portando não pode ser cancelada.'));
        }
    }
}

?>