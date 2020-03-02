<?php

/**
 * Type que repesenta a tabela ava_formulario.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 21/11/2011
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
$MIOLO->uses( "types/avaBloco.class.php", 'avinst' );

class avaFormulario implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idFormulario;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliacao;
    /**
     * @AttributeType integer
     * 
     */
    protected $refPerfil;
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
     * @AttributeType integer
     * 
     */
    protected $refServico;
    /**
     * @AttributeType integer
     * 
     */
    protected $refServicoEmail;
    /**
     * @AttributeType array
     * 
     */
    protected $blocos;    
    
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
        $this->refAvaliacao = $data->refAvaliacao;
        $this->refPerfil = $data->refPerfil;
        $this->nome = $data->nome;
        $this->descritivo = $data->descritivo;        
        $this->refServico = $data->refServico;
        $this->blocos = $data->blocos;
        $this->refServicoEmail = $data->refServicoEmail;
        
        if ( is_numeric($data->idFormulario) )
        {
            $this->idFormulario = $data->idFormulario;
        }
        elseif ( strlen($data->idFormulario) > 0 )
        {
            $this->idFormulario = 0;
        }
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_formulario,
                       ref_avaliacao,
                       ref_perfil,
                       nome,
                       descritivo,
                       ref_servico,
                       ref_servico_email
                  FROM ava_formulario
                 WHERE id_formulario = ?';
        $result = ADatabase::query($sql, array($this->idFormulario));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idFormulario, $this->refAvaliacao, $this->refPerfil, $this->nome, $this->descritivo, $this->refServico, $this->refServicoEmail) = $result[0];
        
        $objectBloco = new stdClass();
        $objectBloco->refFormulario = $this->idFormulario;
        $typeBloco = new avaBloco($objectBloco);
        $this->blocos = $typeBloco->search(ADatabase::RETURN_TYPE,'idBloco');        
    }

    public function search( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT A.id_formulario,
                       A.ref_avaliacao,
                       B.nome as avaliacao,
                       A.ref_perfil,
                       A.nome,
                       A.descritivo,
                       A.ref_servico,
                       A.ref_servico_email
                  FROM ava_formulario A INNER JOIN ava_avaliacao B ON (A.ref_avaliacao = B.id_avaliacao)';
        
        if ( $this->idFormulario == 0 )
        {
            $this->idFormulario = NULL;
        }
        
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_formulario ';
        
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
    
    public function searchLookup( $returnType = ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_formulario,
                       nome
                  FROM ava_formulario A ';
        $where .= ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_formulario ';
        
        if( $returnType  ==  ADatabase::RETURN_SQL )
        {
            return $sql;    
        }
        
        $result = ADatabase::query($sql);
        
        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_formulario 
                            (id_formulario, ref_avaliacao, ref_perfil, nome, descritivo, ref_servico, ref_servico_email)
                     VALUES (?, ?, ?, ?, ?, ?, ?)';
        $idFormulario = ADatabase::nextVal('ava_formulario_id_formulario_seq');
        $params = array($idFormulario, $this->refAvaliacao, $this->refPerfil, $this->nome, $this->descritivo, $this->refServico, $this->refServicoEmail);
        $result = ADatabase::execute($sql, $params);
        
        if ( $result )
        {
            if (is_array($this->blocos))
            {
                $typeBloco = new avaBloco();
                
                foreach ( $this->blocos  as $bloco )
                {   
                    if( $bloco->dataStatus != MSubDetail::STATUS_REMOVE )
                    {
                        $bloco->refFormulario = $idFormulario;
                        $typeBloco->defineData($bloco);
                        $typeBloco->insert();
                    }
                }
            }
            $this->idFormulario = $idFormulario;
        }
        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_formulario
                   SET ref_avaliacao = ?,
                       ref_perfil = ?,
                       nome = ?,
                       descritivo = ?,
                       ref_servico = ?,
                       ref_servico_email = ?
                 WHERE id_formulario = ?';
        $params = array($this->refAvaliacao, $this->refPerfil, $this->nome, $this->descritivo, $this->refServico, $this->refServicoEmail, $this->idFormulario);
        $result = ADatabase::execute($sql, $params);
        
        if ( $result )
        {
            $typeBloco = new avaBloco();
            
            foreach ( $this->blocos  as $bloco )
            {
                
                $bloco->refFormulario = $this->idFormulario;
                $typeBloco->defineData($bloco);
                
                if( $bloco->dataStatus == MSubDetail::STATUS_ADD )
                {
                    $result = $typeBloco->insert();
                }
                elseif( $bloco->dataStatus == MSubDetail::STATUS_REMOVE )
                {
                    $result = $typeBloco->delete();
                }
                else
                {
                    $result = $typeBloco->update();
                }
            }
        }
        
        return $result;
    }

    public function delete()
    {
        if ( strlen($this->idFormulario)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }
        
        try 
        {
            // Apaga as dependências
            $typeBloco = new avaBloco();
            $typeBloco->__set('refFormulario',$this->idFormulario);
        
            foreach ( $typeBloco->search(ADatabase::RETURN_TYPE) as $bloco )
            {
                $result = $bloco->delete();
            }
            
            // Apaga o formulário
            $sql = 'DELETE FROM ava_formulario
                          WHERE id_formulario = ?';
            $params = array($this->idFormulario);
            $result = ADatabase::execute($sql, $params);
            $this->idFormulario = null;
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
        return 'idFormulario';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idFormulario'] = new stdClass();
        $attributesInfo['idFormulario']->columnName = 'id_formulario';
        $attributesInfo['idFormulario']->type = 'integer';
        $attributesInfo['refAvaliacao'] = new stdClass();
        $attributesInfo['refAvaliacao']->columnName = 'ref_avaliacao';
        $attributesInfo['refAvaliacao']->type = 'integer';
        $attributesInfo['refPerfil'] = new stdClass();
        $attributesInfo['refPerfil']->columnName = 'ref_perfil';
        $attributesInfo['refPerfil']->type = 'integer';
        $attributesInfo['nome'] = new stdClass();
        $attributesInfo['nome']->columnName = 'A.nome';
        $attributesInfo['nome']->type = 'text';
        $attributesInfo['descritivo'] = new stdClass();
        $attributesInfo['descritivo']->columnName = 'descritivo';
        $attributesInfo['descritivo']->type = 'text';
        $attributesInfo['refServico'] = new stdClass();
        $attributesInfo['refServico']->columnName = 'ref_servico';
        $attributesInfo['refServico']->type = 'integer';
        $attributesInfo['refServicoEmail'] = new stdClass();
        $attributesInfo['refServicoEmail']->columnName = 'ref_servico_email';
        $attributesInfo['refServicoEmail']->type = 'integer';        
        return $attributesInfo;
    }
    
    //
    //
    //
    public function searchByPerfil($perfis)
    {
        $filters[] = $this->refAvaliacao;
        
        $sql = 'SELECT id_formulario,
                       ref_avaliacao,
                       ref_perfil,
                       nome,
                       descritivo,
                       ref_servico,
                       ref_servico_email
                  FROM ava_formulario
                 WHERE ref_avaliacao = ? ';
        
        // Cria os filtros específicos para os ids
        if (is_array($perfis))
        {
            foreach ($perfis as $perfilId)
            {
                $filters[] = $perfilId;
            }
            $sql.= ' AND ref_perfil IN (?'.  str_repeat(',?', count($perfis)-1).') ';
        }
        $sql.=' ORDER BY id_formulario ';
        
        $sql = ADatabase::prepare($sql, $filters);
        $result = ADatabase::query($sql);
        $result = AVinst::getArrayOfTypes($result, __CLASS__);
        return $result;
    }
    
    /*
     * Função para obter as regras de autorização ou não do formulário
     * 
     * 
     */
    public function verificaRegra($parametros = null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaServico.class.php', 'avinst');
        $data = new stdClass();
        $data->idServico = $this->refServico;
        $servico = new avaServico($data, true);
        $retorno = $servico->chamaServico($parametros);
        return $retorno != false ? true : false;
    }
}


?>