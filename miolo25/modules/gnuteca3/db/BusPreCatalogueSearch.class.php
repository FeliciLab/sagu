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
 * This file handles the connection and actions for Material Gender table
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 25/11/2008
 *
 **/
$MIOLO = MIOLO::getInstance();
$module = MIOLO::getCurrentModule();
$MIOLO->usesBusiness($module, 'BusGenericSearch2');

class BusinessGnuteca3BusPreCatalogueSearch extends BusinessGnuteca3BusGenericSearch2
{
    const TABLE_PRE_CATALOGUE = "gtcPreCatalogue";

    function __construct()
    {
        parent::__construct();

        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
    }


    /**
     * retorna o Sql Where para a tabela gtcMaterial
     *
     * @return string or null
     */
    protected function getPreCataloguelWhere()
    {
        if(is_null($this->attributesMaterial))
        {
            return null;
        }

        $tableName      = self::TABLE_PRE_CATALOGUE ;
        $controlNumber  = " {$tableName}.controlNumber ";
        $subSql         = " SELECT DISTINCT {$controlNumber} FROM {$tableName}  WHERE ";
        $sqlWhere       = null;

        foreach ($this->attributesMaterial as $index => $obj)
        {
            $condition = $this->workingConditionPreCatalogue($obj);

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

                	$sqlWhere .= "AND {$controlNumber} IN ( <SUBSQL><CONDITION> )";

                break;

                case 'NOT':
                    $sqlWhere .= "AND {$controlNumber} NOT IN ( <SUBSQL><CONDITION> )";
                break;
            }

            $priority .= '(';
            $sqlWhere .= ')';

            $sqlWhere = str_replace(array('<SUBSQL>', '<CONDITION>'),array($subSql, $condition),$sqlWhere);
        }

        $sqlWhere = substr($sqlWhere, 4, strlen($sqlWhere));
        $sqlWhere = $priority . $sqlWhere;

