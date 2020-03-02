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
 * Class Dependency Check
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/10/2010
 *
 * */
$MIOLO->uses('classes/BDependencyCheck.class.php', 'base');
$MIOLO->uses('classes/BString.class.php', 'base');
$MIOLO->uses('classes/bBaseDeDados.class.php', 'base');
$MIOLO->uses('classes/bCatalogo.class.php', 'base');

class GDependencyCheck extends BDependencyCheck
{

    public function listDependency()
    {
        $module = MIOLO::getCurrentModule();

        $list = parent::listDependency();
        $list[] = array( 'yazInstalled', _M('Yaz instalada (usada pela Z3950)', $module) );
        $list[] = array( 'zebraServer', _M('Servidor zebra (Z3950)', $module) );
        $list[] = array( 'fileTmpPermission', _M('Permissão padrão dos arquivos temporários do miolo', $module) );
        $list[] = array( 'linkedFiles', _M('Arquivos do Gnuteca que precisam estar no diretório miolo/html/', $module) );
        $list[] = array( 'printerSocket', _M('Impressora usando sistema de socket', $module) );
        $list[] = array( 'gCron', _M('GCron', $module) );
        $list[] = array( 'backgroundTask', _M('Execução de tarefas em segundo plano', $module) );
        $list[] = array( 'dateStyle', _M('Estilo de data do banco de dados', $module) );
        $list[] = array( 'mbstring', _M('Suporte a strings multi byte e UTF-8', $module) );
        $list[] = array( 'googleBook', _M('Suporte a integração com Google Livros', $module) );
        $list[] = array( 'webServices', _M('Serviços web do gnuteca', $module) );
        $list[] = array( 'operatorsAuthenticate', _M('Autenticação de operadores', $module) );
        $list[] = array( 'uploadMaxFilesize', _M('Capacidade de upload de arquivos', $module) );
        $list[] = array( 'shortOpenTags', _M('Diretiva short open tag ativada no php_cli', $module) );
        $list[] = array( 'standardConformingStrings', _M('Diretiva standard_conforming_strings do PostgreSQL', $module) );

        return $list;
    }

    /**
     * Verifica se o estilo da data do banco de dados está definido corretamente
     *
     * @return boolean
     */
    public function dateStyle()
    {
        $bus = new GBusiness();
        $result = $bus->executeSelect("SELECT setting from pg_settings where name = 'DateStyle';");
        $confirm = 'SQL, DMY'; //string que verifica o datestyle correto

        $ok = stripos(' ' . $result[0][0], $confirm) > 0;

        if ( !$ok )
        {
            $this->setMessage("Execute no banco de dados: ALTER DATABASE :DBNAME SET DateStyle TO 'SQL, DMY';");
        }

        return $ok;
    }   

    public function yazInstalled()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->getClass('gnuteca3', 'GZ3950');

        $z3950 = new GZ3950(' ');

