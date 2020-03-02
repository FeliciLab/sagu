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
/**
 * Make a var_export (some kind of var_dump) to firebugs console.
 * <p>
 * It will parse the string to avoid javascript erros.
 *
 * @param any $vd you can pass all you pass to var_dump, object, array, string, or all in one.
 */
function clog($vd)
{
    $array = func_get_args();
    $MIOLO = MIOLO::getInstance();

    if (is_array($array) )
    {
        foreach ($array as $line => $info)
        {
            if ( is_array( $info) )
            {
            	$type = 'info';
            }
            else if ( is_object( $info ) )
            {
            	$type = 'warn';
            }
            else
            {
            	$type = 'log';
            }

            /*console.log
            console.debug
            console.info
            console.warn
            console.error.*/

            //converte pra string caso não for
            if (!is_string($info))
            {
                $info = parseMioloFields($info);
                $info = print_r($info, 1);
            }

            error_log($info);                           // registra nos error do php
			$info = str_replace("\n", '\n', $info ); 	// troca linha nova do php para javascript
            $info = str_replace("'", "\'", $info );     // retira ' para evitar erros de sintaxe js
            $info = new GString($info);                 // suporta clog de strings ISO
            $MIOLO->trace($info);                       // registra no trace do miolo
            $MIOLO->page->addJsCode("console.$type('$info');");
        }
    }
}

/**
 * Inspeciona todos os javascripts enviados pelo MIOLO para o browser.
 */
function debugJs()
{
    $MIOLO = MIOLO::getInstance();
    $page = $MIOLO->page;
    flog( "onsubmit=\n".implode("\n", $page->onsubmit->items) );
    flog( "jscode=\n".implode("\n", $page->jscode->items));
    flog( "onload=\n".implode("\n", $page->onload->items));
}

/**
 * Enter description here...
 *
 * @return debug Object Print
 */
function echoPre()
{
    $return = null;
    $array = func_get_args();

    if (is_array($array) )
    {
        foreach ($array as $info)
        {
            $return.= "<br><pre>". print_r($info, 1) ."</pre><br>";
        }
    }

    return $return;
}



function flog()
{
    $array = func_get_args();
    $MIOLO = MIOLO::getInstance();

    if (!is_array($array) )
    {
        return;
    }

    foreach ($array as $line => $info)
    {
        $info = parseMioloFields($info);
        $content = "\n----------------------------------------------------------------------------------------------------\n";
        $content.= date("y-m-d H:i:s") . "| Microtime: ". microtime() ." \n";
        $content.= print_r($info, 1);

        file_put_contents("/tmp/gnuteca3Log.txt", $content, FILE_APPEND);
        chmod ("/tmp/gnuteca3Log.txt", 0777);
    }
}

/**
 * Faz uma "interpretação" dos campos do miolo, tirando alguns elementos fora, para
 * pode mandar para o clog e flog.
 *
 * @param mixed $field array, ou objeto
 * @return stdClass
 */
function parseMioloFields( $field )
{
    if ( $field instanceOf MObjectList )
    {
        $field = parseMioloFields($field->items);
    }
    if ( is_array($field))
    {
        foreach ( $field as $line => $info)
        {
            $field[$line] = parseMioloFields($info);
        }
    }
    else if ( is_object($field) && $field instanceof MControl  )
    {
        $tmpField = new stdClass();

        $variables = get_object_vars($field);
        $variables = array_keys($variables);

        $tmpField->phpClass         =  get_class($field);

        foreach ( $variables as $line => $var)
        {
            if ( $var != 'form' &&
                $var != 'controlBox' &&
                $var != 'box' &&
                $var != 'parent' &&
                $var != 'painter' &&
                $var != 'manager' &&
                $var != 'page' &&
                $var != 'owner' &&
                $var != 'controlsId'
               )
            {
                $tmpField->$var = parseMioloFields($field->$var);
            }
        }

        $field = $tmpField;
    }

    return $field;
}

/**
 * Manda para o console os dados em forma de stdClass.
 *
 * @param stdClass $data dados do formulário
 */
function utLog( stdClass $data)
{
    clog( _utLog($data) );
}

/**
 * Gera um registro no formato necessário para um teste unitário
 * 
 * @param stdClass $data dados do formulário
 * @param integer $pad quantidade de espaços a por na frente (para identação)
 * @return string
 */
function _utLog( stdClass $data, $pad = 8)
{
    $data = (array) $data;

    $space = str_pad(' ', $pad);

    $content = $space.'$data = new stdClass();'."\n";

    $negate = array('GRepetitiveField','arrayItemTemp','keyCode');

    foreach ( $data as $line => $info )
    {
        
        if ( !in_array($line, $negate) )
        {
            //para suportar dados diferentes de string
            if ( is_string( $info ) )
            {
                $info = "'".$info."'"; //adiciona aspas
            }
            else
            {
                //tira linhas novas para ficar mais organizado
                $info = str_replace("\n", '', var_export($info,true) );
            }
            
            $content .= $space.'$data->'.$line. " = ".'$data->'.$line. "S = " .$info. ";\n";
        }
    }

    return $content;
}

?>