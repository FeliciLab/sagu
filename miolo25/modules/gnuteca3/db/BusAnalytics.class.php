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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 28/04/2011
 *
 * */
$MIOLO = MIOLO::getInstance();
$MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');

class BusinessGnuteca3BusAnalytics extends GBusiness
{

    const LOG_LEVEL_OFF = 0;
    const LOG_LEVEL_DEFAULT = 1;
    const LOG_LEVEL_MAX = 2;
    const ACCESS_TYPE_INNER = 1;
    const ACCESS_TYPE_OUTER = 2;
    const ACCESS_TYPE_SEARCH_CONTENT = 3;
    const ACCESS_TYPE_ERROR = 4;

    public $analyticsId;
    public $query;
    public $action;
    public $__action; //o action tem underline em função de action ser uma variável do form
    public $event;
    public $__event;
    public $libraryUnitId;
    public $operator;
    public $personId;
    public $time;
    public $ip;
    public $browser;
    public $logLevel;
    public $accessType;
    public $menu;
    public $timeSpent;
    public $analyticsIdS;
    public $queryS;
    public $__actionS; //o action tem underline em função de action ser uma variável do form
    public $__eventS;
    public $libraryUnitIdS;
    public $operatorS;
    public $personIdS;
    public $timeS;
    public $ipS;
    public $browserS;
    public $logLevelS;
    public $accessTypeS;
    public $menuS;
    public $beginTimeSpentS;
    public $endTimeSpentS;
    public $beginDateS;
    public $beginDateTimeS;
    public $endDateS;
    public $endDateTimeS;

    function __construct()
    {
        //define a tabela e os campos padrão do bus
        parent::__construct('gtcAnalytics', 'analyticsId', 'query,
                            action,
                            event,
                            libraryUnitId,
                            operator,
                            personId,
                            time,
                            ip,
                            browser,
                            logLevel,
                            accessType,
                            menu,
                            timeSpent'
        );
    }

    /**
     * Monta um array pronto para MSelection.
     * @return array
     */
    public function listLogLevel()
    {
        return array(self::LOG_LEVEL_OFF => _M('Desligado', $this->module),
            self::LOG_LEVEL_DEFAULT => _M('Padrão', $this->module),
            self::LOG_LEVEL_MAX => _M('Completo', $this->module)
        );
    }

