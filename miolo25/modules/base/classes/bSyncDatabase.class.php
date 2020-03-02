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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 10/06/2012
 *
 * */
class bSyncDatabase extends SimpleXMLElement implements bSync
{

    /**
     * Define o modulo de acesso 
     * 
     * @param string $module 
     */
    public function setModule($module)
    {
        $this->addAttribute('module', $module);
    }

    /**
     * Retorna modulo de acesso
     * @return string modulo de acesso
     */
    public function getModule()
    {
        return $this->getAttribute('module') . '';
    }

    /**
     * Retorna um array com os arquivos de sincronização de base do módulo informado.
     * 
     * @param string $module
     * @return array 
     */
    public static function listSyncFiles($module)
    {
        $MIOLO = MIOLO::getInstance();
        $path = $MIOLO->getConf('home.miolo') . '/modules/' . $module . '/syncdb/*.xmi';
        return glob($path);
    }

    /**
     * Remove a attribute
     *
     * @param string $attribute name of attribute
     */
    protected function removeAttribute($attribute)
    {
        unset($this->attributes()->$attribute);
    }

    /**
     * Define an attribute, differs from addAttribute.
     * Define overwrite existent attribute
     *
     * @param string $attribute attribute to set
     * @param string $value value to set
     * @param string $namespace the namespace of attribute
     *
     * @example  $this->addAttribute("xlink:href", $filename, 'http://www.w3.org/1999/xlink');
     */
    protected function setAttribute($attribute, $value, $namespace = null)
    {
        $this->removeAttribute($attribute);
        $this->addAttribute($attribute, $value, $namespace);
    }

    /**
     * Return a value of a attribute. Support namespaces using namespace:attribute
     *
     * @param string $attribute
     * @return string return the value of passed attribute
     * @example $svg->g->image->getAttribute('xlink:href')
     */
    protected function getAttribute($attribute)
    {
        $explode = explode(":", $attribute);

        if ( count($explode) > 1 )
        {
            $attributes = $this->attributes($explode[0], true);

            //if the attribute exits with namespace return it
            if ( $attributes[$explode[1]] )
            {
                return $attributes[$explode[1]];
            }
            else
            {
                //otherwize will return the attribute without namespaces
                $attribute = $explode[1];
            }
        }

        if (@$this && @$attribute && @$this->attributes() )
        {
            return $this->attributes()->$attribute . '';
        }
    }

    /**
     * Retorna versão do XMI
     * 
     * @return string
     */
    public function getXmiVersion()
    {
        return $this->getAttribute('xmi:version') . '';
    }

    /**
     * Retorna uma proprieda do XMI
     * 
     * @param string $propertyName
     * @return string 
     */
    protected function getProperty($propertyName)
    {
        $xmi = $this->children('xmi', true);
        $Extension = $xmi->Extension;
        $projectProperties = $Extension->children();
        $projectProperty = $projectProperties->children();

        foreach ( $projectProperty as $line => $property )
        {
            if ( $property->getAttribute('name') . '' == $propertyName )
            {
                return $property->getAttribute('value') . '';
            }
        }
    }

    /**
     * Retora empresa que gerou o XMI
     * 
     * @return string
     */
    public function getCompany()
    {
        return $this->getProperty('company');
    }

    /**
     * Retorna o autor do XMI
     * 
     * @return string
     */
    public function getAuthor()
    {
        return $this->getProperty('author');
    }

    /**
     * Retorna a descrição do XMI
     * 
     * @return string html
     */
    public function getDescription()
    {
        return $this->getProperty('description');
    }

    /**
     * Retorna um array de mensagens geradas pelo sistema
     * 
     * @return array
     */
    public function getMessages()
    {
        return array_filter(explode("\n", $this->getAttribute('messages') . ''));
    }

    /**
     * Adiciona uma mensagem ao sistema
     * 
     * @param string $msg 
     */
    public function addMessage($msg)
    {
        $messages = $this->getAttribute('messages') . '';
        $messages .= new BString($msg) . "\n";

        $this->setAttribute('messages', $messages);
    }

