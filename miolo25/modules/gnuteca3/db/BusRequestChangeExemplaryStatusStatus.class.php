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
 * Class created on 02/04/2009
 *
 **/


class BusinessGnuteca3BusRequestChangeExemplaryStatusStatus extends GBusiness
{
    /**
     * Attributes
     */

    public  $requestChangeExemplaryStatusStatusId,// | integer               | not null
            $description;                         // | character varying(40) | not null

    public  $requestChangeExemplaryStatusStatusIdS,// | integer               | not null
            $descriptionS;                         // | character varying(40) | not null

    /**
     * Constructor Method
     */

    function __construct()
    {
        parent::__construct();

        $this->pKey         = 'requestChangeExemplaryStatusStatusId';
        $this->columns      = 'description';
        $this->fullColumns  = "{$this->pKey}, {$this->columns}";
        $this->tables       = 'gtcRequestChangeExemplaryStatusStatus';
    }



    /**
     *
     */
    public function searchRequestChangeExemplaryStatusStatus($order = null)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);

        if($v = $this->requestChangeExemplaryStatusStatusIdS)
        {
            $this->setWhere("requestChangeExemplaryStatusStatusId = ?");
            $data[] = $v;
        }
        if($v = $this->descriptionS)
        {
            $this->setWhere("description = ?");
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
     * Enter description here...
     *
     * @return unknown
     */
    public function listRequestChangeExemplaryStatusStatus($filterPossiblesStatus = false)
    {
        parent::clear();
        parent::setColumns($this->fullColumns);
        parent::setTables($this->tables);

        switch ($filterPossiblesStatus)
        {
            // REQUISITADO = poder aprovar, reprovar e cancelar
            case 1 :
                parent::setWhere("requestChangeExemplaryStatusStatusId IN (1, 2, 3, 5)");
                break;

            // APROVADO = pode cancelar e confirmar
            case 2:
                parent::setWhere("requestChangeExemplaryStatusStatusId IN (2, 5)");
                break;

            // REPROVADO = não pode mais trocar de estado
            case 3:
                parent::setWhere("requestChangeExemplaryStatusStatusId IN (3)");
                break;

            // CONCLUIDO = não pode mais trocar de estado
            case 4:
                parent::setWhere("requestChangeExemplaryStatusStatusId IN (4)");
                break;

            // CANCELADO = não pode mais trocar de estado
            case 5:
                parent::setWhere("requestChangeExemplaryStatusStatusId IN (5)");
                break;

            // CONFIRMADO = Pode concluir ou cancelar
            case 6:
                parent::setWhere("requestChangeExemplaryStatusStatusId IN (4, 5, 6)");
                break;

            default: break;
        }

        $sql = parent::select();
        $rs  = parent::query();

        $out = array();
        if ($rs)
        {
            foreach ($rs as $v)
            {
                list($id, $value) = $v;
                $out[ $id ] = $value;
            }
        }

        return $out;
    }



    /**
     *
     */
    public function insertRequestChangeExemplaryStatusStatus()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::insert($this->associateData($this->columns));
        return parent::Execute();
    }


    /**
     *
     */
    public function updateRequestChangeExemplaryStatusContent()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusStatusId = ?");
        parent::update($this->associateData("{$this->columns}, {$this->pKey}"));
        return parent::Execute();
    }



    /**
     *
     */
    public function getRequestChangeExemplaryStatusStatus($requestChangeExemplaryStatusStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusStatusId = ?");
        parent::select(array($requestChangeExemplaryStatusStatusId));
        $result = parent::query(null, true);
        return isset($result[0]) ? $result[0] : false;
    }


    /**
     *
     */
    public function deleteRequestChangeExemplaryStatusStatus($requestChangeExemplaryStatusStatusId)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->columns);
        parent::setWhere("requestChangeExemplaryStatusStatusId = ?");
        parent::delete(array($requestChangeExemplaryStatusStatusId));
        return parent::execute();
    }


    /**
     * Enter description here...
     *
     */
    public function clean()
    {
        $this->requestChangeExemplaryStatusStatusId= // | integer               | not null
        $this->description=                          // | character varying(40) | not null
        $this->requestChangeExemplaryStatusStatusIdS=// | integer               | not null
        $this->descriptionS=null;                    // | character varying(40) | not null

    }



} // final da classe
?>
