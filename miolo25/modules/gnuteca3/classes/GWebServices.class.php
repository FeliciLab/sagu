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
 * Gnuteca Web Services
 *
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 11/08/2009
 *
 **/


class GWebServices
{
    /**
     * Attributes
     */

    public      $MIOLO,
                $module;

    public      $busSoapAccess;

    protected   $clientId,
                $clientPassword,
                $clientIP,
                $authenticate;

    private     $className,
                $methodRequest,
                $webServiceObject,
                $clientObject,
                $errorCode;

    const WEB_SERVICES_OPERATOR         = "webServicesOperator";
    const ERROR_UNDEFINED_METHOD        = 1;
    const ERROR_UNDEFINED_PASS_ID       = 2;
    const ERROR_AUTHENTICATION_FAIL     = 3;
    const ERROR_DENIED_IP               = 4;
    const ERROR_SERVICE_UNAVALIABLE     = 5;
    const ERROR_ACCESS_BLOCKED          = 6;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->className        = get_class($this);
        $this->clientIP         = GServer::getRemoteAddress();
        
        $this->authenticate     = false;
        $this->clientObject     = false;

        // INICIA ELEMENTOS DO MIOLO
        $this->MIOLO            = MIOLO::getInstance();
        $this->module           = $this->MIOLO->getCurrentModule();

        $this->MIOLO            ->getClass($this->module, "GBusiness");
        $this->MIOLO            ->getClass($this->module, "GXML");
        $this->busSoapAccess    = $this->MIOLO->getBusiness($this->module, 'BusSoapAccess');

