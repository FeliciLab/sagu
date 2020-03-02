<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * @author Nataniel I. da Silva [nataniel@solis.coop.br]
 *
 * @version: $Id$
 *
 * @since
 * Class created on 09/06/2015
 *
 **/

class avaCategoriaAvaliacao implements AType
{
    protected $categoriaAvaliacaoId;
    
    protected $categoriaId;
    
    protected $ref_avaliacao;
            
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
    /**
     * Função que preenche o objeto com os dados passados
     *
     * @param stdClass $data    Objeto stdClass com os atributos do objeto a serem preenchidos
     * 
     * @return void
     */
    public function defineData($data)
    {
        $this->categoriaAvaliacaoId = $data->categoriaAvaliacaoId;
        $this->categoriaId = $data->categoriaId;
        $this->ref_avaliacao = $data->ref_avaliacao;
    }
    
    /**
     * Função que preenche o objeto com os dados do banco de dados
     *
     * @return void
     */
    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        
        $sql = 'SELECT categoriaAvaliacaoId,
                       categoriaId,
                       ref_avaliacao               
                  FROM ava_categoria_avaliacao
                 WHERE categoriaAvaliacaoId = ?';
        
        $result = ADatabase::query($sql, array($this->categoriaAvaliacaoId));

        list($this->categoriaAvaliacaoId, $this->categoriaId, $this->ref_avaliacao) = $result[0];
    }

    /**
     * Função que busca os registros da tabela no banco de dados conforme filtro passado
     *
     * @param stdClass $filtro Objeto stdClass com os atributos a serem filtrados
     * 
     * @return matrix Resultado da busca na tabela da base de dados
     */
    public function search($returnType = ADataBase::RETURN_ARRAY)
    {
        
    }
    
    /**
     * Função que insere o registro no banco
     *
     * @return matrix Resultado da função de inserção na tabela da base de dados
     */
    public function insert()
    {
        $sql = ' INSERT INTO ava_categoria_avaliacao 
                             (categoriaId,
                              ref_avaliacao)
                      VALUES (?, ?) ';
        
        $params = array($this->categoriaId, 
                        $this->ref_avaliacao);
        
        return ADatabase::execute($sql, $params);    
    }
    
    /**
     * Função que exclui o registro do banco
     *
     * @return matrix Resultado da função de exclusão na tabela da base de dados
     */
    public function delete()
    {
        $sql = 'DELETE FROM ava_categoria_avaliacao
                      WHERE categoriaAvaliacaoId = ?';
        
        $params = array($this->categoriaAvaliacaoId);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->categoriaAvaliacaoId = null;
        }
        
        return $result;
    }
    
    /**
     * Função que edita o registro no banco
     *
     * @return matrix Resultado da função de edição na tabela da base de dados
     */
    public function update()
    {
        
    }
    
    public function getPrimaryKeyAttribute()
    {
        return 'categoriaAvaliacaoId';
    }
    
    public function __set($attribute,  $value)
    {
        $this->$attribute = $value;
    }

    public function __get($attribute)
    {
        return $this->$attribute;
    }
    
    /**
     * Obtém as categorias vinculadas a uma determinada avaliação
     * 
     * @param int $ref_avaliacao
     * @return \stdClass
     */
    public static function obtemCategoriasDaAvaliacao($ref_avaliacao)
    {
        $sql = 'SELECT A.categoriaId,
                       B.descricao,
                       B.tipo
                  FROM ava_categoria_avaliacao A
            INNER JOIN ava_categoria B
                 USING (categoriaId)
                 WHERE A.ref_avaliacao = ? 
              ORDER BY B.tipo, B.descricao';
        
        $result = ADatabase::query($sql, array($ref_avaliacao));
        
        $categoriaAvaliacao = array();
        foreach ( $result as $categorias )
        {
            $categoria = new stdClass();
            $categoria->subCategorias_categorias = $categorias[0];
            $categoria->subCategorias_categoriaDescricao = $categorias[1];
            $categoria->subCategorias_categoriaTipo = $categorias[2];
            
            $categoriaAvaliacao[] = $categoria;
        }
        
        return $categoriaAvaliacao;
    }
    
    /**
     * Exclui as categorias vinculadas a uma determinada avaliação
     * @param int $ref_avaliacao
     * @return boolean
     */
    public static function deleteCategoriasDaAvaliacacao($ref_avaliacao)
    {
        $sql = 'DELETE FROM ava_categoria_avaliacao
                      WHERE ref_avaliacao = ?';
        
        $params = array($ref_avaliacao);
        
        return ADatabase::execute($sql, $params);
    }
    
    /**
     * Lista as categorias cadastradas para uma determinada avaliação
     * 
     * @param integer $ref_avaliacao
     * @param ADatabase $returnType
     * @return array or object
     */
    public static function listarCategoriasDaAvaliacao($ref_avaliacao, $returnType = ADatabase::RETURN_ARRAY)
    {
        $sql = 'SELECT A.categoriaId,
                       B.descricao || \'/\' || B.tipo
                  FROM ava_categoria_avaliacao A
            INNER JOIN ava_categoria B
                 USING (categoriaId)
                 WHERE A.ref_avaliacao = ?
              ORDER BY B.tipo, B.descricao ';
        
        $categorias = $result = ADatabase::query($sql, array($ref_avaliacao));
        
        if ( $returnType == ADatabase::RETURN_OBJECT )
        {
            $categorias = array();
            
            foreach ( $result as $categoria )
            {
                $categorias[$categoria[0]] = $categoria[1]; 
            }
        }
        
        return $categorias;
    }
    
    /**
     * Verifica se a categoria está cadastrada para a avaliação a partir do bloco
     * @param object $data->refBloco, $data->categoriaId
     * @return boolean
     */
    public static function verificaCategoriaPeloBloco($data)
    {
        $sql = " SELECT COUNT(*) > 0
                   FROM ava_categoria_avaliacao A
             INNER JOIN ava_avaliacao B
                     ON A.ref_avaliacao = B.id_avaliacao 
             INNER JOIN ava_formulario C
                     ON B.id_avaliacao = C.ref_avaliacao
             INNER JOIN ava_bloco D
                     ON C.id_formulario = D.ref_formulario
                  WHERE D.id_bloco = ?
                    AND A.categoriaId = ? ";
        
        $params[] = $data->refBloco;
        $params[] = $data->categoriaId;
        
        $result = ADatabase::query($sql, $params);
        
        return $result[0][0] == DB_TRUE ? true : false;
    }
}