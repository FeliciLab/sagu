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
 * Group right search form
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
 * Class created on 01/08/2008
 *
 **/
class FrmGroupRightSearch extends GForm
{
    public $module;
    public $businessPrivilegeGroup;
    public $businessOperation;
    public $businessMaterialGender;
    
    private $busBond;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        
        $this->module = MIOLO::getCurrentModule();
        $this->businessPrivilegeGroup = $MIOLO->getBusiness($this->module, 'BusPrivilegeGroup');
        $this->businessOperation = $MIOLO->getBusiness($this->module, 'BusOperation');
        $this->businessMaterialGender = $MIOLO->getBusiness($this->module, 'BusMaterialGender');
        $this->busBond = $MIOLO->getBusiness($this->module, 'BusBond');
        $this->setAllFunctions('Right', array('privilegeGroupIdS','linkIdS, materialGenderIdS') ,array('privilegeGroupId','linkid','materialgenderid','operationid'));
        $this->setGrid('GrdGroupRight');
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new GSelection('privilegeGroupIdS', $this->privilegeGroupIdS->value, _M('Grupo de privilégio', $this->module), $this->businessPrivilegeGroup->listPrivilegeGroup());
        $fields[] = new GSelection('linkIdS', $this->linkId->value, _M('Código do vínculo',$this->module), $this->busBond->listBond(true));
        $fields[] = new GSelection('materialGenderIdS', $this->materialGenderId->value, _M('Código do gênero do material',$this->module), $this->businessMaterialGender->listMaterialGender());;
        $fields[] = new GSelection('operationIdS', $this->operationId->value, _M('Código da operação',$this->module), $this->businessOperation->listOperation());
        $this->setFields( $fields );
    }
}
?>