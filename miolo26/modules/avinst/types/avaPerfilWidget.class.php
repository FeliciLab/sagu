<?php

/**
 * Type que repesenta a tabela ava_perfil_widget.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 12/03/2012
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
$MIOLO->uses('types/avaAvaliacaoPerfilWidget.class.php', 'avinst');
$MIOLO->uses('types/avaWidget.class.php', 'avinst');
class avaPerfilWidget implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idPerfilWidget;
    /**
     * @AttributeType character varying
     * 
     */
    protected $refWidget;
    /**
     * @AttributeType integer
     * 
     */
    protected $refPerfil;
    /**
     *
     * @AttributeType object
     */
    protected $widget;
    
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
        $this->idPerfilWidget = $data->idPerfilWidget;
        $this->refWidget = $data->refWidget;
        $this->refPerfil = $data->refPerfil;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_perfil_widget,
                       ref_widget,
                       ref_perfil
                  FROM ava_perfil_widget
                 WHERE id_perfil_widget = ?';
        $result = ADatabase::query($sql, array($this->idPerfilWidget));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idPerfilWidget, $this->refWidget, $this->refPerfil) = $result[0];   
        $filter = new stdClass();
        $filter->idWidget = $this->refWidget;
        $this->widget = new avaWidget($filter, true, false);
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY, $returnEvaluations = false )
    {
        $sql = 'SELECT id_perfil_widget,
                       ref_widget,
                       ref_perfil
                  FROM ava_perfil_widget';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_perfil_widget ';
        $result = ADatabase::query($sql);
        
        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
            if (is_object($result[0]))
            {
                foreach ($result as $key => $res)
                {
                    if ($returnEvaluations == true)
                    {
                        $filter = new stdClass();
                        $filter->refPerfilWidget = $res->idPerfilWidget;
                        $avaAvaliacaoPerfilWidget = new avaAvaliacaoPerfilWidget($filter, null, false);
                        $result[$key]->avaliacaoPerfilWidgets = $avaAvaliacaoPerfilWidget->search(ADatabase::RETURN_TYPE);
                    }
                }
            }
        }
        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_perfil_widget 
                            (id_perfil_widget, ref_widget, ref_perfil)
                     VALUES (?, ?, ?)';
        $idGrupoWidget = ADatabase::nextVal('ava_perfil_widget_id_perfil_widget_seq');
        $params = array($idGrupoWidget, $this->refWidget, $this->refPerfil);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idPerfilWidget = $idGrupoWidget;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_perfil_widget
                   SET ref_widget = ?,
                       ref_perfil = ?
                 WHERE id_perfil_widget = ?';
        $params = array($this->refWidget, $this->refPerfil, $this->idPerfilWidget);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        $sql = 'DELETE FROM ava_perfil_widget';
        
        if( strlen($this->idPerfilWidget) > 0 )
        {
            $where .= ' AND id_perfil_widget = ?';
            $args[] = $this->idPerfilWidget;
        }
        
        if( strlen($this->refWidget) > 0 )
        {
            $where .= ' AND ref_widget = ?';
            $args[] = $this->refWidget;
        }
        
        if( strlen($this->refPerfil) > 0 )
        {
            $where .= ' AND ref_perfil = ?';
            $args[] = $this->refPerfil;
        }

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
            $result = ADatabase::execute($sql, $args);
        }

        if ( $result )
        {
            $this->idPerfilWidget = null;
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
        return 'idGrupoWidget';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idGrupoWidget'] = new stdClass();
        $attributesInfo['idGrupoWidget']->columnName = 'id_perfil_widget';
        $attributesInfo['idGrupoWidget']->type = 'integer';
        $attributesInfo['refWidget'] = new stdClass();
        $attributesInfo['refWidget']->columnName = 'ref_widget';
        $attributesInfo['refWidget']->type = 'character varying';
        $attributesInfo['refPerfil'] = new stdClass();
        $attributesInfo['refPerfil']->columnName = 'ref_perfil';
        $attributesInfo['refPerfil']->type = 'integer';
        return $attributesInfo;
    }
}
?>