        // INICIA UMA SESSÂO PARA PERCISTENCIA
        //$this->startSession();
    }


    /**
     * Seta os attributos do cliente.
     *
     * @param integer   $clientId
     * @param string    $clientPassword
     */
    protected function __setClient($clientId, $clientPassword)
    {
        $this->clientId         = $clientId;
        $this->clientPassword   = $clientPassword;
    }


    /**
     * Seta o method que sera acessado
     *
     * @param string $methodName
     */
    protected function __setMethodRequest($methodName)
    {
        $this->methodRequest    = $methodName;
    }


    /**
     * checa permissão ao methodo
     *
     * @param string $method
     * @param boolean $die
     * @return boolean
     */
    protected function __checkMethod($method, $die = true)
    {        
        $this->__setMethodRequest($method);

        if(!$this->__getWebService())
        {
            return $this->__die($die);
        }

        if($this->webServiceObject->needAuthentication != 'f')
        {
            if(!$this->__authenticate())
            {
                return $this->__die($die);
            }
        }

        if($this->webServiceObject->checkClientIp != 'f')
        {
            $this->__getClient();
            if($this->clientObject->ip != $this->clientIP)
            {
                $this->errorCode = self::ERROR_DENIED_IP;
                return $this->__die($die);
            }
        }
        
        if($this->webServiceObject->needAuthentication != 'f')
        {
            $this->__getClient();
            if(!$this->busSoapAccess->checkAccess($this->webServiceObject->webServiceId ,$this->clientObject->soapClientId))
            {
                $this->errorCode = self::ERROR_ACCESS_BLOCKED;
                return $this->__die($die);
            }
        }

        return true;
    }




    /**
     * Função que autentica um cliente
     *
     * @return boolean
     */
    private function __authenticate()
    {
        if(!strlen($this->clientId) || !strlen($this->clientPassword))
        {
            $this->errorCode = self::ERROR_UNDEFINED_PASS_ID;
            return $this->__die(false);
        }

        if($this->authenticate)
        {
            return true;
        }

        if($this->authenticate = $this->busSoapAccess->authenticate($this->clientId, $this->clientPassword))
        {
            return $this->authenticate;
        }

        $this->errorCode = self::ERROR_AUTHENTICATION_FAIL;
        return $this->__die(false);
    }


    /**
     * Retorna o web service cadastrado na base
     *
     * @return object
     */
    protected function __getWebService($returnType = "object")
    {
        $this->webServiceObject = null;
        if(!strlen($this->methodRequest))
        {
            $this->errorCode = self::ERROR_UNDEFINED_METHOD;
            return $this->__die(false);
        }

        $this->webServiceObject = $this->busSoapAccess->getWebServiceByClassAndMethod($this->className, $this->methodRequest);

        if(!$this->webServiceObject)
        {
            $this->errorCode = self::ERROR_SERVICE_UNAVALIABLE;
            return false;
        }

        switch ($returnType)
        {
            case "xml"      :
                $phpXML = new GXML($this->webServiceObject, "phpToXml");
                return $phpXML->getResult();
            case "object"   :
            default         : return $this->webServiceObject;
        }
    }


    /**
     * Retorna o web service cadastrado na base
     *
     * @return object
     */
    protected function __getClient()
    {
        $this->clientObject = null;
        if(!strlen($this->clientId))
        {
            $this->errorCode = ERROR_UNDEFINED_PASS_ID;
            return $this->__die(false);
        }

        $this->clientObject = $this->busSoapAccess->getClient($this->clientId);
        
        return $this->clientObject;
    }


    /**
     * retorna o operador de web services
     *
     * @return string
     */
    protected function getOperator()
    {
        $operator = !is_null($this->clientId) ? self::WEB_SERVICES_OPERATOR . "_Id[{$this->clientId}]" : self::WEB_SERVICES_OPERATOR;
        return $operator;
    }


    /**
     * função die
     *
     * @param booelan $die
     * @return die or false
     */
    private function __die($die = true)
    {
        if($die)
        {
            die();
        }

        return false;
    }


    /**
     * Retorna o codigo do erro
     *
     * @return integer
     */
    protected function __getError()
    {
        return $this->errorCode;
    }


    /**
     * Retorna a descriçao do erro
     *
     * @return string
     */
    protected function __getErrorStr()
    {
        switch ($this->__getError())
        {
            case self::ERROR_UNDEFINED_METHOD:      return "[INTERNAL ERROR] 1 - methodRequest is not defined";
            case self::ERROR_UNDEFINED_PASS_ID:     return "[GLOBAL ERROR] 2 - clientId or clientPassword is not defined";
            case self::ERROR_AUTHENTICATION_FAIL:   return "[GLOBAL ERROR] 3 - Authetication fail";
            case self::ERROR_DENIED_IP:             return "[GLOBAL ERROR] 4 - IP not allowed. Your IP Address: $this->clientIP;";
            case self::ERROR_SERVICE_UNAVALIABLE:   return "[GLOBAL ERROR] 5 - Service not available.";
            case self::ERROR_ACCESS_BLOCKED:        return "[GLOBAL ERROR] 6 - You do not have access to this service.";
            default: return "[GLOBAL ERROR] Mensage not defined. Error Code: {$this->errorCode}";
        }
    }



    /**
     * trabalha o conteudo de acordo com o tipo de retorno desejado.
     *
     * @param object array $content
     * @param string $type
     * @return object | string xml
     */
    protected function returnType($content, $type = "xml", $encoding = "UTF-8")
    {
        switch(strtoupper($type))
        {
            case "PHP_OBJECT" :
                return $content;

            case "XML"  :
            default     :
                $xml = new GXML($content, "phpToXml", $encoding);
                return $xml->getResult();
        }
    }

    /**
     * Incia um sessão para percistencia do objeto
     *
     * Esta função não sera utilizada no momento, pois o miolo esta comprometendo a persistencia do soap do php
     *
     *
    private function startSession()
    {
        session_name($this->className);
        $this->sessionId = $_COOKIE[$this->className];

        if ($this->sessionId)
        {
            session_id($this->sessionId);
        }

        $this->sessionId = session_id();
        session_start();
    }
    */

}// final da classe

?>