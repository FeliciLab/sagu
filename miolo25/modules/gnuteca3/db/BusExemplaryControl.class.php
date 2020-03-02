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
 * This file handles the connection and actions for gtcExemplaryControl table
 *
 * @author Moises Heberle [moises@solis.coop.br]
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
 * Class created on 03/10/2008
 *
 * */
class BusinessGnuteca3BusExemplaryControl extends GBusiness
{
    public $MIOLO;
    public $module;
    public $cols;
    public $pkeys;
    public $pkeysWhere;
    public $fullColumns;
    public $busExemplaryStatusHistory;
    public $busMaterial;
    public $busMaterialControl;
    public $busLibraryUnit;
    public $busExemplaryStatus;
    public $busMaterialGender;
    public $busMaterialType;
    public $busOperatorLibraryUnit;
    public $busExemplaryFutureStatusDefined;
    public $controlNumber;
    public $itemNumber;
    public $libraryUnitId;
    public $acquisitionType;
    public $exemplaryStatusId;
    public $materialGenderId;
    public $entranceDate;
    public $lowDate;
    public $line;
    public $originalLibraryUnitId;
    public $materialPhysicalTypeId;
    public $materialTypeId;
    //FIXME adicionar registro de estado futuro no histórico, ticket #7358
    public $futureStatusId;
    public $controlNumberS;
    public $itemNumberS;
    public $libraryUnitIdS;
    public $acquisitionTypeS;
    public $exemplaryStatusIdS;
    public $materialGenderIdS;
    public $entranceDateS;
    public $lowDateS;
    public $originalLibraryUnitIdS;
    private $busUpdateSearch;

    /**
     * Class constructor
     * */
    function __construct()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busExemplaryStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $this->busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busMaterialGender = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
        $this->busMaterialType = $this->MIOLO->getBusiness($this->module, 'BusMaterialType');
        $this->busOperatorLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusOperatorLibraryUnit');
        $this->busExemplaryFutureStatusDefined = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');
        $this->busUpdateSearch = $this->MIOLO->getBusiness($this->module, 'BusUpdateSearch');
        
        $this->table = 'gtcExemplaryControl';
        $this->pkeys = 'controlNumber,
                           itemNumber';

        $this->cols = 'libraryUnitId,
                           acquisitionType,
                           exemplaryStatusId,
                           materialGenderId,
                           entranceDate,
                           lowDate,
                           line,
                           originalLibraryUnitId,
                           observation,
                           materialTypeId,
                           materialPhysicalTypeId';

