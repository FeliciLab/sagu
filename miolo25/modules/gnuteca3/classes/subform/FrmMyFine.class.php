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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 22/10/2008
 *
 **/
class FrmMyFine extends GSubForm
{
    public $MIOLO;
    public $module;
    public $business;
    public $busAthenticate;
    public $busFineStatus;
    public $busLoan;
    public $busFine;
    public $busRenew;
    public $busLibraryUnit;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->business         = $this->MIOLO->getBusiness( $this->module, 'BusFine');
        $this->busAthenticate 	= $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busFineStatus 	= $this->MIOLO->getBusiness( $this->module, 'BusFineStatus');
        $this->busLibraryUnit   = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');
        $this->busLoan          = $this->MIOLO->getBusiness( $this->module, 'BusLoan');
        $this->busFine          = $this->MIOLO->getBusiness( $this->module, 'BusFine');
        $this->busRenew         = $this->MIOLO->getBusiness( $this->module, 'BusRenew');

        $this->business->isFormSearch = TRUE;
        $this->gridName = 'GrdMyFine';
        $this->gridSearchMethod = 'searchFine';

        parent::__construct( _M('Histórico de multa', $this->module) );
    }

    public function createFields()
    {
        GForm::setFocus('itemNumberS',false);

        $this->busLibraryUnit->onlyWithAccess  = true;
        $fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
    	$fields[] = new MTextField('itemNumberS', null, _M('Número do exemplar',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MFloatField('valueS', $this->observationS->value, _M('Valor',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('fineStatusIdS',   $this->libraryUnitIdS->value, _M('Estado da multa', $this->module), $this->busFineStatus->listFineStatus());

        $lblDate = new MLabel(_M('Data inicial', $this->module) . ':');
        $beginBeginDateS = new MCalendarField('beginBeginDateS');
        $endBeginDateS = new MCalendarField('endBeginDateS');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginBeginDateS, $endBeginDateS));
        $validators[] = new MDateDMYValidator('beginBeginDateS');

        $this->setFields( array( GUtil::alinhaForm( $fields ) ) , true);
    }

    public function getData()
    {
        $data = parent::getData();
        $data->personIdS = BusinessGnuteca3BusAuthenticate::getUserCode();

        return $data;
    }

    public function showLoan($loanId)
    {
        $data = new stdClass();
        $data->loanIdS = $loanId;
        $this->busLoan->setData($data);
        $search = $this->busLoan->searchLoan(TRUE);

        if ($search)
        {
            $fields[] = new MLabel(_M('Detalhes do código do empréstimo', $this->module) . ': '. $data->loanIdS);
            $tbData = array();
            foreach ($search as $v)
            {
                $tbData[] = array(
                    $v->itemNumber,
                    GDate::construct($v->LoanDate)->getDate(GDate::MASK_DATE_USER),
                    GDate::construct($v->returnForecastDate)->getDate(GDate::MASK_DATE_USER),
                    GDate::construct($v->returnDate)->getDate(GDate::MASK_DATE_USER),
                );
            }
            $tbColumns = array(
                _M('Número do exemplar', $this->module),
                _M('Data do empréstimo', $this->module),
                _M('Data prevista da devolução', $this->module),
                _M('Data de devolução', $this->module),
            );
            $tb = new MTableRaw('', $tbData, $tbColumns);
            $tb->zebra = TRUE;
        }
        else
        {
            $tb = new MLabel(_M('Nenhum detalhe encontrado para este empréstimo', $this->module));
        }

        GForm::injectContent( $tb, true, _M('Detalhes do empréstimo', $this->module) );
    }

    public function showRenew($loanId)
    {
        $search = $this->busRenew->getHistoryOfLoan($loanId, false);

        if ($search)
        {
            $fields[] = new MLabel();
            $tbColumns = array(
                _M('Tipo de renovação', $this->module),
                _M('Data prevista da devolução', $this->module),
                _M('Data de renovação', $this->module),
                _M('Nova data prevista da devolução', $this->module)
            );
            $tb = new MTableRaw('', $search, $tbColumns);
            $tb->zebra = TRUE;
        }
        else
        {
            GForm::information( _M('Não há renovações para este empréstimo', $this->module) );
            return false;
        }

        GForm::injectContent($tb, true, _M('Histórico de renovação', $this->module) .' '. _M('Código do empréstimo', $this->module) . ': '. MIOLO::_REQUEST('loanId') );
    }
}
?>
