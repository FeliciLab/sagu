<?php
/**
 * Gnuteca Default Tesk Testing
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


class informarSolicitanteSobreTerminoRequisicao extends GTask
{

    public $busReqChanExeSts, $busLibraryUnit;


    /**
     * METODO CONSTRUCT É OBRIGATÓRIO, POIS A CLASSE DE SCHEDULE TASK SEMPRE VAI PASSAR O $MIOLO COMO PARAMETRO
     *
     * @param OBJECT $MIOLO
     */
    function __construct($MIOLO, $myTaskId)
    {
        parent::__construct($MIOLO, $myTaskId);

        $this->busReqChanExeSts = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatus');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->MIOLO->getClass($this->module, 'GSendMail');
        $this->mail = new GSendMail();
    }


    /**
     * MÉTODO OBRIGATORIO.
     * ESTE METODO SERA CHAMADO PELA CLASSE SCHEDULE TASK PARA EXECUTAR A TAREFA
     *
     * @return boolean
     */
    public function execute()
    {
        //Obtem tempo de antecedencia através da preferencia.
        $period = $this->busReqChanExeSts->getPeriodInterval();
        $antecedencia = strlen($this->parameters[0]) ? $this->parameters[0] : $period->requestChangeDays;
        
        //se tiver parametros seta a unidade
        if ( strlen($this->parameters[1]) > 0 )
        {
	        if ( strstr($this->parameters[1], ',') )
	        {
	            $libraries = explode(',', $this->parameters[1]);
	        }
	        else 
	        {
	            $libraries = $this->parameters[1];
	        }
        }
        else
        {
             $libraries = $this->busLibraryUnit->searchLibraryUnit();
             $libraries2 = array();
             foreach($libraries as $library)
             {
             	$libraries2[] = $library[0];
             }
             $libraries = $libraries2;
        	
        } 
        
        $this->busReqChanExeSts->notifyEndRequest($antecedencia, $libraries);
        return true;
    }

}

?>