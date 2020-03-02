<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe para administrar sessão
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 28/11/2013
 * 
 **/

include("GnutecaWebServices.class.php");
$MIOLO->getClass('gnuteca3', 'GSession');

class GSipCirculation extends GSession
{       
     /*
     * Esqueleto do Loan
     * Parametros:
     * 
     * $equipamentId :: identificador do equipamento sip, já cadastrado
     * $personId :: identificador da pessoa que esta utilizando o equipamento
     * 
     * Descrição: Método utilizado pelos webservices de empréstimo/devolução da automação
     * 
     * Criado por: Tcharles Silva
     * Em: 25/11/2013
     * Ultima Atualização por: 
     * Em: 
     * Motivo:
     */
    public static function doLoan($padraoLoan, $termId, $personId, $itemNumber, $loanTypeId, $pessoa = NULL, $fazRen, $offline = FALSE)
    {
        
        /* PADRÕES DE EMPRÉSTIMOS *
        $padraoLoan = '1';
        /* - Utilizado em: addOperation
          1 | Empréstimo
          2 | Devolução
          3 | Renovação         */
        
        /* LoanTypeId - Modo de empréstimo *
        $loanTypeId = "4";
          1 | Padrão
          2 | Forçado
          3 | Momentâneo
          4 | Padrão auto-empréstimo
          5 | Offline         */
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        //Instancia os atributos com o objeto Bus$Nome respectivamente
        $busSipEquipament = $MIOLO->getBusiness($module, 'BusSipEquipament');
        $busOperationLoanSip = $MIOLO->getBusiness($module, 'BusOperationLoanSip');
        $busLoan = $MIOLO->getBusiness($module, 'BusLoan');
        $busRenew = $MIOLO->getBusiness($module, 'BusRenew');
        $busMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        
        try
        {
            //Recebe informações do equipamento
            $dadosSip = $busSipEquipament->getSipEquipament($termId);
            
            //Seta se o equipamento faz renovação
            $fazRen = $dadosSip->makeRenew;

            //Setar libraryUnitId
            $busOperationLoanSip->setLibraryUnit($dadosSip->libraryUnitId);

            //Setar Location
            //$busOperationLoanSip->location = $dadosSip->locationformaterialmovementid;
            $busOperationLoanSip->setLocation($dadosSip->locationformaterialmovementid);

            
            //Seta a pessoa e coloca o resultado na variável
            if($pessoa)
            {
                $person = $pessoa;
            }else
            {
                $person = $busOperationLoanSip->setPerson($personId);
            }
            
            //Atualiza o personId do número de cartão para o número de usuário
            $personId = $person->personId;
            
            //Verifica se foi selecionado uma pessoa válida para a operação
            if ($person)
            {
                $noProblem = $busOperationLoanSip->addItemNumber($itemNumber, $loanTypeId, $person, $offline);
                if($noProblem)
                {
                    //Se não tiver problema, executa o finalize
                    $teste = $busOperationLoanSip->finalize(NULL, $fazRen);
                    $busOperationLoanSip->clearItemsLoan();
                }
            }
            
            //Logo após, pega as mensagens de erro com o getMessages...
            $retorno['screenMsg'] = $busOperationLoanSip->getMessages();
        }
        catch (Exception $e)
        {
           
           $retorno['ok'] = 'N';
           $retorno['screenMsg'] = $e->getMessage();     
        }
        
        //Verifica se a String é um erro, caso for, retorna a mensagem de erro.
        $err = $retorno[screenMsg];
        $check = substr($err, 0, 6);
        if($check == "[ERRO]")
        {
            return $retorno;
        }
        
        //Se o finalize foi executado sem erros
        if($teste)
        {
            //Deixar a data YYYYMMDDHHMMSS
            $data = GDate::getYYYYMMDDHHMMSS($busOperationLoanSip->operationDate);
            

            
            //Verifica se é uma renovação            
            if($busOperationLoanSip->isRenew)
            {
                //Essa é uma operação de renovação
                
                //Obtem título do material
                $titulo = $busMaterial->getMaterialTitleByItemNumber($itemNumber);
                
                //A variável $busOperationLoanSip->loanNum contém o renewId
                //Busca o registro da renovação
                $busRenew = $busRenew->getRenew($busOperationLoanSip->loanNum);
                
                //Obtem o registro da gtcLoan referente à esta renovação
                $registroLoan = $busLoan->getLoan($busRenew->loanId);
                
                //Obtem a data da devolução diretamente da loan
                $rfd = GDate::getYYYYMMDDHHMMSS($registroLoan->returnForecastDate);
                
                $vf = GSession::addOperation($termId, $personId, 3, $busOperationLoanSip->loanNum);

                //Formatando retornos
                $retorno['ok'] = $vf ? "Y" : "N";
                $retorno["renewalOk"] = "Y";
                $retorno['dateTime'] = $data;
                $retorno['instID'] = $dadosSip->libraryUnitId;
                $retorno['patronID'] = $personId;
                $retorno['itemID'] = $itemNumber;
                if(empty($titulo))
                {
                    $titulo = ' ';
                }
                $retorno['titleID'] = $titulo;
                $retorno['dueDate'] = $rfd;
            }
            else
            {
                //Reconhecendo que o método é empréstimo
                $titulo = $busMaterial->getMaterialTitleByItemNumber($itemNumber);
                
                //Instancia um objeto busLoan para consumir seus atributos
                $busLoan = $busLoan->getLoan($busOperationLoanSip->loanNum);
                
                //Data de retorno já no padrao abaixo
                $rfd = GDate::getYYYYMMDDHHMMSS($busLoan->returnForecastDate);
                
                //Coloca na sessão o empréstimo
                $vf = GSession::addOperation($termId, $personId, 1, $busOperationLoanSip->loanNum); 
                
                //Formatando retornos
                $retorno['ok'] = $vf ? "Y" : "N";
                $retorno['renewalOk'] = "N";
                $retorno['dateTime'] = $data;
                $retorno['instID'] = $dadosSip->libraryUnitId;
                $retorno['patronID'] = $personId;
                $retorno['itemID'] = $itemNumber;
                if(empty($titulo))
                {
                    $titulo = ' ';
                }
                $retorno['titleID'] = $titulo;
                $retorno['dueDate'] = $rfd;
            }
            
            return $retorno;
        }
    }
    
