<?php

/**
 * Type que repesenta a tabela ava_granularidade.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 18/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class avaGranularidade implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idGranularidade;
    /**
     * @AttributeType text
     * 
     */
    protected $descricao;
    /**
     * @AttributeType integer
     * 
     */
    protected $refServico;
    /**
     * @AttributeType integer
     */
    protected $tipo;
    /**
     * @AttributeType string
     */
    protected $opcoes;
    
     /**
     * @AttributeType integer
     */
    protected $tipoGranularidade;
    
    
    public function __construct($data = null, $populate = false)
    {
        if ( ! empty($data) )
        {
            $this->defineData($data);

            if ( $populate )
            {
                $this->populate();
            }
        }
    }

    public function defineData($data)
    {
        $this->idGranularidade = $data->idGranularidade;
        $this->descricao = $data->descricao;
        $this->refServico = $data->refServico;        
        $this->tipo = $data->tipo;
        $this->opcoes = $data->opcoes;
        $this->tipoGranularidade = $data->tipoGranularidade;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_granularidade,
                       descricao,
                       ref_servico,
                       tipo,
                       opcoes,
                       tipo_granularidade
                  FROM ava_granularidade
                 WHERE id_granularidade = ?';
        $result = ADatabase::query($sql, array($this->idGranularidade));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idGranularidade, $this->descricao, $this->refServico, $this->tipo, $this->opcoes, $this->tipoGranularidade) = $result[0];
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_granularidade,
                       descricao,
                       ref_servico,
                       tipo,
                       opcoes,
                       tipo_granularidade
                  FROM ava_granularidade';
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_granularidade ';
        
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_granularidade 
                            (id_granularidade, descricao, ref_servico, tipo, opcoes, tipo_granularidade)
                     VALUES (?, ?, ?, ?, ?, ?)';
        $idGranularidade = ADatabase::nextVal('ava_granularidade_id_granularidade_seq');
        $params = array($idGranularidade, $this->descricao, $this->refServico, $this->tipo, $this->opcoes, $this->tipoGranularidade);
        
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idGranularidade = $idGranularidade;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_granularidade
                   SET descricao = ?,
                       ref_servico = ?,
                       tipo = ?,
                       opcoes = ?,
                       tipo_granularidade = ?
                 WHERE id_granularidade = ?';
        $params = array($this->descricao, $this->refServico, $this->tipo, $this->opcoes, $this->tipoGranularidade, $this->idGranularidade);
        return ADatabase::execute($sql, $params);
    }
    
    /**
     * Atualiza o tipo de granularidade.
     * 
     * @param integer $idGranularidade Código da granularidade.
     * @param int $tipo Tipo da granularidade.
     * @return boolean Retorna positivo caso tenha conseguido atualizar.
     */
    public static function updateTipoDeGranularidade($idGranularidade, $tipo)
    {
        $sql = 'UPDATE ava_granularidade
                   SET tipo_granularidade = ?
                 WHERE id_granularidade = ?';
        $params = array($tipo, $idGranularidade);
        
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idGranularidade)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_granularidade
                      WHERE id_granularidade = ?';
        $params = array($this->idGranularidade);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idGranularidade = null;
        }

        return $result;
    }

    public function __set($attribute,  $value)
    {
        $this->$attribute = $value;
    }

    public function __get($attribute)
    {
        return $this->$attribute;
    }

    public function getPrimaryKeyAttribute()
    {
        return 'idGranularidade';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idGranularidade'] = new stdClass();
        $attributesInfo['idGranularidade']->columnName = 'id_granularidade';
        $attributesInfo['idGranularidade']->type = 'integer';
        $attributesInfo['descricao'] = new stdClass();
        $attributesInfo['descricao']->columnName = 'descricao';
        $attributesInfo['descricao']->type = 'text';
        $attributesInfo['refServico'] = new stdClass();
        $attributesInfo['refServico']->columnName = 'ref_servico';
        $attributesInfo['refServico']->type = 'integer';
        $attributesInfo['tipo'] = new stdClass();
        $attributesInfo['tipo']->columnName = 'tipo';
        $attributesInfo['tipo']->type = 'integer';
        $attributesInfo['opcoes'] = new stdClass();
        $attributesInfo['opcoes']->columnName = 'opcoes';
        $attributesInfo['opcoes']->type = 'string';
        $attributesInfo['tipoGranularidade'] = new stdClass();
        $attributesInfo['tipoGranularidade']->columnName = 'tipo_granularidade';
        $attributesInfo['tipoGranularidade']->type = 'integer';
        
        return $attributesInfo;
    }
    
    /*
     * Função para obter as regras de autorização ou não do formulário
     */
    public function obtemGraos($parametros = null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaServico.class.php', 'avinst');
        $data = new stdClass();
        $data->idServico = $this->refServico;
        $servico = new avaServico($data, true);
        $retorno = $servico->chamaServico($parametros);
        
        return $retorno;
    }
}
?>