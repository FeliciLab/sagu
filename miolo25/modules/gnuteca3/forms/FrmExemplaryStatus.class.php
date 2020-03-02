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
 * Class Layout form
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 13/08/08
 *
 **/
class FrmExemplaryStatus extends GForm
{
    function __construct()
    {
        $this->setAllFunctions('ExemplaryStatus', null, array('exemplaryStatusId'), array('exemplaryStatusId') );
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new MTextField("description",                    $this->description->value,                  _M("Descrição",                       $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("mask",                           $this->mask->value,                         _M("Máscara",                              $this->module), FIELD_DESCRIPTION_SIZE);
        //FIX-ME : CRIAR DOMINIO PARA ISSO.
        $fields[] = new GSelection('level',                          $this->level->value,                        _M("Nível",                             $this->module), array(1 => _M("Inicial", $this->module), 2 => _M("Transição", $this->module)), null, null, null, TRUE);
        $validators[] = new MRequiredValidator('level');

        $lbl = new MLabel(_M('Executa empréstimo', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $executeLoan = new MRadioButtonGroup('executeLoan', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctExecuteLoan', array($lbl, $executeLoan));

        $lbl = new MLabel(_M('Empréstimo momentâneo', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $momentaryLoan = new MRadioButtonGroup('momentaryLoan', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctMomentaryLoan', array($lbl, $momentaryLoan));

        $fields[] = new MTextField("daysOfMomentaryLoan", $this->daysOfMomentaryLoan->value, _M("@1 de empréstimo momentâneo", $this->module,(LOAN_MOMENTARY_PERIOD == 'H' )? 'Horas':'Dias'), FIELD_ID_SIZE);

        $lbl = new MLabel(_M('Executa reserva', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $executeReserve = new MRadioButtonGroup('executeReserve', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctExecuteReserve', array($lbl, $executeReserve));

        $lbl = new MLabel(_M('Executa reserva em nível inicial', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $executeReserveInInitialLevel = new MRadioButtonGroup('executeReserveInInitialLevel', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctExecuteReserveInInitialLevel', array($lbl, $executeReserveInInitialLevel));

        $lbl = new MLabel(_M('Atende reserva', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $meetReserve = new MRadioButtonGroup('meetReserve', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctMeetReserve', array($lbl, $meetReserve));

        $lbl = new MLabel(_M('É estado de reserva', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $isReserveStatus = new MRadioButtonGroup('isReserveStatus', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctIsReserveStatus', array($lbl, $isReserveStatus));

        $lbl = new MLabel(_M('Está em estado de baixa', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $isLowStatus = new MRadioButtonGroup('isLowStatus', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctIsLowStatus', array($lbl, $isLowStatus));


        $lbl = new MLabel(_M('Permite requisição de alteração de estado', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $scheduleChangeStatusForRequest = new MRadioButtonGroup('scheduleChangeStatusForRequest', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctScheduleChangeStatusForRequest', array($lbl, $scheduleChangeStatusForRequest));
        $fields[] = new MMultiLIneField  ("observation", $this->observation->value,  _M("Observação",   $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        if($this->function == 'update')
        {
            $fields[] = new MHiddenField('exemplaryStatusId', MIOLO::_REQUEST('exemplaryStatusId'));
        }

        // set Search Form Fileds
        $this->setFields( $fields );
        $validators[] = new MRequiredValidator  ("description", _M("Descrição",   $this->module) );
        $this->setValidators($validators);
    }
}
?>
