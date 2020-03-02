<?php
/**
 * run Schedule Tasks
 *
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2009
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The Gnuteca3 Development Team
 *
 * \b CopyLeft: \n
 * CopyLeft (L) 2007 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html
 *
 * \b History: \n
 * See history in SVN repository: http://gnuteca.solis.coop.br
 *
 */


include('iniciaMiolo.php');
$MIOLO->getClass($module, 'GTask');
$busScheduleTask = $MIOLO->getBusiness($module, 'BusScheduleTask');
$busScheduleTask->setScriptPath($mioloPath . '/modules/gnuteca3/misc/scripts');


$busScheduleTask->executeAllTasks(isset($_SERVER['argv'][1]));

?>
