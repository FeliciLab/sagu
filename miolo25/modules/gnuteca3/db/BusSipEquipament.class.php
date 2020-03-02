<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe Business para Equipamento SIP
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 14/11/2013
 * 
 **/

class BusinessGnuteca3BusSipEquipament extends GBusiness
{
    
    //Todos os campos do cadastro. Tela FrmSipEquipament
    public $sipEquipamentId;
    public $description;
    public $password;
    public $libraryUnitId;
    public $makeLoan;
    public $makeReturn;
    public $makeRenew;
    public $denyUserCard;
    public $offlineMode;
    public $timeOutPeriod;
    public $retriesAllow;
    public $locationformaterialmovementid;
    public $binDefault;
    public $screenMessage;
    public $printMessage;
    public $requiredpassword;
    public $sipEquipamentBinRules;
    public $psLoanlimit;
    public $psOverduelimit;
    public $psPenaltylimit;
    public $psFinelimit;
    
    
    public $locationName;
    public $libraryName;
    
    public $sipEquipamentIdS;
    public $descriptionS;
    public $passwordS;
    public $libraryUnitIdS;
    public $makeLoanS;
    public $makeReturnS;
    public $makeRenewS;
    public $denyUserCardS;
    public $offlineModeS;
    public $timeOutPeriodS;
    public $retriesAllowS;
    public $locationformaterialmovementidS;
    public $binDefaultS;
    public $screenMessageS;
    public $printMessageS;
    public $requiredpasswordS;
    public $sipEquipamentBinRulesS;
    public $psLoanlimitS;
    public $psOverduelimitS;
    public $psPenaltylimitS;
    public $psFinelimitS;
        
    public $BusSession;
    public $BusSipEquipamentBinRules;
    public $BusSipEquipamentStatusHistory;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'description,
                           password, 
                           libraryUnitId,
                           makeLoan, 
                           makeReturn, 
                           makeRenew, 
                           denyUserCard, 
                           offlineMode,
                           timeOutPeriod, 
                           retriesAllow, 
                           locationformaterialmovementid,
                           binDefault,
                           screenMessage, 
                           printMessage,
                           requiredpassword,
                           psLoanlimit,
                           psOverduelimit,
                           psPenaltylimit,
                           psFinelimit';
        
        $this->id = 'sipEquipamentId';
        $this->columns  = 'sipEquipamentId, ' . $this->colsNoId;
        $this->tables   = 'gtcSipEquipament';

