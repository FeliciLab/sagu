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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 21/10/2008
 *
 * */
class FrmExemplaryFutureStatusDefined extends GForm
{

    public $busExemplaryControl;
    public $busExemplaryStatus;
    public $busLibraryUnit;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $save_args = array('exemplaryStatusId', 'itemNumber', 'operator');
        $this->setAllFunctions('ExemplaryFutureStatusDefined', $save_args, array('exemplaryFutureStatusDefinedId'), $save_args);
        parent::__construct();
    }

    public function mainFields()
    {
        if ($this->function != 'insert')
        {
            $exemplaryFutureStatusDefinedId = new MTextField('exemplaryFutureStatusDefinedId', $this->exemplaryFutureStatusDefinedId->value, _M('Código', $this->module), FIELD_ID_SIZE);
            $exemplaryFutureStatusDefinedId->setReadOnly(true);
            $fields[] = $exemplaryFutureStatusDefinedId;
        }

        $statusList = $this->busExemplaryStatus->listExemplaryStatus(null, true);

        if (GPerms::checkAccess('gtcMaterialMovementChangeStatusInitial', null, false))
        {
            $levelStatus['level0'] = _M('Estado Anterior', $this->module);
            $statusList = array_merge($levelStatus, $statusList);
        }

        $fields[] = new GSelection('exemplaryStatusId', $this->exemplaryStatusId->value, _M('Estado do exemplar', $this->module), $statusList, false, false, false, false);
        $fields[] = new MTextField('itemNumber', $this->itemNumber->value, _M('Número do exemplar', $this->module), FIELD_ID_SIZE);
        $fields[] = new GRadioButtonGroup('applied', _M('Aplicado', $this->module), GUtil::listYesNo(1), DB_FALSE);
        $fields[] = new MCalendarField('date', GDate::now()->getDate(GDate::MASK_DATE_DB), _M('Data', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MTextField('operator', GOperator::getOperatorId(), _M('Operador', $this->module), null, nul, true);
        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = new MMultiLineField('cancelReserveEmailObservation', null, _M('Observação do e-mail de cancelamento de reserva', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        
        $this->setFields($fields);

        $validators[] = new MDateDMYValidator('date');
        $validators[] = new MRequiredValidator('exemplaryStatusId');
        $validators[] = new MRequiredValidator('itemNumber');

        $this->setValidators($validators);

        //FIXME feito dessa forma pois não consigue definir pelo miolo em tempo curto ( a 3.3 vai pro ar em seguida).
        if (MIOLO::_REQUEST('function') == 'insert')
        {
            $this->jsSetChecked('applied_1', true);
        }
    }

    public function tbBtnSave_click($sender)
    {
        $ec = $this->busExemplaryControl->getExemplaryControl($sender->itemNumber);
        if (!$ec)
        {
            throw new Exception(_M('Número de exemplar inexistente', $this->module)) ;
        }
        $this->busLibraryUnit->filterOperator = TRUE;
        $libraries = $this->busLibraryUnit->listLibraryUnit();
        $hasAccess = FALSE;

        foreach ((array) $libraries as $v)
        {
            $libraryUnitId = $v[0];
            if ($ec->libraryUnitId == $libraryUnitId)
            {
                $hasAccess = TRUE;
            }
        }
        if (!$hasAccess)
        {
            $errors[] = _M('Você não tem permissão para este número do exemplar', $this->module);
        }

        parent::tbBtnSave_click($sender, null, $errors);
    }

    //Sobreescreve as funções pois deve tratar a volta para busca quando selecionado o "Estado anteior"
    public function replaceURL($url)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $busExemplaryStatusHistory = $MIOLO->getBusiness($module, 'BusExemplaryStatusHistory');

        if (MIOLO::_REQUEST('exemplaryStatusId') == 'level0')
        {
            $futureStatus = $busExemplaryStatusHistory->getLastStatus(MIOLO::_REQUEST('itemNumber'));
            $url = str_replace('level0', $futureStatus, $url);
        }

        return $url;
    }

    public static function question($msg, $gotoYes, $gotoNo = NULL, $closeButton = false, $return = false, $doLinkButton = false)
    {
        parent::question($msg, $gotoYes, self::replaceURL($gotoNo), $closeButton, $return, $doLinkButton);
    }

    public static function information($msg, $goto, $event, $closeButton = false, $doLinkButton = false)
    {
        parent::information($msg, self::replaceURL($goto), $event, $closeButton, $doLinkButton);
    }

}

?>