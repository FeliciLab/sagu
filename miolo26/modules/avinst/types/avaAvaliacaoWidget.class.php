<?php

/**
 * Type que repesenta a tabela ava_avaliacao_widget.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 27/03/2012
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
class avaAvaliacaoWidget implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idAvaliacaoWidget;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliacao;
    /**
     * @AttributeType character varying
     * 
     */
    protected $refWidget;
    /**
     * @AttributeType text
     * 
     */
    protected $opcoes;
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
        $this->idAvaliacaoWidget = $data->idAvaliacaoWidget;
        $this->refAvaliacao = $data->refAvaliacao;
        $this->refWidget = $data->refWidget;
        $this->opcoes = $data->opcoes;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_avaliacao_widget,
                       ref_avaliacao,
                       ref_widget,
                       opcoes
                  FROM ava_avaliacao_widget
                 WHERE id_avaliacao_widget = ?';
        $result = ADatabase::query($sql, array($this->idAvaliacaoWidget));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idAvaliacaoWidget, $this->refAvaliacao, $this->refWidget, $this->opcoes) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_avaliacao_widget,
                       ref_avaliacao,
                       ref_widget,
                       opcoes
                  FROM ava_avaliacao_widget';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_avaliacao_widget ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_avaliacao_widget 
                            (id_avaliacao_widget, ref_avaliacao, ref_widget, opcoes)
                     VALUES (?, ?, ?, ?)';
        $idAvaliacaoWidget = ADatabase::nextVal('ava_avaliacao_widget_id_avaliacao_widget_seq');
        $params = array($idAvaliacaoWidget, $this->refAvaliacao, $this->refWidget, $this->opcoes);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idAvaliacaoWidget = $idAvaliacaoWidget;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_avaliacao_widget
                   SET ref_avaliacao = ?,
                       ref_widget = ?,
                       opcoes = ?
                 WHERE id_avaliacao_widget = ?';
        $params = array($this->refAvaliacao, $this->refWidget, $this->opcoes, $this->idAvaliacaoWidget);
        return ADatabase::execute($sql, $params);
    }

    //
    //
    //
    public function delete()
    {
        if ( strlen($this->idAvaliacaoWidget)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_avaliacao_widget
                      WHERE id_avaliacao_widget = ?';
        $params = array($this->idAvaliacaoWidget);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idAvaliacaoWidget = null;
        }

        return $result;
    }

    //
    // Apaga por avaliação
    //
    public function deleteByAvaliacao()
    {
        if ( strlen($this->refAvaliacao)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_avaliacao_widget
                      WHERE ref_avaliacao = ?';
        $params = array($this->refAvaliacao);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->refAvaliacao = null;
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
        return 'idAvaliacaoWidget';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idAvaliacaoWidget'] = new stdClass();
        $attributesInfo['idAvaliacaoWidget']->columnName = 'id_avaliacao_widget';
        $attributesInfo['idAvaliacaoWidget']->type = 'integer';
        $attributesInfo['refAvaliacao'] = new stdClass();
        $attributesInfo['refAvaliacao']->columnName = 'ref_avaliacao';
        $attributesInfo['refAvaliacao']->type = 'integer';
        $attributesInfo['refWidget'] = new stdClass();
        $attributesInfo['refWidget']->columnName = 'ref_widget';
        $attributesInfo['refWidget']->type = 'character varying';
        $attributesInfo['opcoes'] = new stdClass();
        $attributesInfo['opcoes']->columnName = 'opcoes';
        $attributesInfo['opcoes']->type = 'text';
        return $attributesInfo;
    }
}


?>