<?php

/**
 * <--- Copyright 2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
 *
 * O Base é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Classe manipuladora do catálogo do Postgresql.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 22/06/2012
 */
class bCatalogo
{
    /**
     * Constantes que definem os tipos das colunas de tabela do banco de dados.
     */

    const TYPE_TEXT = 'text';
    const TYPE_VARCHAR = 'varchar';
    const TYPE_INT = 'int4';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_CHAR = 'char';
    const TYPE_DATE = 'date';
    const TYPE_TIMESTAMP = 'timestamp';

    /**
     * Listas os nomes das colunas de uma tabela.
     * TODO: considerar esquema.
     * 
     * @param string $tabela Nome da tabela.
     * @return array Vetor com a listagem de colunas da tabela
     */
    public static function listarColunasDaTabela($tabela)
    {
        if (!$tabela)
        {
            return false;
        }

        $tabela = strtolower($tabela);
        
        list($schema, $tabela) = explode('.', $tabela);
        
        if ( !$tabela)
        {
            $tabela = $schema;
            $schema = 'public';
        }

        $sql = " --listarColunasDaTabela
                SELECT attname,
                        attname
                  FROM pg_catalog.pg_attribute a
            INNER JOIN pg_stat_user_tables c on a.attrelid = c.relid
                 WHERE a.attnum > 0
                   AND NOT a.attisdropped
                   AND c.relname = '{$tabela}'
                   AND c.schemaname = '{$schema}'
              ORDER BY c.relname, a.attname";

        $colunas = bBaseDeDados::consultar($sql);

        $coluna = array();

        foreach ($colunas as $linha => $info)
        {
            $coluna[] = $info[0];
        }

        return $coluna;
    }
    
    /**
     * Retorna a estrutura completa dos campos.
     * 
     * Os parametros são filtros no sql.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $tabela Nome da tabela.
     * @param string $coluna Nome da coluna.
     * @return array de stdClass Colunas da tabela. 
     */
    public static function obterColunasDaTabela($esquema = NULL, $tabela = NULL, $coluna = NULL)
    {
//TODO: pegar comentário col_description http://www.postgresql.org/docs/8.2/static/functions-info.html.
        $sql = " --obterColunasDaTabela 
               SELECT a.attname AS column,
                      t.typname as type,
                      a.atttypmod-4 as length,
                      pg_catalog.format_type(a.atttypid, a.atttypmod),
                      a.attnotNULL as notNULL,
                      substring(d.adsrc for 128) as default,
                      coalesce(
                                (
                                SELECT i.indisprimary AS primary_key
                                  FROM pg_class bc,
                                       pg_index i,
                                       pg_attribute ia
                                 WHERE (bc.oid = i.indrelid)
                                   AND (ia.attrelid = i.indexrelid) 
                                   AND (bc.relname = c.relname)
                                   AND ia.attname = a.attname
                                   AND i.indisprimary = true
                                   LIMIT 1
                                ),false
                      ) as primary_key,
                      coalesce(
                                (
                                SELECT i.indisunique AS unique_key
                                  FROM pg_class bc,
                                       pg_index i,
                                       pg_attribute ia
                                 WHERE (bc.oid = i.indrelid)
                                   AND (ia.attrelid = i.indexrelid) 
                                   AND (bc.relname = c.relname)
                                   AND ia.attname = a.attname
                                   AND i.indisunique = true
                                   LIMIT 1
                                ),false
                      ) as unique_key
                 FROM pg_class c
            LEFT JOIN pg_attribute a
                   ON ( a.attrelid = c.oid )
            LEFT JOIN pg_type t
                   ON  ( a.atttypid = t.oid )
            LEFT JOIN pg_catalog.pg_attrdef d
                   ON ( d.adrelid = a.attrelid AND d.adnum = a.attnum )
            LEFT JOIN pg_catalog.pg_tables ta
                   ON ( ta.tablename = c.relname)
                WHERE a.attnum > 0
              AND NOT a.attisdropped
                ";

        $where = NULL;

        if ($tabela)
        {
            $tabela = strtolower($tabela);
            $where[] = "AND c.relname = '{$tabela}'";
        }

        if ($esquema)
        {
            $where[] = "AND schemaname = '$esquema'";
        }

        if ($coluna)
        {
            $where[] = "AND a.attname = '$coluna'";
        }

        $sql .= implode("\n", $where) . "\nORDER BY a.attnum;";

        $colunas = bBaseDeDados::consultar($sql);
	
	if ( is_array($colunas) )
        {
            foreach ($colunas as $linha => $info)
            {
                $coluna = new stdClass();
                $coluna->column = $info[0];
                $coluna->type = $info[1];
                $coluna->length = $info[2];
                $coluna->formatedType = $info[3];
                $coluna->notnull = $info[4];
                $coluna->default = $info[5];
                $coluna->primaryKey = $info[6];
                $coluna->unique = $info[7];

                $resultado[$info[0]] = $coluna;
            }

            return $resultado;
        }
     }
     
