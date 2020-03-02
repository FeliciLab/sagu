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
 * Handler base do modulo Resindecy (Residência)
 *
 * @author Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Fabiano Tomasini [fabiano@solis.coop.br]
 * Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 02/05/2011
 */
$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

$function = MIOLO::_request('function');
if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

//MIOLO2 menu
include_once($MIOLO->GetConf('home.modules') .'/sagu2/handlers/menu.inc');

// Inclui o manipulador
$type = MIOLO::_REQUEST("type", "GET");

if ( strlen($type) > 0 )
{
    $MIOLO->uses('handlers/manipulador.inc.php', 'base');
}
else
{
    SAGU::invokeHandlerAuto();
}
?>