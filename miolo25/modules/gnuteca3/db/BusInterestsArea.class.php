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
 *
 * This file handles the connection and actions for basConfig table
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 *
 * @since
 * Class created on 28/07/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusInterestsArea extends GBusiness
{
    public $personId;
    public $classificationAreaId;
    public $interestsArray;

    public $personIdS;
    public $classificationAreaIdS;
    public $busBond;


    /**
     * Class constructor
     **/
    function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');

        parent::__construct();
        $this->defineTables();
    }


    /**
    * Define or redefine the class atributes;
    */
    function defineTables()
    {
        $this->setTables('gtcInterestsArea');
        $this->setId('personId');
        $this->setColumnsNoId('classificationAreaId');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listInterestsArea($object=FALSE)
    {
        $this->defineTables();
        return $this->autoList();
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
    public function getInterestsArea($id)
    {
        $this->defineTables();
        $this->clear;
        //here you can pass how many where you want
        return $this->autoGet($id);
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchInterestsArea($toObject = false)
    {
        $this->defineTables();
        $this->setColumns('personId, classificationAreaId');
        $this->clear();

        //here you can pass how many where you want, or use filters

        $filters  = array(
                    'personId'              => 'equals',
                    'classificationAreaId'  => 'equals'
                    );

        return $this->autoSearch($filters, $toObject);
    }

    /**
     * Áreas de interesse das pessoas com vínculo
     **/
    public function searchInterestsAreaLink($toObject = false)
    {
        $this->clear();

        if ($this->personId)
        {
        	$this->setWhere('A.personId = ?');
            $args[] =  $this->personId;
        }

        if ($this->classificationAreaId)
        {
        	$this->setWhere('A.classificationAreaId = ? ');
            $args[] =  $this->classificationAreaId;
        }

        $dateValidate = !$this->dateValidate ?  date("Y/m/d") : $this->dateValidate;

        $this->setWhere('B.dateValidate >= ? ');
        $args[] =  $dateValidate;


        $this->setTables('    gtcinterestsarea A
                    LEFT JOIN basPersonLink    B
                           ON A.personId = B.personId');

        $this->setColumns(' A.personId,
                            A.classificationAreaId');

        $sql = $this->select($args);
        $rs  = $this->query($sql, $toObject);

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
    public function insertInterestsArea()
    {
        $this->defineTables();
        $this->deleteInterestsArea($this->personId);
        if ($this->interestsArray && $this->personId)
        {
            foreach ($this->interestsArray as $line => $info)
            {
                $this->classificationAreaId = $info;
                $ok = $this->autoInsert();
            }
        }
        return $ok;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateInterestsArea()
    {
        $this->defineTables();
        return $this->autoUpdate();
    }


    /**
     * Delete a record
     *
     * @param $moduleConfig (string): Primary key for deletion
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteInterestsArea($personId)
    {
        $this->defineTables();
        return $this->autoDelete($personId);
    }

    public function mountInterestsArea($personId)
    {

        /*select CA.classificationareaid, CA.areaname, (select personid from  gtcinterestsArea IA  where CA.classificationAreaId = IA.classificationAreaId and personid =4 limit 1 ) from gtcclassificationarea CA; */
        $args = $personId;
        $this->clear();
        $this->setTables('gtcClassificationArea CA');
        $this->setColumns('
                            CA.classificationAreaId,
                            CA.areaname,
                            (   SELECT IA.personid
                                  FROM gtcinterestsArea IA
                                 WHERE CA.classificationAreaId = IA.classificationAreaId
                                   AND personid = ?
                                    LIMIT 1
                            )' );
        $this->setOrderBy('CA.areaname');
        $sql = $this->select($args);
        $rs  = $this->query($sql);
        return $rs;
    }




    /**
     * Altera os registro de um usu�rio por outro
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