    /**
     * Monta um array pronto para MSelection
     * @return array
     */
    public function listAccessType()
    {
        return array(self::ACCESS_TYPE_INNER => _M('Interno', $this->module),
            self::ACCESS_TYPE_OUTER => _M('Externo', $this->module),
            self::ACCESS_TYPE_SEARCH_CONTENT => _M('Busca', $this->module),
            self::ACCESS_TYPE_ERROR => _M('Erro', $this->module)
        );
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     * */
    public function listAnalytics($object = FALSE)
    {
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
     * */
    public function getAnalytics($id)
    {
        $result = $this->autoGet($id);
        
        $result->__event = $result->event;
        $result->__action = $result->action;
        
        return $result;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchAnalytics($toObject = false)
    {
        
        //o action tem underline em função de action ser uma variável do form
        $this->action = $this->__actionS;


        //here you can pass how many where you want, or use filters
        $filters = array(
            'analyticsId' => 'equals',
            'query' => 'ilike',
            'action' => 'ilike',
            'operator' => 'ilike',
            'personId' => 'equals',
            'ip' => 'equals',
            'browser' => 'ilike',
            'accessType' => 'equals',
            'menu' => 'ilike',
            'timeSpent' => 'equals'
        );
        
        $this->clear();

        $args = $this->addFilters($filters);

        if ($this->libraryUnitIdS)
        {
            $this->setWhere(' L.libraryUnitId = ? ');
            $args[] = $this->libraryUnitIdS;
        }

        if ($this->logLevelS === '0' || ($this->logLevelS > 0)) //Garantir que se for 0 o valor, ele entrará no if
        {
            $this->setWhere(' loglevel = ? ');
            $args[] = $this->logLevelS;
        }

        if ($this->__eventS )
        {
            $this->setWhere(' lower(unaccent(event)) ilike lower(unaccent(?)) ');
            $args[] = $this->__eventS;
        }
        
        $dateTimeBegin = explode(' ', $this->beginDateTimeS);
        
        if ( !strlen($dateTimeBegin[1]) )
        {
            $dateTimeBegin[1] = '00:00:00';
        }
        
        $dateBegin = $dateTimeBegin[0] . ' ' . $dateTimeBegin[1];
        
        $dateTimeEnd = explode(' ', $this->endDateTimeS);

        if ( !strlen($dateTimeEnd[1]) )
        {
            $dateTimeEnd[1] = '23:59:59';
        }
        
        $dateEnd = $dateTimeEnd[0] . ' ' . $dateTimeEnd[1];
        
        if ( strlen($dateTimeBegin[0]) )
        {
            if ( strlen($dateTimeEnd[0]) )
            {
               $this->setWhere('time BETWEEN ? AND ?');
               $args[] = $dateBegin;
               $args[] = $dateEnd;
            }
            else
            {
                $this->setWhere('time >= ?');
                $args[] = $dateBegin;
            }
        }
        elseif ( strlen($dateTimeEnd[0]) )
        {
            $this->setWhere('time <= ?');
            $args[] = $dateEnd;
        }

        if ($this->beginTimeSpentS)
        {
            $this->setWhere(' timespent >= ? ');
            $args[] = $this->beginTimeSpentS;
        }

        if ($this->endTimeSpentS)
        {
            $this->setWhere(' timespent <= ? ');
            $args[] = $this->endTimeSpentS;
        }

        $this->setColumns(str_replace('libraryUnitId', 'L.libraryname', $this->columns)); //Seta colunas, trocando libraryUnitId por L.libraryname, isto foi feito por causa de um leftjoin para mostrar o nome da unidade e não o id.
        $this->setTables($this->tables . " A LEFT JOIN gtclibraryunit L ON A.libraryunitid = L.libraryunitid");
        return $this->query($this->select($args), $toObject);
        
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     * */
    public function insertAnalytics()
    {
        // Caso específico da minha biblioteca.
        if (Gutil::getAjaxFunction() == 'subForm')
        {
            $this->action = 'main:search:myLibrary';
            $this->event = Gutil::getAjaxEventArgs();
            $this->menu = 'Minha biblioteca';
        }
    
        $this->timeSpent = str_replace(",", ".", $this->timeSpent);
        
        return $this->autoInsert();
    }

    /**
     * Função que insere erros no analytics de forma padronizada
     * 
     * @param string $msg mensagem que será gravada
     * @param float $timeSpent  tempo gasto até a mensagem ser mostrada
     */
    public function insertError($msg, $timeSpent)
    {
        $this->accessType = BusinessGnuteca3BusAnalytics::ACCESS_TYPE_ERROR;
        $this->logLevel = BusinessGnuteca3BusAnalytics::LOG_LEVEL_DEFAULT;
        $this->menu = _M('ERRO', 'gnuteca3');
        $this->event = $msg;
        $this->timeSpent = $timeSpent;
        $this->insertAnalytics();
    }

    /**
     * Update data from a specific record
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updateAnalytics()
    {
        // Caso específico da minha biblioteca.
        if (Gutil::getAjaxFunction() == 'subForm')
        {
            $this->action = 'main:search:myLibrary';
            $this->event = Gutil::getAjaxEventArgs();
            $this->menu = 'Minha biblioteca';
        }
        
        $this->timeSpent = str_replace(",", ".", $this->timeSpent);

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
     * */
    public function deleteAnalytics($holidayId)
    {
        return $this->autoDelete($holidayId); //aceita vários id separados por vírgula
    }

    /**
     * Lista os operadores ativos nos últimos $minutes minutos.
     *
     * @param integer $minutes tempo em minutos para considerar o usuário ativo
     */
    public function listActiveOperators($minutes = 3)
    {
        $operators = $this->query("SELECT DISTINCT operator FROM gtcAnalytics WHERE time between now() -  interval '$minutes minutes' and now();");

        $arrayOperators = array();
        foreach ($operators as $i => $operator)
        {
            if (strlen($operator[0]) > 0)
            {
                $arrayOperators[] = $operator;
            }
        }

        return $arrayOperators;
    }

    /**
     * Lista as pessoas ativas nos últimos $minutes minutos.
     *
     * @param integer $minutes tempo em minutos para considerar o usuário ativo
     */
    public function listActivePersons($minutes = 3)
    {
        return $this->query("SELECT DISTINCT a.personId, name FROM gtcAnalytics A LEFT JOIN ONLY basPerson B ON (a.personId = b.personId)  WHERE time between now() -  interval '$minutes minutes' and now();");
    }
    
    /**
     * Obtém os dados para estátistica em tempo real.
     * 
     * @return stdClass Dados estatisticos.
     */
    public static function getRealTimeAnalytics()
    {
        $MIOLO = MIOLO::getInstance();
        $data = new stdClass();
        $data->action = MIOLO::_REQUEST('action');
        $data->time = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
        $data->query = $_SERVER['QUERY_STRING'];
        $data->event = GUtil::getAjaxFunction();
        $data->libraryUnitId = GOperator::getLibraryUnitLogged();
        $data->operator = $MIOLO->getLogin()->id;
        $data->personId = BusinessGnuteca3BusAuthenticate::getUserCode();
        $data->ip = GServer::getRemoteAddress();
        $data->browser = $_SERVER['HTTP_USER_AGENT'];
        $data->logLevel = BusinessGnuteca3BusAnalytics::LOG_LEVEL_DEFAULT;
        $data->accessType = BusinessGnuteca3BusAnalytics::ACCESS_TYPE_INNER;
        $data->menu = implode(":", $_SESSION['menuItems'][MIOLO::_REQUEST('action')]->label);
        
        return $data;
    }

}

?>