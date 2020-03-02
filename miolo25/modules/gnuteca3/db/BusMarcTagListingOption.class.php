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
 * This file handles the connection and actions for gtcMarcTagListingOption table
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
 * Class created on 26/09/2008
 *
 **/

class BusinessGnuteca3BusMarcTagListingOption extends GBusiness
{
    public $MIOLO;
    public $module;
    public $cols;

    public $marcTagListingId;

    /**
     * Class constructor
     **/
    public function __construct()
    {
        parent::__construct();
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->tables   = 'gtcMarcTagListingOption';
        $this->cols     = 'marcTagListingId,
                           option,
                           description';
    }


    /**
     * List values
     *
     * @param $marcTagListingId (integer)
     *
     * @return (array)
     */
    public function listMarcTagListingOption($marcTagListingId, $showDefault = FALSE)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('option, description');
        $this->setWhere('marcTagListingId = ?');
        $sql    = $this->select(array($marcTagListingId));
        $query  = $this->query($sql);
        if (($query) && ($showDefault))
        {
            $query = array_merge(array('DF' => _M('Padrão', $this->module)), $query);
        }
        return $query;
    }


    /**
     * insere as opçoes de listagem do marc
     *
     * @return (boolean)
     */
    public function insertMarcTagListingOptions($marcTagListingId, $marc_options)
    {
        foreach ($marc_options as $i => $values)
        {
        	if ($values->removeData)
        	{
        		continue;
        	}
            parent::clear();
            $this->setTables($this->tables);
            $this->setColumns($this->cols);

            $data = array
            (
                $marcTagListingId,
                $values->option,
                $values->description,
            );

            $sql = parent::insert($data);

            $ok[] = parent::Execute();
        }

        return (array_search(false, $ok) === false);
    }


    /**
     * Remove as opçoes da marc tag listing
     *
     * @param varChar $marcTagListingId
     * @return boolean
     */
    public function removeMarcTagListOptions($marcTagListingId)
    {
        parent::clear();

        parent::setTables($this->tables);

        $this->marcTagListingId = $marcTagListingId;
        $this->getWhereCondition();

        parent::delete($this->getDataConditionArray());

        return parent::Execute();
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

        return $args;
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

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where) - 4);
            parent::setWhere($where);
        }
    }

    /**
     * retorna as opçoes de um determinado marc tag
     *
     * @param (int) $marcTagListingId - Id do registro
     * @return (Array)
     */
    public function getMarcTagListingOptions($marcTagListingId)
    {
        parent::clear();

        parent::setTables($this->tables);
        parent::setColumns('option, description');

        $this->marcTagListingId = $marcTagListingId;

        $this->getWhereCondition();

        parent::setOrderBy("option, marcTagListingId");

        $sql = parent::select($this->getDataConditionArray());

        $result = parent::query(null, 1);

        if(!$result)
        {
            return false;
        }

        return $result;
    }


    public function getMarcTagListingOption($marcTagListingId, $option)
    {
    	$this->clear();
    	$this->setColumns($this->cols);
    	$this->setTables($this->tables);
    	$this->setWhere('marcTagListingId = ?');
    	$this->setWhere('option = ?');
    	$sql = $this->select(array($marcTagListingId, $option));
    	$rs  = $this->query($sql, true);
    	return $rs[0];
    }

    /**
     * Retorna todas as opção ordenando pelo tag e pela descrição
     */
    public function getAllOptions($toObject=false)
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->tables);
        $this->setOrderBy('marctaglistingid, description');
        $sql = $this->select($data);

        $rs  = $this->query($sql, $toObject);

        return $rs;
    }


    /**
     * Retorna todas as opção de tags marc. Ou seja, ignora indicadores e tags de controle
     */
    public function getOnlyTagOptions($toObject=false)
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->tables);
        $this->setWhere("marcTagListingId LIKE ?");
        $this->setOrderBy('marctaglistingid, description');
        $sql = $this->select(array('___._')); //Executa o where para especificar o padrão das tags marc

        $rs  = $this->query($sql, $toObject);

        return $rs;
    }
}
?>
