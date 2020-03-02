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
 * InterchangeObservation business
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
class BusinessGnuteca3BusInterchangeObservation extends GBusiness
{
    public $interchangeObservationId;
    public $interchangeId;
    public $date;
    public $observation;
    public $operator;

    public $interchangeObservationIdS;
    public $interchangeIdS;
    public $dateS;
    public $observationS;
    public $operatorS;


    public function __construct()
    {
    	$table = 'gtcInterchangeObservation';
    	$pkeys = 'interchangeObservationId';
    	$cols  = 'interchangeId,
    	          date,
    	          observation,
    	          operator';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertInterchangeObservation()
    {
    	if ($this->removeData)
    	{
    		if ($this->interchangeObservationId)
    		{
    			$this->deleteInterchangeObservation($this->interchangeObservationId);
    		}
    		return TRUE;
    	}
    	if ($this->interchangeObservationId)
    	{
    		return $this->autoUpdate();
    	}
        return $this->autoInsert();
    }


    public function updateInterchangeObservation()
    {
        return $this->autoUpdate();
    }


    public function getInterchangeObservation($interchangeObservationId)
    {
        $this->clear();
        return $this->autoGet($interchangeObservationId);
    }


    public function deleteInterchangeObservation($interchangeObservationId)
    {
        return $this->autoDelete($interchangeObservationId);
    }


    public function searchInterchangeObservation($toObject = FALSE)
    {
        $filters = array(
            'interchangeObservationId' => 'equals',
            'interchangeId'            => 'equals',
            'observation'           => 'equals',
            'operator'              => 'equals',
        );
        $this->clear();
        return $this->autoSearch($filters, $toObject);
    }


    public function listInterchangeObservation()
    {
        return $this->autoList();
    }


    public function setData($data)
    {
        $this->date         = NULL;
        $this->operator     = NULL;
        $this->observation  = NULL;
        parent::setData($data);
    }
}
?>
