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
 * Class gIso2709Record, extends the default iso2709_record.
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
 * Class created on 29/08/2011
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('classes/iso2709record/iso2709.inc.php', 'gnuteca3');
$MIOLO->getClass('gnuteca3', 'gIso2709');

class gIso2709Record extends iso2709_record
{
    public function __construct($string, $update = true)
    {
        mb_internal_encoding('ISO-8859-1');
        parent::__construct($string, $update);
    }
    
    /**
     * Adiciona uma tag no registro
     * 
     * @param String $fieldid campo
     * @param int $indicator1 indicador 1
     * @param int $indicator1 indicador 2
     * @param array  $arraySubField subcampos
     */
    public function addField($fieldid, $indicator1, $indicator2, $arraySubField)
    {
        $indicators = substr(str_pad($indicator1, 1, ' '), 0, 1);
        $indicators .= substr(str_pad($indicator2, 1, ' '), 0, 1);
                                                 
        $this->add_field($fieldid, $indicators, $arraySubField);
    }
    
    /**
     * Adiciona uma campo de controle no registro
     * Ex: 001 003 008
     * 
     * @param String $fieldid campo
     * @param String content
     */
    public function addControlField($fieldid, $content)
    {
        $this->add_field($fieldid, '', $content);
    }
    
    /**
     * Obtém um campo do registro
     * 
     * @param String $fieldid
     * @param String $subfieldid
     * @return array com campo
     */
    public function getField($fieldId, $subfieldId = null)
    {
        if ( $subfieldId )
        {
            return $this->get_subfield($fieldId, $subfieldId);
        }
        else
        {
            return $this->get_subfield($fieldId);
        }
    }
    
    /**
     * Exclui um campo do registro
     * @param String $field campo 
     */
    public function deleteField($field)
    {
        $this->delete_field($field);
    }
    
    /**
     * Obtém as tags do objeto
     * @return type 
     */
    public function getTags()
    {
        $arrayObject = array();
        if ( is_array($this->inner_data) )
        {
            $lines = array();
            $linesControl = array();
            
            //percorre todos os dados do registro
            foreach ( $this->inner_data as $key => $data )
            {
                //retira os separadores do conteúdo    
                $data['content'] = str_replace(array($this->field_end, $this->record_end), '', $data['content']);

                $fieldId = $data['label']; //obtém o fielId
                //verifica se possui subcampos no conteúdo
                if ( !gIso2709::isControlField($fieldId) )
                {
                    //obtém os indicadores na primeira e segunda posição do conteúdo
                    $indicator1 = substr($data['content'], 0, 1); 
                    $indicator2 = substr($data['content'], 1, 1);
                    $content = substr($data['content'], 2); //separa os indicadores do conteúdo

                    $subFields = explode($this->subfield_begin, $content); //quebra conteúdo para obter os subcampos

                    if ( is_array($subFields) )
                    {
                        //percorre os subcampos da tag
                        foreach( $subFields as $l => $sub )
                        {
                            $subFieldId = substr($sub, 0, 1); //obtém o subcampo que está na primeira posição do conteúdo
                            $content = substr($sub, 1);

                            //só adiciona ao array de dados caso a tag tiver subcampo
                            if ( $subFieldId )
                            {
                                if ( is_null($lines[$fieldId][$subFieldId]) )
                                {
                                    $lines[$fieldId][$subFieldId] = 0;
                                }

                                $tag = new stdClass();
                                $tag->fieldid = $fieldId; //campo
                                $tag->subfieldid = $subFieldId; //subcampo
                                $tag->line = $lines[$fieldId][$subFieldId]; //controla as linhas
                                $tag->indicator1 = $indicator1;
                                $tag->indicator2 = $indicator2;
                                $tag->content = $content;
                                $arrayObject[] = $tag; //adiciona o objeto ao array de dados

                                $lines[$fieldId][$subFieldId]++;
                            }

                        }
                    }
                }
                else //quando for um campo de controle, não possui subcampo. //ex: campos 001 003 ...
                {
                    if ( is_null($linesControl[$fieldId]) )
                    {
                        $linesControl[$fieldId] = 0;
                    }
                                
                    $tag = new stdClass();
                    $tag->fieldid = $fieldId; //campo
                    $tag->subFieldid = null; //subcampo
                    $tag->line = $linesControl[$fieldId]; //controla as linhas
                    $tag->indicator1 = null;
                    $tag->indicator2 = null;
                    $tag->content = $data['content'];
                    
                    $arrayObject[] = $tag; //adiciona o objeto ao array de dados
                    
                    $linesControl[$fieldId]++;
                    
                    
                }
            }
        }
        
        return $arrayObject;
    }
    
    /**
     * Seta delimitador de registro
     * @param String $delimiter  delimitador de fim de registro
     */
    public function setRecordDelimiter($delimiter)
    {
        if ($delimiter)
        {
            $this->record_end = $delimiter;
            $this->rgx_record_end = dechex(ord($delimiter));
        }
    }
    
    /**
     * Seta delimitador de campo
     * @param String $delimiter  delimitador de fim de campo
     */
    public function setFieldDelimiter($delimiter)
    {
        if ( $delimiter )
        {
            $this->field_end = $delimiter;
            $this->rgx_field_end = dechex(ord($delimiter));
        }
    }
    
     /**
     * Seta delimitador de subcampo
     * @param String $delimiter  delimitador de começo de subcampo
     */
    public function subFieldDelimiter($delimiter)
    {
        if ( $delimiter )
        {
            $this->subfield_begin = $delimiter;
            $this->rgx_subfield_begin = dechex(ord($delimiter));
        }
    }
    
    public function generate()
    {
        return $this->full_record;
    }
    
    public function __toString() 
    {
        return $this->generate();
    }
    
    /**
     * Ajusta o código leader do registro ISO 2709
     * @param String $leader 
     */
    public function adjustLeader($leader)
    {
        $this->inner_guide['rs'] = substr($leader, 5, 1);
        $this->inner_guide['dt'] = substr($leader, 6, 1);
		$this->inner_guide['bl'] = substr($leader, 7, 1);
		$this->inner_guide['hl'] = substr($leader, 8, 1);
		$this->inner_guide['pos9'] = substr($leader, 9, 1);
		$this->inner_guide['il'] = substr($leader, 10, 1);
		$this->inner_guide['sl'] = substr($leader, 11, 1);
		$this->inner_guide['ba'] = substr($leader, 12, 5);
		$this->inner_guide['el'] = substr($leader, 17, 1);
		$this->inner_guide['ru'] = substr($leader, 18, 1);
		$this->inner_guide['pos19'] = substr($leader, 19, 1);
		$this->inner_guide['dm1'] = substr($leader, 20, 1);
		$this->inner_guide['dm2'] = substr($leader, 21, 1);	
		$this->inner_guide['dm3'] = substr($leader, 22, 1);	
		$this->inner_guide['pos23'] = substr($leader, 23, 1);
        
        $this->update(); //atualiza o full_record
    }
    
}

?>
