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
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *         Jamiel Spezia [jamiel@solis.coop.br] 
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 31/08/2011
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->getClass('gnuteca3', 'gIso2709Record');

class gIso2709
{
    protected $records,
            $endRecord = array();
    
    protected $busMaterial,
              $busPreCatalogue;
    
    public function __construct($content, $endRecord = null, $endField = null, $beginSubField = null)
    {
        set_time_limit ( 0 ); //seta o php com tempo limite infinito para este processo
        
        $MIOLO = MIOLO::getInstance();
        $this->busMaterial = $MIOLO->getBusiness('gnuteca3', 'BusMaterial');
        $this->busPreCatalogue = $MIOLO->getBusiness('gnuteca3', 'BusPreCatalogue');
        
        $this->setEndRecord($endRecord); //seta o caracter que representa o fim do registro
        $records = explode($this->endRecord, $content); //explode o conteúdo pelo caracter fim de registro
     
        if ( is_array($records) )
        {
            foreach ( $records as $key => $recordString )
            {
                if ( strlen($recordString) > 0 )
                {
                    $record = new gIso2709Record($recordString); //gera um record
                    $record->setRecordDelimiter($endRecord); //seta delimitador de registro
                    $record->setFieldDelimiter($endField); //seta delimitador de campo
                    $record->subFieldDelimiter($beginSubField); //seta delimitador de subcampo

                    $this->addRecord($record); //adiciona o record bo objeto
                }
            }
        }
    }
    
    /**
     * Obtém a quantidade de registros no objeto
     * @return int quantidade de registros 
     */
    public function size()
    {
        return sizeof($this->records);
    }
    
    /**
     * Adiciona um registro no objeto
     * 
     * @param gIso2709Record $record 
     */
    public function addRecord(gIso2709Record $record)
    {
        $this->records[] = $record;
    }
    
    /**
     * Obtém um registro do objeto
     * 
     * @param int $index indice do registro
     * @return gIso2709Record registro 
     */
    public function getRecord($index = null)
    {
        return $this->records[$index];
    }
    
    /**
     * Seta o caractere que representa o fim do registro
     * @param String $endRecord 
     */
    public function setEndRecord($endRecord)
    {
        $this->endRecord = $endRecord;
    }
    
    /**
     * Obtém o caractere que representa o fim do registro
     * @return String caracter 
     */
    public function getEndRecord()
    {
        return $this->endRecord;
    }
    
    /**
     * Checa se o fieldId é um campo que pode ser exportado/importado
     * Quando o parametro export é false, indica que o método está sendo usado em importação
     * 
     * @param String $fieldId campo
     * @param String subcampo
     * @param boolean export flag para sinalizar exportação
     * @return boolean
     */
    protected function checkIgnoreField($fieldId, $subFieldId, $export = true)
    {
        $tagValue = $fieldId . '.' . $subFieldId;
        
        if ( $export )
        {
            $preference = ISO2709_EXPORT; //obtém a preferência de exportação
        }
        else
        {
            $preference = ISO2709_IMPORT; //obtém a preferência de importação
        }
        
        $tags = explode(',', $preference); //gera um array com os dados da preferência explodindo por ","
        
        if ( is_array($tags) )
        {
            foreach ( $tags as $k => $tag )
            {
                $size = strlen($tag); //obtém tamanho da string

                if ( substr($tagValue, 0, $size) == $tag ) //trunca a string com o mesmo tamanho da tag e compara as duas, caso for igual, retorna false
                {
                    return false;
                }
            }
        }

        return true;
    }
    
    public static function isControlField($fieldId)
    {
       return in_array($fieldId,  array('001', '003', '005', '008'));
    }
    
    
    
    /**
     * Gera a String com todos os registros do objeto
     * 
     * @return String conteúdo
     */
    public function generate()
    {
        $content = '';
        
        //chama o generate de cada registro
        foreach ( $this->records as $key => $record )
        {
            $content .= $record->generate();
        }
        
        return $content;
    }
    
}

?>