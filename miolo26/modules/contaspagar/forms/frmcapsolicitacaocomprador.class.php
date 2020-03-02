<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('classes/capformdinamico.class.php', 'contaspagar');
$MIOLO->uses('tipos/caprateio.class.php', 'contaspagar');

class frmcapsolicitacaocomprador extends capformdinamico
{
    public function definirCampos()
    {
        $MIOLO = MIOLO::getInstance();
        parent::definirCampos(FALSE);

        $this->barraDeFerramentas->enableButton(bBarraDeFerramentas::BOTAO_INSERIR);

        if (SAGU::NVL(SAGU::getUsuarioLogado()->name == NULL))
        {
            $goto = $MIOLO->getActionURL('contaspagar', 'main:process:capsolicitacaocomprador', null, array('chave' => 'capsolicitacaocomprador'));
            $MIOLO->error('Nao ha uma pessoa fisica cadastrada.', $goto);

            // dica para desenvolvedor poder usar este form utilizando usuario padrao sagu2:
            // UPDATE basperson SET miolousername ='sagu2' where personid=(SELECT MIN(personid) FROM ONLY basperson);
        }
        else
        {
            $lista = array('justificativa', 'dadoscompra', 'formadepagamentoid', 'fornecedorid', 'accountschemeid', 'divRespostaAjax', 'numerodanotafiscal', 'capsolicitacaoparcela', 'caphistorico');
            list($campos, $validadores) = $this->gerarCamposEspecificos($lista);

            $pname = SAGU::NVL(SAGU::getUsuarioLogado()->name, '-');
            $solicitante = new MTextLabel(rand(), $pname, _M('Solicitante'));
            $campos = array_merge(array($solicitante), $campos);

            if ( $this->funcao == FUNCAO_EDITAR )
            {
                $validadores['justificativa'] = new MRequiredValidator('justificativa');
            }
            else
            {
                unset($campos['justificativa'], $validadores['justificativa']);
            }

            $camposBusca = 'personid, name';
            $campos['fornecedorid'] = new bEscolha('fornecedorid', 'pessoa', 'base', null, _M('Fornecedor'), false, $camposBusca);

            // plano de contas
            $camposBusca = 'accountschemeid, description';
            $campoPC = new bEscolha('accountschemeid', 'capplanodecontas', 'contaspagar', null, _M('Plano de contas'), false, $camposBusca);
            $campos['accountschemeid'] = $campoPC;
            $campos['divRespostaAjax'] = new MDiv("divRespostaAjax", "");

            $campos['dadoscompra'] = new MMultiLineField('dadoscompra', null, _M('Dados da compra'), null, 5, 40);
            $validadores['dadoscompra'] = new MRequiredValidator('dadoscompra');

            $subHistorico = $campos['caphistorico'];
            if ( $subHistorico instanceof MSubDetail )
            {
                $subHistorico->setReadOnly(true);
            }

            $campos['itemSubdetail'] = new MHiddenField('itemSubdetail');

            $this->definirOrdemDosCampos(array('accountschemeid', 'divLocona'));
            $this->addFields($campos);
            $this->setValidators($validadores);

            // FIXME - Ajuste para arrumar erro em ticket #38578
            $acaoAjax = $this->manager->getUI()->getAjax(":verificaPlanoDeContas");
            $MIOLO->page->onLoad("
                var onchange = document.getElementById('accountschemeid').onchange;

                document.getElementById('accountschemeid').onchange = function()
                {
                    onchange();
                    {$acaoAjax}
                };
            ");
        }
    }

    public static function addToTable($data)
    {
        $MIOLO = MIOLO::getInstance();

        $nossoNumero = MIOLO::_REQUEST('capsolicitacaoparcela_nossonumero');
        $numeroDoDocumento = MIOLO::_REQUEST('capsolicitacaoparcela_numerododocumento');
        $itemEditado = MIOLO::_REQUEST('itemSubdetail');

        foreach ( $_SESSION['main:process:capsolicitacaocomprador:capsolicitacaoparcela']->contentData as $dataSubDetail )
        {
            if ( ($itemEditado != $dataSubDetail->arrayItem) || (!strlen($itemEditado) > 0) )
            {
                if ( $dataSubDetail->capsolicitacaoparcela_nossonumero == $nossoNumero && (strlen($nossoNumero) > 0) )
                {
                    new MMessageWarning(_M('Já existe uma parcela cadastra com o nosso número informado, por favor, altere o nosso número.'));
                    return;
                }

                if ( $dataSubDetail->capsolicitacaoparcela_numerododocumento == $numeroDoDocumento && (strlen($numeroDoDocumento) > 0) )
                {
                    new MMessageWarning(_M('Já existe uma parcela cadastra com o número do documento informado, por favor, altere o número do documento.'));
                    return;
                }
            }
        }

        $MIOLO->page->addJsCode("document.getElementById('itemSubdetail').value = '';");

        parent::addToTable($data);
    }

    public static function editFromTable($args)
    {
        $MIOLO = MIOLO::getInstance();

        $item = explode('&', $args);
        $itemSubdetail = explode('=',$item[1]);

        $MIOLO->page->addJsCode("document.getElementById('itemSubdetail').value = '{$itemSubdetail[1]}';");

        parent::editFromTable($args);
    }

    public function onLoad()
    {
        parent::onLoad();

        $tipo = $this->tipo;
        $tipo instanceof capsolicitacao;

        if ( $this->funcao == FUNCAO_EDITAR )
        {
            $tipo->verificaSePodeEditar();
        }

        foreach ( $this->fields as $field )
        {
            if ( $field->name == 'capsolicitacaoparcela_nossonumero' )
            {
                $field->hint = _M('Nosso número do boleto de cobrança');
            }
            else if ( $field->name == 'capsolicitacaoparcela_numerododocumento' )
            {
                $field->hint = _M('Número do documento do boleto de cobrança');
            }
        }
    }

    public function verificaPlanoDeContas()
    {
        $argumentos = $this->getAjaxData();
        $campos = array();

        $caprateio = new caprateio();

        // Se não tem rateio vigente
        if( !$caprateio->planoDeContasTemRateioVigente($argumentos->accountschemeid) )
        {
            // Campos do centro de custo
            $camposBusca = "costcenterid, description";
            $campos['costcenterid'] = new bEscolha("costcenterid", "capcentrodecusto", "contaspagar", null, _M("Centro de custo"), false, $camposBusca);

            if ( capconfiguracao::obterTipoSolicitacaoPagto() == capconfiguracao::NECESSITA_DEFERIMENTO )
            {
                $this->addValidator(new MRequiredValidator('costcenterid'));
            }
            
        }

        $this->setResponse(array($campos), "divRespostaAjax");
    }

    public function botaoSalvar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $existe = capsolicitacaocomprador::verificaFornecedorNotaFiscal($this->getData());

        if ( $existe == DB_TRUE )
        {
            $href = $MIOLO->getActionURL($module, 'main:process:capsolicitacaocomprador', null, array('chave' => 'capsolicitacaocomprador', 'funcao' => 'buscar', 'generico' => $this->getData()->numerodanotafiscal));
            $link = new MLink('_link', '', $href, 'aqui');

            new MMessageWarning(_M('Já existe uma nota fiscal cadastrada para este fornecedor. Clique ' . $link->generate() . ' para consultar.'));
        }
        else
        {
            parent::botaoSalvar_click();
            
            $argumentos = $this->getAjaxData();
            $caprateio = new caprateio();
            $accCost = new BusinessAccountancyBusCostCenter();
            $solicitacaoid = bTipo::instanciarTipo('capsolicitacao');
            $solicitacaoid = $solicitacaoid->obterUltimoIdInserido();
            //se plano conta ter rateio, adicionar chefes centro de custo da acccostcenter
            if( $caprateio->planoDeContasTemRateioVigente($argumentos->accountschemeid) )
            {
                $filtros = new stdClass();
                $filtros->accountschemeid = $argumentos->accountschemeid;
                $busca = $caprateio->buscar($filtros);
                $filtro = new stdClass();
                $filtro->rateioid = $busca[0]->rateioid;            
                $tipo = bTipo::instanciarTipo('caprateiocentrodecusto');
                $centrosdecustos = $tipo->buscar($filtro);

                foreach ($centrosdecustos as $centrosdecusto)
                {
                    $personidowner = $accCost->getCostCenter($centrosdecusto->costcenterid);
                    while(strlen($personidowner->personIdOwner) == 0)
                    {
                         $personidowner = $accCost->getCostCenter($personidowner->parentCostCenterId);
                    }
                    $data = new stdClass();
                    $data->personidowner = $personidowner->personIdOwner;
                    $data->costcenterid = $centrosdecusto->costcenterid;
                    $data->solicitacaoid = $solicitacaoid;
                    $solicitacaoCompra = new capsolicitacaocomprador();
                    $solicitacaoCompra->salvarContasPagarRateio($data);
                }
            }
        }
    }
}

?>