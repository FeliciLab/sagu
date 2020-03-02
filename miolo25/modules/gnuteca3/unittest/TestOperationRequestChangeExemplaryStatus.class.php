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
 *  Teste unitário do business "busOperationRequestChangeExemplaryStatus".
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
class TestOperationRequestChangeExemplaryStatus extends GUnitTest
{
    private $business;
    
    private $module;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->module = 'gnuteca3';
    }
    
    public function test()
    {
        $busReqChanExeStsAccs = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusAccess');
        $busReqChanExeStsComp = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $busReqChanExeStsSts = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusStatus');
        $busOperation = $this->MIOLO->getBusiness($this->module, 'BusOperationRequestChangeExemplaryStatus');

        $this->exibe("================================================================================");
        $this->exibe("     Testando Operações de Solicitação de Alteração de Estado de Exemplar");
        $this->exibe("     Business: BusOperationRequestChangeExemplaryStatus");
        $this->exibe("================================================================================");

        $this->exibe("---------------------");
        $this->exibe("- BusRequestChangeExemplaryStatusAccess");
        $this->exibe("- Inserindo Permissões");
        $this->exibe("------------------");

        //Deletando os acesso caso ja existam
        $ok = $busReqChanExeStsAccs->deleteRequestChangeExemplaryStatusAccess(1,7);
        $this->exibe("DELETE basLinkId = 1 && exemplaryStatusId = 7");
        $this->exibe(!$ok ? 'Não foi possível deletar!' : 'Deletado com sucesso!!');
        
        $ok = $busReqChanExeStsAccs->deleteRequestChangeExemplaryStatusAccess(1,3);
        $this->exibe("DELETE basLinkId = 1 && exemplaryStatusId = 3");
        $this->exibe(!$ok ? 'Não foi possível DELETAR!' : 'Deletado com sucesso!!');

        // INSERIDO ACESSO PARA GRUPO 1 SOLICITAR EXEMPLAR STATUS 7
        $busReqChanExeStsAccs->basLinkId            =  1;
        $busReqChanExeStsAccs->exemplaryStatusId    =  7;
        $ok = $busReqChanExeStsAccs->insertRequestChangeExemplaryStatusAccess();

        $this->exibe("\nInserindo basLinkId = 1 && exemplaryStatusId = 7");
        $this->exibe(!$ok ? 'Não foi possível inserir!' : 'Inserido com sucesso!!');

        // INSERIDO ACESSO PARA GRUPO 1 SOLICITAR EXEMPLAR STATUS 3
        $busReqChanExeStsAccs->basLinkId            =  1;
        $busReqChanExeStsAccs->exemplaryStatusId    =  3;
        $ok = $busReqChanExeStsAccs->insertRequestChangeExemplaryStatusAccess();

        $this->exibe("\nInserindo basLinkId = 1 && exemplaryStatusId = 3");
        $this->exibe(!$ok ? 'Não foi possível inserir!' : 'Inserido com sucesso!!');

        $this->exibe("------------------");
        $this->exibe("- Testes de Acesso");

        $busOperation->clean();
        $busOperation->setFutureStatusId(7);
        $busOperation->setPersonId(1);
        $access1 = $busOperation->checkAccess();
        $this->exibe( "Acesso " . ($access1 ? "" : "NÃO") . " permitido para personId = 1 && exemplaryStatusId = 7" );
        $busOperation->clean();
        $busOperation->setFutureStatusId(3);
        $busOperation->setPersonId(1);
        $access2 = $busOperation->checkAccess();
        $this->exibe( "Acesso " . ($access2 ? "" : "NÃO") . " permitido para personId = 1 && exemplaryStatusId = 3" );
        $busOperation->clean();
        $busOperation->setFutureStatusId(5);
        $busOperation->setPersonId(1);
        $access3 = $busOperation->checkAccess();
        $this->exibe( "Acesso " . ($access3 ? "" : "NÃO") . " permitido para personId = 1 && exemplaryStatusId = 5" );

        $this->exibe("------------------");
        $this->exibe("- Teste de INSERÇÃO");

        if($access1)
        {
            $busOperation->clean();
            $busOperation->setLibraryUnit(1);
            $busOperation->setPersonId(1);
            $busOperation->setDate();
            $busOperation->setRequestChangeExemplaryStatusStatusId(1);
            $busOperation->setFinalDate(null, 40);
            $this->exibe("- Accesso 1 - Inserindo para personId 1 && exemplaryStatusId = 7");
            $composition = array('1', '785569', '1452365');
            $this->exibe("- Accesso 1 - Formando a composição: ". implode(", ", $composition));
            $checkComposition = $busOperation->checkComposition($composition);
            $this->exibe("- Accesso 1 - Composição Válida: ". implode(", ", $busOperation->getRequestComposition()));

            //INSERT
            $insert = $busOperation->insertRequest();
            $this->exibe(' == ' . ($insert ? 'OK insert 1' : "ERROR insert 1: ". $busOperation->getMsg()) );
            
            if($insert)
            {
                $aprove = $busOperation->aproveRequest(null, 1);
                $this->exibe(' == ' . ($aprove ? 'OK aproved 1' : "ERROR not aproved 1: ". $busOperation->getMsg()) );

                if($aprove)
                {
                    $reprove = $busOperation->reproveRequest();
                    $this->exibe(' == ' . ($reprove ? 'OK reproved 1' : "ERROR not reproved 1: ". $busOperation->getMsg()) );

                    if($reprove)
                    {
                        $cancel = $busOperation->cancelRequest();
                        $this->exibe(' == ' . ($cancel ? 'OK cancel 1' : "ERROR not cancel 1: ". $busOperation->getMsg()) );
                    }
                }
            }

        }
        
        if($access2)
        {
            $this->exibe("- Accesso 2 - Inserindo para personId 1 && exemplaryStatusId = 3");
        }

        $this->exibe("------------------");
        $this->exibe("- Lista de Estado de solicitação");
        $this->exibe($busReqChanExeStsSts->listRequestChangeExemplaryStatusStatus());
        $this->exibe("------------------");
    }
}
?>