    public static function doReturn($termId, $itemNumber, $offline = FALSE)
    {
        /* PADRÕES DE EMPRÉSTIMOS *
        $padraoLoan = '1';
        /* - Utilizado em: addOperation
          1 | Empréstimo
          2 | Devolução
          3 | Renovação         */
        
        /* ReturnTypeId - Modo de devolução *
        $returnTypeId = "4";
        $returnTypeId = "3";
          1 | Apagados
          2 | Utilização Local
          3 | Padrão Auto-Atendimento
          4 | Prédio 11
          5 | Prédio 16         */
        
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        //Instancia os atributos com o objeto Bus$Nome respectivamente
        $busSipEquipament = $MIOLO->getBusiness($module, 'BusSipEquipament');
        $busOperationReturnSip = $MIOLO->getBusiness($module, 'BusOperationReturnSip');
        $busMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $dadosBin = $MIOLO->getBusiness($module, 'BusSipEquipamentBinRules');
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        
        //Define titulo do livro
        $titulo = $busMaterial->getMaterialTitleByItemNumber($itemNumber);
        
        //Seta tipo de retorno
        if($offline)
        {
            $busOperationReturnSip->setReturnType(ID_RETURNTYPE_OFFLINE);
        }
        else
        {
            $busOperationReturnSip->setReturnType(ID_RETURNTYPE_DEFAULTSIPEQUIPAMENT);
        }
        
        try
        {
            //Recebe informações do equipamento
            $dadosSip = $busSipEquipament->getSipEquipament($termId);
            
            //Setar libraryUnitId
            $busOperationReturnSip->setLibraryUnit($dadosSip->libraryUnitId);

            //Setar Location
            $busOperationReturnSip->setLocation($dadosSip->locationformaterialmovementid);

            //Verifica o exemplar e coloca o resultado na variável
            $exemplarOk = $busOperationReturnSip->checkItemNumber($itemNumber);
            
            $retorno['screenMsg'] = $busOperationReturnSip->msgs;
            
            if($exemplarOk && is_null($retorno[screenMsg]))
            {
                //Se estiver tudo certo com o exemplar, adiciona o item
                $addItemOk = $busOperationReturnSip->addItemNumber($itemNumber);

                //Atribui as mensagens a variavel retorno
                $retorno['screenMsg'] = $busOperationReturnSip->msgs;
                
                //Testa para ver se a operação anterior foi sem falhas e sem mensagens de erro
                if($addItemOk && is_null($retorno[screenMsg]))
                {
                    //Se estiver tudo certo, executa o finalize.
                    $finalOk = $busOperationReturnSip->finalize();
                }
            }
        }
        catch (Exception $e)
        {
           $retorno['ok'] = '0';
           $retorno['screenMsg'] = $e->getMessage();
           return $retorno;
        }
        
        $retorno['screenMsg'] = $busOperationReturnSip->msgs;

        //Verifica se a String é um erro, caso for, retorna a mensagem de erro.
        $err = $retorno[screenMsg];
        $check = substr($err, 0, 6);
        if($check == "[ERRO]")
        {
            $retorno['ok'] = '0';
            $retorno['resensitize'] = 'N';
            $retorno['alert'] = 'Y';
            $retorno['dateTime'] = $data;
            $retorno['instID'] =  $dadosSip->libraryUnitId;
            $retorno['itemID'] =  $itemNumber;
            $retorno['titleID'] = $titulo;
            $retorno['sortBin'] = 'null';
            $retorno['patronID'] = $busOperationReturnSip->personId;
            
            return $retorno;
        }
        
        if($finalOk)
        {
            $sessionOk = GSession::addOperation($termId, $busOperationReturnSip->personId, 2, $busOperationReturnSip->returnId);
            
            $dadosBin->sipEquipamentId = $termId;
            $bin = $dadosBin->searchSipEquipamentBinRules(TRUE);
            
            //Obtem informação para o bin default
            $padraoBin = $busSipEquipament->binDefault;

            if($sessionOk)
            {
                $data = GDate::getYYYYMMDDHHMMSS($busOperationReturnSip->operationDate);
                
                /*
                 * Preparando o sortBin
                 * Recebe informações de bin do equipamento
                 * E também, realiza pesquisa para saber o exato estado do material no momento
                 */
                $busOperationReturnSip->fStatus = $busExemplaryControl->getExemplaryStatus($itemNumber);

                if(empty($bin))
                {
                    $sortBin = $padraoBin;
                }else
                {
                    foreach($bin as $b)
                    {
                        if($busOperationReturnSip->fStatus == $b->exemplaryStatusId)
                        {
                            $sortBin = $b->bin;
                        }
                    }
                    if(empty($sortBin))
                    {
                        $sortBin = $padraoBin;
                    }
                }

                //Operação foi um sucesso, formatando retorno

                $retorno['ok'] = '1';
                $retorno['resensitize'] = 'Y';
                $retorno['alert'] = 'N';
                $retorno['dateTime'] = $data;
                $retorno['instID'] =  $dadosSip->libraryUnitId;
                $retorno['itemID'] =  $itemNumber;
                $retorno['titleID'] = $titulo;
                $retorno['sortBin'] = $sortBin;
                $retorno['patronID'] = $busOperationReturnSip->personId;

                return $retorno;
            }
            else
            {
                $retorno['ok'] = '0';
                $retorno['resensitize'] = 'N';
                $retorno['alert'] = 'Y';
                $retorno['dateTime'] = $data;
                $retorno['instID'] =  $dadosSip->libraryUnitId;
                $retorno['itemID'] =  $itemNumber;
                $retorno['titleID'] = $titulo;
                $retorno['sortBin'] = $padraoBin;
                $retorno['patronID'] = $busOperationReturnSip->personId;
                return $retorno;
            }

        }

    }
    
