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
 * Creation date 24/10/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestPolicy extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('Policy');
        
        $data = new stdClass();
        $data->privilegeGroupId = $data->privilegeGroupIdS = '1';
        $data->linkList = $data->linkListS = array (  0 => '102',  1 => '29',);
        $data->materialGenderList = $data->materialGenderListS = array (  0 => '2',  1 => '3',  2 => '4',);
        $data->loanDate = $data->loanDateS = '20/10/2011';
        $data->loanDays = $data->loanDaysS = '1';
        $data->loanLimit = $data->loanLimitS = '1';
        $data->renewalLimit = $data->renewalLimitS = '1';
        $data->reserveLimit = $data->reserveLimitS = '1';
        $data->fineValue = $data->fineValueS = '1';
        $data->penaltyByDelay = $data->penaltyByDelayS = '1';
        $data->daysOfWaitForReserve = $data->daysOfWaitForReserveS = '1';
        $data->reserveLimitInInitialLevel = $data->reserveLimitInInitialLevelS = '1';
        $data->daysOfWaitForReserveInInitialLevel = $data->daysOfWaitForReserveInInitialLevelS = '1';
        $data->renewalWebLimit = $data->renewalWebLimitS = '1';
        $data->renewalWebBonus = $data->renewalWebBonusS = 'f';
        $data->additionalDaysForHolidays = $data->additionalDaysForHolidaysS = '1';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';

        $this->business->setData($data);
    }
}
?>