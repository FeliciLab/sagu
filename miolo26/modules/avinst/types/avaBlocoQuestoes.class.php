<?php

/**
 * Type que repesenta a tabela ava_bloco_questoes.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 24/11/2011
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
$MIOLO->uses( "types/avaQuestoes.class.php", 'avinst' );

class avaBlocoQuestoes implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idBlocoQuestoes;
    /**
     * @AttributeType integer
     * 
     */
    protected $refBloco;
    /**
     * @AttributeType integer
     * 
     */
    protected $refQuestao;
    /**
     * @AttributeType integer
     * 
     */
    protected $ordem;
    /**
     * @AttributeType boolean
     * 
     */
    protected $obrigatorio;
    /**
     * @AttributeType boolean
     * 
     */
    protected $ativo;
    /**
     *
     * @AttributeType integer 
     */
    protected $categoriaId;
    
    
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
        $this->idBlocoQuestoes = $data->idBlocoQuestoes;
        $this->refBloco = $data->refBloco;
        $this->refQuestao = $data->refQuestao;
        $this->ordem = $data->ordem;
        $this->obrigatorio = $data->obrigatorio;
        $this->ativo = $data->ativo;
        $this->categoriaId = strlen($data->categoriaId) > 0 ? $data->categoriaId : null;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_bloco_questoes,
                       ref_bloco,
                       ref_questao,
                       ordem,
                       obrigatorio,
                       ativo,
                       categoriaId
                  FROM ava_bloco_questoes
                 WHERE id_bloco_questoes = ?';
        $result = ADatabase::query($sql, array($this->idBlocoQuestoes));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idBlocoQuestoes, $this->refBloco, $this->refQuestao, $this->ordem, $this->obrigatorio, $this->ativo, $this->categoriaId) = $result[0];
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_bloco_questoes,
                       ref_bloco,
                       ref_questao,
                       ordem,
                       obrigatorio,
                       ativo,
                       categoriaId
                  FROM ava_bloco_questoes';
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY ordem ';
        $result = ADatabase::query($sql);
        
        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
            
            foreach ( $result as $key => $blocoQuestao )
            {
                $objectQuestoes->idQuestoes = $blocoQuestao->refQuestao; 
                $result[$key]->questao = $typeQuestoes = new avaQuestoes($objectQuestoes,true);
            }
        }
            
        return $result;
    }

    public function insert()
    {
        // Verifica se a categoria está cadastrada para a avaliação
        $args = new stdClass();
        $args->categoriaId = $this->categoriaId;
        $args->refBloco = $this->refBloco;
        $this->categoriaId = avaCategoriaAvaliacao::verificaCategoriaPeloBloco($args) ? $this->categoriaId : null;
        
        $sql = 'INSERT INTO ava_bloco_questoes 
                            (id_bloco_questoes, ref_bloco, ref_questao, ordem, obrigatorio, ativo, categoriaId)
                     VALUES (?, ?, ?, ?, ?, ?, ?)';
        $idBlocoQuestoes = ADatabase::nextVal('ava_bloco_questoes_id_bloco_questoes_seq');
        $params = array($idBlocoQuestoes, $this->refBloco, $this->refQuestao, $this->ordem, $this->obrigatorio, $this->ativo, $this->categoriaId);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idBlocoQuestoes = $idBlocoQuestoes;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_bloco_questoes
                   SET ref_bloco = ?,
                       ref_questao = ?,
                       ordem = ?,
                       obrigatorio = ?,
                       ativo = ?,
                       categoriaId = ?
                 WHERE id_bloco_questoes = ?';
        $params = array($this->refBloco, $this->refQuestao, $this->ordem, $this->obrigatorio, $this->ativo, $this->categoriaId, $this->idBlocoQuestoes);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idBlocoQuestoes)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_bloco_questoes
                      WHERE id_bloco_questoes = ?';
        $params = array($this->idBlocoQuestoes);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idBlocoQuestoes = null;
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
        return 'idBlocoQuestoes';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idBlocoQuestoes'] = new stdClass();
        $attributesInfo['idBlocoQuestoes']->columnName = 'id_bloco_questoes';
        $attributesInfo['idBlocoQuestoes']->type = 'integer';
        
        $attributesInfo['refBloco'] = new stdClass();
        $attributesInfo['refBloco']->columnName = 'ref_bloco';
        $attributesInfo['refBloco']->type = 'integer';
        
        $attributesInfo['refQuestao'] = new stdClass();
        $attributesInfo['refQuestao']->columnName = 'ref_questao';
        $attributesInfo['refQuestao']->type = 'integer';
        
        $attributesInfo['ordem'] = new stdClass();
        $attributesInfo['ordem']->columnName = 'ordem';
        $attributesInfo['ordem']->type = 'integer';
        
        $attributesInfo['obrigatorio'] = new stdClass();
        $attributesInfo['obrigatorio']->columnName = 'obrigatorio';
        $attributesInfo['obrigatorio']->type = 'boolean';
        
        $attributesInfo['ativo'] = new stdClass();
        $attributesInfo['ativo']->columnName = 'ativo';
        $attributesInfo['ativo']->type = 'boolean';
        
        $attributesInfo['categoriaId'] = new stdClass();
        $attributesInfo['categoriaId']->columnName = 'categoriaId';
        $attributesInfo['categoriaId']->type = 'integer';
        return $attributesInfo;
    }
}
?>
