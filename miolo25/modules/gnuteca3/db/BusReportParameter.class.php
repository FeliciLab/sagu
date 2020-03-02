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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/01/2009
 *
 **/
class BusinessGnuteca3BusReportParameter extends GBusiness
{
	public $reportParameterId;
    public $reportId;
    public $label;
    public $identifier;
    public $type;
    public $defaultValue;
    public $options;
    public $lastValue;
    public $level;
    
    public $reportParameterIdS;
    public $reportIdS;
    public $labelS;
    public $identifierS;
    public $typeS;
    public $defaultValueS;
    public $optionsS;
    public $lastValueS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->defineTables();
    }


    /**
    * Define or redefine the class atributes;
    */
    function defineTables()
    {
        $this->setTables('gtcReportParameter');
        $this->setId('reportParameterId');
        $this->setColumnsNoId('reportId, label, identifier, type, defaultValue, options, lastValue, level');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listReportParameter($object=FALSE)
    {
        $this->defineTables();
        return $this->autoList();
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
    public function getReportParameter($id)
    {
        $this->defineTables();
        $this->clear;
        //here you can pass how many where you want
        return $this->autoGet($id);
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchReportParameter($toObject = false, $orderBy = 'level')
    {
        $this->defineTables();
        $this->clear();

        //here you can pass how many where you want, or use filters

        $filters  = array(
		                    'reportParameterId' => 'equals' ,
		                    'reportId'          => 'equals' ,
		                    'label'             => 'ilike'  ,
		                    'identifier'        => 'ilike'  ,
		                    'type'              => 'equals' ,
		                    'defaultValue'      => 'ilike'  , 
		                    'options'           => 'ilike'  ,
		                    'lastValue'         => 'ilike'  ,
                            'leve'              => 'equals'
                        );
        $this->setOrderBy($orderBy);
                        
        return $this->autoSearch($filters, $toObject);
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertReportParameter()
    {
        if ( !$this->level )
    	{
    		$this->level = '0';
    	}
    	$this->defineTables();
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateReportParameter()
    {
        $this->defineTables();
        return $this->autoUpdate();
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
    public function deleteReportParameter($id)
    {
        $this->defineTables();
        return $this->autoDelete($id);
    }
    
    public function deleteReportParameterByReport($reportId)
    {
    	$this->clear();
        $this->defineTables();
        $this->setWhere( 'reportId = ?');
        $sql = $this->delete( array($reportId) );
        $rs  = $this->execute($sql);
        return $rs;
    }

    /**
     * Método que gera o sql de exportação
     */
    public function exportReportParameter()
    {
    	if ( !$this->level )
    	{
    		$this->level = '0';
    	}

        $args = array( $this->reportId,
                       $this->label,
                       $this->identifier,
                       $this->type,
                       $this->defaultValue,
                       $this->options,
                       $this->lastValue,
                       $this->level);

        $sqlInsert = "INSERT INTO gtcReportParameter (
                                                       reportId,
                                                       label,
                                                       identifier,
                                                       type,
                                                       defaultValue,
                                                       options,
                                                       lastValue,
                                                       level )
                                                       values (
                                                        ?,
                                                        ?,
                                                        ?,
                                                        ?,
                                                        ?,
                                                        ?,
                                                        ?,
                                                        ?);";
        $this->MSQL->clear();
        $this->MSQL->command = $sqlInsert;
        $this->MSQL->prepare($args);

        return $this->MSQL->command;
    }
}
?>