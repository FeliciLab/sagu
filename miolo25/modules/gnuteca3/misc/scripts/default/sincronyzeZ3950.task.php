<?php
/**
  *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Class created on 19/09/2011
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

//var_dump($_SERVER);
class sincronyzeZ3950 extends GTask
{
    public $MIOLO;
    public $module;

    public function __construct($MIOLO, $myTaskId)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        parent::__construct($MIOLO, $myTaskId);
    }

    public function execute()
    {
        $this->isRunningFromGCron();
        
        $this->MIOLO->getClass('gnuteca3', 'GZ3950');
        $this->MIOLO->getClass('gnuteca3', 'GZebra');
        
        $zebra = new GZebra();
        $ok = $zebra->deleteDatabase();
       
        if ( !$ok )
        {
            return false;
        }
        
        $busMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $busMaterialControl = new BusinessGnuteca3BusMaterialControl();
        $materialList = $busMaterialControl->listALlControlNumbers();
       
        //salva no servidor Z3950
        if ( defined( 'Z3950_SERVER_URL' ) && $materialList )
        {
            $z3950 = new GZ3950( Z3950_SERVER_URL, Z3950_SERVER_USER, Z3950_SERVER_PASSWORD );
            
            try
            {
                foreach ( $materialList as $line => $info )
                {
                    $ok = $z3950->insertOrUpdate( $info[0] );
                }
            }
            catch ( Exception $e)
            {
                
            }
        }
        else
        {
            throw new Exception("Preferência Z3950_SERVER_URL não está ativada ou não existem materiais para sincronizar.");
        }
       
        return true;
    }
}
?>