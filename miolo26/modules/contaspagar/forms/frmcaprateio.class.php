<?php

/**
 * Formulário responsável pelo cadastro de Rateios
 * 
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 * 
 * \b Maintainers: \n
 * Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 *
 * @since
 * Classe criada em 07/05/2015
 */
$MIOLO->uses("classes/capformdinamico.class.php", "contaspagar");

class frmcaprateio extends capformdinamico
{

    public function __construct($parametros)
    {
        parent::__construct($parametros, _M("Rateio por plano de contas"));

    }

    public function definirCampos()
    {
        parent::definirCampos(TRUE);

        if ( $this->funcao == FUNCAO_INSERIR )
        {
            $this->addJsCode($this->getJSCorrigeLabels());
        }

    }

    private function getJSCorrigeLabels()
    {
        return "
            (function corrigeLabels() {
                var costCenter = document.getElementById('caprateiocentrodecusto_costcenterid');
                var percentualRateio = document.getElementById('caprateiocentrodecusto_parcentualrateio');
                
                if( costCenter !== null) {
                    var labelCostCenter = costCenter.parentNode.previousSibling;
                    if( labelCostCenter !== null && labelCostCenter.classList.contains('label') ) {
                        labelCostCenter.classList.add('mCaptionRequired');
                    }
                    
                    var labelPercentualRateio = percentualRateio.parentNode.previousSibling.firstChild.nextSibling;
                    
                    if( labelPercentualRateio !== null ) {
                        labelPercentualRateio.classList.add('mCaptionRequired');
                    }
                }
                else {
                    setTimeout(corrigeLabels, 10);
                }
                
            })();  
        ";

    }

    /**
     * Substitui o método definido no parent
     * 
     * Intercepta o evento se salvar as informações para realizar a validação dos
     * dados conforme a regra de negócios
     * 
     * @see capformdinamico::botaoSalvar_click()
     * 
     * @throws Exception Possíveis erros de validação
     */
    public function botaoSalvar_click()
    {
        if ( $this->validate() )
        {
            $data = $this->getData();

            // Valida primeiramente os dados informados pelo usuário
            if ( !$this->validaPeriodicidade($data->datainicial, $data->datafinal) )
            {
                throw new Exception(_M("A data inicial informada deve ser menor que a final. Por favor, verifique os dados informados."));
            }

            if ( !$this->validaPeriodoPlanoDeContas() && $this->funcao !== FUNCAO_EDITAR )
            {
                throw new Exception(_M("Já há um rateio vigente para o plano de contas informado. Por favor, edite o registro existente ou reformule este."));
            }

            if ( !$this->validaPercentuais() )
            {
                throw new Exception(_M("A soma dos percentuais de rateios distribuídos por centro de custo deve totalizar 100. Por favor, verifique os percentuais informados."));
            }
            
            if ( $this->haPlanoDeContaDuplicado() )
            {
                throw new Exception(_M("Há mais de um percentual informado para um mesmo centro de custo. Por favor, verifique os dados informados."));
            }
        }

        parent::botaoSalvar_click();

    }
    
    /**
     * Faz a soma dos percentuais informados pelo usuário e verifica se a soma
     * destes é igual a 100
     * 
     * @return Boolean TRUE caso a soma dos percentuais totalize 100, FALSE caso contrário
     */
    private function validaPercentuais()
    {
        $data = $this->getData();

        $totalizador = 0;

        foreach ( $data->caprateiocentrodecusto as $item )
        {
            // Faz a totalização dos percentuais informados
            $totalizador += floatval($item->parcentualrateio);
        }

        return $totalizador == 100;

    }