    /*
     * Esta classe irá verificar se uma pessoa tem penalidade em aberto e também multas em aberto.
     * 
     * Será utilizada nos webservices dos equipamentos SIP
     */
    public static function getRightWith($verif, $person, $linkid, $idOperation, $libraryUnitId )
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busPenalty = $MIOLO->getBusiness($module, 'BusPenalty');
        $busFine = $MIOLO->getBusiness($module, 'BusFine');
        $busRight = $MIOLO->getBusiness($module, 'BusRight');

        if($verif == 'p')
        {
            $busPenalty->personIdS = $person;
            $busPenalty->personId = $person;
            $busPenalty->onlyActive = true;
            $penalty = $busPenalty->searchPenalty();

            //Caso tenha penalidade
            if($penalty)
            {
            //verifica se tem direito de retirar com penalidade
                $busRight->linkId = $linkid;
                //$busRight->materialGenderId = $materialG;
                $busRight->operationId = $idOperation;
                $comPenal = $busRight->verifyRightSip();

                if($comPenal == FALSE)
                {
                    return true;
                }
            }
            return false;
        }

        if($verif == 'f')
        {
            //Buscar todas as multas que estão no estado de: Em aberto
            $fines = $busFine->getFines($libraryUnitId, $person, ID_FINESTATUS_OPEN);
            
            if($fines)
            {
                //caso tiver, verifica se tem direito de retirar com multas
                $busRight->linkId = $linkid;
                $busRight->operationId = ID_OPERATION_LOAN_FINE;
                
                $comMult = $busRight->verifyRightSip();

                if($comMult == FALSE)
                {
                    return true;
                }
            }
            return false;
        }

