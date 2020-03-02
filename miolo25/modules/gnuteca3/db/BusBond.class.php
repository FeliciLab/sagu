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
 * This file handles the connection and actions for basPersonLink table
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
 * Class created on 01/08/2008
 *
 **/

/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusBond extends GBusiness
{
    public $removeData;
    public $insertData;
    public $updateData;
    
    public $personId;
    public $linkId;
    public $dateValidate;
    public $oldDateValidate;
    public $oldLinkId;
    
    public $personIdS;
    public $linkIdS;
    public $dateValidateS;
    public $beginDateValidateS;
    public $endDateValidateS;
    
    public $byActive; //show only active bonds
    public $allActive; //Filtro que mostra todos ativos no GET
    public $activeLink; //Mostra links ativos

    
    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listBond($selection = false)
    {
        $this->clear();
        
        $columns = 'A.linkid,
                    A.description';
        $tables = 'baslink A';
        
        $data = array();
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setOrderBy('A.description');
        $sql = $this->select($data);
        $rs = $this->query($sql);
        
        if ( ($selection) && (is_array($rs)) )
        {
            foreach ( $rs as $i=> $value )
            {
                $rs[$i][1] = $value[1] ." [{$value[0]}]";
            }
        }
        
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
    public function getBond($personId, $linkId, $dateValidate=null)
    {
        $this->clear();
        
        $columns = 'A.personId,
                    A.linkId,
                    A.dateValidate';
        $tables = 'baspersonlink A';
        
        $where = '    A.personId = ?
                    AND A.linkId = ?';
        $data = array($personId, $linkId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);

        if ( $dateValidate )
        {
            $this->setWhere('(A.dateValidate = ? OR A.dateValidate IS NULL)');
            $data[] = $dateValidate;
        }

        $sql = $this->select($data);
        $rs = $this->query($sql, TRUE);

        if ( $rs )
        {
            $this->setData($rs[0]);
            return $rs[0];
        }
        else
        {
            return false;
        }
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchBond($toObject = FALSE, $orderBy = NULL)
    {
        $this->clear();

        $columns = 'A.personId,
                    B.name,
                    A.linkId,
                    C.linkId,
                    C.description AS linkIdName,
                    A.dateValidate,
                    C.description AS description,
                    A.dateValidate as oldDateValidate,
                    B.email';
        $tables = ' baspersonlink   A
   INNER JOIN ONLY  basPerson       B
                ON  (A.personId = B.personId)
        INNER JOIN  basLink         C
                ON  (A.linkId = C.linkId)';
        
        //mostra somente ativo
        if ( $this->byActive )
        {
            $orderBy = 'C.level asc limit 1';
            $this->setWhere(' (A.datevalidate >= now()::date OR A.datevalidate IS NULL)');
        }

        if ( $this->allActive )
        {
            $orderBy = 'C.level asc';
            $this->setWhere('(A.datevalidate >= now()::date OR A.datevalidate IS NULL)');
        }
        
        //ordenação padrão
        if ( !$orderBy )
        {
            $orderBy = 'A.dateValidate';
        }
        
        if ( !empty($this->personIdS) )
        {
            $this->setWhere('A.personId = ?');
            $data[] = $this->personIdS;
        }
        if ( !empty($this->linkIdS) )
        {
            if ( !is_array($this->linkIdS))
            {
                $this->linkIdS = array($this->linkIdS);
            }

            $this->setWhere('A.linkId in ('.implode(',',$this->linkIdS).')');
        }
        if ( !empty($this->dateValidateS) )
        {
            $this->setWhere('date(A.dateValidate) = ?');
            $data[] = $this->dateValidateS;
        }
        if ( !empty($this->beginDateValidateS) )
        {
            $this->setWhere('date(A.dateValidate) >= ?');
            $data[] = $this->beginDateValidateS;
        }
        if ( !empty($this->endDateValidateS) )
        {
            $this->setWhere('date(A.dateValidate) <= ?');
            $data[] = $this->endDateValidateS;
        }
        
        //Se foi selecionado true ou false
        if ( strlen(trim($this->activeLink)) != 0 )
        {
            //Todos que são ativos tem vinculo que vence hoje ou além ou data de validade nula (permamente)
            $active = "A.datevalidate >= NOW() OR (A.dateValidate IS NULL AND A.personId = ?)";
            $data[] = $this->personIdS;
            
            if ( MUtil::getBooleanValue($this->activeLink) == TRUE )
            {
                $this->setWhere($active);
            }
            else
            {
                //Se for para listar os não ativos, nega o $active
                $this->setWhere("NOT (". $active . ")");
            }
        }
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        $rs = $this->query($sql, $toObject ? TRUE : FALSE);

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
    public function insertBond()
    {
        $data = array($this->personId, $this->linkId,$this->dateValidate);
        $this->clear();
        
        $columns = 'personId,
                    linkId,
                    dateValidate';
        $tables = 'baspersonlink';
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $sql = $this->insert($data);
        $rs = $this->execute($sql);
        
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
    public function updateBond()
    {
        if ( !$this->linkId || !$this->personId )
        {
            return false;
        }
        
        if ( $this->removeData )
        {
            return $this->deleteBond($this->personId, $this->linkId, $this->oldDateValidate);
        }
        elseif ( $this->insertData )
        {
            return $this->insertBond();
        }
        else
        {
            $this->clear();

            $date = new GDate($this->dateValidate);
            $data[] = $date->getDate(GDate::MASK_DATE_DB);
            $data[] = $this->linkId;
            $data[] = $this->personId;
            $data[] = $this->oldLinkId ? $this->oldLinkId : $this->linkId;
            
            $this->setColumns('dateValidate,linkId');
            $this->setTables('baspersonlink');
            $this->setWhere('
                            personId = ?
                            AND linkId = ?
                            ');

            if ( $this->oldDateValidate )
            {
                $this->setWhere('dateValidate = ?');
                $data[] = $this->oldDateValidate;
            }
            else
            {
                //Se não tiver data de validade, altera a que não estiver definida.
                $this->setWhere('dateValidate IS NULL');
            }

            $sql = $this->update($data);
            $rs = $this->execute($sql);
            
            return $rs;
        }
     
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
    public function deleteBond($personId = null, $linkId = null, $oldDateValidate = null)
    {
        $this->clear();

        $tables = 'baspersonlink';
        
        if ( !is_null($personId) )
        {
            $this->setWhere("personId = ?");
            $data[] = $personId;
        }
        if ( !is_null($linkId) )
        {
            $this->setWhere("linkId = ?");
            $data[] = $linkId;
        }

        if ( !empty($oldDateValidate) )
        {
            $this->setWhere("datevalidate = ?");
            $data[] = $oldDateValidate;
        }
        else
        {
            $this->setWhere("datevalidate IS NULL");
        }
        
        $this->setColumns($columns);
        $this->setTables($tables);
        
        $sql = $this->delete($data);
        
        $rs = $this->execute($sql);
        
        return $rs;
    }

    /**
     * Return the Active Link (Bond) of this Person id
     *
     * @param personId the code of person
     */
    public function getPersonLink($personId)
    {
        $this->clear();
        
        $this->byActive = true;
        $this->personIdS = $personId;
        $rs = $this->searchBond(true);
        
        return $rs[0]; //retorna só o primeiro, o ativo
    }

    /**
     * Return All Active Link (Bond) of this Person id
     *
     * @param personId the code of person
     */
    public function getAllPersonLink($personId)
    {
        $this->clear();
        $columns = 'a.linkId as linkId,
                       a.description,
                       B.dateValidate';
        $tables = 'basLink A
                       INNER JOIN basPersonLink B on (A.linkId = B.linkId)';
        $where = '    (B.datevalidate >= now()::date OR B.datevalidate IS NULL)
                       and B.personId = ?';
        $orderBy = 'A.level asc';
        $data = array($personId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        
        $rs = $this->query($sql, TRUE);
        
        return $rs;
    }

    /**
     * Retorna os grupos e validade dos grupos de determinadas pessoas
     *
     * @param integer or simple array $personId
     * @return  object
     */
    public function getLinksByPersonId($personId)
    {
        $this->clear();
        
        $columns = 'linkId,
                       personId,
                       dateValidate';
        
        $tables = 'basPersonLink';
        
        $personId = is_array($personId) ? $personId : array($personId);
        $personId = implode("', '", $personId);
        $where = "personId IN ('{$personId}')";
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $this->setOrderBy($orderBy);
        $sql = $this->select();
        
        $rs = $this->query($sql, TRUE);
        
        if ( !$rs )
        {
            return false;
        }
        
        $result = array();
        foreach ( $rs as $content )
        {
            $result[$content->personId][$content->linkId]->personId = $content->personId;
            $result[$content->personId][$content->linkId]->linkId = $content->linkId;
            $result[$content->personId][$content->linkId]->dateValidate = $content->dateValidate;
        }
        
        return $result;
    }
    
    /**
     * Obtém vínculo ativo do usuário e de maior nível.
     * 
     * @param int $personId Código do usuário.
     * @return stdClass Vínculo ativo de maior prioridade.
     */
    public function getActivePersonLink($personId)
    {
        if ( !$personId )
        {
            return NULL;
        }
        
        $sql = new MSQL();
        $sql->setColumns('P.personId, 
                          P.name, 
                          min( L.level),
                          L.linkid as activelink,
                          login');
        $sql->setTables('  ONLY basPerson P 
                     INNER JOIN basPersonLink PL 
                             ON P.personId = PL.personId 
                     INNER JOIN basLink L 
                             ON L.linkId = PL.linkId 
                            AND (PL.dateValidate >= now()::date OR PL.dateValidate IS NULL)');
        $sql->setGroupBy('1,2,4,5');
        $sql->setOrderBy('1');
        
        $sqlInterno = $sql->select();
        
        $this->clear();
        $this->setColumns('name,
                           activelink, 
                           L.description,
                           level');
        $this->setTables('(' . $sqlInterno . ') as temp
               LEFT JOIN basLink L 
                      ON activelink = L.linkId');
        $this->setWhere('personid = ?');
        
        $this->setOrderBy('level LIMIT 1');
        $sql = $this->select(array($personId));
        $result = $this->query($sql, TRUE);
        
        $personLink = NULL;
        
        if ( is_array($result) )
        {
            $personLink = $result[0];
        }
        
        return $personLink;
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
    public function deletePersonLink($personId)
    {
        $this->clear();
        
        $personId = is_array($personId) ? $personId : array($personId);
        $personId = implode("', '", $personId);
        
        $tables = 'basPersonLink';
        $where = "personId in ('{$personId}')";
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete();
        $rs = $this->execute($sql);
        
        return $rs;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $data
     */
    public function setData($data)
    {
        $this->removeData = NULL;
        $this->insertData = NULL;
        $this->updateData = NULL;
        $this->personId = NULL;
        $this->linkId = NULL;
        $this->dateValidate = NULL;
        $this->oldDateValidate = NULL;
        $this->oldLinkId = NULL;
        
        parent::setData($data);
    }
}
?>
