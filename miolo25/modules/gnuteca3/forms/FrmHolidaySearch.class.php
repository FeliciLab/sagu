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
class FrmHolidaySearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Holiday', array('holidayIdS','descriptionS'),array('holidayId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[]   = new MTextField('holidayIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $lblDate    = new MLabel(_M('Data', $this->module) . ':');
        $beginDateS = new MCalendarField('beginDateS');
        $endDateS   = new MCalendarField('endDateS');
        $fields[]   = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));
        $fields[]   = new MTextField('descriptionS', null, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);
        //('occursAllYearS', null, _M('Todo ano', $this->module), GUtil::listYesNo(1), null, null, FIELD_DESCRIPTION_SIZE, false)

        $fields[]   = new GSelection('occursAllYearS', null, _M('Todo ano', $this->module), GUtil::listYesNo(0), null, null, null, false);
        //$fields[]   = new GRadioButtonGroup('occursAllYearS', _M('Todo ano', $this->module), GUtil::listYesNo(1), null, null, MFormControl::LAYOUT_HORIZONTAL);
        
        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $busLibraryUnit->filterOperator = TRUE;
        $busLibraryUnit->labelAllLibrary = TRUE;
        $list = $busLibraryUnit->listLibraryUnit();
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca', $this->module), $list, null, null, null, TRUE);

        $this->setFields( $fields );

        $validators[]   = new MIntegerValidator('holidayIdS');
        $validators[]   = new MDateDMYValidator('beginDateS');
        $validators[]   = new MDateDMYValidator('endDateS');

        $this->setValidators( $validators );
    }
}
?>