        if($verif == 'l')
        {
            $busRight->linkId = $linkid;            
            $busRight->operationId = $idOperation;

            return $busRight->verifyRightSip();
        }
    }
    
    /*
     * Esta classe será utilizaada para garantir o tamanho de cada campo de retorno do SIP
     * O tamanho máximo de um campo é 258 caracteres.
     * Feito por: Tcharles S.
     * Em: 10/07/2015
     */
    public static function setSipSize($string)
    {
        //Caso a string passada, seja maior do que 258 caracteres, irá cortar a string nas 0-257 posições.
        
        if(strlen($string) > 200)
        {
            $string = substr($string, 0, 200);
            $string .= "...";
        }
        
        return $string;
        
    }
    
    public static function usingSmartReader()
    {
        //Verifica se o valor da preferência SIP_CIRCULATION_READER não está ativa
        return $sipCirculationReader = MUtil::getBooleanValue(SIP_CIRCULATION_READER);
    }
    
    /* Verifica se é possivel utilizar o terminal em caso de exceder empréstimos
     * Criado em: 05/08/2014
     */
    public static function ignoreVerifyAcessToLoan($termID)
    {
        //Pega o terminal corrente
        $myTerm = GSipCirculation::getCurrentTerminal($termID);
        
        if($myTerm)
        {
            //caso tenha psLoanLimit como true, ignora verificação
            if(MUtil::getBooleanValue($myTerm->psLoanlimit))
            {
                return true;
            }
            else
            {
                //caso não tenha, não ignora
                return false;
            }
        }
        else
        {
            //caso não exista terminal, não ignora a verificação
            return false;
        }
    }
    
    /* Verifica se é possivel utilizar o terminal em caso de exceder atrasos
     * Criado em: 05/08/2014
     */
    public static function ignoreVerifyAcessToOverdue($termID)
    {
        $myTerm = GSipCirculation::getCurrentTerminal($termID);
        
        if($myTerm)
        {
            //caso tenha psLoanLimit como true, ignora verificação
            if(MUtil::getBooleanValue($myTerm->psOverduelimit))
            {
                return true;
            }
            else
            {
                //caso não tenha, não ignora
                return false;
            }
        }
        else
        {
            //caso não exista terminal, não ignora a verificação
            return false;
        }
    }
    
    /* Verifica se é possivel utilizar o terminal em caso de exceder penalidades
     * Criado em: 05/08/2014
     */
    public static function ignoreVerifyAcessToPenalty($termID)
    {
        $myTerm = GSipCirculation::getCurrentTerminal($termID);
        if($myTerm)
        {
            //caso tenha psLoanLimit como true, ignora verificação
            if(MUtil::getBooleanValue($myTerm->psPenaltylimit))
            {
                return true;
            }
            else
            {
                //caso não tenha, não ignora
                return false;
            }
        }
        else
        {
            //caso não exista terminal, não ignora a verificação
            return false;
        }
    }
    
    /* Verifica se é possivel utilizar o terminal em caso de exceder multas
     * Criado em: 05/08/2014
     */
    public static function ignoreVerifyAcessToFine($termID)
    {
        $myTerm = GSipCirculation::getCurrentTerminal($termID);
        if($myTerm)
        {
            //caso tenha psLoanLimit como true, ignora verificação
            if(MUtil::getBooleanValue($myTerm->psFinelimit))
            {
                return true;
            }
            else
            {
                //caso não tenha, não ignora
                return false;
            }
        }
        else
        {
            //caso não exista terminal, não ignora a verificação
            return false;
        }
    }
    
    public static function getCurrentTerminal($termID)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $bSeQ = $MIOLO->getBusiness($module, 'BusSipEquipament');
        
        //Recebe terminal utilizado
        $myEq = $bSeQ->getSipEquipament($termID);
        
        //Caso encontre o equipamento
        if($myEq)
        {
            return $myEq;
            
        }else
        {
            //Retorno de falso, não ignora a verificação
            return false;
        }
    }
}
?>