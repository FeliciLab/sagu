<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * Exam occurrence handler.
 * Contains the menus to access exam occurrence registers
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 14/09/2005
 *
 **/
$label  = _M('Pessoa', $module);
$form   = 'Person';
$home   = 'main:register:person';
$data->home = $home;
$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption($label, $module, $home);
$MIOLO->getClass('basic', 'access');
access::insert($label, $home, 'copy-16x16.png');
$function = MIOLO::_request('function');
$ui = $MIOLO->getUI();
if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmPersonControlCopies', A_ACCESS, true, true);
    
    $frmSearch  = $ui->getForm($module,"Frm$form".'Search', $data);
    $frmSearch->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
    $content        = $frmSearch;
}
else
{
    switch ( $function )
    {
        case 'insert':
            $MIOLO->checkAccess('FrmPersonControlCopies', A_INSERT, true, true);
        break;
        case 'update':
            if (strlen(MIOLO::_request('event')) > 0)
            {
                $MIOLO->checkAccess('FrmPersonControlCopies', A_UPDATE, true, true);
            }
            else
            {
                $MIOLO->checkAccess('FrmPersonControlCopies', A_ACCESS, true, true);
            }
        break;
        case 'delete':
            $MIOLO->checkAccess('FrmPersonControlCopies', A_DELETE, true, true);
        break;
    }
    $frm = $ui->getForm($module, "Frm$form");
    $content = $frm;
}
if ($theme->page->generateMethod != 'generateAjax')
{
    $theme->clearContent($content);
    $theme->insertContent($content);
}
?>
