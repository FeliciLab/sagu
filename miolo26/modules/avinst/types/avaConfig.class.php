<?php

/**
 * Type que repesenta a tabela ava_config.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 23/11/2011
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
class avaConfig implements AType
{
    /**
     * @AttributeType text
     * 
     */
    protected $chave_;
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
        $this->chave_ = $data->chave_;
        $this->valor = $data->valor;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT chave,
                       valor
                  FROM ava_config
                 WHERE chave = ?';
        $result = ADatabase::query($sql, array($this->chave_));

        if ( !strlen($result[0][0]) )
        {
            //throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->chave_, $this->valor) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT chave,
                       valor
                  FROM ava_config';
        $where.= ADatabase::generateFilters($this);
        
        $where = str_replace("chave_", "chave", $where);

        if ( strlen($where) > 0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY chave ';
        $result = ADatabase::query($sql);

        if ( $returnType == ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_config 
                            (chave, valor)
                     VALUES (?, ?)';
        $params = array($this->chave_, $this->valor);
        $result = ADatabase::execute($sql, $params);
        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_config
                   SET valor = ?
                 WHERE chave = ?';
        $params = array($this->valor, $this->chave_);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->chave_)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_config
                      WHERE chave = ?';
        $params = array($this->chave_);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->chave_ = null;
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
        return 'chave_';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['chave_'] = new stdClass();
        $attributesInfo['chave_']->columnName = 'chave_';
        $attributesInfo['chave_']->type = 'text';
        $attributesInfo['valor'] = new stdClass();
        $attributesInfo['valor']->columnName = 'valor';
        $attributesInfo['valor']->type = 'text';
        return $attributesInfo;
    }
}


?>