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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 23/10/2008
 *
 * */
class BusinessGnuteca3BusMaterial extends GBusiness
{
    public $MIOLO;
    public $controlNumber,
            $fieldid,
            $subfieldid,
            $line,
            $indicator1,
            $indicator2,
            $content,
            $searchContent,
            $contentS,
            $lineS,
            $prefixid,
            $suffixid,
            $separatorid,
            $prefixidS,
            $suffixidS,
            $separatoridS,
            $exactContent,
            $controlNumberDiff,
            $complement; //utilizado para o searchContent
    public $fullColumns;
    public $businessCataloge;

    function __construct()
    {
        parent::__construct();

        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();

        $this->fullColumns = 'controlNumber, fieldid, subfieldid, line, indicator1, indicator2, content, searchContent, prefixid, suffixid, separatorid';

        $this->setData(null);
        $this->setColumns();
        $this->setTables();
    }

    /**
     * Seta as tabelas
     *
     */
    public function setTables()
    {
        parent::setTables("gtcmaterial");
    }

    /**
     * Este mÃ©todo seta as colunas da tabela.
     *
     * @param String
     */
    public function setColumns($columns = null)
    {
        $columns = $columns ? $columns : $this->fullColumns;
        parent::setColumns($this->fullColumns);
    }

    /**
     * Seta as condiÃ§Ãµes do sql
     *
     * @return void
     */
    public function getWhereCondition()
    {
        $where = "";

        if ( !is_null($this->controlNumber) )
        {
            $where.= " controlNumber = ? AND ";
        }
        if ( !is_null($this->controlNumberDiff) )
        {
            $where.= " controlNumber != ? AND ";
        }
        if ( !is_null($this->fieldid) )
        {
            $where.= " fieldid = ? AND ";
        }
        if ( !is_null($this->subfieldid) )
        {
            $where.= " subfieldid = ? AND ";
        }
        if ( !is_null($this->line) )
        {
            $where.= " line = ? AND ";
        }
        if ( !is_null($this->contentS) )
        {
            //$where.= " lower(content) LIKE lower(?) ";
            $where.= " lower(searchcontent) LIKE lower(?) ";
        }
        if ( !is_null($this->exactContent) )
        {
            $where.= " content = ? AND ";
        }

        if ( strlen($where) )
        {
            $where = substr($where, 0, strlen($where) - 4);
            parent::setWhere($where);
        }
    }

    /**
     * Trabalha o Data Object retornado do form
     *
     * transforma em um array para enviar para o where condition do sql
     *
     * @return (Array) $args
     */
    private function getDataConditionArray()
    {
        $args = array( );

        if ( !is_null($this->controlNumber) )
        {
            $args[] = $this->controlNumber;
        }
        if ( !is_null($this->controlNumberDiff) )
        {
            $args[] = $this->controlNumberDiff;
        }
        if ( !is_null($this->fieldid) )
        {
            $args[] = $this->fieldid;
        }
        if ( !is_null($this->subfieldid) )
        {
            $args[] = $this->subfieldid;
        }
        if ( !is_null($this->line) )
        {
            $args[] = $this->line;
        }
        if ( !is_null($this->contentS) )
        {
            $args[] = "%" . str_replace(" ", "%", $this->content) . "%";
        }
        if ( !is_null($this->exactContent) )
        {
            $args[] = "$this->exactContent";
        }

        return $args;
    }

    public function searchMaterial($order = null)
    {
        parent::clear();
        $this->setTables();
        $this->setColumns();
        $this->getWhereCondition();

        if ( !is_null($order) )
        {
            $this->setOrderBy($order);
        }

        $sql = parent::select($this->getDataConditionArray());
        return parent::query();
    }

    /**
     * FIXME precisa considerar campos unidos (gtcSearchableField)
     * 
     * @return boolean
     */
    public function insertMaterial()
    {
        $this->clear();

        //isso evita que gere erros em função de não ter linha
        if ( !$this->line )
        {
            $this->line = '0';
        }

        $data = array(
            $this->controlNumber,
            $this->fieldid,
            $this->subfieldid,
            $this->line,
            $this->indicator1,
            $this->indicator2,
            str_replace("\r", "", $this->content),
            $this->fieldid . '.' . $this->subfieldid,
            str_replace("\r", "", $this->content),
            $this->complement,
            $this->prefixid,
            $this->suffixid,
            $this->separatorid,
            $this->fieldid . '.' . $this->subfieldid,
            str_replace("\r", "", $this->content),
            $this->complement
        );

        $sql = " INSERT INTO gtcmaterial
                    ( controlNumber,
                    fieldid,
                    subfieldid,
                    line,
                    indicator1,
                    indicator2,
                    content,
                    searchContent,
                    prefixid,
                    suffixid,
                    separatorid,
                    searchContentForSearchModule)
                    VALUES
                    ( ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ( SELECT prepareSearchContent ( ?,?, ? ) )
                    ,?,
                    ?,
                    ?,
                    ( SELECT prepareSearchContentForSearchModule ( ?,?, ? ) ))";

        $this->MSQL->command = $sql;
        $sql = $this->MSQL->prepare($data);

        return $this->execute($sql);
    }

