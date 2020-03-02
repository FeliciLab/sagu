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
 *  Teste unitário do business "busOperationChangeStatus".
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
class TestOperationChangeStatus extends GUnitTest
{
    private $business;
    
    private $module;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->module = 'gnuteca3';
        $this->business = $this->MIOLO->getBusiness($this->module, 'BusOperationChangeStatus');
        
        $data = new stdClass();
        
        $data->operator = 'Gnuteca';
        $data->changeType        = 1;
        $data->libraryUnitId     = 1;
        $data->level             = 1;
        $data->exemplaryStatusId = 2;rue;
        
        $this->business->setData($data);
    }
    
    public function test()
    {
        $this->exibe('Criando BusOperationChangeStatus -> (Operação de mudança de estado)');
        $this->exibe('Requisitos para funiconar: Uma unidade com código 1. Uma pessoa com código 1.');
        $this->exibe('');

        $this->addExemplaryControl('1');
        $this->addExemplaryControl('2');
        $this->addLoan('00008801');
        $this->addLoan('00008802');

        $this->exibe('');
        $this->exibe('2 - Adicionando exemplares a ser gerado empréstimos');
        $exemplares = array('00008801',
                            '00008802',
                            '00008803');

        foreach ($exemplares as $e)
        {
            $this->exibe("Adicionando exemplar \$this->business->addItemNumber('{$e}');");
            $add = $this->business->addItemNumber($e);
            
            if (!$add)
            {
                $this->exibe($this->business->getErrors());
            }
            else
            {
                $itemNumber = $e;
                $lowDate    = '2008-01-10';
                $observation= 'Observacao';

                $exemplary = $this->business->getExemplary($e);
                
                if ($exemplary->low == 1)
                {
                    $this->business->setLowExemplary($itemNumber, $lowDate, $observation);
                }
                else if ($exemplary->low == 2)
                {
                    $this->business->setLowExemplary($itemNumber, $lowDate, $observation);
                }
            }
        }

        $this->exibe('');
        $this->exibe('Finalizando devolução');
        $this->business->finalize();
    }
    
    private function montaAmbienteExemplar($e, $libraryUnitId=1)
    {
        $busExemplary = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $busExemplary->controlNumber = 1;
        $busExemplary->itemNumber = $e;
        $busExemplary->originalLibraryUnitId = $libraryUnitId;
        $busExemplary->libraryUnitId = $libraryUnitId;
        $busExemplary->acquisitionType = 1;
        $busExemplary->exemplaryStatusId = 1;
        $busExemplary->materialGenderId = 1;
        
        $busExemplary->deleteExemplaryControl($e);
        $busExemplary->insertExemplaryControl();
    }

    private function montaAmbienteEmprestimo($e, $atrasa=false, $loanTypeId=1, $addReturnDate=false)
    {
        $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $busFineStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusFineStatusHistory');

        $busLoan->itemNumberS = $e;

        $loans = $busLoan->searchLoan();
        
        if ($loans)
        {
            foreach ($loans as $l)
            {
                $busFine->loanIdS = $l[0];
                $search = $busFine->searchFine(TRUE);
                
                if ($search)
                {
                    foreach ($search as $value)
                    {
                        $busFineStatusHistory->fineIdS = $value->fineId;
                        $searchFSH = $busFineStatusHistory->searchFineStatusHistory(TRUE);
                        
                        if ($searchFSH)
                        {
                            foreach ($searchFSH as $val)
                            {
                                $busFineStatusHistory->deleteFineStatusHistory($val->fineId, $val->fineStatusId);
                            }
                        }
                        
                        $busFine->deleteFine($value->fineId);
                    }
                }
                
                $busLoan->deleteLoan($l[0]);
            }
        }
        
        $busLoan->itemNumber = $e;
        $busLoan->loanTypeId = $loanTypeId;
        $busLoan->personId = 1;
        $busLoan->linkId = 1;
        $busLoan->libraryUnitId = 1;
        $busLoan->loanDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $busLoan->loanOperator = 'Teste';
        $busLoan->returnForecastDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $busLoan->renewalAmount = 5;
        $busLoan->renewalWebAmount = 5;
        $busLoan->renewalWebBonus = DB_TRUE;
        $busLoan->privilegeGroupId = 1;

        if ($atrasa)
        {
            $busLoan->returnForecastDate = '2008-09-25';
        }

        if ($addReturnDate)
        {
            $busLoan->returnDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        }

        $busLoan->insertLoan();
    }

    private function montaAmbienteReserva($e)
    {
        $busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $busReserveStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusReserveStatusHistory');
        $busReserveComposition = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition');

        $busReserveComposition->itemNumberS = $e;
        $rc = $busReserveComposition->searchReserveComposition();

        if ($rc)
        {
            foreach ($rc as $r)
            {
                $busReserveComposition->deleteReserveComposition($r[0], $r[1]);
                $busReserveStatusHistory->deleteReserveStatusHistory($r[0]);
                $busReserve->deleteReserve($r[0]);
            }
        }

        $busReserve->libraryUnitId = 1;
        $busReserve->personId = 1;
        $busReserve->reserveStatusId = 1;
        $busReserve->reserveTypeId = 1;
        $busReserve->requestedDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        
        $busReserve->insertReserve();

        $busReserveComposition->reserveId = $busReserve->reserveId;
        $busReserveComposition->itemNumber = $e;
        $busReserveComposition->isConfirmed = DB_FALSE;
        
        $busReserveComposition->insertReserveComposition();
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
        $busMaterialControl->category = 'BK';
        $busMaterialControl->level = 4;
        $busMaterialControl->materialGenderId = $materialGenderId;
        
        $busMaterialControl->insertMaterialControl();
    }

    private function addLoan($itemNumber)
    {
        $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');

        $busLoan->itemNumberS = itemNumber;
        $search = $busLoan->searchLoan(TRUE);

        $busLoan->personId           = $this->business->personId;
        $busLoan->loanTypeId         = 1;
        $busLoan->linkId             = 1;
        $busLoan->privilegeGroupId   = 1;
        $busLoan->itemNumber         = $itemNumber;
        $busLoan->libraryUnitId      = $this->business->libraryUnitId;
        $busLoan->loanDate           = '2008-10-01';
        $busLoan->loanOperator       = $this->business->operator;
        $busLoan->returnForecastDate = '2008-10-10';
        $busLoan->renewalAmount      = 1;
        $busLoan->renewalWebAmount   = 1;
        $busLoan->renewalWebBonus    = true;
        
        //$busLoan->insertLoan();
    }
        
}
?>