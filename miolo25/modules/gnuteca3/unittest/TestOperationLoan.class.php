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
 *  Teste unitário do business "busOperationLoan".
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 06/01/2012
 *
 **/
include_once '../classes/GUnitTest.class.php';
$MIOLO->getClass('gnuteca3', 'GDate');
$MIOLO->getClass('gnuteca3', 'GBusiness');
$MIOLO->getClass('gnuteca3', 'GMessages');
class TestOperationReturn extends GUnitTest
{
    private $business;
    
    private $module;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->module = 'gnuteca3';
        $this->business = $this->MIOLO->getBusiness($this->module, 'BusOperationLoan');
        
        $data = new stdClass();
        
        $data->operator = 'Gnuteca';
        $data->location = 10;
        $data->libraryUnitId  = 1;
        $data->personId = 2;
        
        $this->business->setData($data);
    }
    
    public function test()
    {
        $this->exibe('Criando GOperationLoan -> (Operação de empréstimo)');
        $this->exibe('Requisitos para funiconar: Uma unidade com código 1. Uma pessoa com código 1.');
        $this->exibe('');

        $this->addExemplaryControl('1');
        $this->addExemplaryControl('2');
        $this->addLoan('1');
        $this->addLoan('2');
        
        $this->exibe($this->business->communicateDelayedLoan());

        $this->exibe('');
        $this->exibe('Adicionando exemplares a ser gerado empréstimos');
        
        $exemplares = array(array('00008801', ID_LOANTYPE_DEFAULT),
                            array('00008802', ID_LOANTYPE_FORCED),
                            array('00008803', ID_LOANTYPE_MOMENTARY));

        foreach ($exemplares as $e)
        {
            $this->exibe("Adicionando exemplar \$this->business->addItemNumber('{$e[0]}', {$e[1]})");
            $this->business->addItemNumber($e[0], $e[1]);
        }

        $this->exibe('');
        $this->exibe('Finalizando devolução');
        $this->business->finalize();
    }
    
    private function addExemplaryControl($itemNumber)
    {
        $busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');

        $busExemplaryControl->deleteExemplaryControl($itemNumber);

        $busExemplaryControl->controlNumber     = 1;
        $busExemplaryControl->itemNumber        = $itemNumber;
        $busExemplaryControl->libraryUnitId     = 1;
        $busExemplaryControl->originalLibraryUnitId     = 1;
        $busExemplaryControl->acquisitionType   = 1;
        $busExemplaryControl->exemplaryStatusId = 1;
        $busExemplaryControl->materialGenderId    = 1;
        $busExemplaryControl->entranceDate      = '2008-10-08';
        $busExemplaryControl->lowDate           = '2008-10-10';
        $busExemplaryControl->insertExemplaryControl();
        
        $this->addMaterialControl($busExemplaryControl->controlNumber, $busExemplaryControl->materialGenderId);
    }

    private function addMaterialControl($controlNumber, $materialGenderId)
    {
        $busMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $busMaterialControl->deleteMaterialControl($controlNumber);
        $busMaterialControl->controlNumber  = $controlNumber;
        $busMaterialControl->entranceDate   = '2008-10-01';
        $busMaterialControl->lastChangeDate = '2008-10-05';
        $busMaterialControl->materialGenderId = $materialGenderId;
        $busMaterialControl->category = 'BK';
        $busMaterialControl->level = 4;
        
        $busMaterialControl->insertMaterialControl();
    }

    private function addLoan($itemNumber)
    {
        $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');

        $busLoan->itemNumberS = $itemNumber;
        $search = $busLoan->searchLoan(TRUE);

        $busLoan->personId           = $this->business->personId;
        $busLoan->loanTypeId         = 1;
        $busLoan->linkId             = 1;
        $busLoan->privilegeGroupId   = 1;
        $busLoan->itemNumber         = $itemNumber;
        $busLoan->libraryUnitId      = 1;
        $busLoan->loanDate           = '2008-10-01';
        $busLoan->loanOperator       = $this->business->getOperator();
        $busLoan->returnForecastDate = '2008-10-10';
        $busLoan->renewalAmount      = 1;
        $busLoan->renewalWebAmount   = 1;
        $busLoan->renewalWebBonus    = true;
        //$bus->insertLoan();
    }
    
}
?>