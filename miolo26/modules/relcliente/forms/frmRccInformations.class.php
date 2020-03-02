<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rccInfos
 *
 * @author artur
 */

$MIOLO->uses('forms/frmDinamico.class.php', 'base');

class frmRccInformations extends frmDinamico 
{
    public $fonts;

    public function __construct()
    {
        parent::__construct(FALSE, 'Informações');
    }

    public function definirCampos()
    {
        parent::definirCampos(TRUE, TRUE);
    }

    public function createFields()
    {
        $tipo = bTipo::instanciarTipo('acdlearningperiod', 'relcliente');
        $module = MIOLO::getCurrentModule();
        
        $fields[] = new MLabel(_M('endereço para cadastro de Interesse: http://127.0.0.1/sagu/miolo26/html/index.php?module=relcliente&action=main:register:interessePortal&chave=rccInteressePortal&funcao=inserir', $module), '', true);
        $fields[] = new MLabel(_M('endereço para Ouvidoria: http://127.0.0.1/sagu/miolo26/html/index.php?module=relcliente&action=main:infos:ouvidoriaPortal&chave=rccMensagemOuvidoriaPortal&funcao=inserir', $module), '', true);
        
        $this->setFields($fields);
        $this->defaultButton = false;
    }


}

?>