     /**
     * Retorna a estrutura completa dos campos.
     * 
     * Os parametros s�o filtros no sql.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $tabela Nome da tabela.
     * @param string $coluna Nome da coluna.
     * @return array de stdClass Colunas da tabela. 
     */
    public static function obterObjetosDasColunasDaTabela($esquema = NULL, $tabela = NULL, $coluna = NULL)
    {        
        $msql = new MSQL();
        $msql->setTables("pg_attribute 
               INNER JOIN pg_class 
                       ON pg_class.oid = pg_attribute.attrelid
                      AND pg_class.relkind = 'r'
               INNER JOIN pg_namespace
                       ON pg_namespace.oid = pg_class.relnamespace
          
               -- TYPE
               INNER JOIN pg_type
                       ON pg_type.oid = pg_attribute.atttypid 
                      AND pg_type.typname NOT IN ('oid', 'tid', 'xid', 'cid')
          
                -- DEFAULT VALUE
                LEFT JOIN pg_attrdef 
                       ON pg_attrdef.adrelid = pg_attribute.attrelid 
                      AND pg_attrdef.adnum = pg_attribute.attnum

                -- FKS
                LEFT JOIN pg_constraint
                       ON pg_constraint.conrelid = pg_attribute.attrelid
                      AND pg_attribute.attnum = ANY(pg_constraint.conkey)
                LEFT JOIN pg_class AS toTable
                       ON toTable.oid = pg_constraint.confrelid
                LEFT JOIN pg_namespace AS toSchema
                       ON toSchema.oid = toTable.relnamespace
                LEFT JOIN pg_attribute AS toColumn
                       ON toColumn.attrelid = toTable.oid 
                      AND conkey @> ARRAY[ pg_attribute.attnum ]
                      AND position(toColumn.attnum::text IN array_to_string(confkey, ' ')) <> 0

                -- COMMENT
                LEFT JOIN pg_description
                       ON pg_description.objoid = pg_class.oid
                      AND pg_description.objsubid = pg_attribute.attnum");

        $msql->setColumns("pg_attribute.attname AS id,
                           format_type(pg_type.oid, NULL) AS tipo,
                           CASE
                               WHEN pg_description.description IS NULL THEN initcap(pg_attribute.attname)
                               ELSE pg_description.description 
                           END AS titulo,
                           pg_attribute.attnotnull AS obrigatorio,
                           CASE
                               WHEN pg_get_expr(pg_attrdef.adbin, pg_attrdef.adrelid) = 'false' THEN 'f'
                               WHEN pg_get_expr(pg_attrdef.adbin, pg_attrdef.adrelid) = 'true' THEN 't'
                                ELSE pg_get_expr(pg_attrdef.adbin, pg_attrdef.adrelid) 
                           END AS valorPadrao, 
                           CASE WHEN pg_attribute.atttypmod > 4 THEN ( pg_attribute.atttypmod - 4 ) ELSE NULL END AS tamanho,
                           pg_constraint.contype AS restricao,
                           toSchema.nspname AS fkEsquema,
                           toTable.relname AS fkTabela,
                           toColumn.attname AS fkColuna,
                           pg_namespace.nspname AS esquema,
                           pg_class.relname AS tabela,
                           pg_namespace.nspname || '__' || pg_class.relname || '__' || pg_attribute.attname AS campo");

        $parametros = array();

        // Ignora campos da baslog.
        $msql->setWhere("pg_attribute.attname NOT IN (SELECT column_name FROM information_schema.columns WHERE table_name = 'baslog')");

        if ($tabela)
        {
            $msql->setWhere('pg_class.relname = ?');
            $parametros[] = strtolower($tabela);
        }

        if ($esquema)
        {
            $msql->setWhere('pg_namespace.nspname = ?');
            $parametros[] = $esquema;
        }

        if ($coluna)
        {
            $msql->setWhere('pg_attribute.attname = ?');
            $parametros[] = $coluna;
        }
        
        $msql->setWhere("CASE WHEN pg_class.relname = 'basperson' THEN (CASE WHEN pg_attribute.attname = 'personid' THEN pg_constraint.contype <> 'f' ELSE TRUE END) ELSE TRUE END");
        
        $resultado = bBaseDeDados::consultar($msql->select($parametros));
//        mutil::flog($msql->select($parametros));

        $colunas = array();
        
        foreach ($resultado as $linha)
        {
            $coluna = new bInfoColuna();
            list(
                    $coluna->nome,
                    $coluna->tipo,
                    $coluna->titulo,
                    $coluna->obrigatorio,
                    $coluna->valorPadrao,
                    $coluna->tamanho,
                    $coluna->restricao,
                    $coluna->fkEsquema,
                    $coluna->fkTabela,
                    $coluna->fkColuna,
                    $coluna->esquema,
                    $coluna->tabela,
                    $coluna->campo
                    ) = $linha;

            if ($colunas[$coluna->nome] != NULL)
            {
                $coluna->fkEsquema = $colunas[$coluna->nome]->fkEsquema;
                $coluna->fkTabela = $colunas[$coluna->nome]->fkTabela;
                $coluna->fkColuna = $colunas[$coluna->nome]->fkColuna;
            }

            $colunas[$coluna->nome] = $coluna;
        }

        return $colunas;
    }

    /**
     * M�todo est�tico para obter coment�rio da tabela.
     * 
     * @param String $nomeDaTabela Nome da tabela.
     * @return String Coment�rio da tabela. 
     */
    public static function obterComentarioDaTabela($nomeDaTabela)
    {
        if (!strlen($nomeDaTabela))
        {
            return NULL;
        }

        $msql = new MSQL();
        $msql->setTables('pg_class');
        $msql->setColumns('obj_description(oid)');
        $msql->setWhere("relkind = 'r' 
                     AND relname = lower(?)");

        $resultado = bBaseDeDados::consultar($msql->select(array($nomeDaTabela)));

        return ucfirst($resultado[0][0]);
    }
    
    /**
     * Função genérica que busca tabela do banco de dados.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $nomeTabela Nome da tabela.
     * @return array de stdClass Lista de tabelas.
     */
    public static function listarTabelas($esquema = NULL, $nomeTabela = NULL, $orderTable = NULL)
    {
        $sql = " --listarTabelas
                 SELECT schemaname,
                        tablename,
                        tableowner,
                        tablespace,
                        hasindexes,
                        hasrules,
                        hastriggers
                   FROM pg_catalog.pg_tables
                   ";

        if ($esquema)
        {
            $where[] = "schemaname ='$esquema' \n";
        }

        if ($nomeTabela)
        {
// Garante que vai encontrar a tabela.
            $nomeTabela = strtolower($nomeTabela);
            $where[] = "tablename ='$nomeTabela' \n";
        }
        
        if (is_array($where))
        {
            $where = ' WHERE ' . implode(' AND ', $where) . ' ';
            $sql .= $where;
        }
        //ordenar tabela utilizar order no fim do codigo
        if($orderTable)
        {
            $sql .= ' ORDER BY tablename';
        }

        $tabelas = bBaseDeDados::consultar($sql);
//die($tabelas);
// Trata os dados para retornar um stdClass.
        if (is_array($tabelas))
        {
            foreach ($tabelas as $linha => $tabela)
            {
                $info = new stdClass();
                $info->schemaname = $tabela[0];
                $info->tablename = $tabela[1];
                $info->tableowner = $tabela[2];
                $info->tablespace = $tabela[3];
                $info->hasindexes = $tabela[4];
                $info->hastriggers = $tabela[5];

                $resultado[$linha] = $info;
            }
        }

        return $resultado;
    }

    /**
     * Verifica se uma tabela existe na base de dados.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $nomeTabela Nome da tabela.
     * @return boolean Retorno positivo caso a tabela exista no esquema especificado.
     */
    public static function verificarExistenciaDaTabela($esquema = NULL, $nomeTabela = NULL)
    {
        $tabela = self::listarTabelas($esquema, $nomeTabela);
        $tabela = $tabela[0];

        return strtolower($tabela->tablename) == strtolower($nomeTabela);
    }

    /**
     * Lista chave estrangeiras de uma tabela.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $tabela Nome da tabela.
     * 
     * @return array de stdClass Lista de chaves estrangeiras.
     */
    public static function obterChavesEstrangeiras($esquema = NULL, $tabela = NULL)
    {
        $sql = "  SELECT
                        n.nspname AS esquema,
                        cl.relname AS tabela,
                        a.attname AS coluna,
                        ct.conname AS chave,
                        nf.nspname AS esquema_ref,
                        clf.relname AS tabela_ref,
                        af.attname AS coluna_ref,
                        pg_get_constraintdef(ct.oid) AS criar_sql
                   FROM pg_catalog.pg_attribute a
                   JOIN pg_catalog.pg_class cl
                     ON (a.attrelid = cl.oid AND cl.relkind = 'r')
                   JOIN pg_catalog.pg_namespace n
                     ON (n.oid = cl.relnamespace)
                   JOIN pg_catalog.pg_constraint ct
                     ON (a.attrelid = ct.conrelid
                    AND ct.confrelid != 0
                    AND ct.conkey[1] = a.attnum)
                   JOIN pg_catalog.pg_class clf
                     ON (ct.confrelid = clf.oid
                    AND clf.relkind = 'r')
                   JOIN pg_catalog.pg_namespace nf
                     ON (nf.oid = clf.relnamespace)
                   JOIN pg_catalog.pg_attribute af
                     ON (af.attrelid = ct.confrelid
                    AND af.attnum = ct.confkey[1])
                    ";

        $where = NULL;

        if ($esquema)
        {
            $where[] = " n.nspname = '$esquema' ";
        }

        if ($tabela)
        {
            $tabela = strtolower($tabela);
            $where[] = " cl.relname = '$tabela' ";
        }

        $sql .= ' WHERE ' . implode("\n AND ", $where) . ';';

        $resultado = bBaseDeDados::consultar($sql);

        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $info)
            {
// Explode as definições para conseguir obter chaves duplas, pois o sql normal não retorna-as.
                $definicao = $info[7];
                $definicao = explode('(', $definicao);

// Separa a coluna.
                $coluna = $definicao[1];
                $coluna = explode(')', $coluna);
                $coluna = strtolower(str_replace(' ', '', trim($coluna[0])));

// Separa a coluna de referência.
                $colunaRef = $definicao[2];
                $colunaRef = explode(')', $colunaRef);
                $colunaRef = strtolower(str_replace(' ', '', trim($colunaRef[0])));

                $infoObj = new stdClass();
                $infoObj->name = $info[3];
                $infoObj->schema = $info[0];
                $infoObj->table = $info[1];
                $infoObj->column = $coluna ? $coluna : $info[2];
                $infoObj->schemaRef = $info[4];
                $infoObj->tableRef = $info[5];
                $infoObj->columnRef = $colunaRef ? $colunaRef : $info[6];
                $infoObj->definition = $info[7];
                $resultadoN[$infoObj->name] = $infoObj;
            }
        }

        return $resultadoN;
    }

    /**
     * Obtem os indices de uma tabela.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $tabela Nome da tabela.
     */
    public static function obterIndices($esquema = NULL, $tabela)
    {
        $sql = " SELECT schemaname,
                        tablename,
                        indexname,
                        tablespace,
                        indexdef
                   FROM pg_catalog.pg_indexes";

        $where = array();

        if ($esquema)
        {
            $where[] = "schemaname ='$esquema'";
        }

        if ($tabela)
        {
            $tabela = strtolower($tabela);
            $where[] = "tablename ='$tabela'";
        }

        $where = ' WHERE ' . implode("\n AND ", $where);

        $resultado = bBaseDeDados::consultar($sql . $where);

// Trata informações transformando em objeto.
        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $info)
            {
                $infoObj = new stdClass();
                $infoObj->schema = $info[0];
                $infoObj->table = $info[1];
                $infoObj->index = $info[2];
                $infoObj->space = $info[3];

// Separa as colunas que montam o índice baseado em sua definição.
                $definicao = $info[4];
                $pos = stripos($definicao, '(');

                $colunas = substr($definicao, $pos + 1, strlen($definicao) - $pos - 2);
                $infoObj->columns = explode(',', strtolower(str_replace(' ', '', $colunas)));

// Define o tipo do índice baseado em sua definição.
                $type = 'index';

                if (stripos(strtolower($definicao), 'unique'))
                {
                    $type = 'unique';
                }

                $infoObj->type = $type;
                $infoObj->definition = $definicao;

                $resultadoN[$infoObj->index] = $infoObj;
            }
        }

