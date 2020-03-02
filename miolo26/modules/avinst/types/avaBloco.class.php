<?php

/**
 * Type que repesenta a tabela ava_bloco.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 24/11/2011
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
$MIOLO->uses( "types/avaBlocoQuestoes.class.php", 'avinst' );

class avaBloco implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idBloco;
    /**
     * @AttributeType text
     * 
     */
    protected $nome;
    /**
     * @AttributeType integer
     * 
     */
    protected $refFormulario;
    /**
     * @AttributeType integer
     * 
     */
    protected $refGranularidade;

    /**
     * @AttributeType integer;
     */
    protected $ordem;
    
    /**
     * @AttributeType array
     * 
     */
    protected $questoes;
    
    
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
        $this->idBloco = $data->idBloco;
        $this->nome = $data->nome;
        $this->refFormulario = $data->refFormulario;
        $this->refGranularidade = $data->refGranularidade;
        $this->questoes = $data->questoes;
        $this->ordem = $data->ordem;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_bloco,
                       nome,
                       ref_formulario,
                       ref_granularidade,
                       ordem
                  FROM ava_bloco
                 WHERE id_bloco = ?';
        $result = ADatabase::query($sql, array($this->idBloco));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idBloco, $this->nome, $this->refFormulario, $this->refGranularidade, $this->ordem) = $result[0];
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY, $attributeIndexValue = NULL )
    {
        $sql = 'SELECT id_bloco,
                       nome,
                       ref_formulario,
                       ref_granularidade,
                       ordem
                  FROM ava_bloco';
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY ordem,
                         nome ';
        $result = ADatabase::query($sql);
        
        if ( $returnType == ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__,$attributeIndexValue);
            
            foreach ( $result as $key => $bloco )
            {
                $objectBlocoQuestoes->refBloco = $bloco->idBloco; 
                $typeBlocoQuestoes = new avaBlocoQuestoes($objectBlocoQuestoes);
                $result[$key]->questoes = $typeBlocoQuestoes->search(ADatabase::RETURN_TYPE);
            }            
        }
        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_bloco 
                            (id_bloco, nome, ref_formulario, ref_granularidade, ordem)
                     VALUES (?, ?, ?, ?, ?)';
        $idBloco = ADatabase::nextVal('ava_bloco_id_bloco_seq');
        $params = array($idBloco, $this->nome, $this->refFormulario, $this->refGranularidade, $this->ordem);
        $result = ADatabase::execute($sql, $params);
        
        if ( $result )
        {
            $typeBlocoQuestoes = new avaBlocoQuestoes();
            
            foreach ( $this->questoes as $questao )
            {
                if( $questao->dataStatus != MSubDetail::STATUS_REMOVE )
                {
                    $questao->refBloco = $idBloco;
                    $typeBlocoQuestoes->defineData($questao);
                    $typeBlocoQuestoes->insert();
                }                                
            }
            
            $this->idBloco = $idBloco;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_bloco
                   SET nome = ?,
                       ref_formulario = ?,
                       ref_granularidade = ?,
                       ordem = ?
                 WHERE id_bloco = ?';
        $params = array($this->nome, $this->refFormulario, $this->refGranularidade, $this->ordem, $this->idBloco);
        $result = ADatabase::execute($sql, $params);
        if ( $result )
        {
            $typeBlocoQuestoes = new avaBlocoQuestoes();
            
            foreach ( $this->questoes as $questao )
            {
                $questao->refBloco = $this->idBloco;
                $typeBlocoQuestoes->defineData($questao);
                
                if( $questao->dataStatus == MSubDetail::STATUS_ADD )
                {
                    $result = $typeBlocoQuestoes->insert();
                }
                elseif( $questao->dataStatus == MSubDetail::STATUS_REMOVE )
                {
                    $result = $typeBlocoQuestoes->delete();
                }
                else
                {
                    $result = $typeBlocoQuestoes->update();
                }
            }
            $this->idBloco = $idBloco;
        }
        return $result;
    }

    public function delete()
    {
        if ( strlen($this->idBloco)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        try
        {
            // Apaga as dependências
            $typeBlocoQuestoes = new avaBlocoQuestoes();
            $typeBlocoQuestoes->__set('refBloco',$this->idBloco);
            $questoes = $typeBlocoQuestoes->search(ADatabase::RETURN_TYPE);
            foreach ( $questoes as $questao )
            {
                $result = $questao->delete();
            }

            // Apaga o bloco
            $sql = 'DELETE FROM ava_bloco
                          WHERE id_bloco = ?';
            $params = array($this->idBloco);
            $result = ADatabase::execute($sql, $params);
            $this->idBloco = null;
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
        return 'idBloco';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idBloco'] = new stdClass();
        $attributesInfo['idBloco']->columnName = 'id_bloco';
        $attributesInfo['idBloco']->type = 'integer';
        $attributesInfo['nome'] = new stdClass();
        $attributesInfo['nome']->columnName = 'nome';
        $attributesInfo['nome']->type = 'text';
        $attributesInfo['refFormulario'] = new stdClass();
        $attributesInfo['refFormulario']->columnName = 'ref_formulario';
        $attributesInfo['refFormulario']->type = 'integer';
        $attributesInfo['refGranularidade'] = new stdClass();
        $attributesInfo['refGranularidade']->columnName = 'ref_granularidade';
        $attributesInfo['refGranularidade']->type = 'integer';
        $attributesInfo['ordem'] = new stdClass();
        $attributesInfo['ordem']->columnName = 'ordem';
        $attributesInfo['ordem']->type = 'integer';
        return $attributesInfo;
    }
    
    public function getGranularidade()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaGranularidade.class.php', 'avinst');
        $data = new stdClass();
        $data->idGranularidade = $this->refGranularidade;
        $granularidade = new avaGranularidade($data, true);
        return $granularidade;
    }
}


?>