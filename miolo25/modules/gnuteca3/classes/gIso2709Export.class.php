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
 * Class gIso2709Record, extends the default gIso2709.
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
$MIOLO->getClass('gnuteca3', 'gIso2709');
$MIOLO->getClass('gnuteca3', 'gIso2709Record');
class gIso2709Export extends gIso2709
{
    private $materials = array();
    
    public function __construct($materials)
    {
        $this->materials = $materials;
        
        parent::__construct();
    }
    
    /**
     * Adiciona número de control no objeto
     * @param int $controlNumber 
     */
    public function addMaterial($controlNumber)
    {
        $this->materials[] = $controlNumber;
    }
    
    /**
     * Apaga número de controle do objeto
     * @param int $index posição que está o número de controle
     */
    public function deleteMaterial($index)
    {
        unset($this->materials[$index]);
    }
    
    /**
     * Obtém o número de controle do material
     * 
     * @param int $index posição que está o número de controle
     * @return int número de controle 
     */
    public function getMaterial($index)
    {
        return $this->materials[$index];
    }
    
    /**
     * Executa a exportação dos materiais
     * @return String no padrão ISO2709 
     */
    public function execute()
    {
        $contents = array();
        
        foreach ( $this->materials as $key => $controlNumber )
        {
            $this->busMaterial->controlNumber = $controlNumber;
            $materials = $this->busMaterial->getMaterial(true);
            
            if ( is_array($materials) )
            {
                $materials = $this->parseSubFieldOfMaterial($materials);
                $record = new gIso2709Record(null, true);
                
                $leaderString = '';
                foreach ( $materials as $fieldId => $material )
                {
                    if ( $material->fieldId == '000' )
                    {
                        $leaderString = $material->subFields[0][1]->getString();
                    }
                    
                    //testa se o campo é um campo de controle
                    if ( self::isControlField($material->fieldId) )
                    {
                        $record->addControlField($material->fieldId, $material->content);
                    }
                    else //campo normal
                    {
                        $record->addField($material->fieldId, $material->indicator1, $material->indicator2, $material->subFields);
                    }
                }

                //ajusta o código leader do registro
                if ( strlen($leaderString) > 0 )
                {
                    $record->adjustLeader($leaderString); 
                }
                
                $contents[] = $record->generate();
            }
        }
        
        return implode("", $contents);
    }
    
    
    /**
     * Realiza o agrupamento dos subFields com o field
     * @param array $material de materiais
     * @return array com os subFields agrupados com os fields
     */
    private function parseSubFieldOfMaterial( $material )
    {
        $subFields = array();
        $indicatorsArray = array();
        $arrayValues = array();
        $controlFields = array();
        
        //percorre todos os registros do material para agrupar os subcampos por campo e linha
        foreach( $material as $k => $value )
        {
            if ( $this->checkIgnoreField($value->fieldid, $value->subfieldid) )
            {
                //obtém campos de controle
                if ( self::isControlField($value->fieldid) )
                {
                    $controlFields[$value->fieldid]->fieldId = $value->fieldid;
                    $string = GString::construct($value->content, 'ISO-8859-1'); //converte a string para ISO-8859-1
                    $string->replace('#', ' '); //substitui o '#' por espaço
                    $controlFields[$value->fieldid]->content = $string;
                }
                else
                {
                    
                    $subFields[$value->fieldid][$value->line][] = array($value->subfieldid, GString::construct($value->content, 'ISO-8859-1')); //FIXME: ver necessidade de converter a string para ISO, a princípio a classe francesa necessita que o dado chegue em ISO-8859-1
                    $indicatorsArray[$value->fieldid][$value->line]->indicator1 = $value->indicator1;
                    $indicatorsArray[$value->fieldid][$value->line]->indicator2 = $value->indicator2;
                }
            }
        }
        //percorre todos campos para adicionar os subcampos, indicador1 e indicador2 ao campo e linha
        foreach( $subFields as $fieldId => $lines )
        {
            foreach( $lines as $line => $value )
            {
                $arrayValues[$fieldId . '_' .$line]->fieldId = $fieldId;
                $arrayValues[$fieldId . '_' .$line]->indicator1 = $indicatorsArray[$fieldId][$line]->indicator1;
                $arrayValues[$fieldId . '_' .$line]->indicator2 = $indicatorsArray[$fieldId][$line]->indicator2;
                $arrayValues[$fieldId . '_' .$line]->subFields = $value;
            }
        }
        
        $arrayValues = array_merge($controlFields, $arrayValues);
        
        return $arrayValues;
    }
    
}

?>
