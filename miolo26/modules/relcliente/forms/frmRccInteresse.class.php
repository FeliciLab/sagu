<?php

/**
 *
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 13/11/2012
 */

$MIOLO->uses('forms/frmDinamico.class.php', 'base');
class frmRccInteresse extends frmDinamico
{
    
    public function definirCampos() 
    {
        parent::definirCampos(FALSE);
        
        $camposEValidadores = $this->gerarCampos();
        $campos = $camposEValidadores[0];
        $validadores = $camposEValidadores[1];
        unset($campos['datahora']);
        unset($campos['rcccontatocontatoid']);
        unset($campos['cursoid']);
        unset($campos['contratoid']);
        
        $campos['nome'] = new MTextField('nome', '', _M('Nome'), 40);
        $campos['telefone'] = new MTextField('telefone', '', _M('Telefone'), 20);
        $campos['email'] = new MTextField('email', '', _M('E-mail'), 40);
        $campos['cpf'] = new MTextField('cpf', '', _M('CPF'), 15);
        $campos['observacao'] = new MMultiLineField('observacao', '', _M('Observação'), 20, 10, 80);
        
        $campos[] = $estaCancelada = new MTextField('datahora', date("d/m/Y H:i:s"));
        $estaCancelada->addStyle('display', 'none');
        
        $validadores[] = new MPhoneValidator('telefone', 'telefone');
        $validadores[] = new MEmailValidator('email', 'email');
        $validadores[] = new MCPFValidator('cpf', _M('CPF'));
                
        $this->addFields($campos);
        $this->setValidators($validadores);
        
    }
}

?>