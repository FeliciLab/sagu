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
 * Brief Class Description.
 * Complete Class Description.
 */

class MIOLOConsole
{
    private $MIOLO, $module;

    function __construct()
    {

    }

    public function getMIOLOInstance($pathMiolo, $module, $httpHost = 'miolo25')
    {
        ob_start();
        echo "MIOLO console\n\n";

        $this->module = $module;

        /**
         * Simula as vari�veis do apache que s�o necess�rias para o MIOLO
         */
        $_SERVER['DOCUMENT_ROOT']   = $pathMiolo . '/html';
        $_SERVER['HTTP_HOST']       = strlen($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $httpHost;
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['QUERY_STRING']    = strlen($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'module=' . $this->module . '&action=main';
        $_SERVER['REQUEST_URI']     = "http://{$_SERVER['HTTP_HOST']}/{$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}";
        $_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'];

        /**
         * Instancia o MIOLO
         */
        require_once ($pathMiolo. '/classes/miolo.class.php');
        $this->MIOLO = MIOLO::getInstance();
        ob_end_clean();


        return $this->MIOLO;
    }

    function loadMIOLO()
    {
        ob_start();
        $this->MIOLO->handlerRequest();
        $this->MIOLO->conf->loadConf($this->module);
        
        //seta valor da variavel HTTP_HOST do Apache caso não tenha ainda
        if ( $_SERVER['HTTP_HOST'] == 'miolo25' )
        {
            $_SERVER['HTTP_HOST'] = URL_GNUTECA; //seta na variavel do apache o valor definido na preferência URL_GNUTECA
            $this->MIOLO->setDispatcher(URL_GNUTECA . $_SERVER['SCRIPT_NAME']); //muda o dispatch do miolo
        }
        
        ob_end_clean();
    }

}
?>
