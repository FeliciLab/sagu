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
 * Class created on 28/07/2008
 *
 **/
class FrmFineSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Fine', array('loanIdS'), array('loanId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $busFineStatus  = $this->MIOLO->getBusiness('gnuteca3', 'BusFineStatus');
        $busLibraryUnit = $this->MIOLO->getBusiness( 'gnuteca3', 'BusLibraryUnit');
        
    	$this->business->isFormSearch = TRUE;
        $fields[] = new MTextField('fineIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $controls[] = new MTextField('loanIdDescriptionS', null, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE,null,null, true);
        $fields[] = new GLookupField('loanIdS', null, _M('Código do empréstimo', $this->module) , 'Loan', $controls);
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');

        $lblDate = new MLabel(_M('Data inicial', $this->module) . ':');
        $beginBeginDateS = new MCalendarField('beginBeginDateS');
        $endBeginDateS = new MCalendarField('endBeginDateS');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginBeginDateS, $endBeginDateS));
       
        $fields[] = new MTextField('valueS', null, _M('Valor', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('fineStatusIdS', null, _M('Estado da multa', $this->module), $busFineStatus->listFineStatus());

        $lblDate = new MLabel(_M('Data final', $this->module) . ':');
        $beginEndDateS = new MCalendarField('beginEndDateS');
        $endEndDateS = new MCalendarField('endEndDateS');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginEndDateS, $endEndDateS));
        $fields[] = new MTextField('itemNumberS', NULL, _M('Número do exemplar',$this->module));

        $busLibraryUnit->filterOperator = TRUE;
        $busLibraryUnit->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca', $this->module), $busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);

        $this->setFields( $fields );
        
        $validators[]   = new MIntegerValidator('fineIdS');
        $validators[] = new MRegExpValidator('valueS', _M('Valor', $this->module), '^([0-9]?[0-9])(\.[0-9][0-9]?)?$');
        
        $this->setValidators( $validators );
    }


    public function getGrid()
    {
        $data = $this->getData();

        if ($data->valueS)
        {
            $data->valueS = str_replace(',', '.', $data->valueS);
        }

        return parent::getGrid($data);
    }


    public function showDetail()
    {
    	$MIOLO  = MIOLO::getInstance();
    	$module = MIOLO::getCurrentModule();

    	$busFineStatusHistory = $MIOLO->getBusiness($module, 'BusFineStatusHistory');
    	$busFineStatusHistory->fineIdS = MIOLO::_REQUEST('fineId');

        $search = $busFineStatusHistory->searchFineStatusHistory(TRUE);

        if ($search)
        {
            $tbData = array();
            $date   = new GDate();
            foreach ($search as $value)
            {
                $date->setDate($value->date);
                $tbData[] = array(
                    $value->fineStatus,
                    $date->getDate(GDate::MASK_DATE_USER),
                    $value->operator,
                    $value->observation
                );
            }
            $tbColumns = array(
                _M('Estado', $this->module),
                _M('Data', $this->module),
                _M('Operador', $this->module),
                _M('Observação', $this->module)
            );
            $tb = new MTableRaw(_M('Histórico', $this->module), $tbData, $tbColumns);
            $tb->zebra = TRUE;
            $fields[] = new MDiv(null, $tb);
        }
        else
        {
            $fields[] = new MLabel(_M('Nenhum histórico para este registro', $this->module));
        }

        $this->injectContent( $tb, true, _M('Histórico do estado da multa', $this->module) . ': '. MIOLO::_REQUEST('fineId') );
    }
}
?>