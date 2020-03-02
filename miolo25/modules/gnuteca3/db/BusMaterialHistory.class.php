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
 * @author Guilherme Soares Soldatelli [guilherme@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Guilherme Soares Soldatelli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 30/05/2011
 *
 **/
class BusinessGnuteca3BusMaterialHistory extends GBusiness
{
    public  $MIOLO;
    public  $controlNumber,
            $materialHistoryId,
            $revisionNumber,
            $operator,
            $data,
            $chancesType,
            $fieldId,
            $subFieldId,
            $previousLine,
            $previousIndicator1,
            $previousIndicator2,
            $previousContent,
            $currentLine,
            $currentIndicator1,
            $currentIndicator2,
            $currentContent,
            $previousprefixid,
            $previoussuffixid,
            $previousseparatorid,
            $currentprefixid,
            $currentsuffixid,
            $currentseparatorid;

    public  $fullColumns,
            $localColumns;

    public $controlNumberS;
    public $revisionNumberS;
    public $operatorS;
    public $chancestypeS;
    public $fieldIdS;
    public $subFieldIdS;
    public $currentContentS;
    public $previousContentS;
    public $beginDateHourS;
    public $endDateHourS;

    const CHANGETYPE_INSERT = 'I';
    const CHANGETYPE_UPDATE = 'U';
    const CHANGETYPE_DELETE = 'D';

    function __construct()
    {
        parent::__construct();

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->localColumns = 'controlNumber,
                               revisionNumber,
                               operator,
                               data,
                               chancesType,
                               fieldId,
                               subFieldId,
                               previousLine,
                               previousIndicator1,
                               previousIndicator2,
                               previousContent,
                               currentLine,
                               currentIndicator1,
                               currentIndicator2,
                               currentContent,
                               previousprefixid,
                               previoussuffixid,
                               previousseparatorid,
                               currentprefixid,
                               currentsuffixid,
                               currentseparatorid';

        $this->fullColumns =  "materialhistoryid, {$this->localColumns}";

        $this->localColumns = str_replace(array(" ", "\n", "\t"), "", $this->localColumns);
        $this->fullColumns  = str_replace(array(" ", "\n", "\t"), "", $this->fullColumns);

        $this->setData(null);
        $this->setColumns($this->localColumns);
        $this->setTables("gtcmaterialhistory");
    }

    
    public function searchMaterialHistory($toObject = false)
    {
        parent::clear();
        $doSearch = false;
        $this->setColumns("     MH.controlNumber,
                                revisionnumber,
                                chancestype,
                                MH.fieldid,
                                MH.subfieldid,
                                gtctag.description || ' (' || MH.fieldid || '.' || MH.subfieldid || ')',
                                currentline,
                                previousline,
                                coalesce(prevPrefix.content ,'') || MH.previouscontent || (CASE WHEN contentComp1.description IS NOT NULL THEN ' - ' || contentComp1.description ELSE '' END) || coalesce(prevSuffix.content ,'') as previousContent,
                                coalesce(currentPrefix.content ,'') || MH.currentcontent || (CASE WHEN contentComp2.description IS NOT NULL THEN ' - ' || contentComp2.description ELSE '' END) || coalesce(currentSuffix.content ,'') as currentContent,
                                previousindicator1 ||' - '|| prevIndicator1.description as previousindicator1,
                                currentindicator1 ||' - '|| currentIndicator1.description as currentindicator1,
                                previousindicator2 ||' - '|| prevIndicator2.description as previousindicator2,
                                currentindicator2 ||' - '|| currentIndicator2.description as currentindicator2,
                                prevPrefix.content    as previousprefixid,
                                currentPrefix.content as currentprefixid,
                                prevSuffix.content as previoussuffixid,
                                currentSuffix.content as currentsuffixid,
                                previousSeparator.content as previousseparatorid,
                                currentSeparator.content as currentseparatorid,
                                data,
                                operator  "
                            );

        $this->setTables("  gtcMaterialHistory as MH
                            LEFT JOIN gtcprefixsuffix prevSuffix ON ( (prevSuffix.prefixsuffixid = MH.previoussuffixid ) AND prevSuffix.type = 2 )
                            LEFT JOIN gtcprefixsuffix prevPrefix ON ( (prevPrefix.prefixsuffixid = MH.previousprefixid ) AND prevPrefix.type = 1 )
                            LEFT JOIN gtcprefixsuffix currentSuffix ON ( (currentSuffix.prefixsuffixid = MH.currentsuffixid ) AND currentSuffix.type = 2 )
                            LEFT JOIN gtcprefixsuffix currentPrefix ON ( (currentPrefix.prefixsuffixid = MH.currentprefixid ) AND currentPrefix.type = 1 )
                            LEFT JOIN gtcseparator previousSeparator ON ( (previousSeparator.separatorid = MH.previousseparatorid ) )
                            LEFT JOIN gtcseparator currentSeparator ON ( (currentSeparator.separatorid = MH.currentseparatorid ) )
                            LEFT JOIN gtcmarctaglistingoption currentIndicator1 ON ( currentIndicator1.marctaglistingid = MH.fieldid ||'-I1' AND currentIndicator1.option = MH.currentindicator1 )
                            LEFT JOIN gtcmarctaglistingoption currentIndicator2 ON ( currentIndicator2.marctaglistingid = MH.fieldid ||'-I2' AND currentIndicator2.option = MH.currentindicator2 )
                            LEFT JOIN gtcmarctaglistingoption prevIndicator1 ON ( prevIndicator1.marctaglistingid = MH.fieldid ||'-I1' AND prevIndicator1.option = MH.previousindicator1 )
                            LEFT JOIN gtcmarctaglistingoption prevIndicator2 ON ( prevIndicator2.marctaglistingid = MH.fieldid ||'-I2' AND prevIndicator2.option = MH.previousindicator2 )
                            LEFT JOIN gtcmarctaglistingoption contentComp1 ON ( contentComp1.marctaglistingid = MH.fieldid || '.' || MH.subfieldid AND contentComp1.option = MH.previousContent )
                            LEFT JOIN gtcmarctaglistingoption contentComp2 ON ( contentComp2.marctaglistingid = MH.fieldid || '.' || MH.subfieldid AND contentComp2.option = MH.currentContent )
                            LEFT JOIN gtctag ON (gtctag.fieldid = MH.fieldid AND gtctag.subfieldid = MH.subfieldid)");

        
        if ( $this->controlNumberS )
        {
            $this->setWhere(" MH.controlnumber = ? ");
            $args[] = $this->controlNumberS;
            $doSearch = true;
        }

        if ( $this->revisionNumberS )
        {
            $this->setWhere(" MH.revisionnumber = ? ");
            $args[] = $this->revisionNumberS;
            $doSearch = true;
        }

        if ( $this->operatorS )
        {
            $this->setWhere(" upper(MH.operator) like upper(?) ");
            $args[] = '%'.$this->operatorS.'%';
            $doSearch = true;
        }

        if ( $this->chancestypeS )
        {
            $this->setWhere(" MH.chancesType = ? ");
            $args[] = $this->chancestypeS;
            $doSearch = true;
        }

        if ( $this->fieldIdS )
        {
            $this->setWhere(" MH.fieldId = ? ");
            $args[] = $this->fieldIdS;
            $doSearch = true;
        }

        if ( $this->subFieldIdS )
        {
            $this->setWhere(" MH.subFieldId = ? ");
            $args[] = $this->subFieldIdS;
            $doSearch = true;
        }

        if ( $this->currentContentS )
        {
            $this->setWhere(" upper(MH.currentContent) like upper(?) ");
            $args[] = '%'.$this->currentContentS.'%';
            $doSearch = true;
        }

        if ( $this->previousContentS )
        {
            $this->setWhere(" upper(MH.previousContent) like upper(?) ");
            $args[] = '%'.$this->previousContentS.'%';
            $doSearch = true;
        }

        if ( $this->beginDateHourS )
        {
            $date = explode (" ",$this->beginDateHourS);

            if ( $date[0] )
            {
                $this->setWhere('data::TIMESTAMP >= ?::TIMESTAMP');
                $args[] = $date[0] . ($date[1]?' '.$date[1]:' 00:00:00');
                $doSearch = true;
            }
        }

        if ( $this->endDateHourS )
        {
            $date = explode (" ",$this->endDateHourS);

            if ( $date[0] )
            {
                $this->setWhere('data::TIMESTAMP <= ?::TIMESTAMP');
                $args[] = $date[0] . ($date[1]?' '.$date[1]:' 00:00:00');
                $doSearch = true;
            }
        }
        
        if ( $doSearch )
        {
            return $this->query($this->select($args),$toObject);
        }
        else
        {
            throw new Exception( _M("É necessário adicionar algum filtro para realizar esta busca.",'gnuteca3') );
        }
    }
    
    /**
     * Seta as condições do sql
     *
     * @return string para where da consulta
     */
    public function getWhereCondition()
    {
        $where = "";

        if(!is_null($this->controlNumber))
        {
            $where.= " controlnumber = ? AND ";
        }
        if(!is_null($this->fieldId))
        {
            $where.= " fieldId = ? AND ";
        }
        if(!is_null($this->subFieldId))
        {
            $where.= " subFieldId = ? AND ";
        }

        if(strlen($where))
        {
            $where = substr($where, 0, strlen($where) - 4);
            parent::setWhere($where);
        }
    }


    function getMaterialHistory()
    {
        parent::clear();
        $this->setTables("gtcmaterialhistory");
        parent::setColumns($this->fullColumns);
        parent::setWhere($this->getWhereCondition());
        $sql = parent::select(array($this->controlNumber, $this->fieldId, $this->subFieldId));
        return parent::query($sql, true);
    }

    /**
     * insere um registro no historico
     *
     */
    public function insertMaterialHistory()
    {
        parent::clear();

        $this->setTables("gtcmaterialhistory");
        parent::setColumns($this->localColumns);
        $sql = parent::insert($this->associateData());

        return parent::Execute();
    }

    /**
     * anula todos atributos locais.
     *
     */
    public function cleanLocalVars()
    {
        $this->controlNumber        =
        $this->materialHistoryId    =
        $this->revisionNumber       =
        $this->operator             =
        $this->data                 =
        $this->chancesType          =
        $this->fieldId              =
        $this->subFieldId           =
        $this->previousLine         =
        $this->previousIndicator1   =
        $this->previousIndicator2   =
        $this->previousContent      =
        $this->currentLine          =
        $this->currentIndicator1    =
        $this->currentIndicator2    =
        $this->currentContent       =
        $this->previousprefixid     =
        $this->previoussuffixid     =
        $this->previousseparatorid  =
        $this->currentprefixid      =
        $this->currentsuffixid      =
        $this->currentseparatorid = null;
    }

    
    function clean()
    {
        $this->cleanLocalVars();
    }

    /**
     * retorna o numero da proxima revisao
     *
     * @param integer $controlNumber
     * @return integer
     */
    function getNextRevision($controlNumber = null)
    {
        if(is_null($controlNumber) && !is_null($this->controlNumber))
        {
            $controlNumber = $this->controlNumber;
        }
        if(!$controlNumber)
        {
            return false;
        }

        parent::clear();
        $this->setTables("gtcmaterialhistory");
        parent::setColumns("MAX(revisionnumber)");
        parent::setWhere("controlNumber = ?");
        $sql = parent::select(array($controlNumber));
        $r = parent::query();

        if(!$r)
        {
            return 1;
        }

        return ($r[0][0] + 1);
    }


    function checkHistory()
    {
        parent::clear();
        $this->setTables("gtcmaterialhistory");
        parent::setColumns("revisionnumber");
        parent::setWhere($this->getWhereCondition());
        $sql = parent::select(array($this->controlNumber, $this->fieldId, $this->subFieldId));
        return parent::query($sql);
    }
    
    /**
     * Retorna o último operador que alterou um material.
     * @param int $controlNumber
     * @return string $rs[0][0]
     */
    public function lastOperator ($controlNumber)
    {
        $rs = $this->query("SELECT distinct(operator) FROM gtcmaterialhistory WHERE revisionnumber = (SELECT max(revisionnumber) FROM gtcmaterialhistory WHERE controlnumber = {$controlNumber}) AND controlnumber={$controlNumber}");
        return $rs[0][0];
    }

    /**
     * Retorna o operador que criou um material.
     * @param int $controlNumber
     * @return string $rs[0][0]
     */
    public function firstOperator ($controlNumber)
    {
        $rs = $this->query("SELECT distinct(operator) FROM gtcmaterialhistory WHERE revisionnumber = (SELECT min(revisionnumber) FROM gtcmaterialhistory WHERE controlnumber = {$controlNumber}) AND controlnumber={$controlNumber}");
        return $rs[0][0];
    }
    
    /**
     * Insere histórico de exclusão de material
     * 
     * @param (integer) número de controle
     * @return (boolean) true caso for executado com sucesso
     */
    public function insertMaterialHistoryForDeleteMaterial($controlNumber)
    {
        if ( !$controlNumber )
        {
            return false;
        }
        
        $nextRevision = $this->getNextRevision($controlNumber);
        $operator = GOperator::getOperatorId();
        $data = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        
        return $this->query("INSERT INTO gtcmaterialhistory ({$this->localColumns}) (SELECT controlNumber, '{$nextRevision}', '{$operator}', '{$data}', 'D', fieldid, subfieldid, line, indicator1, indicator2, content, line, null, null, null, prefixid, suffixid, separatorid, null, null, null FROM gtcmaterial WHERE controlnumber = '{$controlNumber}')");
    }

    /**
     * Retorna um array com tags que já foram moficadas para este material
     *
     * @param integer $controlNumber
     * @return array com tags que já foram moficadas para este material
     */
    public function listModifiedTagsForMaterial( $controlNumber )
    {
        if ( !$controlNumber )
        {
            return null;
        }
        
        return $this->query("SELECT distinct fieldid , subfieldid FROM gtcmaterialhistory WHERE controlnumber = '$controlNumber'");
    }

    /**
     * Retorna array pronto para gerar um GSelection com tipos constantes de manipulação de materiais.
     * 
     * @return (array)
     */
    public function listChangeTypes()
    {
    	$changeTypes = array(
                        self::CHANGETYPE_INSERT => _M('Inserido', $this->module),
                        self::CHANGETYPE_UPDATE => _M('Alterado', $this->module),
                        self::CHANGETYPE_DELETE => _M('Deletado', $this->module)
                            );

        return $changeTypes;
    }
} 
?>
