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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 06/ago/2008
 *
 **/
$MIOLO->uses( "/classes//phpmailer/class.phpmailer.php", 'gnuteca3');
$MIOLO->uses( "/classes/BString.class.php", 'base');

class GMail extends PHPMailer
{
    private $log = false;
    private $logFile;
    public $busLibraryUnitConfig;

    function __construct()
    {
        parent::__construct();
        $this->SetLanguage('br');
        $this->setCharset('UTF-8');
        $this->IsSMTP();
        $this->MIOLO = MIOLO::getInstance();
        $this->busLibraryUnitConfig = $this->MIOLO->getBusiness('gnuteca3', 'BusLibraryUnitConfig');
        //Obtem as preferencias 
        $preferences = $this->busLibraryUnitConfig->getValueLibraryUnitConfig(MIOLO::_REQUEST( 'libraryUnitId' ), array('EMAIL_SMTP','EMAIL_PORT','EMAIL_FROM','EMAIL_FROM_NAME','EMAIL_AUTHENTICATE','EMAIL_USER','EMAIL_PASSWORD','EMAIL_CONTENT_TYPE'));

        //valores padrão das preferências
        $this->setHost($preferences['EMAIL_SMTP']);
        $this->setPort($preferences['EMAIL_PORT']);
        $this->setFrom($preferences['EMAIL_FROM']);
        $this->setFromName($preferences['EMAIL_FROM_NAME']);
        $this->setAuthenticate($preferences['EMAIL_AUTHENTICATE']);
        $this->setUser($preferences['EMAIL_USER']);
        $this->setPassword($preferences['EMAIL_PASSWORD']);
        $this->setContentType($preferences['EMAIL_CONTENT_TYPE']);
        //aumenta o tempo limite de conexão com serviço de email
        $this->Timeout = 60; 
    }

    /**
     * Define o charset para o email.
     *
     * @param string $charset
     */
    public function setCharset( $charset)
    {
        $this->CharSet = new BString($charset);
    }

    /**
     * Retorna o charset do email
     * 
     * @return string
     */
    public function getCharset( )
    {
        return $this->CharSet;
    }

    /**
     * Retorna o arquivo de log
     *
     */
    private function getLogFilePath()
    {
        if ( strlen( $this->logFile ) )
        {
            return;
        }

        //verifica configuração do log
        if ( MUTIL::getBooleanValue( MAIL_LOG_GENERATE ) )
        {
            return;
        }

        $MIOLO          = MIOLO::getInstance();
        $this->logFile  = $MIOLO->getConf('home.logs')."/";
        $this->logFile .= defined('MAIL_LOG_FILE_NAME') ? MAIL_LOG_FILE_NAME : "gnuteca3-mail.log";
    }

    /**
     * Este método incrementa o array de destinatários
     *
     * @param (String) $address
     * @return void
     */
    public function addAddress($address)
    {
        //caso não encontre arroba não adiciona,
        //FIXME verificação fraca
        if ( !ereg( "@", $address ) )
        {
            return;
        }

        //caso seja separado por vírgulas
        if ( ereg(",", $address ) )
        {
            $a = explode(",", $address);
            
            foreach($a as $v)
            {
                parent::addAddress($v);
            }
        }
        else
        {
            parent::addAddress( trim( $address ) );
        }
    }

    /**
     * Define um destinatário
     *
     * @param $address (String)
     */
    public function setAddress($address)
    {
        $this->ClearAllRecipients(); //limpa todos recipientes
        $this->addAddress($address);
    }

    /**
     * Adiciona um anexo
     *
     * @param String - full file path
     */
    public function addAttachment($pathFile)
    {
        $pathFile = trim($pathFile);

        //suporta vários arquivos separados por vírgula
        if ( ereg( ",", $pathFile ) )
        {
            $a = explode(",", $pathFile);

            foreach($a as $v)
            {
                parent::addAttachment($v);
            }
        }
        else
        {
            if ( file_exists( $pathFile ) )
            {
                parent::addAttachment( $pathFile );
            }
        }
    }

    /**
     * seta o conteudo que sera encaminhado pelo email
     *
     * @param String
     */
    public function setContent($content)
    {
        $this->Body = new BString($content);
    }

    /**
     * seta o assunto do email
     *
     * @param String
     */
    public function setSubject($subject)
    {
        //concatena o prefixo
        $this->Subject = new BString(EMAIL_SUBJECT_PREFIX . ' ' . $subject);
        //tira espaços extras
        $this->Subject = str_replace('  ', ' ', $this->Subject);
    }

    /**
     * Seta o Usuario
     *
     * O usuário é necessario caso o metodo de envio seja atutenticado
     *
     * @param String
     */
    public function setUser($user)
    {
        $user = new BString($user);
        $user = str_replace(array("\n", "\t", "\r"), "", $user);
        $user = trim($user);
        $this->Username = $user;
    }

    /**
     * Seta o password
     *
     * este parametro é necessario caso o metodo de envio seja autenticado
     *
     * @param String
     */
    public function setPassword($password)
    {
        $this->Password = new BString($password);
    }

    /**
     * Seta o servidor
     *
     * @param String
     */
    public function setHost($host)
    {
        $this->Host = new BString($host);
    }

    /**
     * Seta a porta de conexao
     *
     * @param Int
     */
    public function setPort($port)
    {
        $port = new BString($port);
        //FIXME Porque o _toString não ta convertendo neste caso ?
        $this->Port = $port->getString();
    }

    /**
     * Seta o remetente
     *
     * @param String
     */
    public function setFrom($from)
    {
        $from = new BString($from);
        $from = str_replace(array("\n", "\t", "\r"), "", $from);
        $from = trim($from);
        $this->From = $from;
    }

