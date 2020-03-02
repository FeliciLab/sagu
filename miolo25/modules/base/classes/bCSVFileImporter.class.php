<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Class
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 11/06/2012
 *
 **/
set_time_limit(0);

$MIOLO = MIOLO::getInstance();
$MIOLO->uses( 'classes/bBaseDeDados.class.php','base');

if ( !defined('DB_NAME') )
{
    define('DB_NAME', 'basic' );
}

class bCSVFileImporter
{
    /**
     * Nome do arquivo
     *
     * @var string
     */
    private $fileName;
    
    /**
     * Conteudo do arquivo
     * 
     * @var string
     */
    private $fileContents;
    
    /**
     * Array de conteudo do CSV quebrado por linha
     *
     * @var array
     */
    private $csvFileData = array();
    
    /**
     * Primeira linha do CSV
     *
     * @var array
     */
    private $headerLine = array();
    
    /**
     * Delimitador utilizado no arquivo CSV
     * 
     * @var string
     */
    private $delimiter = ';';
    
    /**
     * Indica se deve ser feito os SQLS de importacao dentro de uma transacao (BEGIN e COMMIT)
     *
     * @var boolean
     */
    private $checkTransaction = false;
    
    /**
     * Indica que deve ser executado um ROLLBACK ao final da importacao,
     *  e desfazer todas insercoes no banco (util para debug)
     */
    private $executarRollback = false;
    
    /**
     * Indica se ja foi executado a verificacao
     *
     * @var type 
     */
    private $hasChecked = false;
    
    /**
     * Indica se arquivo ja foi carregado
     *
     * @var type 
     */
    private $hasLoadedFile = false;
    
    /**
     * Objeto de colunas do CSV que representam
     * 
     * @var array Array do tipo bCSVColumn
     */
    private $columns = array();
    
    /**
     * Array com erros ocorridos na validacao
     *
     * @var array
     */
    private $errorLog = array();
    
    
    /**
     * Obtem a linha atual que esta sendo processada
     *
     * @var string
     */
    private $currentLine = array();
    
    
    /**
     * Nome de tabela temporaria que armazena dados do CSV
     * 
     * @var string
     */
    private $tmpTableName = 'tmpcsvimporter';
    
    /**
     * SQLs que devem ser executados antes da validacao
     *
     * @var array
     */
    private $sqlsBefore = array();
    
    /**
     * SQLs que devem ser executados depois da validacao
     *
     * @var array
     */
    private $sqlsAfter = array();
    
    
    /**
     * Numero limite de registros que devem ser importados do CSV,
     *  ordenando pela primeira linha em diante do arquivo.
     * 
     * @var int
     */
    private $limitRecords = 9999999;

    /**
     * Considera a primeira linha como sendo o cabecalho do arquivo CSV. Padrao FALSO.
     *
     * @var boolean
     */
    private $ignoreFirstLine = false;

    public function __construct()
    {
    }
    
    public function loadFile($fileName, $delimiter = null)
    {
        $this->setDelimiter($delimiter);
        $this->setFileName($fileName);
        
        $this->hasLoadedFile = true;
    }
    
    /**
     * Retorna se todo conteudo do arquivo CSV esta valido (nao realiza importacao)
     * 
     * @return boolean Retorna TRUE caso todas validacoes estejam OK
     */
    public function check()
    {
        if ( !$this->hasLoadedFile )
        {
            throw new Exception( _M('O arquivo ainda não foi carregado.') );
        }
        
        // Valida se foi definido colunas
        if ( !$this->columns )
        {
            throw new Exception( _M('Devem ser definidas colunas para o arquivo CSV.') );
        }
        
        // Valida se colunas definidas existem na planilha CSV
        foreach ( $this->columns as $col )
        {
            if ( !in_array($col->getName(), $this->headerLine) )
            {
//                throw new Exception(_M('A coluna @1 nao esta presente no cabecalho do arquivo CSV.', null, $col->getName()));
            }
        }
        
        $countHeader = count($this->headerLine);
        $countCols = count($this->columns);
        if ( $countHeader != $countCols )
        {
            throw new Exception( _M('O numero de colunas definidas (@1) nao bate com o numero de colunas do arquivo CSV. (@2)', null, $countCols, $countHeader) );
        }
        
        // Verifica nome repetido de colunas
        $headers = array_filter($this->headerLine);
        if ( count(array_unique($headers)) != count($headers) )
        {
//            throw new Exception( _M('Existem coluna(s) com nome repetido na planilha.') );
        }
        
        $this->createTempTable();
        $this->hasChecked = true;

        return count($this->errorLog) == 0;
    }
    