    /**
     * Atualização de dados
     * FIXME precisa considerar campos unidos (gtcSearchableField)
     */
    public function updateMaterialContent()
    {
        $this->clear();

        $data = array
            (
            str_replace("\r", "", $this->content),
            $this->fieldid . '.' . $this->subfieldid,
            "{$this->content}",
            $this->complement,
            $this->fieldid . '.' . $this->subfieldid,
            "{$this->content}",
            $this->complement,        
            $this->controlNumber,
            $this->fieldid,
            $this->subfieldid,
            "{$this->line}"
        );

        $sql = "UPDATE gtcmaterial
                    SET content = ?,
                        searchcontent= (select prepareSearchContent ( ?,?,? ) ),
                        searchcontentForSearchModule= (select prepareSearchContentForSearchModule ( ?,?,? ) )
                  WHERE controlNumber = ?
                    AND fieldid = ?
                    AND subfieldid = ?
                    AND line = ?";

        $this->MSQL->command = $sql;
        $sql = $this->MSQL->prepare($data);
        
        if ( $this->fieldid == '520' && $this->subfieldid == 'a' )
        {
            $data = array
                (
                str_replace("\r", "", $this->content),
                $this->fieldid . '.' . $this->subfieldid,
                "{$this->content}",
                $this->complement,
                $this->fieldid . '.' . $this->subfieldid,
                "{$this->content}",
                $this->complement,        
                $this->controlNumber,
                $this->fieldid,
                $this->subfieldid
            );

            $sql = "UPDATE gtcmaterial
                        SET content = ?,
                            searchcontent= (select prepareSearchContent ( ?,?,? ) ),
                            searchcontentForSearchModule= (select prepareSearchContentForSearchModule ( ?,?,? ) )
                      WHERE controlNumber = ?
                        AND fieldid = ?
                        AND subfieldid = ?";

            $this->MSQL->command = $sql;
            $sql = $this->MSQL->prepare($data);
        }
        
        if($this->fieldid == '090' && $this->subfieldid == 'b')
        {
            $this->execute($sql);
            
            $this->subfieldid = 'a';
            $content = $this->query("SELECT content FROM gtcmaterial WHERE controlnumber='{$this->controlNumber}' AND fieldid='{$this->fieldid}' AND subfieldid='{$this->subfieldid}'");
            $this->content = $content[0][0];

            $data = array
            (
                str_replace("\r", "", $this->content),
                $this->fieldid . '.' . $this->subfieldid,
                "{$this->content}",
                $this->complement,
                $this->fieldid . '.' . $this->subfieldid,
                "{$this->content}",
                $this->complement,        
                $this->controlNumber,
                $this->fieldid,
                $this->subfieldid,
                "{$this->line}"
            );
            
            $sql = "UPDATE gtcmaterial
                        SET content = ?,
                            searchcontent= (select prepareSearchContent ( ?,?,? ) ),
                            searchcontentForSearchModule= (select prepareSearchContentForSearchModule ( ?,?,? ) )
                    WHERE controlNumber = ?
                        AND fieldid = ?
                        AND subfieldid = ?
                        AND line = ?";

            $this->MSQL->command = $sql;
            $sql = $this->MSQL->prepare($data);
            $this->execute($sql);
            
            $this->fieldid = '080';
            $this->subfieldid = 'a';
            $content = $this->query("SELECT content FROM gtcmaterial WHERE controlnumber='{$this->controlNumber}' AND fieldid='{$this->fieldid}' AND subfieldid='{$this->subfieldid}'");
            $this->content = $content[0][0];
            
            $data = array
            (
                str_replace("\r", "", $this->content),
                $this->fieldid . '.' . $this->subfieldid,
                "{$this->content}",
                $this->complement,
                $this->fieldid . '.' . $this->subfieldid,
                "{$this->content}",
                $this->complement,        
                $this->controlNumber,
                $this->fieldid,
                $this->subfieldid,
                "{$this->line}"
            );
            
            $sql = "UPDATE gtcmaterial
                        SET content = ?,
                            searchcontent= (select prepareSearchContent ( ?,?,? ) ),
                            searchcontentForSearchModule= (select prepareSearchContentForSearchModule ( ?,?,? ) )
                    WHERE controlNumber = ?
                        AND fieldid = ?
                        AND subfieldid = ?
                        AND line = ?";
            
            $this->MSQL->command = $sql;
            $sql = $this->MSQL->prepare($data);
        }
        
        return $this->execute($sql);
    }

    /**
     * Esta função foi criada por que o miolo estava preparando o sql errado na função acima.
     * sql gerado: UPDATE gtcmaterial SET content=  . ed.,searchcontent=  . ED. WHERE  controlNumber = '12019' AND  fieldid = '250' AND  subfieldid = 'a' AND  line = '0'
     * 
     * FIXME remover essa função, pois é duplicada
     * @deprecated
     */
    public function updateMaterialContentDirectSql()
    {
        $this->updateMaterialContent;
    }

    /**
     * Atualiza somente o conteúdo de pesquisa
     */
    public function updateMaterialSearchContent()
    {
        $this->clear();
        $tag = $this->fieldid . '.' . $this->subfieldid;

        $data = array
            (
            $tag,
            $this->searchContent,
            $this->complement,
            $tag,
            $this->searchContent,
            $this->complement,
            $this->controlNumber,
            $this->fieldid,
            $this->subfieldid,
            "{$this->line}"
        );

        $sql = "UPDATE gtcmaterial
                   SET searchcontent = (SELECT prepareSearchContent ( ?,?,? ) ),
                       searchcontentforsearchmodule = (SELECT prepareSearchContentForSearchModule ( ?,?,? ) )
                 WHERE controlNumber = ?
                   AND fieldid = ?
                   AND subfieldid = ?
                   AND line = ?";

        $this->MSQL->command = $sql;
        $sql = $this->MSQL->prepare($data);

        return $this->execute($sql);
    }

