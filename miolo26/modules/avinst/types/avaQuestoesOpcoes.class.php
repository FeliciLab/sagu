<?php

/**
 * Type que repesenta a tabela ava_questoes_opcoes
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 08/07/2014
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2014 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

class avaQuestoesOpcoes implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idQuestoesOpcoes;
    
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
     * @AttributeType text
     * 
     */
    protected $legenda;
    
    /**
     * @AttributeType integer
     * 
     */
    protected $refQuestoes;
    
    /**
     * @AttributeType boolean;
     * 
     */
    protected $opcaoDescritiva;
     
    
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
        $this->idQuestoesOpcoes = $data->idQuestoesOpcoes;
        $this->refQuestoes = $data->refQuestoes;
        $this->codigo = $data->codigo;
        $this->descricao = $data->descricao;
        $this->legenda = $data->legenda;
        $this->opcaoDescritiva = $data->opcaoDescritiva ? $data->opcaoDescritiva : DB_FALSE;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_questoes_opcoes,
                       ref_questoes,
                       codigo,
                       descricao,
                       legenda,
                       opcao_descritiva
                  FROM ava_questoes_opcoes
                 WHERE id_questoes_opcoes = ?';
        $result = ADatabase::query($sql, array($this->idQuestoesOpcoes));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idQuestoesOpcoes, $this->refQuestoes, $this->codigo, $this->descricao, $this->legenda) = $result[0];
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_questoes_opcoes,
                       ref_questoes,
                       codigo,
                       descricao,
                       legenda,
                       opcao_descritiva
                  FROM ava_questoes_opcoes';
       
        
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_questoes_opcoes, codigo ';
        
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
        $sql = 'INSERT INTO ava_questoes_opcoes 
                            (id_questoes_opcoes, ref_questoes, codigo, descricao, legenda, opcao_descritiva)
                    VALUES (?, ?, ?, ?, ?, ?)';
        $this->idQuestoesOpcoes = ADatabase::nextVal('ava_questoes_opcoes_id_questoes_opcoes_seq');
        
        $params = array($this->idQuestoesOpcoes, $this->refQuestoes, $this->codigo, $this->descricao, $this->legenda, $this->opcaoDescritiva);
        $result = ADatabase::execute($sql, $params);

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_questoes_opcoes
                   SET ref_questoes = ?,
                       codigo = ?,
                       descricao = ?,
                       legenda = ?,
                       opcao_descritiva = ?
                 WHERE id_questoes_opcoes = ?';
        $params = array($this->refQuestoes, $this->codigo, $this->descricao, $this->legenda, $this->idQuestoesOpcoes, $this->opcaoDescritiva);
        $result = ADatabase::execute($sql, $params);
        
        return $result;
    }

    public function delete()
    {
        if ( strlen($this->idQuestoesOpcoes)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        try 
        {
            // Apaga a opção.
            $sql = 'DELETE FROM ava_questoes_opcoes
                          WHERE id_questoes_opcoes = ?';
            $params = array($this->idQuestoesOpcoes);
            $result = ADatabase::execute($sql, $params);
            $this->idQuestoesOpcoes = null;
            
            return $result;
        }
        catch (Exception $e)
        {
            return $e;    
        }
    }
    
    /**
     * Apaga todas as opções da questão.
     * 
     * @param int $refQuestoes
     * @return boolean Retorna positivo caso tenha conseguido apagar todas as opções.
     */
    public static function apagarTodasOpcoesDaQuestao($refQuestoes)
    {
        if ( strlen($refQuestoes)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        try 
        {
            // Apaga a opção.
            $sql = 'DELETE FROM ava_questoes_opcoes
                          WHERE ref_questoes = ?';
            $params = array($refQuestoes);
            $result = ADatabase::execute($sql, $params);
            
            return $result;
        }
        catch (Exception $e)
        {
            return $e;    
        }
        
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
        return 'idQuestoesOpcoes';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idQuestoesOpcoes'] = new stdClass();
        $attributesInfo['idQuestoesOpcoes']->columnName = 'id_questoes_opcoes';
        $attributesInfo['idQuestoesOpcoes']->type = 'integer';
        
        $attributesInfo['refQuestoes'] = new stdClass();
        $attributesInfo['refQuestoes']->columnName = 'ref_questoes';
        $attributesInfo['refQuestoes']->type = 'integer';
        
        $attributesInfo['codigo'] = new stdClass();
        $attributesInfo['codigo']->columnName = 'codigo';
        $attributesInfo['codigo']->type = 'varchar';
        
        $attributesInfo['descricao'] = new stdClass();
        $attributesInfo['descricao']->columnName = 'descricao';
        $attributesInfo['descricao']->type = 'varchar';
        
        $attributesInfo['legenda'] = new stdClass();
        $attributesInfo['legenda']->columnName = 'legenda';
        $attributesInfo['legenda']->type = 'varchar';
        
        $attributesInfo['opcaoDescritiva'] = new stdClass();
        $attributesInfo['opcaoDescritiva']->columnName = 'opcao_descritiva';
        $attributesInfo['opcaoDescritiva']->type = 'integer';
        
        return $attributesInfo;
    }
    
}


?>