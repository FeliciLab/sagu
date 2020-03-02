<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


    $content = file_get_contents('/var/www/sagutrunk/modules/base/classes/syncStructDatabase/changes.xml');

    if ( ! $content )
    {
        throw new Exception( new BString("Impossível obter conteúdo do arquivo '$xmlPath'.") );
    }

        $xml= new SimpleXMLElement($content);

        foreach($xml as $item)
        {
            $sql = $item->change;
            $db = new bBaseDeDados();
            
            bBaseDeDados::executar($sql);
            echo 'DJ: ' . $item->change . '<br />';
        }



        //obtem o nome da tabela
        //var_dump($xml);

   // var_dump(toArray($xml));
        
    

    //foreach($xml->SHOUTCASTSERVER as $item)
    //{
    //    echo 'DJ: ' . $item->tag . '<br />';
    //}


?>
