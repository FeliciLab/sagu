<?php
class MISR
{
    public $phonetic;
    public $stopWords;
    private $conn; // connection identifier
    private $db;   // database identifier
    private $tableId;
    private $fieldId;
    private $transaction;
    private $translevel;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->db = $conn->db;
        $this->phonetic = new MPhonetic();
        $this->setStopWords();
        $this->fieldId = array();
        $this->transaction = NULL;
        $this->translevel = 0;
    }

    private function beginTransaction()
    {
        if (!$this->translevel)
        {
           $this->transaction = $this->db->getTransaction();
           $this->transaction->begin();
        }
        $this->translevel++;
        return $this->transaction;
    }

    private function endTransaction()
    {
        $this->translevel--;
        if (!$this->translevel)
        {
           $this->transaction = NULL;
        }
    }

    private function setStopWords()
    {
        $this->stopWords = array
            (
            'QUE',
            'PARA',
            'COM',
            'NÃO',
            'UMA',
            'POR',
            'MAIS',
            'DOS',
            'COMO',
            'MAS',
            'FOI',
            'ELE',
            'DAS',
            'TEM',
            'SEU',
            'SUA',
            'SER',
            'QUANDO',
            'MUITO',
            'NOS',
            'ESTÃ',
            'TAMBÃM',
            'PELO',
            'PELA',
            'ATÃ',
            'ISSO',
            'ELA',
            'ENTRE',
            'ERA',
            'DEPOIS',
            'SEM',
            'MESMO',
            'AOS',
            'SEUS',
            'QUEM',
            'NAS',
            'ESSE',
            'ESTÃO',
            'VOCÃ',
            'ESSA',
            'NUM',
            'NEM',
            'SUAS',
            'MEU',
            'MINHA',
            'NUMA',
            'PELOS',
            'ELAS',
            'HAVIA',
            'SEJA',
            'QUAL',
            'NÃS',
            'LHE',
            'DELES',
            'ESSAS',
            'ESSES',
            'PELAS',
            'ESTE',
            'DELE',
            'VOCÃS',
            'VOS',
            'LHES',
            'MEUS',
            'MINHAS',
            'TEU',
            'TUA',
            'TEUS',
            'TUAS',
            'NOSSO',
            'NOSSA',
            'NOSSOS',
            'NOSSAS',
            'DELA',
            'DELAS',
            'ESTA',
            'ESTES',
            'ESTAS',
            'AQUELE',
            'AQUELA',
            'AQUELES',
            'AQUELAS',
            'ISTO',
            'AQUILO',
            'ESTOU',
            'ESTÃ',
            'ESTAMOS',
            'ESTÃO',
            'ESTIVE',
            'ESTEVE',
            'ESTIVEMOS',
            'ESTIVERAM',
            'ESTAVA',
            'ESTÃVAMOS',
            'ESTAVAM',
            'ESTIVERA',
            'ESTIVÃRAMOS',
            'ESTEJA',
            'ESTEJAMOS',
            'ESTEJAM',
            'ESTIVESSE',
            'ESTIVÃSSEMOS',
            'ESTIVESSEM',
            'ESTIVER',
            'ESTIVERMOS',
            'ESTIVEREM',
            'HEI',
            'HAVEMOS',
            'HÃO',
            'HOUVE',
            'HOUVEMOS',
            'HOUVERAM',
            'HOUVERA',
            'HOUVÃRAMOS',
            'HAJA',
            'HAJAMOS',
            'HAJAM',
            'HOUVESSE',
            'HOUVÃSSEMOS',
            'HOUVESSEM',
            'HOUVER',
            'HOUVERMOS',
            'HOUVEREM',
            'HOUVEREI',
            'HOUVERÃ',
            'HOUVEREMOS',
            'HOUVERÃO',
            'HOUVERIA',
            'HOUVERÃAMOS',
            'HOUVERIAM',
            'SOU',
            'SOMOS',
            'SÃO',
            'ERA',
            'ÃRAMOS',
            'ERAM',
            'FUI',
            'FOI',
            'FOMOS',
            'FORAM',
            'FORA',
            'FÃRAMOS',
            'SEJA',
            'SEJAMOS',
            'SEJAM',
            'FOSSE',
            'FÃSSEMOS',
            'FOSSEM',
            'FOR',
            'FORMOS',
            'FOREM',
            'SEREI',
            'SERÃ',
            'SEREMOS',
            'SERÃO',
            'SERIA',
            'SERÃAMOS',
            'SERIAM',
            'TENHO',
            'TEM',
            'TEMOS',
            'TÃM',
            'TINHA',
            'TÃNHAMOS',
            'TINHAM',
            'TIVE',
            'TEVE',
            'TIVEMOS',
            'TIVERAM',
            'TIVERA',
            'TIVÃRAMOS',
            'TENHA',
            'TENHAMOS',
            'TENHAM',
            'TIVESSE',
            'TIVÃSSEMOS',
            'TIVESSEM',
            'TIVER',
            'TIVERMOS',
            'TIVEREM',
            'TEREI',
            'TERÃ',
            'TEREMOS',
            'TERÃO',
            'TERIA',
            'TERÃAMOS',
            'TERIAM'
            );
    }

    private function isValid($word)
    {
        $isValid = false;

        if (strlen($word) > 2)
        {
            $isValid = (array_search($word, $this->stopWords) === false);
        }

        return $isValid;
    }

    private function getTokens($string, &$array)
    {
        $tok = strtok($string, " ");

        while ($tok)
        {
            $tok =  strtoupper(trim($tok));

            if ($this->isValid($tok))
            {
                $array[$tok] = $tok;
            }
            $tok = strtok(" ");
        }
    }

    private function getWords($string, &$array)
    {
        $tok = strtok($string, " ");

        while ($tok)
        {
            $tok =  strtoupper(trim($tok));
            $array[$tok] = $tok;
            $tok = strtok(" ");
        }
    }

    public function getIdTable($tableName)
    {
        $table = strtoupper($tableName);
        if (isset($this->tableId[$table]))
        {
            return $this->tableId[$table];
        }
        else
        {
            $sql = new MSQL('idTable', 'ISR_TABLE', '(TableName = ?)');
            $sql->setParameters($table);
            $query = $this->conn->getQuery($sql);
            if (!$query->eof)
            {
               $idTable = $query->fields('idtable');
            }
            else
            {
               $idTable = $this->db->getNewId('seq_isr_table');
               $sql = new MSQL('idTable,TableName', 'ISR_TABLE');
               $sql->setParameters($idTable, $table);
               $this->conn->execute($sql->insert());
            }
            $this->tableId[$table] = $idTable;
            return $idTable;
        }
    }

    public function getIdField($idTable, $fieldName)
    {
        $field = strtoupper($fieldName); 
        if (isset($this->fieldId[$idTable][$field]))
        {
            return $this->fieldId[$idTable][$field];
        }
        else
        {
            $sql = new MSQL('idField', 'ISR_FIELD', '(idtable = ?) and (FieldName = ?)');
            $sql->setParameters($idTable, $field);
            $query = $this->conn->getQuery($sql);
            if (!$query->eof)
            {
               $idField = $query->fields('idfield');
            }
            else
            {
               $idField = $this->db->getNewId('seq_isr_field');
               $sql = new MSQL('idField,idTable,FieldName', 'ISR_FIELD');
               $sql->setParameters($idField, $idTable, $field);
               $this->conn->execute($sql->insert());
            }
            $this->fieldId[$idTable][$field] = $idField;
            return $idField;
        }
    }

    public function getIdWord($word, $fono = false)
    {
        $table = $fono ? 'ISR_WORDFONO' : 'ISR_WORD';
        $w = $fono ? $this->fonetize($word) : $word;
        $sql = new MSQL('idWord', $table, "(word = '$w')");
        $query = $this->conn->getQuery($sql);

        if (!$query->eof)
        {
            $idWord = $query->fields('idWord');
        }
        else
        {
            $idWord = $this->db->getNewId('seq_isr_word');
            $sql = new MSQL('idWord,Word', $table);
            $sql->setParameters($idWord, $w);
            $this->conn->execute($sql->insert());
        }

        return $idWord;
    }

    public function encode($value)
    {
        return substr('000000' . dechex(trim($value)), -6);
    }

    public function decode($value)
    {
        return hexdec($value);
    }

    public function addReference($idField, $idWord, $id, $next = 0, $fono = false)
    {
        $id = $id . $this->encode($next);
        $table = $fono ? 'ISR_INDEXFONO' : 'ISR_INDEX';
        $sql = new MSQL('idIndex,len,block', $table, "(idword = $idWord) and (idfield = $idField) and (len < 200)",'','','',true);
        $query = $this->conn->getQuery($sql);
        if ($query->eof)
        {
            $idIndex = $this->db->getNewId('seq_isr_index');
            $sql = new MSQL('idIndex,idWord,idField,len,block', $table);
            $sql->setParameters($idIndex,$idWord, $idField, '1', $id);
            $this->conn->execute($sql->insert());
        }
        else
        {
            $idIndex = $query->fields('idIndex');
            $block = $query->fields('block') . $id;
            $len = $query->fields('len') + 1;
            $sql = new MSQL('len,block', $table,"(idindex = $idIndex)");
            $sql->setParameters($len,$block);
            $this->conn->execute($sql->update());
        }
        return $idIndex;
    }

    public function getReference($idField, $idWord, $fono = false)
    {
        $table = $fono ? 'ISR_INDEXFONO' : 'ISR_INDEX';
        $list = array();
        $sql = new MSQL('len,block', $table, "(idword = $idWord) and (idfield = $idField)");
        $query = $this->conn->getQuery($sql);
        while (!$query->eof)
        {
            $block = $query->fields('block');
            $len = $query->fields('len') * 12;
            for($i=0; $i < $len; $i+=12)
            {
                $id = $this->decode(substr($block,$i,6));
                $list[$id] = $id;
            }
            $query->moveNext();
        }
        return $list;
    }

    public function delReference($idTable, $pk, $fono = false)
    {
        $table = $fono ? 'ISR_KEYFONO' : 'ISR_KEY';
        $tableIndex = $fono ? 'ISR_INDEXFONO' : 'ISR_INDEX';
        $base = intval($pk / 600) * 600;
        $sql = new MSQL('idKey,idTable,key,block', $table, "(idTable = $idTable) and (key = $base)",'','','',true);
        $query = $this->conn->getQuery($sql);
        if (!$query->eof)
        {
            $offset = $pk % 600;
            $id = $this->encode($pk);
            $block = $query->fields('block');
            $index = substr($block,$offset*6,6);
            while ($index != '000000')
            {
                $idIndex = $this->decode($index);
                $sqlidx = new MSQL('len,block', $tableIndex ,"(idindex = $idIndex)",'','','',true);
                $qryidx = $this->conn->getQuery($sqlidx);
                if (!$qryidx->eof)
                { 
                    $block = $qryidx->fields('block');
                    $len = $qryidx->fields('len') - 1;
                    $i = intval(strpos($block,$id) / 12) * 12;
                    $index = substr($block,$i+6,6);
                    $j = $len * 12;
                    $last = substr($block,-12);
                    $block = substr_replace($block,$last,$i,12);                    
                    $block = substr($block,0,-12);                    
                    $sqlidx->setParameters($len,$block);
                    $this->conn->execute($sqlidx->update()); 
                }
                else
                {
                    $index = '000000';
                } 
            }
        }
    }

    public function updateKey($idTable,$pk,$idIndex, $fono = false)
    {
        $table = $fono ? 'ISR_KEYFONO' : 'ISR_KEY';
        $index = $this->encode($idIndex);
        $base = intval($pk / 600) * 600;
        $offset = $pk % 600;
        $sql = new MSQL('idKey,idTable,key,block', $table, "(idTable = $idTable) and (key = $base)",'','','',true);
        $query = $this->conn->getQuery($sql);
        if ($query->eof)
        {
            $idKey = $this->db->GETNewId('seq_isr_key');
            $sql = new MSQL('idKey,idTable,key,block', $table);
            $block = str_repeat('0',3600);
            $block = substr_replace($block,$index,$offset*6,6);
            $sql->setParameters($idKey, $idTable,$base,$block);
            $this->conn->execute($sql->insert());
        }
        else
        {
            $idKey = $query->fields('idKey');
            $block = $query->fields('block');
            $block = substr_replace($block,$index,$offset*6,6);
            $sql = new MSQL('block', $table,"(idKey = $idKey)");
            $sql->setParameters($block);
            $this->conn->execute($sql->update());
        }
    }

    public function indexer($tableName, $fieldname, $pk, $phrase, $fono = true)
    {
        $this->getTokens($phrase, $words);
        if (! count($words)) return;
        $tr = $this->beginTransaction();
        try
        { 
            $idTable = $this->getIdTable($tableName);
            $idField = $this->getIdField($idTable, $fieldname);
            $this->delReference($idTable, $pk);
            if ($fono)
            {
                $this->delReference($idTable, $pk, true);
            }
            $next = $nextFono = 0;
            foreach ($words as $word)
            {
                $id = $this->encode($pk);
                $idWord = $this->getIdWord($word);
                $next = $this->addReference($idField,$idWord,$id,$next);

                if ($fono)
                {
                    $idWord = $this->getIdWord($word, true);
                    $nextFono = $this->addReference($idField,$idWord,$id,$nextFono, true);
                }
            }
            $this->updateKey($idTable,$pk,$next);
            if ($fono)
            {
                $this->updateKey($idTable,$pk,$nextFono,true);
            }
            $tr->commit();
        }
        catch (Exception $e)
        {
            $tr->rollback();
        }
        $this->endTransaction();
    }

    public function delete($tableName, $pk)
    {
        $tr = $this->beginTransaction();
        try
        { 
            $idTable = $this->getIdTable($tableName);
            $this->delReference($idTable,$pk);
            $this->delReference($idTable,$pk, true);
            $tr->commit();
        }
        catch (Exception $e)
        {
            $tr->rollback();
        }
        $this->endTransaction();
    }

    public function retrieve($tableName, $fieldName, $phrase, $fono = true, $max = 200)
    {
        $phrase = trim(strtoupper($phrase));
        $phrase = $this->phonetic->removeMultiple($phrase);
        $phrase = $this->phonetic->removeStrange($phrase);
        $phrase = $this->phonetic->removeAccentuation($phrase);
        $this->getWords($phrase, $words);
        if (! count($words)) return;
        $idTable = $this->getIdTable($tableName);
        $idField = $this->getIdField($idTable, $fieldName);
        $conector = ''; $i = 0;
        foreach ($words as $word)
        {
            if (($word == "OU") || ($word == "E"))
            {
               $conector = $word;
            }
            else
            {
               $idWord = $this->getIdWord($word, $fono);
               if ($i++ == 0)
               {
                   $final = $this->getReference($idField,$idWord, $fono);
               }
               else
               {
                   $list = $this->getReference($idField,$idWord, $fono);
                   if ($conector == "OU")
                   {
                       $final = array_merge($final, $list);
                   }
                   else
                   {
                       $final = array_intersect($final,$list); 
                   }
               }
            }            
        }
        $in = count($final) ? implode(',',array_slice($final,0, $max)) : "NULL";
//        $cmd = "select $fields from $tableName where $key in ($in)";
//        $q = $this->conn->getQueryCommand($cmd);
//        return $q;
        return $in;
    }

    public function fonetize($word)
    {
        return $this->phonetic->fonetize($word);
    }
}
?>