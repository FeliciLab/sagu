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
 * Class created on 25/11/2013
 * 
 **/

class GSession
{       
     const GSESSION_OPERATION_LOAN = 1;
     const GSESSION_OPERATION_RETURN = 2;
     const GSESSION_OPERATION_RENEW = 3;
    
    
     /*
     * Esqueleto do openSession
     * Parametros:
     * 
     * $equipamentId :: identificador do equipamento sip, já cadastrado
     * $personId :: identificador da pessoa que esta utilizando o equipamento
     * 
     * Descrição: Abre sessão
     * 
     * Criado por: Tcharles Silva
     * Em: 25/11/2013
     * Ultima Atualização por: 
     * Em: 
     * Motivo:
     */
    public static function openSession($equipamentId, $personId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $busSession = $MIOLO->getBusiness($module, 'BusSession');
        
        $busSession->sipequipamentId = $equipamentId;
        $busSession->personId = $personId;
        $busSession->isClosed = DB_FALSE;

        $res = $busSession->insertSession();
        if($res)
        {
            $session = $busSession->searchSession(TRUE);
            
            return $session[0];
        }
        return $res;
    }
    
    /*
     * Esqueleto do closeSession
     * Parametros:
     * 
     * $equipamentId :: identificador do equipamento sip, já cadastrado
     * 
     * Descrição: Fecha a sessão, colocando o isClosed como true
     * 
     * Criado por: Tcharles Silva
     * Em: 25/11/2013
     * Ultima Atualização por: 
     * Em: 
     * Motivo:
     */
    public static function closeSession($equipamentId)
    {
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        //Intancia as duas ::BusSessions
        $busSession = $MIOLO->getBusiness($module, 'BusSession');
        
        //Muda o valor de isClosed para true;
        $session = self::getOpenSession($equipamentId);
        $busSession->setData($session);
        
        $busSession->isClosed = DB_TRUE;
        
        if($busSession->sessionId)
        {
            $rs = $busSession->updateSession();
            return $rs;
        }
        else
        {
            return false;
        }
    }
    
    public static function getSession($sessionId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        //Intancia as duas ::BusSessions
        $busSession = $MIOLO->getBusiness($module, 'BusSession');
        $res = $busSession->getSession($sessionId);
        return $res;
    }
    
    public static function getOpenSession($equipamentId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        //Intancia as duas ::BusSessions/Operations
        $busSession = $MIOLO->getBusiness($module, 'BusSession');
        $busSessionOperation = $MIOLO->getBusiness($module, 'BusSessionOperation');
        
        $busSession->sipequipamentId = $equipamentId;
        $busSession->isClosed = f;
        
        //Testa para ver se encontra sessão aberta.
        $session = $busSession->searchSession(TRUE);
        
        if($session)
        {
            return $session[0];
        }
        else
        {
            return false;
        }
    }
    
    public static function addOperation($equipamentId,$personId, $operationTypeId, $operationId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busSessionOperation = $MIOLO->getBusiness($module, 'BusSessionOperation');
        
        //Realiza chamada para o Método getOpenSession, verificando se já há sessão aberta.
        $session = self::getOpenSession($equipamentId);

        //Caso não há, irá abrir uma sessão
        if( !$session )
        {
            $session = GSession::openSession($equipamentId, $personId);
        }
        
        //Atribui valor de sessionId para o campo sessionId
        $busSessionOperation->sessionId =  $session->sessionId;

        //Testa para ver se a operação será emprestimo
        if ( $operationTypeId == GSession::GSESSION_OPERATION_LOAN )
        {
            $busSessionOperation->operation = GSession::GSESSION_OPERATION_LOAN;
            $busSessionOperation->loanId = $operationId;
        }
        //Testa para ver se a operação será devolução
        else if ( $operationTypeId == GSession::GSESSION_OPERATION_RETURN )
        {
            $busSessionOperation->operation = GSession::GSESSION_OPERATION_RETURN;
            $busSessionOperation->returnRegisterId = $operationId;
        }
        //Será renovação
        else
        {
            $busSessionOperation->operation = GSession::GSESSION_OPERATION_RENEW;
            $busSessionOperation->renewId = $operationId;
        }
        
        //Insere os dados na tabela
        $res = $busSessionOperation->insertSessionOperation();
        
        return $res;
    }
    
    
}
?>
