<?php

/**
 * Type que repesenta a tabela ava_servico.
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
class avaServico implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idServico;
    /**
     * @AttributeType text
     * 
     */
    protected $descricao;
    /**
     * @AttributeType text
     * 
     */
    protected $localizacao;
    /**
     * @AttributeType text
     * 
     */
    protected $metodo;
    /**
     * @AttributeType text
     * 
     */
    protected $parametros;
    /**
     * @AttributeType text
     * 
     */
    protected $atributos;
    
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
        $this->idServico = is_numeric($data->idServico) ? $data->idServico : 0;
        $this->descricao = $data->descricao;
        $this->localizacao = $data->localizacao;
        $this->metodo = $data->metodo;
        $this->parametros = $data->parametros;
        $this->atributos = $data->atributos;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_servico,
                       descricao,
                       localizacao,
                       metodo,
                       parametros,
                       atributos
                  FROM ava_servico
                 WHERE id_servico = ?';
        $result = ADatabase::query($sql, array($this->idServico));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idServico, $this->descricao, $this->localizacao, $this->metodo, $this->parametros, $this->atributos) = $result[0];
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_servico,
                       descricao,
                       localizacao,
                       metodo,
                       parametros,
                       atributos
                  FROM ava_servico';
        
        if ( $this->idServico == 0 )
        {
            $this->idServico = NULL;
        }
        
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_servico ';
        
        if( $returnType  ==  ADatabase::RETURN_SQL )
        {
            return $sql;
        }
        
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_servico 
                            (id_servico, descricao, localizacao, metodo, parametros, atributos)
                     VALUES (?, ?, ?, ?, ?, ?)';
        $idServico = ADatabase::nextVal('ava_servico_id_servico_seq');
        $params = array($idServico, $this->descricao, $this->localizacao, $this->metodo, $this->parametros, $this->atributos);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idServico = $idServico;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_servico
                   SET descricao = ?,
                       localizacao = ?,
                       metodo = ?,
                       parametros = ?,
                       atributos = ?
                 WHERE id_servico = ?';
        $params = array($this->descricao, $this->localizacao, $this->metodo, $this->parametros, $this->atributos, $this->idServico);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idServico)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_servico
                      WHERE id_servico = ?';
        $params = array($this->idServico);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idServico = null;
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
        return 'idServico';
    }

    //
    // Colocar informações dos serviços
    //
    public function generateAttributesInfo()
    {
        $attributesInfo['idServico'] = new stdClass();
        $attributesInfo['idServico']->columnName = 'id_servico';
        $attributesInfo['idServico']->type = 'integer';
        $attributesInfo['descricao'] = new stdClass();
        $attributesInfo['descricao']->columnName = 'descricao';
        $attributesInfo['descricao']->type = 'text';
        $attributesInfo['localizacao'] = new stdClass();
        $attributesInfo['localizacao']->columnName = 'localizacao';
        $attributesInfo['localizacao']->type = 'text';
        $attributesInfo['metodo'] = new stdClass();
        $attributesInfo['metodo']->columnName = 'metodo';
        $attributesInfo['metodo']->type = 'text';
        $attributesInfo['parametros'] = new stdClass();
        $attributesInfo['parametros']->columnName = 'parametros';
        $attributesInfo['parametros']->type = 'text';
        $attributesInfo['atributos'] = new stdClass();
        $attributesInfo['atributos']->columnName = 'atributos';
        $attributesInfo['atributos']->type = 'text';
        return $attributesInfo;
    }
    
    //
    //
    //
    public function chamaServico($parametros = null, $cache = true)
    {
        $MIOLO = MIOLO::getInstance();
        $ws = $MIOLO->getWebServices('avinst','wsCoreAvinst');
        $parametrosParsing = explode(';', $this->parametros);

        foreach ($parametrosParsing as $parametro)
        {
            if (isset($parametros[$parametro]))
            {
                $parametrosParse[] = $parametros[$parametro];
            }
            elseif (  substr ( $parametro, 0, 1 ) != '$')
            {
                $parametrosParse[] = $parametro;
            }
        }
        // Terminou o parse, executa a chamada
        try
        {
            $retorno = $ws->chamaServico($this->localizacao, $this->metodo, $parametrosParse, $cache);
            if ( $retorno === null )
            {
                $retorno = _M('Não houve retorno deste serviço.');
            }
            return $retorno;
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }
}
?>