    public function updateMaterialSuffix($controlNumber, $tag, $suffixId, $line)
    {
        parent::clear();
        $this->setTables();
        parent::setColumns("suffixId");
        parent::setWhere
                ("
            controlNumber   = ? AND
            fieldId         = ? AND
            subFieldId      = ? AND
            line            = ?
        ");

        list($fieldId, $subFieldId) = explode(".", $tag);

        $data = array
            (
            $suffixId,
            $controlNumber,
            $fieldId,
            $subFieldId,
            $line
        );

        $sql = parent::update($data);

        return parent::Execute();
    }

    public function updateMaterialPrefix($controlNumber, $tag, $prefixId, $line)
    {
        parent::clear();
        $this->setTables();
        parent::setColumns("prefixId");
        parent::setWhere
                ("
            controlNumber   = ? AND
            fieldId         = ? AND
            subFieldId      = ? AND
            line            = ?
        ");

        list($fieldId, $subFieldId) = explode(".", $tag);

        $data = array
            (
            $prefixId,
            $controlNumber,
            $fieldId,
            $subFieldId,
            $line
        );

        $sql = parent::update($data);

        return parent::Execute();
    }

    public function updateMaterialSeparator($controlNumber, $tag, $separatorId, $line)
    {
        parent::clear();
        $this->setTables();
        parent::setColumns("separatorId");
        parent::setWhere
                ("
            controlNumber   = ? AND
            fieldId         = ? AND
            subFieldId      = ? AND
            line            = ?
        ");

        list($fieldId, $subFieldId) = explode(".", $tag);

        $data = array
            (
            $separatorId,
            $controlNumber,
            $fieldId,
            $subFieldId,
            $line
        );

        $sql = parent::update($data);

        return parent::Execute();
    }

    public function updateMaterialIndicator()
    {
        parent::clear();

        $this->getWhereCondition();

        $this->setTables();
        parent::setColumns("indicator1, indicator2");

        $data = array
            (
            $this->indicator1,
            $this->indicator2,
            $this->controlNumber,
            $this->fieldid
        );

        $sql = parent::update($data);

        return parent::Execute();
    }

    public function getMaterial($toObject = false)
    {
        parent::clear();

        $this->setTables();
        $this->setColumns();

        $this->getWhereCondition();
        $this->setOrderBy('fieldid, line, subfieldid');
        $sql = $this->select($this->getDataConditionArray());

        $result = $this->query($sql, $toObject);

        return $result;
    }

    public function deleteMaterial()
    {
        
        parent::clear();

        $this->setTables();

        $this->getWhereCondition();
        $data = $this->getDataConditionArray();

        $sql = parent::delete($data);

        $MIOLO = MIOLO::getInstance();
        $busMaterialSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusMaterialSearchFormat');
        $busMaterialSearchFormat->deleteAllSearchFormatForControlNumber($this->controlNumber);
        
        $result = parent::Execute();

        return $result;
    }

    public function clean()
    {
        $this->this->controlNumber =
                $this->fieldid =
                $this->subfieldid =
                $this->line =
                $this->indicator1 =
                $this->indicator2 =
                $this->content =
                $this->searchContent =
                $this->contentS =
                $this->lineS =
                $this->prefixid =
                $this->suffixid =
                $this->separatorid =
                $this->prefixidS =
                $this->suffixidS =
                $this->separatoridS =
                $this->exactContent =
                $this->controlNumberDiff = null;
    }

    /**
     * Get content of material
     *
     * @param $controlNumber (String)
     * @param $fieldId (integer)
     * @param $subfieldId (char)
     * @param $line (integer)
     *
     * @return (String) Content
     */
    public function getContent($controlNumber, $fieldId, $subfieldId = null, $line = null, $returnFullLine = false, $returnMultiLine = false, $orderBy = null)
    {
        $getPrefixSuffix = MUtil::getBooleanValue(MATERIAL_SEARCH_USE_PREFIX_SUFFIX);

        parent::clear();

        if ( $getPrefixSuffix && !$returnFullLine )
        {
            $table = 'gtcMaterial material LEFT JOIN gtcPrefixSuffix prefix on (prefix.prefixSuffixId = material.prefixId) LEFT JOIN gtcPrefixSuffix suffix on (suffix.prefixSuffixId = material.suffixId) ';
            $content = "coalesce(prefix.content,'') || coalesce(material.content,'') || coalesce(suffix.content,'') as content ";
        }
        else
        {
            $table = 'gtcMaterial material';
            $content = 'content';
        }

        parent::setTables($table);
        parent::setColumns(($returnFullLine ? $this->fullColumns : $content));

        $this->setWhere('material.controlNumber = ?');
        $this->setWhere('material.fieldId = ?');

        $args[] = $controlNumber;
        $args[] = $fieldId;

        if ( $subfieldId )
        {
            if ( !is_array($subfieldId) )
            {
                $subfieldId = array( $subfieldId );
            }

            $subfieldId = implode("','", $subfieldId);
            $args[] = $subfieldId;
            $this->setWhere("material.subfieldId in (?)");
        }

        if ( isset($line) )
        {
            $this->setWhere('material.line = ?');
            $args[] = $line;
        }

        if ( !is_null($orderBy) )
        {
            $this->setOrderBy($orderBy);
        }

        $sql = $this->select($args);
        $query = $this->query($sql, $returnFullLine);

        parent::setTables('gtcMaterial'); //restaura a tabela original no business

        if ( $returnFullLine && !$returnMultiLine )
        {
            return $query[0];
        }
        elseif ( $returnMultiLine )
        {
            return $query;
        }

        return $query[0][0];
    }

    public function getContentTag($controlNumber, $tag, $line = null, $returnFullLine = false, $returnMultiLine = false, $orderBy = null)
    {
        if ( ereg(",", $tag) )
        {
            $tags = explode(",", $tag);
            $content = "";
            foreach ( $tags as $tag )
            {
                list($fieldId, $subfieldId) = explode('.', trim($tag));
                $content.= $this->getContent($controlNumber, $fieldId, $subfieldId, $line);
                $content.= ", ";
            }

            return substr($content, 0, -2);
        }

        list($fieldId, $subfieldId) = explode('.', trim($tag));
        return $this->getContent($controlNumber, $fieldId, $subfieldId, $line, $returnFullLine, $returnMultiLine, $orderBy);
    }

    /**
     * Retorna o titulo do material
     *
     * @param integer $controlNumber
     * @param integer $line
     * @return string
     */
    public function getMaterialTitle($controlNumber, $line = null)
    {
        return $this->getContentTag($controlNumber, MARC_TITLE_TAG);
    }

    /**
     * Retorna o titulo do material
     *
     * @param integer $controlNumber
     * @param integer $line
     * @return string
     */
    public function getMaterialTitleByItemNumber($itemNumber)
    {
        return $this->getContentByItemNumber($itemNumber, MARC_TITLE_TAG);
    }

    /**
     * Retorna o titulo do material
     *
     * @param integer $controlNumber
     * @param integer $line
     * @return string
     */
    public function getMaterialAuthor($controlNumber, $line = null)
    {
        return $this->getContentTag($controlNumber, MARC_AUTHOR_TAG);
    }

    /**
     * Retorna o titulo do material
     *
     * @param integer $controlNumber
     * @param integer $line
     * @return string
     */
    public function getMaterialAuthorByItemNumber($itemNumber)
    {
        return $this->getContentByItemNumber($itemNumber, MARC_AUTHOR_TAG);
    }

    /**
     * retorna a classificação de um determinado material
     *
     * @param unknown_type $controlNumber
     * @return unknown
     */
    public function getMaterialClassification($controlNumber)
    {
        return $this->getContentTag($controlNumber, MARC_CLASSIFICATION_TAG);
    }
    
    /**
     * Método que busca o searchcontent da classificação conforme os números de controle.
     * 
     * @param array $controlnumbers Números de controle.
     * @return array Vetor o conteúdo de pesquisa das classificações. 
     */
    public function getMaterialClassificationSearchContentFromControlNumbers($controlNumbers)
    {
        $this->clear();
        $this->setTables('gtcMaterial');
        parent::setColumns('DISTINCT searchcontent');
        $controlNumbers = implode(',', $controlNumbers);
        $this->setWhere("controlnumber IN ($controlNumbers)");
        $this->setWhere("fieldid = '090'");
        $this->setWhere("subfieldid = 'a'");
        
        $sql = $this->select();
        
        $result = $this->query($sql);
        
        $return = array();
        if ( is_array($result) )
        {
            foreach ( $result as $i => $values )
            {
                $return[] = $values[0];
            }
        }
        
        return $return;
    }
    
    /**
     * Método público para obter informações das obras a partir dos números de controles passados por parâmetro.
     * 
     * @param Array $controlNumbers Vetor de números de controle.
     * @return Array Vetores de informações das obras. 
     */
    public function getMaterialFromControlNumbers($controlNumbers)
    {
        $return = array();
        
        foreach( $controlNumbers as $controlNumber )
        {
            // Obtém a classificação através do número de controle.
            $classification = $this->getMaterialClassificationSearchContentFromControlNumbers( array($controlNumber) );
            
            if ( is_array($classification) )
            {
                $this->clear();
                parent::setColumns("A.libraryunitid,
                                    B.libraryname,
                                    A.materialphysicaltypeid,
                                    C.description,
                                    (SELECT content FROM gtcmaterial WHERE fieldid = '949' AND subfieldid = 'v' AND controlnumber = A.controlnumber AND line = A.line) as volume,
                                    (SELECT string_agg(content, '<\br>') as url FROM gtcmaterial WHERE fieldid = '856' AND subfieldid = 'u' AND controlnumber = A.controlnumber),                                    
                                    count(A.*) as quantidade");
                parent::setTables('gtcexemplarycontrol A 
                    INNER JOIN gtclibraryunit B
                            USING (libraryunitid)
                    INNER JOIN gtcmaterialphysicaltype C
                            USING (materialphysicaltypeid)
                    INNER JOIN gtcexemplarystatus D
                           USING (exemplarystatusid)');

                $this->setWhere("controlnumber IN ( SELECT controlnumber 
                                                    FROM gtcmaterial A 
                                                    WHERE fieldid = '090' 
                                                    AND subfieldid = 'a' 
                                                    AND searchcontent = (?)
                                                    AND D.islowstatus = FALSE)
                                GROUP BY 1,2,3,4,5,6;");

                $sql = $this->select(array($classification[0]));

                $result = $this->query($sql, FALSE);
                $return = null;
                foreach($result as $material => $materialInfo)
                {
                    $stdObject = new stdClass();
                    $stdObject->libraryunitid = $materialInfo[0];
                    $stdObject->libraryname = $materialInfo[1];
                    $stdObject->materialphysicaltypeid = $materialInfo[2];
                    $stdObject->description = $materialInfo[3];
                    $stdObject->volume = $materialInfo[4];
                    $stdObject->url = $materialInfo[5];
                    $stdObject->quantidade = $materialInfo[6];
                    
                    $return[$material] = $stdObject;
                }
                
                $list[$controlNumber] = $return;
            }
        }
        
        return $list;
    }

    /**
     * Passa-se o numero de controle e o metodo retorna numeros de controle similares.
     * @param type $controlNumbers
     * @return type
     */
    public function getSimilarControlNumber($controlNumbers)
    {
        $return = array();
        
        foreach( $controlNumbers as $controlNumber )
        {
            // Obtém a classificação através do número de controle.
            $classification = $this->getMaterialClassificationSearchContentFromControlNumbers( array($controlNumber) );
            
            if ( is_array($classification) )
            {
                $this->clear();
                parent::setColumns("controlnumber");
                parent::setTables('gtcexemplarycontrol A 
                    INNER JOIN gtclibraryunit B
                            USING (libraryunitid)
                    INNER JOIN gtcmaterialphysicaltype C
                            USING (materialphysicaltypeid)
                    INNER JOIN gtcexemplarystatus D
                           USING (exemplarystatusid)');

                $this->setWhere("controlnumber IN ( SELECT controlnumber 
                                                    FROM gtcmaterial A 
                                                    WHERE fieldid = '090' 
                                                    AND subfieldid = 'a' 
                                                    AND searchcontent = (?)
                                                    AND D.islowstatus = FALSE)
                                GROUP BY controlnumber");

                $sql = $this->select(array($classification[0]));

                $return[$controlNumber] = $this->query($sql, TRUE);
            }
        }
        
        return $return;
    }    

    public function getMaterialType($controlNumber)
    {
        $value = $this->getContentTag($controlNumber, MARC_MATERIAL_TYPE_TAG);
        return $this->relationOfFieldsWithTable(MARC_MATERIAL_TYPE_TAG, $value, false);
    }

    public function getMaterialTypeByItemNumber($itemNumber)
    {
        $value = $this->getContentByItemNumber($itemNumber, MARC_MATERIAL_TYPE_TAG);
        return $this->relationOfFieldsWithTable(MARC_MATERIAL_TYPE_TAG, $value, false);
    }

    /**
     * retorna a classificação relativa aos parametros 1 e 2
     *
     * @param unknown_type $classification
     * @param unknown_type $ignoreClassification
     * @param unknown_type $controlNumbers
     * @return unknown
     */
    public function getControlNumberRelativeClassification($classification = null, $ignoreClassification = null, $controlNumbers = null, $distinct = false)
    {
        parent::clear();
        parent::setTables("gtcMaterial");
        parent::setColumns(($distinct ? "DISTINCT " : "" ) . " controlNumber, content");

        $classificationTags = explode(",", MARC_CLASSIFICATION_TAG);
        $classWhere = "";

        foreach ( $classificationTags as $tags )
        {
            list($fieldId, $subfieldId) = explode(".", $tags);
            $classWhere.= "( fieldid  = '$fieldId' AND subfieldid = '$subfieldId' ) OR ";
        }

        $classWhere = "((" . substr($classWhere, 0, -3) . ") ";

        if ( $distinct )
        {
            $classification = str_replace(", ", "' OR content ILIKE '", $classification);
            $ignoreClassification = str_replace(", ", "' AND content NOT ILIKE '", $ignoreClassification);
        }

        if ( strlen($classification) )
        {
            $classWhere.= " AND (content ILIKE '$classification')";
        }
        if ( $ignoreClassification )
        {
            $classWhere.= " AND (content NOT ILIKE '$ignoreClassification')";
        }

        $classWhere.= ")";
        parent::setWhere($classWhere);

        if ( !is_null($controlNumbers) )
        {
            $controlNumbers = is_array($controlNumbers) ? $controlNumbers : array( $controlNumbers );
            $controlNumbers = implode(",", $controlNumbers);
            parent::setWhere("controlNumber IN ($controlNumbers)");
        }

        $sql = parent::select();

        return parent::query($sql, false);
    }

    /**
     * retorna o conteudo da obra pelo numero do item
     *
     * @param int $itemNumber
     * @param marc tag char(5) $tag
     * @param int $line
     * @return text
     */
    public function getContentByItemNumber($itemNumber, $tag, $line = null)
    {
        $businessExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');

        if ( $cNumber = $businessExemplaryControl->getControlNumber($itemNumber) )
        {
            return $this->getContentTag($cNumber, $tag, $line);
        }

        return false;
    }

    public function getNextWorkNumber()
    {
        $gBussines = new GBusiness();
        
        $result = $gBussines->query("SELECT nextval('seq_nextworknumber');");
        
        return $result[0][0];
    }

    public function getControlNumberByWorkNumber($workNumber)
    {
        parent::clear();
        parent::setTables('gtcMaterial');
        parent::setColumns("controlNumber");
        parent::setWhere("fieldid = ? AND subfieldid = ? AND content = ?");

        $args = explode(".", MARC_WORK_NUMBER_TAG);
        $args[] = $workNumber;

        parent::select($args);
        $r = parent::query();

        if ( !$r )
        {
            return null;
        }

        return ($r[0][0]);
    }

    /**
     * //TODO remove
     */
    public static function prepareSearchContent($tag, $content, $complement = null)
    {
        $gBussines = new GBusiness();

        $tag = addslashes($tag);
        $content = addslashes($content);

        $complement = $complement ? "'" . addslashes($complement) . "'" : 'null';
        
        $result = $gBussines->query("select prepareSearchContent ( '{$tag}','{$content}', {$complement} ) ;");
        return $result[0][0];
    }
    
    
    /**
     * //TODO remove
     */
    public static function prepareSearchContentForSearchModule($tag, $content, $complement = null)
    {
        $gBussines = new GBusiness();

        $tag = addslashes($tag);
        $content = addslashes($content);

        $complement = $complement ? "'" . addslashes($complement) . "'" : 'null';
        
        $result = $gBussines->query("select preparesearchcontentforsearchmodule ( '{$tag}','{$content}', {$complement} ) ;");
        return $result[0][0];
    }
    

    /**
     * TODO remove
     */
    public static function prepareTopographicIndex($content, $complement = null)
    {
        //Adiciona o "|" para definir o fim do conteúdo que irá ser tratado
        $content = trim($content);// . "|";

        //Remove o texto inicial
        $ehNumero = false;

        for ( $i = 0; $i < strlen($content); $i++ )
        {
            if ( is_numeric($content[$i]) )
            {
                $ehNumero = true;
            }
            if ( $ehNumero )
            {
                $content3 .= $content[$i];
            }
        }

        $content3 = substr($content3, 0, 3);

        $content = eregi_replace("[AÁÀÂÃÄaáàâãä]", "a", $content);
        $content = eregi_replace("[Bb]", "b", $content);
        $content = eregi_replace("[Cc]", "c", $content);
        $content = eregi_replace("[Dd]", "d", $content);
        $content = eregi_replace("[EÉÈÊËeéèêë]", "e", $content);
        $content = eregi_replace("[Ff]", "f", $content);
        $content = eregi_replace("[Gg]", "g", $content);
        $content = eregi_replace("[Hh]", "h", $content);
        $content = eregi_replace("[IÍÌÎÏiíìîï]", "i", $content);
        $content = eregi_replace("[Jj]", "j", $content);
        $content = eregi_replace("[Kk]", "k", $content);
        $content = eregi_replace("[Ll]", "l", $content);
        $content = eregi_replace("[Mm]", "m", $content);
        $content = eregi_replace("[Nn]", "n", $content);
        $content = eregi_replace("[OÓÒÔÕÖoóòôõö]", "o", $content);
        $content = eregi_replace("[Pp]", "p", $content);
        $content = eregi_replace("[Qq]", "q", $content);
        $content = eregi_replace("[Rr]", "r", $content);
        $content = eregi_replace("[Ss]", "s", $content);
        $content = eregi_replace("[Tt]", "t", $content);
        $content = eregi_replace("[UÚÙÛÜuúùûü]", "u", $content);
        $content = eregi_replace("[Vv]", "v", $content);
        $content = eregi_replace("[Ww]", "w", $content);
        $content = eregi_replace("[Xx]", "x", $content);
        $content = eregi_replace("[Yy]", "y", $content);
        $content = eregi_replace("[Zz]", "z", $content);
        $content = eregi_replace("[Ññ]", "n", $content);
        $content = eregi_replace("[Çç]", "c", $content);
        $content = eregi_replace("[+]", "A", $content);
        $content = eregi_replace("[/]", "B", $content);
        $content = eregi_replace("[|]", "C", $content);
        $content = eregi_replace("[:]", "D", $content);
        $content = eregi_replace("[=]", "E", $content);
        $content = eregi_replace("[(]", "F", $content);
        $content = eregi_replace("[\"]", "G", $content);
        $content = eregi_replace("[-]", "H", $content);
        $content = eregi_replace("[.]", "I", $content);

        $content = eregi_replace("[0]", "J", $content);
        $content = eregi_replace("[1]", "K", $content);
        $content = eregi_replace("[2]", "L", $content);
        $content = eregi_replace("[3]", "M", $content);
        $content = eregi_replace("[4]", "N", $content);
        $content = eregi_replace("[5]", "O", $content);
        $content = eregi_replace("[6]", "P", $content);
        $content = eregi_replace("[7]", "Q", $content);
        $content = eregi_replace("[8]", "R", $content);
        $content = eregi_replace("[9]", "S", $content);

        //tratamento da exceção (0 => EI deve vir após (1/9 => EJ/9
        $content = str_replace('EI', 'ES', $content);

        //Trata a exceção quando o termo >= 820 e < 900 o (1/9 => E[JKLMNOPQR] vai depois do . => H (I))
        if ( $content3 >= 820 && $content3 < 900 )
        {
            $content = eregi_replace('E([JKLMNOPQR])', 'I\\1', $content);
        }

        //Prioriza os caracteres
        $content = ereg_replace("([a-z])", "F\\1", $content);

        if ( $complement )
        {
            $content .= '@' . $complement;
        }

        return $content;
    }

    /**
     * retorna a categoria da planilha de um determinado numero de controle
     *
     * @param integer $controlNumber
     * @return char(2)
     */
    public function getSpreadsheetCategory($controlNumber)
    {
        $this->clean();
        list($f, $s) = explode(".", MARC_LEADER_TAG);
        $content = $this->getContent($controlNumber, $f, $s);

        $type = substr($content, (str_replace("000-", "", LEADER_TAG_MATERIAL_TYPE) - 1), 1);
        $level = substr($content, (str_replace("000-", "", LEADER_TAG_BIBLIOGRAPY_LEVEL) - 1), 1);
        $levelx = substr($content, (str_replace("000-", "", LEADER_TAG_ENCODING_LEVEL) - 1), 1);

        $this->businessCataloge = $this->MIOLO->getBusiness($this->module, 'BusCataloge');
        return $this->businessCataloge->getSpreadsheetCategory($type, $level);
    }

    /**
     */
    public function getTagSuffix($tag, $controlNumber)
    {
        list($f, $s) = explode(".", $tag);

        $sql = "SELECT  A.suffixid, A.content, B.content ";
        $sql.= "FROM {$this->tables} A LEFT JOIN gtcPrefixSuffix B USING (fieldid, subfieldid) ";
        $sql.= "WHERE A.fieldid = '{$f}' AND A.subfieldid = '{$s}' AND A.controlNumber = '{$controlNumber}' AND A.suffixid = B.prefixsuffixid";

        return $this->query($sql);
    }

    /**
     */
    public function getTagPrefix($tag, $controlNumber)
    {
        list($f, $s) = explode(".", $tag);

        $sql = "SELECT  A.prefixId, A.content, B.content ";
        $sql.= "FROM {$this->tables} A LEFT JOIN gtcPrefixSuffix B USING (fieldid, subfieldid) ";
        $sql.= "WHERE A.fieldid = '{$f}' AND A.subfieldid = '{$s}' AND A.controlNumber = '{$controlNumber}' AND A.prefixId = B.prefixsuffixid";

        return $this->query($sql);
    }

    /**
     */
    public function getMaterialTagPrefixSuffix($controlNumber, $tag, $type = 1)
    {
        $type = ($type == 1) ? "A.prefixid" : "A.suffixid";
        list($fieldId, $subfieldId) = explode(".", $tag);

        $sql = "SELECT B.content ";
        $sql.= "FROM {$this->tables} A LEFT JOIN gtcPrefixSuffix B ON ($type = B.prefixsuffixid) ";
        $sql.= "WHERE A.controlNumber = '{$controlNumber}' AND A.fieldId = '$fieldId' AND A.subFieldId = '$subfieldId'";

        $result = $this->query($sql);
        return isset($result[0][0]) ? $result[0][0] : false;
    }

    public function getTagSeparator($tag, $controlNumber)
    {
        if ( $tag && $controlNumber )
        {
            $businessSeparator = $this->MIOLO->getBusiness($this->module, 'BusSeparator');

            list($f, $s) = explode(".", $tag);

            $sql = "SELECT  A.separatorId, A.content, B.content ";
            $sql.= "FROM {$this->tables} A LEFT JOIN {$businessSeparator->tables} B USING (fieldid, subfieldid) ";
            $sql.= "WHERE A.fieldid = '{$f}' AND A.subfieldid = '{$s}' AND A.controlNumber = '{$controlNumber}' AND A.separatorId = B.separatorId";

            $result = $this->query($sql);
        }

        return $result;
    }

    /**
     * *
     *
     * @param unknown_type $marcTag
     * @param unknown_type $value
     * @return unknown
     */
    public function relationOfFieldsWithTable($marcTag, $value = null, $returnConcatendOption = true)
    {
        $options = $this->optionsTable($marcTag, $value);

        if ( $options )
        {
            return $options;
        }

        // VERIFICA SE TEM NA MARC TAG LISTING
        if ( isset($this->possibleMarcTagListing[$marcTag]) && $this->possibleMarcTagListing[$marcTag] == false )
        {
            return $value;
        }

        $business = $this->MIOLO->getBusiness($this->module, "BusMarcTagListing");

        if ( !$business->getMarcTagListing($marcTag) )
        {
            $this->possibleMarcTagListing[$marcTag] = false;
            return $value;
        }
        else
        {
            $this->possibleMarcTagListing[$marcTag] = true;
        }

        //TODO otimizar guardando em array
        $business = $this->MIOLO->getBusiness($this->module, "BusMarcTagListingOption");
        if ( !$option = $business->getMarcTagListingOption($marcTag, $value) )
        {
            return $value;
        }

        if ( $returnConcatendOption )
        {
            return "{$option->option} - {$option->description}";
        }
        else
        {
            return $option->description;
        }

        return false;
    }

    /**
     * *
     *
     * @param unknown_type $marcTag
     * @param unknown_type $value
     * @return unknown
     */
    public function optionsTable($marcTag, $value = null)
    {
        $constant = str_replace(array( " ", "\n", "\r" ), "", RELATIONSHIP_OF_FIELDS_WITH_TABLES_FOR_SELECTS);
        $relacoes = explode(";", $constant);

        $options = null;

        foreach ( $relacoes as $linhas )
        {
            list($marcConstants, $dbReference) = explode("=", $linhas);
            $marcConstants = explode(",", $marcConstants);

            foreach ( $marcConstants as $constant )
            {
                if ( $constant )
                {
                    eval("\$tags[] = $constant;");
                }
            }
        }

        if ( in_array($marcTag, $tags) )
        {
            foreach ( $relacoes as $linhas )
            {
                list($marcConstants, $dbReference) = explode("=", $linhas);
                $marcConstants = explode(",", $marcConstants);

                foreach ( $marcConstants as $constant )
                {
                    if ( !strlen($constant) || !strlen($dbReference) )
                    {
                        continue;
                    }

                    eval("\$ok = (\$marcTag == $constant);");

                    if ( !$ok )
                    {
                        continue;
                    }

                    $businessName = "Bus{$dbReference}";
                    $methodName = "list{$dbReference}";

                    $business = $this->MIOLO->getBusiness($this->module, $businessName);

                    if ( $ok && is_object($business) && method_exists($business, $methodName) )
                    {
                        $business->filterOperator = FALSE;
                        $filterOperator = MUtil::getBooleanValue(CATALOGUE_FILTER_OPERATOR);

                        //caso for unidade de biblioteca precisa passar o parametro extra
                        if ( strtolower($dbReference) == 'libraryunit' )
                        {
                            $options = $business->$methodName(true, $filterOperator);
                        }
                        else
                        {
                            $options = $business->$methodName(true);
                        }

                        if ( $options && $value )
                        {
                            foreach ( $options as $cont )
                            {
                                if ( $cont->option == $value )
                                {
                                    if ( $returnConcatendOption )
                                    {
                                        return $value . " - " . $cont->description;
                                    }
                                    else
                                    {
                                        return $cont->description;
                                    }
                                }
                            }

                            return $value;
                        }

                        return $options;
                    }
                }
            }
        }

        return false;
    }

    function synchronizeFatherAndSon($controlNumber)
    {
        $businessMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');

        if ( !$father = $businessMaterialControl->getControlNumberFather($controlNumber) )
        {
            return;
        }

        $fatherCategory = $businessMaterialControl->getCategory($father);
        $fatherLevel = $businessMaterialControl->getLevel($father);

        $category = $businessMaterialControl->getCategory($controlNumber);
        $level = $businessMaterialControl->getLevel($controlNumber);

        $businessLinkOfFields = $this->MIOLO->getBusiness($this->module, 'BusLinkOfFieldsBetweenSpreadsheets');

        $businessLinkOfFields->clean();
        $businessLinkOfFields->category = $fatherCategory;
        $businessLinkOfFields->level = $fatherLevel;
        $businessLinkOfFields->tag = null;
        $businessLinkOfFields->categorySon = $category;
        $businessLinkOfFields->levelSon = $level;
        $businessLinkOfFields->tagSon = null;
        $businessLinkOfFields->type = 2;

        $link = $businessLinkOfFields->searchLinkOfFieldsBetweenSpreadsheets(true);

        if ( !$link )
        {
            return;
        }

        foreach ( $link as $object )
        {
            $tags = explode(",", $object->tag);

            foreach ( $tags as $tag )
            {
                list($field, $subField) = explode(".", $tag);
                $tagsSon = explode(",", $object->tagSon);

                foreach ( $tagsSon as $tagSon )
                {
                    list($fieldSon, $subFieldSon) = explode(".", $tagSon);

                    $this->clean();
                    $this->controlNumber = $controlNumber;
                    $this->fieldid = $fieldSon;
                    $this->subfieldid = $subFieldSon;
                    $this->deleteMaterial();

                    $line = 0;

                    while ( 1 )
                    {
                        $content = $this->getContent($father, $field, $subField, $line);

                        if ( $content )
                        {
                            $this->clean();
                            $this->controlNumber = $controlNumber;
                            $this->fieldid = $fieldSon;
                            $this->subfieldid = $subFieldSon;
                            $this->content = $content;
                            $this->line = $line;
                            $this->insertMaterial();

                            $line++;
                        }
                        else
                        {
                            break;
                        }
                    }
                }
            }
        }
    }

    public function verifyLinks($tag = '856.a', $timeOut = 60)
    {
        $iniTimeOut = ini_get('default_socket_timeout');//pega a configuração de timeOut do sistema
        ini_set('default_socket_timeout', $timeOut); //redefine com a personalizada

        $tag = explode('.', $tag);

        if ( !$tag[0] || !$tag[1] )
        {
            return false;
        }

        $sql = "SELECT controlNumber, content FROM gtcmaterial WHERE fieldId = '{$tag[0]}' and subfieldId = '{$tag[1]}'";
        $data = $this->query($sql);

        $testou = 0;

        //transforma num array simples para poder fazer o array unique
        if ( is_array($data) )
        {
            foreach ( $data as $line => $info )
            {
                $controlNumber = $info[0];
                $url = $info[1];

                //verifica se a url ja esta no array de certos ou errados para não testar novamente
                if ( in_array($url, $success) )
                {
                    //sucesso
                }
                else
                if ( in_array($url, $errors) )
                {
                    $fErrors[] = array( $controlNumber, $url );
                }
                else
                {
                    $handle = fopen($url, "r");
                    $testou++;

                    if ( !$handle )
                    {
                        $errors[$controlNumber] = $url;
                        $fErrors[] = array( $controlNumber, $url );
                    }
                    else
                    {
                        $success[$controlNumber] = $url;
                    }

                    fclose($handle);
                }
            }
        }

        ini_set('default_socket_timeout', $iniTimeOut);

        return $fErrors;
    }

    /**
     * Busca vários controls numbers, usando uma tag e alguns conteúdos.
     *
     * Usado por exemplo, para retornar o número de controle de um material que tenha um ISBN específico.
     *
     * Returna um array de controls numbers, alinhado com os conteúdos pesquisas.
     *
     * @param <string> $tag exemplo MARC_ISBN_TAG
     * @param <array> $contentArray exemplo um array linear com os IBSN's
     * @return <array> um array de controls numbers, alinhado com os conteúdos pesquisas.
     *
     */
    public function getControlNumberByContent($tag, $contentArray)
    {
        $tag = explode('.', $tag);
        $fieldId = $tag[0];
        $subFieldId = $tag[1];
        $content = implode("','", $contentArray);

        if ( $content )
        {
            $sql = "select controlNumber,content from gtcMaterial where fieldid ='{$fieldId}' and subfieldid='{$subFieldId}' and content in ('{$content}');";
        }

        $result = $this->query($sql);

        if ( is_array($result) )
        {
            foreach ( $result as $line => $info )
            {
                $return[$info[1]] = $info[0];
            }
        }

        return $return;
    }

    /**
     * Busca vários controls numbers, usando o ISBN
     *
     * Returna um array de controls numbers, alinhado com os conteúdos pesquisas.
     *
     * @param string $tag exemplo MARC_ISBN_TAG
     * @param array $contentArray exemplo um array linear com os IBSN's
     * @param array $controlNumberNotIn número de controle a excluir
     * @return array um array de controls numbers, alinhado com os conteúdos pesquisas.
     *
     */
    public function getControlNumberByISBN($contentArray = null, $controlNumberNotIn = null)
    {
        $tag = explode('.', MARC_ISBN_TAG);
        $fieldId = $tag[0];
        $subFieldId = $tag[1];

        if ( $contentArray )
        {
            $content = implode("','", $contentArray);
        }

        $sql = "select controlNumber,content from gtcMaterial where fieldid ='{$fieldId}' and subfieldid='{$subFieldId}' and content != '' ";

        if ( $content )
        {
            $sql .= " and translate(content, '.-', '') in ('{$content}')";
        }

        if ( $controlNumberNotIn )
        {
            $controlNumberNotIn = implode(",", $controlNumberNotIn);
            $sql .= " and controlNumber not in ( $controlNumberNotIn ) ";
        }

        $result = $this->query($sql);

        if ( is_array($result) )
        {
            foreach ( $result as $line => $info )
            {
                $return[$info[1]] = $info[0];
            }
        }

        return $return;
    }

    public static function converteConteudoImportacao($field, $subfield, $conteudo)
    {
        switch ( $field . '.' . $subfield )
        {
            case '000.a':
            case '008.a':
                $conteudo = str_replace(' ', '#', $conteudo);
                break;
        }

        return $conteudo;
    }
    
    /**
     * Método responsável por buscar material por número de controle com possíbilidade de ignorar tags.
     * 
     * @param int $controlNumber Número de controle do material desejado.
     * @param array $ignoreTags Parâmetro opcional contendo tags que podem ser ignoras.
     *                           São aceitos os seguintes valores: 949.d, 949, 94, 9 (não precisa ter a tag inteira).
     * @return array Vetor com objetos GMaterialItem para cada tag encontrada.
     */
    public function searchMaterialOfControlNumber($controlNumber, $ignoreTags=NULL)
    {
        $this->clear();
        $params = array();
        parent::setTables("gtcmaterial A LEFT JOIN gtcprefixsuffix B ON (B.prefixsuffixid = A.prefixid) LEFT JOIN gtcprefixsuffix C ON (C.prefixsuffixid = A.suffixid)");
        parent::setColumns('A.controlnumber, A.fieldid, A.subfieldid, A.line, A.indicator1, A.indicator2, A.content, B.content as prefix, C.content as suffix');
        $this->setWhere('A.controlnumber = ?');
        $this->setOrderBy('A.fieldid, A.subfieldid, A.line');
       
        $params[] = $controlNumber;
        
        if ( is_array($ignoreTags) )
        {
            foreach ( $ignoreTags as $key => $tag )
            {
                $this->setWhere("A.fieldid || '.' || A.subfieldid NOT LIKE ?");
                $params[] = $tag . '%';
            }
        }
        
        $sql = $this->select($params);
        
        $result = $this->query($sql, TRUE);
        
        $return = array();
        if ( is_array($result) )
        {
            foreach ( $result as $value )
            {
                $return[] = GMaterialItem::fromStdClass($value);
            }
        }
        
        return $return;
    }
    
    /**
     * Função criada para rodar comando copy para o acervo das bibliotecas virtuais
     * 
     * 
     */
    public function sincronyzeMaterials($folderName, $zipName)
    {
        $zip = new ZipArchive();
        
        if( $zip->open( $folderName . $zipName . '.zip' )  === true)
        {
            $zip->extractTo($folderName);
            $zip->close();
        }
        
        chmod($folderName . 'gtcLibraryUnit.csv', 0777);
        chmod($folderName . 'gtcMaterial.csv', 0777);
        chmod($folderName . 'gtcMaterialControl.csv', 0777);
        chmod($folderName . 'gtcExemplaryControl.csv', 0777);
        

        //Cria tabela temporária para a gtcMaterial
        $this->execute("CREATE TEMP TABLE tmpgtclibraryunit (libraryunitid integer, libraryname character varying(100), isrestricted boolean, city character varying(50), 
                                                            zipcode character varying(9), location character varying(100), number character varying(10), complement character varying(60), 
                                                            email character varying(60), url character varying(60), librarygroupid integer, privilegegroupid integer, observation text, 
                                                            level integer, acceptpurchaserequest boolean);");
        //Cria tabela temporária para a gtcMaterial
        $this->execute("CREATE TEMP TABLE tmpgtcmaterial(controlnumber integer, fieldid varchar(3), subfieldid varchar(1), line integer, indicator1 varchar(1), indicator2 varchar(1), 
                                                         content text, searchcontent text, prefixid integer, suffixid integer, separatorid integer, searchcontentforsearchmodule text);");
        
        //Cria tabela temporária para a gtcMaterialControl
        $this->execute("CREATE TEMP TABLE tmpgtcmaterialcontrol (controlnumber integer, controlnumberfather integer, entrancedate date, lastchangedate date, category character varying(2), 
                                                                 level character varying(1), materialgenderid integer, materialtypeid integer, materialphysicaltypeid integer, lastchangeoperator character varying(255));");
        
        //Cria tabela temporária para a gtcExemplaryControl
        $this->execute("CREATE TEMP TABLE tmpgtcexemplarycontrol (controlnumber integer, itemnumber character varying(20), originallibraryunitid integer, libraryunitid integer, acquisitiontype character varying(1), 
                                                                  exemplarystatusid integer, materialgenderid integer, materialtypeid integer, materialphysicaltypeid integer, 
                                                                  entrancedate date, lowdate date, line integer, observation text);");
        //Insere na gtcLibraryUnit
        $this->execute("COPY tmpgtclibraryunit FROM '{$folderName}gtcLibraryUnit.csv' DELIMITERS '|' CSV");
        //Insere na gtcMaterial
        $this->execute("COPY tmpgtcmaterial FROM '{$folderName}gtcMaterial.csv' DELIMITERS '|' CSV");
        //Insere na gtcMaterialControl
        $this->execute("COPY tmpgtcmaterialcontrol FROM '{$folderName}gtcMaterialControl.csv' DELIMITERS '|' CSV");
        //Insere na gtcExemplaryControl
        $this->execute("COPY tmpgtcexemplarycontrol FROM '{$folderName}gtcExemplaryControl.csv' DELIMITERS '|' CSV");
        
        //Atualiza os dados como controlnumber, libraryunit, itemnumber
        $this->query("SELECT atualizaDadosBibliotecaVirtual('{$zipName}')");
        
        $ok[] = $this->execute("INSERT INTO gtclibraryunit (libraryunitid, libraryname, isrestricted, city, zipcode, location, number,
                                                            complement, email, url, librarygroupid, privilegegroupid, observation, level, 
                                                            acceptpurchaserequest) 
                                        SELECT * FROM tmpgtclibraryunit;");
        
        $ok[] = $this->execute("INSERT INTO gtcmaterial (controlnumber, fieldid, subfieldid, line, indicator1, indicator2, content,
                                                 searchcontent, prefixid, suffixid, separatorid, searchcontentforsearchmodule)
                                        SELECT * from tmpgtcmaterial;");
        
        $ok[] = $this->execute("INSERT INTO gtcmaterialcontrol (controlnumber, controlnumberfather, entrancedate, lastchangedate, category,
                                                        level, materialgenderid, materialtypeid, materialphysicaltypeid, lastchangeoperator) 
                                        SELECT * FROM tmpgtcmaterialcontrol;");
        
        $ok[] = $this->execute("INSERT INTO gtcexemplarycontrol (controlnumber, itemnumber, originallibraryunitid, libraryunitid,acquisitiontype, 
                                                         exemplarystatusid, materialgenderid, materialtypeid, materialphysicaltypeid, 
                                                         entrancedate, lowdate, line, observation) 
                                        SELECT * FROM tmpgtcexemplarycontrol;");
        
        $this->query("SELECT gtcfnc_updatesearchmaterialviewtablebool();");
        
        //Atualiza a sequência de número de controle
        $this->query("SELECT setval('seq_controlnumber', (SELECT max(controlnumber) from gtcmaterialcontrol));");
        
        if(count($ok) != 4)
        {
            throw new Exception('Ocorreu um problema durante a importação dos arquivos para a base da biblioteca integradora!');
        }
        else
        {
            return true;
        }
    }
}
?>
