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
class BusinessGnuteca3BusReport extends GBusiness
{
    /** @var BusinessGnuteca3BusReportParameter  */
	public $busReportParameter;

	public $reportId;
    public $Title;
    public $description;
    public $permission;
    public $reportSql;
    public $reportSubSql;
    public $script;
    public $model;
    public $isActive;
    public $reportGroup;
    public $parameters;

    public $reportIdS;
    public $TitleS;
    public $titleS;
    public $descriptionS;
    public $permissionS;
    public $reportSqlS;
    public $reportSubSqlS;
    public $scriptS;
    public $modelS;
    public $isActiveS;
    public $reportGroupS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        $module = 'gnuteca3';
    	$this->busReportParameter = MIOLO::getInstance()->getBusiness($module, 'BusReportParameter');
        parent::__construct();
        $this->defineTables();
    }


    /**
    * Define or redefine the class atributes;
    */
    function defineTables()
    {
        $this->setTables('gtcReport');
        $this->setId('reportId');
        $this->setColumnsNoId('Title, description, permission, reportSql, reportSubSql, script, model, isActive, reportGroup');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listReport($object=FALSE)
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
    public function getReport($id)
    {
        $this->defineTables();
        $this->clear;
        $result     = $this->autoGet($id);
        $parameters = $this->getParameters($id);
        $result->parameters = $parameters;
        $this->parameters = $parameters;
        return $result;
    }


    /**
     * Get all parameters of a report
     *
     * @param integer $reportId
     * @return array array of objects
     */
    public function getParameters($reportId)
    {
    	$this->busReportParameter->reportIdS = $reportId;
        $parameters = $this->busReportParameter->searchReportParameter(true);
        return $parameters;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchReport($toObject = false, $checkAccess = false)
    {

        $this->clear();
        $this->defineTables();
        $this->setOrderBy('title');

        if ( $this->reportIdS )
        {
            $this->setWhere('reportId = ?');
            $data[] = strtoupper( $this->reportIdS );
        }
        if ( $this->titleS )
        {
            $this->setWhere('lower(title) LIKE lower(?)');
            $data[] = $this->titleS . '%';
        }
        if ( $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $this->descriptionS . '%';
        }
        if ( $this->permissionS )
        {
            $this->setWhere('permission = ?');
            $data[] = $this->permissionS;
        }
        if ( $this->isActiveS )
        {
            $this->setWhere('isActive = ?');
            $data[] = $this->isActiveS;
        }
        if ( $this->reportGroupS )
        {
            $this->setWhere('reportGroup = ?');
            $data[] = $this->reportGroupS;
        }

        //Mostra os relatórios de acordo com as permissões do usuário
        if ($checkAccess)
        {
        	if (!GPerms::checkAccess('gtcAdminReportBasic', null, false))
        	{
                $this->setWhere("permission != 'basic'" );
        	}
        	if (!GPerms::checkAccess('gtcAdminReportIntermediary', null, false))
        	{
                $this->setWhere("permission != 'intermediary'" );
        	}
        	if (!GPerms::checkAccess('gtcAdminReportAdvanced', null, false))
        	{
                $this->setWhere("permission != 'advanced'" );
        	}
        }
        
        $sql = $this->select($data);
        $rs  = $this->query($sql, $object);

        return $rs;    
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertReport()
    {
    	$this->defineTables();
        //caso não passe um id pega da sequência, para compatibilidade
        $this->reportId  ? $this->reportId : $this->getNextval();
    	$result = $this->autoInsert();
        
    	if ($result)
    	{
    		$this->insertParameters( $this->reportId );
    	}
        
    	return $result;
    }

    /**
     * Insert an array of object that are in session
     *
     * @param integer $reportId
     * @return boolean true if succeded.
     */
    public function insertParameters($reportId)
    {
        //FIXME corrigir isto não pode pegar sessão dentro do bus fere MVC
        $parameters = $_SESSION['GRepetitiveField']['parameters'];
        
        if ($parameters)
        {
            foreach ($parameters as $line => $info)
            {
                if ( !$info->removeData ) //informações removidas do GRepetitiveField
                {
                	$this->busReportParameter->lastValue       = '';
                	$this->busReportParameter->defaultValue    = '';
                	$this->busReportParameter->options         = '';
                    $info->level = $info->arrayItem; //O nível do parâmetro deverá ser o mesmo que a sua posição dentro da GRepetitiveField, para que o posicionamento alterado não se perca na exportação do relatório.
                    $this->busReportParameter->setData($info);
                    $this->busReportParameter->reportId = $reportId;
                    unset( $this->busReportParameter->reportParameterId );
                    $result = $this->busReportParameter->insertReportParameter();
                }
                else
                {
                    $result = true;
                }
            }
        }
        else
        {
            $result = true;
        }
        
        return $result;
    }


    /**
     * Return the next available id
     *
     * @return integer the next available id
     */
    public function getNextval()
    {
    	$sql    = "select nextval('seq_reportid');";
    	$result = $this->query($sql);
    	return $result[0][0];
    }

   
    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateReport()
    {
        $this->defineTables();

        $ok = $this->autoUpdate();

        if ( $ok && $this->parameters )
        {
            $ok = $this->busReportParameter->deleteReportParameterByReport($this->reportId);
            $ok = $this->insertParameters($this->reportId);
        }
        
        return $ok;
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
    public function deleteReport( $reportId )
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile->folder = 'odt';
        $busFile->fileName = BusinessGnuteca3BusFile::getValidFilename( $reportId ) .'.';
        $file = $busFile->searchFile(true);
        $file = $file[0];

        if ( $file->absolute )
        {
            $ok = $file->delete();

            if ( !$ok )
            {
                //dificilmente entrará aqui, mas é uma segurança contra problemas
                throw new Exception( _M('Impossível remover arquivo @1.','gnuteca3',$file->basename) );
            }
        }

        $this->busReportParameter->deleteReportParameterByReport($reportId);
        $this->defineTables();
        return $this->autoDelete( $reportId );
    }


    /**
     * Método que gera os SQL's de exportação
     *
     */
    public function exportReport( $reportId )
    {
        $report = $this->getReport($reportId);
        $this->setData($report);

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $sqlReport = $this->insert($this->associateData($this->columns) ) . ';'; //sql do report

        $sqlReport .= "\n";

        //sql dos parâmetros
        $parameters = $report->parameters;
        $sqlParameter = array();
        
        if ($parameters)
        {
            foreach ($parameters as $line => $info)
            {
                $this->busReportParameter->setData($info);
                $sqlParameter[] = str_replace(array("\'",'\"'), array("''",'"'), $this->busReportParameter->exportReportParameter()); //O replace está sendo feito direto aqui porque o miolo gera as instruções com \" ou \''
            }
        }
        
        $sqlReport .= implode("\n", $sqlParameter);

        return $sqlReport;
    }

    /**
     * Função para executar relatório a partir da catalogação.
     * 
     * @param string $reportId
     * @param stdClass $parameters
     * @return array de array
     */
    public function executeReport($reportId, $parameters)
    {
        if ( !$reportId )
        {
            return false;
        }

        //Busca pelo relatório do usuário
        $this->reportIdS = $reportId . '_USER';

        //Se não tiver o relatorio do usuário 
        if ( !$this->searchReport() )
        {
            //Usa relatório padrao
            $this->reportIdS = $reportId;
        }

        $report = $this->getReport($this->reportIdS);
        $result = $this->executeSelect($report->reportSql, $report->reportSubSql, $parameters);

        //TODO suportar adicionar total por aqui também;

        return $result;
    }

}
?>