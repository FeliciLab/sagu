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
 * News business
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 06/01/2009
 *
 **/
class BusinessGnuteca3BusNews extends GBusiness
{
    const PLACE_TYPE_MY_LIBRARY = 1;
    const PLACE_TYPE_INITIAL_SCREEN = 2;
    const PLACE_TYPE_SEARCH = 3;

    public $newsId;
	public $place;
    public $title;
    public $title1;
	public $news;
	public $date;
    public $beginDate;
	public $endDate;
    public $operator;
	public $isRestricted;
	public $isActive;
    public $busNewsAccess;
    public $busAuthenticate;
    public $busLibraryGroup;
    public $busBond;
    public $group;
    public $libraryUnitId;
    public $librayUnitNull = false;

    public $newsIdS;
	public $placeS;
    public $titleS;
    public $title1S;
	public $newsS;
    public $beginDateS;
	public $endDateS;
    public $beginBeginDateS;
	public $endBeginDateS;
    public $beginEndDateS;
	public $endEndDateS;
    public $operatorS;
	public $isRestrictedS;
	public $isActiveS;
    public $checkActiveDates;
    public $libraryUnitIdS;
    /**
     * Se é para listar noticias para o usuário somente
     * @var boolean */
    public $listforUser;


    public function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcNews';
        $this->colsNoId = 'place,
                           title1,
                           news,
                           date,
                           beginDate,
                           endDate,
                           operator,
                           isRestricted,
                           isActive,
                           libraryUnitId';
        $this->columns  = 'newsId, ' . $this->colsNoId;
        $this->busNewsAccess   = $this->MIOLO->getBusiness($this->module, 'BusNewsAccess');
        $this->busAuthenticate = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busBond         = $this->MIOLO->getBusiness($this->module, 'BusBond');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listNews($object=FALSE)
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql, $object);
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
    public function getNews($newsId)
    {
        $data = array($newsId);

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('newsId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);
        $this->setData($rs[0]);

        $this->busNewsAccess->newsIdS = $newsId;
        $this->group = $this->busNewsAccess->searchNewsAccess(TRUE);

        return $this;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchNews($toObject = false, $libraryUnitNull = false, $distinct = false)
    {
        $this->clear();

        if ( $this->listforUser )
        {
            $userCode = $this->busAuthenticate->getUserCode();
            $userGroup = $this->busBond->getAllPersonLink($userCode);

            foreach ($userGroup as $key => $group)
            {
                $allGroup[] = $group->linkId;
            }

            $allGroup = ($allGroup) ? implode(',', $allGroup) : 'null';

            if ( $userCode )
            {
                $this->setWhere(" ( B.linkId in (".$allGroup.") or B.linkId is null )");
            }
            else
            {
                //limita as restritas caso não estive logado
                $this->setWhere( $whereRestricted = "A.isRestricted = 'F'" );
                $this->setWhere( $whereLinkId = "B.linkId is null");
            }
        }

        if ($v = $this->newsIdS)
        {
            $this->setWhere('A.newsId = ?');
            $data[] = $v;
        }

        $place = $this->placeS;

        if ( $place )
        {
            $place = is_array($place) ? $place : array($place);
            $this->setWhere(' place IN (' . implode(',', $place) . ') ');
        }

        $title = ($this->titleS) ? $this->titleS : $this->title1S;
        
        if ($title)
        {
            $this->setWhere('lower(title1) LIKE lower(?)');
            $data[] = $title . '%';
        }

        if ($v = $this->newsS)
        {
            $this->setWhere('lower(news) LIKE lower(?)');
            $data[] = '%' . $v . '%';
        }

        if ($this->dateS)
        {
            $this->setWhere('date(date) = ?');
            $data[] = $this->dateS;
        }

        if ($this->beginDateS)
        {
            $this->setWhere('date(date) >= ?');
            $data[] = $this->beginDateS;
        }

        if ($this->endDateS)
        {
            $this->setWhere('date(date) <= ?');
            $data[] = $this->endDateS;
        }

        if ($this->beginBeginDateS)
        {
            $this->setWhere('date(beginDate) >= ?');
            $data[] = $this->beginBeginDateS;
        }

        if ($this->endBeginDateS)
        {
            $this->setWhere('date(beginDate) <= ?');
            $data[] = $this->endBeginDateS;
        }

        if ($this->beginEndDateS)
        {
            $this->setWhere('date(endDate) >= ?');
            $data[] = $this->beginEndDateS;
        }

        if ($this->endEndDateS)
        {
            $this->setWhere('date(endDate) <= ?');
            $data[] = $this->endEndDateS;
        }

        if ($v = $this->operatorS)
        {
            $this->setWhere('lower(operator) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ( $v = $this->isRestrictedS )
        {
        	$this->setWhere('isRestricted = ?');
        	$data[] = $v;
        }

        if ( $v = $this->isActiveS )
        {
        	$this->setWhere('isActive = ?');
        	$data[] = $v;
        }
        
        if ( $this->checkActiveDates )
        {
            $this->setWhere('((beginDate <= now()::date AND endDate >= now()::date)
                      OR (beginDate IS NULL AND endDate >= now()::date)
                      OR (endDate IS NULL AND beginDate <= now()::date)
                      OR (beginDate IS NULL AND endDate IS NULL))');
        }

        if ($v = $this->libraryUnitIdS )
        {
            if ( $this->librayUnitNull)
            {
                $this->setWhere('((libraryUnitId = ?) OR (libraryUnitId is null))');
            }
            else
            {
                $this->setWhere('libraryUnitId = ?');
            }

        	$data[] = $v;
        }
        
        $distinctString = $distinct ? 'distinct ' : '';

        if ( $this->listforUser )
        {
            $this->setTables('gtcNews            A
                    LEFT JOIN gtcNewsAccess      B
                           ON (A.newsId = B.newsId)');
            
            $this->setColumns( $distinctString . 'A.newsId, ' . $this->colsNoId );
        }
        else
        {
            $this->setTables($this->tables . ' A');
            $this->setColumns($distinctString . $this->columns);
        }

        $this->setOrderBy('date DESC');
        $sql = $this->select($data);
        
        return $this->query($sql, $toObject);
    }


    /**
     * Obtem array de objeto com as noticias do tipo $place informado, ativas,
     * levando em consideracao tambem as datas de inicio e fim.
     *
     * @param <type> $place
     * @return <type>
     */
    public function getActiveByPlace($place)
    {
        $this->clear();
        $this->placeS = $place;
        $this->isActiveS = true;
        $this->checkActiveDates = true;
        return $this->searchNews(true,false, true);
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertNews()
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $data= array($this->place,
                     $this->title1,
                     $this->news,
                     $this->date,
                     $this->beginDate,
                     $this->endDate,
                     $this->operator,
                     $this->isRestricted,
                     $this->isActive,
                     $this->libraryUnitId);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);

        if ($this->group && $rs)
        {
            foreach ($this->group as $value)
            {
                $this->busNewsAccess->setData($value);
                $this->busNewsAccess->newsId = $this->getNextNewsId();
                $this->busNewsAccess->insertNewsAccess();
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
     **/
    public function updateNews()
    {
        $data = $this->associateData( $this->colsNoId . ', newsId' );
        $this->clear();
        $this->setWhere('newsId = ?');
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        if ($this->group && $rs)
        {
            $this->busNewsAccess->deleteByGroup($this->newsId);
            foreach ($this->group as $value)
            {
                $this->busNewsAccess->setData($value);
                $this->busNewsAccess->insertNewsAccess();
            }
        }
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
     **/
    public function deleteNews($newsId)
    {
        $data = array($newsId);

        if ($newsId)
        {
            $this->busNewsAccess->deleteByGroup($newsId);
        }

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('newsId = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }


    public function listPlace()
    {
        $listPlace = array(
            self::PLACE_TYPE_MY_LIBRARY => _M('Minha biblioteca', $this->module),
            self::PLACE_TYPE_INITIAL_SCREEN => _M('Tela inicial', $this->module),
            self::PLACE_TYPE_SEARCH => _M('Pesquisa', $this->module)
        );
        return $listPlace;
    }

    
    public function getNextNewsId()
    {
        $query = $this->query("SELECT currval('seq_newsid')");
        return $query[0][0];
    }
    
}
?>
