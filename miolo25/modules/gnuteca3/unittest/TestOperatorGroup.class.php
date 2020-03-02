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
 *  Teste unitário
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 03/11/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestOperatorGroup extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('OperatorGroup');
        
        $data = new stdClass();
        $data->groupName = $data->groupNameS = 'RUTHMAR_GRoot';
        $data->idTransaction = $data->idTransactionS = '2021';
        $data->ACESSAR = $data->ACESSARS = 't';
        $data->ACESSAR_defaultValue = $data->ACESSAR_defaultValueS = 't';
        $data->INCLUIR = $data->INCLUIRS = 't';
        $data->INCLUIR_defaultValue = $data->INCLUIR_defaultValueS = 't';
        $data->ALTERAR = $data->ALTERARS = 't';
        $data->ALTERAR_defaultValue = $data->ALTERAR_defaultValueS = 't';
        $data->REMOVER = $data->REMOVERS = 't';
        $data->REMOVER_defaultValue = $data->REMOVER_defaultValueS = 't';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';
        $data->access = $data->accessS = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:configuration:operatorGroup',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'groupName' => 'RUTHMAR_GRoot',     'idTransaction' => '2021',     'ACESSAR' => 't',     'ACESSAR_defaultValue' => 't',     'INCLUIR' => 'f',     'INCLUIR_defaultValue' => 't',     'ALTERAR' => 't',     'ALTERAR_defaultValue' => 't',     'REMOVER' => 't',     'REMOVER_defaultValue' => 't',     'GRepetitiveField' => 'access',     'arrayItemTemp' => NULL,     'keyCode' => '84',     'isModified' => 't',     'functionMode' => 'manage',     'frm4eb2dd29045f4_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:operatorGroup&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'descTransaction' => 'Administração avançada de relatórios',     'ACESSARDesc' => 'Sim',     'INCLUIRDesc' => 'Não',     'ALTERARDesc' => 'Sim',     'REMOVERDesc' => 'Sim',     'insertData' => true,     'arrayItem' => 0,  )),);

        $this->business->setData($data);
    }
}
?>