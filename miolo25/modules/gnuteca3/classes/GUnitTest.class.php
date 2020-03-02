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
  */

//procura path do miolo
if ( !$_SERVER['HTTP_HOST'] ) //chamado por phpunit
{
    $pwd = $_SERVER['PWD']; //pwd atual
    $oldPwd = $_SERVER['OLDPWD']; //anterior
    $script = $_SERVER['argv'][1]; //chamada do script

    $mioloPath = '';
    if( ($pos = strpos($oldPwd, 'modules')) !== false )
    {
        $mioloPath = substr($oldPwd, 0, $pos);
    }
    else if ( ($pos = strpos($script, 'modules')) !== false )
    {
        $mioloPath = substr($script, 0, $pos);

        if ( substr($mioloPath, 0, 1) != '/' )
        {
            $mioloPath = $pwd . $mioloPath;
        }
    }
    else if ( ($pos = strpos($pwd, 'modules')) !== false )
    {
        $mioloPath = substr($pwd, 0, $pos);
    }
}

//$mioloPath = '/var/www/gnuteca3.eagle/';

$mioloClassesPath   = "$mioloPath/classes";
$module             = 'gnuteca3';
$mioloConsoleFile   = "$mioloPath/modules/$module/classes/mioloconsole.class.php";

$localFile          = str_replace("\\", "/", dirname(__FILE__)); 

// checa se miolo existe
if(!file_exists($mioloPath))
{
    die("\n\n\nMiolo Path not exists!!! \nFile: $mioloPath\n\n\n");
}
if(!file_exists($mioloConsoleFile))
{
    die("\n\n\nMiolo Console File not exists!!! \nFile: $mioloConsoleFile\n\n\n");
}

require_once($mioloConsoleFile);
$MIOLOConsole = new MIOLOConsole();
$GLOBALS['MIOLO'] = $MIOLO = $MIOLOConsole->getMIOLOInstance($mioloPath, $module);
$MIOLOConsole->loadMIOLO();
$MIOLO->uses('handlers/gnutecaClasses.inc.php', $module);
$MIOLO->uses('handlers/define.inc.php', $module);

//limpa a "sessão" dos testes unitários
file_put_contents( BusinessGnuteca3BusFile::getAbsoluteServerPath(true) . '/gUnit.serialize', '');

class GUnitTest extends PHPUnit_Framework_TestCase
{
    protected $MIOLO, $backupGlobalsBlacklist = array('MIOLO', 'autoload', 'MIOLOConsole');

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
    }

    /*
    * Método para mostrar mensagem na tela
    *
    * @param string $msg
    */
    protected function exibe($msg)
    {
        if ( !is_string($msg ))
        {
            var_export($msg);
        }
        else
        {
            echo $msg;
        }

        echo  "\n";
    }

    /**
     * Obtem um valor "salvo na sessão" do GUnit.
     *
     * @param string $name a variável a obter
     * @return mixed
     */
    protected function getValue($name)
    {
        $path = BusinessGnuteca3BusFile::getAbsoluteServerPath(true). '/gUnit.serialize';
        $content = file_get_contents( $path );

        if ( !$content )
        {
            //é usado o die, pois o throw não pararia o resto dos testes
            //die('Arquivo serializado não encontrado, impossível recuperar informações do teste!.');
        }
        
        $file = unserialize($content);
	
        return $file->$name;
    }

    /**
     * Define um valor para ser usado em outro momento, algo semelhante a sessão.
     *
     * @param string $name a variável a salvar
     * @param string $value o conteúdo a salvar
     */
    protected function setValue($name, $value)
    {
        $path = BusinessGnuteca3BusFile::getAbsoluteServerPath(true). '/gUnit.serialize';

        if ( !is_writable( $path) )
        {
            die('Impossível escrever no arquivo '. $path );
        }

        //obtem o arquivo para manter o que já existe nele
        $file = unserialize( file_get_contents( $path ) );
        //define o valor
        $file->$name = $value;
        //salva o arquivo no disco novamente
        $ok = file_put_contents( $path,  serialize($file) );

        if ( !$ok )
        {
            die('Impossível salvar arquivo de valores de teste! ' . $path );
        }

        chmod( $path, 0777);
	}
}
?>