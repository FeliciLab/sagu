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


class GPrinterServer
{

    /**
     * Attributes
     */
    private $serverAddress  = "::";
    private $serverPort     = "1515";
    private $socket         = null;
    private $msgsock        = null;
    private $errorCode      = 0;
    private $lineEnding     = "\n\r";
    private $finalText      = null;

    private $terminalDisplayLine = "+=============================================================================+";
    private $printer        = null;
    private $logFile        = null;
    private $saveMicrotime  = null;

    /**
     * Constructor method
     */
    function __construct()
    {
        if (!extension_loaded('sockets'))
        {
            die('skip sockets extension not available.');
        }

        @date_default_timezone_set('America/Sao_Paulo');
        @error_reporting(E_ALL);
        @set_time_limit(0);
        @ob_implicit_flush();

        require(str_replace("classes", "etc", str_replace("\\", "/", dirname(__FILE__))) . "/GPrinterServer.conf.php");
        require(str_replace("\\", "/", dirname(__FILE__)) . "/Printer.class.php");
        $this->logFile = str_replace("classes", "logs", str_replace("\\", "/", dirname(__FILE__))) . "/". get_class($this) . "_" . date("Y-m-d_H-i-s") .".log";

        $this->serverAddress    = SERVER_ADDRESS_RULES;
        $this->serverPort       = SERVER_PORT;
    }


    /**
     * Esta função cria o socket resource
     *
     * @return boolean
     */
    private function createSocket()
    {
        if (($this->socket = socket_create(SERVER_AF_INET, SOCK_STREAM, SOL_TCP)) === false)
        {
            $this->errorCode = 1;
            return false;
        }

        return true;
    }


    /**
     * Seta detalhes do servidor
     *
     * @return boolean
     */
    private function bindSocket()
    {
        if (socket_bind($this->socket, $this->serverAddress, $this->serverPort) === false)
        {
            $this->errorCode = 2;
            return false;
        }

        return true;
    }


    /**
     * Enter description here...
     *
     * @return booelan
     */
    private function listenSocket()
    {
        if (($list = socket_listen($this->socket, 5)) === false)
        {
            $this->errorCode = 3;
            return false;
        }

        return true;
    }



    /**
     * Aceita uma conexao do socket
     *
     *  @return boolean
     */
    private function acceptSocket()
    {
        $this->finalText = "";

        if (($this->msgsock = socket_accept($this->socket)) === false)
        {
            $this->errorCode = 4;
            return false;
        }

        $this->displayMsg("", null, "*");
        $this->displayMsg("--LINE--");
        $this->displayMsg("Iniciando comunicação com cliente.");
        $this->displayMsg("Data/Hora: ". date("d/m/Y H:i:s"));

        return true;
    }


    /**
     * Encerra uma conexao
     *
     * @param socket connection $resource
     */
    private function finalSocket(&$resource)
    {
        if(is_null($resource))
        {
            return;
        }

        $this->displayMsg("Finalizando comunicação com cliente.");
        $this->displayMsg("Data/Hora: ". date("d/m/Y H:i:s"));
        $this->displayMsg("--LINE--");

        socket_close($resource);
        $resource = null;
    }



    /**
     * Esta função responde para o cliente confirmando os dados recebidos.
     *
     */
    private function confirmComunication()
    {
        $this->displayMsg("Conteudo envidado pelo cliente:");
        $this->displayMsg("");

        $lines = explode($this->lineEnding, $this->finalText);
        foreach ($lines as $c)
        {
            $this->displayMsg($c);
        }

        $md5 = str_replace(array("\n", "\r", "\t"), "", $this->finalText);
        $talkback = str_replace('MD5CODE', md5($md5), PRINTER_SERVER_SIGNAL_DATA_RECEIVE_MD5);

        $this->displayMsg("--LINE--");
        $this->displayMsg("Enviando dados para validar comunicação:");
        $this->displayMsg($talkback);
        $this->displayMsg("--LINE--");

        // ENVIA PARA O CLIENTE UM MD5 DO CONTEUDO RECEBIDO
        $this->responseToClient($talkback);
    }



    /**
     * Responde para o cliente
     *
     * @param string $talkback
     * @return int
     */
    private function responseToClient($talkback)
    {
        return socket_write($this->msgsock, $talkback, strlen($talkback));
    }


    /**
     * Efetua a leitura o socket.
     *
     * @return boolean
     */
    private function readSocket()
    {
        $this->displayMsg("--LINE--");
        $this->displayMsg("Aguardando recebimento das informações.");
        $this->displayMsg("--LINE--");

        $countReadEmpty  = 0;

        // WHILE INFINITO DE LEITURA
        while(true)
        {
            usleep(1);

            $buf = @socket_read($this->msgsock, 2048);

            if ($buf === false)
            {
                return;
            }

            $buf = trim($buf);
            if (!strlen($buf))
            {
                if($countReadEmpty++ >= 30)
                {
                    return;
                }

                continue;
            }

            if(!$this->interpretContent($buf))
            {
                return;
            }
        }

        return;
    }


