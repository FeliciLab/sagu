<?php 

/**
 * Interface padrão das classes que representam tabelas do Avaliação Institucional
 * É implementada pelas classes, conhecidas como types, que se relacionam com a base de dados
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 2011/10/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2009-2010 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

interface AType
{
    /**
     * Constantes utilizadas para inserção, edição e remoção de registros em tabelas relacionadas
     * São utilizadas pelo componente MSubDetail
     * 
     */
    const STATUS_ADDED   = 'add';
    const STATUS_EDITED  = 'edit';
    const STATUS_REMOVED = 'remove';
    
    /**
     * Função que preenche o objeto com os dados passados
     *
     * @param stdClass $data    Objeto stdClass com os atributos do objeto a serem preenchidos
     * 
     * @return void
     */
    public function defineData($data);
    
    /**
     * Função que preenche o objeto com os dados do banco de dados
     *
     * @return void
     */
    public function populate();

    /**
     * Função que busca os registros da tabela no banco de dados conforme filtro passado
     *
     * @param stdClass $filtro Objeto stdClass com os atributos a serem filtrados
     * 
     * @return matrix Resultado da busca na tabela da base de dados
     */
    public function search($returnType = ADataBase::RETURN_ARRAY);
    
    /**
     * Função que insere o registro no banco
     *
     * @return matrix Resultado da função de inserção na tabela da base de dados
     */
    public function insert();
    
    /**
     * Função que exclui o registro do banco
     *
     * @return matrix Resultado da função de exclusão na tabela da base de dados
     */
    public function delete();
    
    /**
     * Função que edita o registro no banco
     *
     * @return matrix Resultado da função de edição na tabela da base de dados
     */
    public function update();
    
    /**
     * Função para converter objeto em um dado de array
     */
    /*public function convertToArray($elements = null)
    {
        if (is_null($elements))
        {
            $elements = $this->get_object_vars();
            foreach ($elements as $key => $element)
            {
                $array[$key] = $this->__get($element);
            }
        }
        return $array;
    }*/
}

?>
