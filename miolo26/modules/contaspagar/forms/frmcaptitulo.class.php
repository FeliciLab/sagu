<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('classes/capformdinamico.class.php', 'contaspagar');

class frmcaptitulo extends capformdinamico
{
    public function __construct($parametros, $titulo = NULL)
    {
        parent::__construct($parametros, _M('Registrar pagamento'));
    }
    

    public function definirCampos() 
    {
        $MIOLO = MIOLO::getInstance();
        
        $busOpenCounter = new BusinessFinanceBusOpenCounter();
        $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();
        
        parent::definirCampos(FALSE);
        
        list($campos, $validadores) = $this->gerarCampos();
        
        unset($campos['solicitacaoparcelaid'], $validadores['solicitacaoparcelaid']);
        unset($campos['tituloaberto'], $validadores['tituloaberto']);

        foreach ( $campos as $campo )
        {
            if ( $campo instanceof MControl )
            {
                if ( $campo->name != 'valor' )
                {
                    $campo->setReadOnly(true);
                }
            }
        }
        
        $finTransferenciaDeCaixa = new FinTransferenciaDeCaixa();
        $campos[] = $finTransferenciaDeCaixa->verificaSePossuiPendenciasDeTransferencia($openCounter->counterId, $openCounter->responsibleUserName);
                
        $campos = array_merge($campos, $this->gerarCamposSubDetail());
        
        $sub = $campos['caplancamento'];
        
        if ( $sub instanceof MSubdetail )
        {
            $sub->setReadOnly(true);
        }
        
        if ( $MIOLO->checkAccess('FrmLancamentoSemVinculo', A_INSERT, false, true) )
        {
            $url = $MIOLO->getActionURL('finance', 'main:register:lancamentoSemVinculo', null, array('function' => SForm::FUNCTION_INSERT));
            $campos['lancamentoSemVinculo'] = new MLinkButton('btnLancamentoSemVinculo', _M(' INSERIR LANÇAMENTO SEM VÍNCULO '), "window.open('{$url}');");
            $campos[] = new MSpacer();
        }
        
        //
        // Tipo de movimentacao
        //
        $options = array(
            array(_M('De caixa'), 'C'),
            array(_M('Bancária'), 'B'),
        );
        $tipoMovimentacao = new MRadioButtonGroup('tipoMovimentacao', _M('Tipo de movimentação'), $options, '', '', 'horizontal');
        $tipoMovimentacao->addAttribute('onclick', "checkTipo();");
        $campos['divTipoMovimentacao'] = new MDiv('divTipoMov', $tipoMovimentacao);
                
        // Caixa
        $busOpenCounter = new BusinessFinanceBusOpenCounter();
        $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();
        $busAccountScheme = new BusinessAccountancyBusAccountScheme();
        $busCounter = new BusinessFinanceBusCounter();
                
        if ( strlen($openCounter->counterId) > 0 )
        {
            // Obtém plano de contas cadastrado no caixa
            $counterInfo = $busCounter->getCounter($openCounter->counterId);
            $accountInfoCounter = $busAccountScheme->getAccountScheme($counterInfo->accountSchemeId);
        }
        
        $MIOLO->page->addJsCode("
            function checkTipo()
            {
                if ( document.getElementById('tipoMovimentacao_0').checked != true && document.getElementById('tipoMovimentacao_1').checked != true )
                {
                    document.getElementById('tipoMovimentacao_0').checked = true;
                }
                
                if ( document.getElementById('tipoMovimentacao_0').checked == true )
                {   
                    document.getElementById('accountschemeid').value = '$counterInfo->accountSchemeId';
                    document.getElementById('accountschemeidDescricao').value = '$accountInfoCounter->accountSchemeId - $accountInfoCounter->description';
                    document.getElementById('divEspecie').style.display = 'block';
                }
                else
                {
                    document.getElementById('accountschemeid').value = '';
                    document.getElementById('accountschemeidDescricao').value = '';
                    document.getElementById('divEspecie').style.display = 'none';
                }

                document.getElementById('divConta').style.display = document.getElementById('tipoMovimentacao_0').checked ? 'none' : 'block';
                document.getElementById('divCaixa').style.display = document.getElementById('tipoMovimentacao_0').checked ? 'block' : 'none';
            }
        ");

        $MIOLO->page->onload("checkTipo();");
        
        
        $bankAccount = new FinBankAccount();
        $select = new MSelection('contaBancariaId', null, _M('Conta bancária'), $bankAccount->findList());
        $select->addAttribute('onchange', MUtil::getAjaxAction('infoContabil'));
        $hct = new MRowContainer('hctCB', array($select));
        $campos['divConta'] = new MDiv('divConta', $hct);
        
        $hct = new MRowContainer('hctLABEL', array(new MTextLabel('caixa', SAGU::NVL($openCounter->counterDescription, '-'), _M('Caixa'))));
        $campos['caixa'] = new MDiv('divCaixa', $hct);
        
        $pgto = new MTextField('valorpgto', null, _M('Valor do pagamento'), T_CODIGO, _M('Informe o valor que foi pago, podendo ser parcial ou total'));
        $validadores['valorpgto'] = new MFloatValidator('valorpgto', _M('Valor do pagamento'));
        $campos['valorpgto'] = $pgto;
        
        // Padrão especie tipo dinheiro
        $moneySpecie = SAGU::getParameter('FINANCE', 'MONEY_SPECIES_ID');
        $camposBusca = 'speciesid, description';
        $campos['speciesid'] = new MDiv('divEspecie', new bEscolha('speciesid', 'finspecies', 'contaspagar', $moneySpecie, _M('Espécie'), false, $camposBusca));
        $validadores['speciesid'] = new MIntegerValidator('speciesid', _M('Espécie'));
        
        $busCostCenter = new BusinessAccountancyBusCostCenter();
        
        $tituloId = MIOLO::_REQUEST('tituloid');
        
        $filtros = new stdClass();
        $filtros->tituloid = $tituloId;
        
        // Obtém objeto do lançamento a partir do título
        $capLancamento = new caplancamento();
        $result = $capLancamento->buscar($filtros);
        
        // Obtém o centro de custo referente ao título
        $costCenterInfo = $busCostCenter->getCostCenter($result[0]->costcenterid);
        
        // centro de custo
        $camposBusca = 'costcenterid, description';
        $campos['costcenterid'] = new bEscolha('costcenterid', 'capcentrodecusto', 'contaspagar', $costCenterInfo->costCenterId, _M('Centro de custo'), false, $camposBusca);
//        $validadores['costcenterid'] = new MFloatValidator('costcenterid', _M('Centro de custo'));
        
        // plano de contas
        $camposBusca = 'accountschemeid, description';
        $campos['accountschemeid'] = new bEscolha('accountschemeid', 'capplanodecontas', 'contaspagar', $counterInfo->accountSchemeId, _M('Plano de contas'), false, $camposBusca);
//        $validadores['accountschemeid'] = new MIntegerValidator('accountschemeid', _M('Plano de contas'));
    
        $this->addFields($campos);
        $this->setValidators($validadores);
        
        $jsCode = " var valorAberto = document.getElementById('valoraberto'); 
                    document.getElementById('valorpgto').value = valorAberto.value;";
                    
        $MIOLO->page->onLoad($jsCode);
    }

    public function infoContabil($args)
    {
        $MIOLO = MIOLO::getInstance();
        $bankAccount = new BusinessFinanceBusBankAccount();
        $busAccountScheme = new BusinessAccountancyBusAccountScheme();
        $busCounter = new BusinessFinanceBusCounter();
        
        $busOpenCounter = new BusinessFinanceBusOpenCounter();
        $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();
                                
        if ( strlen($args->contaBancariaId) > 0 )
        {
            // Obtém plano de contas cadastrado na conta bancária
            $bankAccountInfo = $bankAccount->getBankAccount($args->contaBancariaId);
            $accountInfoBank = $busAccountScheme->getAccountScheme($bankAccountInfo->accountSchemeId);
        }   
        
        if ( strlen($openCounter->counterId) > 0 )
        {
            // Obtém plano de contas cadastrado no caixa
            $counterInfo = $busCounter->getCounter($openCounter->counterId);
            $accountInfoCounter = $busAccountScheme->getAccountScheme($counterInfo->accountSchemeId);
        }
                
        $jsCode = " function infoContabil()
                    {
                        if ( !document.getElementById('tipoMovimentacao_0').checked )
                        {
                            document.getElementById('accountschemeid').value = '$bankAccountInfo->accountSchemeId';
                            document.getElementById('accountschemeidDescricao').value = '$accountInfoBank->accountSchemeId - $accountInfoBank->description'; 
                        }
                        else
                        {
                            document.getElementById('accountschemeid').value = '$counterInfo->accountSchemeId';
                            document.getElementById('accountschemeidDescricao').value = '$accountInfoCounter->accountSchemeId - $accountInfoCounter->description';
                        }
                    }

                    infoContabil();
                ";
        
        $MIOLO->page->addJsCode($jsCode);
        $this->setNullResponseDiv();
    }
    
    
    /**
     * Obtém o openCounterId para confirmar a transferência de caixa
     * 
     * @param type $args
     * @return type
     */
    public function confirmacaoDeRecebimentoDeCaixa()
    {
        $finTransferenciaDeCaixa = new FinTransferenciaDeCaixa();
        $busOpenCounter = new BusinessFinanceBusOpenCounter();
        
        $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();
        $openCounterId = $openCounter->openCounterId;
                
        return $finTransferenciaDeCaixa->confirmacaoDeRecebimentoDeCaixa($openCounterId);
    }
    
    public function botaoSalvar_click()
    {
        $tipo = $this->tipo;
        $tipo instanceof captitulo;
        
        $data = $this->getData();
     
        // Caixa
        if ( $data->tipoMovimentacao == 'C' )
        {
            $busOpenCounter = new BusinessFinanceBusOpenCounter();
            $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();

            if ( !$openCounter )
            {
                new MMessageError('Não é possível registrar pagamento pois não há um caixa aberto para o operador logado.');
                return;
            }
        }
        
        // Conta bancaria
        if ( $data->tipoMovimentacao == 'B' && strlen($data->contaBancariaId) == 0 )
        {
            new MMessageError(_M('Está faltando informar a Conta bancária.'));
            return;   
        }
        
        $tipo->valorpgto = $data->valorpgto;
        $tipo->speciesid = $data->speciesid;
        $tipo->contabancariaid = $data->contaBancariaId;
        $tipo->accountschemeid = $data->accountschemeid;
        $tipo->costcenterid = $data->costcenterid;
        
        parent::botaoSalvar_click();
    }
}
?>