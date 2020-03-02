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

class avaCategoria implements AType
{
    protected $categoriaId;
    
    protected $descricao;
    
    protected $tipo;
            
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
        $this->categoriaId = $data->categoriaId;
        $this->descricao = $data->descricao;
        $this->tipo = $data->tipo;
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
        
        $sql = 'SELECT categoriaId,
                       descricao,
                       tipo                       
                  FROM ava_categoria
                 WHERE categoriaId = ?';
        
        $result = ADatabase::query($sql, array($this->categoriaId));

        list($this->categoriaId, $this->descricao, $this->tipo) = $result[0];
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
        $sql = ' SELECT categoriaId,
                        descricao,
                        tipo                       
                   FROM ava_categoria ';
        
        if ( strlen($this->categoriaId) > 0 && $this->categoriaId > 0 )
        {
            $where .= ' AND categoriaId = ? ';
            $args[] = $this->categoriaId;
        }

        if ( strlen(trim($this->descricao)) > 0 )
        {
            $where .= ' AND descricao ILIKE ?';
            $args[] = "%$this->descricao%";
        }

        if ( strlen($this->tipo) > 0 )
        {
            $where .= ' AND tipo ILIKE ?';
            $args[] = "%$this->tipo%";
        }

        if ( strlen($where)  >  0 )
        {
            $sql .= ' WHERE '.substr($where, 5);
            $sql = ADatabase::prepare($sql,$args);
        }
        
        $sql .= ' ORDER BY categoriaId ';

        if ( $returnType == ADatabase::RETURN_SQL )
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
    
    /**
     * Função que insere o registro no banco
     *
     * @return matrix Resultado da função de inserção na tabela da base de dados
     */
    public function insert()
    {
        $sql = ' INSERT INTO ava_categoria 
                             (categoriaId,
                              descricao,
                              tipo)
                      VALUES (?, ?, ?) ';
        
        $this->categoriaId = ADatabase::nextVal('ava_categoria_categoriaid_seq');
        
        $params = array($this->categoriaId,
                        $this->descricao, 
                        $this->tipo);
        
        return ADatabase::execute($sql, $params);    
    }
    
    /**
     * Função que exclui o registro do banco
     *
     * @return matrix Resultado da função de exclusão na tabela da base de dados
     */
    public function delete()
    {
        $sql = 'DELETE FROM ava_categoria
                      WHERE categoriaId = ?';
        
        $params = array($this->categoriaId);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->categoriaId = null;
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
        $sql = ' UPDATE ava_categoria 
                    SET descricao = ?,
                        tipo = ?
                  WHERE categoriaId = ? ';
        
        $params = array($this->descricao, 
                        $this->tipo,
                        $this->categoriaId);
        
        return ADatabase::execute($sql, $params); 
    }
    
    public function getPrimaryKeyAttribute()
    {
        return 'categoriaId';
    }
    
    public function __set($attribute,  $value)
    {
        $this->$attribute = $value;
    }

    public function __get($attribute)
    {
        return $this->$attribute;
    }
    
    public static function listarCategorias()
    {
        $sql = ' SELECT categoriaId,
                        descricao || \'/\' || tipo 
                   FROM ava_categoria 
               ORDER BY tipo, descricao ';
        
        $result = ADatabase::query($sql);
        
        return $result;
    }
}