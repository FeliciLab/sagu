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
 * This file handles the connection and actions for gtcSpreadsheet table
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 26/09/2008
 *
 **/
class BusinessGnuteca3BusDeleteValuesOfSpreadSheet extends GBusiness
{
    private $busMaterialHistory;
    
    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->busMaterialHistory = $this->MIOLO->getBusiness($this->module, 'BusMaterialHistory');
    }
    
    public function countDeleteLinesOfGtcMaterial($tagsOfSpradSheets)
    {
    	if ( !is_array($tagsOfSpradSheets) )
    	{
    		return false;
    	}
    	
    	$result = array();
    	
    	foreach($tagsOfSpradSheets as $spreadSheet=>$tags)
    	{
             $tags = implode('\',\'', $tags);
            
             $spread = explode('.', $spreadSheet);
	         $args = array();
	         $args[] = $spread[0];
	         $args[] = $spread[1];

	         $this->clear();
    	     //SQL para contar registros afetados
    	     $this->clear();
    	     $this->setColumns('B.fieldid as fieldId,
    	                        B.subfieldid as subFieldId,
    	                        count(B.content) as count');
    	     
    	     $this->setTables('gtcmaterialcontrol A 
                               INNER JOIN gtcmaterial B 
                                       ON (A.controlnumber = B.controlnumber)');
    	     
    	     
    	     $this->setWhere("A.category = ?
                         AND A.level = ?
                         AND B.fieldid || '.' || B.subfieldid NOT IN ('{$tags}')
                         AND fieldid NOT LIKE '00%'
                    GROUP BY 1,2 ");
    	     
    	     $this->setOrderBy('fieldid, subfieldid asc ');
    	     
    	     
    	     $sql = $this->select($args);
    	     $rs = $this->query($sql, true);
            
    	     if ( is_array($rs) )
    	     {
    	     	foreach ($rs as $k=>$val)
    	     	{
    	     		$newValue = array();
    	     		$newValue[] = $args[0];
    	     		$newValue[] = $args[1];
    	     		$newValue[] = $val->fieldId;
                    $newValue[] = $val->subFieldId;
                    $newValue[] = $val->count;
                    $result[] = $newValue;
    	     	}
               
    	     }
    	}
   
        return $result;
    }
    
    
    public function deleteLinesOfGtcMaterial($tagsOfSpradSheets)
    {
        if ( !is_array($tagsOfSpradSheets) )
        {
            return false;
        }
        
        $result = array();
        $sql = array();
        
        foreach($tagsOfSpradSheets as $spreadSheet=>$tags)
        {
            $this->clear();
            $tags = implode('\',\'', $tags);
            
            $spread = explode('.', $spreadSheet);
            $args = array();
            $category = $spread[0];
            $level = $spread[1];
            
            $this->setTables('gtcmaterial A');
            
            $this->setWhere("A.fieldid || '.' || A.subfieldid NOT IN ('{$tags}')
                             AND fieldid NOT LIKE '00%'
                             AND A.controlnumber IN (SELECT controlnumber 
                                                      FROM gtcmaterialcontrol 
                                                     WHERE category = '{$category}'
                                                       AND level = '{$level}')");
            
            
            
             $sql[] = $this->delete($args);

             //salva histórico
             $this->saveHistoryOfTheDeletion($category, $level, $tags);
        }
        
        //FIXME: quando executado em bloco, não retorna valor booleano, talvez seja um bug do miolo
        $this->execute($sql); //executa tudo em um bloco para aproveitar a mesma transação
       
        return true;
    }
    
    private function saveHistoryOfTheDeletion($category=null, $level=null, $tags)
    {
    	$operator = GOperator::getOperatorId();
        $sql = "INSERT INTO gtcMaterialHistory (controlNumber,
                                                revisionNumber,
                                                operator,
                                                data,
				                                chancesType, 
				                                fieldId, 
				                                subfieldid, 
				                                previousLine, 
				                                previousIndicator1, 
				                                previousIndicator2, 
				                                previousContent, 
				                                currentLine, 
				                                currentIndicator1, 
				                                currentIndicator2, 
				                                currentContent)
                            SELECT A.controlNumber, 
                                   (SELECT coalesce(max(revisionnumber), 0)+1 
                                      FROM gtcMaterialHistory 
                                     WHERE controlNumber = a.controlNumber),
                                   '{$operator}',
                                   now()::date,
                                   'D',
                                   A.fieldId, 
							       A.subfieldid, 
							       A.line, 
							       A.indicator1, 
							       A.indicator2, 
							       A.content,
							       A.line, 
							       A.indicator1, 
							       A.indicator2, 
							       '' 
                              FROM gtcMaterial A 
                              INNER JOIN gtcMaterialControl B
                                      ON (A.controlNumber = B.controlNumber)
			                 WHERE B.category = '{$category}'
			                   AND B.level = '{$level}'
			                   AND A.fieldid || '.' || A.subfieldid NOT IN ('{$tags}')
			                   AND A.fieldid NOT LIKE '00%'";
        $ok = $this->execute($sql);        
        
        return $ok;
    }

}
?>