        return $sqlWhere;
    }


    /**
     * Enter description here...
     *
     * @param object fields $obj
     * @return string sql where
     */
    public function workingConditionPreCatalogue($obj)
    {
        $conditionField     = "";
        $conditionValues    = "";

        // TRABALHA A CONDIÇÃO DE SUBCAMPOS
        if(count($obj->subfield) && count($obj->field))
        {
            foreach ($obj->field as $index => $f)
            {
                if(!strlen($f) || !strlen($obj->subfield[$index]))
                {
                    continue;
                }
                $conditionField.=  " ( ". self::TABLE_PRE_CATALOGUE .".fieldid = '{$f}' AND  ". self::TABLE_PRE_CATALOGUE  .".subfieldid = '{$obj->subfield[$index]}' )OR ";
            }
            if(strlen($conditionField))
            {
                $conditionField = substr($conditionField, 0, strlen($conditionField) -3);
                $conditionField = "( $conditionField )";
            }
        }

        //TRABALHA A CONDIÇÃO DE VALORES
        switch($obj->condition)
        {
            case 'LIKE':
            case 'NOT LIKE':
                foreach ($obj->values as $v)
                {
                    $v = $this->busMaterial->prepareSearchContent("{$obj->field[0]}.{$obj->subfield[0]}", $v);
                    $v = str_replace(" ", "%", $v);
                    $conditionValues.= " ( ". self::TABLE_PRE_CATALOGUE  .".searchContent {$obj->condition} '%{$v}%' ) OR ";
                }
                $conditionValues = substr($conditionValues, 0, strlen($conditionValues) -3);
                $conditionValues = "( $conditionValues )";
            break;

            case 'IN':
                $v = implode(",", $obj->values );
                $conditionValues.= " (". self::TABLE_PRE_CATALOGUE  .".content {$obj->condition} ({$v}) )";
            break;

            default:
                foreach ($obj->values as $v)
                {
                    $conditionValues.= " (". self::TABLE_PRE_CATALOGUE  .".searchContent {$obj->condition} '{$v}') OR ";
                }
                $conditionValues = substr($conditionValues, 0, strlen($conditionValues) -3);
                $conditionValues = "( $conditionValues )";
            break;

        }

        $sqlFinal = "";
        if(strlen($conditionField))
        {
            $sqlFinal.= "$conditionField AND ";
        }
        if(strlen($conditionValues))
        {
            $sqlFinal.= "$conditionValues AND ";
        }

        if(!strlen($sqlFinal))
        {
            return "";
        }

        $sqlFinal = substr($sqlFinal, 0, strlen($sqlFinal)-4);
        
        return "( $sqlFinal )";
    }



    /**
     * Executa o sql e seta os numeros de controle.
     *
     */
    public function executeSearch()
    {
        $sqlSelect   = "SELECT DISTINCT ".self::TABLE_PRE_CATALOGUE.".controlNumber ";
        $sqlFrom     = null;
        $sqlWhere    = null;

        $preCatalogueWhere = $this->getPreCataloguelWhere();
        $exemplaryControlWhere  = $this->mountExemplaryControlWhere( $materialWhere );

        if ( $exemplaryControlWhere )
        {
            $sqlFrom    = " FROM ". self::TABLE_PRE_CATALOGUE ." ";
            $sqlWhere   = " WHERE $exemplaryControlWhere";
        }
        elseif ( $preCatalogueWhere )
        {
            $sqlFrom    = " FROM ". self::TABLE_PRE_CATALOGUE ." ";
            $sqlWhere   = " WHERE $preCatalogueWhere";
        }

        return "$sqlSelect $sqlFrom $sqlWhere";
    }
    
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
            $condition = str_replace( 'gtcSearchMaterialView','gtcPrecatalogue', $condition);

            //substitui o operador e a condiçao no modelo
            $op = ($obj->operator == "NOT") ? "AND" : $obj->operator;
            $where = str_replace(array('<operator>', '<condition>'), array($op, $condition), $sqlModel);

            // incrementa o sqlWhere
            $sqlWhere = is_null($sqlWhere) ? $where : str_replace('<continue>', $where, $sqlWhere);
        }

        // remove o conteudo indevido
        $sqlWhere = str_replace('<continue>', '', $sqlWhere);

        if(!$temMaterialControl)
        {
            $sqlWhere = substr($sqlWhere, 4, strlen($sqlWhere));
        }

        return $sqlWhere;
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getWorkSearch()
    {
        // remove valores duplicados
        $this->controlNumbers = array_unique($this->controlNumbers);

        $select = "SELECT   controlNumber,
                            fieldId,
                            subfieldId,
                            content,
                            line
                    FROM    ". self::TABLE_PRE_CATALOGUE  ."
                   WHERE    controlNumber in (" .  $this->executeSearch() . ")
                ORDER BY    controlNumber";
        
        $this->result = $result = parent::query($select);

        if ($result)
        {
            $x = 0;

            //Monta o array para o retorno dos dados em um formato padrão
            foreach ($result as $key=>$res)
            {
                //Define a ordenação
                if ($this->order)
                {
                    //Ordenação - [sequencia][ORDER]
                    if ($this->order->field == $res[1] && $this->order->subfield == $res[2] && !$data[$x]['ORDER'])
                    {
                        $order[$x] = $data[$x]['ORDER'] = $res[3];
                    }
                }

                //Grava o número de controle do registro
                $data[$x]['CONTROLNUMBER'] = $res[0];
                $data[$x][0]               = $res[0]; //usado para conseguir pegar o control number na grid

            	//Dados - [sequencia][tag][line]
                $data[$x][$res[1] . '.' . $res[2]][$res[4]]->controlNumber = $res[0];
                $data[$x][$res[1] . '.' . $res[2]][$res[4]]->fieldId       = $res[1];
                $data[$x][$res[1] . '.' . $res[2]][$res[4]]->subfield      = $res[2];
                $data[$x][$res[1] . '.' . $res[2]][$res[4]]->content       = $res[3];
                $data[$x][$res[1] . '.' . $res[2]][$res[4]]->line          = $res[4];

                //Controle para separa as obras. Cada obra está em uma sequência diferente
                if ($result[$key+1][0] != $res[0])
                {
                    $x++;
                }
            }

            //Se informados, retorna os campos especificados
            if ($this->fields)
            {
                foreach ($data as $key=>$dt)
                {
                    $dataOK[$key][0] = $dt['CONTROLNUMBER']; //usado para conseguir pegar o control number na grid
                    $dataOk[$key]['ORDER'] = $dt['ORDER'];
                    $dataOk[$key]['CONTROLNUMBER'] = $dt['CONTROLNUMBER'];
                    foreach ($this->fields as $field)
                    {
                        $dataOk[$key][$field->field . '.' . $field->subfield] = $dt[$field->field . '.' . $field->subfield];
                    }
                }
            }
            else
            {
                $dataOk = $data;
            }

            array_multisort($order, $this->order->type, $dataOk);
        }
        return $dataOk;
    }
}
?>