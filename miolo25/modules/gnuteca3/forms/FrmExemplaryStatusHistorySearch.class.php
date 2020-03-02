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
 * Class created on 02/08/2008
 *
 **/
class FrmExemplaryStatusHistorySearch extends GForm
{
    public $MIOLO;
    public $module;
    public $busExemplaryStatus;
    public $busLibraryUnit;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busLibraryUnit     = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->setAllFunctions('ExemplaryStatusHistory');
        $this->setGrid('GrdExemplaryStatusHistory');
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new MTextField('controlNumberS', null, _M('Número de controle', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('itemNumberS', null, _M('Número de exemplar', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('exemplaryStatusIdS', null, _M('Estado do exemplar', $this->module), $this->busExemplaryStatus->listExemplaryStatus());

        $this->busLibraryUnit->filterOperator = TRUE;
        $this->busLibraryUnit->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);
        $lblDate  = new MLabel(_M('Data', $this->module) . ':');
        $beginDateS = new MCalendarField('beginDateS', null, null, FIELD_DATE_SIZE);
        $endDateS = new MCalendarField('endDateS', null, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));
        $fields[] = new MTextField('operatorS', null, _M('Operador', $this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields( $fields );
        $this->_toolBar->hideButton( MToolBar::BUTTON_NEW );
        $this->_toolBar->hideButton( MToolBar::BUTTON_DELETE );
    }
}
?>