    /**
     * Sincroniza triggers contraints e chaves estrangeiras em um segundo momento.
     * Normalmente após a criação de funções.
     * 
     * @param array $tablesByI vetor com tabelas ordenadas pelo id do vpp
     * 
     */
    public function syncronizeTriggersAndContraints($tablesById)
    {
        $MIOLO = MIOLO::getInstance();
        $uml = $this->children('uml', true);

        //Inicializa l
        $l = null;
        
        //passa por todo o conteúdo UML
        foreach ( $uml as $line => $xmlElement )
        {
            $extension = $xmlElement->children('xmi', true);
            $extension = $extension->children();

            $ownedMember = $extension->vpumlChildModels->ownedMember;

            foreach ( $ownedMember as $l => $item )
            {
                $xmiType = $item->getAttribute('xmi:type');

                if ( $xmiType == 'dbTriggerContainer' ) //trigger
                {
                    //triggers
                    $this->parseTriggers($db, $item);
                }
                else if ( $xmiType == 'dbForeignKey' )
                {
                    //chaves estrangeiras
                    $this->parseForeignKey($db, $item, $tablesById);
                }
            }
        }

        //checks previamente montados
        if ( is_array($tablesById) )
        {
            foreach ( $tablesById as $tableId => $tableInfo )
            {
                if ( $tableInfo->sqlCheck )
                {
                    bBaseDeDados::consultar($tableInfo->sqlCheck);
                }
            }
        }
    }

    /**
     * Efetua a sincronização
     */
    public function syncronize()
    {
        $MIOLO = MIOLO::getInstance();

        if ( $this->getXmiVersion() != '2.1' )
        {
            throw new Exception(new bString('A versão deste XMI é ' . $this->getXmiVersion() . ',mas a única versão suportada é a 2.1.'));
        }

        $this->addMessage('Empresa: ' . $this->getCompany());
        $this->addMessage('Autor: ' . $this->getAuthor());

        $uml = $this->children('uml', true);

        $this->addMessage('Projeto: ' . $uml->getAttribute('name'));

        //array de tabelas índexadas pelo id
        $tablesById = array( );

        //passa por todo o conteúdo UML
        foreach ( $uml as $line => $xmlElement )
        {
            $extension = $xmlElement->children('xmi', true);
            $extension = $extension->children();
            $ownedMember = $extension->vpumlChildModels->ownedMember;
            
            if ( is_object( $ownedMember ) )
            {
                foreach ( $ownedMember as $l => $item )
                {
                    $xmiType = $item->getAttribute('xmi:type');

                    if ( $xmiType == 'procedureContainer' ) //funções, gerenciado pelo functions.sql
                    {

                    }
                    else if ( $xmiType == 'dbTriggerContainer' ) //trigger
                    {
                        $triggers = $item;
                    }
                    else if ( $xmiType == 'dbTable' )
                    {
                        $tableInfo = $this->parseTable($db, $item);
                        $tablesById[$tableInfo->id] = $tableInfo;
                    }
                    else if ( $xmiType == 'dbForeignKey' )
                    {
                        //this->parseForeignKey($db, $item, $tablesById);
                    }
                    else if ( $xmiType == 'anchor' )
                    {

                    }
                }
            }
        }

        //os trigres tem que ser feitos no fim, pois alguma tabela pode não existir
        if ( $triggers )
        {
            //$this->parseTriggers($db, $triggers );
        }

        return $tablesById;
    }

    /**
     * Faz a sincronização dos trigres
     * 
     * @param Object $db object de execução de queries
     * @param XMLElement $item object xml
     */
    public function parseTriggers($db, $item)
    {
        $triggers = $item->ownedMember;

        foreach ( $triggers as $line => $trigger )
        {
            $triggerName = $trigger->getAttribute('name') . '';

            //determina se é ou não para aplicar o tigre
            //caso inicie com RI são as triggers do postgres, não é preciso mexer nelas
            $doIt = strpos($triggerName, 'RI') !== 0;

            if ( $doIt )
            {
                $childs = $trigger->children('xmi', true);
                $extension = $childs->Extension;

                $childs = $extension->children();
                $createStatement = $childs->createStatement;

                //obtem a string de criação da trigger
                $value = $createStatement->getAttribute('value') . '';

                $posIni = stripos($value, ' on ');
                $posEnd = stripos($value, ' for ');

                //separa somente o nome da tabela determinando a posição de ON e FOR
                $table = trim(substr($value, $posIni + 3, $posEnd - $posIni - 3));

                //não filtra por esquema porque não tem a mão nesse momento
                $triggers = bCatalogo::listarGatilhos(null, $triggerName, $table);
                $trigre = $triggers[$triggerName];

                //só remove se existir no banco
                if ( $trigre )
                {
                    //drop a trigger
                    bBaseDeDados::consultar("DROP TRIGGER $triggerName ON $table;");
                }

                //cria a trigger
                bBaseDeDados::consultar($value);
            }
        }
    }

