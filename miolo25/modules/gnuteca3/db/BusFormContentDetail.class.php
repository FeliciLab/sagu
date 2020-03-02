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
 * gtcFormContentDetail business
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
 * Class created on 07/04/2009
 *
 **/


class BusinessGnuteca3BusFormContentDetail extends GBusiness
{
    public $formContentId;
    public $field;
    public $value;

    public $formContentIdS;
    public $fieldS;
    public $valueS;

    public $_table;
    public $_pkeys;
    public $_cols;


    public function __construct()
    {
        $this->_table = 'gtcFormContentDetail';
        $this->_pkeys = 'formContentId,
                         field';
        $this->_cols  = 'value';
        parent::__construct($this->_table, $this->_pkeys, $this->_cols);
    }


    public function insertFormContentDetail()
    {
    	if ($this->removeData)
    	{
    		return FALSE;
    	}
        return $this->autoInsert();
    }


    public function updateFormContentDetail()
    {
        return $this->autoUpdate();
    }


    public function deleteFormContentDetail($formContentId, $field=NULL)
    {
    	$msql = new MSQL(null, $this->_table);
    	$msql->setWhere('formContentId = ?');
    	$msql->addParameter($formContentId);
    	if ($field)
    	{
    		$msql->setWhere('field = ?');
    		$msql->addParameter($field);
    	}
        return $this->execute( $msql->delete() );
    }


    public function getFormContentDetail($formContentId, $field)
    {
        $this->clear();
        return $this->autoGet($formContentId, $field);
    }


    public function searchFormContentDetail($toObject = FALSE)
    {
        $this->clear();
        $filters = array(
            'formContentId'     => 'equals',
            'field'             => 'ilike',
            'value'             => 'ilike',
        );
        return $this->autoSearch($filters, $toObject);
    }


    public function listFormContentDetail()
    {
        return $this->autoList();
    }
}
?>
