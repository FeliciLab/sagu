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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 02/04/2009
 *
 **/


class BusinessGnuteca3BusRequestChangeExemplaryStatusStatusHistory extends GBusiness
{

    /**
     * Attributes
     */

    public  $requestChangeExemplaryStatusId,        //  | integer                     | not null
            $requestChangeExemplaryStatusStatusId,  //  | integer                     | not null
            $date,                                  //  | timestamp without time zone | not null
            $operator;                              //  | character varying(40)       | not null


    public  $requestChangeExemplaryStatusIdS,        //  | integer                     | not null
            $requestChangeExemplaryStatusStatusIdS,  //  | integer                     | not null
            $dateS,                                  //  | timestamp without time zone | not null
            $operatorS;                              //  | character varying(40)       | not null


    private $localColumns;


    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->localColumns = 'requestChangeExemplaryStatusId, requestChangeExemplaryStatusStatusId, date, operator';
        $this->tables       = 'gtcRequestChangeExemplaryStatusStatusHistory';
    }



    /**
     *
     */
    public function searchRequestChangeExemplaryStatusHistory($order = null)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);

        if($v = $this->requestChangeExemplaryStatusIdS)
        {
            $this->setWhere("requestChangeExemplaryStatusId = ?");
            $data[] = $v;
        }
        if($v = $this->requestChangeExemplaryStatusStatusIdS)
        {
            $this->setWhere("requestChangeExemplaryStatusStatusId = ?");
            $data[] = $v;
        }
        if($v = $this->dateS)
        {
            $this->setWhere("dateS = ?");
            $data[] = $v;
        }
        if($v = $this->operatorS)
        {
            $this->setWhere("operator = ?");
            $data[] = $v;
        }

        if(!is_null($order))
        {
            $this->setOrderBy($order);
        }

        $sql = parent::select($data);
        return parent::query();
    }



    /**
     *
     */
    public function insertRequestChangeExemplaryStatusHistory()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->localColumns);
        $sql = parent::insert($this->associateData($this->localColumns));
        return parent::Execute();
    }



    /**
    * Enter description here...
    *
    * @param unknown_type $requestChangeExemplaryStatusId
    * @return unknown
    */
    public function getLastRequestChangeExemplaryStatusHistory($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables   ($this->tables);
        parent::setColumns  ("requestChangeExemplaryStatusStatusId");
        parent::setWhere    ("requestChangeExemplaryStatusId = ?");
        parent::setOrderBy  ("date DESC");
        $sql = parent::select(array($requestChangeExemplaryStatusId));
        $sql.= " LIMIT 1 ";
        $result = parent::query($sql);

        if(!$result)
        {
            return false;
        }

        return $result[0][0];
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $requestChangeExemplaryStatusId
     * @param unknown_type $statusId
     * @return unknown
     */
    public function compareLastStatus($requestChangeExemplaryStatusId, $statusId)
    {
        return ($statusId == $this->getLastRequestChangeExemplaryStatusHistory($requestChangeExemplaryStatusId));
    }

    public function deleteRequestChangeExemplaryStatusStatusHistory($requestChangeExemplaryStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusId = ?");
        parent::delete(array($requestChangeExemplaryStatusId));
        return parent::execute();
    }


    /**
     * Enter description here...
     *
     */
    public function clean()
    {
        $this->requestChangeExemplaryStatusId=         //  | integer                     | not null
        $this->requestChangeExemplaryStatusStatusId=   //  | integer                     | not null
        $this->date=                                   //  | timestamp without time zone | not null
        $this->operator=                               //  | character varying(40)       | not null
        $this->requestChangeExemplaryStatusIdS=        //  | integer                     | not null
        $this->requestChangeExemplaryStatusStatusIdS=  //  | integer                     | not null
        $this->dateS=                                  //  | timestamp without time zone | not null
        $this->operatorS = null;                       //  | character varying(40)       | not null
    }

} // final da classe
?>
