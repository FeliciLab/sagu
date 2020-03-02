<?php

/**
 * Type que repesenta a tabela ava_widget.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 09/03/2012
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
$MIOLO->uses('classes/atype.class.php', 'avinst');
$MIOLO->uses('classes/adatabase.class.php', 'avinst');

$MIOLO->uses('types/avaPerfilWidget.class.php', 'avinst');
class avaWidget implements AType
{
    const WIDGET_HEIGHT = 'widgetHeight';
    const WIDGET_WIDTH = 'widgetWidth';
    const WIDGET_ROW = 'widgetRow';
    const WIDGET_COL = 'widgetColumn';
    
    /**
     * @AttributeType character varying
     * 
     */
    protected $idWidget;
    /**
     * @AttributeType character varying
     * 
     */
    protected $versao;
    /**
     * @AttributeType character varying
     * 
     */
    protected $nome;
    /**
     * @AttributeType text
     * 
     */
    protected $opcoesPadrao;
    /**
     * @AttributeType array
     * 
     */
    public $perfisWidget;
    
    public function __construct($data = null,  $populate = false, $getChilds = true)
    {
        if ( ! empty($data) )
        {
            $this->defineData($data);

            if ( $populate )
            {
                $this->populate($getChilds);
            }
        }
    }

    public function defineData($data)
    {
        $this->idWidget = $data->idWidget;
        $this->versao = $data->versao;
        $this->nome = $data->nome;
        $this->opcoesPadrao = $data->opcoesPadrao;
        $this->perfisWidget = $data->perfisWidget;
    }

    public function populate($getChilds = true)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_widget,
                       versao,
                       nome,
                       opcoes_padrao
                  FROM ava_widget
                 WHERE id_widget = ?';
        $result = ADatabase::query($sql, array($this->idWidget));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }
        elseif ($getChilds == true)
        {
            $avaPerfilWidget = new avaPerfilWidget($dataPerfilWidget);
            $avaPerfilWidget->__set('refWidget',$this->idWidget);
            $this->perfisWidget = $avaPerfilWidget->search(ADatabase::RETURN_TYPE);
        }

        list($this->idWidget, $this->versao, $this->nome, $this->opcoesPadrao) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_widget,
                       versao,
                       nome,
                       opcoes_padrao
                  FROM ava_widget';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_widget ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
            
            foreach ($result as $key => $widget)
            {
                $objectPerfilWidget = new stdClass();
                $objectPerfilWidget->refWidget = $this->idWidget;
                $typePerfilWidget = new avaPerfilWidget($objectPerfilWidget, null, false);
                $result[$key]->perfisWidget = $typePerfilWidget->search(ADatabase::RETURN_TYPE);
            }
        }
        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_widget 
                            (id_widget, versao, nome, opcoes_padrao)
                     VALUES (?, ?, ?, ?)';
        $params = array($this->idWidget, $this->versao, $this->nome, $this->opcoesPadrao);
        $result = ADatabase::execute($sql, $params);
        if( $result )
        {
            foreach ( $this->perfisWidget as $perfil )
            {
                if( $perfil->dataStatus != MSubDetail::STATUS_REMOVE )
                {
                    $dataPerfilWidget = new stdClass();
                    if ( $perfil->refPerfil && $this->idWidget )
                    {
                        $dataPerfilWidget->refPerfil = $perfil->refPerfil;
                        $dataPerfilWidget->refWidget = $this->idWidget;

                        $avaPerfilWidget = new avaPerfilWidget($dataPerfilWidget);
                        $avaPerfilWidget->insert();
                    }
                }
            }
        }
        
        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_widget
                   SET versao = ?,
                       nome = ?,
                       opcoes_padrao = ?
                 WHERE id_widget = ?';
        $params = array($this->versao, $this->nome, $this->opcoesPadrao, $this->idWidget);
        $result = ADatabase::execute($sql, $params);
        if( $result )
        {
            foreach ( $this->perfisWidget as $perfilWidget )
            {
                $avaPerfilWidget = new avaPerfilWidget();
                $avaPerfilWidget->idPerfilWidget = $perfilWidget->idPerfilWidget;
                
                if ( $perfilWidget->dataStatus == MSubDetail::STATUS_REMOVE )
                {
                    $avaPerfilWidget->delete();
                }
                elseif ( $perfilWidget->dataStatus == MSubDetail::STATUS_ADD )
                {
                    $avaPerfilWidget->refPerfil      = $perfilWidget->refPerfil;
                    $avaPerfilWidget->refWidget      = $this->idWidget;
                    $avaPerfilWidget->insert();
                }
                elseif ( $perfilWidget->dataStatus == MSubDetail::STATUS_EDIT )
                {
                    $avaPerfilWidget->refPerfil      = $perfilWidget->refPerfil;
                    $avaPerfilWidget->refWidget      = $this->idWidget;
                    $avaPerfilWidget->update();
                }
            }
        }
        
        return $result;
    }

    public function delete()
    {
        if ( strlen($this->idWidget)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        $filter = new stdClass();
        $filter->refWidget = $this->idWidget;
        $perfilWidgets = new avaPerfilWidget($filter);
        $data = $perfilWidgets->search(ADatabase::RETURN_TYPE);
        if (is_object($data[0]))
        {
            foreach ($data as $d)
            {
                $d->delete();
            }
        }
        
        $sql = 'DELETE FROM ava_widget
                      WHERE id_widget = ?';
        $params = array($this->idWidget);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idWidget = null;
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
        return 'idWidget';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idWidget'] = new stdClass();
        $attributesInfo['idWidget']->columnName = 'id_widget';
        $attributesInfo['idWidget']->type = 'character varying';
        $attributesInfo['versao'] = new stdClass();
        $attributesInfo['versao']->columnName = 'versao';
        $attributesInfo['versao']->type = 'character varying';
        $attributesInfo['nome'] = new stdClass();
        $attributesInfo['nome']->columnName = 'nome';
        $attributesInfo['nome']->type = 'character varying';
        $attributesInfo['opcoesPadrao'] = new stdClass();
        $attributesInfo['opcoesPadrao']->columnName = 'opcoes_padrao';
        $attributesInfo['opcoesPadrao']->type = 'text';
        return $attributesInfo;
    }
    
    /**
     * Função que retorna os tipos de atributos utilizados pelo sistema
     *
     * @return array
     */
    public static function getSystemAttributes()
    {
        $attributes[self::WIDGET_WIDTH] = 'Largura';
        $attributes[self::WIDGET_HEIGHT] = 'Altura';
        $attributes[self::WIDGET_ROW] = 'Linha';
        $attributes[self::WIDGET_COL] = 'Coluna';
        return $attributes;
    }
    
    public function checkWidgets($filters)
    {
        $sql = ' SELECT id_widget
                   FROM ava_widget A
             INNER JOIN ava_perfil_widget B
                     ON (A.id_widget=B.ref_widget)
             INNER JOIN ava_avaliacao_widget C
                     ON (A.id_widget=C.ref_widget)
                  WHERE B.ref_perfil = ?
                    AND C.ref_avaliacao = ? ';

        $params = array($filters->refPerfil, $filters->refAvaliacao);
        $result = ADatabase::query($sql, $params);
        return $result;
    }
}
?>