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
 * Group Right form
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
 * Class created on 01/08/2008
 *
 **/
class FrmGroupRight extends GForm
{
    public $MIOLO;
    public $module;
    public $businessPrivilegeGroup;
    public $businessMaterialGender;
    public $businessOperation;
    public $businessBond;


    public function __construct()
    {
        $this->setAllFunctions('Right', null, array('privilegeGroupId', 'linkId', 'materialGenderId', 'operationId'), array('privilegeGroupId'));

        $this->MIOLO                  = MIOLO::getInstance();
        $this->module                 = MIOLO::getCurrentModule();
        $this->businessPrivilegeGroup = $this->MIOLO->getBusiness($this->module, 'BusPrivilegeGroup');
        $this->businessBond           = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->businessMaterialGender   = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
        $this->businessOperation      = $this->MIOLO->getBusiness($this->module, 'BusOperation');

        parent::__construct();
        $this->setFocus('privilegeGroupId');
    }


    public function mainFields()
    {
        $privilegeGroupId = new GSelection('privilegeGroupId', $this->privilegeGroupId->value, _M('Grupo de privilégio',$this->module), $this->businessPrivilegeGroup->listPrivilegeGroup());
        $fields[] = $privilegeGroupId;
        $validators[] = new MRequiredValidator('privilegeGroupId');

        $linkList = new MMultiSelection('linkList', array(null), _M('Vínculo',$this->module), $this->businessBond->listBond(true), null, null, 5);
        $fields[] = $linkList;
        $validators[] = new MRequiredValidator('linkList');

        $materialGenderList = new MMultiSelection('materialGenderList', array(null), _M('Gênero do material', $this->module), $this->businessMaterialGender->listMaterialGender(), null, null, 5);
        $fields[] = $materialGenderList;
        $validators[] = new MRequiredValidator('materialGenderList');

        $operationList = new MMultiSelection('operationList', array(null), _M('Tipo de operação', $this->module), $this->businessOperation->listOperation(), null, null, 5);
        $fields[] = $operationList;
        $validators[] = new MRequiredValidator('operationList');

        $this->setFields($fields);
        $this->setValidators($validators);
    }


    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        if ($this->function == 'insert')
        {
            foreach ($data->linkList as $dataInsert->linkId)
            {
                foreach ($data->materialGenderList as $dataInsert->materialGenderId)
                {
                    foreach ($data->operationList as $dataInsert->operationId)
                    {
                        $dataInsert->privilegeGroupId = $data->privilegeGroupId;
                        $this->business->setData($dataInsert);
                        $this->business->insertRight();
                    }
                }
            }
        }
        
        parent::tbBtnSave_click($sender, $data, $errors);
    }
}
?>