    public function interpretContent($buffer)
    {
        $buffer = explode($this->lineEnding, $buffer);

        foreach($buffer as $buf)
        {
            // RECEBEU O SINAL DE FINAL DE ENVIO DE DADOS
            if ($buf == PRINTER_SERVER_SIGNAL_FINISH)
            {
                // CONFIRMA RECEBIMENTO DAS INFORMAÇÔES
                $this->confirmComunication();
                continue;
            }

            // RECEBEU O SINAL PARA IMPRESSAO
            if ($buf == PRINTER_SERVER_SIGNAL_PRINT)
            {
                $this->print_();
                continue;
            }

            // RECEBEU O SINAL DE SAIR
            if ($buf == PRINTER_SERVER_SIGNAL_EXIT)
            {
                $this->responseToClient(PRINTER_SERVER_SIGNAL_EXIT_OK);
                return false;
            }

            $codes = array(PRINTER_SERVER_SIGNAL_FINISH, PRINTER_SERVER_SIGNAL_PRINT, PRINTER_SERVER_SIGNAL_EXIT);
            if(array_search(str_replace(array("\n", "\r", "\t"), "", $buf), $codes) === false)
            {
                // INCREMENTA A VARIAVEL COM AS INFORMAÇÔES A SEREM IMPRIMIDAS
                $this->finalText.= "{$buf}{$this->lineEnding}";
            }
        }

        return true;
    }



    /**
     * Inicia o serviço so server
     *
     * @return boolean
     */
    public function startService()
    {
        $this->serverStartMsg();

        if(!$this->createSocket())
        {
            return false;
        }
        if(!$this->bindSocket())
        {
            return false;
        }
        if(!$this->listenSocket())
        {
            return false;
        }

        // WHILE INFINITO PARA O SERVIÇO FICAR RODANDO
        while(true)
        {
            usleep(10);

            // ACEITA CONEXAO
            if(!$this->acceptSocket())
            {
                continue;
            }

            $this->displayMsg("--LINE--");
            $this->displayMsg("Conexão com o cliente foi aceita.");

            $this->readSocket();
            $this->finalSocket($this->msgsock);
        }

        $this->finalSocket($this->msgsock);
        $this->finalSocket($this->socket);

        return true;
    }


    /**
     * chama a classe de impressão e manda ela imprimir
     *
     * @return unknown
     */
    private function print_()
    {
        $this->printer = new Printer();
        $this->printer->setPrintContent($this->finalText);

        if($this->printer->__print())
        {
            $this->displayMsg("Print");
            $this->responseToClient(PRINTER_SERVER_SIGNAL_PRINTER_SUCCESSFUL);
            return true;
        }

        $this->responseToClient(PRINTER_SERVER_SIGNAL_PRINTER_FAIL);
        return false;
    }


    /**
     * Echo da tela inicial do sistema
     *
     */
    private function serverStartMsg()
    {
        /**
         * Constantes de definição de autoria, versão e criação.
         */
        @define("PRINTER_SERVER_VERSION",   "1.3");
        @define("PRINTER_SERVER_AUTOR",     "Luiz Gilberto Gregory Filho [luiz@solis.coop.br]");
        @define("PRINTER_SERVER_CREATE",    "14/07/2009");

        $this->displayMsg("--LN--");
        $this->displayMsg("--LINE--");
        $this->displayMsg("Gnuteca Printer Server", "CENTER");
        $this->displayMsg("--LINE--");
        $this->displayMsg("");
        $this->displayMsg("Version: ".   PRINTER_SERVER_VERSION);
        $this->displayMsg("Autor: ".     PRINTER_SERVER_AUTOR);
        $this->displayMsg("Create: ".    PRINTER_SERVER_CREATE);
        $this->displayMsg("");
        $this->displayMsg("--LINE--");
        $this->displayMsg("Servidor Inicializado.");
        $this->displayMsg("Data/Hora: ". date("d/m/Y H:i:s"));
        $this->displayMsg("--LINE--");
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

        echo $msg;


        // registra no arquivo de log
        if(!$this->logFile)
        {
            echo "return 1";
            return;
        }

        $h = @fopen($this->logFile , "a+");
        if(!$h)
        {
            echo "$this->logFile, return 2";
            return;
        }

        fwrite($h, $msg);
        fclose($h);
    }


    /**
     * Retorna o codigo do erro.
     *
     * @return integer
     */

    public function getError()
    {
        echo socket_strerror(socket_last_error());
        return $this->errorCode;
    }

}


?>
