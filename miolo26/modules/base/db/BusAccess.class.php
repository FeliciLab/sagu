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
 *
 * This file handles the connection and actions for basAccess table
 *
 * @author Daniel Afonso Heisler [daniel@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 30/01/2006
 *
 **/

/**
 * Class to manipulate the basAccess table
 **/
class BusinessBaseBusAccess extends Business
{

    /**
     * Make a connection to the database
     * 
     * @param $module (string): The module whose database we should connect. If null, the actual module database is connected.
     *
     * @return (object): A MIOLO Database connection
     **/
    public function getDatabase($module = null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('base','bBaseDeDados');
        $module = is_null($module) ? 'base' : $module;

        return $MIOLO->getDatabase($module);
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchAccess($filters)
    {

        $db  = $this->getDatabase();

        $msql = new MSQL();
        $msql->setTables('basAccess');
        $msql->setColumns('login');
        $msql->setColumns('moduleAccess');
        $msql->setColumns('label');
        $msql->setColumns('image');
        $msql->setColumns('handler');
        $msql->setColumns('count(*)');
        
        if ( strlen($filters->login) > 0 )
        {
            $msql->setWhere(' UPPER(login) = UPPER(?)');
            $args[] = $filters->login;
        }

        $filters->isBookmark = $filters->isBookmark == true ? DB_TRUE : DB_FALSE;

        if ( strlen($filters->isBookmark) > 0 )
        {
            $msql->setWhere(' isBookmark = ? ');
            $args[] = $filters->isBookmark;
        }

        if ( strlen($filters->handler) > 0 )
        {
            $msql->setWhere(' handler ILIKE ? ');
            $args[] = $filters->handler;
        }

        if ( strlen($filters->moduleAccess) > 0 )
        {
            $msql->setWhere(' moduleAccess ILIKE ? ');
            $args[] = $filters->moduleAccess;
        }

        unset($result);
        if ( strlen($filters->moduleAccess) > 0 || strlen($filters->login) > 0 )
        {

             $msql->setWhere(substr($where, 4));
             $msql->setGroupBy('login,
                             moduleAccess,
                             label,
                             image,
                             handler');
             
             $msql->setOrderBy('login,
                             count(*) DESC,
                             moduleAccess,
                             label');
             


             //TODO: inserir na base
            if ( $filters->isBookmark == DB_TRUE )
            {
                $msql->setParameters(bBaseDeDados::obterParametro('BASE', 'BOOKMARK_LIMIT'));
            }
            else
            {
                $msql->setParameters(bBaseDeDados::obterParametro('BASE', 'MORE_VISITED_LIMIT'));
            }

            $result = bBaseDeDados::consultar($msql, $args);

        }
        return $result;
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertAccess($data)
    {

        $db  = $this->getDatabase();

        $sql = 'INSERT INTO basAccess
                            (login,
                             moduleAccess,
                             label,
                             image,
                             handler,
                             isBookmark)
                     VALUES (?,?,?,?,?,?)';

        $data->isBookmark = ($data->isBookmark === true || $data->isBookmark == 't') ? DB_TRUE : DB_FALSE;

        $args = array( 
                       $data->login,
                       $data->moduleAccess,
                       $data->label,
                       $data->image,
                       $data->handler,
                       $data->isBookmark
                     );

        if ( $data->isBookmark == DB_TRUE )
        {
            $res = $this->searchAccess($data);

            if ( count($res) == 0 )
            {
                $result = $db->execute(SAGU::prepare($sql, $args, false));
            }
        }
        else
        {
            $result = $db->execute(SAGU::prepare($sql, $args, false));
        }

        

        return $result;
    }

    /**
     * Delete a record
     *
     * @param $login (string): User login for deletion
     * @param $module (string): User module for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteAccess($login, $moduleAccess=NULL, $isBookmark=false)
    {

        $db  = $this->getDatabase();

        $sql = 'DELETE FROM basAccess
                      WHERE login ILIKE ? ';
        $args[] = $login;

        $isBookmark = ($isBookmark === true || $isBookmark == 't') ? DB_TRUE : DB_FALSE;

        if ( strlen($moduleAccess)>0 )
        {
            $sql .= ' AND moduleAccess ILIKE ?';
            $args[] = $moduleAccess;
        }

        if ( strlen($isBookmark)>0 )
        {
            $sql .= ' AND isBookmark = ?';
            $args[] = $isBookmark;
        }

        $result = $db->execute(SAGU::prepare($sql, $args)); 

        return $result;
    }

}

?>
