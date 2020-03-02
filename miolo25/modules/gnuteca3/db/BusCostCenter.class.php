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
 * gtcCostCenter business
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 03/12/2008
 *
 **/
class BusinessGnuteca3BusCostCenter extends GBusiness
{
    public $costCenterId;
    public $description;
    public $libraryUnitId;
    public $columns,
           $table       = 'gtcCostCenter',
           $pkeys       = 'costCenterId',
           $cols        = 'libraryUnitId,description';

    public $costCenterIdS;
    public $libraryUnitIdS;
    public $descriptionS;

    public function __construct()
    {
        $this->columns = "{$this->pkeys}, {$this->cols}";
        parent::__construct($this->table, $this->pkeys, $this->cols);
    }


    public function insertCostCenter()
    {
        return $this->autoInsert();
    }


    public function updateCostCenter()
    {
        return $this->autoUpdate();
    }


    public function deleteCostCenter($costCenterId)
    {
        return $this->autoDelete($costCenterId);
    }


    public function getCostCenter($costCenterId)
    {
        $this->clear();
        return $this->autoGet($costCenterId);
    }


    public function searchCostCenter($object = false)
    {
        $this->clear();

        $filters = array
        (
            'costCenterId' => 'equals',
            'libraryUnitId' => 'equals',
            'description' => 'ilike',
        );

        return $this->autoSearch($filters, $object);
    }


    public function deleteAllCostCenter()
    {
        $rs  = $this->execute("DELETE FROM {$this->tables}");
        return $rs;
    }


    public function listCostCenter($forCatalogue = false)
    {
        $this->setColumns($this->columns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);

        if(!$rs || !$forCatalogue)
        {
            return $rs;
        }

        foreach ($rs as $i => $v)
        {
            $r[$i]->option      = $v[0];
            $r[$i]->description = $v[2];
        }

        return $r;
    }

}
?>
