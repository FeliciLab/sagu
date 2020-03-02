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
 * Class ExemplayStatus Search form
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
class FrmExemplaryStatusSearch extends GForm
{
    function __construct()
    {
        $this->setAllFunctions('ExemplaryStatus', array('exemplaryStatusIdS', 'descriptionS'), array('exemplaryStatusId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MTextField      ("exemplaryStatusIdS",              $this->exemplaryStatusIdS->value,                   _M("Código",                                  $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField      ("descriptionS",                    $this->descriptionS->value,                         _M("Descrição",                           $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField      ("maskS",                           $this->maskS->value,                                _M("Máscara",                                  $this->module), FIELD_DESCRIPTION_SIZE, null);
        $fields[] = new GSelection('levelS',                          $this->levelS->value,                               _M("Nível",                                 $this->module), array(1 => _M("Inicial",    $this->module), 2 => _M("Transição",   $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('executeLoanS',                    $this->executeLoanS->value,                         _M("Executa empréstimo",                          $this->module), array(t => _M("Sim",        $this->module), f => _M("Não",          $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('momentaryLoanS',                  $this->momentaryLoanS->value,                       _M("Empréstimo momentâneo",                        $this->module), array(t => _M("Sim",        $this->module), f => _M("Não",          $this->module)), null, null, null, FALSE);
        $fields[] = new MTextField      ("daysOfMomentaryLoanS",            $this->daysOfMomentaryLoanS->value, _M("@1 de empréstimo momentâneo", $this->module,(LOAN_MOMENTARY_PERIOD == 'H' )? 'Horas':'Dias'), FIELD_ID_SIZE);
        $fields[] = new GSelection('executeReserveS',                 $this->executeReserveS->value,                      _M("Executa reserva",                       $this->module), array(t => _M("Sim", $this->module), f => _M("Não", $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('executeReserveInInitialLevelS',   $this->executeReserveInInitialLevelS->value,        _M("Executa reserva em nível inicial",      $this->module), array(t => _M("Sim", $this->module), f => _M("Não", $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('meetReserveS',                    $this->meetReserveS->value,                         _M("Atende reserva",                          $this->module), array(t => _M("Sim", $this->module), f => _M("Não", $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('isReserveStatusS',                $this->isReserveStatusS->value,                     _M("É estado de reserva",                     $this->module), array(t => _M("Sim", $this->module), f => _M("Não", $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('isLowStatusS',                    $this->isLowStatusS->value,                         _M("Está em estado de baixa",                         $this->module), array(t => _M("Sim", $this->module), f => _M("Não", $this->module)), null, null, null, FALSE);
        $fields[] = new GSelection('scheduleChangeStatusForRequestS', $this->scheduleChangeStatusForRequestS->value,      _M("Permite requisição de alteração de estado",  $this->module), array(t => _M("Sim", $this->module), f => _M("Não", $this->module)), null, null, null, FALSE);

        $this->setFields( $fields );
    }
}
?>
