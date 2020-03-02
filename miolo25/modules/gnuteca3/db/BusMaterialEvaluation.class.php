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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/06/2011
 *
 * */
$MIOLO = MIOLO::getInstance();
$MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');

class BusinessGnuteca3BusMaterialEvaluation extends GBusiness
{

    /**
     * Código da avaliação de material
     * @var integer
     */
    public $materialEvaluationId;

    /**
     * Número de controle
     * @var integer
     */
    public $controlNumber;

    /**
     * Código da pessoa
     * @var integer
     */
    public $personId;

    /**
     * Data
     * @var timestamp
     */
    public $date;

    /**
     * Hora
     * @var timestamp
     */
    public $time;

    /**
     * Comentário
     * @var string
     */
    public $comment;

    /**
     * Avaliação/nota
     * @var integer
     */
    public $evaluation;

    // campos de busca

    /**
     * Código da avaliação de material
     * @var integer
     */
    public $materialEvaluationIdS;

    /**
     * Número de controle
     * @var integer
     */
    public $controlNumberS;

    /**
     * Código da pessoa
     * @var integer
     */
    public $personIdS;

    /**
     * Data 
     * @var timestamp
     */
    public $beginDateS;
    public $endDateS;

    /**
     * Hora
     * @var timestamp
     */
    public $endTimeS;
    public $beginTimeS;

    /**
     * Comentário
     * @var string
     */
    public $commentS;

    /**
     * Avaliação/nota
     * @var integer
     */
    public $evaluationS;

    public function __construct()
    {
        $table = 'gtcMaterialEvaluation';
        $pkeys = 'materialEvaluationId ';
        $cols = 'controlNumber, personId, date, comment, evaluation';
        parent::__construct($table, $pkeys, $cols);
    }

    public function insertMaterialEvaluation()
    {
        if (!$this->validControlNumber($this->controlNumber))
        {
            throw new Exception(_M('Número de controle inválido.', 'gnuteca3'));
        }
        if ($this->date && $this->time)
        {
            $this->date .= " " . $this->time;
        }
        return $this->autoInsert();
    }

    public function updateMaterialEvaluation()
    {
        if (!$this->validControlNumber($this->controlNumber))
        {
            throw new Exception(_M('Número de controle inválido.', 'gnuteca3'));
        }
        if ($this->date && $this->time)
        {
            $this->date .= " " . $this->time;
        }

        return $this->autoUpdate();
    }

    public function getMaterialEvaluation($materialEvaluationId)
    {
        $this->clear();
        $materialEvaluation = $this->autoGet($materialEvaluationId);
        $time = new GDate($materialEvaluation->date);
        $this->time = $time->getHour() . ":" . $time->getMinute(); //Define manualmente a hora no campo time

        return $materialEvaluation;
    }

    public function deleteMaterialEvaluation($materialEvaluationId)
    {
        return $this->autoDelete($materialEvaluationId);
    }

    public function searchMaterialEvaluation($toObject = FALSE)
    {
        $this->materialEvaluationId = $this->controlNumber = $this->personId = null; //Limpa resquicios do update/insert

        $filters = array(
            'materialEvaluationId' => 'equals',
            'controlNumber' => 'equals',
            'comment' => 'ilike',
            'evaluation' => 'equals',
        );

        $args = $this->addFilters($filters);

        if ($this->beginDateS)
        {
            $this->setWhere('date >= ?');
            $args[] = "{$this->beginDateS} " . ($this->beginTimeS ? $this->beginTimeS : '00:00');
        }

        if ($this->endDateS)
        {
            $this->setWhere('date <= ?');
            $args[] = "{$this->endDateS} " . ($this->endTimeS ? $this->endTimeS : '00:00');
        }

        if ($this->beginTimeS)
        {
            $this->setWhere(' date::time >= ?::time ');
            $args[] = $this->beginTimeS;
        }

        if ($this->endTimeS)
        {

            $this->setWhere(' date::time <= ?::time ');
            $args[] = $this->endTimeS;
        }

        if ($this->personIdS)
        {
            $this->setWhere("e.personId = {$this->personIdS} ");
        }

        $this->setTables('gtcMaterialEvaluation e LEFT JOIN ONLY basPerson p ON ( e.personId = p.personId) ');
        $this->setColumns('materialEvaluationId, controlNumber, e.personId, p.name, date, comment, evaluation');
        return $this->query($this->select($args), $toObject);
    }

    /**
     * Lista avaliações com comentário
     *
     * @param integer $controlNumber
     * @param boolean $toObject
     * @return array
     */
    public function listEvalutionWithComment($controlNumber, $toObject = false)
    {
        //não retorna nada caso não passe o número de controle
        if (!$controlNumber)
        {
            return false;
        }

        $this->setWhere("comment <> '' ");
        $this->controlNumberS = $controlNumber;

        //SELECT materialEvaluationId,controlNumber,e.personId,p.name, date,comment,evaluation FROM gtcMaterialEvaluation e LEFT JOIN basPerson p ON ( p.personId = e.personId ) WHERE comment <> ''  and controlNumber = '54545';
        $this->columns = 'materialEvaluationId,controlNumber,e.personId,p.name, date,comment,evaluation';
        $this->tables = 'gtcMaterialEvaluation e LEFT JOIN ONLY basPerson p ON ( p.personId = e.personId )';

        $this->setOrderBy(' date desc ');
        return $this->searchMaterialEvaluation($toObject);
    }

    /**
     * Obtem a nota média do material
     *
     * @param integer $controlNumber
     * @return array [0] - registros que foram considerados na média, [1] média calculada
     */
    public function getAverage($controlNumber)
    {
        if (!$controlNumber)
        {
            return array(0, 0);
        }

        $sql = "SELECT   count(evaluation),
                        avg( evaluation )::int
                 FROM   gtcmaterialevaluation
                WHERE   controlnumber = {$controlNumber}
                  AND   ( evaluation > 0 or evaluation is not null ) ";

        return $this->query($sql);
    }

    public function validControlNumber($controlNumber)
    {
        $busMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $rs = $busMaterialControl->getMaterialControl($controlNumber, true);
        if ($rs)
        {
            return true;
        }
        return false;
    }

}

?>