    /**
     * Valida o período informado pelo usuário para o plano de contas conforme
     * os períodos já registrados para o mesmo plano de contas
     * 
     * @return Boolean TRUE caso as datas informadas não conflitem com os registros
     * já existentes, FALSE caso contrário
     */
    private function validaPeriodoPlanoDeContas()
    {
        $tipo = $this->tipo;
        $tipo instanceof caprateio;

        $data = $this->getData();

        $filtros = new stdClass();
        $filtros->accountschemeid = $data->accountschemeid;

        // Faz a busca pelos registros com o plano de contas informados
        $busca = $tipo->buscar($filtros);

        // Valida conforme o banco de dados
        foreach ( $busca as $resultado )
        {
            if ( $this->dataEstaNoIntervalo($data->datainicial, $data->datafinal, $resultado->datainicial, $resultado->datafinal) )
            {
                // Não pode haver dois rateios vigentes para um mesmo plano de contas!
                return false;
            }
        }

        return true;

    }
    
    /**
     * Verifica se há planos de contas duplicados
     * 
     * @return Boolean Se há duplicação
     */
    private function haPlanoDeContaDuplicado()
    {
        $tipo = $this->tipo;
        $tipo instanceof caprateio;

        $data = $this->getData();
        
        $ids = $this->getIdsCentroDeCustos($data->caprateiocentrodecusto);
        
        return $this->arrayTemValoresDuplicados($ids);

    }
    
    /**
     * A partir dos dados da grid, extrai apenas os identificadores
     * 
     * @param Array $dadosGrid Dados da SubDetail
     * @return Array Com os identificadores
     */
    private function getIdsCentroDeCustos($dadosGrid)
    {
        $centroDeCustos = array();
        
        foreach( $dadosGrid as $dado )
        {
            $centroDeCustos[] = $dado->costcenterid;
            
        }
        
        return $centroDeCustos;
        
    }
    
    /**
     * Verifica se um array possui dados duplicados
     * 
     * @param Array $array Array para verificar
     * @return Boolean Se há duplicação
     */
    private function arrayTemValoresDuplicados($array)
    {
        return count(array_unique($array)) < count($array);
        
    }
        
    /**
     * Corrige o formato da data (dd/mm/yyyy) para o formato do banco de dados (yyyy-mm-dd)
     * 
     * @param String $data Data a ser formatada
     * 
     * @return String|NULL Data formatada, NULL caso a data informada seja inválida
     */
    private function getDataFormatoCorreto($data)
    {
        if ( $data )
        {
            return date('Y-m-d', strtotime(str_replace('/', '-', $data)));
        }

        return NULL;

    }

    /**
     * Verifica se um dado período inicial intercepta um dado período final
     * 
     * @param String $datainicial Data inicial do período inicial
     * @param String $datafinal Data final do período inicial
     * @param String $cdatainicial Data inicial do período final
     * @param String $cdatafinal Data final do período final
     * 
     * @return Boolean TRUE caso a data esteja no dado intervalo e FALSE caso contrário
     */
    private function dataEstaNoIntervalo($datainicial, $datafinal, $cdatainicial, $cdatafinal)
    {
        $dinicial = $this->getDataFormatoCorreto($datainicial);
        $dfinal = $datafinal ? $this->getDataFormatoCorreto($datafinal) : 'infinity';
        $cdinicial = $this->getDataFormatoCorreto($cdatainicial);
        $cdfinal = $cdatafinal ? $this->getDataFormatoCorreto($cdatafinal) : 'infinity';

        $sql = "SELECT (DATE '{$dinicial}', DATE '{$dfinal}') OVERLAPS (DATE '{$cdinicial}', DATE '{$cdfinal}');";

        $retorno = SDatabase::query($sql);

        return ($retorno[0][0] == DB_TRUE);

    }

    /**
     * Valida se o período informado é valido e crescente (data inicial < data final)
     * 
     * @param String $datainicial Data inicial informada pelo usuário
     * @param String $datafinal Data final informada pelo usuário
     * 
     * @return Boolean TRUE caso seja válido e FALSE caso contrário
     */
    private function validaPeriodicidade($datainicial, $datafinal)
    {
        $dataiinformada = strtotime($this->getDataFormatoCorreto($datainicial));
        $datafinformada = strtotime($this->getDataFormatoCorreto($datafinal));

        if ( $datafinformada )
        {
            if ( $dataiinformada > $datafinformada )
            {
                return false;
            }
        }
        
        return true;

    }

}

?>