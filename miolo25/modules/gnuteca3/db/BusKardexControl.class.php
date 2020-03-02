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
 * This file handles the connection and actions for gtcKardexControl table
 *
 * @author Luiz Gilberto Gregory [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/12/2008
 *
 **/
class BusinessGnuteca3BusKardexControl extends GBusiness
{
    public $MIOLO;
    public $module;
    public $cols;
    public $pkeys;
    public $pkeysWhere;
    public $fullColumns;
    public  $controlNumber,
            $codigoDeAssinante,
            $libraryUnitId,
            $acquisitionType,
            $vencimentoDaAssinatura,
            $dataDaAssinatura,
            $entranceDate,
            $line;
    public  $numberType,
            $number,
            $subscriberCode,
            $fiscalNote,
            $titleS,
            $expressionS,
            $startDate,
            $endDate;
   public   $businessGenericSearch2,
            $businessMaterial,
            $businessTag,
            $businessMaterialControl,
            $businessSearchableField;

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->table    = 'gtckardexcontrol';
        $this->pkeys    = 'controlNumber,
                           codigoDeAssinante';
        $this->cols     = 'libraryUnitId,
                           acquisitionType,
                           vencimentoDaAssinatura,
                           dataDaAssinatura,
                           entranceDate,
                           line';

        $this->fullColumns = str_replace(array("\n", " "), "", $this->pkeys . ',' . $this->cols);

        $this->businessGenericSearch2 = $this->MIOLO->getBusiness( $this->module, 'BusGenericSearch2');
        $this->businessMaterial = $this->MIOLO->getBusiness( $this->module, 'BusMaterial');
        $this->businessMaterialControl = $this->MIOLO->getBusiness( $this->module, 'BusMaterialControl');
        $this->businessTag = $this->MIOLO->getBusiness( $this->module, 'BusTag');
        $this->businessSearchableField = $this->MIOLO->getBusiness( $this->module, 'BusSearchableField');
        $this->businessMaterialHistory = $this->MIOLO->getBusiness( $this->module, 'BusMaterialHistory');
    }


    /**
     * Return a specific record from the database
     *
     * @param $itemNumber (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getKardexControl()
    {

    }

    public function getKardexOfMaterial($controlNumber, $libraryUnitId = null)
    {

        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->fullColumns);
        $this->setWhere("controlNumber = ?");

        $data[] = $controlNumber;

        if($libraryUnitId)
        {
            $libraryUnitId = is_array($libraryUnitId) ? $libraryUnitId : array($libraryUnitId);
            $libraryUnitId = implode("','", $libraryUnitId);
            if(strlen($libraryUnitId))
            {
                $this->setWhere("libraryUnitId IN ('$libraryUnitId')");
            }
        }

        $sql = $this->select($data);
        return $this->query($sql, true);
    }

    public function getControlNumber($codigoDeAssinante)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns("controlNumber");
        $this->setWhere("codigoDeAssinante = ?");
        $sql =$this->select(array($codigoDeAssinante));
        $r = $this->query();

        if(!$r)
        {
            return false;
        }

        return $r[0][0];
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchKardexControl()
    {
        $this->businessGenericSearch2->clean();
        $this->businessGenericSearch2->addSearchTagField(MARC_KARDEX_SUBSCRIBER_ID_TAG);
        $this->businessGenericSearch2->addSearchTagField(MARC_KARDEX_LIBRARY_UNIT_ID_TAG);
        $this->businessGenericSearch2->addSearchTagField(MARC_TITLE_TAG);

        $addFilter = false;

        if(!$this->number           &&
           !$this->subscriberCode   &&
           !$this->fiscalNote       &&
           !$this->titleS           &&
           !$this->expressionS      &&
           !$this->startDate        &&
           !$this->endDate)
        {
            $this->expressionS = '%';
        }

        if($this->expressionS)
        {
            $exp = $this->businessSearchableField->parseExpression( $this->expressionS );
            $this->businessGenericSearch2->addMaterialWhereByExpression($exp);
        }

        if($this->subscriberCode)
        {
            $addFilter = true;
            $this->businessGenericSearch2->addMaterialWhere(MARC_KARDEX_FIELD, MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD, $this->subscriberCode);
        }

        if($this->fiscalNote)
        {
            $addFilter = true;
            $this->businessGenericSearch2->addMaterialWhere(MARC_KARDEX_FIELD, MARC_KARDEX_FISCAL_NOTE_SUBFIELD, $this->fiscalNote);
        }

        if($this->titleS)
        {
            $addFilter = true;
            $this->businessGenericSearch2->addMaterialWhereByTag(MARC_TITLE_TAG, $this->titleS);
        }

        if($this->startDate && $this->endDate)
        {
            $addFilter = true;
            $startDate  = new GDate($this->startDate);
            $endDate    = new GDate($this->endDate);
            $values     = array($startDate->getDate(GDate::MASK_DATE_DB), $endDate->getDate(GDate::MASK_DATE_DB));
            $this->businessGenericSearch2->addMaterialWhereByTag(MARC_KARDEX_SIGNATURE_END_TAG, $values, 'AND', 'between');
        }

        if( $this->startDate && ! $this->endDate )
        {
            $addFilter = true;
            $this->businessGenericSearch2->addMaterialWhereByTag(MARC_KARDEX_SIGNATURE_END_TAG, GDate::construct($this->startDate)->getDate(GDate::MASK_DATE_DB), 'AND', '>=');
        }

        if( ! $this->startDate && $this->endDate )
        {
            $addFilter = true;
            $this->businessGenericSearch2->addMaterialWhereByTag(MARC_KARDEX_SIGNATURE_END_TAG, GDate::construct($this->startDate)->getDate(GDate::MASK_DATE_DB), 'AND', '<=');
        }

        list($sp, $le) = explode("-", SPREADSHEET_CATEGORY_COLECTION);
        $this->businessGenericSearch2->addMaterialControlWhere("category",  $sp);
        $this->businessGenericSearch2->addMaterialControlWhere("level",     $le);

        //Adiciona condição quando é informado o número de controle ou o número do tombo
        if(!empty($this->number))
        {
            switch ($this->numberType)
            {
                case "cn" :
                    $cn = $this->number;
                break;
                case "sc" :
                    $cn = $this->getControlNumber($this->number);
                   
                    break;
                case "wn" :
                    $cn = $this->businessMaterial->getControlNumberByWorkNumber($this->number);
                   
                break;
            }

            $this->businessGenericSearch2->addMaterialControlWhere('controlnumber', $cn);

        }
        else
        {
           $this->businessGenericSearch2->addPrefixSuffixInResult();
        }

        $data = $this->businessGenericSearch2->getWorkSearch(9999);
        
        if(!$data)
        {
            return false;
        }

        // TRABALHA A EXIBIÇÂO DOS DADOS NA GRID
        foreach ($data as $l => $values)
        {
            $gridData[$l][0] = $values['CONTROLNUMBER'];

            $div1 =  new MDiv(null, $this->businessTag->getTagNameByTag(MARC_TITLE_TAG)                     .":".    $values[MARC_TITLE_TAG][0]->content);
            $div2 =  new MDiv(null, $this->businessTag->getTagNameByTag(MARC_KARDEX_SUBSCRIBER_ID_TAG)      .":".    $values[MARC_KARDEX_SUBSCRIBER_ID_TAG][0]->content);
            $div3 =  new MDiv(null, $this->businessTag->getTagNameByTag(MARC_KARDEX_LIBRARY_UNIT_ID_TAG)    .":".    $values[MARC_KARDEX_LIBRARY_UNIT_ID_TAG][0]->content);
            $gridData[$l][1] = $div1->generate() . $div2->generate() . $div3->generate();
        }

        return $gridData;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertKardexControl()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->insert( $this->associateData($this->fullColumns) );

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
    public function updateKardexControl()
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $this->setWhere('controlNumber = ? AND codigoDeAssinante = ?');
        $sql = $this->update( $this->associateData($this->cols . ',' . $this->pkeys) );
        $rs  = $this->execute($sql);
        return  $rs;
    }


    /**
     * Delete a record
     *
     * @param $itemNumber (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteKardexControl($controlNumber, $codigoDeAssinante, $line)
    {
        $this->clear();
        $this->setTables($this->table);
        
        $where = 'controlNumber = ? AND line = ? ';
        $filter[] = $controlNumber;
        $filter[] = $line;
        
        if ( $codigoDeAssinante )
        {
            $where .= ' AND codigoDeAssinante = ? ';
            $filter[] = $codigoDeAssinante;
        }
        else 
        {
            $where .= ' AND codigoDeAssinante IS NULL ';
        }
        
        $this->setWhere($where);
        $sql = $this->delete( $filter );

        $rs = $this->execute($sql);
        return $rs;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $controlNumber
     * @return unknown
     */
    public function deleteAllKardexByMaterialControl($controlNumber)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere('controlNumber = ?');
        $this->businessMaterialHistory->insertMaterialHistoryForDeleteMaterial($controlNumber); //registra exclusão em histórico
        $sql = $this->delete( array($controlNumber) );
        $ok[] = $this->execute($sql);

        $this->businessMaterial->controlNumber = $controlNumber;
        $ok[] = $this->businessMaterial->deleteMaterial();
        $ok[] = $this->businessMaterialControl->deleteMaterialControl($controlNumber);

        return array_search(false, $ok) == false;
    }


    /**
     * Enter description here...
     *
     */
    public function clean()
    {
        $this->this->controlNumber      =
        $this->codigoDeAssinante        =
        $this->libraryUnitId            =
        $this->acquisitionType          =
        $this->vencimentoDaAssinatura   =
        $this->dataDaAssinatura         =
        $this->entranceDate             =
        $this->line                     = null;
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $controlNumberFather
     * @param unknown_type $libraryUnitId
     * @return unknown
     */
    function getReferenced($controlNumberFather)
    {
        $sql = "SELECT A.controlNumber as cn
                  FROM gtcmaterialcontrol A
                 WHERE A.controlnumberfather    = '$controlNumberFather'";

        return $this->query($sql);
    }



}
?>