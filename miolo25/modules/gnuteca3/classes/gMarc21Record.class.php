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
 * Class convert string to marc21 format
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *         Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 08/09/2011
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->getClass('gnuteca3', 'GMaterialItem');
$MIOLO->usesBusiness('gnuteca3', 'BusTag');

class gMarc21Record extends GMessages
{
    private $record,
            $tags,
            $fieldDelimiter,
            $subFieldDelimiter,
            $emptyIndicator,
            $fieldsWithIndicator;

    public function __construct($content, $fieldDelimiter = "\n", $subFieldDelimiter = '$', $emptyIndicator = '#')
    {
        $this->emptyIndicator = $emptyIndicator;
        
        $MIOLO = MIOLO::getInstance();
        $busMarcTagListing = $MIOLO->getBusiness('gnuteca3', 'BusMarcTagListing');
        
        // Obtém os campos que possuem indicadores.
        $this->fieldsWithIndicator =  $busMarcTagListing->getFieldsWithIndicators();
        
        $this->setFieldDelimiter($fieldDelimiter); //seta delimitador de campo
        $this->setSubFieldDelimiter($subFieldDelimiter); //seta delimitador de subcampo
        $content = $this->prepareContent($content);
        $this->record = $content; //seta a String conteúdo
        $info = explode($this->fieldDelimiter, $content); //quebra o conteúdo, obtendo os campos
        $line = array();
        
        foreach ( $info as $lines => $fieldLine ) //para cada linha
        {
            //pula linhas em branco
            if ( strlen($fieldLine) == 0 )
            {
                continue;
            }
            
            $fieldId = substr($fieldLine,0, 3); //obtém fieldId
            
            //testa se são campos de controles e líder
            if ( BusinessGnuteca3BusTag::isControlField($fieldId) )
            {
                $data = new GMaterialItem();
                $data->fieldid = $fieldId;
                $data->subfieldid = 'a'; //todos campos de controle são subfield "a"
             
                //controla a linha do item
                if ( is_null($line[$fieldId][$data->subfieldid]) )
                {
                    $line[$fieldId][$data->subfieldid] = 0;
                }
                
                $data->line = $line[$fieldId][$data->subfieldid];
                $data->content = substr($fieldLine, 4); //obtém o conteúdo
                
                
                try
                {
                    //valida item
                    $this->validate($fieldId, $data->content, $lines);
                }
                catch (Exception $e)//Se a validação lançou uma exceção não mostra o campo.
                {
                    continue;
                }
                
                //checa integridade do objeto
                if ( $data->check() )
                {
                    $this->tags[] = $data;
                }
                
                $line[$fieldId][$data->subfieldid]++;
            }
            else
            {
                $indicator1 = '';
                $indicator2 = '';
                
                // Caso o campo tenha indicador, extraí o indicador.
                if ( in_array($fieldId, $this->fieldsWithIndicator) )
                {
                    $indicator1 = str_replace($emptyIndicator, '', substr($fieldLine, 4, 1)); //obtém indicador 1
                    $indicator2 = str_replace($emptyIndicator, '', substr($fieldLine, 5, 1)); //obtém indicador 2
                    $contentParts   = explode($this->subFieldDelimiter, substr($fieldLine, 7)); //obtém subcampos
                }
                else // Campos sem indicador.
                {
                     $contentParts   = explode($this->subFieldDelimiter, substr($fieldLine, 4)); //obtém subcampos
                }
                
                //testa se aconteceu a quebra da string. Se tiver tamanho 1 e o primeiro caractere for diferente do separador de subcampo, indica que não aconteceu a quebra da String
                if( (count($contentParts) == 1) && (substr($contentParts[0], 0, 1) != $this->subFieldDelimiter) )
                {
                    throw new Exception(_M('Verifique o separador de subcampo na linha @1', $this->module, $lines + 1));
                }
                
                foreach ( $contentParts as $k => $parts )
                {
                    $data = new GMaterialItem();
                    $data->fieldid = $fieldId; //seta fieldId
                    $data->subfieldid = substr($parts, 0, 1); //seta o subfieldid
                    $data->indicator1 = $indicator1;
                    $data->indicator2 = $indicator2;
                    
                    //controla a linha do item
                    if ( is_null($line[$fieldId][$data->subfieldid]) )
                    {
                        $line[$fieldId][$data->subfieldid] = 0;
                    }
                
                    $data->line = $line[$fieldId][$data->subfieldid];
                    $data->content = substr($parts, 2); //obtém o conteúdo
                    
                    //valida item
                    $this->validate($fieldId, $data->content, $lines);
                    
                    //checa integridade do objeto
                    if ( $data->check() )
                    {
                        $this->tags[] = $data;
                    }
                    
                    $line[$fieldId][$data->subfieldid]++;
                }
            }
        }
        
        
    }
    
    /**
     * Valida itens do registro
     * 
     * @param int $fieldId
     * @param String $content
     * @param int $line 
     */
    private function validate($fieldId, $content, $line)
    {
        //testa se fieldid é numérico
        if ( !is_numeric($fieldId) )
        {
            throw new Exception(_M('O campo "@1"  da linha @2 não é numérico', $this->module, $fieldId, $line+1));
        }
        
        //testa se leader tem 24 caracteres
        if ( $fieldId == '000' )
        {
            if ( strlen($content) != 24 )
            {
                throw new Exception(_M('O campo leader não está com 24 caracteres, está com @1 na linha @2', $this->module, strlen($content), $line+1));
            }
        }
        
        //testa integridade do campo 005
        if ( $fieldId == '005' )
        {
            if ( !preg_match('/^([0-9]{14}\.[0-9]{1})$/', $content) )
            {
                throw new Exception(_M('O campo 005 da linha @1 não está no formato "yyyymmddhhmmss.f"', $this->module, $line+1));
            }
        }
    }

