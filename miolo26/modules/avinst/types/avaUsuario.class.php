<?php

/**
 * Type que repesenta a tabela ava_usuario.
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
class avaUsuario implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idUsuario;
    /**
     * @AttributeType text
     * 
     */
    protected $nome;
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
        $this->idUsuario = $data->idUsuario;
        $this->nome = $data->nome;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_usuario,
                       nome
                  FROM ava_usuario
                 WHERE id_usuario = ?';
        $result = ADatabase::query($sql, array($this->idUsuario));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idUsuario, $this->nome) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_usuario,
                       nome
                  FROM ava_usuario';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_usuario ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_usuario 
                            (id_usuario, nome)
                     VALUES (?, ?)';
        $idUsuario = ADatabase::nextVal('ava_usuario_id_usuario_seq');
        $params = array($idUsuario, $this->nome);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idUsuario = $idUsuario;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_usuario
                   SET nome = ?
                 WHERE id_usuario = ?';
        $params = array($this->nome, $this->idUsuario);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idUsuario)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_usuario
                      WHERE id_usuario = ?';
        $params = array($this->idUsuario);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idUsuario = null;
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
        return 'idUsuario';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idUsuario'] = new stdClass();
        $attributesInfo['idUsuario']->columnName = 'id_usuario';
        $attributesInfo['idUsuario']->type = 'integer';
        $attributesInfo['nome'] = new stdClass();
        $attributesInfo['nome']->columnName = 'nome';
        $attributesInfo['nome']->type = 'text';
        return $attributesInfo;
    }
}


?>