        return $resultadoN;
    }

    /**
     * Obter os checks/constraints de uma tabela.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $nomeTabela Nome da tabela.
     */
    public static function obterChecagens($esquema = NULL, $nomeTabela, $check = NULL)
    {
        $sql = "--obterChecagens
               SELECT conname,
                      consrc 
                 FROM pg_catalog.pg_attribute a
                 JOIN pg_catalog.pg_class cl
                   ON (a.attrelid = cl.oid AND cl.relkind = 'r')
                 JOIN pg_catalog.pg_constraint ct
                   ON (a.attrelid = ct.conrelid)
                 JOIN pg_catalog.pg_tables t
                   ON (cl.relname = t.tablename)
                  AND ct.conkey[1] = a.attnum
                WHERE consrc <> ''
                ";

// Monta condições dinamicamente.
        $where = '';

        if ($esquema)
        {
            $where .= " AND t.schemaname='{$esquema}'\n";
        }

        if ($nomeTabela)
        {
            $nomeTabela = strtolower($nomeTabela);
            $where .= " AND cl.relname = '{$nomeTabela}'\n";
        }

        if ($check)
        {
            $check = strtolower($check);
            $where .= " AND conname = '{$check}'\n";
        }

        $sql = $sql . $where;

        $resultado = bBaseDeDados::consultar($sql);

// Converte cada linha para um objeto.
        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $info)
            {
                $checkObj = new stdClass();
                $checkObj->name = $info[0];
                $checkObj->check = $info[1];

                $resultado[$linha] = $checkObj;
            }
        }

        return $resultado;
    }

    /**
     * Retorna o código fonte das funções do banco.
     * 
     * @param string $funcao Nome da função.
     * @return string Código fonte das funções do banco.
     */
    public static function obterCodigoFonteDaFuncao($funcao = NULL)
    {
        $sql = "SELECT 'CREATE OR REPLACE FUNCTION ' || proname || '(' ||
                    coalesce( ( SELECT array_to_string( array_agg(coalesce(varname,'') || ' ' ||
                            ( SELECT coalesce(typname,'') 
                                FROM pg_type
                                WHERE oid::varchar = type::varchar
                            ) ) , ' , ' )
                            FROM (    SELECT  unnest( coalesce(proargnames,ARRAY[''] ) ) as varname,
                                            regexp_split_to_table( proargtypes::varchar , E'\\ +') as type
                                    FROM  pg_proc A
                                    WHERE pronamespace = 2200 and A.proname = B.proname and A.oid = B.oid
                            ) as foo 
                    ),'') || ') \nRETURNS ' ||
                    ( SELECT typname FROM pg_type where oid = prorettype )
                    || ' as \$BODY\$' || prosrc ||
                    ' \$BODY\$ language ' || ( SELECT lanname FROM pg_language l WHERE l.oid = b.prolang ) || ';\n' 
              FROM pg_proc B
             WHERE pronamespace = 2200 
            ";

        if ($funcao)
        {
            $sql .= ' AND lower(b.proname ) = \'' . $funcao . '\' ';
        }

        return bBaseDeDados::consultar($sql);
    }

    /**
     * Lista as funções do banco.
     * 
     * @return array of stdClass Lista de funções.
     */
    public static function listarFuncoes()
    {
        $sql = "SELECT lower(proname) as function,
                        ( SELECT typname FROM pg_type where oid = prorettype ) as return,
                        ( SELECT array_to_string( array_agg(coalesce(varname,'') || ' ' ||
                            ( SELECT coalesce(typname,'') 
                                FROM pg_type
                                WHERE oid::varchar = type::varchar
                            ) ) , ' , ' )
                                FROM (    SELECT  unnest( coalesce(proargnames,ARRAY[''] ) ) as varname,
                                                    regexp_split_to_table( proargtypes::varchar , E'\\ +') as type
                                            FROM  pg_proc A
                                            WHERE pronamespace = 2200 and A.proname = B.proname and A.oid = B.oid
                                    ) as foo 
                            ) as params
                  FROM pg_proc B
                 WHERE pronamespace = 2200 order by 1;";

        $resultado = bBaseDeDados::consultar($sql);

        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $info)
            {
                $obj = new stdClass();
                $obj->function = $info[0];
                $obj->return = $info[1];
                $obj->params = $info[2];
                $resultado[$linha] = $obj;
            }
        }

        return $resultado;
    }

    /**
     * Lista todas as visões de um esquema
     * 
     * @param string $esquema Nome do esquema.
     * @return array de stdClass Lista de visões.
     */
    public static function listarVisoes($esquema = 'public')
    {
        $sql = 'SELECT * FROM pg_views';

        if ($esquema != NULL)
        {
            $where[] = "schemaname = '$esquema'";
        }

        $where = ' WHERE ' . implode(' AND', $where);

        $sql .= $where;

        $resultado = bBaseDeDados::consultar($sql);

        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $view)
            {
                $viewObj = new stdClass();
                $viewObj->schema = $view[0];
                $viewObj->name = $view[1];
                $viewObj->owner = $view[2];
                $viewObj->source = $view[3];
                $resultado[$linha] = $viewObj;
            }
        }

        return $resultado;
    }

    /**
     * Lista uma ou mais sequências.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $sequencia Nome da sequência
     * @return array de stdClass Lista de sequências.
     */
    public static function listarSequencias($esquema = NULL, $sequencia = NULL)
    {
        $sql = "--listarSequencias
                SELECT  sequence_schema,
                        sequence_name,
                        data_type,
                        numeric_precision,
                        numeric_precision_radix,
                        numeric_scale,
                        maximum_value,
                        minimum_value,
                        increment,
                        cycle_option 
                FROM information_schema.sequences";

        $where = '';

        if ($esquema)
        {
            $where[] = " sequence_schema = '$esquema' ";
        }

        if ($sequencia)
        {
            $sequencia = strtolower($sequencia);
            $where[] = " sequence_name = '$sequencia';";
        }

        $sql .= ' WHERE ' . implode("\n AND ", $where);

        $resultado = bBaseDeDados::consultar($sql);

        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $info)
            {
                $infoObj = new stdClass();
                $infoObj->schema = $info[0];
                $infoObj->name = $info[1];
                $infoObj->dataType = $info[2];
                $infoObj->precision = $info[3];
                $infoObj->precisionRadix = $info[4];
                $infoObj->numericScale = $info[5];
                $infoObj->maxValue = $info[6];
                $infoObj->minValue = $info[7];
                $infoObj->increment = $info[8];
                $infoObj->cycleOption = $info[9];

                $resultadoN[$infoObj->name] = $infoObj;
            }
        }

        return $resultadoN;
    }

    /**
     * Método público e estático que retorna uma lista com gatilhos de determinado esquema e tabela.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $gatilho Nome do gatilho.
     * @param string $tabela Nome da tabela.
     * @return array de stdClass Lista de gatilhos.
     */
    public static function listarGatilhos($esquema = NULL, $gatilho = NULL, $tabela = NULL)
    {
        $sql = "-- listarGatilhos
               SELECT trigger_schema as schema,
                     trigger_name as name,
                     event_manipulation as event,
                     event_object_schema as eventSchema,
                     event_object_table as table,
                     action_statement as action,
                     action_orientation as orientation
               FROM information_schema.triggers
          ";

        if ($esquema)
        {
            $where[] = "event_object_schema = '$esquema'";
        }

        if ($tabela)
        {
            $where[] = "event_object_table = '$tabela'";
        }

        if ($gatilho)
        {
            $where[] = "trigger_name = '$gatilho'";
        }

        $sql .= ' WHERE ' . implode("\n AND ", $where);
        $resultado = bBaseDeDados::consultar($sql);
        $resultadoN = array();

        if (is_array($resultado))
        {
            foreach ($resultado as $linha => $info)
            {
                $infoObj = new stdClass();
                $infoObj->schema = $info[0];
                $infoObj->name = $info[1];
                $infoObj->event = $info[2];
                $infoObj->eventSchema = $info[3];
                $infoObj->table = $info[4];
                $infoObj->action = $info[5];
                $infoObj->orientation = $info[6];

// Precisa ser assim porque uma trigger de UPDATE DELETE E INSERT aparece 3 vezes.
                $resultadoN[$infoObj->name][] = $infoObj;
            }
        }

        return $resultadoN;
    }

    /**
     * Adiciona uma coluna em uma tabela
     * @param string $esquema
     * @param string $tabela
     * @param string $coluna
     * @param string $tipo
     * @return boolean
     */
    public static function adicionarColuna($esquema, $tabela, $coluna, $tipo)
    {
// Caso não exista um esquema coloca o público.
        if (!$esquema)
        {
            $esquema = 'public';
        }

        $sql = "ALTER TABLE $esquema.$tabela ADD COLUMN $coluna $tipo";

        return bBaseDeDados::consultar($sql);
    }

    /**
     * Remove uma coluna.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $tabela Nome da tabela.
     * @param string $coluna Nome da coluna.
     * @return boolean Retorna positivo se a coluna foi removida foi sucesso.
     */
    public static function removerColuna($esquema = NULL, $tabela, $coluna)
    {
// Caso não exista um esquema coloca o público.
        if (!$esquema)
        {
            $esquema = 'public';
        }

        $sql = "ALTER TABLE $esquema.$tabela DROP COLUMN $coluna";

        return bBaseDeDados::consultar($sql);
    }

    /**
     * Remove uma coluna caso exista.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $tabela Nome da tabela.
     * @param string $coluna Nome da coluna.
     * @return boolean Retorna positivo se a coluna foi removida com sucesso.
     */
    public static function removerColunaSeExistir($esquema = NULL, $tabela, $coluna)
    {
        if (self::obterColunasDaTabela($esquema, $tabela, $coluna))
        {
            return self::removerColuna($esquema, $tabela, $coluna);
        }

        return FALSE;
    }

    /**
     * Executa a criação de uma sequência.
     * 
     * @param string $nomeSequencia Nome da sequência.
     * @return boolean Retorna positivo se a sequência foi criada com sucesso.
     */
    public static function criarSequencia($nomeSequencia)
    {
        return bBaseDeDados::consultar("CREATE SEQUENCE $nomeSequencia;");
    }

    /**
     * Executa a criação de uma sequencia, caso não exista.
     * 
     * @param string $nomeSequencia Nome da sequência.
     * @return boolean Retorna positivo se a sequência foi criada com sucesso.
     */
    public static function criarSequenciaQuandoPossivel($nomeSequencia)
    {
        $sequencia = self::listarSequencias(NULL, $nomeSequencia);
        $sequencia = $sequencia[strtolower($nomeSequencia)];

        if (!$sequencia)
        {
            return self::criarSequencia($nomeSequencia);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Renomeia uma tabela na base de dados.
     * 
     * @param string $nomeAntigo Nome que  a tabela tem atualmente.
     * @param string $nomeNovo Nome que  a tabela terá.
     * @return boolean Retorna positivo se a tabela foi renomeada
     */
    public static function renomearTabela($nomeAntigo, $nomeNovo)
    {
//Renomeia a tabela para um novo nome
        $sql = "ALTER TABLE $nomeAntigo RENAME TO $nomeNovo;";

        return bBaseDeDados::consultar($sql);
    }

    /**
     * Renomeia uma tabela somente se ela existir na base de dados.
     * 
     * @param string $nomeAntigo Nome que  a tabela tem atualmente.
     * @param string $nomeNovo Nome que  a tabela terá.
     * @return boolean Retorna positivo se a tabela foi renomeada
     */
    public static function renomearTabelaSeExistir($nomeAntigo, $nomeNovo)
    {
//Verifica se a tabela existe na base de dados
        if (self::verificarExistenciaDaTabela(null, $nomeAntigo))
        {
//Renomeia a tabela para um novo nome
            self::renomearTabela($nomeAntigo, $nomeNovo);
        }

        return FALSE;
    }

    /**
     * Remove uma tabela na base de dados.
     * 
     * @param string $nomeAntigo nome que a tabela tem atualmente.

     * @return boolean Retorna positivo se a tabela foi removida
     */
    public static function removerTabela($esquema, $nome)
    {
        if (!$esquema)
        {
            $esquema = 'public';
        }

        return bBaseDeDados::consultar("DROP TABLE $esquema.$nome;");
    }

    /**
     * Remove uma tabela na base de dados.
     * 
     * @param string $nomeAntigo nome que a tabela tem atualmente.

     * @return boolean Retorna positivo se a tabela foi removida
     */
    public static function removerTabelaSeExistir($esquma, $nome)
    {
        if (self::verificarExistenciaDaTabela($esquema, $nome))
        {
            return self::removerTabela($esquema, $nome);
        }

        return false;
    }

    /**
     * Determinar de qual tabela a tabela passada possui herança
     * 
     * @param string $schema
     * @param string $tabela
     * @return stdClass com tabela e esquema
     */
    public static function obterHeranca($schema, $tabela)
    {
        if (!$schema)
        {
            $schema = 'public';
        }

        $tabela = strtolower($tabela);

        $sql = "   SELECT bt.relname as table_name,
                         bns.nspname as table_schema 
                    FROM pg_class ct 
                    JOIN pg_namespace cns on ct.relnamespace = cns.oid and cns.nspname = '$schema' 
                    JOIN pg_inherits i on i.inhrelid = ct.oid and ct.relname = '$tabela' 
                    JOIN pg_class bt on i.inhparent = bt.oid 
                    JOIN pg_namespace bns on bt.relnamespace = bns.oid;";

        $result = bBaseDeDados::consultar($sql);
        $result = $result[0];

        if ($result)
        {
            $inherit = new stdClass();
            $inherit->table = strtolower($result[0]);
            $inherit->schema = strtolower($result[1]);

            return $inherit;
        }

        return null;
    }

    public static function listarLinguagens()
    {
        $data = bBaseDeDados::consultar("SELECT lanname FROM pg_language;");

        $languages = array();

        if (is_array($data))
        {
            foreach ($data as $line => $info)
            {
                $languages[$line] = $info[0];
            }
        }

        return $languages;
    }

}

?>
