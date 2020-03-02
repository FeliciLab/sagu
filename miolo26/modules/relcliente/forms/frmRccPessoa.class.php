<?php

/**
 *
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 02/01/2013
 */

$MIOLO->uses('forms/frmDinamico.class.php', 'base');
$MIOLO->uses('tipos/rccBasPerson.class.php', 'relcliente');

class frmRccPessoa extends frmDinamico
{
    /**
    *Classe criada para ser o acesso a informações padrão da PESSOA
    *
    */
    public function definirCampos()
    {
        
        parent::definirCampos(FALSE);
        
        $camposEValidadores = $this->gerarCampos();
        $campos = $camposEValidadores[0];
        $validadores = $camposEValidadores[1];
        unset($campos['username']);
        unset($campos['datetime']);
        unset($campos['ipaddress']);
        unset($campos['persondv']);
        unset($campos['personmask']);
        unset($campos['shortname']);
        unset($campos['cityid']);
        unset($campos['url']);
        unset($campos['datein']);
        unset($campos['password']);
        unset($campos['isallowpersonaldata']);
        unset($campos['miolousername']);
        unset($campos['locationtypeid']);
        unset($campos['sentemail']);
        unset($campos['photoid']);
              

        $this->addFields($campos);
        $this->setValidators($validadores);

    }
}

?>