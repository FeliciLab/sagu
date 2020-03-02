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
 * Return register search form
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 25/05/2009
 *
 **/
class FrmReturnRegisterSearch extends GForm
{
    public $MIOLO;
    public $module;
    public $busReturnRegister;
    public $busReturnType;

    public function __construct()
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();
    	$this->busReturnRegister = $this->MIOLO->getBusiness($this->module, 'BusReturnRegister');
    	$this->busReturnType = $this->MIOLO->getBusiness($this->module, 'BusReturnType');

        $this->setAllFunctions('ReturnRegister', array('returnRegisterIdS','itemNumberS', 'operatorS'),array('returnRegisterId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[]       = new MTextField('returnRegisterIdS', $this->returnRegisterIdS->value, _M('Código', $this->module), 3);
        $fields[]       = new GSelection('returnTypeIdS', $this->returnTypeId->value, _M('Tipo de devolução',$this->module), $this->busReturnType->listReturnType());
        $fields[]       = new MTextField('itemNumberS', $this->itemNumberS->value, _M('Número do exemplar', $this->module), FIELD_DESCRIPTION_SIZE);
        $lblDate        = new MLabel(_M('Data', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginDateS     = new MCalendarField('beginDateS', $this->beginDateS->value, null, FIELD_DATE_SIZE);
        $endDateS       = new MCalendarField('endDateS', $this->endDateS->value, null, FIELD_DATE_SIZE);
        $fields[]       = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));
        $fields[]       = new MTextField('operatorS', $this->operatorS->value, _M('Operador', $this->module), FIELD_DESCRIPTION_SIZE);$operatorS;

        $this->setFields($fields);
    }
}
?>