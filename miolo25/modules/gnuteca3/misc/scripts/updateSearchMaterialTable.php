<?php
/**
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/11/2010
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The Gnuteca3 Development Team
 *
 * \b CopyLeft: \n
 * CopyLeft (L) 2010 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html
 *
 * \b History: \n
 * See history in SVN repository: http://gnuteca.solis.coop.br
 *
 */
global $busUpdateSearch;
include ('iniciaMiolo.php');
$busUpdateSearch = $MIOLO->getBusiness($module, 'BusUpdateSearch');

function updateTable()
{
    global $busUpdateSearch;

    return $busUpdateSearch->updateSearch();
}

//se passar parâmetro true, atualiza executa a tarefa que atualiza a tabela de pesquisa
if ( $_SERVER['argv'][1] == 'true' )
{
    updateTable();
}
?>