        $this->BusSession = $this->MIOLO->getBusiness($this->module, 'BusSession');
        $this->BusSipEquipamentBinRules = $this->MIOLO->getBusiness($this->module, 'BusSipEquipamentBinRules');
        $this->BusSipEquipamentStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusSipEquipamentStatusHistory');
    }


    /**
     * List all records from the table handled by the class
     *
     * @DEPRECATED usar o método BusBond::listBond() 
     * 
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listSipEquipament($forCataloge = false)
    {
        //return 'Not implemented';
        $data = array();

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $forCataloge);

        if(!$forCataloge || !$rs)
        {
            return $rs;
        }

        foreach ($rs as $i => $v)
        {
            $r[$i]->option      = $v->sipEquipamentId;
            $r[$i]->description = $v->libraryunitid;
        }

        return $r;
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
    public function getSipEquipament($sipEquipamentId)
    {
        $data = array($sipEquipamentId);
        $this->clear();
        $this->setColumns('A.sipEquipamentId,
                           A.description,
                           A.password, 
                           A.libraryUnitId,
                           A.makeLoan, 
                           A.makeReturn, 
                           A.makeRenew, 
                           A.denyUserCard, 
                           A.offlineMode,
                           A.timeOutPeriod, 
                           A.retriesAllow, 
                           A.locationformaterialmovementid,
                           A.binDefault,
                           A.screenMessage, 
                           A.printMessage,
                           A.requiredpassword,
                           B.libraryName,
                           C.description as locationName,
                           A.psLoanlimit,
                           A.psOverduelimit,
                           A.psPenaltylimit,
                           A.psFinelimit');
        
        $this->setTables('gtcsipequipament A 
                LEFT JOIN gtclibraryunit B
                       ON (A.libraryunitid = B.libraryunitid)
                LEFT JOIN gtclocationformaterialmovement C
                       ON (A.locationformaterialmovementid = C.locationformaterialmovementid)');
        
        $this->setWhere('A.sipEquipamentId = ?');
        
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);
        
        $this->setData($rs[0]);
        
        $this->BusSipEquipamentBinRules->sipEquipamentId = $this->sipEquipamentId;
        $this->sipEquipamentBinRules = $this->BusSipEquipamentBinRules->searchSipEquipamentBinRules(TRUE);
        
        return $this;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchSipEquipament()
    {
        $this->clear();
        
        //Setando o where para procurar por sipEquipamentId
        if ( $v = $this->sipEquipamentIdS )
        {
            $this->setWhere('sipEquipamentId = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(unaccent(A.description)) LIKE lower(unaccent(?))');
            $data[] = $v . '%';
        }
        
        // Comentado para poder vizualizar todos os cadastros de SipEquipament
        if ( $v = $this->libraryUnitIdS )
        {
            $this->setWhere('libraryunitid = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->makeLoanS )
        {
            $this->setWhere('makeLoan = ?');
            $data[] = $v;
        }
        if ( $v = $this->makeReturnS )
        {
        	$this->setWhere('makeReturn = ?');
        	$data[] = $v;
        }

        if ( $v = $this->makeRenewS )
        {
        	$this->setWhere('makeRenew = ?');
        	$data[] = $v;
        }
        
        if ( $v = $this->denyUserCardS )
        {
        	$this->setWhere('denyUserCard = ?');
        	$data[] = $v;
        }
        
        if ( $v = $this->offlineModeS )
        {
        	$this->setWhere('offlineMode = ?');
        	$data[] = $v;
        }
        
        if ( $v = $this->requiredpasswordS)
        {
            $this->setWhere('requiredpassword = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->binDefaultS)
        {
            $this->setWhere('binDefault = ?');
        	$data[] = $v;
        }
        
        // Comentado para poder vizualizar todos os cadastros de SipEquipament
        if ( $v = $this->locationformaterialmovementidS )
        {
        	$this->setWhere('C.locationformaterialmovementid = ?');
        	$data[] = $v;
        }
        
        if ( $v = $this->psLoanlimitS)
        {
            $this->setWhere('psLoanlimit = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->psOverduelimitS)
        {
            $this->setWhere('psOverduelimit = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->psPenaltylimitS)
        {
            $this->setWhere('psPenaltylimit = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->psFinelimitS)
        {
            $this->setWhere('psFinelimit = ?');
            $data[] = $v;
        }
        
        $this->setColumns('A.description,
                           A.sipEquipamentId, 
                           B.libraryname,
                           A.makeLoan, 
                           A.makeReturn, 
                           A.makeRenew, 
                           A.denyUserCard, 
                           A.offlineMode,
                           A.requiredpassword,
                           C.description,
                           A.psLoanlimit,
                           A.psOverduelimit,
                           A.psPenaltylimit,
                           A.psFinelimit');
        
       $this->setTables($this->tables . ' A 
                        INNER JOIN gtclibraryUnit B 
                              USING (libraryunitid)
                        INNER JOIN gtclocationformaterialmovement C
                              ON (A.locationformaterialmovementid = C.locationformaterialmovementid)');
        

        $this->setOrderBy('sipEquipamentId');
        $sql = $this->select($data);
        
        $rs  = $this->query($sql);

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
    public function insertSipEquipament()
    {
        $data = array(
            $this->sipEquipamentId,
            $this->description,
            $this->password,
            $this->libraryUnitId,
            $this->makeLoan,
            $this->makeReturn,
            $this->makeRenew,
            $this->denyUserCard,
            $this->offlineMode,
            $this->timeOutPeriod,
            $this->retriesAllow,
            $this->locationformaterialmovementid,
            $this->binDefault,
            $this->screenMessage,
            $this->printMessage,
            $this->requiredpassword,
            $this->psLoanlimit,
            $this->psOverduelimit,
            $this->psPenaltylimit,
            $this->psFinelimit
        );
        
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        
        $rs  = $this->execute($sql);
        
        if ($rs && $this->sipEquipamentBinRules)
            {
                foreach ($this->sipEquipamentBinRules as $value)
                {
                    $this->BusSipEquipamentBinRules->setData($value);
                    $this->BusSipEquipamentBinRules->sipEquipamentId = $this->sipEquipamentId;
                    $this->BusSipEquipamentBinRules->insertSipEquipamentBinRules();
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
    public function updateSipEquipament()
    {
        //------ Update dos campos, fora a Repetitive
        //
        $data = array(
            $this->description,
            $this->password,
            $this->libraryUnitId,
            $this->makeLoan,
            $this->makeReturn,
            $this->makeRenew,
            $this->denyUserCard,
            $this->offlineMode,
            $this->timeOutPeriod,
            $this->retriesAllow,
            $this->locationformaterialmovementid,
            $this->binDefault,
            $this->screenMessage,
            $this->printMessage,
            $this->requiredpassword,
            $this->psLoanlimit,
            $this->psOverduelimit,
            $this->psPenaltylimit,
            $this->psFinelimit,
            $this->sipEquipamentId
        );
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('sipEquipamentId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);
        
        if ( $rs )
        {
            $this->BusSipEquipamentBinRules->deleteSipEquipamentBinRulesForSipEquipamentId($this->sipEquipamentId);
       
            if ( $this->sipEquipamentBinRules)
            {
                $res->sipEquipamentId = $this->sipEquipamentId;
                
                foreach ($this->sipEquipamentBinRules as $value)
                {
                    $this->BusSipEquipamentBinRules->setData($value);                    
                    $this->BusSipEquipamentBinRules->sipEquipamentId = $this->sipEquipamentId;
                    $this->BusSipEquipamentBinRules->insertSipEquipamentBinRules();
                }
            }
        }
        
        return $rs;
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
    public function deleteSipEquipament($sipEquipamentId)
    {
        //Instancia um objeto session para verificar se há alguma aberta para o equipamento
        $this->BusSession->sipequipamentId = $this->sipEquipamentId;
        $this->BusSession->isClosed = DB_FALSE;
    	$haveS = $this->BusSession->searchSession();
        
        //Verifica se existe registro na sessão em aberto para o equipamento
        
        if(!$haveS)
        {
            $this->BusSipEquipamentBinRules->deleteSipEquipamentBinRulesForSipEquipamentId($sipEquipamentId);
            $this->BusSipEquipamentStatusHistory->deleteBySipEquipament($sipEquipamentId);

            $this->clear();

            $tables  = 'gtcSipEquipament';
            $where   = 'sipEquipamentId = ?';
            $data = array($sipEquipamentId);

            $this->setColumns($columns);
            $this->setTables($tables);
            $this->setWhere($where);
            $sql = $this->delete($data);

            $rs  = $this->execute($sql);

            return $rs;
        }
        else
        {
            throw new Exception("Este equipamento possui sessão aberta. Não poderá deletar antes de encerrar a sessão.");
        }
    }
    
    /*
     * Neste método será implementado o retorno para o webservice
     * Tal retorno será uma String, com seus campos separados por PIPE '|'
     * 
     * OBS: Ainda não esta pronto. Necessário ainda algumas implementações
     * 
     * Método Status($termID, $status, $maxPtrWidth, $protocol) do webservice
     * 
     */
        
    public function checkStatus($termID)
    {
        //Define o Numero do Equipamento Sip a ser trabalhado
        $test = $this->getSipEquipament($termID);
        
        //Define o array em que será retornado os dados
        $retorno = array();
        
        
        //Respondendo se o Gnuteca esta Online
        $retorno['online'] = 'Y';
        
        
        //Indica se esta habilitado para realizar checkin (devolução :: makeReturn)
        $retorno['checkinOk'] = GUtil::convertBooleanToString($this->makeReturn);
        
        
        //Testa se esta habilitado para realizar checkout (empréstimo :: makeLoan)
        $retorno['checkoutOk'] = GUtil::convertBooleanToString($this->makeLoan);
        
        
        //Testa se esta habilitado para realizar renovação
        $retorno['ACSRenPolicy'] = GUtil::convertBooleanToString($this->makeRenew);
        
        
        //Testa se esta habilitado à bloquear cartões de usuário
        $retorno['statusUpdateOk'] = GUtil::convertBooleanToString($this->denyUserCard);
        
        
        //Testa se esta habilitado a trabalhar no modo offline
        $retorno['offlineOk'] = GUtil::convertBooleanToString($this->offlineMode);
        
        
        //Testa a quantidade de décimos de segundo que será esperada por uma transação
        if($this->timeOutPeriod >= 999)
        {
            $retorno['timeoutPeriod'] = "999";
        }else
        {
            if(is_null($this->timeOutPeriod))
            {
                $retorno['timeoutPeriod'] = '999';
            }else{
                $retorno['timeoutPeriod'] = $this->timeOutPeriod;
            }
        }
        
        
        //Número de retentativas realizadas pelo sistema
        if($this->retriesAllow >= 999)
        {
            $retorno['retriesAllowed'] = "999";
        }else
        {
            if(is_null($this->retriesAllow))
            {
                $retorno['retriesAllowed'] = '999';
            }
            else
            {
                $retorno['retriesAllowed'] = $this->retriesAllow;
            }
        }
        
        
        //Concatena com a Data Atual no formato YYYYMMDDHHMMSS
        $dataC = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $v1 = str_replace("-", "", $dataC);
        $v2 = str_replace(" ", "", $v1);
        $v3 = str_replace(":", "", $v2);
        $retorno['dateTime'] = $v3;

        
        //Concatena o identificador único de biblioteca 
        $retorno['institutionId'] = $this->libraryUnitId;
        
        
        //Concatena o Nome da biblioteca
        $retorno['libraryName'] = $this->libraryName;

        
        //Nome da localização desse terminal
        $retorno['terminalLocation'] = $this->locationName;

        
        //Mensagem de Screen
        //if(empty($this->screenMessage))
        //{
            //$this->screenMessage = 'null';
        //}
        $retorno['screenMsg'] = $this->screenMessage;
        
        //Print de Mensagem
        //if(empty($this->printMessage))
        //{
        //    $this->printMessage = 'null';
        //}
        $retorno['printMsg'] = $this->printMessage;
        
        return $retorno;
    }
    
    /**
    * Método responsável pela autenticação de equipamentos de auto-empréstimo.
    *
    * @param: int $login Indentificação do equipamento.
    * @param: string $password Senha para autenticar equipamento.
    * @return boolean true Retorna verdadeiro quando a autenticação ocorre com sucesso.
    */
   public function authenticate($login, $password)
   {
       //Monta o objeto com o termID do $login
       $this->getSipEquipament($login);
       
       //Caso o sipEquipamentId não for nulo
       if(!is_null($this->sipEquipamentId))
       {
           //Verifica se o Password do objeto possui senha
           if( is_null($this->password) )
           {
               //O campo de password é opcional, ou seja, pode ser deixado em branco
               //Por isso retornamos true, caso esteja em branco
               return true;
           }
           elseif ( $this->requiredpassword == DB_FALSE )
           {
                return true;
           }
           else
           {
               //Caso não for em branco, compara com a variável passada como parâmetro
               //Retornando se esta correta.
               if($this->password == $password)
               {
                   return true;
               }
               else
               {
                   return false;
               }
           }
       }else
       {
           //Caso o ID passado for nulo
           return false;
       }
   }
}
?>
