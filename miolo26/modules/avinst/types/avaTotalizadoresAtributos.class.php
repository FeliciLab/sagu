<?php

/**
 * Type que repesenta a tabela ava_totalizadores_atributos.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 23/02/2012
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
class avaTotalizadoresAtributos implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idTotalizadorAtributo;
    /**
     * @AttributeType integer
     * 
     */
    protected $refTotalizador;
    /**
     * @AttributeType text
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
        $this->idTotalizadorAtributo = $data->idTotalizadorAtributo;
        $this->refTotalizador = $data->refTotalizador;
        $this->chave = $data->chave;
        $this->valor = $data->valor;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_totalizador_atributo,
                       ref_totalizador,
                       chave,
                       valor
                  FROM ava_totalizadores_atributos
                 WHERE id_totalizador_atributo = ?';
        $result = ADatabase::query($sql, array($this->idTotalizadorAtributo));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idTotalizadorAtributo, $this->refTotalizador, $this->chave, $this->valor) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_totalizador_atributo,
                       ref_totalizador,
                       chave,
                       valor
                  FROM ava_totalizadores_atributos';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_totalizador_atributo ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_totalizadores_atributos 
                            (id_totalizador_atributo, ref_totalizador, chave, valor)
                     VALUES (?, ?, ?, ?)';
        $idTotalizadorAtributo = ADatabase::nextVal('ava_estatisticas_atributos_id_estatistica_atributo_seq');
        $params = array($idTotalizadorAtributo, $this->refTotalizador, $this->chave, $this->valor);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idTotalizadorAtributo = $idTotalizadorAtributo;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_totalizadores_atributos
                   SET ref_totalizador = ?,
                       chave = ?,
                       valor = ?
                 WHERE id_totalizador_atributo = ?';
        $params = array($this->refTotalizador, $this->chave, $this->valor, $this->idTotalizadorAtributo);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idTotalizadorAtributo)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_totalizadores_atributos
                      WHERE id_totalizador_atributo = ?';
        $params = array($this->idTotalizadorAtributo);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idTotalizadorAtributo = null;
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
        return 'idTotalizadorAtributo';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idTotalizadorAtributo'] = new stdClass();
        $attributesInfo['idTotalizadorAtributo']->columnName = 'id_totalizador_atributo';
        $attributesInfo['idTotalizadorAtributo']->type = 'integer';
        $attributesInfo['refTotalizador'] = new stdClass();
        $attributesInfo['refTotalizador']->columnName = 'ref_totalizador';
        $attributesInfo['refTotalizador']->type = 'integer';
        $attributesInfo['chave'] = new stdClass();
        $attributesInfo['chave']->columnName = 'chave';
        $attributesInfo['chave']->type = 'text';
        $attributesInfo['valor'] = new stdClass();
        $attributesInfo['valor']->columnName = 'valor';
        $attributesInfo['valor']->type = 'text';
        return $attributesInfo;
    }
}


?>