    /**
     * Importa o arquivo CSV, apenas caso tenha passado pela validacao
     * 
     * @return boolean
     */
    public function import()
    {
        $ok = $this->check();
        
        if ( $ok )
        {
            if ( $this->getCheckTransaction() )
            {
                bBaseDeDados::iniciarTransacao();
            }
            
            $this->executarBeforeImport();

            $cols = array_merge(array('linha'), $this->generateCreateTableColumns(false));
            
            $colsStr = implode(',', $cols);
            $rows = bBaseDeDados::consultar("SELECT {$colsStr} FROM {$this->tmpTableName} order by linha LIMIT {$this->limitRecords}");

            foreach ( $rows as $key => $row )
            {
                $line = new stdClass();
                
                foreach ( $cols as $key => $col )
                {
                    $value = $row[$key];
//                    $value = new BString($value, mb_detect_encoding($value));
//                    $value = $this->toASCII($value);
                    if ( in_array(strtolower($value), bCSVColumn::$booleanRangesAll) )
                    {
                        $value = strtolower($value);
//                        $value = $value->toLower();
                    }
//                    $value = $value->__toString();
                    $value = stripslashes($value);
                    $value = addslashes($value);
                    
                    $line->$col = $value;
                }

                $this->importLine($line);
            }
            
            $this->executarAfterImport();
            
            if ( $this->getCheckTransaction() )
            {
                $this->getExecuteRollback() ? bBaseDeDados::reverterTransacao() : bBaseDeDados::finalizarTransacao();
            }
        }
        else
        {
//            throw new Exception( _M('O arquivo CSV não está com os dados validados, verificar.') );
        }

        return $ok;
    }
    
    /**
     * Chamada executada antes de realizar a importacao 
     */
    public function executarBeforeImport()
    {
    }

    /**
     * Chamada executada depois de realizar a importacao 
     */
    public function executarAfterImport()
    {
    }
    
    /**
     * Percorre cada linha do arquivo CSV
     *
     * @param type $data 
     */
    public function importLine($data)
    {
    }
    
    public function getCheckTransaction()
    {
        return $this->checkTransaction;
    }

    public function setCheckTransaction($checkTransaction)
    {
        $this->checkTransaction = $checkTransaction;
    }
    
    public function getFileName()
    {
        return $this->fileName;
    }

    private function setFileName($fileName)
    {
        if ( !file_exists($fileName) )
        {
            throw new Exception(_M('O arquivo @1 nao foi encontrado.', null, $fileName));
        }
        
        $this->fileName = $fileName;
        $this->fileContents = file_get_contents($fileName);
        $this->csvFileData = explode("\n", $this->fileContents);
        $this->headerLine = explode($this->delimiter, trim($this->csvFileData[0]));
        
        // TODO Aplicar outras validacoes basicas do CSV se esta correto
    }
    
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setDelimiter($delimiter = null)
    {
        if ( strlen($delimiter) > 0 )
        {
            $this->delimiter = $delimiter;
        }
    }
    
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    public function setErrorLog($errorLog)
    {
        $parsed = array();
        
        foreach ( (array) $errorLog as $line )
        {
            $parsed[ $line[0] ] = $line[1];
        }
        
        $this->errorLog = $parsed;
    }
    
