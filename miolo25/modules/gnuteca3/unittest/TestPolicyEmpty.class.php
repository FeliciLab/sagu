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
class TestPolicy extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('Policy');
        
        $data = new stdClass();
        $data->privilegeGroupId = $data->privilegeGroupIdS = '16';
        $data->linkList = $data->linkListS = array (  0 => '102',);
        $data->materialGenderList = $data->materialGenderListS = array (  0 => '3',  1 => '4',);
        $data->loanDate = $data->loanDateS = '';
        $data->loanDays = $data->loanDaysS = '';
        $data->loanLimit = $data->loanLimitS = '';
        $data->renewalLimit = $data->renewalLimitS = '';
        $data->reserveLimit = $data->reserveLimitS = '';
        $data->fineValue = $data->fineValueS = '';
        $data->penaltyByDelay = $data->penaltyByDelayS = '';
        $data->daysOfWaitForReserve = $data->daysOfWaitForReserveS = '';
        $data->reserveLimitInInitialLevel = $data->reserveLimitInInitialLevelS = '';
        $data->daysOfWaitForReserveInInitialLevel = $data->daysOfWaitForReserveInInitialLevelS = '';
        $data->renewalWebLimit = $data->renewalWebLimitS = '';
        $data->renewalWebBonus = $data->renewalWebBonusS = 'f';
        $data->additionalDaysForHolidays = $data->additionalDaysForHolidaysS = '';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';
        $data->LibraryUnits = $data->LibraryUnitsS = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:configuration:libraryAssociation',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     'function' => 'insert',     'description' => '',     'libraryUnitId' => '15',     'libraryUnitDescription' => 'Museu Reg. do Livro',     'GRepetitiveField' => 'LibraryUnits',     'arrayItemTemp' => NULL,     'keyCode' => '',     'isModified' => 't',     'functionMode' => 'manage',     'frm4e9490dfb195f_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:configuration:libraryAssociation&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTARGUMENT' => '',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'insertData' => true,     'arrayItem' => 0,  )),);

        $this->business->setData($data);
    }
}
?>