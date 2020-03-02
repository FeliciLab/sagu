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
 *
 * @author William Prigol Lopes [william@solis.coop.br]
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
 * Class created on 07/12/2005
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess($module, A_ACCESS, true, true);

$function = MIOLO::_request('function');
if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

$home = 'main:process:remittanceReturn:returnAutomaticDebit';

if ( !$MIOLO->invokeHandler($module,'process/remittanceReturn/'.$context->shiftAction()) )
{
    $ui       = $MIOLO->getUI();
    $btnClose = $MIOLO->getActionURL($module, substr($home, 0, strrpos($home, ':')));

    $icon       = $ui->getImage($module, 'process-16x16.png');
    $pnlReturn = new MActionPanel('pnlReturn', _M('Retorno do débito automático', $module), null, $btnClose, $icon);
    $pnlReturn->setIconType('small');
    $pnlReturn->setControlSize(SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_WIDTH'), SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_HEIGHT'));
    $businessBanks = new BusinessFinanceBusBank();
    $banks = $businessBanks->listBank();

    // If exists banks registered search the classes available for that
    if (is_array($banks[0]))
    {
        $MIOLO->getClass($module, 'ReturnAutomaticDebit');
        $returnAutomaticDebit = new returnAutomaticDebit();
        foreach ($banks as $b)
        {
            if ($returnAutomaticDebit->returnExists($b[0]))
            {
                $items = array($b[1], 'returnAutomaticDebit-16x16.png', "$home:returnAutomaticDebit".$b[0]);
                $menuItem[] = $items;
            }
            unset($items);
        }
    }
   
    if (is_array($menuItem))
    {
        $sortMenuItem = SAGU::arraySort($menuItem, 0);
    
        foreach ( $sortMenuItem as $m )
        {
            $pnlReturn->addAction($m[0], $ui->getImage($module, $m[1]), $module, $m[2]);
        }

        $menu = array($pnlReturn);
    }
    else
    {
        $MIOLO->information(_M('Não há retornos para débitos automáticos instalados', $module), $btnClose);
    }
    $theme->clearContent();
    $theme->insertContent($menu);
}
?>
