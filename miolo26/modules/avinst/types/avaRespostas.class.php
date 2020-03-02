<?php

/**
 * Type que repesenta a tabela ava_respostas.
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
class avaRespostas implements AType
{
    const TIPO_RESPOSTA_VALOR = 1;
    const TIPO_RESPOSTA_OBJETO = 2;
    
    /**
     * @AttributeType integer
     * 
     */
    protected $idRespostas;
    /**
     * @AttributeType integer
     * 
     */
    protected $refBlocoQuestoes;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliado;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliador;
    /**
     * @AttributeType text
     * 
     */
    protected $valor;
    /**
     * 
     * 
     */
    protected $questao;
    /*
     * 
     */
    protected $atributos;
    
    public function __construct($data = null,  $populate = false)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaAtributos.class.php', 'avinst');
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
        $this->idRespostas = $data->idRespostas;
        $this->refBlocoQuestoes = $data->refBlocoQuestoes;
        $this->refAvaliado = $data->refAvaliado;
        $this->refAvaliador = $data->refAvaliador;
        $this->valor = $data->valor;
        $this->questao = $data->questao;
        $this->atributos = $data->atributos;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_respostas,
                       ref_bloco_questoes,
                       ref_avaliado,
                       ref_avaliador,
                       valor,
                       questao
                  FROM ava_respostas
                 WHERE id_respostas = ?';
        $result = ADatabase::query($sql, array($this->idRespostas));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idRespostas, $this->refBlocoQuestoes, $this->refAvaliado, $this->refAvaliador, $this->valor, $this->questao) = $result[0];
        
        $atributosData = new stdClass();
        $atributosData->refResposta = $this->idRespostas;
        $avaAtributos = new avaAtributos($atributosData);
        unset($atributosData);
        $atributos = $avaAtributos->search(ADatabase::RETURN_TYPE);
        if (is_array($atributos))
        {
            $this->atributos = $atributos;
        }
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_respostas,
                       ref_bloco_questoes,
                       ref_avaliado,
                       ref_avaliador,
                       valor,
                       questao
                  FROM ava_respostas';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_respostas ';
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
        
        $idRespostas = ADatabase::nextVal('ava_respostas_id_respostas_seq');
        $refBlocoQuestoes = strlen($this->refBlocoQuestoes) > 0 ? $this->refBlocoQuestoes : 'NULL';
        $refAvaliado = strlen($this->refAvaliado) > 0 ? $this->refAvaliado : 'NULL';
        $avaliador = $this->refAvaliador;
        $valor = $this->valor;
        $questao = $this->questao;
        
        $sql = "INSERT INTO ava_respostas 
                            (id_respostas, ref_bloco_questoes, ref_avaliado, ref_avaliador, valor, questao)
                     VALUES ($idRespostas, $refBlocoQuestoes, $refAvaliado, $$$avaliador$$, $$$valor$$, $$$questao$$)";
        
        $result = ADatabase::execute($sql);

        if ( $result )
        {
            $this->idRespostas = $idRespostas;
        }
        
        //
        // Registra os atributos
        //

        if (count($this->atributos)>0)
        {
            foreach ($this->atributos as $atributo)
            {
                $atributo->refResposta = $this->idRespostas;
                $atributos = new avaAtributos($atributo);
                $atributos->insert();
            }
        }
        return $result;
    }

    //
    //
    //
    public function update()
    {
        $idRespostas = $this->idRespostas;
        $refBlocoQuestoes = strlen($this->refBlocoQuestoes) > 0 ? $this->refBlocoQuestoes : 'NULL';
        $refAvaliado = strlen($this->refAvaliado) > 0 ? $this->refAvaliado : 'NULL';
        $avaliador = $this->refAvaliador;
        $valor = $this->valor;
        $questao = $this->questao;
        
        $sql = "UPDATE ava_respostas
                   SET ref_bloco_questoes = $refBlocoQuestoes,
                       ref_avaliado = $refAvaliado,
                       ref_avaliador = $$$avaliador$$,
                       valor = $$$valor$$, 
                       questao = $$$questao$$
                 WHERE id_respostas = $idRespostas";

        return ADatabase::execute($sql);
    }

    public function delete()
    {
        if ( strlen($this->idRespostas)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        $atributosData = new stdClass();
        $atributosData->refResposta = $this->idRespostas;
        $atributos = new avaAtributos($atributosData);
        $atributosData = $atributos->search(ADatabase::RETURN_TYPE);
        if (is_object($atributosData[0]))
        {
            foreach($atributosData as $atributoData)
            {
                $atributoData->delete();
            }
        }
        
        $sql = 'DELETE FROM ava_respostas
                      WHERE id_respostas = ?';
        $params = array($this->idRespostas);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idRespostas = null;
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
        return 'idRespostas';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idRespostas'] = new stdClass();
        $attributesInfo['idRespostas']->columnName = 'id_respostas';
        $attributesInfo['idRespostas']->type = 'integer';
        $attributesInfo['refBlocoQuestoes'] = new stdClass();
        $attributesInfo['refBlocoQuestoes']->columnName = 'ref_bloco_questoes';
        $attributesInfo['refBlocoQuestoes']->type = 'integer';
        $attributesInfo['refAvaliado'] = new stdClass();
        $attributesInfo['refAvaliado']->columnName = 'ref_avaliado';
        $attributesInfo['refAvaliado']->type = 'integer';
        $attributesInfo['refAvaliador'] = new stdClass();
        $attributesInfo['refAvaliador']->columnName = 'ref_avaliador';
        $attributesInfo['refAvaliador']->type = 'integer';
        $attributesInfo['valor'] = new stdClass();
        $attributesInfo['valor']->columnName = 'valor';
        $attributesInfo['valor']->type = 'text';
        $attributesInfo['questao'] = new stdClass();
        $attributesInfo['questao']->columnName = 'questao';
        $attributesInfo['questao']->type = 'text';
        return $attributesInfo;
    }
 
    //
    // Obtém uma resposta através do nome do formulário
    //
    public function obtemResposta($tipoRetorno = self::TIPO_RESPOSTA_VALOR, $ref_avaliador)
    {
        $MIOLO = MIOLO::getInstance();
        $this->__set('refAvaliador', $ref_avaliador);
        // Dados da resposta
        $questionInfo = $this->search(ADatabase::RETURN_TYPE);
        if (is_object($questionInfo[0]))
        {
            if ($tipoRetorno == self::TIPO_RESPOSTA_VALOR)
            {
                $valor = $questionInfo[0]->valor;
            }
            elseif ($tipoRetorno == self::TIPO_RESPOSTA_OBJETO)
            {
                $valor = $questionInfo[0];
            }
        }
        else
        {
            $valor = null;
        }
        return $valor;
    }
    
    //
    //
    //
    public function limpaRegistrosRemanescentes($idFormulario, $respostasRegistradas)
    {
        $MIOLO = MIOLO::getInstance();
        // Se tiver um array de respostas registradas, continua o processo
        if (is_array($respostasRegistradas))
        {
            foreach ($respostasRegistradas as $key => $respostaRegistrada)
            {
                $respostasRegistradas[$key] = '\''.$respostaRegistrada.'\'';
            }
            $respostas = implode(', ', $respostasRegistradas);
            
            $sql = ' SELECT ava_respostas.id_respostas 
                       FROM ava_respostas 
                 INNER JOIN ava_bloco_questoes 
                         ON ava_respostas.ref_bloco_questoes=ava_bloco_questoes.id_bloco_questoes 
                 INNER JOIN ava_bloco 
                         ON ava_bloco_questoes.ref_bloco=ava_bloco.id_bloco 
                 INNER JOIN ava_formulario 
                         ON ava_bloco.ref_formulario=ava_formulario.id_formulario 
                      WHERE ava_formulario.id_formulario=? 
                        AND ava_respostas.ref_avaliador=?
                        AND ava_respostas.questao NOT IN ('.$respostas.') ';
            
            $login = $MIOLO->getLogin()->user;
            $params = array($idFormulario, $login);
            $result = ADatabase::query($sql, $params);
            
            // Se existir algum registro, procede com a exclusão
            if (is_array($result[0]))
            {
                foreach ($result as $res)
                {
                    $data = new stdClass();
                    $data->idRespostas = $res[0];
                    $registro = new avaRespostas($data);
                    $registro->delete();
                    unset($data);
                }
            }
            return true;
        }
        // Se não tiver, retorna true, por segurança
        return true;
    }
}
?>