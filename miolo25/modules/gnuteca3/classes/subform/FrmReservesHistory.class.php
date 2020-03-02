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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 22/10/2008
 *
 **/
class FrmReservesHistory extends GSubForm
{
    public $MIOLO;
    public $module;
    public $busAthenticate;
    public $busLoanType;
    public $busLibraryUnit;
    public $busReserveStatus;

    public function __construct()
    {
    	$this->MIOLO 	= MIOLO::getInstance();
    	$this->module 	= MIOLO::getCurrentModule();
        $this->business = $this->MIOLO->getBusiness( $this->module, 'BusReserve');
    	$this->busAthenticate = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busLoanType = $this->MIOLO->getBusiness( $this->module, 'BusLoanType');
        $this->busReserveStatus = $this->MIOLO->getBusiness($this->module, 'BusReserveStatus');
        $this->busLibraryUnit = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');

        $this->business->isFormSearch = TRUE;
        $this->gridSearchMethod = 'searchReserve';
    	$this->gridName = 'GrdReservesHistory';

    	parent::__construct( _M('Histórico de reservas', $this->module) );

    	if ( $this->firstAccess() )
    	{
            GForm::setFocus('libraryUnitIdS',false);
    	}
    }

    public function createFields()
    {
        $this->busLibraryUnit->onlyWithAccess  = true;
        //foi feito com container, pois tinha problema de layout no chrome
        $libraryUnitIds = new GSelection('libraryUnitIdS', null, null, $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
        $fields[] = new GContainer('',array( new MLabel( _M('Unidade de biblioteca', $this->module) ), $libraryUnitIds) );

        $lblDate = new MLabel(_M('Data da requisição', $this->module) . ':');
        $beginRequestedDateS_DATE = new MCalendarField('beginRequestedDateSDate');
        $endRequestedDateS_DATE = new MCalendarField('endRequestedDateSDate');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginRequestedDateS_DATE, $endRequestedDateS_DATE));
        $validators[] = new MDateDMYValidator('beginRequestedDateSDate');

        $lblDate = new MLabel(_M('Data limite', $this->module)  . ':');
        $beginLimitDateS = new MCalendarField('beginLimitDateS');
        $endLimitDateS = new MCalendarField('endLimitDateS');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginLimitDateS, $endLimitDateS));
        $validators[] = new MDateDMYValidator('beginLimitDateS');
        $reserveStatuId = new GSelection('reserveStatusIdS', null, null, $this->busReserveStatus->listReserveStatus());
        $fields[] = new GContainer('', array(new MLabel(_M('Estado da reserva', $this->module)),$reserveStatuId));

        $this->setFields( $fields , true);
    }

    public function getData()
    {
        $data = parent::getData();
        $data->personIdS = BusinessGnuteca3BusAuthenticate::getUserCode();

        return $data;
    }
}
?>