    public function getCurrentLine()
    {
        return $this->currentLine;
    }

    public function setCurrentLine($currentLine)
    {
        $this->currentLine = $currentLine;
    }    
    
    public function getExecuteRollback()
    {
        return $this->executarRollback;
    }

    public function setExecuteRollback($executarRollback)
    {
        $this->executarRollback = $executarRollback;
    }
    
    public function getFileContents()
    {
        return $this->fileContents;
    }

    public function setFileContents($fileContents)
    {
        $this->fileContents = $fileContents;
    }

    public function getCsvFileData()
    {
        return $this->csvFileData;
    }

    public function setCsvFileData($csvFileData)
    {
        $this->csvFileData = $csvFileData;
    }

    public function getHeaderLine()
    {
        return $this->headerLine;
    }

    public function setHeaderLine($headerLine)
    {
        $this->headerLine = $headerLine;
    }

    public function getHasChecked()
    {
        return $this->hasChecked;
    }

    public function setHasChecked($hasChecked)
    {
        $this->hasChecked = $hasChecked;
    }

    public function getHasLoadedFile()
    {
        return $this->hasLoadedFile;
    }

    public function setHasLoadedFile($hasLoadedFile)
    {
        $this->hasLoadedFile = $hasLoadedFile;
    }

    public function getTmpTableName()
    {
        return $this->tmpTableName;
    }

    public function setTmpTableName($tmpTableName)
    {
        $this->tmpTableName = $tmpTableName;
    }
    
    public function getLimitRecords()
    {
        return $this->limitRecords;
    }

    public function setLimitRecords($limitRecords)
    {
        $this->limitRecords = $limitRecords;
    }

    public function getIgnoreFirstLine()
    {
        return $this->ignoreFirstLine;
    }

    public function setIgnoreFirstLine($ignoreFirstLine)
    {
        $this->ignoreFirstLine = $ignoreFirstLine;
    }

    public function getSqlsBefore()
    {
        return $this->sqlsBefore;
    }

    public function setSqlsBefore($sqlsBefore)
    {
        $this->sqlsBefore = $sqlsBefore;
    }

    public function getSqlsAfter()
    {
        return $this->sqlsAfter;
    }

    public function setSqlsAfter($sqlsAfter)
    {
        $this->sqlsAfter = $sqlsAfter;
    }

                
    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns(array $columns)
    {
        foreach ( $columns as $col )
        {
            $this->addColumn($col);
        }
    }
    
    /**
     * Define colunas a partir de um array passado
     *
     * @param array $typeDefs
     * @param array $defs 
     */
    public function setColumnsArray(array $typeDefs, array $defs)
    {
        foreach ( $defs as $def )
        {
            $column = new bCSVColumn();
            foreach ( $typeDefs as $key => $func )
            {
                $method = 'set' . ucfirst($func);
                $value = $def[$key];
                
                if ( $value )
                {
                    $column->$method( $value );
                }
            }
            
            $this->addColumn($column);
        }
    }
    
    public function addColumn(bCSVColumn $column)
    {
        $column->_validateParams();
        
        $this->columns[] = $column;
    }
    
