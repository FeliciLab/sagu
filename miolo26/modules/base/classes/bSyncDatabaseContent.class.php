<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 06/01/2011
 *
 **/
/**
 * Sincroniza dados de uma tabela com um xml
 */
class bSyncDatabaseContent
{
    /**
     * Loaded XML
     * @var SimpleXmlElement
     */
    private $xml;
    /**
     * Table name
     * @var string 
     */
    private $table;
    
    /**
     * Miolo module to syncronize
     * @var string
     */
    private $module = 'base';
    
    public function __construct( $table = null, $module =null )
    {
        if ( $table && $module )
        {
            $MIOLO = MIOLO::getInstance();
            $path = $MIOLO->getConf('home.miolo').'/modules/'.$module.'/syncdb/'.$table.'.xml';
            //$path = $MIOLO->getAbsolutePath('syncdb/'.$table.'.xml', $module);
            $this->setXmlPath( $path );
            $this->module = $module;
        }

	$this->compare = true;
    }
    
    /**
     * Define se faz a comparação
     * 
     * @param boolean $compare 
     */
    public function setCompare($compare)
    {
        $this->compare = $compare;
    }
    
    /**
     * Retorna se faz a comparação
     * 
     * @return type 
     */
    public function getCompare()
    {
        return $this->compare;
    }

    /**
     * Define o modulo de acesso 
     * 
     * @param string $module 
     */
    public function setModule($module)
    {
        $this->module = $module;
    }
    
    /**
     * Retorna modulo de acesso
     * @return string modulo de acesso
     */
    public function getModule()
    {
        return $this->module;
    }
    
    /**
     * Retorna um array com os arquivos de sincronização de base do módulo informado.
     * @param string $module
     * @return array 
     */
    public static function listSyncFiles($module)
    {
        $MIOLO = MIOLO::getInstance();
        $path = $MIOLO->getConf('home.miolo').'/modules/'.$module.'/syncdb/*.xml';
        //$path = $MIOLO->getAbsolutePath('syncdb', $module).'/*.xml'; MIOLO 2.5 only
        
        return glob($path);
    }
    
    /**
     * Definir o caminho do xml a ser interpretado
     * 
     * @param string $xmlPath 
     */
    public function setXmlPath($xmlPath)
    {
        if ( !$xmlPath )
        {
            throw new Exception ( new BString("É necessário informar um script XML.") );
        }
        
        //$this->table = str_replace('.xml', '', basename($xmlPath));
        $content = file_get_contents($xmlPath);
        
        if ( ! $content )
        {
            throw new Exception( new BString("Impossível obter conteúdo do arquivo '$xmlPath'.") );
        }

        $this->xml= new SimpleXMLElement($content);
        
        //obtem o nome da tabela
        $this->table = $this->xml->getName();
    }
    
    /**
     * Sincroniza os dados da tabela
     * Nâo remove nenhum registro.
     * 
     * @return stdClass 
     */
    public function syncronize()
    {
        $MIOLO = MIOLO::getInstance();
        $items = $this->xml->item;
        
        $updateCount = 0;
        $insertCount = 0;
        $deleteCount = 0;
        $result = new stdClass();
       
        $ok = bBaseDeDados::consultar( $this->mountSqlCount() );
        $result->countStart = $ok[0][0];
        $result->countXml = count($items);
        
        //faz atualizações
        if ( count( $items ) > 0 )
        {
            foreach ( $items as $line => $item )
            {
                $ok = $this->locateItem($item);

                if ( is_array( $ok ) )
                {
                     //Ignora completamente o update
                    if ( trim($this->xml->ignoreOnUpdate[0]) != '*' )
                    {
                        $sql = $this->mountUpdateSql( $item );
                        $ok = bBaseDeDados::executar( $sql );
                    
                        $updateCount ++;
                    }
                }
                else
                {
                    $sql = $this->mountInsertSql($item);
                    $ok = bBaseDeDados::executar($sql);
                    $insertCount ++;
                }
            }
        }
        
        $deletes = $this->xml->delete;
        
        //procede com as remoções
        if ( count( $deletes ) > 0 )
        {
            foreach ( $deletes as $line => $delete )
            {
                $ok = $this->locateItem($delete);
                
                if ( is_array( $ok ) )
                {
                    $ok = bBaseDeDados::executar( $this->mountDeleteSql($delete) );
                    $deleteCount++;
                }
            }
        }
        
        $result->updateCount = $updateCount;
        $result->insertCount = $insertCount;
        $result->deleteCount = $deleteCount;
        
        $ok = bBaseDeDados::consultar( $this->mountSqlCount() );
        $result->countEnd = $ok[0][0];
        
        //caso tenha diferenças entre o xml e a contagem final tenta localizar registros sobrando
        if ( $result->countEnd != $result->countXml )
        {
            $sqlListAll = $this->mountSqlSelectAll();
            
            $all = bBaseDeDados::consultar( $sqlListAll );
            $columns = bCatalogo::listarColunasDaTabela( $this->table );
            
            if ( is_array( $all) )
            {
                foreach ( $all as $line => $info )
                {
                    //converte para objeto
                    $info = $this->resultToObject($columns, $info);
                    
                    //monta array de localização
                    foreach ( $this->xml->locate as $l => $locate)
                    {
                        $locate = $locate.'';
                        $search[ $locate.''] = $info->{$locate.''};
                    }
                    
                    $achou = false;
                    
                    //sai procurando 1 por 1 (pode demorar)
                    if ( count( $items ) > 0 )
                    {
                        foreach ( $items as $line => $item )
                        {
                            $certo = false;
                            
                            foreach ($search as $word => $content )
                            {
                                if ( $content == $item->$word)
                                {
                                    $certo[] = true;
                                }
                            }
                            
                            if ( count($certo) == count( $search ) )
                            {
                                $achou = true;
                                break;
                            }
                        }
                    }

                    //caso não encontrou joga em um array com os que estão sobrando
                    if ( !$achou)
                    {
                        $sobrando[] = $info;
                    }
                }
            }
            
            $result->extras = $sobrando;
        }
        
        return $result;
    }


