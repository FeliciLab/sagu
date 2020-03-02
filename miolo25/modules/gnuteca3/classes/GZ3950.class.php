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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 04/05/2009
 *
 **/
class GZ3950
{
    protected $host;
    protected $options = array();
    protected $yazConnection  = null;
    protected $tagSearch = null;
    protected $count = null;

    /**
     * Constructor Method
     */
    function __construct( $host = null, $user = null, $password = null )
    {
        $this->host = $host;
        $this->options['password'] = $password;
        $this->options['user'] = $user;
        $this->options['charset'] = 'xml; charset=marc-8,utf-8';

        if ( !$host )
        {
            throw new Exception( _M( "É necessário informar um endereço para conexão.",'gnuteca3' ) );
        }
        
        if ( ! $this->isInstalled() )
        {
            throw new Exception( _M('Por favor, instale o modulo YAZ para usar o sistema Z3950', $this->module) );
        }
       
        $this->yazConnection = yaz_connect( $this->getHost() , $this->options );
    }

    /**
     * Verifica se o yaz (utilizado pelo Z3950) esta instalado;
     *
     *
     * @return boolean se yaz esta instalado
     *
     */
    public function isInstalled()
    {
        return function_exists('yaz_connect');
    }
    
    public function isServerOnline()
    {
        try
        {
            //teste de pesquisa
            $this->addTagSearch('1016', 'test');
            $ok = $this->search( 'xml', 'usmarc', 1, 1);
            
            return true;
        }
        catch ( Exception $e)
        {
            return false;
        }
    }

    /**
     * retorna o usuario
     *
     * @return String
     */
    public function getUser()
    {
        return $this->options['user'];
    }

    /**
     * retorna o password
     *
     * @return String
     */
    public function getPassword()
    {
        return $this->options['password'];
    }
    
    /**
     * retorna o host
     *
     * @return String
     */
    public function getHost()
    {
        return $this->host;
    }
    
   /**
     * Adiciona um clausula de condição para pesquisa
     *
     * @param char 5 $tag
     * @param string $content
     */
    public function addTagSearch($tag, $content, $operator = '@and')
    {
        if ( $tag && $content && $operator )
        {
            $this->tagSearch[$tag]->content  = $content;
            $this->tagSearch[$tag]->operator = $operator;
        }
    }
    
    /**
     * Caso seja uma busca, retorna quantidade total de registros retornados.
     * 
     * @return int retorna quantidade total de registros retornados.
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Executa a busca
     * 
     * @param string $recordType tipo de retorno de dados (array de ..)
     * @param string $sintax usmarc deve ser o suficiente
     * @param int $rangeStart índice do primeiro registro
     * @param int $rangelength quantidade de registros
     * 
     * @return array array com os dados dos registros
     */
    public function search( $recordType = 'xml' , $sintax = 'usmarc', $rangeStart = 1, $rangelength = '20' )
    {
        require_once( dirname(__FILE__) . "/GXML.class.php" );
        
        if ( !$this->yazConnection )
        {
            throw new Exception( _M( 'Sem conexão com servidor Z3950' , 'gnuteca3' ) );
        }

        if ( !$this->tagSearch )
        {
            throw new Exception( _M('É necessário informar conteúdo para busca.',' gnuteca3' ) );
        }
        
        if ( !trim( strtolower( $recordType ) )  )
        {
            throw new Exception ( _M("É necessário informar um formato de retorno.", 'gnuteca' ) );
        }
        
        if ( ! trim( strtolower( $recordType ) ) == 'xml'  )
        {
            //só suporta xml por enquanto
            throw new Exception ( _M("Formato @1 não suportado pelo client Gnuteca.", 'gnuteca', $recordType ) );
        }
        
        if ( !$sintax )
        {
            $sintax = 'usmarc';
        }
        
        yaz_syntax( $this->yazConnection, $sintax) ; //define sintaxe
        yaz_range( $this->yazConnection, $rangeStart, $rangelength ); //define intervalo

        $query  = null;
        $fieldS = null;
        $conteS = null;

        //monta query
        foreach ( $this->tagSearch as $id => $content )
        {
            if ( is_null( $fieldS ) )
            {
                $fieldS = "@attr 1=$id";
            }
            else
            {
                $fieldS = "@attr 1=1016"; // all Fields
            }

            $conteS = "@attr 4=1 ";
            $opera  = $content->operator;
            $value  = explode(" ", $content->content);

            if ( count( $value ) > 1 )
            {
                for ($x = 1; $x < count($value); $x++)
                {
                    $conteS.= " $opera ";
                }
            }

            $value = implode("' '", $value);
            $conteS.= " '$value'";

            break;
        }

        $query = str_replace("  ", " ", "$fieldS $conteS"); //tira espaços sobresalentes

        if ( !strlen($query) )
        {
            throw new Exception( _M('Sem filtro','gnuteca3') );
        }
        
        if ( yaz_search( $this->yazConnection, "rpn", $query ) )
        {
            $wait_options = array("timeout" => 30 );
            yaz_wait( $wait_options );
            
            $error = yaz_error( $this->yazConnection );
        
            if ( $error )
            {
                throw new Exception( _M('Erro ao conectar com serviror: @1','gnuteca3', "$error" ));
            }
            
            //define contagem total de registros
            $this->count = yaz_hits( $this->yazConnection );

            $result = array();

            for ( $x = $rangeStart; $x <= ( $rangeStart + $rangelength ); $x++ )
            {
                //recortypes possíveis = string //marc, xml //padrão, raw //iso2709, //syntax //database retorna o nome da base de dado //array
                $record = yaz_record( $this->yazConnection, $x, $this->options['charset'] ); //obtem o registro
                
                if ( $record && trim( strtolower( $this->options['charset'] ) ) == 'xml; charset=marc-8,utf-8' )
                {
                    $aux        = new GXML( $record );
                    $array      = $aux->getResult();
                    $array      = $array['record'][0];
                    $returnList = array();

                    foreach ( $array as $tagX => $elements )
                    {
                        foreach ($elements as $content)
                        {
                            $tag = isset($content['_attributes_']['tag']) && strlen($content['_attributes_']['tag']) ? $content['_attributes_']['tag'] : false;

                            if ( $tagX == 'controlfield' && $tag )
                            {
                                $line   = isset($returnList[$tag]->subfields['a']) ? count($returnList[$tag]->subfields['a']) : 0;
                                $returnList[$tag]->subfields['a'][$line]->content = $content['_content_'];
                            }
                            elseif($tagX == 'datafield' && $tag)
                            {
                                $subfields = isset($content['subfield']) ? $content['subfield'] : false;

                                if(!$subfields)
                                {
                                    continue;
                                }

                                foreach ($subfields as $subfieldContent)
                                {
                                    $line   = isset($returnList[$tag]->subfields[$subfieldContent['_attributes_']['code']]) ? count($returnList[$tag]->subfields[$subfieldContent['_attributes_']['code']]) : 0;
                                    $returnList[$tag]->subfields[$subfieldContent['_attributes_']['code']][$line]->content = $subfieldContent['_content_'];
                                }

                                $returnList[$tag]->ind1 = isset($content['_attributes_']['ind1']) && strlen($content['_attributes_']['ind1']) ? $content['_attributes_']['ind1'] : false;
                                $returnList[$tag]->ind2 = isset($content['_attributes_']['ind2']) && strlen($content['_attributes_']['ind2']) ? $content['_attributes_']['ind2'] : false;
                            }
                        }
                    }
                    
                    $result[] = $returnList;
                }
            }
            
            return $result;
        }
    }

