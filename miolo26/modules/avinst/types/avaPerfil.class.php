<?php

/**
 * Type que repesenta a tabela ava_perfil.
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
class avaPerfil implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idPerfil;
    /**
     * @AttributeType text
     * 
     */
    protected $descricao;
    /**
     * @AttributeType integer
     * 
     */
    protected $tipo;
    /**
     * @AttributeType boolean
     * 
     */
    protected $avaliavel;
    /**
     * @AttributeType integer
     * 
     */
    protected $posicao;
    
    const TIPO_COORDENADOR = 'COORDENADORES';
    const TIPO_PROFESSOR = 'PROFESSORES';
    const TIPO_ALUNO = 'ALUNOS';
    
    
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
        $this->idPerfil = is_numeric($data->idPerfil) ? $data->idPerfil : 0;
        $this->descricao = $data->descricao;
        $this->tipo = $data->tipo;
        $this->avaliavel = $data->avaliavel;
        $this->posicao = strlen(trim($data->posicao)) > 0 ? $data->posicao : null;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_perfil,
                       descricao,
                       tipo,
                       avaliavel,
                       posicao
                  FROM ava_perfil
                 WHERE id_perfil = ?';
        $result = ADatabase::query($sql, array($this->idPerfil));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idPerfil, $this->descricao, $this->tipo, $this->avaliavel, $this->posicao) = $result[0];
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_perfil,
                       descricao,
                       tipo,
                       avaliavel,
                       posicao
                  FROM ava_perfil';
        
        if ( $this->idPerfil == 0 )
        {
            $this->idPerfil = NULL;
        }
        
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY posicao ';
        
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
        $sql = 'INSERT INTO ava_perfil 
                            (id_perfil, descricao, tipo, avaliavel, posicao)
                     VALUES (?, ?, ?, ?, ?)';
        
        $idPerfil = ADatabase::nextVal('ava_perfil_id_perfil_seq');
        $params = array($idPerfil, $this->descricao, $this->tipo, $this->avaliavel, $this->posicao);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idPerfil = $idPerfil;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_perfil
                   SET descricao = ?,
                       tipo = ?,
                       avaliavel = ?,
                       posicao = ?
                 WHERE id_perfil = ?';
        $params = array($this->descricao, $this->tipo, $this->avaliavel, $this->posicao, $this->idPerfil);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idPerfil)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_perfil
                      WHERE id_perfil = ?';
        $params = array($this->idPerfil);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idPerfil = null;
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
        return 'idPerfil';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idPerfil'] = new stdClass();
        $attributesInfo['idPerfil']->columnName = 'id_perfil';
        $attributesInfo['idPerfil']->type = 'integer';
        $attributesInfo['descricao'] = new stdClass();
        $attributesInfo['descricao']->columnName = 'descricao';
        $attributesInfo['descricao']->type = 'text';
        $attributesInfo['tipo'] = new stdClass();
        $attributesInfo['tipo']->columnName = 'tipo';
        $attributesInfo['tipo']->type = 'text';
        $attributesInfo['avaliavel'] = new stdClass();
        $attributesInfo['avaliavel']->columnName = 'avaliavel';
        $attributesInfo['avaliavel']->type = 'boolean';
        $attributesInfo['posicao'] = new stdClass();
        $attributesInfo['posicao']->columnName = 'posicao';
        $attributesInfo['posicao']->type = 'integer';
        return $attributesInfo;
    }
}
?>