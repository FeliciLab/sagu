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
 * InterchangeItem business
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
class BusinessGnuteca3BusInterchangeItem extends GBusiness
{
    public $interchangeItemId;
    public $interchangeId;
    public $controlNumber;
    public $content;

    public $interchangeItemIdS;
    public $interchangeIdS;
    public $controlNumberS;
    public $contentS;


    public function __construct()
    {
    	$table = 'gtcInterchangeItem';
    	$pkeys = 'interchangeItemId';
    	$cols  = 'interchangeId,
    	          controlNumber,
    	          content';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertInterchangeItem()
    {
    	if ($this->removeData)
    	{
    		if ($this->interchangeItemId)
    		{
    			$this->deleteInterchangeItem($this->interchangeItemId);
    		}
    		return TRUE;
    	}
    	if ($this->interchangeItemId)
    	{
            return $this->updateInterchangeItem();
    	}
        return $this->autoInsert();
    }


    public function updateInterchangeItem()
    {
        return $this->autoUpdate();
    }


    public function getInterchangeItem($interchangeItemId)
    {
        $this->clear();
        $data = $this->autoGet($interchangeItemId);
        return $data;
    }


    public function deleteInterchangeItem($interchangeItemId)
    {
        return $this->autoDelete($interchangeItemId);
    }


    public function searchInterchangeItem($toObject = FALSE)
    {
        $filters = array(
            'interchangeItemId'=> 'equals',
            'interchangeId'    => 'equals',
            'controlNumber' => 'equals',
            'content'       => 'ilike'
        );
        $this->clear();
        return $this->autoSearch($filters, $toObject);
    }


    public function listInterchangeItem()
    {
        return $this->autoList();
    }


    public function setData($data)
    {
    	$this->controlNumber = NULL;
    	$this->content       = NULL;
    	parent::setData($data);
    }
}
?>
