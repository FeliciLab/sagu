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
 * This file handles the connection and actions for gtcFavorite tables
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 21/08/2008
 *
 **/
class BusinessGnuteca3BusFavorite extends GBusiness
{
	public $personId;
	public $controlNumber;
	public $entraceDate;

    public $personIdS;
    public $controlnumberS;
	public $entraceDateS;
    public $beginEntraceDateS;
    public $endEntraceDateS;
    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO        = MIOLO::getInstance();
        $this->tables       = 'gtcFavorite';
        $this->id           = 'personId, controlNumber';
        $this->columnsNoId  = 'entraceDate';
        $this->columns      = $this->id . ',' . $this->columnsNoId;
    }

    public function insertFavorite()
    {
    	return $this->autoInsert();
    }


    public function getFavorite($personId, $controlNumber)
    {
    	return $this->autoGet($personId, $controlNumber);
    }

    /**
     * Delete a record
     *
     * @param $fineId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteFavorite($personId, $controlnumber)
    {
        $data[] = $personId;
        $data[] = $controlnumber;

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $this->setWhere('controlnumber = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * List all records from the tables handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire tables
     *
     **/
    public function searchFavorite()
    {
        $this->clear();
        $this->setColumns($this->id . ' , '. $this->columnsNoId);
        $this->setTables($this->tables);
        if ($this->personIdS)
        {
            $this->setWhere('personId = ?');
            $args[] = $this->personIdS;
        }
        if ($this->controlnumberS)
        {
            $this->setWhere('controlNumber = ?');
            $args[] = $this->controlnumberS;
        }
        if ($this->entraceDateS)
        {
            $this->setWhere('date(entraceDate) = date(?)');
            $args[] = $this->entraceDateS;
        }
        if ($this->beginEntraceDateS)
        {
            $this->setWhere('date(entraceDate) >= date(?)');
            $args[] = $this->beginEntraceDateS;
        }
        if ($this->endEntraceDateS)
        {
            $this->setWhere('date(entraceDate) <= date(?)');
            $args[] = $this->endEntraceDateS;
        }
        $sql = $this->select($args);
        $rs  = $this->query($sql);
        return $rs;
    }







    /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("personId");
        $this->setTables($this->tables);
        $this->setWhere(' personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }

}
?>
