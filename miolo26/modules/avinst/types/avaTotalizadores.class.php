<?php

/**
 * Type que repesenta a tabela ava_totalizadores.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 23/02/2012
 *
 * \b Organization: \
 * va de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
$MIOLO = MIOLO::getInstance();
$MIOLO->uses('types/avaTotalizadoresAtributos.class.php', 'avinst');

class avaTotalizadores implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idTotalizador;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliacao;
    /**
     * @AttributeType integer
     * 
     */
    protected $refGranularidade;
    /**
     * @AttributeType text
     * 
     */
    protected $codigo;
    /**
     * @AttributeType text
     * 
     */
    protected $descricao;
    /**
     * @AttributeType integer
     * 
     */
    protected $count;
    /**
     *
     */
    protected $totalizadorAtributos;
    
    // Construct
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
        $this->idTotalizador = $data->idTotalizador;
        $this->refAvaliacao = $data->refAvaliacao;
        $this->refGranularidade = $data->refGranularidade;
        $this->codigo = $data->codigo;
        $this->descricao = $data->descricao;
        $this->count = $data->count;
        $this->totalizadorAtributos = $data->totalizadorAtributos;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_totalizador,
                       ref_avaliacao,
                       ref_granularidade,
                       codigo,
                       descricao,
                       count
                  FROM ava_totalizadores
                 WHERE id_totalizador = ?';
        $result = ADatabase::query($sql, array($this->idTotalizador));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idTotalizador, $this->refAvaliacao, $this->refGranularidade, $this->codigo, $this->descricao, $this->count) = $result[0];
        
        if (strlen($this->idTotalizador)>0)
        {
            $filter = new stdClass();
            $filter->refTotalizador = $this->idTotalizador;
            $avaTotalizadoresAtributos = new avaTotalizadoresAtributos($filter);
            $this->totalizadorAtributos = $avaTotalizadoresAtributos->search(ADatabase::RETURN_TYPE);
        }
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_totalizador,
                       ref_avaliacao,
                       ref_granularidade,
                       codigo,
                       descricao,
                       count
                  FROM ava_totalizadores';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_totalizador ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_totalizadores 
                            (id_totalizador, ref_avaliacao, ref_granularidade, codigo, descricao, count)
                     VALUES (?, ?, ?, ?, ?, ?)';
        $idTotalizador = ADatabase::nextVal('ava_estatisticas_id_estatistica_seq');
        $params = array($idTotalizador, $this->refAvaliacao, $this->refGranularidade, $this->codigo, $this->descricao, $this->count);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idTotalizador = $idTotalizador;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_totalizadores
                   SET ref_avaliacao = ?,
                       ref_granularidade = ?,
                       codigo = ?,
                       descricao = ?,
                       count = ?
                 WHERE id_totalizador = ?';
        $params = array($this->refAvaliacao, $this->refGranularidade, $this->codigo, $this->descricao, $this->count, $this->idTotalizador);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idTotalizador)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        if (is_array($this->totalizadorAtributos))
        {
            foreach ($this->totalizadorAtributos as $totalizadorAtributo)
            {
                $totalizadorAtributo->delete();
            }
        }

        $sql = 'DELETE FROM ava_totalizadores
                      WHERE id_totalizador = ?';
        $params = array($this->idTotalizador);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idTotalizador = null;
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
        return 'idTotalizador';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idTotalizador'] = new stdClass();
        $attributesInfo['idTotalizador']->columnName = 'id_totalizador';
        $attributesInfo['idTotalizador']->type = 'integer';
        $attributesInfo['refAvaliacao'] = new stdClass();
        $attributesInfo['refAvaliacao']->columnName = 'ref_avaliacao';
        $attributesInfo['refAvaliacao']->type = 'integer';
        $attributesInfo['refGranularidade'] = new stdClass();
        $attributesInfo['refGranularidade']->columnName = 'ref_granularidade';
        $attributesInfo['refGranularidade']->type = 'integer';
        $attributesInfo['codigo'] = new stdClass();
        $attributesInfo['codigo']->columnName = 'codigo';
        $attributesInfo['codigo']->type = 'text';
        $attributesInfo['descricao'] = new stdClass();
        $attributesInfo['descricao']->columnName = 'descricao';
        $attributesInfo['descricao']->type = 'text';
        $attributesInfo['count'] = new stdClass();
        $attributesInfo['count']->columnName = 'count';
        $attributesInfo['count']->type = 'integer';
        return $attributesInfo;
    }
    
    //
    // Procedimento de atualiação dos totalizadores (processo em lote)
    //
    public function atualizaTotalizadores($idAvaliacao, $idGranularidade)
    {
        $return->status = true;
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaGranularidade.class.php', 'avinst');
        $filters = new stdClass();
        $filters->idGranularidade = $idGranularidade;
        $avaGranularidade = new avaGranularidade($filters, true);
        $opcoesGranularidade = AGranularity::parseStatisticsOptions($avaGranularidade->opcoes);
        set_time_limit(0);
        if (is_array($opcoesGranularidade))
        {
            $indiceTotalizador = new stdClass();
            // Cria um array de índices
            foreach ($opcoesGranularidade as $indice)
            {
                if (is_object($indice))
                {
                    if ($indice->tipoDeTratamento == AGranularity::GRANULARITY_STATISTICS_TYPE_CODE)
                    {
                        if (!isset($indiceTotalizador->codigo))
                        {
                            $indiceTotalizador->codigo = $indice->atributo;
                        }
                        else
                        {
                            $return->error = 'Há mais de um atributo como índice para a granularidade selecionada, por favor, verifique as configurações das opções da granularidade e atualize o índice para apenas um elemento da granularidade';
                            $return->status = false;
                        }
                    }
                    elseif ($indice->tipoDeTratamento == AGranularity::GRANULARITY_STATISTICS_TYPE_DESCRIPTION)
                    {
                        if (!isset($indiceTotalizador->descricao))
                        {
                            $indiceTotalizador->descricao = $indice->atributo;
                        }
                        else
                        {
                            $return->error = 'Há mais de um atributo como descrição para a granularidade selecionada, por favor, verifique as configurações das opções da granularidade e atualize o índice para apenas um elemento da granularidade';
                            $return->status = false;
                        }
                    }
                    elseif ($indice->tipoDeTratamento == AGranularity::GRANULARITY_STATISTICS_TYPE_ATTRIBUTE)
                    {
                        $indiceAtributo[$indice->atributo] = $indice->tipoDeTratamento;
                    }
                }
                else
                {
                    $return->error = 'Desculpe, houve problemas em interpretar os índices da granularidade, por favor, contate o administrador do sistema';
                    $return->status = false;   
                }
            }
        }
        //
        // Se existir índice estatístico e a granularidade for de objetos, continua
        //
        if ($return->status == true)
        {
            if ((isset($indiceTotalizador->codigo)) && (isset($indiceTotalizador->descricao)))
            {
                if ($avaGranularidade->tipo == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS)
                {
                    // Conecta no webservice para obter as os dados para inserir nos totalizadores
                    $refServico = $avaGranularidade->refServico;
                    $MIOLO->uses('types/avaServico.class.php', 'avinst');
                    unset($data);
                    $data = new stdClass();
                    $data->idServico = $refServico;
                    $servico = new avaServico($data, true);
                    unset($retorno);
                    $retorno = $servico->chamaServico(null);
                    // Se retornou procede com a atualização
                    if (is_array($retorno))
                    {
                        try
                        {
                            ADatabase::execute('begin');
                            // Verifica se existe totalizadores e exclui
                            $totalizadorFilter = new stdClass();
                            $totalizadorFilter->refAvaliacao = $idAvaliacao;
                            $totalizadorFilter->refGranularidade = $idGranularidade;
                            $avaTotalizadores = new avaTotalizadores($totalizadorFilter);
                            $totalizadores = $avaTotalizadores->search(ADatabase::RETURN_TYPE);
                            if (is_object($totalizadores[0]))
                            {
                                foreach ($totalizadores as $totalizador)
                                {
                                    $totalizador->populate();
                                    $totalizador->delete();
                                }
                            }
                            unset($totalizadorFilter);
                            unset($totalizadores);
                            unset($totalizador);

                            $codigo = $indiceTotalizador->codigo;
                            $descricao = $indiceTotalizador->descricao;
                            // Insere os Totalizadores
                            foreach ($retorno as $dado)
                            {
                                // Adiciona o elemento na avaTotalizador
                                $totalizador = new stdClass();
                                $totalizador->refAvaliacao = $idAvaliacao;
                                $totalizador->refGranularidade = $idGranularidade;
                                $totalizador->codigo = $dado->$codigo;
                                $totalizador->descricao = $dado->$descricao;
                                $totalizador->count = $dado->count;
                                
                                if (!is_null($totalizador->codigo))
                                {
                                    $avaTotalizadores = new avaTotalizadores($totalizador);
                                    $avaTotalizadores->insert();
                                    unset($totalizador);
                                    if (is_array($indiceAtributo))
                                    {
                                        foreach ($indiceAtributo as $atributo => $tipoDeTratamento)
                                        {
                                            $totalizadorAtributo = new stdClass();
                                            $totalizadorAtributo->refTotalizador = $avaTotalizadores->idTotalizador;
                                            $totalizadorAtributo->chave = $atributo;
                                            $totalizadorAtributo->valor = isset($dado->$atributo) ? $dado->$atributo : '';
                                            $avaTotalizadoresAtributos = new avaTotalizadoresAtributos($totalizadorAtributo);
                                            $avaTotalizadoresAtributos->insert();
                                            unset($totalizadorAtributo);
                                        }
                                    }
                                }
                            }

                            $return->status = true;
                            $return->total = count($retorno);
                            unset($dado);
                        }
                        catch (Exception $e)
                        {
                            
                        }
                    }
                    else
                    {
                        $return->error = 'O webservice não retornou valores válidos para processar, por favor, verifique seu webservice';
                        $return->status = false;
                    }
                }
                else
                {
                    $return->error = 'Tipo de granularidade inválida, por favor, selecione uma granularidade do tipo "Array de objetos"';
                    $return->status = false;
                }
            }
            else
            {
                $return->error = 'Índice estatístico não cadastrado para a granularidade';
                $return->status = false;
            }
        }
        ADatabase::execute($return->status == true ? 'commit' : 'rollback');
        return $return;
    }
}


?>