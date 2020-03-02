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
class FrmMyLoan extends GSubForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $business;
    public $grid;
    public $function;
    public $busAthenticate;
    public $busLoanType;
    public $busLibraryUnit;
    public $busRenew;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->business             = $this->MIOLO->getBusiness( $this->module, 'BusLoan');
        $this->busAthenticate   = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busLoanType        = $this->MIOLO->getBusiness( $this->module, 'BusLoanType');
        $this->busLibraryUnit   = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');
        $this->busRenew             = $this->MIOLO->getBusiness( $this->module, 'BusRenew');
        $this->function = MIOLO::_REQUEST('function');

        $this->gridName = 'GrdMyLoan';
        $this->gridSearchMethod = 'searchLoan';

        parent::__construct( _M('Histórico de empréstimo', $this->module) );
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        
        GForm::jsSetFocus('itemNumberS',false);

        $this->busLibraryUnit->onlyWithAccess  = true;
        $fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitIdS->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
        $fields[] = new MTextField('itemNumberS', $this->itemNumberS->value, _M('Número do exemplar',$this->module), FIELD_DESCRIPTION_SIZE);

        $lblDate = new MLabel(_M('Data do empréstimo', $this->module) . ':');
        $beginLoanDateS = new MCalendarField('beginLoanDateS', $this->beginLoanDateS->value, null, FIELD_DATE_SIZE);
        $endLoanDateS = new MCalendarField('endLoanDateS', $this->endLoanDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginLoanDateS, $endLoanDateS));
        $validators[] = new MDateDMYValidator('beginLoanDateS');

        $lblDate = new MLabel(_M('Data prevista da devolução', $this->module) . ':');
        $beginReturnForecastDateS = new MCalendarField('beginReturnForecastDateS', $this->beginReturnForecastDateS->value, null, FIELD_DATE_SIZE);
        $endReturnForecastDateS = new MCalendarField('endReturnForecastDateS', $this->endReturnForecastDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginReturnForecastDateS, $endReturnForecastDateS));
        $validators[] = new MDateDMYValidator('beginReturnForecastDateS');

        $lblDate = new MLabel(_M('Data de devolução', $this->module) . ':');
        $beginReturnDateS = new MCalendarField('beginReturnDateS', $this->beginReturnDateS->value, null, FIELD_DATE_SIZE);
        $endReturnDateS = new MCalendarField('endReturnDateS', $this->endReturnDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginReturnDateS, $endReturnDateS));
        $validators[] = new MDateDMYValidator('beginReturnDateS');
        $fields[] = new GSelection('status', null, _M('Estado', $this->module), array( 1 => _M("Pendente", $this->module),2 => _M("Atrasado", $this->module) ) );
        $fields[] = new MSeparator();

        $this->setFields( GUtil::alinhaForm($fields), true);
        
        //busca os empréstimos atrasados quando vir da minha biblioteca
        if ( MIOLO::_REQUEST('myLibrary') )
        {
            $MIOLO->page->onload("dojo.byId('status').value = '2';" .GUtil::getAjax('searchFunctionSub'));
        }
    }

    public function getData()
    {
        $data = parent::getData();
        $data->personIdS = BusinessGnuteca3BusAuthenticate::getUserCode();

        return $data;
    }

    public function showRenew($loanId)
    {
        if (!is_numeric($loanId))
        {
            return;
        }

        $search = $this->busRenew->getHistoryOfLoan($loanId, false);
        if ($search)
        {
            $tbColumns = array(
                _M('Tipo de renovação', $this->module),
                _M('Data prevista da devolução', $this->module),
                _M('Data de renovação', $this->module),
                _M('Nova data prevista da devolução', $this->module),
                
            );
            $tb = new MTableRaw('', $search, $tbColumns);
            $tb->zebra = TRUE;
        }
        else
        {
            GForm::information( _M('Não há renovações para este empréstimo.', $this->module) );
            return false;
        }
        
        GForm::injectContent($tb, true, _M('Histórico de renovação', $this->module) . ' - ' ._M('código do empréstimo', $this->module) . ': '. $loanId );
    }
}
?>