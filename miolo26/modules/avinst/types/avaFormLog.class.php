<?php

/**
 * Type que repesenta a tabela ava_form_log.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 18/01/2012
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
class avaFormLog implements AType
{
    // Registro de login
    const FORM_LOG_BEGIN = 1;
    // Registro de não validado
    const FORM_LOG_NO_VALIDATED = 2;
    // Registrado com sucesso
    const FORM_LOG_SUCCESS = 3;
    /**
     * @AttributeType integer
     * 
     */
    protected $idFormLog;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliador;
    /**
     * @AttributeType integer
     * 
     */
    protected $refFormulario;
    /**
     * @AttributeType integer
     * 
     */
    protected $tipoAcao;
    /**
     * @AttributeType timestamp without time zone
     * 
     */
    protected $data;
    /**
     * @AttributeType character varying
     * 
     */
    protected $sessao;
    /**
     * @AttributeType character varying
     * 
     */
    protected $tentativa;    
    
    //
    // Função construtura
    //
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

    //
    // Função para retornar os tipos de log
    //
    public function retornaTiposLog()
    {
        $tiposLog[avaFormLog::FORM_LOG_BEGIN] = 'Entrada no formulário';
        $tiposLog[avaFormLog::FORM_LOG_NO_VALIDATED] = 'Tentativa de envio';
        $tiposLog[avaFormLog::FORM_LOG_SUCCESS] = 'Enviado com sucesso';
        return $tiposLog;
    }
    
    //
    // Função para carregar o objeto
    //
    public function defineData($data)
    {
        $this->idFormLog = $data->idFormLog;
        $this->refAvaliador = $data->refAvaliador;
        $this->refFormulario = $data->refFormulario;
        $this->tipoAcao = $data->tipoAcao;
        $this->data = $data->data;
        $this->sessao = $data->sessao;
        $this->tentativa = $data->tentativa;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_form_log,
                       ref_avaliador,
                       ref_formulario,
                       tipo_acao,
                       TO_CHAR(data, \''.DB_MASK_TIMESTAMP.'\'),
                       sessao,
                       tentativa
                  FROM ava_form_log
                 WHERE id_form_log = ?';
        $result = ADatabase::query($sql, array($this->idFormLog));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idFormLog, $this->refAvaliador, $this->refFormulario, $this->tipoAcao, $this->data, $this->sessao, $this->tentativa) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_form_log,
                       ref_avaliador,
                       ref_formulario,
                       tipo_acao,
                       TO_CHAR(data, \''.DB_MASK_TIMESTAMP.'\'),
                       sessao,
                       tentativa
                  FROM ava_form_log';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_form_log ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_form_log 
                            (id_form_log, ref_avaliador, ref_formulario, tipo_acao, sessao, tentativa)
                     VALUES (?, ?, ?, ?, ?, ?)';
        $idFormLog = ADatabase::nextVal('ava_form_log_id_form_log_seq');
        $params = array($idFormLog, $this->refAvaliador, $this->refFormulario, $this->tipoAcao, $this->sessao, $this->tentativa);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idFormLog = $idFormLog;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_form_log
                   SET ref_avaliador = ?,
                       ref_formulario = ?,
                       tipo_acao = ?,
                       data = TO_TIMESTAMP(?, \''.DB_MASK_TIMESTAMP.'\'),
                       sessao = ?,
                       tentativa = ?
                 WHERE id_form_log = ?';
        $params = array($this->refAvaliador, $this->refFormulario, $this->tipoAcao, $this->data, $this->sessao, $this->tentativa, $this->idFormLog);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idFormLog)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_form_log
                      WHERE id_form_log = ?';
        $params = array($this->idFormLog);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idFormLog = null;
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
        return 'idFormLog';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idFormLog'] = new stdClass();
        $attributesInfo['idFormLog']->columnName = 'id_form_log';
        $attributesInfo['idFormLog']->type = 'integer';
        $attributesInfo['refAvaliador'] = new stdClass();
        $attributesInfo['refAvaliador']->columnName = 'ref_avaliador';
        $attributesInfo['refAvaliador']->type = 'integer';
        $attributesInfo['refFormulario'] = new stdClass();
        $attributesInfo['refFormulario']->columnName = 'ref_formulario';
        $attributesInfo['refFormulario']->type = 'integer';
        $attributesInfo['tipoAcao'] = new stdClass();
        $attributesInfo['tipoAcao']->columnName = 'tipo_acao';
        $attributesInfo['tipoAcao']->type = 'integer';
        $attributesInfo['data'] = new stdClass();
        $attributesInfo['data']->columnName = 'data';
        $attributesInfo['data']->type = 'timestamp without time zone';
        $attributesInfo['sessao'] = new stdClass();
        $attributesInfo['sessao']->columnName = 'sessao';
        $attributesInfo['sessao']->type = 'character varying';
        $attributesInfo['tentativa'] = new stdClass();
        $attributesInfo['tentativa']->columnName = 'tentativa';
        $attributesInfo['tentativa']->type = 'character varying';
        return $attributesInfo;
    }
    
   /*
    * Função para obter os avaliadores de uma avaliação
    */
    public function obterAvaliadores( $refAvaliacao, $tipoAcao = null, $refPerfil = null, $refFormulario = null )
    {
        $sql = '    SELECT DISTINCT ref_avaliador,
                           			TO_CHAR( MAX(data), \''.DB_MASK_TIMESTAMP.'\' )
                               FROM ava_form_log
                         INNER JOIN ava_formulario
                                 ON ( ava_form_log.ref_formulario = ava_formulario.id_formulario )
                              WHERE ava_formulario.ref_avaliacao = ?';
        
        
        $args[] = $refAvaliacao;
        
        if( strlen($tipoAcao) > 0 )
        {
            $sql .= ' AND ava_form_log.tipo_acao = ? ';
            $args[] = $tipoAcao; // Tentativa de envio com sucesso
        }
        
        if( strlen($refPerfil) > 0 )
        {
            $sql .= ' AND ava_formulario.ref_perfil = ? ';
            $args[] = $refPerfil;
        }
        
        if( strlen($refFormulario) > 0 )
        {
            $sql .= ' AND ava_formulario.id_formulario = ? ';
            $args[] = $refFormulario;
        }
        
        $sql .= ' GROUP BY 1';
        $result = ADatabase::query($sql,$args);
        return AVinst::getArrayOfObjects($result, array('refAvaliador','data'), 'refAvaliador');
    }
    
    //
    // Função que retorna o status do respondente
    // Esta função retorna uma string pequena contendo as informações do status conforme
    // regras convencionadas durante a arquitetura do projeto
    //
    public function obtemStatusRespondente()
    {
        
        $args[] = $this->refFormulario;
        $args[] = $this->refAvaliador;
        // Primeiramente, verifica se o respondente já enviou o formulário com sucesso
        $sql = '    SELECT DISTINCT tipo_acao, 
                                    TO_CHAR( MAX(data), \''.DB_MASK_TIMESTAMP.'\' )
                               FROM ava_form_log
                              WHERE ref_formulario = ?
                                AND ref_avaliador = ? 
                                AND tipo_acao = '.avaFormLog::FORM_LOG_SUCCESS.'
                           GROUP BY 1 ';
        
        $result = ADatabase::query($sql, $args);
        if (is_array($result[0]))
        {
            $resObj = new stdClass();
            $resObj->tipoAcao = $result[0][0];
            $resObj->data = $result[0][1];
            return $resObj;
        }
        // Se não enviou com sucesso, então
        $sql = ' SELECT tipo_acao,
                        TO_CHAR( MAX(data), \''.DB_MASK_TIMESTAMP.'\' )
                   FROM ava_form_log
                  WHERE ref_formulario = ?
                    AND ref_avaliador = ? 
                    AND tipo_acao <> '.avaFormLog::FORM_LOG_SUCCESS.'
               GROUP BY 1 ';
        $result = ADatabase::query($sql,$args);
        if (is_array($result[0]))
        {
            $resObj = new stdClass();
            $resObj->tipoAcao = $result[0][0];
            $resObj->data = $result[0][1];
            return $resObj;
        }
        return false;
    }
    
    //
    // Conta quantos respondentes já acessaram o formulário
    //
    public function contaTentativasPorFormulario()
    {
        $sql = ' SELECT COUNT(DISTINCT ref_avaliador) 
                   FROM ava_form_log 
                  WHERE ref_formulario = ? ';
        $args[] = $this->refFormulario;

        $result = ADatabase::query($sql, $args);
        if (is_array($result[0]))
        {
            return $result[0][0];
        }
        else
        {
            return 0;
        }
    }
    
    //
    //
    //
    public function estatisticasRespondentesPorDia()
    {
        $sql = 'SELECT TO_CHAR(data, \''.DB_MASK_DATE.'\'), 
                       COUNT(ref_avaliador) 
                  FROM (SELECT ref_avaliador, 
                               MAX(data::DATE) AS data 
                          FROM ava_form_log 
                         WHERE tipo_acao = '.self::FORM_LOG_SUCCESS.'
                           AND ref_formulario = ?
                      GROUP BY 1) AS A 
               GROUP BY 1, 
                        data 
               ORDER BY data ';
        $args[] = $this->refFormulario;
        $result = ADatabase::query($sql, $args);
        if (is_array($result[0]))
        {
            return $result;
        }
        else
        {
            return null;
        }
    }
}
?>
