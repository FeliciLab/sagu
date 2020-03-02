<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('classes/capformdinamico.class.php', 'contaspagar');

class frmcaplancamento extends capformdinamico
{
    public function __construct($parametros, $titulo = NULL)
    {
        parent::__construct($parametros, _M('Alteração de títulos'));
    }
    

    public function definirCampos() 
    {
        $MIOLO = MIOLO::getInstance();
        
        parent::definirCampos(FALSE);

        $lista = array('lancamentoid', 'valor');
        list($campos, $validadores) = $this->gerarCamposEspecificos($lista);

        $this->addFields($campos);
        $this->setValidators($validadores);
    }
    
    public function botaoSalvar_click()
    {
        $tipo = $this->tipo;
        $tipo instanceof captitulo;
        
        $data = $this->getData();
        
        $tipo->valorpgto = $data->valorpgto;
        $tipo->speciesid = $data->speciesid;
        
        parent::botaoSalvar_click();
    }
    
    public function onLoad()
    {
        parent::onLoad();
     
        $busOpenCounter = new BusinessFinanceBusOpenCounter();
        $openCounter = $busOpenCounter->getCurrentOpenCounterLogged();
        
        if ( !$openCounter )
        {
            throw new Exception('Não é possível registrar pagamento pois não há um caixa aberto para o operador logado.');
        }
    }
}

?>