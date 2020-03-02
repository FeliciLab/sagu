<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 03/08/2008
 *
 **/

/**
 * Class to manipulate
 **/

class BusinessGnuteca3BusMyReserves extends GBusiness
{
    /**
     * Attributes
     */

    public $MIOLO;


    public $businessReserve;
    public $businessAutheticate;
    public $businessReserveComposition;
    public $businessExemplary;
    public $businessMaterial;
    public $busSearchFormat;
    public $busLoan;
    public $busAuthenticate;

    /**
     * Constructor Method
     */
    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->businessReserve              = $this->MIOLO->getBusiness('gnuteca3', 'BusReserve');
        $this->businessReserveComposition   = $this->MIOLO->getBusiness('gnuteca3', 'BusReserveComposition');
        $this->businessAutheticate          = $this->MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');
        $this->businessExemplaryControl     = $this->MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
        $this->businessMaterial             = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterial');
        $this->busSearchFormat              = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busLoan                      = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busAuthenticate              = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
    }


    /**
     * Retorna as reservas de um determinado usuario
     *
     * @return Array
     */
    public function getMyReserves()
    {
        $this->businessReserve->personIdS           = $this->businessAutheticate->getUserCode();
        $this->businessReserve->reserveStatusIdS    = array(ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED);

        $user   = $this->busAuthenticate->getUserCode();
        $result = $this->businessReserve->searchReserve("RS.description");
        $return = array();

        if($result)
        {
            foreach($result as $i => $v)
            {
                $this->businessReserveComposition->reserveIdS   = $v[0];
                //$this->businessReserveComposition->isConfirmedS = 't';
                $item   = $this->businessReserveComposition->searchReserveComposition();
                $ex     = array();
            	$title  = '';
            	$author = '';

                if($item)
                {
                    foreach($item as $i2 => $v2)
                    {
                        $exemplary  = $this->businessExemplaryControl->getExemplaryControl($v2[1]);
                        if(!$exemplary)
                        {
                            continue;
                        }
                        if (!$title)
                        {
                            $title = $this->busSearchFormat->getFormatedString($exemplary->controlNumber, FAVORITES_SEARCH_FORMAT_ID);
                        }
                        $answered = GUtil::getYesNo($v2[2]);
		                $ex[$i2]->controlNumber = $exemplary->controlNumber;
		                $ex[$i2]->itemNumber    = $exemplary->itemNumber;
		                $ex[$i2]->answered      = $answered;

                        //pega data prevista de devolução
                        $returnForecastDate = $this->busLoan->getReturnForecastDateFromItemNumber($exemplary->itemNumber);
                        $returnForecastDate = GDate::construct($returnForecastDate)->getDate(GDate::MASK_DATE_USER);
                        
                        //posição do usuário na fila de reserva
                        $position = $this->businessReserve->getReservePosition($v[0]);
                        
                        if ($position[0][0] == 0)
                        {
                            $positionReserve = 'Aguardando retirada'; //Quando reserva atendida
                        }
                        else
                        {
                        $positionReserve = $position[0][0] . 'º'; //Quando reserva solicitada, retorna a posição
                        }    
                    }
                }

                $return[$i] = array
                (
                    $v[0],
                    $title,
                    $author,
                    GDate::construct($v[5])->getDate(GDate::MASK_DATE_USER),
                    GDate::construct($v[6])->getDate(GDate::MASK_DATE_USER),
                    $v[7],
                    $positionReserve,
                    $returnForecastDate,
                    $v[9]
                );
            }
        }
        
        return $return;
    }

}
?>
