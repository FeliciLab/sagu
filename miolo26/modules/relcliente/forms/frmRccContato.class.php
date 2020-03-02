<?php

/**
 *
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 13/11/2012
 */
$MIOLO->uses('forms/frmDinamico.class.php', 'base');
class frmRccContato extends frmDinamico
{    
    public function definirCampos() 
    {
        $MIOLO = MIOLO::getInstance();
        parent::definirCampos(FALSE);
        
        $camposEValidadores = $this->gerarCampos();
        $campos = $camposEValidadores[0];
        $validadores = $camposEValidadores[1];
        
        //campo bEscolha, dinamicamente, escolhe a primeira coluna depois do ID para se relacionar.
        //neste caso, a coluna 'name' não está logo depois da coluna 'personid', por isso esta classe não é dinâmica
        $campos['pessoa'] = NULL;
        $camposBusca = 'personid, name';
        $campos['pessoa'] = new bEscolha('pessoa', 'basperson', 'relcliente', null, 'Pessoa', false, $camposBusca);
        
        
        $campos['assuntodecontato'] = NULL;
        $camposBusca = 'assuntodecontatoid, descricao';
        $campos['assuntodecontato'] = new bEscolha('assuntodecontato', 'rccassuntodecontato', 'relcliente', null, 'Assunto', false, $camposBusca);
        
        $campos['operador'] = NULL;
        $campos['operador'] = $hide = new MTextField( 'operador', $MIOLO->getLogin()->id );
        $hide->addStyle('display', 'none');
        
        $campos['rcccontatocontatoid'] = NULL;

        $this->addFields($campos);
        $this->setValidators($validadores);
        
    }
}

?>
