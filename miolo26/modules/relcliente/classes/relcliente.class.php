<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of relcliente
 *
 * @author augusto
 */
class relcliente 
{
    /**
     * Retorna se esta em uma acao (action) permitida, que nao requere autenticacao no módulo.
     * Ex.: Inscricao processo seletivo
     *
     * @return boolean
     */
    public static function isAllowedAction()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $allowedActionsRelcliente = array(
            'main:process:mensagemDeOuvidoriaPortal'
        );

        return ($module == 'relcliente' && (in_array($action, $allowedActionsRelcliente)));
    }
}

?>
