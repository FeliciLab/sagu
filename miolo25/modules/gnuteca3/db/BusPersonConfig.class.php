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
 * This file handles the connection and actions for basConfig table
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
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 23/10/2008
 *
 **/
class BusinessGnuteca3BusPersonConfig extends GBusiness
{
    public $personId;
    public $parameter;
    public $value;
    public $configData;
    public $busPreference;

    const NOT_SEND_MAIL = 1;
    const PRINT_AND_SEND_RECEIPT = 2;
    const PRINT_RECEIPT = 3;
    const SEND_RECEIPT = 4;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->defineTables();
        $this->busPreference = $this->MIOLO->getBusiness($this->module, 'BusPreference');
    }


    /**
    * Define or redefine the class atributes;
    */
    function defineTables()
    {
        $this->setTables('gtcPersonConfig');
        $this->setId('personId, parameter');
        $this->setColumnsNoId('value');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listPersonConfig($object=FALSE)
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
    public function getPersonConfig($id)
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
    public function searchPersonConfig($object=false, $object)
    {
        $this->defineTables();
        $this->clear();

        //here you can pass how many where you want, or use filters

        $filters  = array(
                    'personId'              => 'equals',
                    'parameter'  			=> 'equals',
                    'value'                 => 'equals',
                    );
        return $this->autoSearch($filters, $object);
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertPersonConfig()
    {
    	unset($this->parameter);
    	$search = $this->searchPersonConfig(true, true);
    	foreach ((array)$search as $v)
    	{
    		$parameters[] = $v->parameter;
    	}

        if ($this->configData && $this->personId)
        {
            //$this->deletePersonConfig($this->personId);
            $this->defineTables();
            foreach ($this->configData as $line => $info)
            {
                if ($line != 'btnSave' && strlen($info) > 0) 
                {
                    $this->parameter = $line;
                    $this->value     = $info;
                    $ok = (in_array($line, $parameters)) ? $this->autoUpdate() : $this->autoInsert();
                }
            }
        }
        return $ok;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updatePersonConfig()
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
    public function deletePersonConfig($personId)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('personId = ?');
        $sql = $this->delete( $personId );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Get value of person config
     *
     * @param $personId (integer)
     * @param $parameter (String)
     *
     * @return (String): Value
     */
    public function getValuePersonConfig($personId, $parameter)
    {
        $parameter = trim(strtoupper($parameter));
    	$ignorePersonConfig = FALSE;
        $user_config = explode("\n", USER_CONFIG);

        foreach ((array)$user_config as $v)
        {
            list($var, $perm) = explode('=', $v);

            //desconsidera as observações, que estão depois do |
            $perm = explode('|',$perm );
            $perm = strtoupper(trim($perm[0]));

            $correctParameter = '';
            if (in_array($parameter, array('MARK_SEND_RETURN_MAIL_RECEIPT', 'MARK_PRINT_RECEIPT_RETURN')))
            {
            	$correctParameter = 'CONFIGURE_RECEIPT_RETURN';
            }

            if (in_array($parameter, array('MARK_SEND_LOAN_MAIL_RECEIPT', 'MARK_PRINT_RECEIPT_LOAN')))
            {
                $correctParameter = 'CONFIGURE_RECEIPT_LOAN';
            }

            if ( ((strtoupper($var) == $correctParameter) || (strtoupper($var) == $parameter)) && (in_array($perm, array('R', 'I'))))
            {
            	$ignorePersonConfig = TRUE;
            }
        }

        if ( (!$ignorePersonConfig) && ($personId) )
        {
	        $this->personId = $personId;
	        $this->parameter= $parameter;
	        $search			= $this->searchPersonConfig(null, true);
		    $result			= $search[0]->value;
        }

        //caso não encontre preferência do usuário pega preferência geral, na basConfig
	    if ( strlen($result) == 0 )
	    {
	    	$result = constant( $parameter );
	    }

	    return $result;
    }


    /**
     * Método que retorna a listagem de configurações do usuário
     *
     * @param int $personId
     * @return array de dados
     */
    public function getCompleteInfoForPersonConfig($personId)
    {
        $dataArray = array();
        $data = $this->getParseValuesPersonConfig($personId);
        $user_config = explode ("\n", USER_CONFIG);

        if ( $data instanceof stdClass )
        {
            foreach ( (array)$user_config as $i=>$v )
            {
                //Separa a legenda do parâmetro
                list($var1, $label) = explode('|', $v);
                list($id, $perm) = explode('=', $var1);
                $id = strtoupper(trim($id));

                $dataArray[$i][0] = $label;
                $dataArray[$i][1] = $data->$id;
            }
        }

        return $dataArray;

    }


    /**
     *
     * Método que retorna as configurações do usuário
     *
     * @param int $personId
     * @param boolean $onlyValues
     * @return objeto com dados
     */
    public function getParseValuesPersonConfig($personId = null, $onlyValues = false)
    {
        if ( !$personId )
        {
            return;
        }

        $user_config = explode ("\n", USER_CONFIG);

        $data = new stdClass();
        foreach ( (array)$user_config as $i=>$v )
        {
        	//Separa a legenda do parâmetro
            list($var1, $label) = explode('|', $v);
            list($id, $perm) = explode('=', $var1);
            $id = strtoupper(trim($id));

            $value = $this->getValuePersonConfig($personId, $id);

            if ( !$onlyValues && in_array($id, array('USER_SEND_DELAYED_LOAN', 'USER_SEND_NOTIFY_AQUISITION', 'USER_SEND_DAYS_BEFORE_EXPIRED', 'USER_SEND_RECEIPT_RENEW_WEB') ) )
            {
                $data->$id = GUtil::getYesNo($value);
            }
            elseif ( $id == 'USER_DELAYED_LOAN' )
            {
                $delayedLoan   = explode(';', $value);
                $data->quantidade    = $delayedLoan[0];
                $data->periodo       = $delayedLoan[1];
                $data->$id = _M('Quantidade: @1', $this->module, $data->quantidade) . ' - ' . _M('Período: @1', $this->module, $data->periodo);
            }
            elseif ( in_array($id, array('CONFIGURE_RECEIPT_LOAN', 'CONFIGURE_RECEIPT_RETURN') ) )
            {
                if ( $id == 'CONFIGURE_RECEIPT_LOAN' )
                {
                    $print = $this->getValuePersonConfig($personId, 'MARK_PRINT_RECEIPT_LOAN');
                    $send = $this->getValuePersonConfig($personId, 'MARK_SEND_LOAN_MAIL_RECEIPT');
                    $value = $this->getReceiptConfig($print, $send);
                }
                else
                {
                    $print = $this->getValuePersonConfig($personId, 'MARK_PRINT_RECEIPT_RETURN');
                    $send = $this->getValuePersonConfig($personId, 'MARK_SEND_RETURN_MAIL_RECEIPT');
                    $value = $this->getReceiptConfig($print, $send);

                }

                $data->$id = $onlyValues ? $value : $this->getNameOfReceiptConfigure($value);
            }
            else
            {
                $data->$id = $value;
            }

        }

        return $data;
    }


    /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("personId");
        $this->setTables($this->tables);
        $this->setWhere(' personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Método que pega o valor da configuração de recibo
     * @param $print
     * @param $send
     * @return value of receipt configure
     */
    private function getReceiptConfig($print, $send)
    {
        $print = MUtil::getBooleanValue($print);
        $send = MUtil::getBooleanValue($send);

    	if ( !$print && !$send)
        {
            $value = self::NOT_SEND_MAIL;
        }
        elseif ( $print && $send )
        {
            $value = self::PRINT_AND_SEND_RECEIPT;
        }
        elseif ( $print )
        {
            $value = self::PRINT_RECEIPT;
        }
        else
        {
            $value = self::SEND_RECEIPT;
        }

        return $value;
    }


    /**
     * Método que pega as configurações de impressão e envio de recibo
     * @param valor da configuração
     * @return (object)
     */
    public function getPrintAndSendReceiptConfigure($value)
    {
    	$data = new stdClass();
    	switch ($value)
    	{
    		case self::NOT_SEND_MAIL:
    			$data->print = DB_FALSE;
    			$data->send = DB_FALSE;
    			break;

            case self::PRINT_AND_SEND_RECEIPT:
    			$data->print = DB_TRUE;
    			$data->send = DB_TRUE;
    			break;

            case self::PRINT_RECEIPT:
            	$data->print = DB_TRUE;
            	$data->send = DB_FALSE;
            	break;

            case self::SEND_RECEIPT:
            	$data->print = DB_FALSE;
            	$data->send = DB_TRUE;
            	break;
    	}
    	return $data;
    }


    /**
     * Método que retorna lista de opções de configuração de recibo
     * @return (array) lista de opções
     */
    public function listReceiptConfigure()
    {
    	if (MUtil::getBooleanValue(MARK_DONT_PRINT_SEND_RECEIPT))
    	{
	    	return array(self::NOT_SEND_MAIL=>_M("Não imprimir e enviar recibo", $this->module),
	    	             self::PRINT_AND_SEND_RECEIPT => _M('Imprimir e enviar recibo', $this->module),
                         self::PRINT_RECEIPT => _M('Imprimir recibo', $this->module),
	    	             self::SEND_RECEIPT => _M('Enviar recibo por e-mail', $this->module));
    	}
    	else
    	{
            return array(self::PRINT_AND_SEND_RECEIPT => _M('Imprimir e enviar recibo', $this->module),
                         self::PRINT_RECEIPT => _M('Imprimir recibo', $this->module),
                         self::SEND_RECEIPT => _M('Enviar recibo por e-mail', $this->module));

    	}
    }


    /**
     * Método que retorna o nome da opção
     * @param valor
     */
    private function getNameOfReceiptConfigure( $get=null)
    {
    	if (!$get)
    	{
    		return null;
    	}
    	$config = $this->listReceiptConfigure();
    	return $config[$get];
    }
}
?>