    /**
     * Substitui valores no conteúdo de acordo com a preferência MARC21_REPLACE_VALUES
     * @param String $content
     * @return String conteúdo tratado 
     */
    private function prepareContent($content)
    {
        $strings = MARC21_REPLACE_VALUES;
        
        $parts = explode("\n", $strings);
        if ( is_array($parts) )
        {
            foreach ( $parts as $i=> $values )
            {
                $oldNew = explode("=", $values);
                $content = str_replace( $oldNew[0], $oldNew[1], $content);
            }
        }

        return $content;
    }

    /**
     * Seta delimitador de campo
     * @param String $fieldDelimiter 
     */
    public function setFieldDelimiter($fieldDelimiter)
    {
        //suporte a caracteres especiais
        $fieldDelimiter = str_replace('\t', "\t", $fieldDelimiter);
        $fieldDelimiter = str_replace('\n', "\n", $fieldDelimiter);
        
        $this->fieldDelimiter = $fieldDelimiter;
    }

    /**
     * Obtém delimitador de campo
     * @return String 
     */
    public function getFieldDelimiter()
    {
        return $this->fieldDelimiter;
    }
    
    
    
    /**
     * Seta o delimitador de subcampo
     * @param String $subFieldDelimiter 
     */
    public function setSubFieldDelimiter($subFieldDelimiter)
    {
        $this->subFieldDelimiter = $subFieldDelimiter;
    }
    
    /**
     * Obtém delimitador de subcampo
     * @return String delimitador
     */
    public function getSubFieldDelimiter()
    {
        return $this->subFieldDelimiter;
    }

    /**
     * Método que obtém um array de GMaterialItem
     * @return array de itens 
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    /**
     * Método que define as tags do registros.
     * 
     * @param $tags Array de objetos GMaterialItem
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        $this->refreshRecord();
    }
    
    
    /**
     * Método que define uma tag para o registro.
     * 
     * @param GMaterialItem $tag Objeto GMaterialItem.
     * @return Boolean Retorna positivo caso tenha conseguido adicionar a tag.
     */
    public function addTag($tag)
    {
        $return = false;
        
        if ( $tag instanceof GMaterialItem )
        {
            $this->tags[] = $tag;
            $return = true;
            $this->refreshRecord();
        }

        return $return;
    }
    
    /**
     * Método responsável por atualizar o atríbuto record que contém o conteúdo MARC;
     */
    public function refreshRecord()
    {
        if ( is_array($this->tags) )
        {
            $arrayTags = array();
            foreach ( $this->tags as $tag )
            {
                if ( !strlen($tag->indicator1) )
                {
                    $tag->indicator1 = $this->emptyIndicator;
                }
                
                if ( !strlen($tag->indicator2) )
                {
                    $tag->indicator2 = $this->emptyIndicator;
                }
                
                $arrayTags[$tag->fieldid][$tag->line][] = array($tag->indicator1, $tag->indicator2, $tag->subfieldid, $tag->content, $tag->prefix, $tag->suffix);                
            }
            
            foreach ( $arrayTags as $fieldId => $line )
            {
                foreach ( $line as $j => $subfields )
                {
                    foreach ( $subfields as $i => $tag )
                    {
                        if ( $i == 0 )
                        {
                            if ( in_array($fieldId, $this->fieldsWithIndicator) )
                            {
                                $this->record  .= $fieldId . ' ' . $tag[0] . $tag[1];
                            }
                            else
                            {
                                 $this->record  .= $fieldId;
                            }
                        }
                        
                        // Concatena prefixo caso tenha.
                        if ( strlen($tag[4]) )
                        {
                            $tag[3] = $tag[4] . ' ' . $tag[3];
                        }
                        
                        // Concatena sufixo caso tenha.
                        if ( strlen($tag[5]) )
                        {
                            $tag[3] = $tag[3] . ' ' . $tag[5];
                        }
                         
                        if ( BusinessGnuteca3BusTag::isControlField($fieldId) )
                        {
                             $this->record  .= ' ' . $tag[3];
                        }
                        else
                        {
                            $this->record  .= ' ' . $this->subFieldDelimiter . $tag[2] . ' ' . $tag[3];
                        }
                    }
                    
                    $this->record .= $this->fieldDelimiter;
                }
            }
            
        }
        
    }
    
    /**
     * Método que obtém a String record
     * @return String record 
     */
    public function getRecord()
    {
        return $this->record;
    }
    
    /**
     * Método que obtém a String record
     * @return String record 
     */
    public function generate()
    {
        return $this->record;
    }
    
    //TODO: implementar método para adicionar campo
    public function addField($fieldId, $indicator1, $indicator2, $content)
    {
    }
    
    //TODO: implementar método para obter campo
    public function getField($fieldId, $subFieldId)
    {
    }
            
    //TODO: implementar método para apagar campo        
    public function deleteField($fieldId, $subFieldId)
    {
    }
}
?>