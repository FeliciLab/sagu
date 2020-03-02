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
 * Lookup
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 *
 * @since
 * Class created on 07/08/2008
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->getClass('gnuteca3', 'GSipCirculation');
$MIOLO->uses('db/BusAuthenticate.class.php', 'gnuteca3');
class BusinessGnuteca3Lookup extends MBusiness
{
    public $MIOLO;
    public $module;
    public $db;
    public $gridListing = 15;
    public $forRepetitiveField;

    function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = 'gnuteca3';
        $this->db     = $this->MIOLO->getDatabase($this->module);

        $local = str_replace("/db", "/handlers", dirname(__FILE__));
        require_once("$local/define.inc.php");
        require_once("$local/debugFunctions.inc.php");

        $this->MIOLO->getClass('gnuteca3', 'GBusiness');
        $this->MIOLO->getClass('gnuteca3', 'controls/GSelection');
        $this->MIOLO->getClass('gnuteca3', 'GOperator');
    }

    /**
     * Define se é para usar no repetitive field
     *
     * @param boolean $value
     * @param boolean $returnAll normalmente o autocomplete retorna só um mas no caso do campo de dicionário precisamos retornar vários registros
     */
    public function setForRepetitiveField( $value = true , $returnAll = false)
    {
    	$this->forRepetitiveField = $value;
        $this->returnAll = $returnAll;
    }

    function setContext( &$context, $sql, $database = null )
    {
        $MIOLO  = MIOLO::getInstance();
        $filter = MIOLO::_REQUEST('filter');
        $sql    = str_replace('?', addslashes($filter) , $sql) ; //trata acentos

        $database = $database ? $database : MIOLO::getCurrentModule();
        $db     = $MIOLO->getDatabase( $database );
        $result = $db->query($sql);
        $result = $this->returnAll ? $result->result : $result->result[0];
            
        // Se for para repetitive field retorna o resultado pra função e seta na classe.
        if ( $this->forRepetitiveField )
        {
            $this->result = $result;
            return $result;
        }
        else
        {
            $context->setAutoComplete( $result );
        }
    }
    
    
    function setContextCostCenter( &$context, $sql, $database = null )
    {
        $MIOLO  = MIOLO::getInstance();
        $filter = MIOLO::_REQUEST('filter');

        if (is_numeric($filter))
        {
            $sql    = str_replace('?', addslashes($filter) , $sql) ; //trata acentos
        }
        else
        {
            $sql    = str_replace('?', addslashes("'" .$filter . "'") , $sql) ; //trata acentos
        }
        
        try
        {
            $database = $database ? $database : MIOLO::getCurrentModule();
            $db     = $MIOLO->getDatabase( $database );
            $result = $db->query($sql);
            $result = $this->returnAll ? $result->result : $result->result[0];
        }
        catch ( Exception $e )
        {}
        
        // Mantém o valor do filtro caso não retorne valores da base de dados.
        if ( !$result )
        {
            $result = array($filter, '');
        }    
            
        // Se for para repetitive field retorna o resultado pra função e seta na classe.
        if ( $this->forRepetitiveField )
        {
            $this->result = $result;
            return $result;
        }
        else
        {
            $context->setAutoComplete( $result );
        }
    }
    

    /**
    * Autocomplete for person
    **/
    public function autoCompletePerson( &$context )
    {
        $sql = 'SELECT  personid,
                        name,
                        email
            FROM ONLY   basperson
            LEFT JOIN  gtclibperson LP USING (personid)
                  WHERE ';
        
        if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE )
        {
            //FIXME quando passa dois filtros, eles não funcionam com '?' automaticamente
            $login = MIOLO::_REQUEST('filter0');
            $base = MIOLO::_REQUEST('filter1');
           
            if ( (strlen($login) > 0) && (strlen($base) > 0) )
            {
                $sql .= "login = '{$login}'AND PL.baseLdap = '{$base}'";
            }
            else
            {
                $sql .= "login = '?'";
            }
       }
       else if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN )
       {
           $sql .= "login = '?'";
       }
       else
       {
           $codigo = MIOLO::_REQUEST('filter');
           
           if ( is_numeric($codigo) )
           {
               $sql .= "personId = '?'";
           }
           else
           {
               $sql .= "personId = 0 LIMIT 0";
           }
       }

        $this->setContext($context, $sql);
    }

    function LookupPerson( &$lookup )
    {
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )        {
            $lookup->addFilterField( new MTextField( 'loginS', null, _M( 'Login', $this->module ), 8 ) );
        }

        $lookup->addFilterField( new MTextField( 'nameS', null, _M( 'Nome', $this->module ), 40 ) );
        
        $bases =  BusinessGnuteca3BusAuthenticate::listMultipleLdap();
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
        {
            $lookup->addFilterField(  new GSelection('baseLdapS', '', _M('Base', $this->module), $bases) );
        }            

        $columns = array(
            new DataGridColumn('personid', _M('Código', $this->module), 'left', true, null, true),
            new DataGridColumn('name',     _M('Nome', $this->module), 'left', true, null, true)
        );

        $name     = MIOLO::_REQUEST('nameS');
        $personId = MIOLO::_REQUEST('filler') ? MIOLO::_REQUEST('filler') : MIOLO::_REQUEST('personIdL');
        $loginS   = MIOLO::_REQUEST('loginS');
        $baseLdap = MIOLO::_REQUEST('baseLdapS');
        
        $sql = new MSQL('personid, name, LP.baseLdap, email
                            FROM ONLY basperson 
                      LEFT JOIN gtclibperson LP USING (personid)');        
        
        if ( strlen( $name ) > 0 )
        {
            $sql->setWhere("lower(unaccent(name)) LIKE lower(unaccent('%" . $name . "%'))");
        }

        if ( strlen( $personId ) > 0 )
        {
            $sql->setWhere("personId = $personId ");
        }
        
        if ( strlen($loginS) > 0 )
        {
            $sql->setWhere("login = '{$loginS}' ");
        }
        
        if ( strlen($baseLdap) > 0 )
        {
            $sql->setWhere("LP.baseLdap = '{$baseLdap}'");
        }
        
        $sql->setOrderBy( 'name' );

        $lookup->setGrid( $sql, $columns );
    }

    /**
    * Autocomplete for person
    **/
    public function autoCompletePersonName( &$context )
    {
        $sql = 'SELECT  personId,
                        name
             FROM ONLY  basperson
                 WHERE  personid = ?';

        $this->setContext($context, $sql);
    }

    function LookupPersonName( &$lookup )
    {
        $person = str_replace("'","''", MIOLO::_REQUEST( 'personName' ) );

        $lookup->addFilterField( new MTextField( 'personName', $person, _M('Nome', $this->module ), 40 ) );

        $columns = array(
            new DataGridColumn('personid', _M('Código', $this->module), 'left', true, null, false),
            new DataGridColumn('name',     _M('Nome', $this->module), 'left', true, null, true)
        );

        $sql = new MSQL( 'personId, name',
                                        'basPerson', '( personId in(
                                        SELECT personId
                                        FROM gtcRequestChangeExemplaryStatus as request
                                       ))'
                       );
        if ( strlen($person) > 0 )
        {
            $sql->setWhere("lower(name) LIKE lower('%" . $person . "%')");
        }
        
        $sql->setOrderBy('name');     
        $lookup->setGrid( $sql, $columns );
    }
    
    /**
    * Autocomplete for person
    **/
    public function autoCompletePersonIsOperator( &$context )
    {
        $sql = "SELECT DISTINCT P.personId, 
                                P.name
                      FROM ONLY basPerson P 
                     INNER JOIN basPersonLink PL 
                             ON P.personId = PL.personId 
                     INNER JOIN basLink L 
                             ON L.linkId = PL.linkId 
                            AND (PL.dateValidate >= now()::date OR PL.dateValidate IS NULL)
                            AND  L.isoperator = 't'
                            AND P.personId = '?'";
        
        $this->setContext($context, $sql);
    }

    function LookupPersonIsOperator( &$lookup )
    {
        $person = str_replace("'","''", MIOLO::_REQUEST( 'personName' ) );

        $lookup->addFilterField( new MTextField( 'personName', $person, _M('Nome', $this->module ), 40 ) );

        $columns = array(
            new DataGridColumn('personid', _M('Código', $this->module), 'left', true, null, false),
            new DataGridColumn('name',     _M('Nome', $this->module), 'left', true, null, true)
        );

        
        $sql = new MSQL( "DISTINCT P.personId, 
                                   P.name
                         FROM ONLY basPerson P 
                        INNER JOIN basPersonLink PL 
                                ON P.personId = PL.personId 
                        INNER JOIN basLink L 
                                ON L.linkId = PL.linkId 
                               AND (PL.dateValidate >= now()::date OR PL.dateValidate IS NULL)
                               AND  L.isoperator = 't'");
        
        if ( strlen($person) > 0 )
        {
            $sql->setWhere("lower(P.name) LIKE lower('%" . $person . "%')");
        }
        
        $sql->setOrderBy('P.personid');
        $lookup->setGrid( $sql, $columns );
    }
    
    function LookupPersonCongelado( &$lookup )
    {
        $person = str_replace("'","''", MIOLO::_REQUEST( 'personName' ) );

        $lookup->addFilterField( new MTextField( 'personName', $person, _M('Nome', $this->module ), 40 ) );

        $columns = array(
            new DataGridColumn('personid', _M('Código', $this->module), 'left', true, null, false),
            new DataGridColumn('name',     _M('Nome', $this->module), 'left', true, null, true)
        );

        
        $sql = new MSQL( "DISTINCT P.personId, 
                                   P.name
                         FROM ONLY basPerson P 
                        INNER JOIN basPersonLink PL 
                                ON P.personId = PL.personId 
                        INNER JOIN basLink L 
                                ON L.linkId = PL.linkId ");
        
        $sql->setWhere("PL.linkid IN (SELECT distinct baslinkid FROM gtcrequestchangeexemplarystatusaccess WHERE exemplarystatusid = " . DEFAULT_EXEMPLARY_STATUS_CONGELADO . ")");
        
        if ( strlen($person) > 0 )
        {
            $sql->setWhere("lower(P.name) LIKE lower('%" . $person . "%')");
        }
        
        $sql->setOrderBy('P.personid');

        $lookup->setGrid( $sql, $columns );
    }
    
    public function autoCompletePersonCongelado( &$context )
    {
        $sql = "SELECT DISTINCT P.personId, 
                                P.name
                      FROM ONLY basPerson P 
                     INNER JOIN basPersonLink PL 
                             ON P.personId = PL.personId 
                     INNER JOIN basLink L 
                             ON L.linkId = PL.linkId 
                            AND PL.linkid IN (SELECT distinct baslinkid FROM gtcrequestchangeexemplarystatusaccess WHERE exemplarystatusid = " . DEFAULT_EXEMPLARY_STATUS_CONGELADO . ") " .
                            "AND P.personId = '?'";
        
        $this->setContext($context, $sql);
    }

    /**
    * Lookup for person in material circulation
    **/
    public function autoCompletePersonMaterialCirculation( &$context )
    {
        $sql = 'SELECT personId, name, email
             FROM ONLY basperson 
           LEFT JOIN gtclibperson LP USING (personid) 
                WHERE ';
        
       if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE )
       {
           //FIXME quando passa dois filtros, eles não funcionam com '?' automaticamente
           $login = MIOLO::_REQUEST('filter0');
           $base = MIOLO::_REQUEST('filter1');
           $sql .= "login = '{$login}' AND LP.baseLdap = '{$base}'";
       }
       else if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN )
       {
           $sql .= "login = '?'";
       }
       else
       {
           if(GSipCirculation::usingSmartReader())
           {
               $login = MIOLO::_REQUEST('filter');
               
               $sql .= "personId::text = '{$login}' OR login = '{$login}'";
               
           }else
           {
               $sql .= "personId = '?'";
           }
       }
       
       $this->setContext($context, $sql);
    }


    function LookupPersonMaterialCirculation( &$lookup )
    {
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
        {
            $sql = new MSQL('login, name, LP.baseLdap, email
                       FROM ONLY basperson 
                       LEFT JOIN gtclibperson LP USING (personid)');
            
            $lookup->addFilterField( new MTextField( 'loginS', null, _M( 'Login', $this->module ), 8 ) );
            $labelFirstColumn = _M('Login', $this->module); 
            
            //Adiciona campo de nome
            $lookup->addFilterField( new MTextField( 'nameS', null, _M( 'Nome', $this->module ), 40 ) );
        }
        else
        {
            //Adiciona filtro de código da pessoa
            $lookup->addFilterField( new MTextField( 'personIdL', null, _M( 'Código', $this->module ), 8 ) );
            
            //Caso utilizar leitor de cartão (Implementação versão 3.8)
            if(GSipCirculation::usingSmartReader())
            {
                //Adiciona campo de nome
                $lookup->addFilterField( new MTextField( 'nameS', null, _M( 'Nome', $this->module ), 40 ) );
                
                //Adiciona campo de login (código do cartão)
                $lookup->addFilterField( new MTextField( 'loginS', null, _M( 'Login', $this->module ), 20 ) );
                
                //Para o select, seleciona personid, nome e login
                $sql = new MSQL( 'personId,name,login,email', ' ONLY basPerson' );
            }
            else
            {
                //Else é o método tradicional até a versão 3.7
                
                $lookup->addFilterField( new MTextField( 'nameS', null, _M( 'Nome', $this->module ), 40 ) );
                $sql = new MSQL( 'personId,name,email', ' ONLY basPerson' );
            }
            
            $labelFirstColumn = _M('Código', $this->module); 
        }
        
        
        
        $bases =  BusinessGnuteca3BusAuthenticate::listMultipleLdap();
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
        {
            $lookup->addFilterField(  new GSelection('baseLdapS', '', _M('Base', $this->module), $bases) );
        }            

        //Alteração para montar utilizando cartão
        if(GSipCirculation::usingSmartReader())
        {
            $columns = array(
                new DataGridColumn('personid',  $labelFirstColumn,        'left', true, null, true),
                new DataGridColumn('name',     _M('Nome', $this->module), 'left', true, null, true),
                new DataGridColumn('login', _M('Login', $this->module), 'left', true, null, true)
            );
        }
        else
        {
            $columns = array(
                new DataGridColumn('personid',  $labelFirstColumn,        'left', true, null, true),
                new DataGridColumn('name',     _M('Nome', $this->module), 'left', true, null, true)
            );
        }

        $name     = MIOLO::_REQUEST('nameS');
        $personId = MIOLO::_REQUEST('filler') ? MIOLO::_REQUEST('filler') : MIOLO::_REQUEST('personIdL');
        $loginS   = MIOLO::_REQUEST('loginS');
        $baseLdap = MIOLO::_REQUEST('baseLdapS');

        if ( strlen( $name ) > 0 )
        {
            $sql->setWhere("lower(unaccent(name)) LIKE lower(unaccent('%" . $name . "%'))");
        }

        if ( strlen( $personId ) > 0 )
        {
            $sql->setWhere("personId = $personId ");
        }
        
        if ( strlen($loginS) > 0 )
        {
            $sql->setWhere("login = '{$loginS}' ");
        }
        
        if ( strlen($baseLdap) > 0 )
        {
            $sql->setWhere("LP.baseLdap = '{$baseLdap}'");
        }
        
        $sql->setOrderBy( 'name' );
        
        $lookup->setGrid($sql, $columns);
    }
    

    /**
    * Autocomplete for person
    **/
    public function autoCompleteActivePerson( &$context )
    {
        $sql = 'SELECT personId, 
                       name,
                       activelink, 
                       L.description,
                       level
                  FROM ( SELECT P.personId, 
                                P.name, 
                                min( L.level),
                                L.linkid as activelink,
                                login
                      FROM ONLY basPerson P 
                     INNER JOIN basPersonLink PL 
                             ON P.personId = PL.personId 
                     INNER JOIN basLink L 
                             ON L.linkId = PL.linkId 
                            AND (PL.dateValidate >= now()::date OR PL.dateValidate IS NULL)
                       GROUP BY 1,2,4,5
                       ORDER BY 1) as temp 
             LEFT JOIN basLink L 
                    ON activelink = L.linkId
                 WHERE';
        
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
        {
            $sql .= " login = '?' ";
        }
        else
        {
            $sql .= "  case when '?' = '' then 0=1 else personid = '?' end ";
        }
        
        $sql .= ' ORDER BY level
                 LIMIT 1';

        $this->setContext( $context, $sql );
    }

    function LookupActivePerson( &$lookup )
    {
        if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
        {
            $lookup->addFilterField( new MTextField( 'loginS', null, _M( 'Login', $this->module ), 15 ) );
            
            $bases =  BusinessGnuteca3BusAuthenticate::listMultipleLdap();
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
            {
                $lookup->addFilterField(  new GSelection('baseLdapS', '', _M('Base', $this->module), $bases) );
            }  
        }
        
        $lookup->addFilterField( new MIntegerField( 'personIdS', NULL, _M( 'Código', $this->module ), FIELD_ID_SIZE ) );
        $lookup->addFilterField( new MTextField( 'personNameS', NULL, _M('Nome', $this->module ), FIELD_DESCRIPTION_SIZE ) );
        $columns = array(
            new DataGridColumn('personid',      _M('Código', $this->module), 'right', true, null, true),
            new DataGridColumn('name',          _M('Nome', $this->module), 'left', true, null, true),
            new DataGridColumn('linkId',        _M('Vínculo', $this->module), 'right', true, null, false),
            new DataGridColumn('description',   _M('Vínculo', $this->module), 'left', true, null, true)
        );

        $sql = new MSQL( "personId, 
                          name, 
                          activelink, 
                          L.description,
                          level
                    FROM ( SELECT P.personId, 
                                  P.name, 
                                  min( L.level), 
                                  L.linkid as activelink,
                                  P.login,
                                  LP.baseLdap
                       FROM ONLY  basPerson P 
                       INNER JOIN basPersonLink PL 
                               ON P.personId = PL.personId 
                       INNER JOIN basLink L 
                               ON L.linkId = PL.linkId 
                       LEFT JOIN gtcLibPerson LP
                               ON P.personId = LP.personId 
                              AND (PL.dateValidate >= now()::date OR PL.dateValidate IS NULL)
                         GROUP BY 1,2,4,5,6
                         ORDER BY 1) as temp 
               LEFT JOIN basLink L 
                      ON activelink = L.linkId 
                          ");
        
        $personName = MIOLO::_REQUEST('personNameS');
        $personId = MIOLO::_REQUEST('personIdS');
        
        if ( strlen($personName) > 0 )
        {
            $sql->setWhere("lower(unaccent(name)) LIKE lower(unaccent('%" . $personName . "%'))");
        }

        if ( strlen($personId) > 0 )
        {
            $sql->setWhere("personid = " . $personId );
        }        
        
        $login = MIOLO::_REQUEST('loginS');
        if ( strlen($login) > 0 )
        {
            $sql->setWhere("login = '{$login}'");
        }
        
        $base = MIOLO::_REQUEST('baseLdapS');
        if ( strlen($base) > 0 )
        {
            $sql->setWhere("LP.baseldap = '{$base}'");
        }
        
        if ( MIOLO::_REQUEST('related') ) 
        {
            $sql->setOrderBy( 'name' );
        }
        else 
        {
            $sql->setOrderBy( 'name, level' );
        }

        $lookup->setGrid($sql, $columns);
    }

    /**
     * Lookup Loan
     */
    public function autoCompleteLoan( &$context )
    {
        $sql = 'SELECT  loanId,
                        B.name,
                        ' . $this->db->dateToChar('A.returnForecastDate') . '
                  FROM  gtcLoan     A
       INNER JOIN ONLY  basPerson   B
                    ON  (A.personId = B.personId)
                 WHERE  loanId = ?';
        $this->setContext($context, $sql);
    }


    public function LookupLoan( &$lookup )
    {
    	$busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
    	$busLibraryUnit->filterOperator = TRUE;

        $loanId        = str_replace("'","''", MIOLO::_REQUEST('loanId'));
        $personName    = str_replace("'","''", MIOLO::_REQUEST('personName'));
        $libraryUnitId = str_replace("'","''", MIOLO::_REQUEST('libraryUnitId'));

        $lookup->addFilterField( new MIntegerField('loanId',     null, _M('Código', $this->module), 5));
        $lookup->addFilterField( new MTextField('personName', null, _M('Nome da pessoa', $this->module), 20));
        $lookup->addFilterField( new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $busLibraryUnit->listLibraryUnit(), null, null, null, TRUE));

        $columns = array(
            new DataGridColumn('loanId',        _M('Código', $this->module),         'left', true, null, true),
            new DataGridColumn('name',          _M('Nome da pessoa', $this->module),  'left', true, null, true),
            new DataGridColumn('itemNumber',    _M('Número do exemplar', $this->module),  'left', true, null, true),
            new DataGridColumn('libraryName',   _M('Unidade de biblioteca', $this->module), 'left', true, null, true)
        );

        $sql = new MSQL();
        $sql->setColumns('
            A.loanId,
            B.name,
            A.itemNumber,
            L.libraryName
        ');
        $sql->setTables('
                    gtcloan     A
    LEFT JOIN ONLY  basPerson   B
                ON  (A.personId = B.personId)
         LEFT JOIN  gtcLibraryUnit L
                ON  (L.libraryUnitId = A.libraryUnitId)
        ');

        if ($loanId != null)
        {
            $sql->setWhere("A.loanId = '{$loanId}'");
        }
        if ($personName != null)
        {
            $sql->setWhere("lower(unaccent(B.name)) LIKE lower(unaccent('{$personName}%'))");
        }
        if ($libraryUnitId != null)
        {
        	$sql->setWhere("A.libraryUnitId = '{$libraryUnitId}'");
        }

        $sql->setOrderBy( 'loanDate DESC' );

        $lookup->setGrid( $sql, $columns);
    }



    /**
     * Lookup FineStatus
     */
    public function autoCompleteFineStatus( &$context )
    {
        $sql = 'SELECT fineStatusId, description FROM gtcFineStatus WHERE fineStatusId = ?';
        $this->setContext( $context, $sql );
    }


    public function LookupFineStatus( &$lookup )
    {
        $fineStatusId = str_replace("'","''", MIOLO::_REQUEST('fineStatusId'));
        $description  = str_replace("'","''", MIOLO::_REQUEST('descritpion'));

        $lookup->addFilterField(new MTextField('fineStatusId',  $fineStatusId,  _M('Código', $this->module), 5));
        $lookup->addFilterField(new MTextField('description',   $description,   _M('Descrição', $this->module), 20));

        $columns = array(
            new DataGridColumn('fineStatusId',  _M('Código', $this->module),        'left', true, null, true),
            new DataGridColumn('description',   _M('Descrição', $this->module), 'left', true, null, true),
        );

        $sql = new MSQL();
        $sql->setTables('gtcFineStatus');
        $sql->setColumns('fineStatusId,
                          description');

        if (is_numeric($fineStatusId))
        {
            $sql->setWhere("fineStatusId = '{$fineStatusId}'");
        }
        if (strlen($description) > 0)
        {
            $sql->setWhere("lower(description) LIKE lower('%{$description}%')");
        }

        $sql->setOrderBy( 'fineStatusId' );
            
        $lookup->setGrid( $sql, $columns );
    }


    /**
    * Lookup for subfield
    **/
    function LookupSubfieldLK(&$lookup)
    {
        $field       = str_replace("'","''", MIOLO::_REQUEST('fieldid'));
        $subfield    = str_replace("'","''", MIOLO::_REQUEST('subfieldid'));
        $description = str_replace("'","''", MIOLO::_REQUEST('description'));

        $lookup->addFilterField( new MTextField('fieldid',       $field,       _M('Campo'),       3) );
        $lookup->addFilterField( new MTextField('subfieldid',    $subfield,    _M('Subcampo'),     1) );
        $lookup->addFilterField( new MTextField('description', $description, _M('Descrição'), 20) );

        $columns = array(
                            new DataGridColumn('fieldid',    _M('Campo'),       'left', true, null, true),
                            new DataGridColumn('subfieldid', _M('Subcampo'),    'left', true, null, true),
                            new DataGridColumn('description', _M('Descrição'), 'left', true, null, true)
                        );
        if( !empty($field) )
        {
            $where .= " AND fieldid = '".$field."'";
        }
        if( !empty($subfield) )
        {
            $where .= " AND subfieldid = '".$subfield."'";
        }
        if( !empty($description) )
        {
            $where .= " AND lower(description) LIKE lower('".$description."%')";
        }
        $MIOLO = MIOLO::getInstance();
        $db    = $MIOLO->getDatabase($this->module);

        $MSQL = new MSQL('subfieldid, fieldid, description', 'gtcTag');
        if( !is_null($where) )
        {
            $MSQL->setWhere( substr($where, 5) );
        }
        
        $MSQL->setOrderBy( 'fieldid, subfieldid' );
        $sqlObject = new sql();
        $sqlObject->createFrom($MSQL->select());

        $lookup->setGrid( $sqlObject, $columns,_M('Pesquisar'), null, 1 );
    }


    /**
    * Autocomplete for subfield
    **/
    public function autoCompleteSubfieldLK(&$context)
    {
        $MIOLO      = MIOLO::getInstance();
        $fieldId    = MIOLO::_REQUEST('fieldid');
        $subfieldId = MIOLO::_REQUEST('value');

        if (!$fieldId)
        {
            $fieldId = MIOLO::_REQUEST('fieldS');
            $desc    = MIOLO::_REQUEST('descS');

            if (!$fieldId)
            {
                $fieldId = MIOLO::_REQUEST('field');
                $desc    = MIOLO::_REQUEST('desc');
            }

            return array($fieldId, $desc);
        }

        $MSQL = new MSQL('fieldId, description', 'gtcTag', 'fieldId = ? AND subfieldId = ?');
        $MSQL->setParameters($fieldId, $subfieldId);
        
        $MSQL->setOrderBy( 'fieldId' );
        $result = $MIOLO->getDatabase( $this->module )->query( $MSQL->select( ));

        return( $result[0] );
    }

    function LookupPrivilege( &$lookup )
    {
        $privilegeGroupId = str_replace("'","''", MIOLO::_REQUEST( 'privilegeGroupId' ));
    	$description = str_replace("'","''", MIOLO::_REQUEST( 'description' ));

        $lookup->addFilterField( new MTextField( 'privilegeGroupId', $privilegeGroupId, _M('Código',$this->module ), 8 ) );
        $lookup->addFilterField( new MTextField( 'description', $description, _M('Descrição',$this->module ), 40 ) );

        $columns = array(
                            new DataGridColumn('privilegeGroupId', _M('Código do grupo de privilégio'), 'left', true, null, true),
                            new DataGridColumn('description', _M('Descrição'), 'left', true, null, true)
                        );
        if( $privilegeGroupId != null )
        {
            $where .= " and privilegeGroupId = $privilegeGroupId";
        }
	    if( $description != null )
    	{
	    	$where .= " and description like '$description%'";
    	}

        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( $this->module );

        $sql = new MSQL( 'privilegeGroupId, description', 'gtcprivilegegroup' );

        if( !is_null( $where ) )
        {
            $sql->setWhere( substr( $where, 5 ) );
        }
        
        $sql->setOrderBy( 'privilegegroupid' );

        $lookup->setGrid($sql, $columns );

    }

    public function autoCompletePrivilege( &$context )
    {
        $sql = 'SELECT  privilegeGroupId, description
                  FROM  gtcprivilegegroup
                 WHERE  privilegegroupid = ?';

        $this->setContext( $context, $sql );
    }

    function LookupMaterialGender( &$lookup )
    {
        $materialGenderId = str_replace("'","''", MIOLO::_REQUEST( 'materialGenderId' ));
    	$description = strtoupper(str_replace("'","''", MIOLO::_REQUEST('description' )));

        $lookup->addFilterField( new MTextField( 'materialGenderId', $materialGenderId, _M('Código',$this->module ), 8 ) );
        $lookup->addFilterField( new MTextField( 'description', $description, _M('Descrição',$this->module ), 40 ) );

        $columns = array(
                            new DataGridColumn('materialGenderId', _M('Código do gênero do material'), 'left', true, null, true),
                            new DataGridColumn('description', _M('Descrição'), 'left', true, null, true)
                        );
        if( $materialGenderId != null )
        {
            $where .= " and materialgenderid = $materialGenderId";
        }
	    if( $description != null )
    	{
	    	$where .= " and description like '$description%'";
    	}

        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( $this->module );

        $sql = new MSQL( 'materialgenderid, description', 'gtcmaterialgender' );

        if( !is_null( $where ) )
        {
            $sql->setWhere( substr( $where, 5 ) );
        }

        $sql->setOrderBy( 'materialgenderid' );

        $lookup->setGrid( $sql, $columns );
    }

    public function autoCompleteMaterialGender( &$context )
    {
        $sql = 'SELECT  materialGenderId, description
                  FROM  gtcMaterialGender
                 WHERE  materialGenderId = ?';

        $this->setContext( $context, $sql );
    }

    function LookupOperation( &$lookup )
    {
        $operationId = str_replace("'","''", MIOLO::_REQUEST( 'operationId' ));
        $description = str_replace("'","''", MIOLO::_REQUEST( 'description' ));

        $lookup->addFilterField( new MTextField( 'operationId', $operationId, _M('Código',$this->module ), 8 ) );
        $lookup->addFilterField( new MTextField( 'description', $description, _M('Descrição',$this->module ), 40 ) );

        $columns = array(
                            new DataGridColumn('operationId', _M('Operação do código do grupo'), 'left', true, null, true),
                            new DataGridColumn('description', _M('Descrição'), 'left', true, null, true)
                        );
        if( $operationId != null )
        {
            $where .= " and operationId = $operationId";
        }
        if( $description != null )
        {
            $where .= " and description like '$description%'";
        }

        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( $this->module );

        $sql = new MSQL( 'operationId, description', 'gtcOperation' );

        if( !is_null( $where ) )
        {
            $sql->setWhere( substr( $where, 5 ) );
        }

        $sql->setOrderBy( 'operationid' );

        $lookup->setGrid( $sql, $columns );

    }

    public function autoCompleteOperation( &$context )
    {
        $sql = 'SELECT  operationId, description
                  FROM  gtcOperation
                 WHERE  operationid = ?';

        $this->setContext( $context, $sql );
    }

    /**
    * Lookup for person
    **/
    function LookupLabelLayout( &$lookup )
    {
        $labelLayoutId = str_replace("'","''", MIOLO::_REQUEST( 'labelLayoutId' ));
        $description   = str_replace("'","''", MIOLO::_REQUEST( 'description' ));
        $lines         = str_replace("'","''", MIOLO::_REQUEST( 'lines' ));
        $labelColumns  = str_replace("'","''", MIOLO::_REQUEST( 'columns' ));

        $lookup->addFilterField( new MTextField( 'labelLayoutId', $labelLayoutId, _M('Código do modelo da etiqueta', $this->module ), 10 ) );
        $lookup->addFilterField( new MTextField( 'description', $description, _M('Descrição', $this->module ), 40 ) );
        $lookup->addFilterField( new MTextField( 'lines', $lines, _M('Linhas', $this->module ), 10 ) );
        $lookup->addFilterField( new MTextField( 'columns', $labelColumns, _M('Colunas', $this->module ), 10 ) );

        $columns = array(
                            new DataGridColumn('labelLayoutId', _M('Código do modelo da etiqueta'), 'left', true, null, true),
                            new DataGridColumn('description', _M('Descrição'), 'left', true, null, true),
                            new DataGridColumn('lines', _M('Linhas'), 'left', true, null, true),
                            new DataGridColumn('columns', _M('Colunas'), 'left', true, null, true),
                            new DataGridColumn('topMargin', _M('Margem superior'), 'left', true, null, true),
                            new DataGridColumn('leftMargin', _M('Margem esquerda'), 'left', true, null, true),
                            new DataGridColumn('verticalSpacing', _M('Espaco Vertical'), 'left', true, null, true),
                            new DataGridColumn('horizontalSpacing', _M('Espaço horizontal'), 'left', true, null, true),
                            new DataGridColumn('height', _M('Altura'), 'left', true, null, true),
                            new DataGridColumn('width', _M('Largura'), 'left', true, null, true),
                            new DataGridColumn('pageFormat', _M('Formato da página'), 'left', true, null, true)
                        );

        if( $labelLayoutId != '' )
        {
            $where .= " and labelLayoutId = " . $labelLayoutId;
        }
        if( $description != '' )
        {
            $where .= " and description LIKE '%" . $description . "%'";
        }
        if( $lines != '' )
        {
            $where .= " and lines = " . $lines;
        }
        if( $labelColumns != '' )
        {
            $where .= " and columns = " . $labelColumns;
        }
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( $this->module );

        $sql = new MSQL( 'labelLayoutId,
                          description,
                          lines,
                          columns,
                          topMargin,
                          leftMargin,
                          verticalSpacing,
                          horizontalSpacing,
                          height,
                          width,
                          pageformat',
                         'gtcLabelLayout' );

        if( !is_null( $where ) )
        {
            $sql->setWhere( substr( $where, 5 ) );
        }

        $sql->setOrderBy( 'labelLayoutId' );

        $lookup->setGrid( $sql, $columns );

    }

    /**
    * Autocomplete for person
    **/
    public function autoCompleteLabelLayout( &$context )
    {
        $sql = 'SELECT labelLayoutId,
                       description,
                       lines,
                       columns,
                       topMargin,
                       leftMargin,
                       verticalSpacing,
                       horizontalSpacing,
                       height,
                       width,
                       pageformat
                  FROM gtcLabelLayout
                 WHERE labelLayoutId = ?';

        $this->setContext( $context, $sql );
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $context
     */
    public function autoCompleteSupplier( &$context )
    {
        $sql = 'SELECT  supplierid , name
                  FROM  gtcsupplier
                 WHERE  supplierid = ?';

        $this->setContext( $context, $sql );
    }


    function LookupSupplier( &$lookup )
    {
        $name = str_replace("'","''", MIOLO::_REQUEST( 'name' ));

        $f[] = new MTextField( 'name', null, _M('Nome', $this->module ), 40 );
        $continer = new MVContainer("containerLookUpSupplier", $f);

        $lookup->addFilterField($continer);

        $columns = array
        (
            new DataGridColumn('supplierid',    _M('Código do fornecedor', $this->module), 'left', true, null, true),
            new DataGridColumn('name',          _M('Nome',          $this->module), 'left', true, null, true),
        );

        $sql = new MSQL( 'supplierid, name', 'gtcsupplier' );
      
        $sql->setOrderBy( 'name' );

        $where = null;

        if ( strlen($name) )
        {
            $name = str_replace(" ", "%", $name);
            $where .= "lower(unaccent(name)) LIKE lower(unaccent('%$name%'))";
        }

        if(!is_null($where))
        {
            $sql->setWhere($where);
        }

        $lookup->setGrid( $sql, $columns );
    }

    public function autoCompleteSupplierType( &$context )
    {
        $sql = 'SELECT  supplierid , name
                  FROM  gtcsupplier
                 WHERE  supplierid = ?';
        $this->setContext( $context, $sql );
    }


    function LookupSupplierType( &$lookup )
    {
        $supplierIdS = str_replace("'","''", MIOLO::_REQUEST('supplierIdS'));
        $nameS       = str_replace("'","''", MIOLO::_REQUEST('nameS'));
        $companyName = str_replace("'","''", MIOLO::_REQUEST('companyName'));

        $lookup->addFilterField( new MIntegerField('supplierIdS', null, _M('Código', $this->module), FIELD_ID_SIZE ) );
        $lookup->addFilterField( new MTextField('nameS', null, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE) );
        $lookup->addFilterField( new MTextField('companyName', null, _M('Nome da companhia', $this->module), FIELD_DESCRIPTION_SIZE) );

        $listType = array(
            'c' => _M('Compra', $this->module),
            'd' => _M('Doação', $this->module),
            'p' => _M('Permuta', $this->module)
        );

        $columns = array
        (
            new DataGridColumn('supplierid',    _M('Código do fornecedor',         $this->module), 'left', true, null, true),
            new DataGridColumn('nameS',         _M('Nome',                  $this->module), 'left', true, null, true),
            new DataGridColumn('companyName',   _M('Nome da companhia',          $this->module), 'left', true, null, true),
            new DataGridColumn('type',          _M('Tipo',                  $this->module), 'left', true, true, true, $listType),
        );


        $sql = new MSQL( 'A.supplierId,B.name,A.companyName,A.type',
                                'gtcSupplierTypeAndLocation A, gtcSupplier B',
                                'A.companyName is not null and A.supplierId=B.supplierId'
        );

        if ( $supplierIdS )
        {
            $sql->setWhere("A.supplierId = '{$supplierIdS}'");
        }

        if ( $nameS )
        {
            $sql->setWhere("lower(unaccent(B.name)) LIKE lower(unaccent('{$nameS}%'))");
        }

        if ( $companyName )
        {
            $sql->setWhere("lower(A.companyName) LIKE lower('{$companyName}%')");
        }
       
        $sql->setOrderBy( 'companyName' );

        $lookup->setGrid( $sql, $columns);
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $context
     */
    public function autoCompleteCutter( &$context )
    {
        $this->setContext(&$context, "SELECT '?'");
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $lookup
     */
    function LookupCutter( &$lookup )
    {
        $abbreviation   = str_replace("'","''", MIOLO::_REQUEST('abbreviation' ));

        $lookup->addFilterField( new MTextField( 'abbreviation',    null,  _M('Abreviação',    $this->module ), 40 ) );

        $columns = array
        (
            new DataGridColumn('code',          _M('Código',          $this->module), 'left', true, null, true),
            new DataGridColumn('abbreviation',  _M('Abreviação',  $this->module), 'left', true, null, true),
        );

        $sql = new MSQL( '(substr(abbreviation, 1, 1) || code) as code, abbreviation', 'gtccutter' );

        if ( strlen($abbreviation) )
        {
            $where .= "lower(abbreviation) LIKE lower('$abbreviation%')";
            $sql->setWhere($where);
        }

        $sql->setOrderBy( 'abbreviation' );
        
        $lookup->setGrid( $sql, $columns );
    }


    /*Autocomplete usado no campo GDictionaryField*/
    public function autoCompleteDictionary( &$context )
    {
        $name = MIOLO::_REQUEST('name'); //spreeadsheetField_650_a
        $explode = explode('_', $name);
        $tag = $explode[1].'.'.$explode[2];

        $sql = "  SELECT DISTINCT DC.dictionaryContent
                    FROM gtcdictionarycontent DC
                   WHERE DC.dictionaryid IN ( SELECT dictionaryId FROM gtcDictionary WHERE tags like '%{$tag}%' )
                     AND lower(DC.dictionaryContent) LIKE lower('?%')
                   ORDER BY DC.dictionaryContent
                   LIMIT 100";

        $this->setContext( $context, $sql );
    }


    /**
     * Enter description here...
     *
     * @param object $lookup
     */
    function LookupDictionary( &$lookup )
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'gnuteca3';

        $content = str_replace("'","''", MIOLO::_REQUEST( 'content' ));
        $filter  = strlen($_GET['filter0']) ? $_GET['filter0'] : str_replace("'","''", MIOLO::_REQUEST( 'filter' ));
        $related = MIOLO::_REQUEST('related');
        $url = $MIOLO->getActionURL($module, 'main:catalogue:dictionarycontent', null, array(
            'function'      => 'insert',
            'from'          =>'lookup',
            'event'         =>'tbBtnNew:click',
            'openerRelated' => $related
        ));
        $url = "window.open('{$url}', 'mywindow'); miolo.getWindow('').close();";

        $lookup->addFilterField( new MButton('novoRegistro', _M('Inserir novo registro'), $url )  );
        $lookup->addFilterField( new MTextField( 'content', $content, _M('Conteúdo', $this->module ), 40 ) );
        $lookup->addFilterField( new MHiddenField( 'filter',  $filter ) );
        $lookup->addFilterField( new MHiddenField( 'related',  $related ) );
        $lookup->addFilterField( new MHiddenField( 'searchButton',  $related ) );

        $columns = array
        (
            new DataGridColumn('dictionaryContent', _M('Conteúdo',           $this->module), 'left', true, null, true),
            new DataGridColumn('relatedContent',    _M('Conteúdo relacionado',   $this->module), 'left', true, null, true),
        );

        $sql = new MSQL();
        $sql->setColumns("DISTINCT DC.dictionaryContent, getRelated(DC.dictionarycontentid) as relatedcontent ");

        $sql->setTables("               gtcdictionarycontent        DC
                            LEFT JOIN   gtcdictionaryrelatedcontent DRC
                                   ON   (DC.dictionarycontentid  = DRC.dictionarycontentid)");

        //evita erros de sql
        if ( $filter )
        {
            $where.= " DC.dictionaryid = '{$filter}' AND ";
        }

        if ( strlen($content) )
        {
            $where .= "(lower(unaccent(DC.dictionaryContent))            LIKE lower(unaccent('{$content}%'))  OR  ";
            $where .= " lower(unaccent(DRC.relatedcontent))    LIKE lower(unaccent('{$content}%'))) AND ";
        }

        $sql->setWhere(substr($where, 0, strlen($where)-4));
        
        $sql->setOrderBy( 'DC.dictionaryContent ASC' );

        $lookup->setGrid( $sql, $columns );
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $context
     */
    public function autoCompleteClassification( &$context )
    {
        $this->setContext(&$context, "select '?'");
    }




    /**
     * Enter description here...
     *
     * @param object $lookup
     */
    function LookupClassification( &$lookup )
    {
        $MIOLO = MIOLO::getInstance();
        $busMaterial = $this->MIOLO->getBusiness($this->module, "BusMaterial");

        $classification = str_replace("'","''", MIOLO::_REQUEST( 'classification' ));
        $cutter         = str_replace("'","''", MIOLO::_REQUEST( 'cutter' ));

        $classification = strlen($classification)   ? $classification   : '%';
        $cutter         = strlen($cutter)           ? $cutter           : '%';

        $lookup->addFilterField( new MTextField( 'classification', null, _M('Classificação', $this->module ), 40 ) );
        $lookup->addFilterField( new MTextField( 'cutter', null, _M( 'Cutter', $this->module ), 40 ) );

        $columns = array
        (
            new DataGridColumn('content',           _M('Número de controle', $this->module), 'left', true, null, false),
            new DataGridColumn('tag',               _M('Etiqueta',           $this->module), 'left', true, null, true),
            new DataGridColumn('classification',    _M('Classificação',      $this->module), 'left', true, null, true),
            new DataGridColumn('cutter',            _M('Cutter',             $this->module), 'left', true, null, true),
            new DataGridColumn('author',            _M('Autor',              $this->module), 'left', true, null, true),
            new DataGridColumn('title',             _M('Título',             $this->module), 'left', true, null, true),
            new DataGridColumn('edition',           _M('Edição',             $this->module), 'left', true, null, true),
        );

        list($cutterF, $cutterSF) = explode(".", MARC_CUTTER_TAG);
        $sqlCutter = "SELECT DISTINCT content FROM gtcMaterial WHERE controlNumber = A.controlNumber AND fieldId = '$cutterF' AND subfieldId = '$cutterSF' LIMIT 1";


        list($authorF, $authorSF) = explode(".", MARC_AUTHOR_TAG);
        $sqlAuthor = "SELECT DISTINCT content FROM gtcMaterial WHERE controlNumber = A.controlNumber AND fieldId = '$authorF' AND subfieldId = '$authorSF' LIMIT 1";


        list($titleF, $titleSF) = explode(".", MARC_TITLE_TAG);
        $sqlTitle = "SELECT DISTINCT content FROM gtcMaterial WHERE controlNumber = A.controlNumber AND fieldId = '$titleF' AND subfieldId = '$titleSF' LIMIT 1";


        list($editionF, $editionSF) = explode(".", MARC_EDITION_TAG);
        $sqlEdition = "SELECT DISTINCT content FROM gtcMaterial WHERE controlNumber = A.controlNumber AND fieldId = '$editionF' AND subfieldId = '$editionSF' LIMIT 1";


        $sql = new MSQL();
        $sql->setColumns("'', fieldId || '.' || subfieldId, content, ($sqlCutter) as cutter, ($sqlAuthor) as author, ($sqlTitle) as title, ($sqlEdition) as edition");

        $sql->setTables("gtcMaterial A");

        $contentSearch = $busMaterial->prepareSearchContent("090.a", $classification, $cutter);
        $sql->setWhere("searchContent ILIKE '$contentSearch'  AND content != '' AND fieldId = '090' AND subFieldId = 'a'");

        $sql->setOrderBy( 'A.searchContent' );

        $lookup->setGrid(  $sql, $columns );
    }




    /**
     * Enter description here...
     *
     * @param object $lookup
     */
    function LookupCostCenter( &$lookup )
    {
        global $MIOLO;

        $costCenterId = str_replace("'","''", MIOLO::_REQUEST( 'costCenterId' ));
        $costCenterDescription = str_replace("'","''", MIOLO::_REQUEST( 'costCenterDescription' ));
        
        $lookup->addFilterField( new MTextField ( 'costCenterId', null, _M('Centro de custo',   $this->module ), 20 ) );
        $lookup->addFilterField( new MTextField ( 'costCenterDescription', null, _M('Descrição',   $this->module ), 40 ) );

        $columns = array
        (
            new DataGridColumn('costCenter',  _M('Centro de custo',   $this->module), 'left', true, null, true),
            new DataGridColumn('description', _M('Descrição',   $this->module), 'left', true, null, true),
            new DataGridColumn('libraryUnit', _M('Unidade de biblioteca',  $this->module), 'left', true, null, true),
        );

        $sql = new MSQL();
        $sql->setColumns("A.costcenterid, A.description, B.libraryname  ");
        $sql->setTables("gtcCostCenter A left JOIN gtcLibraryUnit B USING (libraryunitid)");

        if (  strlen($costCenterId) > 0 )
        {
            if ( !is_numeric( $costCenterId ))
            {
                $myOrderBy = 'A.description';
            }
            else
            {
                $sql->setWhere("A.costcenterid = {$costCenterId}");
            }
        }

        if ( strlen($costCenterDescription) > 0 )
        {
        	$sql->setWhere("A.description ILIKE '%$costCenterDescription%'");
        }
        
        if (  $MIOLO->page->isPostBack() )
        {
            $sql->setOrderBy( $myOrderBy ? $myOrderBy : 'A.description' );
        }
        else
        {
            $sql->setOrderBy( 'A.description' );
        }
      
        $lookup->setGrid( $sql, $columns );
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $context
     */
    public function autoCompleteCostCenter( &$context )
    {
         $sql = 'SELECT  costcenterid,
                        description,
                        libraryunitid
                  FROM  gtcCostCenter
                 WHERE  costcenterid = ?';
         
        $this->setContextCostCenter($context, $sql);
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $context
     */
    public function autoCompleteMaterial( &$context )
    {
        $sql = "SELECT  controlNumber, content
                  FROM  gtcMaterial
                 WHERE  controlNumber = ? AND (fieldId = '245' and subFieldId = 'a')";

        $this->setContext( $context, $sql );
    }


    /**
     * Lookup de seleção de material usado no form  interchange
     *
     * @param object $lookup
     */
    function LookupMaterial( &$lookup )
    {
    	$MIOLO         = MIOLO::getInstance();
    	$module        = MIOLO::getCurrentModule();
        $busMaterial   = $MIOLO->getBusiness( $module , 'BusMaterial');

    	//trata dados pra pesquisa
        $author = strtoupper( str_replace("'","''", MIOLO::_REQUEST( 'author' )) );
        $author = str_replace(' ','%', $author );
        $author = $busMaterial->prepareSearchContent(null, $author );

        $title  = strtoupper( str_replace("'","''", MIOLO::_REQUEST( 'title' )) );
        $title  = str_replace(' ','%', $title );
        $title  = $busMaterial->prepareSearchContent(null, $title );

        $controlNumber = MIOLO::_REQUEST('filter');

        $lookup->addFilterField( new MTextField ( 'author', null,    _M('Autor',    $this->module ), 40 ) );
        $lookup->addFilterField( new MTextField ( 'title',  null,     _M('Título',     $this->module ), 40 ) );

        $columns = array
        (
            new DataGridColumn('controlNumber', _M('Número de controle', $this->module), 'left', true, null, true),
            new DataGridColumn('title',         _M('Título',          $this->module), 'left', true, null, true),
            new DataGridColumn('author',        _M('Autor',         $this->module), 'left', true, null, true),
            new DataGridColumn('edition',       _M('Edition',        $this->module), 'left', true, null, true),
            new DataGridColumn('publication',   _M('Publication',    $this->module), 'left', true, null, true),
        );

        if ( $author || $title || $controlNumber )
        {

	        list($authorF, $authorSF) = explode(".", MARC_AUTHOR_TAG);
	        $sqlAuthor = "SELECT B.content FROM gtcMaterial B WHERE B.controlNumber = A.controlNumber AND B.fieldId = '$authorF' AND B.subfieldId = '$authorSF' LIMIT 1";
	        $sqlAuthorB = "SELECT B.content FROM gtcMaterial B WHERE B.controlNumber = A.controlNumber AND B.fieldId = '700' AND B.subfieldId = 'A' LIMIT 1";

	        list($titleF, $titleSF) = explode(".", MARC_TITLE_TAG);
	        $sqlTitle = "SELECT CASE WHEN CHAR_LENGTH(C.content) > 50 THEN SUBSTR(C.content,0,50) || '...' ELSE C.content END FROM gtcMaterial C WHERE C.controlNumber = A.controlNumber AND C.fieldId = '$titleF' AND C.subfieldId = '$titleSF' LIMIT 1";

	        list($editionF, $editionSF) = explode(".", MARC_EDITION_TAG);
	        $sqlEdition = "SELECT D.content FROM gtcMaterial D WHERE D.controlNumber = A.controlNumber AND D.fieldId = '$editionF' AND D.subfieldId = '$editionSF' LIMIT 1";

	        list($publicationF, $publicationSF) = explode(".", MARC_PERIODIC_INFORMATIONS);
	        $sqlPublication = "SELECT E.content FROM gtcMaterial E WHERE E.controlNumber = A.controlNumber AND E.fieldId = '$publicationF' AND E.subfieldId = '$publicationSF' LIMIT 1";

	        $sql = "DISTINCT controlnumber,
	                         ( $sqlTitle ),
	                         coalesce( ($sqlAuthor) , ($sqlAuthorB) )  ,
	                         ( $sqlEdition ),
	                         ( $sqlPublication )
	                     FROM gtcmaterial A ";

	        if ( !$controlNumber)
	        {
	            $sql .="     WHERE";

	            if ( $author )
	            {

			        $sql .="          fieldid IN ('$authorF', '700', '710')
			                      AND subfieldid = 'a'
			                      AND searchcontent LIKE '%$author%'";
	                $sql .="      AND";
	            }

		        $sql .="          controlnumber IN
		                          (
		                            SELECT DISTINCT controlnumber
		                              FROM gtcmaterial
		                             WHERE fieldid = '$titleF'
		                                AND subfieldid = '$titleSF'
		                                AND searchcontent LIKE '%$title%'
		                          )";
	        }
	        else
	        {
	        	$sql .= "WHERE controlNumber = '$controlNumber' ";
	        }

	        $sql .=" ORDER BY 1, 2, 3, 4
	                 LIMIT 100";
        }
        else
        {
        	$sql  = 'null';
        }

        $sql = new MSQL($sql);

        $lookup->setGrid( $sql, $columns );
    }
    
    
    public function LookupCity( &$lookup )
    {
        $searchTerm = str_replace("'","''", MIOLO::_REQUEST('cityId'));
        
        $lookup->addFilterField( new MTextField( 'cityId', null, _M('Termo', $this->module), 40));

        $columns = array(
            new DataGridColumn('cityId', _M('Código', $this->module),         'left', true, null, true),
            new DataGridColumn('cityName', _M('Cidade', $this->module),  'left', true, null, true),
            new DataGridColumn('zipcode', _M('Cep', $this->module),  'left', true, null, true),
            new DataGridColumn('stateId', _M('Código estado', $this->module), 'left', true, null, true),
            new DataGridColumn('stateName', _M('Estado', $this->module), 'left', true, null, true),
            new DataGridColumn('countryId', _M('Código país', $this->module), 'left', true, null, true),
            new DataGridColumn('countryName', _M('País', $this->module), 'left', true, null, true),            
        );

        $sql = new MSQL();
        $sql->setColumns('
            cityid ,
            bascity.name as cityName,
            zipcode,
            bascity.stateid as stateId ,
            basstate.name as stateName,
            bascity.countryId,
            bascountry.name as countryName
        ');
        $sql->setTables('
                        bascity
                        LEFT JOIN basstate
                        ON bascity.stateid = basstate.stateid
                        LEFT JOIN bascountry
                        ON bascity.countryId = bascountry.countryid
        ');

        if ($searchTerm != null)
        {
            $sql->setWhere("lower(unaccent(cityid::varchar)) ilike lower(unaccent('%{$searchTerm}%'))
                            OR lower(unaccent(bascity.name::varchar)) ilike lower(unaccent('%{$searchTerm}%'))
                            OR lower(unaccent(bascity.zipcode)) ilike lower(unaccent('%{$searchTerm}%'))
                            OR lower(unaccent(bascountry.name::varchar)) ilike lower(unaccent('%{$searchTerm}%'))");
        }

        $sql->setOrderBy( 'bascity.name ASC' );

        $lookup->setGrid( $sql, $columns);
    }
    
    public function autoCompleteCity( &$context )
    {
        $sql = 'SELECT 
                    cityid,
                    name
                FROM bascity
                WHERE cityid = ?';
        
        $this->setContext($context, $sql);
    }    
}
?>
	
