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
 * Creation date 2011/10/11
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

class AUtil
{
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
     * Função para converter um array em um objeto
     *
     * @return Object
     */
    public static function arrayToObject($array,$attributes=null)
    {
        $object = new stdClass();
        if( count($array) > 0 )
        {
            if( count($attributes) > 0 )
            {
                foreach ( $attributes as $key => $attribute )
                {
                    $arr = each($array);
                    $object->$attribute = $arr['value'];
                }
                return $object; 
            }
            foreach($array as $key => $row)
            {
                $object->$key = $row;
            }
        }
        return $object;
    }
    
    //
    // Função para gerar nome do arquivo codificado para envio à url de navegação
    //
    public static function generateEncodedFileHash($filename)
    {
        return base64_encode($filename);
    }
    
    //
    // Função para gerar o hash codificado do nome do arquivo para envio à url de navegação
    //
    public static function generateEncodedFileName($filename)
    {
        if (defined('SEED_REQUEST_FILE'))
        {
            return base64_encode(crypt($filename, SEED_REQUEST_FILE));
        }
        else
        {
            throw new Exception('Ocorreu um erro ao buscar o arquivo para download, por favor, tente novamente mais tarde');
            return false;
        }
    }
    
    //
    // Gera a url para download do arquivo
    //
    public static function generateFileCall($filename)
    {
        $MIOLO = MIOLO::getInstance();
        $file = self::generateEncodedFileHash($filename);
        $hash = self::generateEncodedFileName($filename);
        $url = $MIOLO->getAbsoluteURL('getFile.php').'?file='.urlencode($file).'&hash='.urlencode($hash);
        return $url;
    }
    
    public static function explodirData($data)
    {
        $dia =  substr($data, 0, 2);
	$mes =  substr($data, 3, 5);
	$ano =  substr($data, 6, 10);
        
        return array('dia' => $dia, 'mes' => $mes, 'ano' => $ano);
    }
    
    // Retorna true se a data1 for anterior ou igual a data2.
    public static function compararDatas($data1, $data2)
    {
        $data1 = self::explodirData($data1);
        $data1 = date('Y-m-d',mktime(0,0,0,$data1['mes'],$data1['dia'],$data1['ano']));
        
        $data2 = self::explodirData($data2);
        $data2 = date('Y-m-d',mktime(0,0,0,$data2['mes'],$data2['dia'],$data2['ano']));
        
        $data1 = strtotime($data1);
        $data2 = strtotime($data2);
        
        return $data1 <= $data2;
    }
}
?>
