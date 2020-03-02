<?php
/**
 * Finaliza solicitações catalogadas
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
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
class finalizePurchaseRequest extends GTask
{
    function __construct($MIOLO, $myTaskId)
    {
        parent::__construct($MIOLO, $myTaskId);
    }

    public function execute()
    {
        $MIOLO = $this->MIOLO;
        $MIOLO->getClass( 'gnuteca3', 'workflow/wfPurchaseRequest' );
        return wfPurchaseRequest::finalizePurchaseRequestTaks();
    }
}
?>