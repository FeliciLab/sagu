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
 * Class Gnuteca Socket Server
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 14/07/2009
 *
 **/

@define("PRINTER_CLIENT_VERSION",   "1.2");
@define("PRINTER_CLIENT_AUTOR",     "Luiz Gilberto Gregory Filho [luiz@solis.coop.br]");
@define("PRINTER_CLIENT_CREATE",    "14/07/2009");

@define("PRINTER_SERVER_SIGNAL_FINISH",             "--finish--");
@define("PRINTER_SERVER_SIGNAL_PRINT",              "--print--");
@define("PRINTER_SERVER_SIGNAL_EXIT",               "--exit--");

@define("PRINTER_SERVER_SIGNAL_PRINTER_SUCCESSFUL", "--printerSuccessful--");
@define("PRINTER_SERVER_SIGNAL_PRINTER_FAIL",       "--printerFail--");
@define("PRINTER_SERVER_SIGNAL_EXIT_OK",            "--closingConnection--");
@define("PRINTER_SERVER_SIGNAL_DATA_RECEIVE_MD5",   "--MD5Response--[MD5CODE]--");


class GPrinterClient
{

    private $serverAddress  = "127.0.0.1";
    private $serverPort     = "1515";
    private $socket         = null;
    private $connect        = null;
    private $errorCode      = 0;
    private $lineEnding     = "\n\r";
    private $md5Mensage     = null;

    private $watingCount    = 0;

    private $terminalDisplayLine = "+=============================================================================+";


    /**
     * Constructor method
     *
     */
    function __construct($serverAddress = null, $serverPort = null)
    {
        if (!extension_loaded('sockets'))
        {
            die('skip sockets extension not available.');
        }

        //@date_default_timezone_set('America/Sao_Paulo');
        //@error_reporting(E_ALL);

        if(is_null($serverAddress))
        {
            if (PRINT_SERVER_HOST != 'PRINT_SERVER_HOST' && strlen(PRINT_SERVER_HOST))
            {
                $this->serverAddress = PRINT_SERVER_HOST;
            }
            else //Obtem o ip que esta sendo feita a requisicao (cliente)
            {
                $this->serverAddress = GServer::getRemoteAddress();
            }
        }
        else
        {
            $this->serverAddress = $serverAddress;
        }
        
        if(is_null($serverPort))
        {
            if (PRINT_SERVER_PORT != 'PRINT_SERVER_PORT')
            {
                $this->serverPort = PRINT_SERVER_PORT;
            }
        }
        else
        {
            $this->serverPort = $serverPort;
        }
        
        $this->starting();
    }



    /**
     * Esta funï¿½ï¿½o cria o socket resource
     *
     * @return boolean
     */
    private function createSocket()
    {
        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false)
        {
            $this->errorCode = 1;
            return false;
        }

        return true;
    }


    /**
     * Esta funï¿½ï¿½o efetua a conexao com o server
     *
     * @return boolean
     */
    private function connectSocket()
    {
        $this->connect = socket_connect($this->socket, $this->serverAddress, $this->serverPort);

        if (!$this->connect)
        {
            $this->errorCode = 2;
            return false;
        }

        return true;
    }


    /**
     * Inicia a conexao com o servidor
     *
     * @return boolean
     */
    public function starting()
    {
        if(!$this->createSocket())
        {
            return false;
        }
        if(!$this->connectSocket())
        {
            return false;
        }

        return true;
    }


    /**
     * Envia mensagem para o server
     *
     * @param string $msg
     */
    public function send($msg)
    {
        $msg.= $this->lineEnding;

        $codes = array(PRINTER_SERVER_SIGNAL_FINISH, PRINTER_SERVER_SIGNAL_PRINT, PRINTER_SERVER_SIGNAL_EXIT);
        if(array_search(str_replace(array("\n", "\r", "\t"), "", $msg), $codes) === false)
        {
            $this->md5Mensage.= $msg;
        }
        
        //o socket não reconhece o envio de caracteres UTF-8, gerando o trucamento de Strings
        $msg = utf8_decode($msg);
        socket_write($this->socket, $msg, mb_strlen($msg, 'ISO-8859-1'));

        $this->watingCount = 0;
    }



    /**
     * Aguarda uma resposta do servidor.
     *
     */
    public function waitingResponse($validateCode)
    {
        while($out = @socket_read($this->socket, 2048))
        {
            //echo "\n\n($out == $validateCode)\n\n";
            return ($out == $validateCode);
        }

        if($this->watingCount > 20)
        {
            $this->watingCount = 0;
            return false;
        }

        $this->watingCount++;
    }



    /**
     * retorna o codigo validador
     *
     * @return string
     */
    public function getTextConfirmCode()
    {
        $md5 = str_replace(array("\n", "\r", "\t"), "", $this->md5Mensage);
        return str_replace('MD5CODE', md5($md5), PRINTER_SERVER_SIGNAL_DATA_RECEIVE_MD5);
    }



    /**
     * Desconecta com servidor
     *
     */
    public function closeConnection()
    {
        socket_close($this->socket);
    }



    /**
     * Exibe uma mensagem na tela;
     *
     * @param unknown_type $msg
     */
    public function displayMsg($msg, $align = "LEFT", $X = " ")
    {
        if(is_null($align))
        {
            $align = "LEFT";
        }

        if($msg == "--LINE--")
        {
            $msg = "{$this->terminalDisplayLine}{$this->lineEnding}";
        }
        elseif($msg == "--LN--")
        {
            $msg = $this->lineEnding;
        }
        elseif($align == "LEFT")
        {
            $msg = GUtil::strPad("| {$msg}", strlen($this->terminalDisplayLine)-1, $X, STR_PAD_RIGHT) ."|{$this->lineEnding}";
        }
        elseif($align == "CENTER")
        {
            $msgLength  = strlen($msg);
            $msg        = GUtil::strPad($msg, ( ceil(strlen($this->terminalDisplayLine)/2) + ceil($msgLength/2.5)), $X, STR_PAD_LEFT);
            $msg        = "|". GUtil::strPad($msg,  strlen($this->terminalDisplayLine)-2, $X, STR_PAD_RIGHT) ."|{$this->lineEnding}";
        }

        //echo $msg;
    }


    /**
     * Verifica se o servidor de impressão esta rodando corretamente
     *
     * @return <boolean>
     */
    public function isConnect()
    {
        return $this->connect && is_resource($this->socket);
    }

    /**
     * Retorna o codigo do erro.
     *
     * @return integer
     */
    public function getError()
    {
        return $this->errorCode;
    }

    public function getSocketLastError()
    {
        return socket_strerror( socket_last_error() );
    }

    /**
     * Retorna o endereço do servidor de impressão
     *
     * @return <string> server address
     */
    public function getServerAddress()
    {
        return $this->serverAddress;
    }
}
?>