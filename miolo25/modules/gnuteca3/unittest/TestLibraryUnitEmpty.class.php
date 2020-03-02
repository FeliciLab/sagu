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
        $data->libraryName = $data->libraryNameS = 'Nome';
        $data->isRestricted = $data->isRestrictedS = 'f';
        $data->acceptPurchaseRequest = $data->acceptPurchaseRequestS = 'f';
        $data->city = $data->cityS = '';
        $data->zipCode = $data->zipCodeS = '';
        $data->location = $data->locationS = '';
        $data->number = $data->numberS = '';
        $data->complement = $data->complementS = '';
        $data->email = $data->emailS = '';
        $data->url = $data->urlS = '';
        $data->privilegeGroupId = $data->privilegeGroupIdS = '1';
        $data->libraryGroupId = $data->libraryGroupIdS = '';
        $data->level = $data->levelS = '';
        $data->observation = $data->observationS = '';
        $data->weekDayId = $data->weekDayIdS = '1';
        $data->description = $data->descriptionS = '';
        $data->linkId = $data->linkIdS = '1';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';

        $this->business->setData($data);
    }
}
?>