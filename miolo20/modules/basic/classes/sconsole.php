<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Script que permite rodar funções do SAGU a partir do terminal. Para utilizar,
 * basta incluir este arquivo no script que deve ser executado e acessar todas
 * as funcionalidades como se estivesse em qualquer local do sistema. É
 * possível, por exemplo, fazer $bus = $MIOLO->getBusiness() diretamente ou
 * mesmo utilizar funções da sagu.class, etc.
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @since
 * Class created on 27/05/2011
 *
 **/

// manter error reporting padrão, menos E_NOTICEs
error_reporting(error_reporting() & ~E_NOTICE & ~E_WARNING);

$path = dirname(__FILE__) . '/../../../';

if ( ! file_exists($path . '/classes/miolo.class') )
{
    // verifica onde o miolo existe
    $DIRS = array(
        '/var/www/sagu/',
        '/var/www/sagu2/',
        '/var/www/sagu2local/',
        '/usr/local/sagu/',
        '/usr/local/sagu2/'
    );

    foreach ( $DIRS as $DIR )
    {
        if ( is_dir( $DIR ) )
        {
            $path = $DIR;
            break;
        }
    }
}

if ( strlen($path) == 0 )
{
    die("Diretório do miolo não encontrado. Você deve usar um dos diretórios a seguir para o Miolo:\n" . implode("\n", $DIRS) . "\n");
}

// definir algumas variáveis globais
$classPath  = $path . '/classes/';
$module     = array_key_exists('module', $_REQUEST) ? $_REQUEST['module'] : 'sagu2';
$action     = array_key_exists('action', $_REQUEST) ? $_REQUEST['action'] : 'main';

include("$classPath/miolo.class");

class MIOLOConsole
{
    private $MIOLO, $module;

    public function __construct()
    {
    }

    public function getMIOLOInstance($pathMiolo, $module)
    {
        /**
         * Carregar miolo.conf
         */
        $conf = new MConfigLoader();
        $conf->LoadConf();
	
        $url = $conf->getConf('home.url');

        if ( !$url )
        {
            $url = 'http://localhost/sagu';
            
            consoleOutput("A opcao home.url nao foi definida no miolo.conf, portanto sera utilizado o padrao: {$url} - Caso este nao seja o endereco de acesso da instalacao, altere manualmente no miolo.conf");
        }
        
        ob_start();
        
        echo "MIOLO console\n\n";

        $this->module = $module;

        /**
         * Simula as variáveis do apache que são necessárias para o MIOLO
         */
        $_SERVER['DOCUMENT_ROOT']   = $pathMiolo . '/html';
        $_SERVER['SCRIPT_FILENAME'] = $pathMiolo . '/html/index.php';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['QUERY_STRING']    = array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : 'module=' . $this->module . '&action=main';        
	$_SERVER['HTTP_HOST']       = str_replace(array('http://', 'https://'), '', $url);
        $_SERVER['REMOTE_ADDR']     = '1.1.1.1';
        $_SERVER['REQUEST_URI']     = $url . "/{$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}";

        /**
         * Instancia o MIOLO
         */
        $this->MIOLO = MIOLO::getInstance();
        $this->MIOLO->conf = $conf;
        ob_end_clean();

        return $this->MIOLO;
    }

    public function loadMIOLO()
    {
        ob_start();
        $this->MIOLO->handlerRequest();
        ob_end_clean();
    }

}

$MIOLOConsole = new MIOLOConsole();

$GLOBALS['MIOLO'] = $MIOLO = $MIOLOConsole->getMIOLOInstance($path, $module);
$MIOLOConsole->loadMIOLO();

$MIOLO->setConf('usuario_console', $MIOLO->getConf('db.basic.user'));

// Simular um login
$login = new MLogin('sagu2', null, 'sagu2');
$MIOLO->getAuth()->setLogin($login);

function consoleOutput($msg)
{
    echo '>> ' . $msg . "\n";
}

?>
