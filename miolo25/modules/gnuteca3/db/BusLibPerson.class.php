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
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2008
 *
 * */
class BusinessGnuteca3BusLibPerson extends GBusiness
{
    public $colsNoId;
    public $fullColumns;
    public $MIOLO;
    public $module;

    public $personId;
    public $baseLdap;    
    public $sex;    
    public $profession;    
    public $workPlace;
    public $school;    
    public $dateBirth;
    public $personGroup;
    public $operationProcess;
    
    public $personIdS;
    public $baseLdaps;    
    public $sexS;    
    public $professionS;    
    public $workPlaceS;
    public $schoolS;    
    public $dateBirthS;
    public $personGroupS;
    public $operationProcessS;    

    public function __construct()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
        $this->tables = 'gtcLibPerson';
        $this->colsNoId = 'baseLdap,      
                           sex,      
                           profession,      
                           workPlace,  
                           school,      
                           dateBirth,  
                           personGroup,  
                           operationProcess';
        
        $this->fullColumns = 'personId, ' . $this->colsNoId;
    }

    public function getLibPerson($personId)
    {
        $result = NULL;

        if ( !$personId || !is_numeric($personId) )
        {
            return false;
        }
        else
        {
            $data = array( $personId );

            $this->clear();
            $this->setColumns($this->fullColumns);
            $this->setTables($this->tables);
            $this->setWhere('personId = ?');
            $sql = $this->select($data);
            $result = $this->query($sql, TRUE);
       }
       
       return $result;
    }

    public function searchLibPerson($returnAsObject = false)
    {
        $this->clear();

        if ( $v = $this->personIdS )
        {
            $this->setWhere('personId = ?');
            $data[] = $v;
        }

        if ( $v = $this->baseLdapS )
        {
            $this->setWhere('baseLdap = ?');
            $data[] = $v;
        }

        if ( $v = $this->personGroupS )
        {
            $this->setWhere('lower(persongroup) LIKE lower(?)');
            $data[] = $v . '%';
        }

        if ( $v = $this->sexS )
        {
            $this->setWhere('sex = ?');
            $data[] = $v;
        }

        if ( $v = $this->dateBirthS )
        {
            $this->setWhere('lower(dateBirth) = ?');
            $data[] = $v;
        }

        if ( $v = $this->professionS )
        {
            $this->setWhere('lower(profession) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        if ( $v = $this->workPlaceS )
        {
            $this->setWhere('lower(workPlace) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        if ( $v = $this->schoolS )
        {
            $this->setWhere('lower(school) ILIKE lower(?)');
            $data[] = $v . '%';
        }

        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $this->setOrderBy('personId');
        $sql = $this->select($data);

        return $this->query($sql, $returnAsObject);
    }

    public function insertLibPerson()
    {
        $this->clear();

        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);

        $sql = $this->insert($this->associateData($this->fullColumns));

        $rs = $this->execute($sql);

        return $rs;
    }

    public function updateLibPerson()
    {
        $this->clear();

        $columns = $this->colsNoId;
        $colsAssociate = $this->colsNoId;

        $this->setColumns($columns);
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->update($this->associateData($colsAssociate . ', personId'));
        $rs = $this->execute($sql);

        return $rs;
    }

    public function deleteLibPerson($personId)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $rs = $this->execute($this->delete(array( $personId )));

        return $rs;
    }

    public function isOperationProcess()
    {
        if ( !$this->personId )
        {
            return false;
        }

        //testa se tem registro, caso não, retorna false
        $personOperationProcess = $this->getPersonOperationProcess(true);

        $diff = 0;
        if ( $personOperationProcess )
        {
            $operationProcess = new GDate($personOperationProcess->operationProcess);
            $now = GDate::now();
            $diff = $now->diffDates($operationProcess, GDate::ROUND_DOWN);
            
            if ( ($diff->seconds / 60) < OPERATION_PROCESS_TIME )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function removeOperationProcess($personId)
    {
        if ( !$this->personId || !is_numeric($this->personId) ) //se não existir ou se não for número
        {
            return false;
        }
        else
        {
            $this->operationProcess = null;
            return $this->updatePersonOperationProcess();
        }
    }

    public function setOperationProcess()
    {
        if ( !$this->personId )
        {
            return FALSE;
        }

        $data = array( );
        $data[] = $this->personId;

        //pega hora e data atual em timestamp
        $this->clear();
        $this->setColumns('now()');
        $resultData = $this->query($this->select());
        $this->operationProcess = $resultData[0][0];

        //atualiza campo operationProcess
        $this->updatePersonOperationProcess();
        
    }
    

    
    
    public function listPersonOperationProcess()
    {
    	$this->clear();
    	$this->setColumns('personId,operationProcess');
    	$this->setTables($this->tables);
    	$sql = $this->select();
    	$rs = $this->query($sql);
        
    	return $rs;
    }
    

    public function setData($data)
    {
        $objectData = new stdClass();
        $objectData->personId = $data->personId;
        $objectData->baseLdap = $data->baseLdap;    
        $objectData->sex = $data->sex;        
        $objectData->profession = $data->profession;        
        $objectData->workPlace = $data->workPlace;
        $objectData->school = $data->school;    
        $objectData->dateBirth = $data->dateBirth;
        $objectData->personGroup = $data->personGroup;
        $objectData->operationProcess = $data->operationProcess; 

        parent::setData($objectData);
    }    
    
    
    
    public function getPersonOperationProcess($return=FALSE)
    {
    	if (!$this->personId || !is_numeric($this->personId))
    	{
    		return false;
    	}
    	else
    	{
	        $data = array($this->personId);

	        $this->clear();
	        $this->setColumns('operationProcess');
	        $this->setTables($this->tables);
	        $this->setWhere('personId = ?');
	        $sql = $this->select($data);
	        $rs  = $this->query($sql, TRUE);
                
	        if ($rs)
	        {
		        if ( !$return )
		        {
			        $this->setData( $rs[0] );
			        return $this;
		        }
		        else
		        {
		        	$result  = $rs[0];
			        return $result;
		        }
	        }
    	}
    }
    
    
    public function updatePersonOperationProcess()
    {
        $this->clear();
        $this->setColumns('operationProcess');
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->update( $this->associateData( 'operationProcess, personId' ) );
        $rs  = $this->execute($sql);

        return $rs;
    }
    
}

?>