    /**
     * Este método faz uma relação entre campos do marc com campos do z3950
     *
     * @param unknown_type $tag
     */
    public static function getRelationOfMarc21AndZ3950($id = null, $forCombo = false )
    {
        //FONTE : http://www.loc.gov/z3950/agency/defns/bib1.html
        // DOCUMENTO LOCAL gnuteca3/misc/docs/z3950/semantic.txt

        $opts   = Z3950_SEARCH_OPTIONS;
        $opts   = explode(";", $opts);
        $count  = 1;

        foreach ($opts as $line)
        {
            if(ereg("//", $line) || ereg("#", $line))
            {
                continue;
            }

            list($id, $label) = explode("=", $line);

            if(!strlen(trim($id)) || !strlen(trim($label)))
            {
                continue;
            }

            if ( $forCombo )
            {
                $z3950Marc21[$count] = array( trim($id) ,trim($label) );
            }
            else
            {
                $z3950Marc21[trim($id)] = trim($label);
            }

            $count++;
        }

        return $z3950Marc21;
    }
    
    
    /**
     * Faz insert ou update de acordo com a situação
     * 
     * @param integer $controlNumber
     * @return boolean
     */
    public function insertOrUpdate( $controlNumber )
    {
        try
        {
            return $this->insert($controlNumber);
        }
        catch ( Exception $exc )
        {
            return $this->update($controlNumber);
        }
        
        return false;
    }
    
    /**
     * Faz uma operação de inserção
     * 
     * @param integer $controlNumber
     * @return boolean 
     */
    public function insert( $controlNumber )
    {
        return $this->manageRecord( $controlNumber, 'recordInsert');
    }
    
    /**
     * Faz uma operação de atualização
     * 
     * @param integer $controlNumber
     * @return boolean 
     */
    public function update( $controlNumber )
    {
        return $this->manageRecord( $controlNumber, 'recordReplace');
    }
    
    public function delete( $controlNumber )
    {
        //faz insert/update para ter certeza que o registro está atualizado para remoção funcionar
        $this->insertOrUpdate($controlNumber);
        $this->manageRecord($controlNumber,'recordDelete');
    }
    
    /**
     * Insere ou atualiza um registro na base de dados
     * 
     * @param int $controlNumber 
     * @param string $controlNumber  recordInsert for insert ou recordReplace para update
     */
    protected function manageRecord( $controlNumber , $function = 'recordInsert')
    {
        if ( !$controlNumber )
        {
            throw new Exception( _M('É necessário informar um número de controlar para lidar com servidor Z3950.','gnuteca3') );
        }
        
        $iso = new gIso2709Export( array( $controlNumber ) );
        $record = $iso->execute();
       
        $args = array('action' => $function,
                      'syntax' => 'xml',
                      'record' => $record,
                      'recordIdOpaque' => $controlNumber
                     );

        yaz_es( $this->yazConnection , 'update', $args );
        yaz_es( $this->yazConnection , 'commit', array() );
        $ok = yaz_wait(); //realmente executa
        
        $result = yaz_es_result( $this->yazConnection );
        $error = yaz_error( $this->yazConnection );
        
        if ( $error )
        {
            throw new Exception( _M('Erro ao conectar com serviror: @1','gnuteca3', "$error" ));
        }
        /*else
        {
            //$result = yaz_es_result( $id );
        }*/
        
        return true;
    }
}
?>