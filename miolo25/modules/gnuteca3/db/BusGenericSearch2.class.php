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
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 25/11/2008
 *
 **/
class BusinessGnuteca3BusGenericSearch2 extends GBusiness
{
    /**
     * Vetor de campos a serem pesquisados na tabela de materiais
     *
     * @var array
     */
    protected $attributesMaterial = null;
    
    /**
     * Vetor de filtros a serem utilizados na materialcontrol
     * @var array
     */
    protected $attributesMaterialControl = null;
    
    /**
     * Vetor de filtros a serem utilizados na exemplarycontrol
     * @var array
     */
    protected $attributesExemplaryControl = null;

    /**
     * Filtros extras, no caso categoria e nível, bem como tipo de materal
     *
     * Pode ser adicionado na tabela de materiais ou exemplares
     *
     * @var array
     */
    protected $extraAttributes = null;

    /**
     * Vetor de campos a serem pesquisados
     * 
     * @var array
     */
    protected $fields = null;

    /**
     * Vetor de campos para ordenação
     * 
     * @var array
     */
    protected $order = null;

    /**
     * Se é para usar termo exato
     *
     * @var boolean
     */
    protected $accurateTerm = false;

    /**
     * Se é para usar prefix e sufixo no resultado
     * @var boolean
     */
    protected $addPrefixSuffixInresult = true;

    public $busMaterial = null;
    
    /**
     * Contador de resultados
     * 
     * @var int 
     */
    protected $count;

    /**
     * Contantes Locais que definem os nome das tabelas
     */
    const TABLE_EXEMPLARY_CONTROL = "gtcSearchMaterialView";
    const TABLE_MATERIAL_CONTROL = "gtcSearchMaterialView";
    const TABLE_MATERIAL = "gtcMaterial";
    const TABLE_PREFIX_SUFFIX = "gtcPrefixSuffix";

    function __construct()
    {
        parent::__construct();

        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->addPrefixSuffixInresult = MUtil::getBooleanValue( MATERIAL_SEARCH_USE_PREFIX_SUFFIX );
    }

    public function clean()
    {
        $this->controlNumbers = array();
        $this->attributesMaterial = null;
        $this->attributesMaterialControl = null;
        $this->attributesExemplaryControl = null;
        $this->extraAttributes = null;
        $this->fields = null;
        $this->order = null;
    }
    
    /**
     * Retorna contagem de registros
     * 
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Adiciona um campo de filtro na pesquisa
     *
     * @example ->addSearchField( '100', 'a' );
     * @param string $field char(3)
     * @param string $subfield char(1)
     */
    public function addSearchField( $field, $subfield )
    {
        $obj = new stdClass();
        $obj->field = $field;
        $obj->subfield = $subfield;
        $obj->tag = $field.'.'.$subfield; //FIXME é ruim pois pode ficar desincronizado

        $this->fields[$obj->tag] = $obj;
    }

    /**
     * Adiciona um filtro a pesquisa, utilizando a etiqueta.
     *
     * @example ->addSearchTagField( '100.a' );
     * @param string $tag
     */
    public function addSearchTagField( $tag )
    {
        if ( stripos( $tag, '.') == false )
        {
            throw new Exception( _M('Formato de etiqueta inválido na adição de filtro de pesquisa. Precisa ser no formato 999.x','gnuteca') );
        }

        list($field, $subfield) = explode(".", $tag);

        $this->addSearchField( $field, $subfield );
    }

    /**
     * Adiciona uma condiçao para a tabela gtcMaterial;
     *
     * @example addMaterialWhere( '100', 'a', $arrayValue, 'AND', 'LIKE );
     * @param array char(3) $field campo
     * @param array char(1) $subfield subcampo
     * @param array varchar $arrayValue valor
     * @param string $operator operador AND, OR)
     * @param $sring $condition char( LIKE, = )
     *
     * TODO fazer função para listar operadores possíveis e condições possíveis, e verificar se esta correto
     * TODO debugar exatamente os parametros que vem
     *
     */
    public function addMaterialWhere( $field, $subfield, $arrayValue, $operation = "AND", $condition = "LIKE" )
    {
        //garante integridade da pesquisa, define operador e condição padrão
        if ( ! trim( $operation ) )
        {
            $operation = 'AND';
        }
        if ( ! trim( $condition ) )
        {
            $condition = 'LIKE';
        }

        $obj = new stdClass();
        $obj->field     = is_array($field) ? $field : array($field);
        $obj->subfield  = is_array($subfield) ? $subfield : array($subfield);
        $obj->values    = is_array($arrayValue) ? $arrayValue : array($arrayValue);
        $obj->operator  = $operation;
        $obj->condition = $condition;

        $this->attributesMaterial[] = $obj;
    }

    /**
     * Adiciona uma condiçao para a tabela gtcMaterial, utilizando uma etiqueta
     *
     * @example addMaterialWhere( '100', 'a', $arrayValue, 'AND', 'LIKE );
     * @param array char(3) $field campo
     * @param array char(1) $subfield subcampo
     * @param array varchar $arrayValue valor
     * @param string $operator operador AND, OR)
     * @param sring $condition char( LIKE, = )
     *
     * TODO debugar o que passa aqui
     *
     */
    public function addMaterialWhereByTag($tag, $arrayValue, $operation = "AND", $condition = "LIKE")
    {
        if ( $this->accurateTerm )
        {
            $condition = "=";
        }
        
        if( (!strlen($tag) && !is_array($tag)) || ($tag == 'ALL') )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');;
            
            // Procura em todos os campos
            if ( $tag == 'ALL' )
            {
                $fieldsList = $busSearchFormat->getVariablesFromSearchFormat($args->searchFormat);
                if ( is_array($fieldsList) )
                {
                    foreach ( $fieldsList as $line => $info )
                    {
                        $tag = str_replace('$', '', $info);
                        $tag = explode('.', $tag);
                        $this->addMaterialWhere($tag[0], $tag[1], $arrayValue, 'OR', $condition);
                    }
                    
                    return;
                }
            }
            
            $this->addMaterialWhere("", "", $arrayValue, $operation, $condition);
            return;
        }

