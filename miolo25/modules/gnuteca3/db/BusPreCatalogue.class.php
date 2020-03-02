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
 *
 * @since
 * Class created on 18/12/2008
 *
 **/
class BusinessGnuteca3BusPreCatalogue extends GBusiness
{
    public  $MIOLO;
    public  $controlNumber,
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
            $separatorid;
    
    public  $expressionS,
            $number,
            $numberType;
    public  $fullColumns;
    public  $businessPreCatalogueSearch,
            $businessTag,
            $busSearchFormat,
            $busFile;
    
    private $busMaterial;
    private $busSearchableField;

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->tables = 'gtcPreCatalogue';
        $this->fullColumns =  'controlNumber, fieldid, subfieldid, line, indicator1, indicator2, content, searchcontent, prefixid, suffixid, separatorid';

        $this->setData(null);
        $this->setColumns();
        $this->setTables();

        $this->businessPreCatalogueSearch   = $this->MIOLO->getBusiness( $this->module, 'BusPreCatalogueSearch');
        $this->businessTag                  = $this->MIOLO->getBusiness( $this->module, 'BusTag');
        $this->busSearchFormat              = $this->MIOLO->getBusiness( $this->module, 'BusSearchFormat');
        $this->busFile                      = $this->MIOLO->getBusiness( $this->module, 'BusFile');
        $this->busMaterial                  = $this->MIOLO->getBusiness( $this->module, 'BusMaterial');
        $this->busSearchableField       = $this->MIOLO->getBusiness( $this->module, 'BusSearchableField');
    }

    /**
     * Seta as tabelas
     *
     */
    public function setTables()
    {
        parent::setTables("gtcprecatalogue");
    }

    /**
     * Este método seta as colunas da tabela.
     *
     * @param String
     */
    public function setColumns()
    {
        parent::setColumns($this->fullColumns);
    }


    /**
     * Seta as condições do sql
     *
     * @return void
     */
    public function getWhereCondition()
    {
        $where = "";

        if(!is_null($this->controlNumber))
        {
            $where.= " controlNumber = ? AND ";
        }
        if(!is_null($this->fieldid))
        {
            $where.= " fieldid = ? AND ";
        }
        if(!is_null($this->subfieldid))
        {
            $where.= " subfieldid = ? AND ";
        }
        if(!is_null($this->line))
        {
            $where.= " line = ? AND ";
        }
        if(!is_null($this->contentS))
        {
            $where.= " lower(searchcontent) LIKE lower(?) ";
        }

        if(strlen($where))
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
        $args = array();

        if(!is_null($this->controlNumber))
        {
            $args[] = $this->controlNumber;
        }
        if(!is_null($this->fieldid))
        {
            $args[] = $this->fieldid;
        }
        if(!is_null($this->subfieldid))
        {
            $args[] = $this->subfieldid;
        }
        if(!is_null($this->line))
        {
            $args[] = $this->line;
        }
        if(!is_null($this->contentS))
        {
            $args[] = "%". str_replace(" ", "%", $this->content) ."%";
        }

        return $args;
    }


    public function searchMaterial($order = null)
    {
        parent::clear();
        $this->setTables();
        $this->setColumns();
        $this->getWhereCondition();

        if(!is_null($order))
        {
            $this->setOrderBy($order);
        }

        $sql = parent::select($this->getDataConditionArray());

        return parent::query();
    }


    public function searchInPreCatalogue($order = null)
    {
        $this->businessPreCatalogueSearch->clean();

        $this->businessPreCatalogueSearch->addSearchTagField(MARC_TITLE_TAG);
        $this->businessPreCatalogueSearch->addSearchTagField(MARC_AUTHOR_TAG);

        if( ! strlen($this->expressionS) && ! strlen($this->number) )
        {
            $this->expressionS = '%';
        }
        
        $exp = $this->busSearchableField->parseExpression( $this->expressionS );
        $this->businessPreCatalogueSearch->addMaterialWhereByExpression($exp);

        //Adiciona condição quando é informado o número de controle ou o número do tombo
        if( ! empty($this->number) )
        {

            switch ( $this->numberType )
            {
                case "cn" :
                    $ok = $this->addControlNumber($this->number);
                break;
                case "in" :
                    $cn = $this->getControlNumber($this->number,'MARC_EXEMPLARY_ITEM_NUMBER_TAG');
                    $ok = $this->addControlNumber($cn);
                break;
                case "wn" :
                    $cn = $this->getControlNumber($this->number,'MARC_WORK_NUMBER_TAG');
                    $ok = $this->addControlNumber($cn);
                break;
            }
            //Se não conseguiu inserir o número de controle
            if ( !$ok )
            {
                return false;
            }
        }

        $data = $this->businessPreCatalogueSearch->getWorkSearch();
        
        if( ! $data )
        {
            return false;
        }
        
        foreach ( $data as $l => $values )
        {
            $gridData[$l][0] = $values['CONTROLNUMBER'];

            $div1 =  new MDiv(null, $this->businessTag->getTagNameByTag(MARC_TITLE_TAG)     .":". $values[MARC_TITLE_TAG][0]->content);
            $div2 =  new MDiv(null, $this->businessTag->getTagNameByTag(MARC_AUTHOR_TAG)    .":". $values[MARC_AUTHOR_TAG][0]->content);

            $gridData[$l][1] = $div1->generate() . $div2->generate();
        }

        return $gridData;
    }


    public function insertMaterial()
    {
        $this->clear();

        $this->content = str_replace("\r", "", $this->content);
        $this->searchContent  = $this->busMaterial->prepareSearchContent($this->fieldid.'.'.$this->subfieldid, $this->content );
        $this->setTables();
        $this->setColumns();

        $args = array
        (
            $this->controlNumber,
            $this->fieldid,
            $this->subfieldid,
            $this->line,
            $this->indicator1,
            $this->indicator2,
            $this->content,
            $this->searchContent,
            $this->prefixid,
            $this->suffixid,
            $this->separatorid
        );

        $sql    = $this->insert($args);
        $result = $this->execute($sql);

        return $result;
    }


    public function updateMaterialContent()
    {
        parent::clear();

        $this->content = str_replace("\r", "", $this->content);
        $this->searchContent  = $this->busMaterial->prepareSearchContent($this->fieldid.'.'.$this->subfieldid, $this->content );

        $this->getWhereCondition();

        $this->setTables();
        parent::setColumns("content, searchcontent");

        $data = array
        (
            $this->content,
            $this->searchContent,
            $this->controlNumber,
            $this->fieldid,
            $this->subfieldid,
            "{$this->line}"
        );

        $sql = parent::update($data);

        $result = parent::Execute();

        return $result;
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

        list($fieldId,$subFieldId) = explode(".", $tag);

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

        list($fieldId,$subFieldId) = explode(".", $tag);

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

        list($fieldId,$subFieldId) = explode(".", $tag);

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


    public function getMaterial()
    {
        parent::clear();

        $this->setTables();
        $this->setColumns();

        $this->getWhereCondition();
        parent::select($this->getDataConditionArray());

        $result = parent::query();

        return $result;
    }

    /**
     * Remove um material.
     *
     * @param boolean $deleteImage deleta a imagem também
     * @return boolean retorna um boolean caso a remoção funcione.
     */
    public function deleteMaterial( $deleteImage=true, $controlNumber=null )
    {
        parent::clear();

        if ( ! is_null($controlNumber) ) //Se foi passado numero de controle
        {
            $this->controlNumber = $controlNumber;
        }
        elseif ( is_null($this->controlNumber) ) //Se não existe numero de controle definido no objeto atual
        {
            $this->controlNumber = MIOLO::_REQUEST('controlNumber'); //Define numero de controle vindo da sessão.
        }

        $this->setTables();
        $this->getWhereCondition();
        $data = $this->getDataConditionArray();
        $sql = parent::delete($data);
        $result = parent::Execute();

        //remove imagem
        if ( $deleteImage )
        {
            $this->busFile->folder= 'coverpre'; //escolhe a pasta certa para escolher a imagem
            $this->busFile->fileName = $this->controlNumber;
            $file = $this->busFile->searchFile(true);

            $file= $file[0];

            if ( $file->absolute )
            {
                $this->busFile->deleteFile( $file->absolute );
            }
        }

        return $result;
    }



    public function clean()
    {
        $this->controlNumber    =
        $this->fieldid          =
        $this->subfieldid       =
        $this->line             =
        $this->indicator1       =
        $this->indicator2       =
        $this->content          =
        $this->searchContent    =
        $this->contentS         = null;
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
    public function getContent($controlNumber, $fieldId, $subfieldId = null, $line = null, $returnFullLine = false, $returnMultiLine = false , $orderBy = null)
    {
        parent::clear();
        $this->setTables();
        parent::setColumns(($returnFullLine ? $this->fullColumns :  'content'));

        $this->setWhere('controlNumber = ?');
        $this->setWhere('fieldId = ?');

        $args[] = $controlNumber;
        $args[] = $fieldId;

        if($subfieldId)
        {
            $args[] = $subfieldId;
            $this->setWhere('subfieldId = ?');
        }

        if ($line)
        {
            $this->setWhere('line = ?');
            $args[] = $line;
        }

        if(!is_null($orderBy))
        {
            $this->setOrderBy($orderBy);
        }

        $sql   = $this->select($args);

        $query = $this->query($sql, $returnFullLine);

        if($returnFullLine && !$returnMultiLine)
        {
            return $query[0];
        }
        elseif($returnMultiLine)
        {
            return $query;
        }

        return $query[0][0];
    }


    public function getContentTag($controlNumber, $tag, $line = null)
    {
        list($fieldId, $subfieldId) = explode('.', $tag);
        return $this->getContent($controlNumber, $fieldId, $subfieldId, $line);
    }


    /**
     * Retorna o próximo número de controle a ser usado.
     * A pré-catalogação reaproveita números.
     */
    public function getNextControlNumber()
    {
        parent::clear();
        $this->setTables();
        parent::setColumns("MAX(controlnumber)");

        $sql = parent::select($args);

        $r = parent::query();

        if(!$r)
        {
            return 1;
        }

        return ($r[0][0]+1);
    }

    /**
     * Retorna os números de controle possível para esse itemNumber
     *
     * @param string $number número que irá ser procurado.
     * @param string $numberType tipo do número a ser procurado, é para ser utilizado valores das constantes do sistema para campos MARC.
     *
     * @return array retorna os números de controle possível para esse itemNumber
     */
    public function getControlNumber($number,$numberType)
    {
        //segurança para não retornar um
        if ( !$number)
        {
            return false;
        }

        $sql = "SELECT controlNumber
                  FROM gtcPrecatalogue
                 WHERE fieldid = ( SELECT split_part( (SELECT value FROM basconfig where parameter ='$numberType' ), '.',1) )
                   AND subfieldid = ( SELECT split_part( (SELECT value FROM basconfig where parameter ='$numberType' ), '.',2)
                 WHERE content = '$number');
        ";

        $result = $this->query($sql);

        return $result;
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

        $type   = substr($content, (str_replace("000-","", LEADER_TAG_MATERIAL_TYPE)        -1), 1);
        $level  = substr($content, (str_replace("000-","", LEADER_TAG_BIBLIOGRAPY_LEVEL)    -1), 1);
        $levelx = substr($content, (str_replace("000-","", LEADER_TAG_ENCODING_LEVEL)       -1), 1);

        $this->businessCataloge = $this->MIOLO->getBusiness($this->module, 'BusCataloge');
        return $this->businessCataloge->getSpreadsheetCategory($type, $level);
    }

    
    /**
    */
    public function getTagSuffix($tag, $controlNumber)
    {
        $businessPrefixSuffix = $this->MIOLO->getBusiness($this->module, 'BusPrefixSuffix');

        list($f,$s) = explode(".", $tag);

        $sql = "SELECT  A.suffixid, A.content, B.content ";
        $sql.= "FROM {$this->tables} A LEFT JOIN {$businessPrefixSuffix->tables} B USING (fieldid, subfieldid) ";
        $sql.= "WHERE A.fieldid = '{$f}' AND A.subfieldid = '{$s}' AND A.controlNumber = '{$controlNumber}' AND A.suffixid = B.prefixsuffixid";

        return $this->query($sql);
    }


    public function getTagPrefix($tag, $controlNumber)
    {
        $businessPrefixSuffix = $this->MIOLO->getBusiness($this->module, 'BusPrefixSuffix');

        list($f,$s) = explode(".", $tag);

        $sql = "SELECT  A.prefixId, A.content, B.content ";
        $sql.= "FROM {$this->tables} A LEFT JOIN {$businessPrefixSuffix->tables} B USING (fieldid, subfieldid) ";
        $sql.= "WHERE A.fieldid = '{$f}' AND A.subfieldid = '{$s}' AND A.controlNumber = '{$controlNumber}' AND A.prefixId = B.prefixsuffixid";

        return $this->query($sql);
    }


    public function getTagSeparator($tag, $controlNumber)
    {
        $businessSeparator = $this->MIOLO->getBusiness($this->module, 'BusSeparator');

        list($f,$s) = explode(".", $tag);

        $sql = "SELECT  A.separatorId, A.content, B.content ";
        $sql.= "FROM {$this->tables} A LEFT JOIN {$businessSeparator->tables} B USING (fieldid, subfieldid) ";
        $sql.= "WHERE A.fieldid = '{$f}' AND A.subfieldid = '{$s}' AND A.controlNumber = '{$controlNumber}' AND A.separatorId = B.separatorId";

        return $this->query($sql);
    }

    /**
     * Adiciona os números de controle no busPrecatalogue.
     *
     * @param string/array $controlNumber
     * @return boolean $return verifica se o número de controle foi realmente passado para o businessPreCatalogueSearch
     */

    public function addControlNumber($controlNumber)
    {
        //Se foi passada uma string
        if ( !is_array($controlNumber) )
        {
            //Transforma-a em array
            $controlNumber = array($controlNumber); 
        }

        //Para cada número de control
        foreach ( $controlNumber as $line => $number )
        {
            //Verifica se um número foi passado
            if ( $number )
            {
                //Adiciona-o no bus da pesquisa da pre-catalogação
                $this->businessPreCatalogueSearch->addControlNumber( $number );
                //Define flag que identifica que um número de controle foi inserido corretamente.
                $ok = true;
            }
        }

        return $ok;
    }
}
?>