<?php

/**
 *  Classe com componentes uteis para diversas tarefas
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/16
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

class AVinst
{
    const RETURN_TYPE_STRING = 'string';
    const RETURN_TYPE_ARRAY = 'array';
    const RETURN_TYPE_SINGLE_ARRAY = 'sarray';
    const RETURN_TYPE_OBJECT = 'object';
    
    /**
     * Função para exibir informações no console
     *
     * @return Object
     */
    public static function flog()
    {
        if ( file_exists('/tmp/var_dump_andre') )
        {
            $numArgs = func_num_args();
            $dump = '';
            for($i = 0; $i < $numArgs; $i++)
            {
                $dump .= var_export(func_get_arg($i), true) . "\n";
            }

            $f = fopen('/tmp/var_dump_andre', 'w');
            fwrite($f, $dump);
            fclose($f);
        }
    }
    
    /**
     * Função para exibir informações no console
     *
     * @return Object
     */
    public static function listYesNo($returnType=self::RETURN_TYPE_ARRAY)
    {
        if( $returnType == self::RETURN_TYPE_ARRAY )
        {
            return array('f'=>array('Não','f'),'t'=>array('Sim','t'));
        }
        elseif ($returnType == self::RETURN_TYPE_SINGLE_ARRAY)
        {
            return array('f'=>'Não', 't'=>'Sim');
        }
        else
        {
             $object = new stdClass();
             $object->Não = 'f';
             $object->Sim = 't';   
        }        
    }
    
    /**
     * Função para exibir informações no console
     *
     * @return Object
     */
    public static function listYesNoBoth($returnType=self::RETURN_TYPE_ARRAY)
    {
        if( $returnType == self::RETURN_TYPE_ARRAY )
        {
            return array('f'=>array('Não','f'),'t'=>array('Sim','t'), array('Ambos', ''));
        }
        else
        {
             $object = new stdClass();
             $object->Não = 'f';
             $object->Sim = 't';
             $object->Ambos = '';
        }        
    }

	/**
     * Função que converte uma matriz de valores em um array de types
     *
     * @return Array
     */
    public static function getArrayOfTypes($data,$typeName,$attributeIndex=null)
    {
        // Pega os atributos(public,protected) de acordo com o nome do type $typeName
        $reflectionClass = new ReflectionClass($typeName);
        $typeAttributes = array_keys($reflectionClass->getDefaultProperties());
        $arrayOfTypes = array();
        
        for ( $i = 0;  $i  <  count($data);  $i++ )
        {
            if($data[$i] instanceof stdClass) // Se for um stdClass, passa direto para o defineData
            {
               $typeData = $data[$i];
            }
            else // Se for um array, combina os valores de $data com os atributos do type para o defineData
            {
                $typeData = new stdClass();
                
                foreach ($typeAttributes as $typeAttribute)
                {
                    $dataArray = each($data[$i]);
                    $typeData->$typeAttribute = $dataArray['value'];
                }
            }
            $arrayOfTypes[ ! is_null($attributeIndex) ? $typeData->$attributeIndex : $i ] = new $typeName($typeData);
        }
        
        return $arrayOfTypes;
    }
    
	/**
     * Função que converte uma matriz de valores em um array de objetos stdClass
     *
     * @return Array
     */
    public static function getArrayOfObjects($data,$attributes,$attributeIndex=null)
    {
        $arrayOfObjects = array();
        
        for ( $i = 0;  $i  <  count($data);  $i++ )
        {
            if($data[$i] instanceof stdClass) // Se for um stdClass, passa direto para o defineData
            {
               $objectData = $data[$i];
            }
            else // Se for um array, combina os valores de $data com os atributos do type para o defineData
            {
                $objectData = new stdClass();
                
                foreach ($attributes as $attribute)
                {
                    $dataArray = each($data[$i]);
                    $objectData->$attribute = $dataArray['value'];
                }
            }
            $arrayOfObjects[ ! is_null($attributeIndex) ? $objectData->$attributeIndex : $i ] = $objectData;
        }
        
        return $arrayOfObjects;
    }
    
    //
    //
    //
    public static function invokeHandler($module, $action, $args)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->redirect( $MIOLO->getActionURL( $module, $action, '', $args ) );
    }
    
    public static function registerMicrotime($message)
    {   
        $date = date('H:i:s');
        $tod = gettimeofday();
        
        file_put_contents('/tmp/microtime_info.txt', $date.':'.$tod['usec'].':: '.$message."\n", FILE_APPEND);
    }
    
    public static function isFirstAccessToForm()
    {   
        return MUtil::isFirstAccessToForm() || in_array(MIOLO::_REQUEST(MUtil::getDefaultEvent()),array('tbBtnNew:click'));
    }
    
    public static function getAjaxURLArgs()
    {
        $MIOLO = MIOLO::getInstance();
        $requestId = $MIOLO->page->getFormId() . '__EVENTARGUMENT';
        $ajaxArgs = array( );

        if ( $_REQUEST[$requestId] )
        {
            $url = rawurldecode($_REQUEST[$requestId]);
            $args = explode('&', $url);

            foreach ( $args as $a )
            {
                $param = explode('=', $a);
                $ajaxArgs[$param[0]] = $param[1];
            }
        }

        return (object) array_merge($ajaxArgs);
    }
    
    /**
     * Função que verifica se um registro ou tabela tem dependências em outra(s).
     *
     * @return Boolean
     */
    public static function checkTableDependencies($table,$refValue=null)
    {
        $MIOLO = MIOLO::getInstance();
        
        if( strlen($refValue) > 0 ) // Se for um registro, busca as dependências nas outras tabelas
        {
            $sql  = "'SELECT (' || array_to_string( (array(  SELECT 'select count(*) > 0 from ' || cl.relname ||' where '|| a.attname ||' = ' || ?";
            $sql1 = ")), ') OR (') || ')'";
            $args[] = is_numeric($refValue) ? "$refValue" : "'$refValue'";                        
        }
        else // Verfifica algum campo da tabela é referenciada por outra
        {
            $sql = 'count(*) > 0';
            $sql1 = '';
        }
        
        $args[] = $table;
        
        $sql = "SELECT $sql
                       FROM pg_catalog.pg_attribute a
                       JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = 'r')
                       JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND ct.confrelid != 0 AND ct.conkey[1] = a.attnum)
                       JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = 'r')
                       JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND af.attnum = ct.confkey[1]) where clf.relname = ? $sql1";
        
        $data = ADatabase::query($sql, $args);
        
        if( strlen($refValue) > 0 && count($data) > 0 && $data[0][0] != 'SELECT ()')
        {
            $data = ADatabase::query($data[0][0]);            
        }
        
        return $data[0][0] == DB_TRUE;
    }
}
?>