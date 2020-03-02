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
 * Class
 *
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 05/05/2009
 *
 **/

class GXML
{

    private $return     = false,
            $array      = false,
            $firstTag   = 'gnuteca3',
            $xml        = '';


    function __construct($var, $type = 'xmlToPhp', $encoding = "ISO-8859-1")
    {
        if(strtoupper($type) == 'XMLTOPHP')
        {
            $this->xmlToPhp($var, $this->return);
        }
        elseif(strtoupper($type) == 'PHPTOXML')
        {
            $xmlStart = '<?xml version="1.0" encoding="'. $encoding .'" standalone="yes"?><'. $this->firstTag .'></'. $this->firstTag .'>';

            $this->xml = new SimpleXMLElement($xmlStart);
            $this->arrayToXml($var, $this->xml);
        }
    }


    /**
     * Retona o conteudo
     *
     * @return unknown
     */
    public function getResult()
    {
        return $this->return;
    }


    /**
     * Este metodo foi implementado para transformar o XML do Z3950 para PHP
     *
     */
    function makeAttributes($attributes, $tag)
    {
        $attributes = str_replace("  ", " ", $attributes);
        $attributes = preg_replace('/([\'"]{1}) ([\'"]{1})/', '""', $attributes);
        $attributes = explode(" ", $attributes);

        $returnAttr = array();

        foreach ($attributes as $attr)
        {
            preg_match('/^([a-zA-Z0-9]{1,})=?([\'"]{0,1})?([\w:\/\. ]{0,})?([\'"]{0,1})$/', $attr, $match);
            if(strlen($match[1]) && $match[1] != substr($tag,strlen($tag)-1, strlen($tag)))
            {
                $returnAttr[$match[1]] = $match[3];
            }
        }

        return $returnAttr;
    }


    /**
     * Este metodo foi implementado para transformar o XML do Z3950 para PHP
     *
     * @param unknown_type $xml
     * @param unknown_type $result
     * @return unknown
     */
    function xmlToPhp($xml, &$result)
    {
        $x = 0;
        while(strlen($xml))
        {
            // CAPTURA A TAG
            preg_match('/^<([a-zA-Z0-9]{1,})/', $xml, $tag);
            $tag = trim($tag[1]);

            if(!strlen($tag))
            {
                return $xml;
            }

            // REMOVE ESPEÇOS, QUEBRA DE LINHA, TABS
            $xml = str_replace(array("\n", "\t", "\r"), "", $xml);
            $xml = preg_replace('/>[ ]{1,}</', '><', $xml);

            if(!strlen($xml))
            {
                return $xml;
            }

            $xmlExplode = explode("</$tag>", $xml);

            $x = 0;
            foreach ($xmlExplode as $index => $xml)
            {
                $xml = trim($xml);
                if(!strlen($xml))
                {
                    continue;
                }

                $xml.= "</$tag>";

                $result[$tag][$x] = null;

                // CAPTURA OS ATRIBUTOS E O CONTEUDO
                preg_match('/<'.$tag.'?([a-zA-Z0-9="\':\/\. ]{1,})>(.*)<\/'. $tag .'>/', $xml, $match);
                $xml = preg_replace('/<'.$tag.'?([a-zA-Z0-9="\':\/\. ]{1,})>(.*)<\/'. $tag .'>/', '', $xml);

                $attributes = $match[1];
                $content    = $match[2];

                // TRABALHA OS ATRIBUTOS
                $attributes = $this->makeAttributes($attributes, $tag);

                if(strlen($content))
                {
                    $content = $this->xmlToPhp($content, $result[$tag][$index]);
                }

                if(!is_array($result[$tag][$x]))
                {
                    $result[$tag][$x]['_content_'] = is_string($content) ? $content : $result[$tag]->content ;
                }

                $result[$tag][$x]['_attributes_']   = $attributes;

                $x++;
            }
        }
    }


    public function arrayToXml($array, $sun = false)
    {

        if(is_array($array) || is_object($array))
        {
            foreach ($array as $tagName => $content)
            {
                if(is_array($content) || is_object($content))
                {
                    $sunX = $sun->addChild($tagName);
                    $this->arrayToXml($content, $sunX);
                    continue;
                }

                $sun->addChild($tagName, $content);
            }
        }
        else
        {
            $array = is_bool($array) ? ($array ? "true" : "false") : "$array";
            $sun->addChild($array, $array);
        }

        $this->return = str_replace(array("\n", "\t", "\r"), "", $this->xml->asXML());
    }

}// final da classe