    /**
     * Gera tabela temporaria com os dados do CSV
     */
    private function createTempTable()
    {
        // Cria tabela temporaria
        $colsStr = $this->generateCreateTableColumnsString(true);
        bBaseDeDados::executar("DROP TABLE IF EXISTS {$this->tmpTableName}");
        bBaseDeDados::executar("CREATE TABLE {$this->tmpTableName}({$colsStr}) WITH OIDS");
        bBaseDeDados::executar("VACUUM {$this->tmpTableName}");

        // Importa arquivo CSV diretamente via base de dados
        bBaseDeDados::executar("COPY {$this->tmpTableName} FROM '{$this->fileName}' DELIMITERS '{$this->delimiter}' CSV");

        // Adiciona coluna com numero da linha e erros
        bBaseDeDados::executar("ALTER TABLE {$this->tmpTableName} ADD linha SERIAL");
        bBaseDeDados::executar("ALTER TABLE {$this->tmpTableName} ADD erros TEXT");

        // Remove a primeira linha do arquivo
        if ( $this->getIgnoreFirstLine() )
        {
            bBaseDeDados::executar("DELETE FROM {$this->tmpTableName} WHERE oid::int = (SELECT MIN(oid)::int FROM {$this->tmpTableName})");
        }

        // Define o numero da linha igualada a do arquivo CSV
        bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET linha = ( oid::int - (SELECT MIN(oid)::int FROM {$this->tmpTableName}) + 1 )");

        // Apaga primeiro registro que e o cabecalho do CSV
        bBaseDeDados::executar("DELETE FROM {$this->tmpTableName} WHERE linha > ({$this->limitRecords} + 1)");

        // Atualiza valores booleanos
        foreach ( $this->columns as $col )
        {
            $colName = $col->getName();
            bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET {$colName} = TRIM({$colName})");

            if ( $col->getType() == bCSVColumn::TYPE_BOOLEAN )
            {
                $values = SAGU::quoteArrayStrings(bCSVColumn::$booleanRangesTrue);
                $values = implode(',', $values);
                bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET {$colName} = 't' WHERE lower({$colName}) IN ({$values})");
                
                $values = SAGU::quoteArrayStrings(bCSVColumn::$booleanRangesFalse);
                $values = implode(',', $values);
                bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET {$colName} = 'f' WHERE lower({$colName}) IN ({$values})");
            }
            
            // Faz substituicoes de valores
            $repVars = $col->getReplaceVars();
            foreach ( $repVars as $old => $new )
            {
                bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET {$colName} = '{$new}' WHERE lower({$colName}) = lower('{$old}')");
            }
        }
        
        foreach ( $this->sqlsBefore as $sql )
        {
            bBaseDeDados::executar($sql);
        }
        
        // Aplica validacoes 
        $cases = $this->generateSQLValidators();
        $cases = implode(' || ', $cases);
        bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET erros = {$cases}");
        bBaseDeDados::executar("UPDATE {$this->tmpTableName} SET erros = trim(both E'\n' FROM erros)");

        foreach ( $this->sqlsAfter as $sql )
        {
            bBaseDeDados::executar($sql);
        }
        
        // Coleta os erros de validacao
        $result = bBaseDeDados::consultar("SELECT linha,erros FROM {$this->tmpTableName} where erros <> '' order by linha LIMIT {$this->limitRecords}");
        $this->setErrorLog( $result );
    }
    
    /**
     * Gera colunas para o CREATE TABLE
     * 
     * @return array
     */
    public function generateCreateTableColumns($includeType = true)
    {
        // Gera colunas para CREATE TABLE
        $cols = array();
        for ($i=0; $i < count($this->columns); $i++)
        {
            $colName = MUtil::NVL($this->columns[$i]->getName(), "col{$i}");
            $colName = trim($colName);
            $cols[] = $colName . ($includeType ? ' varchar' : null);
        }

        return $cols;
    }
    
    public function generateCreateTableColumnsString($includeType = null)
    {
        $cols = $this->generateCreateTableColumns($includeType);
        $colsStr = implode(',', $cols);
        
        return $colsStr;
    }
    
    
    /**
     * Gera condicoes SQL de validacoes das linhas do CSV.
     * Foi feito via base de dados por questoes de performance.
     *
     * @return array
     */
    private function generateSQLValidators()
    {
        $col = new bCSVColumn();
        $cases = array();
        
        foreach ( $this->columns as $key => $col )
        {
            $col->setColPosition($key);
            $cases = array_merge($cases, $col->getValidateExpressions());
        }

        return $cases;
    }
    
    
    public function toASCII($string)
    {
        $value = new bString($string, 'utf-8');
        $value = $value->toASCII();
        
        return $value;
    }
    
    
}
?>
