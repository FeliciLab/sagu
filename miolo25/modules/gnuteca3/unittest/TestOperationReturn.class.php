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
 *  Teste unitário do business "busOperationReturn".
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
    
    private $busMaterialControl;
    
    private $module;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->module = 'gnuteca3';
        $this->business = $this->MIOLO->getBusiness($this->module, 'BusOperationReturn');
        
        $data = new stdClass();
        
        $data->operator = 'Gnuteca';
        $data->location = 10;
        $data->libraryUnitId  = 1;
        $data->checkIsDelayAndHasReserve = true;
        
        $this->business->setData($data);
        
        $this->busMaterialControl = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialControl');
    }
    
    public function test()
    {
        $this->busMaterialControl->beginTransaction();
        
        $this->busMaterialControl->deleteMaterialControl(1);
        $this->busMaterialControl->controlNumber   = 1;
        $this->busMaterialControl->entranceDate    = GDate::now()->getDate(GDate::MASK_DATE_DB);
        $this->busMaterialControl->lastChangeDate  = GDate::now()->getDate(GDate::MASK_DATE_DB);
        $this->busMaterialControl->materialGenderId  = 1;
        $this->busMaterialControl->category = 'BK';
        $this->busMaterialControl->level = 4;
        $this->busMaterialControl->insertMaterialControl();
        
        $this->exibe('Criando GnutecaReturnOperation -> (Operação de devolução)');
        $this->exibe('Requisitos para funiconar: Uma unidade com código 1. Uma pessoa com código 1.');
        $this->exibe('');

        $this->exibe('Adicionando exemplares a serem devolvidos');
        $exemplares = array('GOR-01', 'GOR-02', 'GOR-03', 'GOR-04', 'GOR-05', 'GOR-06');

        //Gera casos para testes
        $this->montaAmbienteExemplar('GOR-03');
        $this->montaAmbienteExemplar('GOR-04');
        $this->montaAmbienteExemplar('GOR-05');
        $this->montaAmbienteExemplar('GOR-06');

        $this->montaAmbienteEmprestimo('GOR-03');
        $this->montaAmbienteEmprestimo('GOR-04', true); //Atrasará e gerará uma multa em gtcFine
        $this->montaAmbienteEmprestimo('GOR-05', false, ID_LOANTYPE_MOMENTARY);
        $this->montaAmbienteEmprestimo('GOR-06');

        $this->montaAmbienteReserva('GOR-04');

        foreach ($exemplares as $e)
        {
            $this->exibe('Verificando exemplar $this->business->checkItemNumber(\''.$e.'\')');
            
            if ($this->business->checkItemNumber($e))
            {
                $quest = $this->business->getQuestions();
                
                if ($quest)
                {
                    foreach ($quest as $q)
                    {
                        $this->exibe("-" . $q->message);
                    }
                }
                
                $this->exibe('Adicionando exemplar $this->business->addItemNumber(\''.$e.'\')');
                $this->business->addItemNumber($e);
            }
            else
            {
                $this->exibe('Exemplar '.$e.' não foi inserido na lista pois: ');
                
                foreach ($errors = $this->business->getErrors() as $e)
                {
                    $this->exibe("-" . $e->message);
                }
            }
            
            $this->exibe('');
        }

        $this->exibe('');
        $this->exibe('Finalizando devolução');
        $this->business->finalize();

        // Verifica multas
        $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        
        foreach ($exemplares as $e)
        {
            $busLoan->itemNumberS = $e;
            $search = $busLoan->searchLoan(TRUE);
            $loan = $search[0];

            if ($loan->loanId)
            {
                $busFine->loanIdS = $loan->loanId;
                $search = $busFine->searchFine(TRUE);
                
                if ($search)
                {
                    $value = $search[0]->value;
                    $this->exibe('O exemplar ' . $e . ' recebeu uma multa no valor de R$: ' . $value . ', pois a devolucao estava prevista para ' . GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER) . ', e ocorreu no dia ' . GDate::construct($loan->returnDate)->getDate(GDate::MASK_DATE_USER));
                }
            }
        }
        
        $this->busMaterialControl->commitTransaction();
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
        
}
?>