    /**
     * Faz o que é necessário para sincronizar uma chave estrangeira
     * 
     * @param XmlElement $xmlElement 
     */
    protected function parseForeignKey($db, $xmlElement, $tablesById)
    {
        $id = $xmlElement->getAttribute('xmi:id') . '';
        $name = strtolower($xmlElement->getAttribute('name') . '');
        $from = $xmlElement->getAttribute('from') . '';
        $from = $tablesById[$from];
        $fromName = $from->name;
        $to = $xmlElement->getAttribute('to') . '';
        $to = $tablesById[$to];
        $toName = $to->name;

        if ( !$toName || !$fromName )
        {
            return;
        }

        $fkDb = bCatalogo::obterChavesEstrangeiras($to->schema, $toName);

        /* $fromMultiplicity = $xmlElement->getAttribute('fromMultiplicity') . '';
          $toMultiplicity = $xmlElement->getAttribute('toMultiplicity') . '';

          if ( $fromMultiplicity == '1' && $toMultiplicity == '0..*' )
          {

          } */

        $myToColumn = $to->foreignKeys[$id];
        $toColumn = array();
        $fromColumn = array();

        if ( is_array($myToColumn) )
        {
            foreach ( $myToColumn as $line => $info )
            {
                $toColumn[] = $info->columnName;

                if ( $info instanceof bSyncDatabase )
                {
                    $fromColumnId = $info->getAttribute('refColumn');
                    $fromColumn[] = $from->indexedColumns[$fromColumnId];
                }
            }
        }

        $toColumn = implode(',', $toColumn);
        $fromColumn = implode(',', $fromColumn);

        $create = false;
        $drop = false;

        $fkeyDb = $fkDb[$name];

        if ( $fkeyDb )
        {
            if ( $fkeyDb->schema != $to->schema
                    || strtolower($fkeyDb->table) != strtolower($to->name)
                    || strtolower($fkeyDb->column) != strtolower($toColumn)
                    || strtolower($fkeyDb->schemaRef) != strtolower($from->schema)
                    || strtolower($fkeyDb->tableRef) != strtolower($from->name)
                    || strtolower($fkeyDb->columnRef) != strtolower($fromColumn)
            )
            {
                $create = true;
                $drop = true;
            }
        }
        else
        {
            $create = true;
        }

        if ( $create && $toColumn && $fromColumn )
        {
            if ( $drop )
            {
                $sql = "ALTER TABLE {$toName} DROP CONSTRAINT $name;";
                bBaseDeDados::consultar($sql);
            }

            $fromColumn = $this->reservedColumnNames($fromColumn);

            $this->addMessage("{$to->schema}.{$toName}: criando chave estrangeira '$name'.");

            $sql = "ALTER TABLE {$toName} ADD CONSTRAINT $name FOREIGN KEY ($toColumn) REFERENCES {$fromName} ( $fromColumn );";
            bBaseDeDados::consultar($sql);
        }
    }

