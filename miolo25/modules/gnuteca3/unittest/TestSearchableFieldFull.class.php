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
class TestSearchableField extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('SearchableField');
        
        $data = new stdClass();
        $data->description = $data->descriptionS = 'Exem';
        $data->field = $data->fieldS = '092.d';
        $data->identifier = $data->identifierS = 'cgg';
        $data->observation = $data->observationS = 'Jorjs';
        $data->isRestricted = $data->isRestrictedS = 'f';
        $data->level = $data->levelS = '1';
        $data->fieldType = $data->fieldTypeS = '1';
        $data->helps = $data->helpsS = 'Helpo';
        $data->linkId = $data->linkIdS = '1';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'search';
        $data->group = $data->groupS = array (  0 =>   (array(     'module' => 'gnuteca3',     'action' => 'main:configuration:searchablefield',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'description' => 'Exem',     'field' => '092.d',     'identifier' => 'cgg',     'observation' => 'Jorjs',     'isRestricted' => 'f',     'level' => '1',     'fieldType' => '1',     'helps' => 'Helpo',     'linkId' => '28',     'GRepetitiveField' => 'group',     'arrayItemTemp' => NULL,     'keyCode' => '79',     'isModified' => 't',     'functionMode' => 'search',     'frm4ea6b1f4a769e_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:searchablefield&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'linkDescription' => 'Comunidade interna',     'insertData' => true,     'arrayItem' => 0,  )));

        $this->business->setData($data);
    }
}
?>