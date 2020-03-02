<?php

/**
 * @author Bruno Edgar Fuhr [bruno@solis.com.br]
 *
 * @since
 * Class created on 12/06/2014
 */

$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
class frmRccContatoBusca extends frmDinamicoBusca 
{
    
    public function gerarFiltrosEColunas()
    {
        $filtrosEColunas = parent::gerarFiltrosEColunas();
        
        $filtrosEColunas[0] = array(
            new bEscolha('tipodecontatoid', 'rccTipoDeContato','relcliente', null, 'Tipo de contato'),
            new MDiv(),
            new MDiv(),
            new MDiv(),
            new MDiv()
        );
        
        return $filtrosEColunas;
    }

}
?>