    /**
     * Faz o que é necessário para a sincronização de uma tabela
     * 
     * @param type $xmlElement 
     */
    protected function parseTable($db, $xmlElement)
    {
        $schema = $xmlElement->getAttribute('schema');
        $schema = $schema ? $schema : 'public'; //esquema padrão
        $tableName = $xmlElement->getAttribute('name') . '';
        $tableNameWithSchema = $schema . '.' . $tableName;
        $tableId = $xmlElement->getAttribute('xmi:id') . '';

        //verifica se tabela existe
        $verificarExistenciaDaTabela = bCatalogo::verificarExistenciaDaTabela($schema, $tableName);
        
        //caso não existe faz script básico de criação de tabela
        if ( !$verificarExistenciaDaTabela )
        {
            bBaseDeDados::consultar($this->mountCreateTableSql($xmlElement));
        }

        $columsDb = bCatalogo::listarColunasDaTabela($tableName); //FIXME falta passar esquema
        $columnDbData = bCatalogo::obterColunasDaTabela($schema, $tableName, null);
        $columns = $this->getColumns($xmlElement);

        //monta um array com chaves primárias
        $primaryKeysDb = null;
        //array de colunas indexadas pelo seu id
        $indexedColumns = null;
        //chaves estrangeiras dessa tabela
        $foreignKeys = null;

        //comparando campo por campo
        foreach ( $columns as $line => $column )
        {
            $columnName = strtolower($column->getAttribute('name'));
            $columnNameReserved = $this->reservedColumnNames($columnName);
            $columnDb = $columnDbData[$columnName];
            $indexedColumns[$column->getAttribute('xmi:id') . ''] = strtolower($column->getAttribute('name') . '');

            //caso seja um campo de chave primária no banco, adiciona ao array
            if ( MUtil::getBooleanValue($columnDb->primaryKey) == true )
            {
                $primaryKeysDb[] = $this->reservedColumnNames($columnDb->column);
            }

            //guarda informações para posterior criação de chaves estrangeiras
            $foreignKeyConstraints = $column->foreignKeyConstraints->ownedMember;

            $foreignKeyId = $foreignKeyConstraints->getAttribute('foreignKey');

            //só adiciona na relação caso tenha id
            if ( $foreignKeyId )
            {
                $foreignKeyConstraints->columnName = $columnName;
                $foreignKeyConstraints->columnId = $column->getAttribute('xmi:id') . '';
                $foreignKeys[$foreignKeyId][] = $foreignKeyConstraints;
            }

            if ( $verificarExistenciaDaTabela )
            {
                //verifica se a coluna xml existe no banco
                if ( array_search($columnName, $columsDb) !== false )
                {
                    //verificação de tipo
                    $typeXml = $this->xmlTypeToDB($this->getColumnType($column,false));
                    $typeDB = strtolower($columnDb->type);
                    $lengthXml = strtolower($column->getAttribute('length'));
                    $lengthDB = strtolower($columnDb->length);

                    $doDiff = true;

                    //-5 significa sem limite par esse select, dessa forma não precisa aplicar correção de tamanho.
                    if ( $lengthDB == '-5' && $typeXml == 'varchar' && $typeDB == 'varchar' )
                    {
                        $doDiff = false;
                    }

                    if ( !$lengthXml )
                    {
                        $lengthXml = 255; //valor padrão do Vpp
                    }

                    //verifica necessiade de mudança de campo, só muda o tamanho se o do banco for menor
                    if ( $doDiff && ( $typeDB != $typeXml || ( ( $lengthXml > $lengthDB ) && $typeXml == 'varchar' ) ) )
                    {
                        //caso especial do varchar
                        if ( $typeXml == 'varchar' )
                        {
                            $typeDB .='(' . $lengthDB . ')';
                            $typeXml .='(' . $lengthXml . ')';
                        }

                        $this->addMessage("{$tableNameWithSchema}.{$columnName}: alterando tipo de '$typeDB' para '$typeXml'.");

                        //altera o tipo da coluna tentando forçar a conversão de tipo
                        bBaseDeDados::consultar("ALTER TABLE $tableNameWithSchema ALTER $columnNameReserved TYPE $typeXml USING \"$columnName\"::$typeXml;");
                    }
                }
                else //caso campo não exista cria
                {
                    $type = $this->getColumnType($column, true);
                    $this->addMessage("{$tableNameWithSchema}.{$columnName}: criando campo como '$type'.");
                    bBaseDeDados::consultar("ALTER TABLE $tableName ADD COLUMN $columnNameReserved $type;");
                }
            }

            //a partir deste ponto presume que o campo existe

            $uniqueDb = $columnDb->unique == 't';
            $uniqueXmi = $column->getAttribute('unique') == 'true';

            //são diferentes, precisa modificação na base, mas só se não for chave primária
            if ( $uniqueDb != $uniqueXmi && MUtil::getBooleanValue($columnDb->primaryKey) == false )
            {
                //tem no banco, mas não no Xmi, dropa
                if ( $uniqueDb && !$uniqueXmi )
                {
                    $checkName = "{$tableName}_{$columnName}_key";

                    //só dropa a constraint caso ela realmente exista
                    if ( bCatalogo::obterChecagens($schema, $tablename, $checkName) )
                    {
                        $this->addMessage("$tableNameWithSchema.$columnName: removendo unique.");
                        bBaseDeDados::consultar("ALTER TABLE ONLY $tableNameWithSchema DROP CONSTRAINT $checkName;");
                    }
                }
                //tem no Xmi , mas não no banco, adiciona
                else
                {
                    $this->addMessage("$tableNameWithSchema.$columnName: adicionando unique.");
                    bBaseDeDados::consultar("ALTER TABLE ONLY $tableNameWithSchema ADD CONSTRAINT {$tableName}_{$columnName}_key UNIQUE ($columnName);");
                }
            }

            //VALOR padrão
            $defaultValue = $column->getAttribute('defaultValue') . '';

            $idGenerator = strtolower($column->getAttribute('idGenerator') . '');
            $idGeneratorKey = $column->getAttribute('idGeneratorKey') . '';

            //da prioridade para sequência como valor padrão
            if ( $idGenerator == 'sequence' )
            {
                //caso não exista nome de sequência define o padrão
                if ( !$idGeneratorKey )
                {
                    $idGeneratorKey = 'seq_' . $columnName;
                }

                bCatalogo::criarSequenciaQuandoPossivel($idGeneratorKey);

                bBaseDeDados::consultar("ALTER TABLE $tableNameWithSchema ALTER COLUMN $columnNameReserved SET DEFAULT nextval('{$idGeneratorKey}'::regclass);");
            }
            else if ( $defaultValue )
            {
                //caso tenha um nextval no default value tentar obter o nome da sequencia e criar ela
                if ( stripos(trim($defaultValue), 'nextval') === 0 )
                {
                    $sequence = explode("('", trim($defaultValue));
                    $sequence = substr($sequence[1], 0, strlen($sequence[1]) - 2);

                    bCatalogo::criarSequenciaQuandoPossivel($sequence);
                }

                //pode ser aplicado SEMPRE, para nossa alegria
                bBaseDeDados::consultar("ALTER TABLE $tableNameWithSchema ALTER $columnNameReserved SET DEFAULT $defaultValue;");
            }

            //verificação de possibilidade de valores nulos
            $notNullXml = $column->getAttribute('nullable') == 'true';
            $notNullDB = $columnDb->notnull == 'f';
            
            //verifica necessidade de tirar ou colocar not null
            if ( $notNullDB != $notNullXml )
            {
                bBaseDeDados::consultar($this->mountSqlNotNull($column, $tableNameWithSchema));
            }

            //sempre atualiza comentário da coluna
            $columnComment = $this->getComment($column);

            bBaseDeDados::consultar("COMMENT ON COLUMN $tableNameWithSchema.$columnName IS '$columnComment';\n");
        }

        //a partir desse ponto pressume-se que a tabela esta criada e com os campos sincronizados
        //constraints e check
        //guarda para aplicar depois
        $sqlCheck = ($this->getTableConstraints($xmlElement, $schema, $tableName, $db));

        //chaves primárias
        bBaseDeDados::consultar($this->getSqlPrimaryKey($xmlElement, $schema, $tableName, $primaryKeysDb));

        //indices
        bBaseDeDados::consultar($this->getSqlIndex($xmlElement, $schema, $tableName, $indexedColumns, $db));

        //comentário da tabela
        $columnComment = $this->getComment($xmlElement) . '';

        if ( $columnComment )
        {
            bBaseDeDados::consultar("COMMENT ON TABLE $tableNameWithSchema IS '" . $columnComment . "';");
        }
        
        $this->sincronizarHeranca($xmlElement, $schema, $tableName);

        $tableInfo = new stdClass();
        $tableInfo->name = $tableName;
        $tableInfo->schema = $schema;
        $tableInfo->id = $tableId;
        $tableInfo->foreignKeys = $foreignKeys;
        $tableInfo->indexedColumns = $indexedColumns;
        $tableInfo->sqlCheck = $sqlCheck;

        return $tableInfo;
    }
    
