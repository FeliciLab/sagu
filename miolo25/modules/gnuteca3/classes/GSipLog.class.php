<?php

/**
 * <--- Copyright 2005-2014 de Solis - Cooperativa de Soluções Livres Ltda. e
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
  @ @author Tcharles Silva [tcharles@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Pereira da Silva[eduardo@solis.coop.br]
 * Lucas Gerhardt
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 10/01/2014
 *
 * */

class GSipLog
{
    public function __construct()
    {
        $miolo = MIOLO::getInstance();
    }

    /*
     * Registra um arquivo de log para equipamentos SIP
     * Criado em Junho/2014
     * Por: Tcharles Silva
     */
    public static function insertSipLog($msg)
    {
        $path = LOCAL_SIPLOG;
        
        //Verifica se o diretório existe
        if(!is_dir($path))
        {
            //Cria diretorio de logs
            mkdir($path, 0777);
        }
            
        //Verifica se tem permissãoo para gravar no arquivo
        if(!file_exists("$path/logSip.log"))
        {
            $fp = fopen("$path/logSip.log", "a+");
            fclose($fp);
        }
        
        
        if(is_writable("$path/logSip.log"))
        {
            //Só receberá o time, se for a mensagem de resposta do webservice

            $fileOutput = fopen("$path/logSip.log", "a+");
            $date = GDate::now()->getDate();
            $msg = $date . " - " . $msg;
            fwrite($fileOutput, $msg."\n");
            fclose($fileOutput);
        }
    }
}
?>