        //explode por expressão regular
        $tag = preg_split('/[,+]/', $tag);
        //converte para array
        $arrayTag = is_array($tag) ? $tag : array($tag);

        $fields = array();
        $subFields = array();

        //monta um array com campos e subcamos
        foreach ($arrayTag as $tag)
        {
            //TODO informar formato errado
            if (!ereg( ".", $tag ) )
            {
                continue;
            }

            list($field, $subfield) = explode(".", $tag);

            $fields[] = $field;
            $subFields[] = $subfield;
        }

        $this->addMaterialWhere( $fields, $subFields, $arrayValue, $operation, $condition);
    }

    /**
     * Adiciona uma condiçao para a tabela Material Control
     *
     * @param array $fieldName campo
     * @param array $arrayValue valor
     * @param string $operation operação
     * @param string $condition condição
     */
    public function addMaterialControlWhere($fieldName, $arrayValue, $operation = "AND", $condition = "=")
    {
        $obj = new stdClass();
        $obj->fieldName = is_array($fieldName)  ? $fieldName  : array($fieldName);
        $obj->values    = is_array($arrayValue) ? $arrayValue : array($arrayValue);
        $obj->operator  = $operation;
        $obj->condition = $condition;

        //FIXME me parece errado, não deveria ser
        $this->attributesExemplaryControl[] = $obj;
    }

    /**
     * Adiciona uma condiçao para a tabela Exemplary Control
     *
     * @param array $fieldName campos
     * @param array $arrayValue valores
     * @param string $operation operação
     * @param string $condition condição
     */
    public function addExemplaryControlWhere($fieldName, $arrayValue, $operation = 'AND', $condition = '=')
    {
    	$obj = new StdClass();
        $obj->fieldName = is_array($fieldName) ? $fieldName : array($fieldName);
        $obj->values    = is_array($arrayValue) ? $arrayValue : array($arrayValue);
        $obj->operator  = $operation;
        $obj->condition = $condition;

        // concatena exemplary na frente do campo, para funcionar na wiew
        foreach ($obj->fieldName as $index => $fieldName)
        {
            $obj->fieldName[$index] = "exemplary{$fieldName}";
        }

        $this->attributesExemplaryControl[] = $obj;
    }

    /**
     * Adiciona número de controle na listagem.
     * A pesquisa retorna o conteudo os numeros de controle existentes nesta lista.
     *
     *
     * @param integer $controlNumber
     */
    public function addControlNumber($controlNumber)
    {
        $this->addMaterialControlWhere('controlNumber', $controlNumber);
    }

    /**
     * Adicionr condições de pesquisa na tabela de materiais por um expressão
     *
     * TODO Avaliar, não está suportando todos operadores
     *
     * @param String $expression
     */
    public function addMaterialWhereByExpression( $expression, $all = FALSE )
    {
    	//tira os espaços sobresalentes no inicio e no fim da expressão.
    	$expression = trim( $expression ); //o trim é importante aqui na classe, pois pode evitar um monte de erros na pesquisa.
        // Filtra as tags e conteúdos - "([0-9]{3}\.[0-9a-zA-Z],?)*:"
        $searchWere = preg_split('/(([0-9]{3}\.[0-9a-zA-Z][,+]?\|?)*:| AND | OR | NOT )/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE);

        $specialCondition = null;
        $specialTagSeparators = " .-";

        for ($x=0; $x<count($searchWere); $x++ )
        {
        	 $sW = $searchWere[$x];

            //Verifica se é uma tag
            if (ereg('([0-9]{3}\.[0-9a-zA-Z],?\|?)*:', $sW))
            {
                $condition->tag = trim(trim($sW, ':'));
                $x += 1;
            }
            // VERIFICA SE A UMA REGRA ESPECIAL DE SEPARADORES ENTRE DUAS OU MAIS TAGS
            elseif(ereg("\([0-9A-Za-z.|$specialTagSeparators]{1,}\)", $sW)) // \(([0-9A-Za-z.]{5})(([|]{1}).*([|]{1}))?\)
            {
                $condition->tag = trim(trim($sW, ':'));
                $condition->tag = preg_replace("/,?\([0-9A-Za-z.|$specialTagSeparators]{1,}\),?/", '', $sW);
                $specialCondition->tags = str_replace(array($condition->tag, ','), '', $sW);
                $x += 1;
            }
            //Verifica se é um operador lógico
            elseif (ereg(' AND | OR | NOT ', $sW))
            {
                $condition->operator = trim($sW);
            }
            else
            {
                if ($sW)
                {
                    $condition->content = trim($sW);

                    if($specialCondition->tags)
                    {
                        $specialCondition->content = $condition->content;
                    }

                    if (!$condition->operator)
                    {
                        $condition->operator = 'AND';
                    }

                    if ( $all && strlen($condition->tag) == 0 )
                    {
                        $condition->tag = 'ALL';
                    }
                    
                    $this->addMaterialWhereByTag($condition->tag, $condition->content, $condition->operator);
                    unset($condition);
                }
            }
        }

        if($specialCondition)
        {
            $tags = preg_match_all('/[0-9A-Za-z.]{5}/', $specialCondition->tags, $tagMatch);
            $sepa = preg_match_all("/\|[$specialTagSeparators]{1}\|/", $specialCondition->tags, $sepMatch);

            $separadores = null;
            
            foreach ($sepMatch[0] as $content)
            {
                $separadores[] = str_replace('|', '', $content);
            }

            $sep = implode(null, $separadores);
            $conteudos = preg_split("/[$sep]/", $specialCondition->content);
            $tag = $tagMatch[0];

            foreach($conteudos as $index => $value)
            {
                $this->addMaterialWhereByTag($tag[$index], $value, ($index > 0 ? 'AND' : 'OR'));
            }
        }
    }

    /**
     * Adiciona uma ordenação para a pesquisa
     *
     * @param integer $field campo
     * @param integer  $subfield subcampo
     * @param string $type - SORT_ASC, SORT_DESC. conforme constant do php
     * @param string $fieldType - SORT_NUMERIC, SORT_STRING. conforme constant do php
     *
     * //FIXME deveria ser addOrder se suporta mais de uma ordenação
     */
    public function setOrder( $field, $subfield, $type = SORT_ASC, $fieldType = SORT_NUMERIC )
    {
        $obj = new stdClass();
        $obj->field = $field;
        $obj->subfield = $subfield;
        $obj->type = (int) $type;
        $obj->fieldType = (int) $fieldType;

        $this->order["{$field}.{$subfield}"] = $obj;
    }

    /**
     * Limpa a ordem
     * //TODO é realmente necesária essa função
     */
    public function cleanOrder()
    {
        $this->order = null;
    }

        /**
     * Define se a busca sera por um termo exato
     *
     * @param boolean $status
     */
    public function setAccurateTerm($status = true)
    {
        $this->accurateTerm = $status;
    }

    /**
     * Define concatenação de prefixos e suffixos com o resultado.
     *
     * @param boolean  $status
     */
    public function addPrefixSuffixInResult($status = true)
    {
        $this->addPrefixSuffixInresult = $status;
    }

    /**
     * Este metodo permite filtar a consulta por diversos tipos diferentes de material,
     * definindo a planilha e o level desejado.
     *
     * @param array Object $arrayObject
     * @param boolean $ignore; define se é para ignorar ou nao as categorias informadas.
     */
    public function setCategoryLevel($arrayObject, $ignore = false)
    {
        $arrayObject = is_array($arrayObject) ? $arrayObject : array($arrayObject);

        $sql = "";

        foreach($arrayObject as $obj)
        {
            $condition  = $ignore ? " != " : " = ";
            $category   = strlen($obj->category)    ? " ". self::TABLE_EXEMPLARY_CONTROL .".category {$condition} '{$obj->category}' " : "";
            $level      = strlen($obj->level)       ? " ". self::TABLE_EXEMPLARY_CONTROL .".level    {$condition} '{$obj->level}' "    : "";
            $union      = strlen($category) && strlen($level) ? " AND " : "";
            $operator   = $ignore ? " AND " : " OR ";

            $sql.= "( {$category} {$union} {$level} )  {$operator}";
        }

        $sql = substr($sql, 0, strlen($sql)-4);
        $sql = " AND ({$sql})";

        $this->extraAttributes[self::TABLE_MATERIAL_CONTROL]->categoryLevel = $sql;
    }

    /**
     * Este metodo permite definir um filtro pelo tipo de material.
     *
     * @param array integer $arrayMaterialTypeId
     * @param boolean $ignore - define se é para ignorar ou nao os ids do array
     */
    public function setMaterialTypeId($arrayMaterialTypeId, $ignore = false)
    {
        $arrayMaterialTypeId    = is_array($arrayMaterialTypeId) ? $arrayMaterialTypeId : array($arrayMaterialTypeId);
        $ids                    = implode(", ", $arrayMaterialTypeId);
        $operator               = $ignore ? " NOT IN " : " IN ";

        // filtro apenas por obra
        if(MATERIAL_TYPE_CONTROL == 1)
        {
            // SETA TIPO DE MATERIAL
            $this->addMaterialControlWhere("materialtypeid", $arrayMaterialTypeId, "AND", trim($operator));
            return;
        }
        //filtro por obra e exemplar
        elseif(MATERIAL_TYPE_CONTROL != 2)
        {
            return ;
        }

        //TODO pergunta? alguma vez chega aqui?

        $materialFilter  = $this->makeSqlToMaterialTypeConstant(MATERIAL_TYPE_FORCE_BY_MATERIAL, $ignore);
        $exemplaryFilter = $this->makeSqlToMaterialTypeConstant(MATERIAL_TYPE_FORCE_BY_EXEMPLARY, $ignore);

        $sqlFinal .= strlen($materialFilter)   ? "($materialFilter  AND ". self::TABLE_MATERIAL_CONTROL   .".materialtypeid            $operator ($ids) )    ". ($ignore ? " AND " : " OR ") : "";
        $sqlFinal .= strlen($exemplaryFilter)  ? "($exemplaryFilter AND ". self::TABLE_MATERIAL_CONTROL   .".exemplarymaterialtypeid   $operator ($ids))   ". ($ignore ? " AND " : " OR ") : "";
        $sqlFinal = substr($sqlFinal, 0, -4);

        $this->extraAttributes[self::TABLE_EXEMPLARY_CONTROL]->materialType = " AND ($sqlFinal)";
    }

    /**
     * Este metodo foi criado para transformar duas constantes em SQL para a função que filtra tipo de material.
     *
     * TODO descobrir qual a ideia disso, função precisa de mais documentação
     *
     * @param string $constant
     * @param boolean $ignore
     * @return string sql
     */
    private function makeSqlToMaterialTypeConstant($constant, $ignore = false)
    {
        $materialFilter     = explode("\n", $constant);
        $operator           = $ignore ? " NOT IN " : " IN ";

        if(!is_array($materialFilter) || !count($materialFilter))
        {
            return "";
        }

        $materialCategory       = " ". self::TABLE_MATERIAL_CONTROL   .".category   $operator (<--MATERIAL_CATEGORY_IN_LOCAL-->) ";
        $materialLevel          = " ". self::TABLE_MATERIAL_CONTROL   .".level      $operator (<--MATERIAL_LEVEL_IN_LOCAL-->) ";
        $categories             = null;
        $levels                 = "";

        foreach ($materialFilter as $v)
        {
            list($category, $level) = explode(",", $v);

            if(!strlen($level))
            {
                $categories[] = $category;
                continue;
            }

            $levels.= " ( ";
            $levels.= str_replace("<--MATERIAL_CATEGORY_IN_LOCAL-->", "'$category'", $materialCategory);
            $levels.= " AND ";
            $levelX = null;

            for($x = 0; $x < strlen($level); $x++)
            {
                $levelX[] = $level[$x];
            }

            $levels.= str_replace("<--MATERIAL_LEVEL_IN_LOCAL-->", "'". implode("','", $levelX) ."'", $materialLevel);
            $levels.= ")". ($ignore ? " AND " : " OR ");
        }

        $sqlMaterial = $categories ? str_replace("<--MATERIAL_CATEGORY_IN_LOCAL-->", "'". implode("','", $categories) ."'", $materialCategory) . ($ignore ? " AND " : " OR ") : "";
        $sqlMaterial = (strlen($levels)) ? $sqlMaterial . $levels : $sqlMaterial;
        $sqlMaterial = substr($sqlMaterial, 0, -4);
        $sqlMaterial = "($sqlMaterial)";

        return $sqlMaterial;
    }

    public function workingConditionMaterial($obj)
    {
        $conditionField = "";
        $conditionValues = "";

        // TRABALHA AS TAGS DE PESQUISA
        if(count($obj->subfield) && count($obj->field))
        {
            $fields = array();
            foreach ($obj->field as $index => $f)
            {
                if(!strlen($f) || !strlen($obj->subfield[$index]))
                {
                    $f = "*";
                    $obj->subfield[$index] = "*";
                }

                $obj->tags["{$f}.{$obj->subfield[$index]}"] = true;
            }
        }

        // PREPARA OS VALORES PARA SEREM PESQUISADOS
        $valores = array();

        //com base nas tags a pesquisar trata os valores montando um array indexado pelas tags
        foreach ($obj->tags as $tag => $f)
        {
            foreach ($obj->values as $v)
            {
                $complement = null;

                //caso seja classificação trata os dados separação o cutter
                if ( ereg ( $tag, MARC_CLASSIFICATION_TAG ) )
                {
                    $v = str_replace("  ", " ", $v);
                    list($v, $complement) = explode(" ", $v);
                }
                
                $str = $this->busMaterial->prepareSearchContentForSearchModule($tag, $v, $complement);
                
                $valores[$tag][] = str_replace("'","''", $str); //troca ' por '' para funcionar no postgres pesquisa com '
            }
        }
        
        $condition = "";

        //TRABALHA A CONDIÇÃO DE VALORES
        switch(strtoupper($obj->condition))
        {
            // TRABALHA WHERE CONDITION PARA START END
            case 'START':
            case 'END':

                foreach($valores as $tag => $conteudoS)
                {
                    list($field, $subField) = explode(".", $tag);
                    $condition.= " ( ";
                    
                    if("{$field}.{$subField}" != "*.*")
                    {
                        $condition.=  self::TABLE_MATERIAL  .".fieldid = '{$field}' AND  ". self::TABLE_MATERIAL  .".subfieldid = '{$subField}' AND ";
                    }
                    
                    $condition.= " ( ";

                    foreach ($conteudoS as $conteudo)
                    {
                        $conteudo = $obj->condition == 'START' ? "{$conteudo}%" : "%{$conteudo}";
                        //$conteudo = str_replace(' ', '%', $conteudo);

                        $condition.= " ". self::TABLE_MATERIAL  .".searchContentForSearchModule LIKE '{$conteudo}' OR ";
                        //$condition.= " to_tsvector('portuguese', ". self::TABLE_MATERIAL  .".searchContent) @@ to_tsquery('portuguese', '{$conteudo}') OR ";
                    }
                    
                    $condition = substr($condition, 0, -3);
                    $condition.= " )) OR ";
                }

                $condition = substr($condition, 0, -4);
                $condition = "( $condition )";

                return $condition;



            // TRABALHA WHERE CONDITION PARA IN
            case 'IN'       :
            case 'NOT IN'  :

                foreach($valores as $tag => $conteudoS)
                {
                    list($field, $subField) = explode(".", $tag);
                    $condition.= " ( ";
                    if("{$field}.{$subField}" != "*.*")
                    {
                        $condition.=  self::TABLE_MATERIAL  .".fieldid = '{$field}' AND  ". self::TABLE_MATERIAL  .".subfieldid = '{$subField}' AND ";
                    }

                    $inContent = implode("', '", $conteudoS);

                    $condition.= " ". self::TABLE_MATERIAL  .".searchContentForSearchModule IN ('{$inContent}') ";
                    $condition.= " ) ";
                    $condition.= (strtoupper($obj->condition) == "IN") ? " OR " : " AND ";
                }

                $condition = substr($condition, 0, -4);
                $condition = "( $condition )";

                return $condition;


            // TRABALHA WHERE CONDITION PARA BETWEEN
            case "BETWEEN"  :
            case "NUMERIC BETWEEN"  :

                foreach($valores as $tag => $conteudoS)
                {
                    if(count($conteudoS) != 2)
                    {
                       //continue;
                    }

                    $condition.= " ( ";

                    if($tag != "*.*")
                    {
                        list($field, $subField) = explode(".", $tag);
                        $condition.=  self::TABLE_MATERIAL  .".fieldid = '{$field}' AND  ". self::TABLE_MATERIAL  .".subfieldid = '{$subField}' AND (";
                    }

                    $searchContent = self::TABLE_MATERIAL  .".searchContentForSearchModule";
                    $conteudo1      = "'{$conteudoS[0]}'";
                    $conteudo2      = "'{$conteudoS[1]}'";

                    if(strtoupper($obj->condition) == "NUMERIC BETWEEN")
                    {
                        $searchContent1  = "getSearchContentToYearCompare(". self::TABLE_MATERIAL  .".searchContentForSearchModule, FALSE)::integer";
                        $searchContent2  = "getSearchContentToYearCompare(". self::TABLE_MATERIAL  .".searchContentForSearchModule, TRUE)::integer";
                        $conteudo1      = "getSearchContentToYearCompare('{$conteudoS[0]}', TRUE)::integer";
                        $conteudo2      = "getSearchContentToYearCompare('{$conteudoS[1]}', TRUE)::integer";
                    }

                    switch ($tag)
                    {
                        // CONDIÇÃO ESPECIAL PARA 260.c = data de publicação
                        case MARC_PUBLICATION_DATE_TAG :
                            $condition.= "CASE WHEN char_length(getSearchContentToYearCompare(". self::TABLE_MATERIAL  .".searchContentForSearchModule, FALSE)) >= 1 THEN ";
                            $condition.= "(";
                            $condition.= "      ($searchContent1 >= $conteudo1 ";
                            $condition.= "          AND ";
                            $condition.= "       $searchContent2 <= $conteudo2) ";
                            $condition.= "  OR  compareYearPeriod(". self::TABLE_MATERIAL  .".searchContentForSearchModule, getSearchContentToYearCompare('{$conteudoS[0]}', FALSE), getSearchContentToYearCompare('{$conteudoS[1]}', TRUE)) ";
                            $condition.= ") ELSE FALSE END ";
                            break;

                        default:
                            $condition.= " ($searchContent >= $conteudo1 AND $searchContent <= $conteudo2 )";
                            break;
                    }

                    $condition.= ")) OR ";
                }

                $condition = substr($condition, 0, -4);
                $condition = "( $condition )";

                return $condition;


            // TRABALHA WHERE CONDITION PARA LIKE
            case 'ILIKE'     :
            case 'LIKE'      :
            case 'NOT LIKE'  :
            case 'NOT ILIKE' :

                $obj->condition = str_replace("ILIKE", "LIKE", $obj->condition);

                foreach($valores as $tag => $conteudoS)
                {
                    $condition.= "\n ( "; //linha nova para facilitar a leitura

                    list($field, $subField) = explode(".", $tag);

                    if("{$field}.{$subField}" != "*.*")
                    {
                        $condition.=  self::TABLE_MATERIAL  .".fieldid = '{$field}' AND  ". self::TABLE_MATERIAL  .".subfieldid = '{$subField}' AND ";
                    }

                    $condition.= " ( ";

                    foreach ($conteudoS as $conteudo)
                    {
                        
                        if( ereg ( $tag, MARC_CLASSIFICATION_TAG ) )
                        {
                            $conteudo .= '%';
                        }
                        else if ( ereg ( $tag, MARC_CUTTER_TAG ) )
                        {
                            $conteudo = '%' . $conteudo . '%';
                        }

                        if ( ereg("%", $conteudo) )
                        {
                            $condition.= " ". self::TABLE_MATERIAL  .".searchContentForSearchModule {$obj->condition} '{$conteudo}' OR ";
                        }
                        
                        else if ($obj->field[0] == '949' && $obj->subfield[0] == 'h')
                        {
                            $condition.= " ". self::TABLE_MATERIAL  .".searchContentForSearchModule {$obj->condition} '%{$conteudo}%' OR ";
                        }
                        
                        else
                        {
                            $conteudo = str_replace(" ", "%", $conteudo);

                            $fullTextSearch = " to_tsvector('portuguese', ". self::TABLE_MATERIAL  .".searchContentForSearchModule) @@ plainto_tsquery('portuguese', '{$conteudo}') ";

                            if ( $obj->condition == 'NOT LIKE' OR $obj->condition == 'NOT ILIKE' )
                            {
                                $fullTextSearch = " !(" . $fullTextSearch . ")";
                            }

                            $condition .= $fullTextSearch . " OR ";
                            
                            // Adiciona LIKE para complementar a busca.
                            $condition.= " ". self::TABLE_MATERIAL  .".searchContentForSearchModule LIKE '{$conteudo}%' OR ";
                        }
                    }

                    $condition = substr($condition, 0, -3);
                    $condition.= " ))";
                    $condition.= ($obj->condition == "LIKE") ? " OR " : " AND ";
                }

                $condition = substr($condition, 0, -4);
                $condition = "( $condition )";

                return $condition;

            default:

                // Variável para definir se é pesquisa avançada.
                $advanced = false;
                
                foreach($valores as $tag => $conteudoS)
                {
                    $condition.= "\n ( "; //linha nova para facilitar a leitura
                    list($field, $subField) = explode(".", $tag);
                    
                    if ( $field != '*' )
                    {
                        $advanced = true;
                        $conditionValues.= "(".self::TABLE_MATERIAL  .".fieldid = '{$field}' AND  ". self::TABLE_MATERIAL  .".subfieldid = '{$subField}' AND (";
                    }
                    
                    foreach ($conteudoS as $conteudo)
                    {
                        $conditionValues.= " (". self::TABLE_MATERIAL  .".searchContentForSearchModule = '{$conteudo}') OR ";
                    }
                    
                    $conditionValues = substr($conditionValues, 0, -3);
                    if ( $advanced )
                    {
                        $conditionValues.= ")) OR ";
                    }
                }
                
                if ( $advanced )
                {
                    $conditionValues = substr($conditionValues, 0, -3);
                }
                
                return "( $conditionValues )";
        }

    }

    /**
     * Monta condições de exemplar
     * 
     * @param type $temMaterialControl
     * @return string
     */
    protected function mountExemplaryControlWhere($temMaterialControl = false)
    {
        if(is_null($this->attributesExemplaryControl))
        {
            return null;
        }

        $sqlModel = '<operator>   ( <condition> <continue> )';
        $sqlWhere = null;

        foreach ($this->attributesExemplaryControl as $obj)
        {
            // Trabalha o bloco de condição para consulta
            $condition = $this->workingConditionExemplaryControl($obj);

            //substitui o operador e a condiçao no modelo
            $op = ($obj->operator == "NOT") ? "AND" : $obj->operator;
            $where = str_replace(array('<operator>', '<condition>'), array($op, $condition), $sqlModel);

            // incrementa o sqlWhere
            $sqlWhere = is_null($sqlWhere) ? $where : str_replace('<continue>', $where, $sqlWhere);
        }

        // remove o conteudo indevido
        $sqlWhere = str_replace('<continue>', '<extra_sql>', $sqlWhere);

        if(!$temMaterialControl)
        {
            $sqlWhere = substr($sqlWhere, 4, strlen($sqlWhere));
        }

        return $sqlWhere;
    }

    public function workingConditionExemplaryControl($obj)
    {
        $fields     = $obj->fieldName;
        $values     = $obj->values;


        // CONDIÇÂO ESPEXIAL PARA FILTRO DE PLANILHAS
        if($fields[0] == 'categoryLevel')
        {
            $v = $values[0];
            list($cat, $lev) = explode(",", $v);

            $operator = ((trim($obj->condition) == "=") ? 'AND' : 'OR');

            return " ( ". self::TABLE_EXEMPLARY_CONTROL .".category {$obj->condition} '{$cat}' {$operator} ". self::TABLE_EXEMPLARY_CONTROL .".level {$obj->condition} '{$lev}' ) ";
        }

        $conditionFields  = "";

        foreach ($fields as $index => $f)
        {
            $parcialCondition = "";

            switch(strtoupper($obj->condition))
            {
                case "IN" :
                    $v = implode(", ", $values);
                    $parcialCondition.= " ". self::TABLE_EXEMPLARY_CONTROL    .".{$f}  IN ($v) ";
                break;

                case "NOT":
                case "NOT IN":
                    $v = implode(", ", $values);
                    $parcialCondition.= " ". self::TABLE_EXEMPLARY_CONTROL    .".{$f}  NOT IN ($v) ";
                break;

                case "BETWEEN"  :
                    if(count($values) == 2 && strlen($values[0]) && strlen($values[1]))
                    {
                        $parcialCondition.= " (";
                        $parcialCondition.= "    ". self::TABLE_EXEMPLARY_CONTROL  .".{$f} >= '{$values[0]}' ";
                        $parcialCondition.= "    AND ";
                        $parcialCondition.= "    ". self::TABLE_EXEMPLARY_CONTROL  .".{$f} <= '{$values[1]}' ";
                        $parcialCondition.= " )";
                    }
                break;

                default:
                    foreach ($values as $v)
                    {
                        $parcialCondition.= " ". self::TABLE_EXEMPLARY_CONTROL    .".{$f} {$obj->condition} '$v' OR ";
                    }
                    $parcialCondition = substr($parcialCondition, 0, strlen($parcialCondition)-3);
                break;
            }

            $conditionFields.= " ($parcialCondition) OR ";
        }
        
        $conditionFields = substr($conditionFields, 0, strlen($conditionFields) -3);

        return "($conditionFields)\n";
    }

    /**
     * Executa o sql e interno
     */
    protected function executeSearch( $limit = null )
    {
        //caso não tenha filtros, sai fora
        if(!$this->attributesMaterial && !$this->attributesMaterialControl && !$this->attributesExemplaryControl && !$this->extraAttributes)
        {
            return;
        }

        if ( ! $this->attributesMaterial )
        {
            $sqlWhere = null;
        }

        $tableName = self::TABLE_MATERIAL;
        $subSql = " SELECT DISTINCT {$tableName}.controlNumber FROM {$tableName} <HINNER_JOIN_MATEIRAL_CONTROL> <HINNER_JOIN_EXEMPLARY_CONTROL> WHERE ";
        $sqlWhere = null;

        if ( is_array($this->attributesMaterial))
        {
            foreach ($this->attributesMaterial as $index => $obj)
            {
                $condition = $this->workingConditionMaterial($obj);

                switch ( trim( $obj->operator ) )
                {
                    case 'OR':
                        //O operador or, como ele adiciona mais registros, não tem necessidade de executá-lo com subsql
                        $sqlWhere .= "OR  <CONDITION>";
                    break;

                    case 'AND':
                        if ($index == 0)
                        {
                            //Se o termo for o primeiro, não gera o subsql por questões de desempenho
                            $sqlWhere .= "AND <CONDITION> ";
                            break;
                        }

                        $sqlWhere .= "AND  {$tableName}.controlNumber IN ( <SUBSQL><CONDITION> <CONDITION_MATEIRAL_CONTROL> <CONDITION_EXEMPLARY_CONTROL>)";

                    break;

                    case 'NOT':
                        $sqlWhere .= "AND  {$tableName}.controlNumber NOT IN ( <SUBSQL><CONDITION> <CONDITION_MATEIRAL_CONTROL> <CONDITION_EXEMPLARY_CONTROL>)";
                    break;
                }

                $priority .= '(';
                $sqlWhere .= ')';

                $sqlWhere = str_replace(array('<SUBSQL>', '<CONDITION>'),array($subSql, $condition),$sqlWhere);
            }
        }

        $sqlWhere = substr($sqlWhere, 4, strlen($sqlWhere));
        $materialWhere = $priority . $sqlWhere;

        $exemplaryControlWhere  = $this->mountExemplaryControlWhere( $materialWhere );
        
        $secTitle = MARC_SECUNDARY_TITLE_TAG;
        $title = MARC_TITLE_TAG;
       
        if ( is_array ( $this->order ) )
        {
            foreach ( $this->order as $line => $info )
            {
                $where[] = " ( fieldid = '{$info->field}' AND subfieldid = '{$info->subfield}') \n";
            }
            
            $where = implode( ' OR ', $where );
            
            $sqlorder = "( SELECT CASE WHEN fieldid || '.' || subfieldid = '{$title}' or fieldid || '.' || subfieldid = '{$secTitle}'  then trim( substring(searchcontent from regexp_replace('0' || coalesce(indicator2,'0') , '[^0-9]', '0', 'g' )::int  for length(searchcontent) )  ) else searchcontent end
                    FROM gtcMaterial mo 
                   WHERE ( {$where}) 
                     AND mo.controlnumber = gtcMaterial.controlNumber
                ORDER BY fieldid, subfieldid
                   LIMIT 1 )";
        }
        else
        {
            $sqlorder = 0; //caso não tenha order coloca tudo como 0
        }
        
        $sqlSelect = "SELECT DISTINCT controlNumber, {$sqlorder} as ordem ";

        if ( $materialWhere )
        {
            $sqlFrom    = " FROM ". self::TABLE_MATERIAL ." ";
            $sqlWhere   = " WHERE $materialWhere";
        }

        // adiciona join com gtcExemplaryControl
        if ( $exemplaryControlWhere || isset( $this->extraAttributes[self::TABLE_EXEMPLARY_CONTROL] ) || isset( $this->extraAttributes["all"] ) )
        {
            if ( !strlen($exemplaryControlWhere) )
            {
                $exemplaryControlWhere = "<extra_sql>";
            }

            $sqlSelect = is_null($sqlFrom) ? str_replace("<fistTableName>", self::TABLE_EXEMPLARY_CONTROL  , $sqlSelect) : $sqlSelect;
            $sqlFrom   = !($materialWhere) && !($materialControlWhere) ? " FROM ". self::TABLE_EXEMPLARY_CONTROL   ." " : " $sqlFrom INNER JOIN ". self::TABLE_EXEMPLARY_CONTROL   ." USING (controlNumber) ";
            $sqlWhere  = !($materialWhere) && !($materialControlWhere) ? " WHERE $exemplaryControlWhere " : " $sqlWhere $exemplaryControlWhere ";

            // ADICIONA AS CONDIÇÔES ESPECIAIS
            if(isset($this->extraAttributes[self::TABLE_EXEMPLARY_CONTROL]))
            {
                foreach ($this->extraAttributes[self::TABLE_EXEMPLARY_CONTROL] as $sql)
                {
                    $sqlWhere = str_replace('<extra_sql>', " $sql <extra_sql>", $sqlWhere);
                }
            }

            $sqlWhere = str_replace(array("<HINNER_JOIN_EXEMPLARY_CONTROL>", "<CONDITION_EXEMPLARY_CONTROL>", '<extra_sql>'), array(" INNER JOIN ". self::TABLE_EXEMPLARY_CONTROL  ." USING (controlNumber) ", $exemplaryControlWhere, ""), $sqlWhere);
        }

        // Adiciona condição extra de ambas as tabelas.
        if(isset($this->extraAttributes["all"]))
        {
            $sqlWhere.= " AND (<extra_sql>) ";

            foreach ($this->extraAttributes["all"] as $sql)
            {
                $sqlWhere = str_replace('<extra_sql>', " $sql <extra_sql>", $sqlWhere);
            }
        }

        $sqlFinal = "$sqlSelect 
               FROM ( SELECT DISTINCT controlNumber
                    $sqlFrom
                    $sqlWhere
                    ) gtcMaterial "; //monta o sql final

        // FAZ UM REPLACE DA TAGS QUE SOBRARAM
        $sqlFinal = str_replace(array("<HINNER_JOIN_MATEIRAL_CONTROL>", "<HINNER_JOIN_EXEMPLARY_CONTROL>", "<CONDITION_MATEIRAL_CONTROL>", "<CONDITION_EXEMPLARY_CONTROL>", "<extra_sql>"), "", $sqlFinal);
        
        $orderType = array_values( is_array($this->order)? $this->order : array() );
        $orderType = $orderType[0]->type;
        $orderType = $orderType == SORT_ASC ? ' asc ' : ' desc ';
        
        $sqlFinal .= " order by ordem ". $orderType;
        
        if ( $limit )
        {
            $sqlFinal .= " LIMIT $limit";
        }
        
        $result = $this->query( $sqlFinal );
        $this->count = count( $result );
       
        //IMPORTANTE: Se não foi passado $limit ou ele for maior que 1 quer dizer que 
        //foi buscado um material ao clicar em alguma ação da pesquisa simples, 
        //por exemplo 'Mais detalhes', 'Reserva', então se esta busca sobrescrever 
        //$_SESSION['materialSearchResult'] a pesquisa se perderá. Para mais 
        //detalhes vide ticket #12825
        if ( $limit != 1 ) //Se não for uma pesquisa oriunda de uma ação da grdSimpleSearch
        {
            $_SESSION['materialSearchResult'] = $result;
        }
        
        return $result;
    }

    public function getWorkSearch( $limit= null, $firstTime = false )
    {
        if ( $limit )
        {
            $firstTime = true;
        }

        $tablePrefixSuffix = self::TABLE_PREFIX_SUFFIX;
        
        if ( $firstTime || ! is_array( $_SESSION['materialSearchResult'] ) )
        {
            $controlNumberList = $this->executeSearch( $limit );
        }
        //Se não foi passado limite ou foi passado um limite maior que 1 então 
        //pega o valor da sessão.
        if ( $limit != 1 )
        {
            $controlNumberList = $_SESSION['materialSearchResult'];
        }
        
        //Define o limite do foreach
        if ($limit)
        {
            $start = 0;
            $end = $limit;
        }
        else
        {
            $pnPage = MIOLO::_REQUEST('pn_page') ? MIOLO::_REQUEST('pn_page') : 1;
            $start = ( $pnPage * LISTING_NREGS ) - LISTING_NREGS;;
			$end = $start+LISTING_NREGS; //Deixa o limite padrão
        }

        $this->result = array();
        
        //precisa ser um foreach para manter a ordem
        for ( $i = $start ; $i < $end; $i++ )
        {
            $controlNumber = $controlNumberList[$i][0];
            
            //precisa disso para a última página
            if ( !$controlNumber )
            {
                continue;
            }

            //adiciona o prefixo e sufixo caso estado definido que assim deve ser
            if ( $this->addPrefixSuffixInresult )
            {
                $content = "(CASE WHEN prefixId IS NOT NULL THEN (SELECT $tablePrefixSuffix.content FROM $tablePrefixSuffix WHERE prefixId = prefixsuffixid ) ELSE '' END)
                              || content  ||
                            (CASE WHEN suffixId IS NOT NULL THEN (SELECT $tablePrefixSuffix.content FROM $tablePrefixSuffix WHERE suffixId = prefixsuffixid ) ELSE '' END) as content";
            }
            else
            {
                $content = 'content';
            }

            $select = "SELECT   controlNumber,
                                fieldId,
                                subfieldId,
                                $content,
                                line,
                                searchcontent,
                                indicator1,
                                indicator2,
                                prefixId,
                                suffixId
                        FROM    ".self::TABLE_MATERIAL;

            $select .= " WHERE controlNumber = $controlNumber ORDER BY fieldid, subfieldid, line";

            $this->clear();
            //Seta as colunas para retorno em objeto
            $this->setColumns("controlNumber,fieldId,subFieldId,content,line,searchContent,indicator1,indicator2,prefixId,suffixId");

            $result = $this->query($select, true);
            
            if ( is_array( $result ) )
            {
                foreach ( $result as $line => $res )
                {
                    $tag = "{$res->fieldId}.{$res->subFieldId}";

                    //Grava o número de controle do registro
                    $data[$controlNumber]['CONTROLNUMBER'] = $res->controlNumber;
                    $data[$controlNumber][0] = $controlNumber; //usado para conseguir pegar o control number na grid

                    if ( is_array( $this->fields ) )
                    {
                        if ( array_key_exists( $tag,$this->fields) )
                        {
                            $data[$controlNumber][$tag][$res->line] = $res;
                        }
                    }
                    else
                    {
                        $data[$controlNumber][$tag][$res->line] = $res;
                    }
                }
            }
        }
        
        return array_values($data);
    }
}
?>
