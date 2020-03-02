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
 * Library Unit search form
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
 * Class created on 29/07/2008
 *
 **/

class FrmReserveSearch extends GForm
{
    public $MIOLO;
    public $module;
    public $busRS;
    public $busReserveComposition;
    public $busRT;
    public $busLU;


    public function __construct()
    {
        $this->MIOLO        = MIOLO::getInstance();
        $this->module       = MIOLO::getCurrentModule();
        $this->busRS        = $this->MIOLO->getBusiness($this->module, 'BusReserveStatus');
        $this->busReserveComposition = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition');
        $this->busRT        = $this->MIOLO->getBusiness($this->module, 'BusReserveType');
        $this->busLU        = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->setAllFunctions('Reserve', array('libraryUnitIdS'), array('reserveId'));

        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new MIntegerField('reserveIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');

        $fields[] = new MHiddenField('requestedDateS');
        $beginRequestedDateS = new MTimestampField('beginRequestedDateS', null, 'Data/Hora da solicitação');
        $endRequestedDateS = new MTimestampField('endRequestedDateS', null);
        $fields[] = new GContainer('hctRequestedDate', array($beginRequestedDateS, $endRequestedDateS));
        $validators[] = new MDateDMYValidator('requestedDateS_DATE', _M('Data da solicitação', $this->module) . ' (' . _M('Data', $this->module) . ')');
        $fields[] = new MTextField('itemNumberS', null, _M('Número do exemplar', $this->module), FIELD_ID_SIZE);

        $this->busLU->filterOperator = TRUE;
        $this->busLU->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLU->listLibraryUnit(), null, null, null, TRUE);

        $lblDate             = new MLabel(_M('Data limite', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginLimitDateS     = new MCalendarField('beginLimitDateS', $this->beginLimitDateS->value, null, FIELD_DATE_SIZE);
        $endLimitDateS       = new MCalendarField('endLimitDateS', $this->endLimitDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginLimitDateS, $endLimitDateS));
        $validators[] = new MDateDMYValidator('beginLimitDateS');

        $fields[] = new GSelection('reserveStatusIdS', null, _M('Estado da reserva', $this->module), $this->busRS->listReserveStatus());
        $fields[] = new GSelection('reserveTypeIdS', null, _M('Tipo de reserva', $this->module), $this->busRT->listReserveType());

        $this->setFields( $fields );
        $this->setValidators( $validators );
    }


    /**
     * Mostra o histórico da reserva
     */
    public function showDetail()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $busReserveHistory = $MIOLO->getBusiness($module, 'BusReserveStatusHistory');
        $busReserveHistory->reserveId = MIOLO::_REQUEST('reserveId');

        $search = $busReserveHistory->searchReserveStatusHistory();

        $tbColumns = array(
                _M('Estado', $this->module),
                _M('Data', $this->module),
                _M('Operador', $this->module)
        );
        
        $tb = new MTableRaw('', $search, $tbColumns);
        $tb->zebra = TRUE;

        $this->injectContent( $tb, true, _M('Histórico', $this->module) . ' '. _M('Código da reserva', $this->module) . ': '. MIOLO::_REQUEST('reserveId') );
    }

    /**
     * Mostra a composição da reserva, ação chamada pela grid
     */
    public function showReserveComposition()
    {
    	$reserveId = MIOLO::_REQUEST('reserveId');

    	//Search data...
    	$this->busReserveComposition->reserveIdS = $reserveId;
    	$data = $this->busReserveComposition->searchReserveComposition(TRUE);

    	if ($data)
    	{
            $cols = array(
               _M('Número do exemplar', $this->module),
               _M('Está confirmado', $this->module)
            );

            $tbData = array();

            foreach ($data as $v)
            {
            	$tbData[] = array( $v->itemNumber, GUtil::getYesNo($v->isConfirmed));
            }
            
            $tb = new MTableRaw(null, $tbData, $cols);
            $tb->setAlternate(true);
    	}
        else
        {
            $tb = new MLabel(_M('Nenhuma composição para o código de reserva: @1', $this->module, $reserveId));
        }

        $this->injectContent($tb, true, _M('Composição da reserva ', $this->module) . $reserveId );
    }
    
    
    /**
     * Mostra mensagem de verificacao se o usuario realmente quer desatender a
     * reserva.
     */
    public function btnNeglectReserve()
    {
        $reserveId = MIOLO::_REQUEST('reserveId');
        $msg = _M('Você deseja mesmo desatender a reserva @1?','gnuteca3',$reserveId);    

        
        $this->question($msg, GUtil::getAjax('neglectReserve', array('reserveId'=>$reserveId)));
    }
    
    /**
     * Desatende a reserva e mostra a mensagem de finalizaçao.
     */
    public function neglectReserve()
    {
        $reserveId = MIOLO::_REQUEST('reserveId');
        $busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $busReserve->neglectReserve($reserveId);
        $js = 'javascript:'. GUtil::getCloseAction() . "; dojo.byId('reserveStatusIdS').value='" . ID_RESERVESTATUS_REQUESTED . "' ; " . GUtil::getAjax('searchFunction',array('reserveIdS'=>$reserveId));

        GForm::information(_M('Reserva @1 desatendida com sucesso!',$this->module,$reserveId), $js);
    }
}
?>