        return $z3950->isInstalled();
    }

    public function zebraServer()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->getClass('gnuteca3', 'GZ3950');
        $MIOLO->getClass('gnuteca3', 'GZebra');

        if ( !defined('Z3950_SERVER_URL') || !Z3950_SERVER_URL )
        {
            $this->setMessage(_M('Integração com servidor desabilitada.', 'gnuteca3'));
            return true;
        }

        $z3950 = new GZ3950(Z3950_SERVER_URL);
        $zebra = new GZebra();

        if ( $z3950->isServerOnline() )
        {
            $lastDate = $zebra->getLastLogDate();
            $this->setMessage(_M("Última operação : @1", 'gnuteca3', $lastDate->getDate(GDate::MASK_TIMESTAMP_USER)));
            return true;
        }

        $this->setMessage(_M('Servidor zebra não está rodando, execute o comando "@1" como root.', 'gnuteca3', $zebra->getServerStartCommand()));
        return false;
    }

    public function filePermission()
    {
        $busFile = new BusinessGnuteca3BusFile();
        $this->setMessage(BusinessGnuteca3BusFile::getAbsoluteServerPath(true));
        return $busFile->isWritable();
    }

    public function fileTmpPermission()
    {
        $MIOLO = MIOLO::getInstance();
        $tmpPath = $MIOLO->getConf('home.html') . '/files/tmp';
        $this->setMessage($tmpPath);

        return is_writable($tmpPath);
    }

    public function printerSocket()
    {
        $MIOLO = MIOLO::getInstance();

        if ( PRINT_MODE == 1 )
        {
            $MIOLO->getClass('gnuteca3', 'GPrinterClient');
            $socket = new GPrinterClient();
            $ok = $socket->isConnect();

            if ( $ok )
            {
                $this->setMessage(_M('O servidor de impressão está rodando', 'gnuteca3'));
            }
            else
            {
                $aviso = _M('Não existe servidor de impressão rodando nesta máquina (@1). - ', 'gnuteca3', $socket->getServerAddress());
                $this->setMessage($aviso . new GString($socket->getSocketLastError()));
            }
        }
        else
        {
            $ok = true;
            $this->setMessage(_M('Mode de impressão definido como : Navegador.', 'gnuteca3'));
        }

        return $ok;
    }

    /**
     * Checa se a GCron está sendo executada
     */
    public function gCron()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $filename = BusinessGnuteca3BusFile::getAbsoluteServerPath(true) . '/gcron.log';
        $content = file_get_contents($filename);

        $ok = false;
        if ( strlen($content) > 0 )
        {
            $dateNow = GDate::now();
            $dateRunning = new GDate($content);
            $diff = $dateNow->diffDates($dateRunning);

            //se faz mais de 60 minutos desde a última execução, o gcron está parado
            if ( $diff->minutes <= 60 )
            {
                $ok = true;
            }
        }

        if ( $ok )
        {
            $this->setMessage(_M('GCron está sendo executado.', 'gnuteca3'));
            return true;
        }
        else
        {
            $this->setMessage(_M('GCron não está sendo executado.', 'gnuteca3'));
            return false;
        }
    }

    public function emailConfigured()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->getClass($module, 'GMail');

        $mail = new GMail();
        $mail->setAddress(EMAIL_ADMIN);
        $mail->setSubject(_M('Teste de dependências', $module));
        $mail->setContent(_M('Se você esta lendo esta mensagem é porque seu sistema de emails esta funcionando corretamente.', $module));

        $send = $mail->send();

        if ( $send )
        {
            $this->setMessage(_M('Email enviado para ', $module) . EMAIL_ADMIN . ' . ' . _M('Por favor, confira o recebimento.'));
        }
        else
        {
            $this->setMessage($mail->ErrorInfo);
        }

        return $send;
    }

    public function linkedFiles()
    {
        $MIOLO = MIOLO::getInstance();
        $htmlPath = $MIOLO->getConf('home.html');

        $file = file_exists($htmlPath . '/file.php');
        $google = file_exists($htmlPath . '/googleViewer.php');

        if ( !$file || !$google )
        {
            $aviso = _M('Arquivos que precisam estar em ', 'gnuteca3');
            $this->setMessage($aviso . $htmlPath . ' : ' . 'file.php e googleViewer.php');
            return false;
        }

        return true;
    }

    public function backgroundTask()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('gnuteca3', 'backgroundTasks/GBackgroundTask');
        $isPossible = GBackgroundTask::isPossible();

        if ( !$isPossible )
        {
            $this->setMessage(_M('Não é possível executar tarefas em segundo plano, verifique: <br/>1. Se a função exec existe e pode ser chamada; <br/>2. PHP esta instalado. <br/>3. Modo seguro esta desabilitado. <br/>4. Preferência EXECUTE_BACKGROUND_TASK esta habilitada. ', 'gnuteca3'));
        }

        return $isPossible;
    }

    public function mbstring()
    {
        $MIOLO = MIOLO::getInstance();
        $charset = strtoupper($MIOLO->getConf('options.charset'));

        if ( $charset != 'UTF-8' )
        {
            $this->setMessage(_M('Códificação no miolo.conf está errada, em options.charset coloque UTF-8.', 'gnuteca3'));
            return false;
        }

        //Verifica se tem suporte para trabalhar com strings em UTF8
        $overload = ini_get('mbstring.func_overload');
        $encoding = ini_get('mbstring.internal_encoding');

        $error = '';
        if ( $overload != 7 )
        {
            $error[] = "'php_admin_value mbstring.func_overload  7'";
        }

        if ( $encoding != 'UTF-8' )
        {
            $error[] = "'php_value mbstring.internal_encoding UTF-8'";
        }

        if ( $error )
        {
            $this->setMessage(_M("Ative a(s) diretiva(s) " . implode(_M(" e ", 'gnuteca3'), $error), 'gnuteca3') . _M(" no virtualHost.", 'gnuteca3'));
            return false;
        }

        return true;
    }

    public function googleBook()
    {
        if ( !MUtil::getBooleanValue(GB_INTEGRATION) )
        {
            $this->setMessage(_M("A integração com o Google Livros está desabilitada, utilize a preferência GB_INTEGRATION para ativá-la"));
            return true;
        }

        $allow = ini_get('allow_url_fopen');

        if ( !$allow )
        {
            $this->setMessage(_M("É necessário ativar a propriedade allow_url_fopen no php.ini"));
            return false;
        }

        $urlTest = "http://books.google.com/books/feeds/volumes?hl=pt-BR&q=machado";

        try
        {
            $xmlContent = file_get_contents($urlTest);

            if ( !$xmlContent )
            {
                $this->setMessage(_M("Impossível conectar ao servidor do google, verifique as regras de firewall."));
                return false;
            }

            $xml = new MSimpleXml($xmlContent);
        }
        catch ( Exception $e )
        {
            $this->setMessage(_M("Impossível conectar ao servidor do google, verifique as regras de firewall."));
            return false;
        }

        return true;
    }

    public function webServices()
    {
        $class = "gnuteca3WebServicesTesting";

        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getConf('home.url'); //url inicial do miolo

        $clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class={$class}";
        $clientOptions["uri"] = "$url";

        try
        {
            $client = new SoapClient(NULL, $clientOptions);
            $result = $client->basicTest(); //Retornar algum valor para comparar
        }
        catch ( Exception $e )
        {
            $this->setMessage(_M('Erro ao acessar webservice: "@1"', 'gnuteca3', $e->getMessage()));
            return false;
        }

        if ( $result == 'basicTest' )
        {
            return true;
        }
        else
        {
            $this->setMessage(_M('Webservice funcionou, mas não obteve o resultado esperado.', 'gnuteca3'));
            return false;
        }
    }

    public function operatorsAuthenticate()
    {
        $MIOLO = MIOLO::getInstance();
        $class = strtolower($MIOLO->getConf('login.class')); //classe de autenticação

        if ( in_array($class, array( 'mauthdbmd5', 'mauthgnuteca' )) )
        {
            return true;
        }
        else
        {
            $this->setMessage(_M('A autenticação de operadores não está no padrão. A configuração do Miolo login.class deve ser MAuthDbMd5 ou MAuthGnuteca', 'gnuteca3'));

            return false;
        }
    }

    public function uploadMaxFilesize()
    {
        $uploadMaxFilesize = str_replace('M', '', ini_get('upload_max_filesize'));

        if ( $uploadMaxFilesize >= 8 )
        {
            return true;
        }

        $this->setMessage(_M("Capacidade do upload de arquivos inferior a 8 Mb", 'gnuteca3'));
        return false;
    }
    
    public function shortOpenTags()
    {
        exec("php5 -info | grep 'short_open_tag' ", $message); 

        if ( stripos( $message[0], 'On') > 0 )
        {
            return true;
        }

        $this->setMessage(_M("short_open_tags está desativada, as tarefas em segundo plano não serão executadas;", 'gnuteca3'));
        return false;
    }
    
    public function standardConformingStrings()
    {
        $value = bCatalogo::obterConfiguracaoPostgres("standard_conforming_strings", 'gnuteca3');

        if ( $value == 'off' )
        {
            return true;
        }

        $this->setMessage(_M("standard_conforming_strings deve ser 'off'. Verifique a configuração no postgresql.conf", 'gnuteca3'));
        return false;
    }
    
    
}

?>