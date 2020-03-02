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
 * Preference search form
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
 * Class created on 28/07/2008
 *
 **/
class FrmRenewSearch extends GForm
{
	public $MIOLO;
	public $module;
	public $busLibraryUnit;
    public $busRenewType;


    public function __construct($data = null)
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busRenewType   = $this->MIOLO->getBusiness($this->module, 'BusRenewType');
        $this->setAllFunctions('Renew', array('loanIdS', 'renewIdS'),array('loanId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $validators[]   = new MIntegerValidator('renewIdS');
        $fields[]       = new MTextField('renewIdS', $this->renewIdS->value, _M('Código', $this->module), FIELD_ID_SIZE);

        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'Person');

        $loanIdLabelS = new MLabel(_M('Código do empréstimo', $this->module) . ':');
        $loanIdLabelS->setWidth(FIELD_LABEL_SIZE);
        $loanIdS = new GLookupTextField('loanIdS', '', '', FIELD_LOOKUPFIELD_SIZE);
        $loanIdS->setContext($this->module, $this->module, 'Loan', 'filler', 'loanIdS,loanIdDescriptionS,returnForecastDateS', '', true);
        $loanIdS->baseModule = $this->module;
        $loanIdDescriptionS = new MTextField('loanIdDescriptionS', $this->loanIdDescriptionS, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $loanIdDescriptionS->setReadOnly(true);
        $validators[] = new MIntegerValidator('loanIdS');
        $fields[] = new GContainer('loanIdContainerS', array($loanIdLabelS, $loanIdS, $loanIdDescriptionS));
        $fields[] = new GSelection('renewTypeIdS', $this->renewTypeIdS->value, _M('Tipo de renovação', $this->module), $this->busRenewType->listRenewType());

        $this->busLibraryUnit->filterOperator = TRUE;
        $this->busLibraryUnit->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);

        $lblDate             = new MLabel(_M('Data de renovação', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginRenewDateS     = new MCalendarField('beginRenewDateS', $this->beginRenewDateS->value, null);
        $endRenewDateS       = new MCalendarField('endRenewDateS', $this->endRenewDateS->value, null);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginRenewDateS, $endRenewDateS));

        $lblDate                   = new MLabel(_M('Data prevista da devolução', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginReturnForecastDateS  = new MCalendarField('beginReturnForecastDateS', $this->beginReturnDateS->value, null, FIELD_DATE_SIZE);
        $endReturnForecastDateS    = new MCalendarField('endReturnForecastDateS', $this->endReturnForecastDateS->value, null, FIELD_DATE_SIZE);

        $fields[] = new GContainer('hctDates', array($lblDate, $beginReturnForecastDateS, $endReturnForecastDateS));
        $fields[] = new MTextField('operatorS', $this->operatorS->value, _M('Operador', $this->module), 30);
        $fields[] = new MTextField('itemNumberS', NULL, _M('Número do exemplar', $this->module), FIELD_ID_SIZE);

        $this->setFields($fields);
        $this->setValidators( $validators );
    }
}
?>
