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
 * gtcSearchFormatColumn business
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
 * Class created on 08/04/2009
 *
 **/


class BusinessGnuteca3BusSearchFormatColumn extends GBusiness
{
    public $searchFormatId;
    public $column;


    public function __construct()
    {
        $table = 'gtcSearchFormatColumn';
        $pkeys = 'searchFormatId,
                  "column"';
        $cols  = '';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertSearchFormatColumn()
    {
    	$msql = new MSQL($this->columns, $this->tables);
    	$sql = $msql->insert(array($this->searchFormatId, $this->column));
        return $this->execute($sql);
    }


    public function updateSearchFormatColumn()
    {
        return $this->autoUpdate();
    }


    public function deleteSearchFormatColumn($searchFormatId, $column = NULL)
    {
        $msql = new MSQL($this->columns, $this->tables);
        $msql->setWhere('searchFormatId = ?');
        $msql->addParameter($searchFormatId);
        if ($column)
        {
        	$msql->setWhere('column = ?');
        	$msql->addParameter($column);
        }
        return $this->execute( $msql->delete() );
    }


    public function getSearchFormatColumn($searchFormatId)
    {
        $this->clear();
        return $this->autoGet($searchFormatId);
    }


    public function searchSearchFormatColumn($toObject = FALSE)
    {
        $this->clear();
        $filters = array(
            'searchFormatId'    => 'equals',
            'column'            => 'equals',
        );
        return $this->autoSearch($filters, $toObject);
    }


    public function listSearchFormatColumn($associative = FALSE)
    {
        return $this->autoList(null, $associative);
    }


    /**
     * Get SimpleSearch form grid column names
     *
     */
    public function listColumns()
    {
    	$columns = array(
    	   'Image' => _M('Imagem','gnuteca3'),
    	   'Data' => _M('Dados','gnuteca3'),
    	   'Exemplarys' => _M('Exemplares','gnuteca3')
    	);
        
        return $columns;
    }
}
?>