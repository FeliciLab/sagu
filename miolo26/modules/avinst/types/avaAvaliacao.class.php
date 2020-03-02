<?php

/**
 * Type que repesenta a tabela ava_avaliacao.
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

$MIOLO->uses('/types/avaAvaliacaoPerfilWidget.class.php', 'avinst');
$MIOLO->uses('/types/avaCategoriaAvaliacao.class.php', 'avinst');
class avaAvaliacao implements AType
{
    const AVALIACAO_TIPO_PROCESSO_PONTUAL = 1;
    const AVALIACAO_TIPO_PROCESSO_CONTINUO = 2;
    /**
     * @AttributeType integer
     * 
     */
    protected $idAvaliacao;
    /**
     * @AttributeType text
     * 
     */
    protected $nome;
    /**
     * @AttributeType text
     * 
     */
    protected $descritivo;

    /**
     * @AttributeType date
     * 
     */
    protected $dtInicio;
    /**
     * @AttributeType date
     * 
     */
    protected $dtFim;
    /**
     * @AttributeType integer
     * 
     */
    protected $tipoProcesso;
    /**
     * @AttributeType date
     * 
     */
    protected $dtFimRelatorio;
    
    /**
     * @var Array para as categorias
     * 
     */
    protected $categorias;
    
    //
    // Array para as avaliações
    //
    protected $avaliacaoPerfilWidgets;

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
        $this->idAvaliacao = is_numeric($data->idAvaliacao) ? $data->idAvaliacao : 0;
        $this->nome = $data->nome;
        $this->descritivo = $data->descritivo;
        $this->dtInicio = $data->dtInicio;
        $this->dtFim = $data->dtFim;
        $this->tipoProcesso = $data->tipoProcesso;
        $this->avaliacaoPerfilWidgets = $data->avaliacaoPerfilWidgets;
        $this->dtFimRelatorio = $data->dtFimRelatorio;
        $this->categorias = $data->categorias;
    }

    //
    //
    //
    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_avaliacao,
                       nome,
                       descritivo,
                       TO_CHAR(dt_inicio, \''.DB_MASK_DATE.'\'),
                       TO_CHAR(dt_fim, \''.DB_MASK_DATE.'\'),
                       tipo_processo,
                       TO_CHAR(dt_fim_relatorio, \''.DB_MASK_DATE.'\')                       
                  FROM ava_avaliacao
                 WHERE id_avaliacao = ?';
        $result = ADatabase::query($sql, array($this->idAvaliacao));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idAvaliacao, $this->nome, $this->descritivo, $this->dtInicio, $this->dtFim, $this->tipoProcesso, $this->dtFimRelatorio) = $result[0];
        
        if (strlen($this->idAvaliacao)>0)
        {
            $filter = new stdClass();
            $filter->refAvaliacao = $this->idAvaliacao;
            $avaAvaliacaoPerfilWidget = new avaAvaliacaoPerfilWidget($filter);
            $this->avaliacaoPerfilWidgets = $avaAvaliacaoPerfilWidget->search(ADatabase::RETURN_TYPE);
            
            if( count($this->avaliacaoPerfilWidgets) > 0 )
            {
                foreach ( $this->avaliacaoPerfilWidgets as $key => $avaliacaoPerfilWidget)
                {
                    $this->avaliacaoPerfilWidgets[$key]->idAvaliacaoPerfilWidget = $avaliacaoPerfilWidget->idAvaliacaoPerfilWidget;
                    $this->avaliacaoPerfilWidgets[$key]->refPerfilWidget = $avaliacaoPerfilWidget->refPerfilWidget;
                    $this->avaliacaoPerfilWidgets[$key]->linha = $avaliacaoPerfilWidget->linha;
                    $this->avaliacaoPerfilWidgets[$key]->coluna = $avaliacaoPerfilWidget->coluna;
                    $this->avaliacaoPerfilWidgets[$key]->altura = $avaliacaoPerfilWidget->altura;
                    $this->avaliacaoPerfilWidgets[$key]->largura = $avaliacaoPerfilWidget->largura;
                }                
            }
        }
    }

    //
    //
    //
    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_avaliacao,
                       nome,
                       descritivo,
                       TO_CHAR(dt_inicio, \''.DB_MASK_DATE.'\'),
                       TO_CHAR(dt_fim, \''.DB_MASK_DATE.'\'),
                       tipo_processo,
                       TO_CHAR(dt_fim_relatorio, \''.DB_MASK_DATE.'\')
                  FROM ava_avaliacao';
        
        if( strlen($this->idAvaliacao) > 0 && $this->idAvaliacao > 0)
        {
            $where .= ' AND id_avaliacao = ?';
            $args[] = $this->idAvaliacao;
        }
        
        if( strlen(trim($this->nome)) > 0 )
        {
            $where .= ' AND nome ILIKE ?';
            $args[] = "%$this->nome%";
        }
        
        if( strlen($this->descritivo) > 0 )
        {
            $where .= ' AND descritivo ILIKE ?';
            $args[] = "%$this->descritivo%";
        }
        
        if( strlen($this->dtInicio) > 0 )
        {
            $where .= " AND dt_inicio >= TO_DATE(?,'" . DB_MASK_DATE . "')";
            $args[] = $this->dtInicio;
        }
        
        if( strlen($this->dtFim) > 0 )
        {
            $where .= " AND dt_fim <= TO_DATE(?,'" . DB_MASK_DATE . "')";
            $args[] = $this->dtFim;
        }
        
        if( strlen($this->tipoProcesso) > 0 )
        {
            $where .= ' AND tipo_processo = ?';
            $args[] = $this->tipoProcesso;
        }
        
        if( strlen($this->dtFimRelatorio) > 0 )
        {
            $where .= " AND dt_fim_relatorio <= TO_DATE(?,'" . DB_MASK_DATE . "')";
            $args[] = $this->dtFim;
        }

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
            $sql = ADatabase::prepare($sql,$args);
        }

        $sql .= ' ORDER BY id_avaliacao ';
        
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

    //
    //
    //
    public function insert()
    {
        $sql = 'INSERT INTO ava_avaliacao 
                            (id_avaliacao, nome, descritivo, dt_inicio, dt_fim, tipo_processo, dt_fim_relatorio)
                     VALUES (?, ?, ?, TO_DATE(?, \''.DB_MASK_DATE.'\'), TO_DATE(?, \''.DB_MASK_DATE.'\'), ?, TO_DATE(?, \''.DB_MASK_DATE.'\'))';
        $idAvaliacao = ADatabase::nextVal('ava_avaliacao_id_avaliacao_seq');
        $params = array($idAvaliacao, $this->nome, $this->descritivo, $this->dtInicio, $this->dtFim, $this->tipoProcesso, $this->dtFimRelatorio);
        $result = ADatabase::execute($sql, $params);
        if ( $result )
        {
            if (is_array($this->avaliacaoPerfilWidgets))
            {
                $typeAvaliacaoPerfilWidget = new avaAvaliacaoPerfilWidget();
                $opcoes = new stdClass();
                                        
                foreach ($this->avaliacaoPerfilWidgets as $avaliacaoPerfilWidget)
                {
                    if( $bloco->dataStatus != MSubDetail::STATUS_REMOVE )
                    {
                        $avaliacaoPerfilWidget->refAvaliacao = $idAvaliacao;
                        $opcoes->altura = $avaliacaoPerfilWidget->altura;
                        $opcoes->largura = $avaliacaoPerfilWidget->largura;
                        $opcoes->linha = $avaliacaoPerfilWidget->linha;
                        $opcoes->coluna = $avaliacaoPerfilWidget->coluna;
                        $typeAvaliacaoPerfilWidget->defineData($avaliacaoPerfilWidget);
                        $status = $typeAvaliacaoPerfilWidget->insert();
                    }
                }
            }
            
            if ( is_array($this->categorias) )
            {
                foreach ( $this->categorias as $categoria )
                {
                    $avaCategoria = new avaCategoriaAvaliacao();
                    $avaCategoria->categoriaId = $categoria;
                    $avaCategoria->ref_avaliacao = $idAvaliacao;
                    $avaCategoria->insert();
                }
            }
            
            $this->idAvaliacao = $idAvaliacao;
        }
        return $result;
    }

    //
    //
    //
    public function update()
    {
        $sql = 'UPDATE ava_avaliacao
                   SET nome = ?,
                       descritivo = ?,
                       dt_inicio = TO_DATE(?, \''.DB_MASK_DATE.'\'),
                       dt_fim = TO_DATE(?, \''.DB_MASK_DATE.'\'),
                       tipo_processo = ?,
                       dt_fim_relatorio = TO_DATE(?, \''.DB_MASK_DATE.'\')
                 WHERE id_avaliacao = ?';
        $params = array($this->nome, $this->descritivo, $this->dtInicio, $this->dtFim, $this->tipoProcesso, $this->dtFimRelatorio, $this->idAvaliacao);
        $result = ADatabase::execute($sql, $params);
        if( $result )
        {
            $avaAvaliacaoPerfilWidget = new avaAvaliacaoPerfilWidget();
            foreach ( $this->avaliacaoPerfilWidgets as $avaliacaoPerfilWidget )
            {
                $avaAvaliacaoPerfilWidget->idAvaliacaoPerfilWidget = $avaliacaoPerfilWidget->idAvaliacaoPerfilWidget;               
                if( $avaliacaoPerfilWidget->dataStatus === MSubDetail::STATUS_REMOVE )
                {
                    $result = $avaAvaliacaoPerfilWidget->delete();
                }
                else
                {
                    $avaAvaliacaoPerfilWidget->refAvaliacao            = $this->idAvaliacao;
                    $avaAvaliacaoPerfilWidget->refPerfilWidget         = $avaliacaoPerfilWidget->refPerfilWidget;
                    $avaAvaliacaoPerfilWidget->largura                 = $avaliacaoPerfilWidget->largura;
                    $avaAvaliacaoPerfilWidget->altura                  = $avaliacaoPerfilWidget->altura;
                    $avaAvaliacaoPerfilWidget->linha                   = $avaliacaoPerfilWidget->linha;
                    $avaAvaliacaoPerfilWidget->coluna                  = $avaliacaoPerfilWidget->coluna;
                    if ($avaliacaoPerfilWidget->dataStatus == MSubDetail::STATUS_ADD )
                    {
                        $result = $avaAvaliacaoPerfilWidget->insert();
                    }
                    elseif ($avaliacaoPerfilWidget->dataStatus == MSubDetail::STATUS_EDIT )
                    {
                        $result = $avaAvaliacaoPerfilWidget->update();
                    }
                }
            }
            
            avaCategoriaAvaliacao::deleteCategoriasDaAvaliacacao($this->idAvaliacao);
            
            if ( is_array($this->categorias) )
            {
                foreach ( $this->categorias as $categoria )
                {
                    $avaCategoria = new avaCategoriaAvaliacao();
                    $avaCategoria->categoriaId = $categoria;
                    $avaCategoria->ref_avaliacao = $this->idAvaliacao;
                    $avaCategoria->insert();
                }
            }
        }
        return $result;
    }

    //
    //
    //
    public function delete()
    {
        if ( strlen($this->idAvaliacao)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        $filters = new stdClass();
        $filters->refAvaliacao = $this->idAvaliacao;
        $avaAvaliacaoWidget = new avaAvaliacaoPerfilWidget($filters);
        $return = $avaAvaliacaoWidget->deleteByAvaliacao();
        
        if ($return)
        {
            $sql = 'DELETE FROM ava_avaliacao
                          WHERE id_avaliacao = ?';
            $params = array($this->idAvaliacao);
            $result = ADatabase::execute($sql, $params);
        
            if ( $result )
            {
                $this->idAvaliacao = null;
            }
        }
        return $result;
    }

    //
    //
    //
    public function __set($attribute,  $value)
    {
        $this->$attribute = $value;
    }

    //
    //
    //
    public function __get($attribute)
    {
        return $this->$attribute;
    }

    //
    //
    //
    public function getPrimaryKeyAttribute()
    {
        return 'idAvaliacao';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idAvaliacao'] = new stdClass();
        $attributesInfo['idAvaliacao']->columnName = 'id_avaliacao';
        $attributesInfo['idAvaliacao']->type = 'integer';
        $attributesInfo['nome'] = new stdClass();
        $attributesInfo['nome']->columnName = 'nome';
        $attributesInfo['nome']->type = 'text';
        $attributesInfo['descritivo'] = new stdClass();
        $attributesInfo['descritivo']->columnName = 'descritivo';
        $attributesInfo['descritivo']->type = 'text';
        $attributesInfo['dtInicio'] = new stdClass();
        $attributesInfo['dtInicio']->columnName = 'dt_inicio';
        $attributesInfo['dtInicio']->type = 'date';
        $attributesInfo['dtFim'] = new stdClass();
        $attributesInfo['dtFim']->columnName = 'dt_fim';
        $attributesInfo['dtFim']->type = 'date';
        $attributesInfo['tipoProcesso'] = new stdClass();
        $attributesInfo['tipoProcesso']->columnName = 'tipo_processo';
        $attributesInfo['tipoProcesso']->type = 'integer';
        $attributesInfo['dtFimRelatorio'] = new stdClass();
        $attributesInfo['dtFimRelatorio']->columnName = 'dt_fim_relatorio';
        $attributesInfo['dtFimRelatorio']->type = 'date';
        
        return $attributesInfo;
    }
    
    //
    //
    // Função para obter as avaliações abertas de acordo com a data atual ou com datas informadas
    //
    //
    public function getAvaliacoesAbertas($returnType = ADatabase::RETURN_ARRAY, $dtInicio = null, $dtFim = null, $reports = false)
    {
        $where = '';
        
        $sql = 'SELECT id_avaliacao,
                       nome
                  FROM ava_avaliacao';
        
        // Se for null, compara com a data atual
        
        if (is_null($dtInicio))
        {
            $where .= " AND now() >=dt_inicio";
        }
        // Se não for null e não for false
        elseif (($dtInicio !== false) && ( strlen($dtInicio) > 0 ))
        {
            $where .= " AND TO_DATE('$dtInicio','" . DB_MASK_DATE . "') >= dt_inicio";
        }
        
        if ($reports == false)
        {
            if (is_null($dtFim))
            {
                $where.= " AND now()<=dt_fim";
            }
            elseif ( ($dtFim !== false) && ( strlen($dtFim) > 0 ))
            {
                $where .= " AND TO_DATE('$dtFim','" . DB_MASK_DATE . "') <= dt_fim";
            }
        }
        else
        {
            if (is_null($dtFim))
            {
                $where.= " AND (now()<=dt_fim_relatorio OR now()<=dt_fim)";
            }
            elseif ( ($dtFim !== false) && ( strlen($dtFim) > 0 ))
            {
                $where .= " AND (TO_DATE('$dtFim','" . DB_MASK_DATE . "') <= dt_fim_relatorio OR (TO_DATE('$dtFim','" . DB_MASK_DATE . "') <= dt_fim)";
            }
        }

        $where = ' WHERE '.substr($where, 5);        
        $sql .= $where;
        $result = ADatabase::query($sql);
        
        if( $returnType == ADatabase::RETURN_ARRAY )
        {
            return $result;
        }
        
        if (is_array($result[0]))
        {
            foreach ($result as $res)
            {
                $data = new stdClass();
                $data->idAvaliacao = $res[0];
                $avaliacao[] = new avaAvaliacao($data, true);
            }
        }
        
        return $avaliacao;
    }
    
    //
    // Verifica se a avaliação está ativa, ou seja, se a data de fim da avaliação é maior
    // que a data atual
    //
    public function checkAvaliacaoAtiva($idAvaliacao)
    {
        $sql = ' SELECT id_avaliacao
                   FROM ava_avaliacao
                  WHERE now()<=dt_fim 
                    AND id_avaliacao=?';
        $params[] = $idAvaliacao;
        $result = ADatabase::query($sql, $params);
        if ($result[0][0] == $idAvaliacao)
        {
            return true;
        }
        return false;
    }
    
    //
    // Obtém os formulários da avaliação
    //
    public function getFormularios($perfis = null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        // Instancia os formulários
        $MIOLO->uses('types/avaFormulario.class.php', 'avinst');
        $formularios = new avaFormulario();
        $data->refAvaliacao = $this->idAvaliacao;
        $formularios->defineData($data);
        
        // Obtém os perfis
        $result = $formularios->searchByPerfil($perfis);
        $this->formularios = $result;
        return $this->formularios;
    }
    
    //
    //
    //
    public function obtemTiposProcesso()
    {
        $tiposProcesso[self::AVALIACAO_TIPO_PROCESSO_PONTUAL] = 'Processo pontual';
        $tiposProcesso[self::AVALIACAO_TIPO_PROCESSO_CONTINUO] = 'Processo contínuo';
        return $tiposProcesso;
    }
    
    //
    //
    //
    public function obtemTiposProcessoDescritivo()
    {
        $tiposProcesso[self::AVALIACAO_TIPO_PROCESSO_PONTUAL] = 'Após efetuar a avaliação e gravar as informações, estas não poderão ser alteradas';
        $tiposProcesso[self::AVALIACAO_TIPO_PROCESSO_CONTINUO] = 'Após efetuar a avaliação e gravar as informações, estas poderão ser alteradas';
        return $tiposProcesso;
    }
    
    public function obtemOpcoesQuestoes($idAvaliacao)
    {
        $sql = ' SELECT A.valor, 
                       (SELECT opcoes 
                          FROM ava_questoes 
                         WHERE id_questoes = A.id_questoes ), 
                        A.id_questoes 
                   FROM (SELECT DISTINCT F.valor,
                                        E.id_questoes
                                   FROM ava_formulario A
                             INNER JOIN ava_bloco C
                                     ON C.ref_formulario = A.id_formulario
                             INNER JOIN ava_bloco_questoes D
                                     ON C.id_bloco = D.ref_bloco
                             INNER JOIN ava_questoes E
                                     ON E.id_questoes = D.ref_questao		
                             INNER JOIN ava_respostas F
                                     ON F.ref_bloco_questoes = D.id_bloco_questoes

                                  WHERE E.tipo IN (2,3,4)
                                    AND A.ref_avaliacao = ?) A ';
        
        $params[] = $idAvaliacao;
        
        $result = ADatabase::query($sql, $params);
        
        return $result;
    }
    
    public function insereOpcoesQuestoes($valor, $opcao, $questao)
    {
        $sql = ' INSERT INTO ava_opcoes_questoes
                             (valor, opcao, questao)
                      VALUES (?, ?, ?) ';
        
        $params[] = $valor;
        $params[] = $opcao;
        $params[] = $questao;
        
        $result = ADatabase::query($sql, $params);
        
        return $result;
    }
    
    public function deleteOpcoesQuestoes()
    {
        $sql = 'DELETE FROM ava_opcoes_questoes ';
        
        $result = ADatabase::query($sql, $params);
        
        return $result;
        
    }
    
    public static function verificaAvalicoesAbertas($tipoAcesso)
    {
        $sql = " SELECT COUNT(*) > 0
                   FROM ava_avaliacao A
             INNER JOIN ava_formulario B
                     ON (A.id_avaliacao = B.ref_avaliacao)
             INNER JOIN ava_perfil C
                     ON (B.ref_perfil = C.id_perfil)
                  WHERE now()::DATE BETWEEN dt_inicio AND dt_fim 
                    AND C.tipo = ? ";
        
        $args[] = $tipoAcesso;
        
        $result = ADatabase::query($sql, $args);
        
        return $result[0][0];
        
    }
}

?>