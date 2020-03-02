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
 * Holiday form
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
 * Class created on 28/07/2008
 *
 **/
class FrmHoliday extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Holiday', null, 'holidayId', 'description');
        parent::__construct();
    }

    public function mainFields()
    {
        if ( MIOLO::_REQUEST('function') != "insert")
        {
            $fields[] = new MTextField('holidayId', null, _M('Código',$this->module), FIELD_ID_SIZE,null, null, true);
            $validators[] = new MRequiredValidator('holidayId');
            $validators[] = new MIntegerValidator('holidayId');
        }

        $fields[] = new MCalendarField('date', null, _M('Data',$this->module) );;
        $fields[] = new MTextField('description', null, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);;
        $fields[] = new GRadioButtonGroup( 'occursAllYear', _M('Todo ano', $this->module).':' , GUtil::listYesNo(1), 'f', null);
        
        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $busLibraryUnit->filterOperator = TRUE;
        $list       = $busLibraryUnit->listLibraryUnit(false, true);
        $hideDefSel = !$busLibraryUnit->operatorAllLibraries;
        $fields[] = new GSelection('libraryUnitId', null, _M("Unidade de biblioteca", $this->module), $list, null, null, null, $hideDefSel);

        $this->setFields($fields);

        $validators[]   = new MRequiredValidator('date');
        $validators[]   = new MRequiredValidator('description');
        $validators[] = new MRequiredValidator('occursAllYear', _M('Todo ano', $this->module));

        $this->setValidators($validators);
    }
}
?>