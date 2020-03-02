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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/

/**
 * Class to manipulate
 **/

class BusinessGnuteca3BusMarcTagListing extends GBusiness
{
    /**
     * Attributes
     */

    public  $MIOLO;
    public  $marcTagListingId;
    public  $description_;
    public  $marc_options;
    public  $marcTagListingIdS,
            $descriptionS,
            $leaderTags,
            $indicador;
    public  $businessMarcTagListingOption;

    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->businessMarcTagListingOption = $this->MIOLO->getBusiness('gnuteca3', 'BusMarcTagListingOption');
        
        $this->setData(null);
        $this->setColumns();
        $this->setTables();
    }

    /**
     * Seta as tabelas
     *
     * @param (String || Array) $tables
     */

    public function setTables($tables = 1)
    {
        switch($tables)
        {
            case 1 :
                $table = "gtcmarctaglisting";
                break;

            case 2 :
                $table = "gtcmarctaglistingoption";
                break;
        }

        parent::setTables($table);
    }

    /**
     * Este método seta as colunas da tabela.
     *
     * @param (String || Array) $columns
     */

    public function setColumns($type = "All")
    {
        switch($type)
        {
            case "All" :
                $columns = array
                (
                    'marcTagListingId',
                    'description',
                );
                break;

            case "select" :
                $columns = array
                (
                    'description',
                );
                break;

            case "options":
                $columns = array
                (
                    'marcTagListingId',
                    'option',
                    'description',
                );
                break;

            case "selectOption":
                $columns = array
                (
                    'option',
                    'description',
                );
                break;
        }

        parent::setColumns($columns);
    }


    /**
     * Seta as condições do sql
     *
     * @return void
     */
    public function getWhereCondition()
    {
        $where = "";

        if(!empty($this->marcTagListingId))
        {
            $where.= " marcTagListingId = ? AND ";
        }

        if(!empty($this->marcTagListingIdS))
        {
            $where.= " lower(marcTagListingId) LIKE lower(?) AND ";
        }

        if(!empty($this->descriptionS))
        {
            $where.= " lower(description) LIKE lower(?) AND ";
        }

        if($this->leaderTags)
        {
            $where.= " lower(marcTagListingId) LIKE lower('000-%') AND ";
        }

        if(!empty($this->indicador))
        {
            $where.= " marcTagListingId LIKE ('{$this->indicador}-I%') AND ";
        }

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where) - 4);
            parent::setWhere($where);
        }
    }


    /**
     * Trabalha o Data Object retornado do form
     *
     * transforma em um array para enviar para o where condition do sql
     *
     * @return (Array) $args
     */
    private function getDataConditionArray()
    {
        $args = array();

        if(!empty($this->marcTagListingId))
        {
            $args[] = $this->marcTagListingId;
        }
        if(!empty($this->marcTagListingIdS))
        {
            $this->marcTagListingIdS = trim($this->marcTagListingIdS);
            $this->marcTagListingIdS = str_replace(" ", "%", $this->marcTagListingIdS);
            $args[] = "%{$this->marcTagListingIdS}%";
        }
        if(!empty($this->descriptionS))
        {
            $this->descriptionS = trim($this->descriptionS);
            $this->descriptionS = str_replace(" ", "%", $this->descriptionS);
            $args[] = "%{$this->descriptionS}%";
        }

        return $args;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */
    public function searchMarcTagListing()
    {
        parent::clear();
        $this->setTables();
        $this->setColumns();
        $this->getWhereCondition();
        parent::setOrderBy("marcTagListingId");
        $sql = parent::select($this->getDataConditionArray());
        return parent::query($sql);
    }


    /**
     * Insert a new record
     *
     * @return True if succed, otherwise False
     */
    public function insertMarcTagListing()
    {
        parent::clear();

        $this->setTables();
        $this->setColumns();

        $data = array
        (
            $this->marcTagListingId,
            $this->description_,
        );

        $sql = parent::insert($data);

        $ok = parent::Execute();

        if(!$ok)
        {
            return false;
        }

        return $this->insertMarcTagListingOptions();
    }


    /**
     * insere as opçoes de listagem do marc
     *
     * @return (boolean)
     */
    private function insertMarcTagListingOptions()
    {
        return $this->businessMarcTagListingOption->insertMarcTagListingOptions($this->marcTagListingId, $this->marc_options);
    }


    /**
     * Atualiza um determinado registro
     *
     * @return True if succed, otherwise False
     */
    public function updateMarcTagListing()
    {
        parent::clear();

        $this->marcTagListingId = $this->marcTagListingId;
        $this->getWhereCondition();

        $this->setTables();
        $this->setColumns("select");

        $data = array
        (
            $this->description_,
            $this->marcTagListingId
        );

        $sql = parent::update($data);

        $ok = parent::Execute();

        if(!$ok)
        {
            return false;
        }

        $this->removeMarcTagListOptions($this->marcTagListingId);

        return $this->insertMarcTagListingOptions();
    }


    /**
     * Remove as opçoes da marc tag listing
     *
     * @param varChar $marcTagListingId
     * @return boolean
     */
    public function removeMarcTagListOptions($marcTagListingId)
    {
       return $this->businessMarcTagListingOption->removeMarcTagListOptions($marcTagListingId);
    }


    /**
     * retorna um determinado registro
     *
     * @param (int) $marcTagListingId - Id do registro
     * @return (Array)
     */
    public function getMarcTagListing($marcTagListingId)
    {
        parent::clear();

        $this->setTables();
        $this->setColumns("All");

        $this->marcTagListingId = $marcTagListingId;

        $this->getWhereCondition();
        parent::select($this->getDataConditionArray());

        $result = parent::query();

        if(!$result)
        {
            return false;
        }

        list
        (
            $this->marcTagListingId,
            $this->description_

        ) = $result[0];

        return $result[0];
    }


    /**
     * retorna as opçoes de um determinado marc tag
     *
     * @param (int) $marcTagListingId - Id do registro
     * @return (Array)
     */
    public function getMarcTagListingOptions($marcTagListingId)
    {
        $this->marc_options = $this->businessMarcTagListingOption->getMarcTagListingOptions($marcTagListingId);
        return $this->marc_options;
    }


    /**
     * Delete a record
     *
     * @param $marcTagListingId (int): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */
    public function deleteMarcTagListing($marcTagListingId)
    {
        $ok = $this->removeMarcTagListOptions($marcTagListingId);

        if(!$ok)
        {
            return false;
        }

        parent::clear();

        $this->setTables();

        $this->marcTagListingId = $marcTagListingId;
        $this->getWhereCondition();

        parent::delete(array($marcTagListingId));

        return parent::Execute();
    }

    /**
     * seta na classe os valores das opçoes
     *
     * @param (Array de Objectos) $op
     */
    public function setMarcOptions($op)
    {
        $this->marc_options = $op;
    }


    /**
     * Retorna os campos do Leader
     */
    public function getMarcLeaderTags($marcTagListingId)
    {
        if(!$marcTagListingId)
        {
            return false;
        }

        $this->leaderTags = true;
        $this->marcTagListingId = $marcTagListingId;
        $r = $this->searchMarcTagListing();

        $this->leaderTags = false;

        if(!$r)
        {
            return false;
        }

        $marcLeaderTags->name    = $r[0][1];
        $marcLeaderTags->options = $this->businessMarcTagListingOption->getMarcTagListingOptions($r[0][0]);

        return $marcLeaderTags;
    }


    /**
     *
     */
    public function getTagOptions($marcTagListingId)
    {
        $this->marcTagListingId = $marcTagListingId;
        $r = $this->searchMarcTagListing();

        if(!$r)
        {
            return false;
        }

        $marcLeaderTags->name    = $r[0][1];
        $marcLeaderTags->options = $this->businessMarcTagListingOption->getMarcTagListingOptions($r[0][0]);

        return $marcLeaderTags;
    }

    public function getTagsOptions($filter)
    {

        $sql = "SELECT A.marctaglistingid,
                       A.description,
                       B.option,
                       B.description
                  FROM gtcMarcTagListing A
            INNER JOIN gtcmarctaglistingoption B
                    ON (A.marctaglistingid = B.marctaglistingid)
                 WHERE A.marctaglistingid LIKE '$filter'
              ORDER BY marctaglistingid, B.option";

        $rs  = $this->query($sql);

        if ($rs)
        {
            $lastTag = null;
            foreach ($rs as $r)
            {
                $ind[$r[0]]->name = $r[1];

                $options = new stdClass();
                $options->option      = $r[2];
                $options->description = $r[3];
                $ind[$r[0]]->options[] = $options;
            }
        }

        return $ind;
    }

    /**
     *
     */
    public function getIndicadores($etiqueta)
    {
        $this->indicador        = $etiqueta;
        $this->marcTagListingId = null;

        $r = $this->searchMarcTagListing(1);
        $this->indicador    = false;

        if(!$r)
        {
            return false;
        }

        foreach($r as $i => $v)
        {
            $ind[$v[0]]->name    = $v[1];
            $ind[$v[0]]->options = $this->businessMarcTagListingOption->getMarcTagListingOptions($v[0]);
        }

        return $ind;
    }

    public function getAllIndicators()
    {

        $sql = "SELECT A.marctaglistingid,
                       A.description,
                       B.option,
                       B.description
                  FROM gtcMarcTagListing A
            INNER JOIN gtcmarctaglistingoption B
                    ON (A.marctaglistingid = B.marctaglistingid)
                 WHERE A.marctaglistingid LIKE '%-I1' OR A.marctaglistingid LIKE '%-I2'
              ORDER BY marctaglistingid, B.option";

        $rs  = $this->query($sql);

        if ($rs)
        {
            $lastTag = null;
            foreach ($rs as $r)
            {
                $ind[$r[0]]->name = $r[1];

                $options = new stdClass();
                $options->option      = $r[2];
                $options->description = $r[3];
                $ind[$r[0]]->options[] = $options;
            }
        }

        return $ind;
    }
    
    /**
     * Método para obter campos que possuem indicadores.
     * 
     * @return array Vetor com campos que possuem indicadores.
     */
    public function getFieldsWithIndicators()
    {
        $this->clear();
        
        parent::setColumns("distinct(split_part(split_part(marctaglistingid, '-', 1), '.', 1))");
        parent::setTables('gtcMarcTagListing');
        parent::setWhere("marctaglistingid LIKE '%-I1' OR marctaglistingid LIKE '%-I2'");
        
        $sql = parent::select();

        $result  = $this->query($sql);
        
        $return = array();
        
        if ( $result )
        {
            foreach ( $result as $value )
            {
                $return[] = $value[0];
            }
        }
        
        return $return;
    }
}
?>
