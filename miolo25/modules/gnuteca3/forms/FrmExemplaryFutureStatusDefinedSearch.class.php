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
 * ExemplaryFutureStatusDefined search form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 21/10/2008
 *
 **/

class FrmExemplaryFutureStatusDefinedSearch extends GForm
{
    public $busExemplaryStatus;
    public $busLibraryUnit;


    public function __construct()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->busLibraryUnit     = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $save_args = array('exemplaryStatusId', 'itemNumber', 'operator');
        $this->setAllFunctions('ExemplaryFutureStatusDefined', $save_args, 'exemplaryFutureStatusDefinedId');
        parent::__construct();
    }


    public function mainFields()
    {
        $this->busLibraryUnit->filterOperator = TRUE;
        $this->busLibraryUnit->labelAllLibrary = TRUE;

        $fields[]       = new MTextField('exemplaryFutureStatusDefinedIdS', $this->exemplaryFutureStatusDefinedIdS->value, _M('Código', $this->module), FIELD_ID_SIZE);
        $validators[]   = new MIntegerValidator('exemplaryFutureStatusDefinedIdS');
        $fields[]       = new GSelection('exemplaryStatusIdS', $this->exemplaryStatusIdS->value, _M('Estado do exemplar', $this->module), $this->busExemplaryStatus->listExemplaryStatus());
        $fields[]       = new GSelection('libraryUnitIdS',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);
        $fields[]       = new MTextField('itemNumberS', $this->itemNumberS->value, _M('Número do exemplar', $this->module), FIELD_ID_SIZE);
        $fields[]       = new GSelection('appliedS', $this->appliedS->value, _M('Aplicado', $this->module), GUtil::getYesNo());
        $lblDate        = new MLabel(_M('Data', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginDateS     = new MCalendarField('beginDateS', $this->beginDateS->value, null, FIELD_DATE_SIZE);
        $endDateS       = new MCalendarField('endDateS', $this->endDateS->value, null, FIELD_DATE_SIZE);
        $fields[]       = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));
        $fields[]       = new MTextField('operatorS', $this->operatorS->value, _M('Operador', $this->module), 30);
        $fields[]       = new MTextField('observationS', $this->observationS->value, _M('Observação', $this->module), 30);
        $fields[]       = new MTextField('cancelReserveEmailObservationS', $this->cancelReserveEmailObservationS->value, _M('Observação do e-mail de cancelamento de reserva', $this->module), 30);

        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>