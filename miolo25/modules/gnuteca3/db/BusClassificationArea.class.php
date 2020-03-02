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
class BusinessGnuteca3BusClassificationArea extends GBusiness
{
    public $classificationAreaId;
    public $areaName;
    public $classification;
    public $ignoreClassification;

    public $classificationAreaIdS;
    public $areaNameS;
    public $classificationS;
    public $ignoreClassificationS;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct(
                            'gtcClassificationArea',
                            'classificationAreaId',
                            'areaName,
                            classification,
                            ignoreClassification'
                            );
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listClassificationArea($object=FALSE)
    {
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
    public function getClassificationArea($id)
    {
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
    public function searchClassificationArea($toObject = false)
    {
        unset($this->classificationAreaId); //estava ocorrendo bug pos-busca no formulario
        //here you can pass how many where you want, or use filters
        $filters  = array(
                    'classificationAreaId'  => 'equals',
                    'areaName'              => 'ilike' ,
                    'classification'        => 'ilike',
                    'ignoreClassification'  => 'ilike'
                    );

        return $this->autoSearch($filters, $toObject);
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertClassificationArea()
    {
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateClassificationArea()
    {
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
    public function deleteClassificationArea($holidayId)
    {
        return $this->autoDelete($holidayId); //aceita vários id separados por vírgula
    }
}
?>
