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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 02/08/2008
 *
 **/
class BusinessGnuteca3BusExemplaryStatusHistory extends GBusiness
{
    public $cols, $table;

    public $itemNumber;
    public $exemplaryStatusId;
    public $libraryUnitId;
    public $date;
    public $operator;

    public $itemNumberS;
    public $exemplaryStatusIdS;
    public $libraryUnitIdS;
    public $dateS;
    public $beginDateS;
    public $endDateS;
    public $operatorS;
    public $controlNumberS;

    function __construct()
    {
        parent::__construct();

        $this->table  = 'gtcExemplaryStatusHistory';

        $this->cols = 'itemNumber,
                      exemplaryStatusId,
                      libraryUnitId,
                      date,
                      operator';
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertExemplaryStatusHistory()
    {
        if (!$this->operator)
        {
        	$this->operator = GOperator::getOperatorId();
        }

        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        $sql = $this->insert( $this->associateData( $this->cols ) );

        $rs  = $this->execute($sql);
        return $rs;
    }

    public function updateExemplaryStatusHistory()
    {
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchExemplaryStatusHistory()
    {
        $this->clear();

        if ($this->itemNumberS)
        {
            $this->setWhere('A.itemNumber = ?');
            $data[] = $this->itemNumberS;
        }
        
        if ($this->controlNumberS)
        {
            $this->setWhere('D.controlNumber = ?');
            $data[] = $this->controlNumberS;
        }

        if ($this->exemplaryStatusIdS)
        {
            $this->setWhere('A.exemplaryStatusId = ?');
            $data[] = $this->exemplaryStatusIdS;
        }

        if ($this->libraryUnitIdS)
        {
            $this->setWhere('A.libraryUnitId in (' . $this->libraryUnitIdS . ')' );
        }

        if ($this->dateS)
        {
            $this->setWhere('date(A.date) = ?');
            $data[] = $this->dateS;
        }
        if ($this->beginDateS)
        {
            $this->setWhere('date(A.date) >= ?');
            $data[] = $this->beginDateS;
        }
        if ($this->endDateS)
        {
            $this->setWhere('date(A.date) <= ?');
            $data[] = $this->endDateS;
        }

        if ($this->operatorS)
        {
            $this->setWhere('lower(A.operator) LIKE lower(?)');
            $data[] = '%' . $this->operatorS . '%';
        }
        
        $titleTag = explode('.', MARC_TITLE_TAG );
        $authorTag = explode('.', MARC_AUTHOR_TAG);

        $tables  = '    gtcExemplaryStatusHistory   A
            INNER JOIN  gtcExemplaryStatus          B
                    ON  (A.exemplaryStatusId = B.exemplaryStatusId)
            INNER JOIN  gtcLibraryUnit              C
                    ON  (A.libraryUnitId = C.libraryUnitId)
            INNER JOIN  gtcExemplaryControl         D
                    ON  (A.itemNumber = D.itemNumber)';
        $columns = 'D.controlNumber,
                    A.itemNumber,
                     ( SELECT content FROM gtcmaterial WHERE fieldid = \''.$titleTag[0].'\' and subfieldid = \''.$titleTag[1].'\' and controlnumber = d.controlNumber LIMIT 1) AS title,
                     ( SELECT content FROM gtcmaterial WHERE fieldid = \''.$authorTag[0].'\' and subfieldid = \''.$authorTag[1].'\' and controlnumber = d.controlNumber LIMIT 1) AS author,
                    B.description,
                    C.libraryName,
                    A.date,
                    A.operator';

        $this->setTables($tables);
        $this->setColumns($columns);
        $this->setOrderBy('A.date');
        $sql = $this->select($data);

        $rs  = $this->query($sql);
        return $rs;
    }


    public function getLastStatus($itemNumber, $level=NULL, $libraryUnitId=NULL)
    {
        $this->clear();
        $columns = 'A.itemNumber,
                    A.exemplaryStatusId,
                    B.level';

        $tables = '          gtcExemplaryStatusHistory A
                   LEFT JOIN gtcExemplaryStatus B
                          ON (A.exemplaryStatusId = B.exemplaryStatusId)';

        $this->setColumns($columns);
        $this->setTables($tables);

        $this->setWhere('A.itemNumber = ?');
        $args[] = $itemNumber;

        if ($libraryUnitId)
        {
        	$this->setWhere('A.libraryUnitId = ?');
        	$args[] = $libraryUnitId;
        }

        $this->setOrderBy('A.date DESC');
        $sql = $this->select($args);
        $rs  = $this->query($sql, true);

        //Procura o último estado desconsiderando o estado atual
        for ($x=1; $x<sizeof($rs); $x++)
        {
            //Se não for definido nível retorna o primeiro valor
            if (!$level)
            {
                return $rs[$x]->exemplaryStatusId;
            }
            elseif ($level == $rs[$x]->level)
            {
                return $rs[$x]->exemplaryStatusId;
            }
        }
    }

    public function getDateOfLastStatus($itemNumber)
    {
        $this->clear();
        $this->setColumns('MAX(date)');
        $this->setTables('gtcExemplaryStatusHistory');
        $this->setWhere('itemNumber = ?');
        $this->setOrderBy('A.date DESC');
        $sql = $this->select(array($itemNumber));
        $rs  = $this->query($sql);
        if(!$rs)
        {
            return false;
        }

        return $rs[0][0];
    }
    
    public function getDateOfLastReturn($itemNumber)
    {
        $this->clear();
        $this->setColumns('MAX(date)');
        $this->setTables('gtcExemplaryStatusHistory');
        $this->setWhere("itemNumber = ? AND exemplaryStatusId = '" . DEFAULT_EXEMPLARY_STATUS_DISPONIVEL . "';");
        $sql = $this->select(array($itemNumber));
        $rs  = $this->query($sql);
        
        if(!$rs)
        {
            return false;
        }

        return $rs[0][0];
    }
    
    public function getLibraryOfItemNumber($itemNumber)
    {
        $this->clear();
        $this->setColumns('*');
        $this->setTables('gtcExemplaryStatusHistory INNER JOIN gtclibraryunit ON(gtcExemplaryStatusHistory.libraryunitid = gtclibraryunit.libraryunitid)');
        $this->setWhere("itemNumber = ?  LIMIT 1;");
        $sql = $this->select(array($itemNumber));
        $rs  = $this->query($sql);
        
        if(!$rs)
        {
            return false;
        }

        return $rs[0][6];
    }
}
?>