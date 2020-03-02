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
 * This file handles the connection and actions for basConfig table
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 21/07/2008
 *
 **/
class BusinessGnuteca3BusPreference extends GBusiness
{
    public $busLibraryUnitConfig;
    public $libraryUnitConfig;
    public $allowedLibraryUnitConfig;
    public $filterGroupByNotNull;

    public $moduleConfig;
    public $parameter;
    public $configValue;
    public $description;
    public $type;
    public $groupBy;
    public $orderBy;
    public $label;

    public $moduleConfigS;
    public $parameterS;
    public $configValueS;
    public $descriptionS;
    public $typeS;
    public $groupByS;
    public $orderByS;
    public $labelS;


    /**
     * Class constructor
     **/
    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = 'gnuteca3';
        $this->busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');

        parent::__construct();

        $this->columns = 'moduleConfig,
                          parameter,
                          value AS configValue,
                          description,
                          type,
                          groupBy,
                          orderBy,
                          label';

        $this->tables  = 'basConfig';
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listPreference( $returnAsObject = false )
    {
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('parameter');
        $this->select();
        return $this->query( $this->sql, $returnAsObject );
    }

    /**
     * Lista modulos distintos
     *
     * @return array
     */
    public function listDistinctModules()
    {
    	$sql = "SELECT DISTINCT moduleConfig FROM basConfig";
    	return $this->query( $sql );
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
    public function getPreference($moduleConfig, $parameter, $thisObject = true)
    {
        $data = array(strtoupper($moduleConfig), strtoupper($parameter));

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('
                moduleConfig = ?
            AND parameter    = ?
        ');
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject = true);

        if($thisObject)
        {
            $this->setData($rs[0]);

            $this->busLibraryUnitConfig->parameterS = strtoupper($this->parameter);
            $this->libraryUnitConfig = $this->busLibraryUnitConfig->searchLibraryUnitConfig(TRUE);

            return $this;
        }

        return $rs[0]->configValue;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchPreference($returnAsObject = false, $orderBy = null)
    {
        $this->clear();

        if ( !empty($this->moduleConfigS) )
        {
            $this->setWhere('lower(moduleConfig) LIKE lower(?)');
            $data[] = $this->moduleConfigS;
        }
        if ( !empty($this->parameterS) )
        {
            $this->setWhere('lower(parameter) LIKE lower(?)');
            $data[] = '%'.$this->parameterS . '%';
        }
        if ( !empty($this->configValueS) )
        {
            $this->setWhere('lower(value) LIKE lower(?)');
            $data[] = '%'.$this->configValueS . '%';
        }
        if ( !empty($this->descriptionS) )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%'.$this->descriptionS . '%';
        }
        if ( !empty($this->typeS) )
        {
            $this->setWhere('lower(type) LIKE lower(?)');
            $data[] = $this->typeS;
        }
        if ( !empty($this->groupByS) )
        {
            $this->setWhere('groupBy = ?');
            $data[] = $this->groupByS;
        }
        if ( !empty($this->orderByS) )
        {
            $this->setWhere('orderBy = ?');
            $data[] = $this->orderByS;
        }
        if ( !empty($this->labelS) )
        {
            $this->setWhere('lower(label) LIKE lower(?)');
            $data[] = $this->labelS . '%';
        }
        if ($this->filterGroupByNotNull)
        {
            $this->setWhere('groupBy IS NOT NULL');
        }


        if ( count($data) > 0 || $this->filterGroupByNotNull )
        {
            $this->setColumns($this->columns);
            $this->setTables($this->tables);
            $this->setOrderBy( $orderBy ? $orderBy : 'parameter' );
            $sql = $this->select($data);
            $rs  = $this->query($sql, $returnAsObject);
        }

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
    public function insertPreference()
    {
        $data = array(
            strtoupper($this->moduleConfig),
            strtoupper($this->parameter),
            $this->configValue,
            $this->description,
            $this->type,
            $this->groupBy,
            $this->orderBy,
            $this->label,
        );

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('
            moduleConfig,
            parameter,
            value,
            description,
            type,
            groupBy,
            orderBy,
            label
        ');
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);

        if ($this->libraryUnitConfig)
        {
            foreach ($this->libraryUnitConfig as $d)
            {
                $d->parameter = strtoupper($d->parameter);
                $this->busLibraryUnitConfig->setData($d);
                $this->busLibraryUnitConfig->insertLibraryUnitConfig();
            }
        }

        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updatePreference()
    {
        $this->moduleConfig = strtoupper($this->moduleConfig);
        $this->parameter = strtoupper($this->parameter);
        $data = array(
            $this->configValue,
            $this->description,
            $this->type,
            $this->groupBy,
            $this->orderBy,
            $this->label,
            $this->moduleConfig,
            $this->parameter
        );

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('
            value,
            description,
            type,
            groupBy,
            orderBy,
            label
        ');
        $this->setWhere('
                moduleConfig = ?
            AND parameter    = ?
        ');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        if ($this->libraryUnitConfig && $this->allowedLibraryUnitConfig)
        {
            $this->busLibraryUnitConfig->deleteLibraryUnitConfig($this->allowedLibraryUnitConfig, $this->parameter); //Remove todos com o parametro atual
            foreach ($this->libraryUnitConfig as $d)
            {
                if (!$d->removeData) //Nao esta marcado para ser removido
                {
                    $d->parameter = strtoupper($d->parameter);
                    $this->busLibraryUnitConfig->setData($d);
                    $this->busLibraryUnitConfig->insertLibraryUnitConfig();
                }
            }
        }

        return $rs;
    }

    /**
     * Atualiza todas preferencias de uma unica vez, com os argumentos passados.
     * O formato passado devera ser:
     * array(
     *   [id_bibliotecaA] array( [PARAMETRO_A] => 'valor'
     *                           [PARAMETRO_B] => 'valor' )
     * 
     *   [id_bibliotecaB] array( [PARAMETRO_A] => 'valor'
     *                           [PARAMETRO_B] => 'valor)
     * );
     *
     * Caso o id da biblioteca seja == $this->getGeneralId(), atualiza na basConfig, senao atualiza na gtcLibraryUnitConfig.
     */
    public function updateAll($data)
    {
        if ( !is_array($data) )
        {
            return false;
        }

        $moduleConfig = strtoupper($this->module);

        foreach ( $data as $libraryUnitId => $parameters )
        {
            //Para cada configuração alterada
            foreach ( $parameters as $key => $val )
            {
                $key = strtoupper($key);
                $val = addslashes($val); //segurança já que são sqls puros
                //Verifica se é da unidade geral
                if ( $libraryUnitId == $this->getGeneralId() )
                {
                    //Faz update na basconfig se for unidade geral.
                    $sql[] = "UPDATE basConfig SET value ='{$val}' WHERE moduleConfig = '{$moduleConfig}' AND parameter='{$key}'";
                }
                else if ( strlen($val) > 0 )
                {
                    //Se não for unidade geral, tem que remover o parametro da configuração na gtclibraryUnitConfig
                    $sql[] = "DELETE FROM gtcLibraryUnitConfig WHERE libraryUnitId = '{$libraryUnitId}' AND parameter = '{$key}'";
                    //Inserir novamente o parametro na gtclibraryunitconfig com o valor ajustado.
                    $sql[] = "INSERT INTO gtcLibraryUnitConfig (libraryUnitId, parameter, value) VALUES  ('{$libraryUnitId}', '{$key}', '{$val}')";
                }
                else // Exclui a preferência caso esteja vazia.
                {
                    $sql[] = "DELETE FROM gtcLibraryUnitConfig WHERE libraryUnitId = '{$libraryUnitId}' AND parameter = '{$key}'";
                }  
            }
        }

        return $this->execute( implode(";\r\n", $sql) );
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
    public function deletePreference($moduleConfig, $parameter)
    {
        $parameter = strtoupper($parameter);
        $data = array(strtoupper($moduleConfig), $parameter);

        $this->busLibraryUnitConfig->deleteLibraryUnitConfigByParameter($parameter);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('
                moduleConfig = ?
            AND parameter = ?
        ');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }


    /**
     * Get constants for a specified module
     *
     * @param $moduleConfig (string): Name of the module to load values from
     *
     * @return (array): An array of key pair values
     *
     **/
    public function getModuleValues($moduleConfig)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('parameter,
                           value');
        $this->setWhere('moduleConfig = ?');
        $data = array(strtoupper($moduleConfig));
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        return $rs;
    }

    /**
     * @param (int) $libraryUnitId
     * @param (array) $parameters
     */
    public function getParameterValues($libraryUnitId, $parameters = null, $queryToObject = false)
    {
        $moduleConfig = strtoupper($this->module);
        if ($parameters && !is_array($parameters))
        {
            $parameters = array($parameters);
        }
        if (!is_numeric($libraryUnitId))
        {
            return false;
        }

        //Caso seja passado parametros especificos, monta condicao where
        if (count($parameters) > 0)
        {
            foreach ($parameters as $key => $val)
            {
                $parameters[$key] = "'{$val}'" ;
            }
            $parameters = implode(',', $parameters);
            $specificParameters = "AND parameter IN ({$parameters})";
        }
        else
        {
            $specificParameters = '';
        }

        $sql ="
        SELECT parameter,value FROM gtclibraryunitconfig WHERE libraryUnitId={$libraryUnitId} {$specificParameters}
        UNION
        SELECT parameter,value FROM basConfig WHERE moduleConfig ='{$moduleConfig}'
        AND parameter NOT IN (SELECT parameter FROM gtcLibraryUnitConfig WHERE libraryUnitId='{$libraryUnitId}') {$specificParameters}";

        return $this->query($sql, $queryToObject);
    }

    /**
     * Retorna string de id para ser utilizado no selecion do formulario de FrmLibraryPreference
     * @return (String)
     */
    public function getGeneralId()
    {
        return '-GERAL-';
    }
}
?>