    public function makeXMLfromResult($extras)
    {
        $xml = '';
    
        foreach ( (array)$extras as $line => $extra )
        {
            $xml .= "    <item>\n";
    
            foreach ( (array)$extra as $attribute => $value )
            {
                // Estes atributos não devem aparecer no xml do sagu
                if( !in_array($attribute, array('datetime', 'ipaddress', 'username')) )
                {
                    //Adicionando suporte a CDATA nos caracteres <>/
                    if(preg_match('/(>|<|\/)/', $value))
                    {
                        $value = '<![CDATA[' . $value . ']]>';
                    }
                
                    $xml .= "        <$attribute>$value</$attribute>\n";
                }
            }
    
            $xml .= "    </item>\n";
        }

        return $xml;
    }

    
    
    /**
     * Tenta localizar elemenet XML na base
     * 
     * @param XMLElement $item
     * @return arrat
     */
    public function locateItem( $item )
    {
        $locateString = $this->getLocateString( $item );
        
        $msql = new MSQL();
        $msql->setTables($this->table);
        $msql->setColumns('*');
        $msql->setWhere($locateString);

        return bBaseDeDados::consultar( $msql );
    }
    
    /** 
     * Converte um resultado para objeto
     * 
     * @param array $columns
     * @param array $info
     * 
     * @return stdClass 
     */
    protected function resultToObject($columns, $info)
    {
        $obj = new stdClass();
        
        foreach ( $columns as $l => $i )
        {
            $obj->{ $columns[$l] }= $info[$l];
        }
        
        return $obj;
    }
    
    /**
     * Retorna a string de localização de registro
     * @param type $item 
     */
    public function getLocateString( $item )
    {
        //obtem o parametro de localização de registros
        foreach ( $this->xml->locate as $l => $locate)
        {
            //define item localizador
            $itemLocator = addslashes( $item->$locate );
            
            //monta string sql de localização de sql
            if ( ! $itemLocator ) 
            {
                //caso especial para string vazia
                $locateString[] = "( $locate = '$itemLocator' OR  $locate IS NULL ) ";
            }
            else
            {
                $locateString[] = "$locate = '$itemLocator'";
            }
        }
        
        $locateString = implode( ' AND ', $locateString );
        
        return $locateString;
    }

    /**
     * Obtem a listagem de campos a serã atualizados.
     * Considera a propriedade ignoreOnUpdate do xml
     *  
     * @param SimpleXmlElement $item
     * @return array 
     */
    public function getUpdateFields($item)
    {
        $fields = get_object_vars( $item );

        foreach ( $this->xml->ignoreOnUpdate as $l => $ignore)
        {
            unset( $fields[ $ignore.'' ]);
        }
        
        return array_keys( $fields );
    }

    /**
     * Monta sql de atualização para um item
     * 
     * @param SimpleXmlElement $item
     * @return string
     */
    public function mountUpdateSql( $item )
    {
        $dataString = array();
        $fields =  $this->getUpdateFields( $item );
                    
        if ( is_array( $fields ) )
        {
            foreach ( $fields as $line => $field )
            {
                $fieldContent = addslashes( $item->$field );
                $dataString[] = " $field = '$fieldContent' ";
            }

            $dataString = implode(',', $dataString);
            
            $locateString = $this->getLocateString($item);
            $sqlUpdate = "UPDATE {$this->table} SET $dataString WHERE $locateString";
            
            return $sqlUpdate;
        }
    }
    
    public function mountDeleteSql( $item )
    {
        $locateString = $this->getLocateString($item);
        $sqlDelete = "DELETE FROM {$this->table} WHERE $locateString;";
        return $sqlDelete;
    }
    
    public function mountInsertSql($item)
    {
        $dataString = array();
        
        $fields = array_keys( get_object_vars( $item ) );
        
        if ( is_array( $fields ) )
        {
            $dataString = implode(',', $dataString);
            $f = implode(',', $fields);
            $values = array_values( get_object_vars( $item ) );
            
            foreach ( $values as $l => $value )
            {
                $values[$l] = addslashes($value);
            }
            
            $values = implode("','",$values);
            
            $locateString = $this->getLocateString($item);
            $sqlInsert = "INSERT INTO {$this->table} ( $f ) VALUES ( '$values' );";
            
            return $sqlInsert;
        }
    }
    
    /**
     * Monta instrução sql para contagem.
     * 
     * @return MSQL Objeto para contar a quantidade de registros.
     */
    public function mountSqlCount()
    {
        $msql = new MSQL();
        $msql->setColumns('count(*)');
        $msql->setTables($this->table);
        
        return $msql;
    }
    
    /**
     * Monta intrução sql com seleção de todos regitros
     * 
     * @return string
     */
    public function mountSqlSelectAll()
    {
        $column = implode(', ', bCatalogo::listarColunasDaTabela($this->table) );
        
        $msql = new MSQL();
        $msql->setTables($this->table);
        $msql->setColumns($column);
        
        return $msql;
        
    }
}
?>
