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
 * @author Fabiano Tomasini [fabiano@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Fabiano Tomasini [fabiano@solis.com.br]
 *
 * @since
 * Class created on 06/03/2013
 *
 * */

    $xml = simplexml_load_file('/home/solis/sagu2/misc/changes/changes.xml');

    foreach($xml->sql as $item)
    {
        echo $item->tag;
    }

class bSyncStructDatabase extends SimpleXMLElement
{

    private $patch;

    private $xml;
    
    public function __construct()
    {
        // Seta caminho do arquivo de modificações
        $this->setChangesFilePath();
        // Carrega xml
        $this->loadXMLFile();
    }


    private function loadXMLFile()
    {
        $MIOLO = MIOLO::getInstance();

        try
        {
            if( ! $this->xml = simplexml_load_file($this->patch) )
            {
                throw new Exception("Não foi possível carregar o arquivo XML {$this->patch}. O arquivo pode estar com problemas.");
            }
        }
        catch (Exception $e)
        {
            $MIOLO->error($e->getMessage());
        }

    }

    public function syncStructDatabase()
    {
        
    }

    /*
     * Obtém arquivo de mudança
     */
    private function setChangesFilePath()
    {
        $MIOLO = MIOLO::getInstance();
        $path = $MIOLO->getConf('home.miolo') . '/modules/basic/syncdb/dbchanges.xml';
        
        try
        {
            if( file_exists($path) )
            {
                $this->path = $path;
            }
            else
            {
                throw new Exception("O arquivo $path não existe");
            }
        }
        catch (Exception $e)
        {
            $MIOLO->error($e->getMessage());
        }
    }

    

//    $xml = simplexml_load_file('/home/solis/sagu2/misc/changes/changes.xml');

//    foreach($xml->SHOUTCASTSERVER as $item)
//    {
//        echo 'DJ: ' . $item->tag . '<br />';
//    }


}
?>
