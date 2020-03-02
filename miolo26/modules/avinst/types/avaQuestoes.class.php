<?php

/**
 * Type que repesenta a tabela ava_questoes.
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

$MIOLO->uses( "types/avaQuestoesOpcoes.class.php", 'avinst' );
class avaQuestoes implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idQuestoes;
    /**
     * @AttributeType text
     * 
     */
    protected $descricao;
    /**
     * @AttributeType text
     * 
     */
    protected $tipo;
    /**
     * @AttributeType text
     * 
     */
    protected $opcoes;
    /**
     * @AttributeType text
     * 
     */
    protected $opcoesUnserialize;
    
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
        $this->idQuestoes = is_numeric($data->idQuestoes) ? $data->idQuestoes : 0;
        $this->descricao = $data->descricao;
        $this->tipo = $data->tipo;
        $this->opcoes = $data->opcoes;
        $this->opcoesUnserialize = $data->opcoesUnserialize;
        
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_questoes,
                       descricao,
                       tipo,
                       opcoes
                  FROM ava_questoes
                 WHERE id_questoes = ?';
        $result = ADatabase::query($sql, array($this->idQuestoes));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idQuestoes, $this->descricao, $this->tipo, $this->opcoes) = $result[0];
        
        
        $objectQuestoesOpcoes= new stdClass();
        $objectQuestoesOpcoes->refQuestoes = $this->idQuestoes;
        
        $typeQuestoesOpcoes = new avaQuestoesOpcoes($objectQuestoesOpcoes);
        $this->opcoesUnserialize = $typeQuestoesOpcoes->search(ADatabase::RETURN_TYPE);
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_questoes,
                       descricao,
                       tipo,
                       opcoes
                  FROM ava_questoes';
        
        if ( $this->idQuestoes == 0 )
        {
            $this->idQuestoes = NULL;
        }
        
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where) > 0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_questoes ';
        
        if( $returnType == ADatabase::RETURN_SQL )
        {
            return $sql;
        }
        
        $result = ADatabase::query($sql);

        if ( $returnType == ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_questoes 
                            (id_questoes, descricao, tipo, opcoes)
                     VALUES (?, ?, ?, ?)';
        $idQuestoes = ADatabase::nextVal('ava_questoes_id_questoes_seq');
        $params = array($idQuestoes, $this->descricao, $this->tipo, $this->opcoes);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idQuestoes = $idQuestoes;
            
            if (is_array($this->opcoesUnserialize))
            {
                $typeQuestoesOpcoes = new avaQuestoesOpcoes();

                foreach ( $this->opcoesUnserialize  as $opcoes )
                {   
                    $opcoes->refQuestoes = $idQuestoes;
                    $opcoes->descricao = $opcoes->descricaoOpcao;
                    $typeQuestoesOpcoes->defineData($opcoes);
                    $typeQuestoesOpcoes->insert();
                }
            }
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_questoes
                   SET descricao = ?,
                       tipo = ?,
                       opcoes = ?
                 WHERE id_questoes = ?';
        $params = array($this->descricao, $this->tipo, $this->opcoes, $this->idQuestoes);
        $result = ADatabase::execute($sql, $params);
        
        if ( $result )
        {
            if (is_array($this->opcoesUnserialize))
            {
                // Apaga todas as opcoes.
                avaQuestoesOpcoes::apagarTodasOpcoesDaQuestao($this->idQuestoes);
                
                $typeQuestoesOpcoes = new avaQuestoesOpcoes();

                foreach ( $this->opcoesUnserialize  as $opcoes )
                {   
                    $opcoes->refQuestoes = $this->idQuestoes;
                    $opcoes->descricao = $opcoes->descricaoOpcao;
                    $typeQuestoesOpcoes->defineData($opcoes);
                    $typeQuestoesOpcoes->insert();
                }
            }
        }
        
        return $result;
        
    }

    public function delete()
    {
        if ( strlen($this->idQuestoes)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        // Apaga as referências.
        avaQuestoesOpcoes::apagarTodasOpcoesDaQuestao($this->idQuestoes);

        $sql = 'DELETE FROM ava_questoes
                      WHERE id_questoes = ?';
        $params = array($this->idQuestoes);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idQuestoes = null;
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
        return 'idQuestoes';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idQuestoes'] = new stdClass();
        $attributesInfo['idQuestoes']->columnName = 'id_questoes';
        $attributesInfo['idQuestoes']->type = 'integer';
        $attributesInfo['descricao'] = new stdClass();
        $attributesInfo['descricao']->columnName = 'descricao';
        $attributesInfo['descricao']->type = 'text';
        $attributesInfo['tipo'] = new stdClass();
        $attributesInfo['tipo']->columnName = 'tipo';
        $attributesInfo['tipo']->type = 'integer';
        $attributesInfo['opcoes'] = new stdClass();
        $attributesInfo['opcoes']->columnName = 'opcoes';
        $attributesInfo['opcoes']->type = 'text';
        return $attributesInfo;
    }
}


?>