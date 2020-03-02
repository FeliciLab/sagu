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

$MIOLO->uses('types/avaPerfilWidget.class.php', 'avinst');
$MIOLO->uses('types/avaWidget.class.php', 'avinst');

class avaAvaliacaoPerfilWidget implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idAvaliacaoPerfilWidget;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliacao;
    /**
     * @AttributeType integer
     * 
     */
    protected $refPerfilWidget;
    /**
     * @AttributeType text
     * 
     */
    protected $altura;
    /**
     * @AttributeType text
     * 
     */
    protected $largura;
    /**
     * @AttributeType integer
     * 
     */
    protected $linha;
    /**
     * @AttributeType integer
     * 
     */
    protected $coluna;
    /*
     * @AttribyteType object
     */
    protected $perfilWidget;
    
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
        $this->idAvaliacaoPerfilWidget = $data->idAvaliacaoPerfilWidget;
        $this->refAvaliacao = $data->refAvaliacao;
        $this->refPerfilWidget = $data->refPerfilWidget;
        $this->altura = $data->altura;
        $this->largura = $data->largura;
        $this->linha = $data->linha;
        $this->coluna = $data->coluna;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_avaliacao_perfil_widget,
                       ref_avaliacao,
                       ref_perfil_widget,
                       altura,
                       largura,
                       linha,
                       coluna
                  FROM ava_avaliacao_perfil_widget
                 WHERE id_avaliacao_perfil_widget = ?';
        $result = ADatabase::query($sql, array($this->idAvaliacaoPerfilWidget));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idAvaliacaoPerfilWidget, $this->refAvaliacao, $this->refPerfilWidget, $this->altura, $this->largura, $this->linha, $this->coluna) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY, $orderBy = null, $returnPerfilWidget = true )
    {
        $sql = 'SELECT id_avaliacao_perfil_widget,
                       ref_avaliacao,
                       ref_perfil_widget,
                       altura,
                       largura,
                       linha,
                       coluna
                  FROM ava_avaliacao_perfil_widget';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        if (is_array($orderBy))
        {
            $order = implode(', ', $orderBy);
            $sql.= 'ORDER BY '.$order;
        }
        else
        {
            $sql.=' ORDER BY id_avaliacao_perfil_widget ';
        }
        $result = ADatabase::query($sql);
        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }
        
        if ((is_object($result[0])) && ($returnPerfilWidget == true))
        {
            foreach ($result as $pos => $res)
            {
                $filter = new stdClass();
                $filter->idPerfilWidget = $res->refPerfilWidget;
                $avaPerfilWidget = new avaPerfilWidget($filter, true);
                $result[$pos]->perfilWidget = $avaPerfilWidget;
            }
        }
        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_avaliacao_perfil_widget 
                            (id_avaliacao_perfil_widget, ref_avaliacao, ref_perfil_widget, altura, largura, linha, coluna)
                     VALUES (?, ?, ?, ?, ?, ?, ?)';
        $idAvaliacaoPerfilWidget = ADatabase::nextVal('ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq');
        $params = array($idAvaliacaoPerfilWidget, $this->refAvaliacao, $this->refPerfilWidget, $this->altura, $this->largura, $this->linha, $this->coluna);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idAvaliacaoPerfilWidget = $idAvaliacaoPerfilWidget;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_avaliacao_perfil_widget
                   SET ref_avaliacao = ?,
                       ref_perfil_widget = ?,
                       altura = ?,
                       largura = ?,
                       linha = ?,
                       coluna = ?
                 WHERE id_avaliacao_perfil_widget = ?';
        $params = array($this->refAvaliacao, $this->refPerfilWidget, $this->altura, $this->largura, $this->linha, $this->coluna, $this->idAvaliacaoPerfilWidget);
        return ADatabase::execute($sql, $params);
    }

    //
    //
    //
    public function delete()
    {
        if ( strlen($this->idAvaliacaoPerfilWidget)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_avaliacao_perfil_widget
                      WHERE id_avaliacao_perfil_widget = ?';
        $params = array($this->idAvaliacaoPerfilWidget);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idAvaliacaoPerfilWidget = null;
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

        $sql = 'DELETE FROM ava_avaliacao_perfil_widget
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
        return 'idAvaliacaoPerfilWidget';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo = array();
        $attributesInfo['idAvaliacaoPerfilWidget'] = new stdClass();
        $attributesInfo['idAvaliacaoPerfilWidget']->columnName = 'id_avaliacao_perfil_widget';
        $attributesInfo['idAvaliacaoPerfilWidget']->type = 'integer';
        $attributesInfo['refAvaliacao'] = new stdClass();
        $attributesInfo['refAvaliacao']->columnName = 'ref_avaliacao';
        $attributesInfo['refAvaliacao']->type = 'integer';
        $attributesInfo['refPerfilWidget'] = new stdClass();
        $attributesInfo['refPerfilWidget']->columnName = 'ref_perfil_widget';
        $attributesInfo['refPerfilWidget']->type = 'character varying';
        $attributesInfo['altura'] = new stdClass();
        $attributesInfo['altura']->columnName = 'altura';
        $attributesInfo['altura']->type = 'character varying';
        $attributesInfo['largura'] = new stdClass();
        $attributesInfo['largura']->columnName = 'largura';
        $attributesInfo['largura']->type = 'character varying';
        $attributesInfo['linha'] = new stdClass();
        $attributesInfo['linha']->columnName = 'linha';
        $attributesInfo['linha']->type = 'integer';
        $attributesInfo['coluna'] = new stdClass();
        $attributesInfo['coluna']->columnName = 'coluna';
        $attributesInfo['coluna']->type = 'integer';
        return $attributesInfo;
    }    
    
    //
    // Obtém os widgets específicos de uma avaliação
    //
    public function getWidgetsByEvaluation($idPerfil = null)
    {
        if (strlen($this->refAvaliacao)>0)
        {
            $avaliacaoPerfisWidgetsData = array();
            $avaliacaoPerfisWidgets = $this->search(ADatabase::RETURN_TYPE, array('linha', 'coluna'));
            
            if (is_array($avaliacaoPerfisWidgets))
            {
                foreach ($avaliacaoPerfisWidgets as $pos => $avaliacaoPerfilWidget)
                {
                    if (strlen($idPerfil))
                    {
                        if ($avaliacaoPerfilWidget->perfilWidget->refPerfil == $idPerfil)
                        {
                            $avaliacaoPerfisWidgetsData[] = $avaliacaoPerfilWidget;
                        }
                    }
                }
                return count($avaliacaoPerfisWidgetsData)>0 ? $avaliacaoPerfisWidgetsData : false;
            }
            return false;
        }
        else
        {
            return false;
        }
    }
}
?>