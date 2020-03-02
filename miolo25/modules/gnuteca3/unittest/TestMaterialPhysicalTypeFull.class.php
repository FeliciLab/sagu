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
class TestMaterialPhysicalType extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('MaterialPhysicalType');
        
        $data = new stdClass();
        $data->description = $data->descriptionS = 'Vlan';
        $data->observation = $data->observationS = 'sdf';
        $data->__ISFILEUPLOADPOST = $data->__ISFILEUPLOADPOSTS = 'yes';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';
        $data->uploadedfile = $data->uploadedfileS = 'kratos (1).jpg';
        $data->generalUploader = $data->generalUploaderS = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:configuration:materialPhysicalType',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'description' => 'Vlan',     'observation' => 'sdf',     '__ISFILEUPLOADPOST' => 'yes',     'GRepetitiveField' => 'generalUploader',     'arrayItemTemp' => NULL,     'keyCode' => '70',     'isModified' => 't',     'functionMode' => 'manage',     'frm4eb2d19f56333_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:materialPhysicalType&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     'uploadedfile' => 'kratos (1).jpg',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'uploadInfo' => 'kratos (1).jpg;2011-11-03_031151_kratos (1).jpg',     'basename' => 'kratos (1).jpg',     'type' => 'file',     'tmp_name' => '/home/guilherme/svn/gnutecatrunk/www/modules/gnuteca3/html/files/tmp/2011-11-03_031151_kratos (1).jpg',     'size' => 24180,     'mimeContent' => 'image/jpeg',     'insertData' => true,     'arrayItem' => 0,  )),);

        $this->business->setData($data);
    }
}
?>