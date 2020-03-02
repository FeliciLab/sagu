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
 * Penalty form
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
 *
 * @since
 * Class created on 23/09/2008
 *
 **/


/**
 * Form to manipulate a preference
 **/
class FrmPenalty extends GForm
{
    public $MIOLO;
    public $module;
    public $businessLibraryUnit;


    function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->businessLibraryUnit = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');

        $this->setAllFunctions('Penalty', 'penaltyId');
        $this->setPrimaryKeys('penaltyId');
        $this->setSaveArgs(array('personId', 'operator'));
        parent::__construct();
       	$this->setFocus('personId');
    }


    public function mainFields()
    {
        if ($this->function != 'insert')
        {
            $penaltyId      = new MTextField('penaltyId', $this->penaltyId->value, _M('Código', $this->module), FIELD_ID_SIZE);
            $penaltyId->setReadOnly(TRUE);
            $fields[]       = $penaltyId;
            $validators[]   = new MIntegerValidator('penaltyId', null, 'required');
        }
        else
        {
            $operatorValue  = $this->MIOLO->getLogin()->id;
        }
        
        $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');
        
        $validators[] = new MIntegerValidator('personId', _M('Pessoa', $this->module), 'required');

        $observation = new MMultiLineField('observation', $this->observation->value, _M('Observação', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $observation->hint = _M('Este campo é visível para os estudantes em seus históricos', $this->module);
        $fields[] = $observation;
        $validators[] = new MRequiredValidator('observation');

        $internalObservation = new MMultiLineField('internalObservation', $this->internalObservation->value, _M('Observação interna', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = $internalObservation;

        $penaltyDate = new MCalendarField('penaltyDate', $this->penaltyDate->value, _M('Data da penalidade', $this->module), FIELD_DATE_SIZE, null);
        $fields[] = $penaltyDate;
        $validators[] = new MDateDMYValidator('penaltyDate', null);
        $validators[] = new MRequiredValidator('penaltyDate');

        $penaltyEndDate = new MCalendarField('penaltyEndDate', $this->penaltyEndDate->value, _M('Data final de penalidade', $this->module), FIELD_DATE_SIZE, null);
        $fields[] = $penaltyEndDate;
        $validators[] = new MDateDMYValidator('penaltyEndDate', null);

        $operator = new MTextField('operator', GOperator::getOperatorId(), _M('Operador', $this->module));
        $operator->setReadOnly(true);
        $fields[]       = $operator;
        $validators[]   = new MRequiredValidator('operator');

        $this->businessLibraryUnit->filterOperator = TRUE;
        $fields[]       = new GSelection('libraryUnitId',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->businessLibraryUnit->listLibraryUnit());

        $this->setFields($fields);
        $this->setValidators($validators);

        if ($this->function == 'insert')
        {
            $this->penaltyDate->value = GDate::now()->getDate(GDate::MASK_DATE_DB);
        }
    }

}
?>
