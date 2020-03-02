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
 *
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
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class FrmGeneralPolicy extends GForm
{
    public $businessPrivilegeGroup;
    public $businessLink;

    function __construct()
    {
        $this->businessPrivilegeGroup   = MIOLO::getInstance()->getBusiness(MIOLO::getCurrentModule(), 'BusPrivilegeGroup');
        $this->businessLink     = MIOLO::getInstance()->getBusiness(MIOLO::getCurrentModule(), 'BusBond');
        $this->setAllFunctions('GeneralPolicy', array('privilegeGroupIdS','linkIdS'), array('privilegeGroupId','linkId'), 'privilegeGroupId');
        parent::__construct();
    }

    public function mainFields()
    {
        if ( $this->function != 'update' )
        {
            $fields[]       = new GSelection('privilegeGroupId', $this->privilegeGroupId->value, _M('Grupo de privilégio',$this->module), $this->businessPrivilegeGroup->listPrivilegeGroup());
            $validators[]   = new MRequiredValidator('privilegeGroupId');
            $optionsBond    = $this->businessLink->listBond(true);
            $fields[]       = new MMultiSelection('linkList', array(null), _M('Vínculo',$this->module), $optionsBond, null, null, 10);
            $validators[]   = new MRequiredValidator('linkList');
        }
        else
        {
            $fields[] = new MHiddenField('privilegeGroupId');
            $fields[] = new MTextField('privilegeGroupDescription', $this->privilegeGroupId->value, _M('Código do grupo de privilégio',$this->module), FIELD_ID_SIZE,null, null, true);;
            $fields[] = new MHiddenField('linkId');
            $fields[] = new MTextField('linkDescription', $this->linkDescription->value, _M('Código do vínculo',$this->module), FIELD_ID_SIZE, null, null, true);
        }

        $fields[]       = new MIntegerField('loanGeneralLimit', null, _M('Limite de empréstimo', $this->module));
        $validators[]   = new MRequiredValidator('loanGeneralLimit');
        $fields[]       = new MIntegerField('reserveGeneralLimit', null, _M('Limite de reserva', $this->module));
        $validators[]   = new MRequiredValidator('reserveGeneralLimit');
        $fields[]       = new MIntegerField('reserveGeneralLimitIninitialLevel', null, _M('Limite de reserva para nível inicial', $this->module));
        $validators[]   = new MRequiredValidator('reserveGeneralLimitIninitialLevel');
        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>