    public function sincronizarHeranca($xmlElement, $schema, $tableName)
    {
        $ddlClauses = $xmlElement->getAttribute('ddlClauses');
        
        if ( $ddlClauses )
        {
            $ddlClauses = explode('(', $ddlClauses);
            
            if ( trim(strtolower($ddlClauses[0])) == 'inherits' )
            {
                $tabelaXml = strtolower(str_replace(')', '', $ddlClauses[1] ));
                $inherit = bCatalogo::obterHeranca($schema, $tableName);
                $tabelaDB = $inherit->table;
                
                if ( $tabelaXml && $tabelaDB)
                {
                    if ( $tabelaXml != $tabelaDB )
                    {
                        throw new Exception( "Inconsistência na herança de na tabela '$schema.$tableName', está apontando '$tabelaXml' no xml e '$tabelaDB' no banco.");
                    }
                }
                else if ( $tabelaXml && !$tabelaDB )
                {
                    $this->addMessage("Adicionando herança '$tabelaXml' na tabela '$schema.$tableName'.");
                    
                    //FIXME tornar genérico
                    if ( $tabelaXml == 'baslog')
                    {
                        bCatalogo::adicionarColuna($schema, $tableName, 'username', 'varchar(20)');
                        bCatalogo::adicionarColuna($schema, $tableName, 'datetime', 'timestamptz');
                        bCatalogo::adicionarColuna($schema, $tableName, 'ipaddress', 'inet');
                        bBaseDeDados::consultar("ALTER TABLE basconfig ALTER username SET default 'curent_user';");
                        bBaseDeDados::consultar("ALTER TABLE basconfig ALTER datetime SET default 'now()';");
                    }
                   
                    bBaseDeDados::consultar( "ALTER TABLE $schema.$tableName INHERIT $tabelaXml;" );
                    
                }
                else if ( $tabelaDB && !$tabelaXml )
                {
                    $this->addMessage("Herança '$tabelaDb' sobrando na tabela '$shema.$tableNama'.");  
                }
            }
        
        }
    }