        $this->fullColumns = str_replace(array( "\n", " " ), "", $this->pkeys . ',' . $this->cols);
        $this->cols = str_replace(array( "\n", " " ), "", $this->cols);
    }

    /**
     * Return a specific record from the database
     *
     * @param $itemNumber (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     * //TODO otimizar o extraInfo pra fazer menos sql???
     *
     * */
    public function getExemplaryControl($itemNumber, $extraInfo = FALSE)
    {
        $this->clear();
        $this->setColumns('controlNumber,
							itemNumber,
							EC.libraryUnitId,
							acquisitionType,
							EC.exemplaryStatusId,
							EC.materialGenderId,
							entranceDate,
							lowDate,
							line,
							originalLibraryUnitId,
							EC.observation,
							EC.materialTypeId,
							materialPhysicalTypeId,
							LU.libraryName as libraryName,
                            MG.description as materialGenderDescription,
							MT.description as materialTypeDescription,
							MS.description as exemplaryStatusDescription
							');
        
        $sql = "SELECT controlNumber,
                itemNumber,
                EC.libraryUnitId,
                acquisitionType,
                EC.exemplaryStatusId,
                EC.materialGenderId,
                entranceDate,
                lowDate,
                line,
                originalLibraryUnitId,
                EC.observation,
                EC.materialTypeId,
                materialPhysicalTypeId,
                LU.libraryName as libraryName,
                MG.description as materialGenderDescription,
                MT.description as materialTypeDescription,
                MS.description as exemplaryStatusDescription
           FROM gtcExemplaryControl EC
     INNER JOIN gtcLibraryUnit LU
             ON (EC.libraryUnitId = LU.libraryUnitId )
     INNER JOIN gtcMaterialGender MG
             ON (EC.materialGenderId = MG.materialGenderId )
     INNER JOIN gtcMaterialType MT
             ON (EC.materialTypeId = MT.materialTypeId )
        INNER JOIN gtcExemplaryStatus MS
             ON ( EC.exemplaryStatusId = MS.exemplaryStatusId)
         WHERE itemNumber = '{$itemNumber}'";

        $rs = $this->query($sql, true);
        $rs = $rs[0];

        if ( $rs && $extraInfo )
        {
            $this->busMaterialGender = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
            $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
            $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

            $rs->libraryName = $busLibraryUnit->getLibraryName($rs->libraryUnitId);

            $materialGender = $this->busMaterialGender->getMaterialGender($rs->materialGenderId, true);
            $rs->materialGenderDescription = $materialGender->description;

            $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($rs->exemplaryStatusId, true);

            $rs->exemplaryStatusDescription = ($exemplaryStatus->mask) ? $exemplaryStatus->mask : $exemplaryStatus->description;

            $rs->currentStatus = $exemplaryStatus;

            //Pegar só o campo 090.a
            $classification = MARC_CLASSIFICATION_TAG;
            list($noventa, $oitenta) = explode(',', $classification);

            $rs->title = $this->busMaterial->getContentTag($rs->controlNumber, MARC_TITLE_TAG);
            $rs->author = $this->busMaterial->getContentTag($rs->controlNumber, MARC_AUTHOR_TAG);
            $rs->classification = $this->busMaterial->getContentTag($rs->controlNumber, $noventa);
            $rs->cutter = $this->busMaterial->getContentTag($rs->controlNumber, MARC_CUTTER_TAG);
        }

        return $rs;
    }
    
    /**
     * Lista quantidade de obras
     * 
     * @return array
     */
    public function countTotalExemplares()
    {
        $rs = $this->query("SELECT COUNT(controlNumber) FROM gtcExemplaryControl");
        return $rs[0];
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchExemplaryControl($toObject = FALSE)
    {
        $this->clear();

        if ( $this->controlNumberS )
        {
            $this->setWhere('controlNumber = ?');
            $data[] = $this->controlNumberS;
        }
        if ( $this->itemNumberS )
        {
            $this->setWhere('itemNumber = ?');
            $data[] = $this->itemNumberS;
        }
        if ( $this->libraryUnitIdS )
        {
            if ( ereg(',', $this->libraryUnitIdS) )
            {
                $this->libraryUnitIdS = explode(',', $this->libraryUnitIdS);
            }

            if ( is_array($this->libraryUnitIdS) )
            {
                $dado = implode(',', $this->libraryUnitIdS);
                $this->setWhere("libraryUnitId in ( $dado )");
            }
            else
            {
                $this->setWhere('libraryUnitId = ?');
                $data[] = $this->libraryUnitIdS;
            }
        }
        if ( $this->originalLibraryUnitIdS )
        {
            if ( ereg(',', $this->originalLibraryUnitIdS) )
            {
                $this->originalLibraryUnitIdS = explode(',', $this->originalLibraryUnitIdS);
            }

            if ( is_array($this->originalLibraryUnitIdS) )
            {
                $dado = implode(',', $this->originalLibraryUnitIdS);
                $this->setWhere("( libraryUnitId in ( $dado ) OR originalLibraryUnitId in ($dado) ) ");
            }
            else
            {
                $this->setWhere('( libraryUnitId = ? OR originalLibraryUnitId = ? ) ');
                $data[] = $this->originalLibraryUnitIdS;
                $data[] = $this->originalLibraryUnitIdS;
            }
        }
        if ( $this->acquisitionTypeS )
        {
            $this->setWhere('acquisitionType = ?');
            $data[] = $this->acquisitionTypeS;
        }
        if ( $this->exemplaryStatusIdS )
        {
            $this->setWhere('exemplaryStatusId = ?');
            $data[] = $this->exemplaryStatusIdS;
        }
        if ( $this->exemplaryStatusIdNotIn )
        {
            $this->setWhere("exemplaryStatusId NOT IN ('$this->exemplaryStatusIdNotIn')");
        }
        if ( $this->materialGenderIdS )
        {
            $this->setWhere('materialGenderId = ?');
            $data[] = $this->materialGenderIdS;
        }
        if ( $this->entranceDateS )
        {
            $this->setWhere('entranceDate = ?');
            $data[] = $this->entranceDateS;
        }
        if ( $this->lowDateS )
        {
            $this->setWhere('lowDate = ?');
            $data[] = $this->lowDateS;
        }
        if ( $this->line )
        {
            $this->setWhere('line = ?');
            $data[] = $this->line;
        }

        if ( !$toObject )
        {
            $this->setTables($this->table);
            $this->setColumns($this->cols);
        }
        else
        {
            $this->setTables('gtcExemplaryControl EC
        	        LEFT JOIN gtcLibraryUnit LU
        	            USING (libraryUnitId)
        	        LEFT JOIN gtcExemplaryStatus ES
        	            USING (exemplaryStatusId)
                    LEFT JOIN gtcMaterialType MT
                        USING (materialTypeId)
                    LEFT JOIN gtcMaterialGender MG
                        USING (materialGenderId)
                    LEFT JOIN gtcMaterialPhysicalType MP
                        USING (materialPhysicalTypeId)
        	');
            //$this->setTables($this->table);
            $this->setColumns('
                            controlNumber,
                            itemNumber,
                            EC.libraryUnitId,
                            acquisitionType,
                            exemplaryStatusId,
                            materialGenderId,
                            entranceDate,
                            lowDate,
                            line,
                            originalLibraryUnitId,
                            EC.observation,
                            materialTypeId,
                            materialPhysicalTypeId,
                            libraryName,
                            ES.description as exemplaryStatusDescription,
                            MT.description as materialTypeDescription,
                            MG.description as materialGenderDescription,
                            ES.isLowStatus as isLowStatus ,
                            MP.description as materialPhysicalTypeDescription
                            ');
        }

        //coalesce(ES.mask,ES.description) as exemplaryStatusDescription,

        $this->setOrderBy("line");
        $sql = $this->select($data);
        $rs = $this->query($sql, ($toObject) ? TRUE : FALSE);
        return $rs;
    }

    /**
     * List exemplarys of a Material (by Control Number)
     *
     * @param integer $controlNumber the controlNumber of material
     * @param integer $libraryUnitId the id of the library to filter
     * @return object
     */
    public function getExemplaryOfMaterial($controlNumber, $libraryUnitId = null, $extraInfo = null, $extraMarcTagsToGet = null, $getOriginalLibraryExemplaresToo = false, $getFromFather = false)
    {
        $this->isFatherExemplar = false; //define na classe se são exemplares do pai ou não, ele troca depois da iteração caso seja necessário
        $this->controlNumberS = $controlNumber;

        //para buscar original libraryUnit
        if ( $libraryUnitId != null )
        {
            if ( $getOriginalLibraryExemplaresToo )
            {
                $this->originalLibraryUnitIdS = $libraryUnitId;
            }
            else
            {
                $this->libraryUnitIdS = $libraryUnitId;
            }
        }

        $result = $this->searchExemplaryControl(true);

        //se for para pegar exemplares do pai e não tiver exemplares e for artigo
        if ( $getFromFather &&
                !$result &&
                ( $this->busMaterialControl->isBookArticle($controlNumber)
                || $this->busMaterialControl->isColletionArticle($controlNumber) )
        )
        {
            //pega número de controle do pai
            $controlNumberFather = $this->busMaterialControl->getControlNumberFather($controlNumber);

            if ( $controlNumberFather )
            {
                //tenta buscar os exemplares pelo pai
                $this->controlNumberS = $controlNumberFather;
                $result = $this->searchExemplaryControl(true);
                $this->isFatherExemplar = true; //define na classe que são exemplares do pai, o programador da interface pode detectar por aqui
            }
        }

        //FIXME tem que otimizar pra ser só uma chamada no banco //acredito este if não precise mais ser usado
        if ( is_array($result) && $extraInfo )
        {
            foreach ( $result as $line => $info )
            {
                //busca Objeto do estado do Exemplar
                $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($info->exemplaryStatusId, true);
                $result[$line]->exemplaryStatus = $exemplaryStatus;

                $text = $exemplaryStatus->mask;

                if ( !$text )
                {
                    $text = $exemplaryStatus->description;
                }

                $result[$line]->exemplaryStatusDescription = $text;

                $volume = MARC_EXEMPLARY_VOLUME_TAG;
                $tomo = MARC_EXEMPLARY_TOMO_TAG;

                $result[$line]->volume = $this->busMaterial->getContentTag($controlNumber, MARC_EXEMPLARY_VOLUME_TAG, $info->line);
                $result[$line]->$volume = $result[$line]->volume;
                $result[$line]->tomo = $this->busMaterial->getContentTag($controlNumber, MARC_EXEMPLARY_TOMO_TAG, $info->line);
                $result[$line]->$tomo = $result[$line]->tomo;
            }
        }

        //passe um array de campos marcs extras a serem pegos, very usefull
        if ( is_array($result) && is_array($extraMarcTagsToGet) )
        {
            foreach ( $result as $line => $info )
            {
                foreach ( $extraMarcTagsToGet as $name => $tag )
                {
                    $content = $this->busMaterial->getContentTag($controlNumber, $tag, $info->line);

                    $description = $this->busMaterial->relationOfFieldsWithTable($tag, $content, false);

                    if ( !is_array($description) && ($description) )
                    {
                        $tmp = "{$tag}_DESC";
                        $result[$line]->$tmp = $description;
                    }

                    if ( !is_numeric($name) )
                    {
                        $result[$line]->$name = $content;
                    }

                    if ( strlen($tag) )
                    {
                        $result[$line]->$tag = $content;
                    }
                }
            }
        }


        return $result;
    }

    /**
     * List all exemplarys of an material and return it as an array separated by libraryUnitId and exemplaryStatusId
     *
     * @param integer $controlNumber
     * @param integer  $libraryUnitId
     * @param boolean $getFromFather is don't find exemplarys in self controlNumber, locate in father
     */
    public function getExemplaryOfMaterialByLibrary($controlNumber, $libraryUnitId = null, $getFromFather = false)
    {
        $data = $this->getExemplaryOfMaterial($controlNumber, $libraryUnitId, true, null, false, $getFromFather);
        $result = null;

        if ( is_array($data) && $data )
        {
            foreach ( $data as $line => $info )
            {
                $result[$info->libraryUnitId][$info->exemplaryStatusId][] = $info;
            }
        }

        return $result;
    }

    /**
     * List all exemplarys of an material and return it as an array separated by libraryUnitId and exemplaryStatusId
     *
     * @param integer $controlNumber
     * @param intger  $libraryUnitId
     */
    public function getExemplaryOfMaterialByGrid($controlNumber, $libraryUnitId = null, $getOriginalLibraryExemplaresToo = false, $getFromFather = false)
    {
        $data = $this->getExemplaryOfMaterial($controlNumber, $libraryUnitId, false, null, $getOriginalLibraryExemplaresToo, $getFromFather);
        $result = null;

        if ( is_array($data) && $data )
        {
            foreach ( $data as $line => $info )
            {
                $result[$info->libraryUnitId][$info->exemplaryStatusId][$info->materialTypeId][$info->materialPhysicalTypeId][] = $info;
            }
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
     * */
    public function insertExemplaryControl()
    {
        if ( empty($this->itemNumber) )
        {
            return false;
        }

        $columns = $this->fullColumns;

        if ( is_null($this->lowDate) )
        {
            $columns = str_replace(",lowDate", "", $columns);
        }
        if ( is_null($this->materialGenderId) || !strlen($this->materialGenderId) )
        {
            $columns = str_replace(",materialGenderId", "", $columns);
        }

        $this->clear();
        $this->setColumns($columns);
        $this->setTables($this->table);
        $sql = $this->insert($this->associateData($columns));

        $rs = $this->execute($sql);

        if ( $rs )
        {
            $this->busExemplaryStatusHistory->itemNumber = $this->itemNumber;
            $this->busExemplaryStatusHistory->exemplaryStatusId = $this->exemplaryStatusId;
            $this->busExemplaryStatusHistory->libraryUnitId = $this->libraryUnitId;
            $this->busExemplaryStatusHistory->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            $this->busExemplaryStatusHistory->insertExemplaryStatusHistory();

            //FIXME grava histórico do estado futuro #7358
            if ( $this->futureStatusId )
            {
                $this->busExemplaryStatusHistory->itemNumber = $this->itemNumber;
                $this->busExemplaryStatusHistory->exemplaryStatusId = $this->futureStatusId;
                $this->busExemplaryStatusHistory->libraryUnitId = $this->libraryUnitId;
                $this->busExemplaryStatusHistory->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
                $this->busExemplaryStatusHistory->insertExemplaryStatusHistory();
            }
        }

        return $rs;
    }

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updateExemplaryControl($oldItemNumber = null)
    {
        $this->clear();
        $this->setTables($this->table);

        //TODO Este WORKAROUND/POG foi feito porque quem alguns casos o sql deve ser gerado dando um update
        //do itemnumber (949.a) para um novo valor. Se for feita esta alteração direto no código
        //abaixo do IF, o impacto será muito grande e dadas as circunstâncias poderá gerar mais bugs.
        //Então se $oldItemNumber for passado vai fazer de maneira muito especifica para ele.
        if ( $oldItemNumber )
        {
            $this->setWhere("controlNumber = {$this->controlNumber} AND itemNumber = '{$oldItemNumber}'");
            $columns = $this->cols . "," . $this->pkeys;

            if ( is_null($this->originalLibraryUnitId) )
            {
                $columns = str_replace(",originalLibraryUnitId", "", $columns);
            }

            $this->setColumns($columns);
            $sql = $this->update($this->associateData($columns));
        }
        else //Se for o caso normal prepara o where sem levar em conta o itemnumber
        {
            $this->setWhere('controlNumber = ? AND itemNumber = ?');
            $columns = $this->cols;

            if ( is_null($this->originalLibraryUnitId) )
            {
                $columns = str_replace(",originalLibraryUnitId", "", $columns);
            }

            $this->setColumns($columns);
            $queryData = $this->associateData($columns . ',' . $this->pkeys);
            $sql = $this->update($queryData);
        }

        $rs = $this->execute($sql);

        if ( $rs )
        {
            $this->busExemplaryStatusHistory->itemNumber = $this->itemNumber;
            $this->busExemplaryStatusHistory->exemplaryStatusId = $this->exemplaryStatusId;
            $this->busExemplaryStatusHistory->libraryUnitId = $this->libraryUnitId;
            $this->busExemplaryStatusHistory->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            $this->busExemplaryStatusHistory->insertExemplaryStatusHistory();
        }

        return $rs;
    }

    /**
     * Delete a record
     *
     * @param $itemNumber (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function deleteExemplaryControl($itemNumber, $controlNumber = null)
    {
        $this->clear();
        $this->setTables($this->table);

        $where = 'itemNumber = ? ';

        $args[] = $itemNumber;
        if ( !is_null($controlNumber) )
        {
            $args[] = $controlNumber;
            $where.= ' AND controlNumber = ?';
        }

        $this->setWhere($where);

        $sql = $this->delete($args);
        $rs = $this->execute($sql);
        return $rs;
    }

    public function deleteAllExemplariesByMaterialControl($controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $sql = $this->delete(array( $controlNumber ));
        $rs = $this->execute($sql);
        return $rs;
    }

    /**
     * Troca estado do exemplar
     *
     * Se você não passar o objeto com os dados do exemplar, ele busca no banco
     *
     * @param unknown_type $itemNumber
     * @param unknown_type $exemplaryStatusId
     * @param unknown_type $operator
     * @return unknown
     */
    public function changeStatus($itemNumber, $exemplaryStatusId, $operator, $data = null)
    {
        if ( !$data )
        {
            $data = $this->getExemplaryControl($itemNumber);
        }

        $this->clear();
        $this->setTables($this->table);
        $this->setColumns('exemplaryStatusId');
        $this->setWhere('itemNumber = ?');
        $sql = $this->update(array( $exemplaryStatusId, $itemNumber ));
        $rs = $this->execute($sql);

        if ( $rs ) //Se alterou o estado atualiza tambï¿½m na gtcMaterial
        {
            $rs = $this->updateMaterial($itemNumber, $exemplaryStatusId, MARC_EXEMPLARY_FIELD, MARC_EXEMPLARY_EXEMPLARY_STATUS_SUBFIELD, $data);
        }

        if ( $data->exemplaryStatusId != $exemplaryStatusId )
        {
            $data->operator = $operator;
            $data->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            $data->exemplaryStatusId = $exemplaryStatusId;
            $this->busExemplaryStatusHistory->setData($data);
            $this->busExemplaryStatusHistory->insertExemplaryStatusHistory();
        }

        return $rs;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $itemNumber
     * @param unknown_type $libraryUnitId
     * @return unknown
     */
    public function changeLibraryUnit($itemNumber, $libraryUnitId, $view = false)
    {
        //No empréstimo entre biblioteca, também tem que atualizar a biblioteca da gtcSearchMaterialView
        if ( $view )
        {
            //Atualiza tabela gtcSearchMaterilView, a qual é utilizada na pesquisa
            $this->clear();
            $msqlView = new MSQL('exemplaryLibraryUnitId', 'gtcSearchMaterialView', 'exemplaryItemNumber = ?');
            $sqlView = $msqlView->update(array( $libraryUnitId, $itemNumber ));
            $this->execute($sqlView);

            //Atualiza unidade na gtcMaterial
            list($field, $subField) = explode('.', MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG);
            $this->updateMaterial($itemNumber, $libraryUnitId, $field, $subField);
        }

        $this->clear();
        $msql = new MSQL('libraryUnitId', $this->table, 'itemNumber = ?');
        $sql = $msql->update(array( $libraryUnitId, $itemNumber ));
        $rs = $this->execute($sql);
        if ( $rs )
        {
            list($field, $subField) = explode('.', MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG);
            $this->updateMaterial($itemNumber, $libraryUnitId, $field, $subField);
        }
        return $rs;
    }

    /**
     *  Set Low information of exemplary (make low)
     *
     * @param  string  $itemNumber             the itemNumber to update
     * @param  integer $lowDate                the the of the low
     * @param  string  $observation            the observation you want to update
     * @param  boolean $keepBeforeObservation  mantain the before observation (add it to actual)
     * @param  object  $data                   exemplary data, used if you actualy has exemplaryData, to avoid more selects in database
     * @return boolean
     */
    public function updateLow($itemNumber, $lowDate = NULL, $observation = NULL, $keepBeforeObservation = false, $data = null)
    {
        if ( !$data )
        {
            $data = $this->getExemplaryControl($itemNumber);
        }

        $this->clear();
        $this->setTables($this->table);

        if ( $observation )
        {
            //Acrescentar observação ao final
            if ( $keepBeforeObservation )
            {
                $observation = $data->observation .= $observation;
            }
            $this->setColumns('lowDate, observation');
            $info = array( $lowDate, $observation, $itemNumber );
        }
        else
        {
            $this->setColumns('lowDate');
            $info = array( $lowDate, $itemNumber );
        }
        $this->setWhere('itemNumber = ?');
        $sql = $this->update($info);
        $rs = $this->execute($sql);

        if ( $rs ) //Se alterou o estado atualiza tambï¿½m na gtcMaterial
        {
            $rs = $this->updateMaterial($itemNumber, $lowDate, MARC_EXEMPLARY_FIELD, MARC_EXEMPLARY_LOW_DATE_SUBFIELD, $data);

            if ( $rs && $observation )
            {
                $rs = $this->updateMaterial($itemNumber, $observation, MARC_EXEMPLARY_FIELD, MARC_EXEMPLARY_OBSERVATION_SUBFIELD, $data);
            }
        }

        return $rs;
    }

    /**
     * Return the material gender of passed itemNumber
     *
     * @param $itemNumber the item number to verify the material gender
     * @return the material gender of passed itemNumber
     */
    public function getMaterialGender($itemNumber)
    {
        $exemplary = $this->getExemplaryControl($itemNumber);

        if ( MATERIAL_GENDER_CONTROL == 1 )
        {
            $materialControl = $this->busMaterialControl->getMaterialControl($exemplary->controlNumber);
            return $materialControl->materialGenderId;
        }
        else
        {
            return $exemplary->materialGenderId;
        }
    }

    /**
     * Return the material type of passed itemNumber
     *
     * @param $itemNumber the item number to verify the material gender
     * @return the material gender of passed itemNumber
     */
    public function getMaterialType($itemNumber)
    {
        $exemplary = $this->getExemplaryControl($itemNumber);

        if ( MATERIAL_TYPE_CONTROL == 1 )
        {
            $materialControl = $this->busMaterialControl->getMaterialControl($exemplary->controlNumber);
            return $materialControl->materialTypeId;
        }
        else
        {
            return $exemplary->materialTypeId;
        }
    }

    /**
     * Return the controlNumber of an Exemplary
     *
     * @param string the itemNumber of exemplary
     * @return integer the control number of exemplary
     */
    function getControlNumber($itemNumber)
    {
        if ( !$itemNumber )
        {
            return false;
        }
        $r = $this->getExemplaryControl($itemNumber);
        return (isset($r->controlNumber) ? $r->controlNumber : false);
    }

    function clean()
    {
        $this->controlNumber =
                $this->itemNumber =
                $this->libraryUnitId =
                $this->acquisitionType =
                $this->exemplaryStatusId =
                $this->materialGenderId =
                $this->entranceDate =
                $this->lowDate =
                $this->line =
                $this->originalLibraryUnitId = null;
    }

    private function updateMaterial($itemNumber, $content, $fieldId, $subfieldId, $exemplaryControl = null)
    {
        $ok = FALSE;
        
        if ( !$exemplaryControl )
        {
            $exemplaryControl = $this->getExemplaryControl($itemNumber);
        }

        if ( $exemplaryControl )
        {
            $exemplaryTag = $this->busMaterial->getContent($exemplaryControl->controlNumber, $fieldId, $subfieldId, $exemplaryControl->line, false, true);

            $this->busMaterial->content = $content;
            $this->busMaterial->controlNumber = $exemplaryControl->controlNumber;
            $this->busMaterial->fieldid = $fieldId;
            $this->busMaterial->subfieldid = $subfieldId;
            $this->busMaterial->line = $exemplaryControl->line;
            
            if ( $exemplaryTag ) //se existe o registro exclui ou altera
            {
                if ( !$content ) //se nï¿½o tive conteudo execlui o registro
                {
                    $ok =  $this->busMaterial->deleteMaterial();
                }
                else
                {
                    $ok =  $this->busMaterial->updateMaterialContent();
                }
            }
            else if ( $content )
            {
                $ok =  $this->busMaterial->insertMaterial();
            }
            
            // Caso tenha executado corretamente a operação com material, atualiza a tabela de pesquisa.
            if ( $ok )
            {
                $this->busUpdateSearch->updateSearchForMaterial($exemplaryControl->controlNumber);
            }
        }

        return $ok;
    }

    public function checkAccessExemplary($itemNumber)
    {
        $this->itemNumberS = $itemNumber;
        $search = $this->searchExemplaryControl(TRUE);
        if ( !(count($search) > 0) )
        {
            return TRUE;
        }

        $operator = GOperator::getOperatorId();
        $libs = $this->busOperatorLibraryUnit->getOperatorLibraryUnit($operator);
        if ( ($libs->operator) && (!isset($libs->operatorLibrary[0]->libraryUnitId)) )
        {
            return TRUE;
        }
        if ( $libs->operatorLibrary )
        {
            $_libs = array( );
            if ( $search )
            {
                foreach ( $search as $v )
                {
                    $_libs[] = $v->libraryUnitId;
                }
            }
            foreach ( $libs->operatorLibrary as $v )
            {
                if ( in_array($v->libraryUnitId, $_libs) )
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Verifica se um exemplar ï¿½ valido, ou se pertence a uma determinada biblioteca
     *
     * @param int $itemNumber
     * @param int $libraryUnitId
     */
    public function checkExemplaryExists($itemNumber, $libraryUnitId = null)
    {
        parent::clear();
        parent::setColumns("1");
        parent::setTables($this->table);

        $this->setWhere('itemNumber = ?');
        $data[] = $itemNumber;

        if ( $libraryUnitId )
        {
            $this->setWhere('libraryUnitId = ?');
            $data[] = $libraryUnitId;
        }

        $sql = parent::select($data);
        $rs = parent::query($sql, true);
        return isset($rs[0]) ? true : false;
    }

    /**
     * Retorna o ID do status atual de um determinando exemplar
     *
     * @param int $itemNumber
     * @return int
     */
    public function getExemplaryStatus($itemNumber)
    {
        parent::clear();
        parent::setColumns("exemplaryStatusId");
        parent::setTables($this->table);
        parent::setWhere('itemNumber = ?');
        $sql = parent::select(array( $itemNumber ));
        $rs = parent::query($sql);
        return isset($rs[0]) ? $rs[0][0] : false;
    }

    /**
     * Retorna as unidades que possuem um determinado exemplar.
     *
     * @param unknown_type $controlNumbers
     * @return unknown
     */
    public function getLibrariesOfMaterial($controlNumbers, $libraryUnits = null)
    {
        $controlNumbers = is_array($controlNumbers) ? $controlNumbers : array( $controlNumbers );

        parent::clear();
        parent::setColumns("A.libraryUnitId, B.libraryName");
        parent::setTables(" $this->table A INNER JOIN gtcLibraryUnit B USING (libraryUnitId) ");
        parent::setWhere('controlNumber IN (?)');

        if ( !is_null($libraryUnits) )
        {
            $libraryUnits = is_array($libraryUnits) ? $libraryUnits : array( $libraryUnits );
            $libraryUnits = implode(",", $libraryUnits);
            parent::setWhere("libraryUnitId IN ($libraryUnits)");
        }

        $sql = parent::select(array( implode(",", $controlNumbers) ));
        return parent::query($sql, true);
    }

    /**
     * Obtém exemplares dos números de controle
     * @param (array) $controlNumbers
     * @return array de objetos 
     */
    public function getExemplarysOfMaterialControlNumber($controlNumbers)
    {
        $groupExemplarys = array( );

        if ( is_array($controlNumbers) )
        {
            $arrayNumbers = array( );
            foreach ( $controlNumbers as $numbers )
            {
                $arrayNumbers[] = $numbers[0];
            }
            $arrayNumbers = implode(',', $arrayNumbers);

            $this->clear();
            $this->setTables($this->table);
            $this->setColumns($this->fullColumns);
            $this->setWhere("controlNumber IN ({$arrayNumbers})");
            $sql = $this->select();

            $result = $this->query($sql, true);

            if ( is_array($result) )
            {
                foreach ( $result as $i => $values )
                {
                    $groupExemplarys[$values->controlNumber][] = $values;
                }
            }
        }

        return $groupExemplarys;
    }

    /**
     * A partir do numero de controle e unidade obtem os exemplares que pertencem ao mesmo material
     * em um array separado pelo tipo,tipo fisico,tomo e volume.
     * 
     * Esta regra de agrupacao esta sendo utilizada na pesquisa ao congelar um material, e nao
     * esta chamando esta funcao ainda.
     * 
     * @param integer $controlNumber
     * @param integer $libraryUnitId
     * @return array
     */
    public function getExemplaryByTomeTypeVolume($controlNumber = null, $libraryUnitId = null)
    {
        $MIOLO = $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('/forms/FrmSimpleSearch.class.php', 'gnuteca3');

        if ( is_null($controlNumber) )
        {
            $controlNumber = $this->controlNumber;
        }

        if ( is_null($libraryUnitId) )
        {
            $libraryUnitId = $this->libraryUnitId;
        }

        //o terceiro paramatro força tentar pegar os exemplares do pai
        $exemplarys = $this->getExemplaryOfMaterialByLibrary($controlNumber, null, true);

        //verifica se o estado do exemplar não esta na lista de ignorados
        $exemplarys = FrmSimpleSearch::checkExemplarysInclude($exemplarys);
        $exemplaryByStatusId = $exemplarys[$libraryUnitId];
        unset($exemplarys);

        //Prepara exemplares para serem desmembrados em tipo/tipo fisico/tomo/volume
        foreach ( $exemplaryByStatusId as $exemplaryStatusId => $exemplares )
        {
            if ( is_array($exemplares) )
            {
                foreach ( $exemplares as $line => $exemplar )
                {
                    $exemplarys[] = $exemplar;
                }
            }
        }

        //Desmembra em tipo/tipo fisico/tomo/volume
        foreach ( $exemplarys as $line => $info )
        {
            $info->volume = strlen($info->volume) ? $info->volume : '-';
            $info->tomo = strlen($info->tomo) ? $info->tomo : '-';

            $filter[$info->materialTypeId][$info->materialPhysicalTypeId][$info->tomo][$info->volume][] = $info;
        }
        
        return $filter;
    }
    
    /**
     * Retorna itemNumber apartir do loanId
     * 
     * @param integer $loanId
     * 
     * @return string $rs
     **/
    public function getItemNumberByLoan ($loanId)
    {
        $this->clear();
        $this->setColumns("itemNumber");
        $this->setTables('gtcLoan');
        $this->setWhere('loanId = ?');
        $sql = $this->select($loanId);
        $rs = $this->query($sql);
        
        return $rs[0][0];
    }

    /**
     * Retorna itemNumber apartir do reserveId
     * 
     * @param integer $reserveId
     * 
     * @return string $rs
     **/
    public function getItemNumberByReserve ($reserveId)
    {
        $this->clear();
        $this->setColumns("itemNumber");
        $this->setTables('gtcReserveComposition');
        $this->setWhere('reserveId = ?');
        $sql = $this->select($reserveId);
        $rs = $this->query($sql);

        return $rs[0][0];
    }

}

?>