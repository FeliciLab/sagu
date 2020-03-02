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
 * Business da verificação de inventário
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 26/09/2011
 *
 **/

class BusinessGnuteca3BusInventoryCheck extends GBusiness
{
    public $libraryUnitId,
           $exemplaryStatusId,
           $beginClassification,
           $endClassification,
           $inventory;
    
    /**
     * Obtem exemplares não encontrados e fora de lugar 
     * 
     * @return array de dados 
     */
    public function inventoryCheck()
    {
        $MIOLO = MIOLO::getInstance();
        
        //prepara o conteúdo por PHP por questões de otimização
        $this->beginClassification = strtoupper(BusinessGnuteca3BusMaterial::prepareSearchContent('090.a', $this->beginClassification));
        $this->endClassification = strtoupper(BusinessGnuteca3BusMaterial::prepareSearchContent('090.a', $this->endClassification));
        
        //cria tabela temporária
        $this->db->execute("CREATE TEMP TABLE gtcTmpInventary (itemNumber varchar)");
        $exemplarys = explode("\n", $this->inventory);
        
        //insere exemplares na tabela temporária
        if ( is_array($exemplarys) )
        {
            foreach( $exemplarys as $i => $itemNumber )
            {
                if ( strlen($itemNumber) > 0 )
                {
                    $this->db->execute($sq[$i] = "INSERT INTO gtcTmpInventary VALUES (trim('{$itemNumber}', '\r')); \n");
                }
            }
        }
       
        $result = array();
        
        $missing = $this->verifyMissedExemplarys(); //busca exemplares não encontrados
       
        $wrong = $this->verifyWrongExemplarys(); //busca exemplares que estão fora de lugar
        
        //faz o merge dos perdisos e fora de lugar
        if ( is_array($missing) && is_array($wrong) )
        {
            $result = array_merge($missing, $wrong);
        }
        else if ( is_array($missing) )
        {
            $result = $missing;
        }
        else if ( is_array($wrong) )
        {
            $result = $wrong;
        }
        
        return $result;
    }
    
    /**
     * Método que busca exemplares que não se encontram na lista de exemplares
     * 
     * @return array de dados 
     */
    private function verifyMissedExemplarys()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        
        $data = array($this->beginClassification, 
                      $this->endClassification);
        
        $sql = " SELECT A.controlnumber,
                        'data',
                        B.itemNumber,
                        C.description,
                        'Não encontrado'
                   FROM gtcMaterial A
             INNER JOIN gtcExemplaryControl B
                     ON (A.controlNumber = B.controlNumber)
             INNER JOIN gtcExemplaryStatus C
                     ON (B.exemplaryStatusId = C.exemplaryStatusId)
                  WHERE A.fieldid = '090' 
                    AND A.subfieldid = 'a'
                    AND UPPER(split_part(A.searchcontent, '@', 1)) BETWEEN ? AND ?
                    AND B.itemnumber NOT IN (SELECT itemNumber FROM gtcTmpInventary)";
        
        if ( $this->exemplaryStatusId )
        {
            $status = implode(',', $this->exemplaryStatusId);
            $sql .= " AND B.exemplaryStatusId IN ({$status}) ";
        }
        
        if ( $this->libraryUnitId )
        {
            $sql .= " AND B.libraryUnitId = ? ";
            $data[] = $this->libraryUnitId;
        }
        
        $sql = $this->prepare($sql, $data);

        $result = $this->query($sql);
        
        foreach ( $result as $key => $line )
        {
            $result[$key][1] = strip_tags($busSearchFormat->getFormatedString($line[0], ADMINISTRATION_SEARCH_FORMAT_ID));
        }
        
        return $result;
    }
    
    /**
     * Método que busca exemplares que se encontram na lista e não se encontram na base de dados
     * 
     * @return array de dados 
     */
    private function verifyWrongExemplarys()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        
        //sql interno
        $this->MSQL->clear();
        
        $data = array($this->beginClassification, 
                      $this->endClassification);
        
        $this->setTables('gtcMaterial A
               INNER JOIN gtcExemplaryControl B
                       ON (A.controlNumber = B.controlNumber)');

        $this->setColumns("B.itemnumber");
        
        $this->setWhere ("A.fieldid = '090' 
                      AND A.subfieldid = 'a'
                      AND UPPER(split_part(A.searchcontent, '@', 1)) BETWEEN ? AND ?");
        
        if ( $this->exemplaryStatusId )
        {
            $status = implode(',', $this->exemplaryStatusId);
            $this->setWhere("B.exemplaryStatusId IN ({$status})");
        }
        
        if ( $this->libraryUnitId )
        {
            $this->setWhere('B.libraryUnitId = ?');
            $data[] = $this->libraryUnitId;
        }
        
        $internalSql = $this->select($data);
        
        //sql externo
        $this->MSQL->clear();
        
        $this->setColumns('*');
        $this->setTables("gtcTmpInventary");
        $this->setWhere("itemNumber NOT IN ({$internalSql})");
       
        $sqlItemNumber = $this->select();
        
        $this->clear();
        $this->setTables('gtcExemplaryControl A
               INNER JOIN gtcExemplaryStatus B
                       ON (A.exemplaryStatusId = B.exemplaryStatusId)');
        
        $this->setColumns("A.controlnumber,
                          'data',
                          A.itemnumber,
                          B.description,
                          'Fora de lugar'");
        
        $this->setWhere("A.itemnumber IN ($sqlItemNumber)");
                
        $sql = $this->select();

        $result = $this->query($sql);
        
        foreach ( $result as $key => $line )
        {
            $result[$key][1] = strip_tags($busSearchFormat->getFormatedString($line[0], ADMINISTRATION_SEARCH_FORMAT_ID));
        }
        
        return $result;
    }
    

}
?>
