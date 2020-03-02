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
 * This file handles the connection and actions for gtcEmailControlNotifyAquisition table
 *
 * @author Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 09/12/2009
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusEmailControlNotifyAquisition extends GBusiness
{
    public $personId;
    public $lastSent;

    public $personIdS;
    public $lastSentS;
    public $busPersonConfig;


    /**
     * Class constructor
     **/
    function __construct()
    {
        $this->MIOLO            = MIOLO::getInstance();
        $this->module           = MIOLO::getCurrentModule();

        $this->busPersonConfig     = $this->MIOLO->getBusiness($this->module, 'BusPersonConfig');

        $table = 'gtcEmailControlNotifyAquisition';
        $pkeys = 'personId';
        $cols  = 'lastSent';
        parent::__construct($table, $pkeys, $cols);
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertEmailControlNotifyAquisition($personId)
    {
        $this->personId = $personId;
        $this->lastSent = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        return $this->autoInsert();
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateEmailControlNotifyAquisition($personId)
    {
    	$this->personId = $personId;
    	$this->lastSent = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        return $this->autoUpdate();
    }

    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateLastDate($personId)
    {
//        return $this->autoUpdate();
        if (!$this->getLastSent($personId))
        {
        	return $this->insertEmailControlNotifyAquisition($personId);
        }
        else
        {
        	return $this->updateEmailControlNotifyAquisition($personId);
        }
    }


    /**
     * Delete a record
     *
     * @param $loanId (integer)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteEmailControlNotifyAquisition($personId)
    {
        return $this->autoDelete($personId);
    }


    /**
     * Return a specific record from the database
     *
     * @param $loanId (integer)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getEmailControlNotifyAquisition($personId)
    {
        $this->clear();
        return $this->autoGet($personId);
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $object (bool): Case TRUE return as Object, otherwise Array
     *
     * @return (Array): An array containing the search results
     **/
    public function searchEmailControlNotifyAquisition($object = false)
    {
        $this->clear();
        $filters = array(
            'personId'     => 'equals',
            'lastSent'   => 'equals'
        );
        return $this->autoSearch($filters, $object);
    }


    /**
     * List all records from the table handled by the class
     *
     * @param None
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listEmailControlNotifyAquisition()
    {
        return $this->autoList();
    }


    /**
     * Retorna a última vez que foi enviado email para essa pessoa
     *
     * @param integer $personId
     *
     * @return Object Retorna a última vez que foi enviado email para essa pessoa
     **/
    public function getLastSent($personId)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('lastSent');
        $this->setWhere('personId = ?');
        $this->setOrderBy('lastSent DESC');
        $rs = $this->query($this->select(array($personId, $libraryUnitId)), true);
        return $rs[0]->lastSent;
    }

    public function checkSendMail($personId)
    {
    	$lastSent = $this->getLastSent($personId);
        
    	if ( ! $lastSent )
    	{
    		return true;
    	}

    	unset($userNotifyAquisition);
        $lastSent = new GDate($lastSent);
    	//Pega a quantidade de dias da notificação. Se preferência estiver como escrita, pega valor padrão; caso contrário pega valor definido pelo usuário
    	$userNotifyAquisition = $this->busPersonConfig->getValuePersonConfig($personId, 'USER_NOTIFY_AQUISITION');
        $lastSent->addDay($userNotifyAquisition);
        $dateX = GDate::now();

        return $lastSent->compare($dateX, "<=");
    }
}
?>
