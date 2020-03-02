<?php

/**
 * @author moises
 *
 * @since
 * Class created on 08/04/2013
 *
 */
class caplancamento extends bTipo
{
    public $lancamentoid;
    public $tituloid;
    public $valor;
    public $tipolancamento;
    public $contabancariaid;
    public $accountschemeid;
    public $costcenterid;
    public $operationid; 
    public $countermovementid;
    public $bankmovementid;
    
    /**
     *
     * @var int
     */
    public $speciesid;
    
    /**
     * Constante que indica qual é a coluna na FinDefaultOperation que contém a 
     * referência à operação de cobrança
     * 
     */
    const COLUNA_REFERENTE_OPERACAO_COBRANCA = "capoperacaocobrancaid";
        
    const CREDITO = 'C';
    const DESCONTO = 'D';
    
    protected $ordenacaoPadrao = 'datalancamento';

    public function inserir()
    {
        $operacaoCobranca = FinDefaultOperations::getInformacaoOperacaoPorOperacaoPadrao(caplancamento::COLUNA_REFERENTE_OPERACAO_COBRANCA);
                
        // Caso seja uma operação de cobrança, não reliza movimentações bancárias/de caixa
        if( $this->operationid !== $operacaoCobranca->operationid )
        {
            if ( strlen($this->contabancariaid) > 0 ) // Se no formulario de titulos foi informado tipo de mov. = "Bancaria"
            {
                $this->bankmovementid = $this->registraMovimentacaoBancaria();
            }
            else // Se no formulario de titulos foi informado tipo de mov. = "De caixa"
            {
                $this->countermovementid = $this->registraMovimentoCaixa();
            }
        }
        
        return parent::inserir();
    }
    
    public function registraMovimentoCaixa()
    {
        $busOpenCounter = new BusinessFinanceBusOpenCounter();
        $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();
        
        if ( !$openCounter )
        {
            throw new Exception(_M('Não é possível registrar pagamento pois não há um caixa aberto para o operador logado.'));
        }
        
        $mov = new stdClass();
        $mov->value = $this->valor;
        $mov->operation = $this->tipolancamento;
        $mov->speciesId = $this->speciesid;
        $mov->openCounterId = $openCounter->openCounterId;
        $mov->tituloId = $this->tituloid;
        
        $busCounterMovement = new BusinessFinanceBusCounterMovement();
        $busCounterMovement->insertCounterMovement($mov);
        
        // Retorna o último id inserido
        return $busCounterMovement->getLastInsertId();
    }
    
    public function registraMovimentacaoBancaria()
    {
        $busBankAccount = new BusinessFinanceBusBankAccount();

        $bankAccount = $busBankAccount->getBankAccount($this->contabancariaid);
        
        $finBankMovement = new FinBankMovement();
        $finBankMovement->bankId               = $bankAccount->bankId;
        $finBankMovement->ourNumber            = $bankAccount->ourNumber;
        $finBankMovement->branch               = $bankAccount->branchNumberDigit;
        $finBankMovement->branchNumber         = $bankAccount->branchNumber;
        $finBankMovement->wallet               = $bankAccount->wallet;
        $finBankMovement->value                = $this->valor * (-1); //Registra valor negativo por ser débito
        $finBankMovement->valuePaid            = $this->valor * (-1);
        $finBankMovement->occurrenceDate       = SAGU::getDateNow();
        $finBankMovement->fileDiscount         = 0;
        $finBankMovement->saguDiscount         = 0;
        $finBankMovement->fileInterestFine     = 0;
        $finBankMovement->saguInterestFine     = 0;
        $finBankMovement->otherDiscounts       = 0;
        $finBankMovement->otherAdditions       = 0;
        $finBankMovement->expenditure          = 0;
        $finBankMovement->bankMovementStatusId = FinBankMovementStatus::STATUS_NOT_FOUND;
        $finBankMovement->tituloid             = $this->tituloid;
        
        // tivemos que executar o SQL aqui no modulo 2.6 pois no 2.0 congelava a consulta, pelo fato de estar em uma transacao diferente
        $sql = $finBankMovement->getInsertSql();
        bBaseDeDados::executar($sql);
        
        // Retorna o último id inserido
        return $finBankMovement->getLastInsertId();
        
//        $operations = $BusDefaultOperations->getDefaultOperations();
//
//        $filterDiscount = new FinEntry();
//        $filterDiscount->invoiceId    = $finBankMovement->invoiceId;
//        $filterDiscount->isAccounted  = DB_FALSE;
//        $filterDiscount->creationType = 'A'; // gerado automaticamente pelo sistema
//        $filterDiscount->costCenterId = SAGU::NVL($data->costCenterId, $invoice->costCenterId);
//        $filterDiscount->entryDate    = SAGU::getDateNow();
//
//        // Desconto
//        if ( strlen($discountValue) > 0 )
//        {
//            self::insertEntryOperation($filterDiscount, $operations->discountOperation, $discountValue, $data->comments, $forceInsert);
//        }
        
    }
    
//    public static function insertEntryOperation($filters, $operationId, $value, $comments=null, $forceInsert = false)
//    {
//        $MIOLO = MIOLO::getInstance();
//        $function = MIOLO::_REQUEST("function");
//        
//        $BusEntry = new BusinessFinanceBusEntry();
//        $filters->operationId = $operationId;
//                    
//        if ( $function == 'insert' || $forceInsert )
//        { 
//            $filters->value    = $value;
//            $filters->comments = $comments;
//            $BusEntry->insertEntry($filters);
//        }
//        else
//        {
//            unset($filters->value);
//            unset($filters->comments);
//            unset($filters->entryId);
//        
//            $entry = $BusEntry->searchEntry($filters);
//
//            $filters->value    = $value;
//            $filters->entryId  = $entry[0][0];
//            $filters->comments = $comments;
//
//            $BusEntry->updateEntry($filters);
//        }
//    }
    
    public function buscarNaReferencia($colunas, $valoresFiltrados = array( ))
    {
        $msql = parent::buscarNaReferencia($colunas, $valoresFiltrados);
        $msql->addLeftJoin('public.capsolicitacaoparcela', 'capsolicitacaoparcela.solicitacaoparcelaid = captitulo.solicitacaoparcelaid');
        $msql->addLeftJoin('public.capsolicitacao', 'capsolicitacao.solicitacaoid = capsolicitacaoparcela.solicitacaoid');
        $msql->addLeftJoin('ONLY basphysicalperson', 'basphysicalperson.personid = capsolicitacao.fornecedorid');

        return $msql;
    }
}

?>