    /**
     * Lista os campos do xml
     * 
     * @param array $xmlTableElement 
     */
    public function listColumns($xmlTableElement)
    {
        $childs = $xmlTableElement->children();
        $columns = $childs[0]->ownedMember;

        foreach ( $columns as $line => $column )
        {
            $xmiType = $column->getAttribute('xmi:type');

            if ( $xmiType == 'dbColumn' )
            {
                $result[] = $column->getAttribute('name');
            }
        }

        return $result;
    }

    /**
     * Retorna um array de stdClass de colunas
     * 
     * @param XmlElement $xmlTableElement
     * @return XMlElement 
     */
    public function getColumns($xmlTableElement)
    {
        $childs = $xmlTableElement->children();
        $ownedMember = $childs[0]->ownedMember;

        foreach ( $ownedMember as $line => $column )
        {
            if ( $column->getAttribute('xmi:type') == 'dbColumn' )
            {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Aplica os índices do XMI no BD, caso necessário.
     * Não apaga índices adicionais que existam no banco.
     * 
     * @param XmlElement $xmlTableElement
     * @param string $schema esquema
     * @param string $table tabela
     * @param bDatabase $db objeto para execucação de base de dados
     * 
     * @return XMlElement 
     */
    public function getSqlIndex($xmlTableElement, $schema, $table, $indexedColumns, $db)
    {
        $childs = $xmlTableElement->children();
        $ownedMember = $childs[0]->ownedMember;

        //separa somente os indices
        foreach ( $ownedMember as $line => $index )
        {
            if ( $index->getAttribute('xmi:type') == 'dbIndex' )
            {
                $indexs[] = $index;
            }
        }

        //obtem os indices do banco (array indexado pelo nome do índice)
        $indexesDb = bCatalogo::obterIndices($schema, $table);

        if ( is_array($indexs) )
        {
            foreach ( $indexs as $line => $index )
            {
                $indexColumns = $index->columns->column;
                $indexName = strtolower($index->getAttribute('name') . '');

                $realColumnNames = null;

                //procura pelos nomes reais das colunas, pois o vpp só guarda o seu id interno
                foreach ( $indexColumns as $line => $indexColumn )
                {
                    $idRef = $indexColumn->getAttribute('xmi:idref') . '';
                    //nome real as colunas
                    $realColumnNames[] = strtolower($indexedColumns[$idRef]);
                }

                //procura esse indice nos indices do banco
                $indexDb = $indexesDb[$indexName];

                //por padrão não cria nem exclui os índices
                $create = false;
                $drop = false;

                if ( $indexDb )
                {
                    //caso exista mas a relação de campos seja diferente, determina exclusão e criação
                    if ( $realColumnNames != $indexDb->columns )
                    {
                        $drop = true;
                        $create = true;
                    }
                }
                else //caso o índice não existe determina a sua criação
                {
                    $create = true;
                }

                //remoção
                if ( $drop )
                {
                    $this->addMessage("$schema.$table: removendo índice '$indexName'.");
                    $sql[] = "DROP INDEX $indexName;";
                }

                //criação
                if ( $create )
                { 
                    $sql[] = "DROP INDEX IF EXISTS  $indexName;";
                    $realColumnNames = implode(', ', $realColumnNames);
                    $this->addMessage("$schema.$table: criando índice '$indexName' nas coluna(s) '$realColumnNames'.");
                    $sql[] = "CREATE INDEX $indexName ON $schema.$table ($realColumnNames);";
                }
            }
        }

        return $sql;
    }

    /**
     * Aplica constraint a tabela
     * 
     * @param XmlElement $xmlTableElement elemento xml
     * @param string $schema esquema
     * @param string $tableName nome da tabela
     * @param object $db objeto para execução das instruções, necessário para obter os checks do banco
     * @return string 
     */
    public function getTableConstraints($xmlTableElement, $schema, $tableName, $db)
    {
        //seleciona as constraints no xmi
        $childs = $xmlTableElement->children();
        $constraints = $childs[1]->ownedMember;

        //obtem os checks no banco
        $dbChecks = bCatalogo::obterChecagens($schema, $tableName);

        if ( is_array($constraints) )
        {
            //passa pelas contraints verificando necessidade de inserção/atualização
            foreach ( $constraints as $line => $constraint )
            {
                $cName = $constraint->getAttribute('name');
                $checkConstraint = $constraint->getAttribute('checkConstraint');
                $type = $constraint->getAttribute('xmi:type');

                //variáveis auxiliares
                $found = false;
                $foundedCheck = '';
                $drop = false;

                //tenta localizar os checks do xmi no banco
                if ( is_array($dbChecks) )
                {
                    foreach ( $dbChecks as $line => $check )
                    {
                        //caso já exista define que tem que aplicar o alter
                        if ( strtolower($check->name) == strtolower($cName) )
                        {
                            //informa que achou e oque achou
                            $found = true;
                            $foundedCheck = $check->check;
                        }
                    }
                }

                //caso tenha encontrado, tenta verificar se o check foi modificado
                if ( $found && $foundedCheck != $checkConstraint )
                {
                    $found = false;
                    $drop = true;
                }

                //só insere o check caso seja o tipo certo e ele não existir no banco
                if ( $type == 'dbCheckConstraint' && !$found )
                {
                    //caso seja diferente drop para poder inserir
                    if ( $drop )
                    {
                        $sql[] = "ALTER TABLE $schema.$tableName DROP CONSTRAINT $cName;";
                    }

                    $this->addMessage("$schema.$tableName: atualizando check '$cName'.");
                    $sql[] = "ALTER TABLE $schema.$tableName ADD CONSTRAINT $cName CHECK ( $checkConstraint );";
                }
            }
        }

        return $sql;
    }

    /**
     * Obtem o sql para aplicação de chaves primárias
     * 
     * @param type $xmlTableElement 
     * @return string $sql
     */
    public function getSqlPrimaryKey($xmlTableElement, $schema, $tableName, $primaryKeysDb = null)
    {
        $columns = $this->getColumns($xmlTableElement);

        //passa pelas colunas listando as que são chaves primárias
        foreach ( $columns as $line => $column )
        {
            $xmiType = $column->getAttribute('xmi:type');

            if ( $xmiType == 'dbColumn' )
            {
                if ( $column->getAttribute('primaryKey') == 'true' )
                {
                    $primaryKeys[] = $this->reservedColumnNames($column->getAttribute('name') . '');
                }
            }
        }

        //gera chave constraint primária
        if ( is_array($primaryKeys) && $primaryKeys != $primaryKeysDb )
        {
            $primaryKeyConstraintName = $xmlTableElement->getAttribute('primaryKeyConstraintName');

            //caso não exista um nome definido, cria o padrão
            if ( !$primaryKeyConstraintName )
            {
                $primaryKeyConstraintName = "{$tableName}_pkey";
            }

            $primaryString = trim(implode(',', $primaryKeys));

            //caso já exista no banco, precisa dropar
            if ( $primaryKeysDb )
            {
                //pode ser cascade quando chegar nas outras tabelas vai criar as relações
                $sql[] = "ALTER TABLE ONLY $schema.$tableName DROP CONSTRAINT $primaryKeyConstraintName CASCADE;";
            }

            //segurança
            if ( $primaryString )
            {
                $this->addMessage("$schema.$tableName: adicionando chave primária '$primaryKeyConstraintName'");
                $sql[] = "ALTER TABLE ONLY $schema.$tableName ADD CONSTRAINT $primaryKeyConstraintName PRIMARY KEY ( $primaryString );";
            }
        }

        return $sql;
    }

    /**
     * Monta sql de criação de tabela
     * 
     * @param XmlElement $xmlElement
     * @return string
     */
    protected function mountCreateTableSql($xmlTableElement)
    {
        $tableName = $xmlTableElement->getAttribute('name');
        $schema = $xmlTableElement->getAttribute('schema');
        $schema = $schema ? $schema : 'public'; //esquema padrão

        $this->addMessage("$schema.$tableName: criando tabela.");

        $columns = $this->getColumns($xmlTableElement);

        foreach ( $columns as $line => $column )
        {
            $xmiType = $column->getAttribute('xmi:type');

            if ( $xmiType == 'dbColumn' )
            {
                $columnsSql[] = $this->reservedColumnNames($column->getAttribute('name')) . ' ' . $this->getColumnType($column, true);
            }
        }
        
        //clausulas de criação de tabelas, utilizado. por exemplo, para heranças
        $ddlClauses = $xmlTableElement->getAttribute('ddlClauses');

        return 'CREATE TABLE ' . $tableName . " ( \n" . implode(', ', $columnsSql) . " ) $ddlClauses;\n\n";
    }

    /**
     * Obtém comentário de tabela ou coluna
     * 
     * @param XmlElement $xmlElement
     * @return string 
     */
    public function getComment($xmlElement)
    {
        $xmi = $xmlElement->children('xmi', true);
        $xmi = $xmi[0];

        if ( $xmi )
        {
            $documentation = $xmi->children()->documentation;
            return $documentation->getAttribute('body');
        }

        return '';
    }

    /**
     * Obtem tipo da coluna.
     * 
     * O VPP tem duas colunas com o tipo o typeName e o type.
     * 
     * Type é o valor mais correto, mas é um integer, em função disso essa
     * função foi criada, para escolher o tipo certo.
     * 
     * @param string $column 
     */
    public function getColumnType($column, $formated = false)
    {
        //caso tenha tipo do usuário retorna ele e pronto
        $userType = $column->columnUserTypes->ownedMember->getAttribute('type').'';
        
        if (  $userType != '' &&  strtolower($userType)  != 'serial' )
        {
            //caso não for formatado tira o tamanaho para o caso do varchar
            if ( $formated == false)
            {
                $userType = explode('(', $userType);
                $userType = $userType[0];
            }
            
            return $userType;
        }
        
        $typeName = $column->getAttribute('typeName');
        $length = $column->getAttribute('length');
        $typeInt = $column->getAttribute('type');
        
        //array que relaciona typeName com type
        $types = array( );
        $types[1] = 'bool';
        $types[4] = 'float4';
        $types[6] = 'double precision';
        $types[9] = 'integer';
        $types[16] = 'bigint';
        $types[17] = 'date';
        $types[22] = 'timestamp';
        $types[27] = 'varchar';
        $types[34] = 'text';
        $types[42] = 'char';

        //condições especiais para o vpp
        $type = $types[$typeInt];

        //caso não ache pelo código do tipo tenta obter pelo nome
        //isso é feito assim pois algumas vezes o VPP gera o typeName errado
        if ( !$type )
        {
            $type = $typeName;
        }

        if ( $type == 'varchar' && $formated )
        {
            $length = $length ? $length : 255; //valor padrão do VPP
            $type .= '(' . $length . ')';
        }

        return strtolower($type);
    }

    /**
     * Converte um tipo do xml para o banco de dados
     * 
     * @param string $xmlType
     * 
     * @return string 
     */
    protected function xmlTypeToDB($xmlType)
    {
        $xmlType = strtolower($xmlType);

        //array de - para
        $dePara['integer'] = 'int4';
        $dePara['bigint'] = 'int8';
        $dePara['char'] = 'bpchar';
        $dePara['double precision'] = 'float8';
	$dePara['timestamp with time zone'] = 'timestamptz';

        $type = $dePara[$xmlType];

        if ( !$type )
        {
            $type = $xmlType;
        }

        return $type;
    }

    /**
     * Monta sqls para adição/remoção de not null
     * 
     * @param xmlElement $column
     * @param string $name nome da tabela
     * @return string sql
     */
    public function mountSqlNotNull($column, $tablename)
    {
        $columnName = $column->getAttribute('name') . '';
        $messageString = $column->getAttribute('nullable') == 'true' ? 'Removendo' : 'Adicionando';
        $this->addMessage("{$tablename}.{$columnName}: $messageString NOT NULL.");

        $nullable = $column->getAttribute('nullable') == 'true';
        $nullableString = $nullable ? 'DROP' : 'SET';

        $value = false;
        $type = $this->getColumnType($column);

        //evita erros na aplicação do not null
        if ( $type == 'varchar' || $type == 'text' )
        {
            $value = '';
        }
        else if ( $type == 'integer' || $type == 'bool' )
        {
            $value = '0';
        }

        $defaultValue = $column->getAttribute('defaultValue') . '';
        
                if ($defaultValue == "'gnuteca3'")
                {
                        $defaultValue = "gnuteca3";
                }

	//caso tenha um valor padrão, aplica-o
        //não obtem valor padrão caso tenha um "nextval" nele
        if ( ($defaultValue || $defaultValue == '0' ) && stripos($defaultValue, 'nextval') === false )
        {
            $value = $defaultValue;
        }

        //só adiciona o update caso precise, só quando definindo
        if ( $value !== false && $nullableString == 'SET' )
        {
            $columnName = $this->reservedColumnNames($column->getAttribute('name'));
	    $value = ($value == "'gnuteca'")? 'gnuteca3':$value;
            $sql = "UPDATE $tablename SET {$columnName} = '$value' WHERE {$columnName} IS NULL;\n";
        }

        $columName = $this->reservedColumnNames($column->getAttribute('name'));

        return "$sql ALTER TABLE $tablename ALTER $columName $nullableString NOT NULL;";
    }

    /**
     * Trata o nome de coluna para nomes reservados
     * 
     * @param string $columName nome da coluna
     * @return string nome da coluna tratado
     */
    public function reservedColumnNames($columName)
    {
        if ( strtolower($columName) == 'column' )
        {
            $columName = '"' . $columName . '"';
        }

        return trim(strtolower($columName));
    }
}

?>
