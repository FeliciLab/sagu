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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 06/10/2010
 *
 * */
class bSyncDatabaseView implements bSync
{
    protected $file;
    protected $syncModule;

    public function __construct($file, $module)
    {
        if ( !$file )
        {
            throw new Exception(_M('É necessário informar um arquivo para sincronização de visões.'));
        }
        
        if ( !is_array($file) )
        {
            $this->file[] = $file;
        }
        else
        {
            $this->file = $file;
        }

        if ( !$module )
        {
            throw new Exception(_M('É necessário informar um modulo para sincronização de visões.'));
        }

        $this->module = $module;
    }

    /**
     * Dropar views da base.
     * 
     * @return boolean
     */
    public function drop()
    {
        $content = '';
        foreach ( $this->file as $arquivo ) 
        {
            $content = $content . file_get_contents($arquivo);
        }
        
        if ( !$content )
        {
            return false;
        }
        
        bBaseDeDados::consultar("DROP VIEW IF EXISTS " . $this->getViews($content) . " CASCADE;");
    }
    
    /**
     * Faz a sincronização do arquivo com o banco
     * 
     * @return stdClass
     */
    public function syncronize()
    {
        $content = '';
        foreach ( $this->file as $arquivo ) 
        {
            $content = $content . file_get_contents($arquivo);
        }
        
        if ( !$content )
        {
            return false;
        }

        bBaseDeDados::consultar($content);

        return true;
    }

    /**
     * Faz parser do arquivo sql obtendo a listagem de funções
     * 
     * @param string $content conteúdo do arquivo sql
     * @return array of stdClass
     * 
     */
    protected function getViews($content)
    {
        preg_match_all("/CREATE OR REPLACE VIEW (.*) AS/i", $content, $matches);

        return $matches[1][0];
    }

    /**
     * Retorna um array com os arquivos de sincronização de base do módulo informado.
     * 
     * @param string $module
     * @return array 
     */
    public static function listSyncFiles($module)
    {
        $MIOLO = MIOLO::getInstance();
        $caminho = $MIOLO->getConf('home.miolo').'/modules/'.$module.'/syncdb/views/';
        $pasta = opendir($caminho);
        
        $files = array();
        while ( false !== ($filename = readdir($pasta)) ) 
        {
            if ( pathinfo($filename, PATHINFO_EXTENSION) == 'sql')
            {
                $files[] = $caminho . $filename;
            }
        }
        
        sort($files);
        
        return $files;
    }
    
    public static function syncAllViews($syncModule)
    {
        $files  = BSyncDatabaseView::listSyncFiles( $syncModule );
        
        $view = new BSyncDatabaseView( $files , $syncModule );
        $result = $view->syncronize();
        
        return $result;
    }
}

?>
