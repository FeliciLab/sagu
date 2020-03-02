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
 * Creation date 25/10/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestReport extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('Report');
        
        $data = new stdClass();
        $data->reportId = $data->reportIdS = 'AB1';
        $data->Title = $data->TitleS = 'hell';
        $data->description = $data->descriptionS = 'asdad';
        $data->permission = $data->permissionS = 'basic';
        $data->reportGroup = $data->reportGroupS = 'EMP';
        $data->isActive = $data->isActiveS = 't';
        $data->__ISFILEUPLOADPOST = $data->__ISFILEUPLOADPOSTS = 'yes';
        $data->label = $data->labelS = '';
        $data->identifier = $data->identifierS = '';
        $data->type = $data->typeS = 'string';
        $data->defaultValue = $data->defaultValueS = '';
        $data->lastValue = $data->lastValueS = '';
        $data->options = $data->optionsS = '';
        $data->reportSql = $data->reportSqlS = 'dsfsf';
        $data->reportSubSql = $data->reportSubSqlS = 'sfdsfd';
        $data->script = $data->scriptS = 'sdfsdfs';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';
        $data->uploadedfile = $data->uploadedfileS = 'Adicionar_campo_mais_detalhes.odt';
        $data->group = $data->groupS = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:configuration:searchablefield',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'description' => 'Exem',     'field' => '092.d',     'identifier' => 'cgg',     'observation' => 'Jorjs',     'isRestricted' => 'f',     'level' => '1',     'fieldType' => '1',     'helps' => 'Helpo',     'linkId' => '28',     'GRepetitiveField' => 'group',     'arrayItemTemp' => NULL,     'keyCode' => '79',     'isModified' => 't',     'functionMode' => 'search',     'frm4ea6b1f4a769e_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:searchablefield&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'linkDescription' => 'Comunidade interna',     'insertData' => true,     'arrayItem' => 0,  )),);
        $data->parameters = $data->parametersS = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:configuration:configReport',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'reportId' => 'AB1',     'Title' => 'hell',     'description' => 'asdad',     'permission' => 'basic',     'reportGroup' => 'EMP',     'isActive' => 't',     '__ISFILEUPLOADPOST' => 'yes',     'label' => 'asd',     'identifier' => 'dsf',     'type' => 'string',     'defaultValue' => '',     'lastValue' => '',     'options' => '',     'reportSql' => '',     'reportSubSql' => '',     'script' => '',     'GRepetitiveField' => 'parameters',     'arrayItemTemp' => NULL,     'keyCode' => '70',     'isModified' => 't',     'functionMode' => 'manage',     'frm4ea6bb11e5950_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:configReport&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'insertData' => true,     'arrayItem' => 0,  )),);
        $data->generalUploader = $data->generalUploaderS = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:configuration:configReport',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'reportId' => 'AB1',     'Title' => 'hell',     'description' => 'asdad',     'permission' => 'basic',     'reportGroup' => 'EMP',     'isActive' => 't',     '__ISFILEUPLOADPOST' => 'yes',     'label' => '',     'identifier' => '',     'type' => 'file',     'defaultValue' => '',     'lastValue' => '',     'options' => '',     'reportSql' => 'dsfsf',     'reportSubSql' => 'sfdsfd',     'script' => 'sdfsdfs',     'GRepetitiveField' => 'generalUploader',     'arrayItemTemp' => NULL,     'keyCode' => '83',     'isModified' => 't',     'functionMode' => 'manage',     'frm4ea6bb11e5950_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:configReport&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     'uploadedfile' => 'Adicionar_campo_mais_detalhes.odt',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'uploadInfo' => 'Adicionar_campo_mais_detalhes.odt;2011-10-25_111014_Adicionar_campo_mais_detalhes.odt',     'basename' => 'Adicionar_campo_mais_detalhes.odt',     'tmp_name' => '/home/guilherme/svn/gnutecatrunk/www/modules/gnuteca3/html/files/tmp/2011-10-25_111014_Adicionar_campo_mais_detalhes.odt',     'size' => 542275,     'mimeContent' => 'application/vnd.oasis.opendocument.text',     'insertData' => true,     'arrayItem' => 0,  )),);

        $this->business->setData($data);
    }
}
?>