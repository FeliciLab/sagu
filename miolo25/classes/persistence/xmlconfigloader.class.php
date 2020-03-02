<?php

class XMLConfigLoader
{
   private $broker;
   private $xmlMaps = array();
   private $classMaps = array();

   public function __construct(PersistentManagerFactory $broker)
   {
       $this->broker = $broker;  // factory
   }

   private function getAsArray($object)
   {
       return (is_array($object)) ? $object : array($object);
   }

   public function getClassMap($module, $name, $associative=FALSE)
   {   
       $MIOLO = MIOLO::getInstance();

       if (isset($this->classMaps[$module][$name]))
       {
          return $this->classMaps[$module][$name];
       }
       if (isset($this->xmlMaps[$module][$name]))
       {
          $xml = $this->xmlMaps[$module][$name];
       }
       else
       { 
          $file = $MIOLO->getNamespacePath('modules::' . $module . '::business::map::'. $name . '.xml');
          $xmlTree = new MXMLTree($file);
          $tree = $xmlTree->getTree();
          $xml = $xmlTree->getXMLTreeElement($tree);
          $this->xmlMaps[$module][$name] = $xml;
          if (!$associative)
          {
              $MIOLO->usesBusiness($module,$name);
          }
       }

       $database = (string)$xml->databaseName; 
       $className = (string)$xml->moduleName . (string)$xml->className;
       $cm = new ClassMap($className, $database, $this->broker);
       $dm = new DatabaseMap((string)$xml->databaseName);
       $tableMap = new TableMap();
       $tableMap->setName((string)$xml->tableName);
       $tableMap->setDatabaseMap($dm);
       if (isset($xml->extends))
       {
          $MIOLO->usesBusiness((string)$xml->extends->moduleName,(string)$xml->extends->className);
          $superClassName = (string)$xml->extends->moduleName . (string)$xml->extends->className;
          $cm->setSuperClass($superClassName, $this->broker);
       }
       $attributes = $this->getAsArray($xml->attribute);
       foreach($attributes as $attr)
       {
           $am = new AttributeMap((string)$attr->attributeName, $cm);
           $converter = $this->getConverter($attr);
           if (isset($attr->attributeIndex))
           {
               $am->setIndex($attr->attributeIndex);
           }
           if (isset($attr->columnType))
           {
              $am->setColumnType($attr->columnType);
              $cm->setHasTypedAttribute(true);  
              // set the conveter to this attribute : "converter".$type
              $factory = new ConverterFactory();
              $name = "converter" . $attr->columnType;  
              $converter = $factory->getConverter($name);
              $this->broker->putConverter($name, $converter);
           }
           if (isset($attr->columnName))
           {
              $colm = new ColumnMap($attr->columnName, $tableMap, $converter);
 
              if (isset($attr->key))
              {
                  if ($attr->key == 'primary')
                  {
                     $colm->setKeyType('primary');
                     if (isset($attr->idgenerator))
                     {
                         $idGenerator = $attr->idgenerator;
                         $colm->setIdGenerator($idGenerator);
                     }
                  }
                  elseif ($attr->key == 'foreign')
                  {
                     $colm->setKeyType('foreign');
                  }
              }
              else
              {
                  $colm->setKeyType('none');
              }
              $am->setColumnMap($colm);
           }
           $am->setProxy($attr->proxy);
           if ((isset($attr->reference)) && ($cm->getSuperClass() != NULL) )
           {
              $referenceAttribute = $cm->getSuperClass()->getAttributeMap($attr->reference);
              if ($referenceAttribute)
                 $am->setReference($referenceAttribute);
           }
           if ($attr->attributeName == 'timestamp')
           {
              $cm->setTimestampAttributeMap($am); 
           }
           else
           {
              $cm->addAttributeMap($am); 
           }
       }   
       $this->classMaps[$module][$name] = $cm;

// Associations       
       if (isset($xml->association)) 
       { 
          $fromClassMap = $cm;
          $associations = $this->getAsArray($xml->association);
          foreach($associations as $assoc)
          {
              $toModule = $assoc->toClassModule;
              $toName = $assoc->toClassName;
              $toClassMap = $this->getClassMap($toModule, $toName);            
              $am = new UniDirectionalAssociationMap();
              $am->setForClass($toClassMap);
              $am->setTargetName($assoc->target);
              $am->setTarget($fromClassMap->getAttributeMap($assoc->target));
              $am->setDeleteAutomatic($assoc->deleteAutomatic);
              $am->setSaveAutomatic($assoc->saveAutomatic);
              $am->setRetrieveAutomatic($assoc->retrieveAutomatic);
              $am->setJoinAutomatic($assoc->joinAutomatic);
              if (isset($assoc->indexAttribute)) 
              {
                  $am->setIndexAttribute($assoc->indexAttribute->indexAttributeName);
              }
              $am->setInverse($assoc->inverse);
              $am->setCardinality($assoc->cardinality);
              if ($assoc->cardinality == 'manyToMany')
              {
                 $associativeModule = $assoc->associativeClassModule;
                 $associativeName = $assoc->associativeClassName;
                 $associativeClassMap = $this->getClassMap($associativeModule, $associativeName, true);            
                 $am->setAssociativeClass($associativeClassMap);
                 foreach($assoc->direction as $direction)
                 {
                     $am->addDirection($direction); 
                 }
              }
              else
              {
                 $entries = $this->getAsArray($assoc->entry);
                 foreach($entries as $entry)
                 {
                     $fromAttribute = $entry->fromAttribute;
                     $toAttribute = $entry->toAttribute;
                     if ($am->isInverse())
                     {
                         $e = new UDAMapEntry($toClassMap->getAttributeMap($fromAttribute),                                                               $fromClassMap->getAttributeMap($toAttribute));
                     }
                     else
                     {
                         $e = new UDAMapEntry($fromClassMap->getAttributeMap($fromAttribute),                                                               $toClassMap->getAttributeMap($toAttribute));
                     } 
                     $am->addEntry($e);
                 }
              }
              if (isset($assoc->orderAttribute)) 
              {
                 $orderEntry = array(); 
                 $orderAttributes = $this->getAsArray($assoc->orderAttribute);
                 foreach($orderAttributes as $order)
                 {
                     $ascend = ($order->orderAttributeDirection == 'ascend');

                     $attributeMap = $am->getForClass()->getAttributeMap($order->orderAttributeName);
                     $orderEntry[] = new OrderEntry($attributeMap, $ascend);
                 }
                 if (count($orderEntry))
                 {
                    $am->setOrderAttributes($orderEntry);
                 } 
              }
              $fromClassMap->putAssociationMap($am);
          }  
       }

       return $cm;
   }

   public function getConverter($attributeNode)
   {
        $converterNode = $attributeNode->converterClass;
        if (!$converterNode)
        {
            $converterNode = $attributeNode->converter;
            if (!$converterNode)
            {
                $converter = ConverterFactory::getTrivialConverter();
            }
            else
            {
                $name = $converterNode->converterName;
                $converter = $this->broker->getConverter($name);
                if (!$converter)
                {
                    $parameters = $this->getParameters($converterNode);
                    $factory = new ConverterFactory();
                    $converter = $factory->getConverter($name, $parameters);
                    $this->broker->putConverter($name, $converter);
                }
            }
        }
        else
        {
            $name = (string)$converterNode;
            $converter = $this->broker->getConverter($name);
            if (!$converter)
            {
                $factory = new ConverterFactory();
                $converter = $factory->getConverter($name);
                $this->broker->putConverter($name, $converter);
            }
        }
        return $converter;       
   }

   public function getParameters($node = NULL)
   {
       $param = NULL;
       if ($node)
       { 
           $parameters = $this->getAsArray($node->parameter);
           foreach($parameters as $parameter)
           {
               $param[$parameter->parameterName] = $parameter->parameterValue;
           }
       } 
       return $param;
   } 
}
?>