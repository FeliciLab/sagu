<?php

/**
 * Type que repesenta a tabela ava_atributos.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 11/01/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class avaAtributos implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idAtributos;
    /**
     * @AttributeType integer
     * 
     */
    protected $refResposta;
    /**
     * @AttributeType integer
     * 
     */
    protected $chave;
    /**
     * @AttributeType text
     * 
     */
    protected $valor;
    
    public function __construct($data = null,  $populate = false)
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
        $this->idAtributos = $data->idAtributos;
        $this->refResposta = $data->refResposta;
        $this->chave = $data->chave;
        $this->valor = $data->valor;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_atributos,
                       ref_resposta,
                       chave,
                       valor
                  FROM ava_atributos
                 WHERE id_atributos = ?';
        $result = ADatabase::query($sql, array($this->idAtributos));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idAtributos, $this->refResposta, $this->chave, $this->valor) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_atributos,
                       ref_resposta,
                       chave,
                       valor
                  FROM ava_atributos';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_atributos ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $idAtributos = ADatabase::nextVal('ava_atributos_id_atributos_seq');
        $refResposta = $this->refResposta;
        $chave = $this->chave;
        $valor = strlen($this->valor) > 0 ? $this->valor : 'NULL';
        
        $sql = "INSERT INTO ava_atributos 
                            (id_atributos, ref_resposta, chave, valor)
                     VALUES ($idAtributos, $refResposta, $$$chave$$, $$$valor$$)";
        
        $result = ADatabase::execute($sql);

        if ( $result )
        {
            $this->idAtributos = $idAtributos;
        }

        return $result;
    }

    public function update()
    {
        $idAtributos = $this->idAtributos;
        $refResposta = $this->refResposta;
        $chave = $this->chave;
        $valor = strlen($this->valor) > 0 ? $this->valor : 'NULL';
        
        $sql = "UPDATE ava_atributos
                   SET ref_resposta = $refResposta,
                       chave = $$$chave$$,
                       valor = $$$valor$$
                 WHERE id_atributos = $idAtributos";

        return ADatabase::execute($sql);
    }

    public function delete()
    {
        if ( strlen($this->idAtributos)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_atributos
                      WHERE id_atributos = ?';
        $params = array($this->idAtributos);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idAtributos = null;
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
        return 'idAtributos';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idAtributos'] = new stdClass();
        $attributesInfo['idAtributos']->columnName = 'id_atributos';
        $attributesInfo['idAtributos']->type = 'integer';
        $attributesInfo['refResposta'] = new stdClass();
        $attributesInfo['refResposta']->columnName = 'ref_resposta';
        $attributesInfo['refResposta']->type = 'integer';
        $attributesInfo['chave'] = new stdClass();
        $attributesInfo['chave']->columnName = 'chave';
        $attributesInfo['chave']->type = 'integer';
        $attributesInfo['valor'] = new stdClass();
        $attributesInfo['valor']->columnName = 'valor';
        $attributesInfo['valor']->type = 'text';
        return $attributesInfo;
    }
}


?>