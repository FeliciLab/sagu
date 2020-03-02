<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of frmProtocoloCoordenador
 *
 * @author augusto
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
class frmProtocoloCoordenador extends frmMobile
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtUsuario.class.php', $module);
        
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Coordenador'), MIOLO::getCurrentModule());
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $busTransaction = new BusinessAdminTransaction();
        $MIOLO->uses('classes/prtUsuario.class.php', $module);
        
        $panel = new mobilePanel('panel');

        if ( $MIOLO->checkAccess('FrmProtocoloEncaminhamento', A_ACCESS, FALSE) )
        {
            $panel->addAction($busTransaction->getTransactionName('FrmProtocoloEncaminhamento'), $ui->getImageTheme($module, 'lists.png'), $module, 'main:protocoloencaminhamento');
        }
        
        if ( $MIOLO->checkAccess('FrmProtocoloSolicitacao', A_ACCESS, FALSE) )
        {
            $panel->addAction($busTransaction->getTransactionName('FrmProtocoloSolicitacao'), $ui->getImageTheme($module, 'protocol.png'), $module, 'main:protocolo');
        }
        
        $fields[] = $panel;
        
	parent::addFields($fields);
    }
}

?>
