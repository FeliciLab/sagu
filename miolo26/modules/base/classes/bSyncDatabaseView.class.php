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

        $this->file = $file;

        if ( !$module )
        {
            throw new Exception(_M('É necessário informar um modulo para sincronização de visões.'));
        }

        $this->module = $module;
    }

    /**
     * Faz a sincronização do arquivo com o banco
     * 
     * @return stdClass
     */
    public function syncronize()
    {
        $content = file_get_contents($this->file);

        if ( !$content )
        {
            return false;
        }

        //lista views no arquivo e no banco
        $fileViews = $this->getViews($content);
        $dbViews = bCatalogo::listarVisoes('public');

        //marca contadores no resultado
        $result = new stdClass();
        $result->file = count($fileViews);
        $result->start = count($dbViews);

        //explode os conteúdo para executar um por um
        $sqlCommands = explode('CREATE OR REPLACE VIEW', $content);
        //filtra array em função de linha em branco
        $sqlCommands = array_values(array_filter($sqlCommands));

        //passa as instruções uma a uma para mostrar o erro corretamente
        foreach ( $sqlCommands as $line => $sql )
        {
            if ( $sql )
            {
                //exclui e recria a view
                $sql = 'DROP VIEW IF EXISTS ' . $fileViews[$line] . ";\n" . 'CREATE OR REPLACE VIEW' . $sql;
                bBaseDeDados::executar($sql);
            }
        }

        //obtem lista atualizada
        $finalDbViews = bCatalogo::listarVisoes();

        //marca no contador
        $result->final = count($finalDbViews);

        $sqlResult = '';

        //busca views a sobrando no banco
        foreach ( $finalDbViews as $line => $view )
        {
            if ( !in_array($view->name, $fileViews) )
            {
                $missing[] = $view->name;
                $sqlResult .= 'CREATE OR REPLACE VIEW ' . $view->name . ' AS ' . $view->source . "\n\n\n";
            }
        }

        //faltantes
        $result->missing = $missing;
        //sql para incluir no views.sql
        $result->sql = $sqlResult;

        return $result;
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
        preg_match_all("/CREATE OR REPLACE VIEW (.*) AS/", $content, $matches);

        return $matches[1];
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
        $path = $MIOLO->getConf('home.miolo') . '/modules/' . $module . '/syncdb/views.sql';

        return glob($path);
    }
}

?>