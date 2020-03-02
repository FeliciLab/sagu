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
 * Interchange business
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
 * Class created on 20/02/2009
 *
 **/
class BusinessGnuteca3BusInterchange extends GBusiness
{
    public $interchangeId;
    public $type;
    public $supplierId;
    public $description;
    public $date;
    public $interchangeStatusId;
    public $interchangeTypeId;
    public $operator;

    public $interchangeIdS;
    public $typeS;
    public $supplierIdS;
    public $_supplierIdS;
    public $descriptionS;
    public $dateS;
    public $beginDateS;
    public $endDateS;
    public $interchangeStatusIdS;
    public $interchangeTypeIdS;
    public $operatorS;

    public $interchangeItem;
    public $interchangeObservation;

    public $busInterchangeItem;
    public $busInterchangeObservation;


    public function __construct()
    {
    	$table = 'gtcInterchange';
    	$pkeys = 'interchangeId';
    	$cols  = 'type,
    	          supplierId,
    	          description,
    	          date,
    	          interchangeStatusId,
    	          interchangeTypeId,
    	          operator';
        parent::__construct($table, $pkeys, $cols);
        $this->busInterchangeItem        = $this->MIOLO->getBusiness($this->module, 'BusInterchangeItem');
        $this->busInterchangeObservation = $this->MIOLO->getBusiness($this->module, 'BusInterchangeObservation');
    }


    public function insertInterchange()
    {
        if (!$this->interchangeId)
        {
            $this->interchangeId = $this->getNextId();
        }

        if ($this->autoInsert())
        {
            $this->insertExternalData();
            return $this->interchangeId;
        }

        return FALSE;
    }


    public function updateInterchange()
    {
    	$this->insertExternalData();
        return $this->autoUpdate();
    }
    
    public function updateStatus($interchangeId, $statusId)
    {
        $this->getInterchange($interchangeId);
        $this->interchangeStatusId = $statusId;
        return $this->updateInterchange();
    }



    /**
     * retorna um intercambio
     *
     * @param integer $interchangeId
     * @param char $returnType
     * @return unknown
     */
    public function getInterchange($interchangeId, $returnType = null)
    {
    	if (!$interchangeId)
    	{
    	   return false;
    	}

    	switch ($returnType) {
    		case "object":
                $this->interchangeIdS = $interchangeId;
                $s = $this->searchInterchange(true);
                return $s ? $s[0] : false;

    		default:
        		$this->clear();
                $this->findExternalData($interchangeId);
    	        return $this->autoGet($interchangeId);
    	}
    }


    public function deleteInterchange($interchangeId)
    {
    	if ($interchangeId)
    	{
            $this->findExternalData($interchangeId, TRUE);
            return $this->autoDelete($interchangeId);
    	}
    	return FALSE;
    }


    public function searchInterchange($toObject = FALSE)
    {
    	$this->clear();
    	if ($this->interchangeIdS)
    	{
    		$this->setWhere('E.interchangeId = ?', $this->interchangeIdS);
    	}
    	if ($this->typeS)
    	{
    		$this->setWhere('E.type = ?', strtolower($this->typeS));
    	}
    	if (($v = $this->supplierIdS) || ($v = MIOLO::_REQUEST('_supplierIdS')))
    	{
    		$this->setWhere('E.supplierId = ?', $v);
    	}
    	if ($this->descriptionS)
    	{
    		$this->setWhere('lower(E.description) LIKE lower(?)', $this->descriptionS);
    	}
    	if ($this->dateS)
    	{
    		$this->setWhere('date(E.date) = ?', $this->dateS);
    	}
    	if ($this->beginDateS)
    	{
    		$this->setWhere('date(E.date) >= ?', $this->beginDateS);
    	}
    	if ($this->endDateS)
    	{
    		$this->setWhere('date(E.date) <= ?', $this->endDateS);
    	}
    	if ($this->interchangeStatusIdS)
    	{
    		$this->setWhere('E.interchangeStatusId = ?', $this->interchangeStatusIdS);
    	}
    	if ($this->interchangeTypeIdS)
    	{
    		$this->setWhere('E.interchangeTypeId = ?', $this->interchangeTypeIdS);
    	}
    	if ($this->operatorS)
    	{
    		//$this->setWhere('E.operator = ?', $this->operatorS);
    	}

    	$this->setTables('gtcInterchange       E
    	       LEFT JOIN  gtcSupplier       S
    	              ON  (E.supplierId = S.supplierId)
    	       LEFT JOIN  gtcInterchangeStatus ES
    	              ON  (E.interchangeStatusId = ES.interchangeStatusId)
    	       LEFT JOIN  gtcInterchangeType   ET
    	              ON  (E.interchangeTypeId = ET.interchangeTypeId)');
    	$this->setColumns('E.interchangeId,
    	                   E.type,
    	                   S.supplierId,
    	                   S.name,
    	                   (SELECT companyName FROM gtcSupplierTypeAndLocation STL WHERE STL.supplierId=E.supplierId AND STL.type=E.type) AS companyName,
    	                   E.description,
    	                   E.date,
    	                   ES.interchangeStatusId,
    	                   ES.description,
    	                   ET.interchangeTypeId,
    	                   ET.description,
    	                   E.operator');
    	$sql   = $this->select();
    	$query = $this->query($sql, $toObject);
    	return $query;
    }


    public function listInterchange()
    {
        return $this->autoList();
    }


    public function listTypes()
    {
    	return array(
    	   'p' => _M('Permuta', $this->module), //Permuta
    	   'd' => _M('Doação', $this->module), //Doacao
    	);
    }


    public function insertExternalData()
    {
        if ($this->interchangeItem)
	    {
	        foreach ($this->interchangeItem as $v)
	        {
	            $v->interchangeId = $this->interchangeId;
	            $this->busInterchangeItem->setData($v);
	            $this->busInterchangeItem->insertInterchangeItem();
	        }
	    }

        //insere observações
	    if ($this->interchangeObservation)
	    {
	        foreach ($this->interchangeObservation as $v)
	        {
	            $v->interchangeId = $this->interchangeId;
	            $this->busInterchangeObservation->setData($v);

                //caso tenha id faz update
                if ( $v->interchangeObservationId && !$v->removeData )
                {
                    $this->busInterchangeObservation->updateInterchangeObservation();
                }
                else
                {
                    $this->busInterchangeObservation->insertInterchangeObservation();
                }
	        }
	    }
    }


    public function findExternalData($interchangeId, $deleteData = FALSE)
    {
	    $this->busInterchangeItem->interchangeIdS = $interchangeId;
	    $this->interchangeItem = $this->busInterchangeItem->searchInterchangeItem(TRUE);

	    $this->busInterchangeObservation->interchangeIdS = $interchangeId;
	    $this->interchangeObservation = $this->busInterchangeObservation->searchInterchangeObservation(TRUE);

	    if ($deleteData)
	    {
	    	if ($this->interchangeItem)
	    	{
	    		foreach ($this->interchangeItem as $v)
	    		{
	    			$this->busInterchangeItem->deleteInterchangeItem($v->interchangeItemId);
	    		}
	    	}
	    	if ($this->interchangeObservation)
	    	{
	    		foreach ($this->interchangeObservation as $v)
	    		{
	    			$this->busInterchangeObservation->deleteInterchangeObservation($v->interchangeObservationId);
	    		}
	    	}
	    }
    }

    public function getInterchangeItem($interchangeId)
    {
        return $this->busInterchangeItem->getInterchangeItem($interchangeId);
    }
    
    public function getNextId()
    {
        return $this->db->getNewId('seq_interchangeid');
    }
}
?>
