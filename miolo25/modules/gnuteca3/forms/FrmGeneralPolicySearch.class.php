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
class FrmGeneralPolicySearch extends GForm
{
    public $businessPrivilegeGroup;
    public $businessLink;

    public function __construct()
    {
        $this->businessPrivilegeGroup   = MIOLO::getInstance()->getBusiness(MIOLO::getCurrentModule(), 'BusPrivilegeGroup');
        $this->businessLink     = MIOLO::getInstance()->getBusiness(MIOLO::getCurrentModule(), 'BusBond');
        $this->setAllFunctions('GeneralPolicy', array('privilegeGroupIdS','linkIdS'),array('privilegeGroupId','linkId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new GSelection('privilegeGroupIdS', $this->privilegeGroupIds->value, _M('Grupo de privilégio',$this->module), $this->businessPrivilegeGroup->listPrivilegeGroup());
        $fields[] = new GSelection('linkIdS', $this->linkIdS->value, _M('Vínculo',$this->module), $this->businessLink->listBond(true));
        $fields[] = new MIntegerField('loanGeneralLimitS', null, _M('Limite de empréstimo', $this->module));
        $fields[] = new MIntegerField('reserveGeneralLimitS', null, _M('Limite de reserva', $this->module));
        $fields[] = new MIntegerField('reserveGeneralLimitIninitialLevelS', null, _M('Limite de reserva para nível inicial', $this->module));
        $this->setFields( $fields );
    }
}
?>