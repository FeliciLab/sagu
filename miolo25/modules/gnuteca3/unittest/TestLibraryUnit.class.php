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
 * Creation date 11/10/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestLibraryUnit extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('LibraryUnit');
        
        $data = new stdClass();
        $data->libraryUnitId = $data->libraryUnitIdS = '299';
        $data->libraryName = $data->libraryNameS = 'Nomer';
        $data->isRestricted = $data->isRestrictedS = 'f';
        $data->acceptPurchaseRequest = $data->acceptPurchaseRequestS = 'f';
        $data->city = $data->cityS = 'cidade';
        $data->zipCode = $data->zipCodeS = '95900-000';
        $data->location = $data->locationS = 'Locla';
        $data->number = $data->numberS = '23';
        $data->complement = $data->complementS = 'compl';
        $data->email = $data->emailS = 'mail@mail.com';
        $data->url = $data->urlS = 'www.php.net.br';
        $data->privilegeGroupId = $data->privilegeGroupIdS = '2';
        $data->libraryGroupId = $data->libraryGroupIdS = '1';
        $data->level = $data->levelS = '12';
        $data->observation = $data->observationS = 'Obvaser';
        $data->weekDayId = $data->weekDayIdS = '1';
        $data->description = $data->descriptionS = '';
        $data->linkId = $data->linkIdS = '1';
        $data->isModified = $data->isModifiedS = '';
        $data->functionMode = $data->functionModeS = 'manage';
        $data->libraryUnitIsClosed = $data->libraryUnitIsClosedS = array (  0 =>   stdClass::__set_state(array(     'libraryUnitId' => '299',     'weekDayId' => '3',     'weekDescription' => 'Quarta-feira',     'arrayItem' => 0,  )),);
        $data->group = $data->groupS = array (  0 =>   stdClass::__set_state(array(     'libraryUnitId' => '299',     'linkId' => '115',     'description' => 'Solis',     'arrayItem' => 0,  )),);

        $this->business->setData($data);
    }
}
?>