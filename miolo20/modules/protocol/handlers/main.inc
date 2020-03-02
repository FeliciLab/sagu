<?php
/**
 * <--- Copyright 2011-2011 de Facimed - Faculdade de Ciências Biométricas de Cacoal.
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
 * Protocol main handler.
 *
 * @author Alessandro Rodrigues da Cruz [cpd@facimed.edu.br]
 *
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 *
 * @version $Id$
 *
 *
 * @since
 * Class created on 16/03/2011
 */

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$function = MIOLO::_request('function');

if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

include_once($MIOLO->GetConf('home.modules') .'/sagu2/handlers/menu.inc');

// Inclui o manipulador
$type = MIOLO::_REQUEST("type", "GET");

if ( strlen($type) > 0 )
{
    $MIOLO->uses('handlers/manipulador.inc.php', 'base');
}
else
{
    $ui = $MIOLO->getUI();

    if ( !SAGU::invokeHandlerAuto() )
    {
        $MIOLO->checkAccess('PROTOCOL', A_ACCESS, true, true);

        $frmDiverseConsultation = $ui->getForm($module, 'FrmDiverseConsultation', $data);
        $frmDiverseConsultation->setClose($btnClose);

        if ( $theme->page->generateMethod != 'generateAJAX' )
        {
            $theme->clearContent();
            $theme->AppendContent($frmDiverseConsultation);
        }
    }
}
?>