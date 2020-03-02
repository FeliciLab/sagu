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
 * This file handles the connection and actions for z3950servers table
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/12/2010
 *
 **/

class BusinessGnuteca3BusZ3950Servers extends GBusiness
{
    public $colsNoId;

    public $serverId;
    public $description;
    public $host;
    public $recordType;
    public $sintax;
    public $username;
    public $password;
    public $country;

    public $serverIdS;
    public $descriptionS;
    public $hostS;
    public $recordTypeS;
    public $sintaxS;
    public $usernameS;
    public $passwordS;
    public $countryS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcz3950servers';
        $this->colsNoId = 'description, host, recordtype, sintax, username, password, country';
        $this->id       = 'serverid';
        $this->columns  = $this->id.', ' . $this->colsNoId;
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listZ3950Servers($toObject = false)
    {
        $data = array();

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject);

        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getZ3950Servers($serverId, $return = FALSE)
    {
        $data = array($serverId);

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('serverId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        if ($return  == false )
        {
        	$this->setData($rs[0]);
	        return $this;
        }
        else
        {
        	return $rs[0];
        }
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchZ3950Servers()
    {
        $this->clear();

		if ( $this->serverIdS )
		{
		    $this->setWhere('serverId = ?');
		    $data[] = $this->serverIdS;
		}

        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ( $this->hostS )
        {
            $this->setWhere('lower(host) LIKE lower(?)');
            $data[] = $this->hostS . '%';
        }

        if ( $this->recordTypeS )
        {
            $this->setWhere('recordtype = ?');
            $data[] = $this->recordTypeS;
        }

        if ( $this->sintaxS )
        {
            $this->setWhere('lower(sintax) like lower(?)');
            $data[] = $this->sintaxS . '%';
        }

        if ( $this->usernameS )
        {
            $this->setWhere('lower(username) LIKE lower(?)');
            $data[] = $this->usernameS . '%';
        }

        if ( $this->passwordS )
        {
            $this->setWhere('lower(password) LIKE lower(?)');
            $data[] = $this->passwordS . '%';
        }

        if ( $this->countryS )
        {
            $this->setWhere('lower(country) LIKE lower(?)');
            $data[] = $this->countryS . '%';
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('serverId');
        $sql = $this->select($data);

        $rs  = $this->query($sql);

        return $rs;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertZ3950Servers()
    {
        $data = array($this->description, $this->host, $this->recordType, $this->sintax, $this->username, $this->password, $this->country);

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);

        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateZ3950Servers()
    {
        $data = array($this->description, $this->host, $this->recordType, $this->sintax, $this->username, $this->password, $this->country, $this->serverId);

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('serverId = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $moduleConfig (string): Primary key for deletion
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */
    public function deleteZ3950Servers($serverId)
    {
        $data = array($serverId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('serverId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }
}
?>