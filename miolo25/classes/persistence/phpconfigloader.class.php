<?php

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class PHPConfigLoader
{
    /**
     * Attribute Description.
     */
    public $broker;

    /**
     * Attribute Description.
     */
    public $maps = array();

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param &$broker (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function PHPConfigLoader(&$broker)
    {
        $this->broker = &$broker; // factory
    }

    public function &getMap($classMapName)
    {
        $MIOLO = MIOLO::getInstance();

        if (!$this->maps[$classMapName])
        {
            $MIOLO->assert(class_exists(strtolower($classMapName)),
                           'PHPConfigLoader::getMap() ' . _M('Error') . ": $classMapName - " . _M('Class not found'));

            eval("\$map = new {$classMapName}();");
            $this->maps[$class] = &$map;
        }

        return $this->maps[$class];
    }

    public function &getClassMap($classMapName)
    {
        $MIOLO = MIOLO::getInstance();
        $classMap = $this->getMap($classMapName);
        echo $classMapName;
        echo $classMap->databaseName;
        $database = $MIOLO->getDatabase($classMap->databaseName);
        $cm = new ClassMap($classMap->className, $database, &$this->broker);
        $dm = new DatabaseMap($classMap->databaseName);
        $tableMap = new TableMap();
        $tableMap->setName($classMap->tableName);
        $tableMap->setDatabaseMap($dm);

        foreach ($classMap->attributes as $attr)
        {
            $am = new AttributeMap($attr['name']);
            $converter = $this->getConverter($attr['converter']);

            if (isset($attr['column']))
            {
                $colm = new ColumnMap($attr['column'], &$tableMap, &$converter);

                if (isset($attr['key']))
                {
                    if ($attr['key'] == 'primary')
                    {
                        $colm->setKeyType('primary');

                        if (isset($attr['id_generator']))
                        {
                            $idGenerator = $attr['id_generator'];
                            $colm->setIdGenerator($idGenerator);
                        }
                    }
                    elseif ($attr['key'] == 'foreign')
                    {
                        $colm->setKeyType('foreign');
                    }
                }
                else
                {
                    $colm->setKeyType('none');
                }

                $am->setColumnMap(&$colm);
            }

            if ($attr['proxy'] === false)
            {
                $am->setProxy(false);
            }

            if ((isset($attr['reference'])) && ($cm->superClass != null))
            {
                $referenceAttribute = &$cm->superClass->getAttributeMap($attr['reference']);

                if ($referenceAttribute)
                    $am->setReference(&$referenceAttribute);
            }

            if ($attr['name'] == 'timestamp')
            {
                $cm->setTimestampAttributeMap(&$am);
            }
            else
            {
                $cm->addAttributeMap(&$am);
            }
        }

        return $cm;
    }

    public function &getAssociationMap($classMapName)
    {
        $classMap = &$this->getMap($classMapName);

        if (!count($classMap->associations))
            return;

        foreach ($classMap->associations as $assoc)
        {
            $fromClassMap = &$this->broker->getClassMap($assoc['from_class']);
            $toClassMap = &$this->broker->getClassMap($assoc['to_class']);
            $am = new UniDirectionalAssociationMap();
            $am->setForClass(&$toClassMap);
            $am->setTargetName($assoc['target']);
            $am->setTarget($fromClassMap->getAttributeMap($assoc['target']));
            $am->setDeleteAutomatic($assoc['delete_automatic']);
            $am->setSaveAutomatic($assoc['save_automatic']);
            $am->setRetrieveAutomatic($assoc['retrieve_automatic']);
            $am->setInverse($assoc['inverse']);
            $am->setCardinality($assoc['cardinality']);

            if ($assoc['cardinality'] == 'manyToMany')
            {
                $associativeClassMap = &$this->broker->getClassMap($assoc['associative_class']);
                $am->setAssociativeClass(&$associativeClassMap);

                foreach ($assoc['direction'] as $direction)
                {
                    $am->addDirection($direction);
                }
            }
            else
            {
                foreach ($assoc['entry'] as $entry)
                {
                    $fromAttribute = $entry['from'];
                    $toAttribute = $entry['to'];
                    $e = new UDAMapEntry($fromClassMap->getAttributeMap($fromAttribute), $toClassMap->getAttributeMap($toAttribute));
                    $am->addEntry(&$e);
                }
            }

            $fromClassMap->putAssociationMap(&$am);
        }
    }

    public function &getConverter($name = null)
    {
        if (!$name)
        {
            $converter = ConverterFactory::getTrivialConverter();
        }
        else
        {
            $converter = &$this->broker->getConverter($name);

            if (!$converter)
            {
                $converter = new ConverterFactory($name);
                $this->broker->putConverter($name, &$converter);
            }
        }

        return $converter;
    }
}
?>