    /**
     * Seta o nome do rementente
     *
     * @param String
     */
    public function setFromName($fromName)
    {
        $this->FromName = new BString($fromName);
    }

    /**
     *
     * Seta se o metodo de conexao com o server é autenticado ou não
     *
     * @param (Boolean)
     */
    public function setAuthenticate($authenticate)
    {
        if(!is_bool($authenticate))
        {
            $authenticate = (array_search($authenticate, array('1', 't', 'true','yes')) !== false);
        }

        $this->SMTPAuth = $authenticate;
    }

    /**
     * Seta o tipo do conteudo que será enviado (HTML|TEXT)
     *
     * @param Boolean
     */
    public function setIsHtml($isHtml = true)
    {
        $this->IsHTML($isHtml);
    }

    /**
     * Seta a liguagem do conteudo
     *
     * @param varchar $type
     */
    public function setContentType($type = 'html')
    {
        switch ($type)
        {
            default:
                $this->setIsHtml(true);
        }
    }

    public function getContentType()
    {
        return $this->ContentType;
    }

    /**
     *
     * Retorna os destinatários
     *
     * @return (Array)
     */
    public function getAddress()
    {
        return array_keys( $this->all_recipients );
    }

    /**
     * Retorna os anexos
     *
     * @return (Array)
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * retorna o remetente
     *
     * @return String
     */
    public function getFrom()
    {
        return $this->From;
    }

    /**
     * retorna o nome do remetente
     *
     * @return String
     */
    public function getFromName()
    {
        return $this->FromName;
    }

    /**
     * retorna o Assunto do email
     *
     * @return String
     */
    public function getSubject()
    {
        return $this->Subject;
    }

    /**
     * retorna o Conteudo do email
     *
     * @return String
     */
    public function getContent()
    {
        if ($this->getIsHTML())
        {
            $this->Body = new BString(str_replace("\n", "<br>", $this->Body));
        }
        
        return $this->Body;
    }

    /**
     * retorna o usuario
     *
     * @return String
     */
    public function getUser()
    {
        return $this->Username;
    }

    /**
     * retorna o password
     *
     * @return String
     */
    public function getPassword()
    {
        return $this->Password;
    }

    /**
     * retorna o host
     *
     * @return String
     */
    public function getHost()
    {
        return $this->Host;
    }

    /**
     * retorna a porta
     *
     * @return Integer
     */
    public function getPort()
    {
        return $this->Port;
    }

    /**
     * retorna se o metodo de conexao é autenticado (authenticate true|false)
     *
     * @return (Boolean)
     */
    public function getAuthenticate()
    {
        return $this->SMTPAuth;
    }

    /**
     * retorna se o tipo de texto é html (isHTML true|false)
     *
     * @return (Boolean)
     */

    public function getIsHtml()
    {
        return $this->ContentType == 'text/html';
    }

    /**
     * envia o email
     *
     * @return Boolean
     */
    public function send()
    {
        $preferences = $this->busLibraryUnitConfig->getValueLibraryUnitConfig(MIOLO::_REQUEST( 'libraryUnitId' ),array('EMAIL_TESTING','EMAIL_TEST_RECEIVE'));
        
        // VERIFICA SE È TESTES E SUBTITUI OS ENDEREÇOS PELO EMAIL DE TESTE
        if ( MUtil::getBooleanValue( $preferences['EMAIL_TESTING'] ) && strlen( $preferences['EMAIL_TEST_RECEIVE'] ) )
        {
            $this->setAddress( $preferences['EMAIL_TEST_RECEIVE'] );
        }

        $send = parent::send();
        $this->recordLog( $send );
        return $send;
    }

    /**
     * Escreve o arquivo de log
     *
     * @param resultado do envio (Boolean) $result
     */
    private function recordLog($result)
    {
        $this->getLogFilePath();

        if(!strlen($this->logFile))
        {
            return;
        }

        $recordSeparator    = "+----------------------------------------------------------------+";

        $content = new BString("\n{$recordSeparator}\n");
        $content.= ($result) ? "E-mail foi enviado com sucesso!\n" : "Não foi possível enviar o e-mail.\n";
        $content.= "Destino: '". (!is_null($this->getAddress()) ? implode(",\n\t", $this->getAddress()) : "null") ."'\n";
        $content.= "Data/Hora: '". date("d/m/Y H:i:s") ."'\n";
        $content.= "ContentType: '". $this->ContentType ."'\n";
        $content.= "Authenticate: '". $this->getAuthenticate() ."'\n";
        $content.= "Host: '". $this->getHost() .":". $this->getPort() ."'\n";
        $content.= "User: '". $this->getUser() ."'\n";
        $content.= "Password: '". $this->getPassword() ."'\n";
        $content.= "From: '". $this->getFrom() ."'\n";
        $content.= "From Name: '". $this->getFromName() ."'\n";
        $content.= "Subject: '". $this->getSubject() ."'\n";
        $content.= "Content: '". $this->getContent() ."'\n";
        
        if(!$result)
        {
            $content.=  "\n". $this->ErrorInfo ."\n";
        }

        file_put_contents($this->logFile, $content, FILE_APPEND);
    }
    
    
    /**
     * Obtém os parametros para envio de e-mail da unidade logada
     * @return \stdClass
     */
    public function getParametersForEmail()
    {
        $parametros = $this->busLibraryUnitConfig->listLibraryUnitConfig( MIOLO::_REQUEST( 'libraryUnitId' ) );

        $std = new stdClass();
        
        foreach ( $parametros as $chave => $valor )
        {
            $std->$valor[1] = $valor[2];
        }
        
        return $std;
    }
}

?>
