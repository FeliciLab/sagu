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

        $msql = new MSQL();
        $msql->setTables('pg_catalog.pg_attribute a
               INNER JOIN pg_stat_user_tables c 
                       ON a.attrelid = c.relid');

        $msql->setColumns('attname,
                           attname');

        $msql->setWhere('a.attnum > 0
                   AND NOT a.attisdropped
                   AND c.relname = lower(?)
              ORDER BY c.relname, a.attname');

        $colunas = bBaseDeDados::consultar($msql, $tabela);

        $coluna = array();

        foreach ($colunas as $linha => $info)
        {
            $coluna[] = $info[0];
        }

        return $coluna;
    }

    /**
     * Obtém dados da coluna. Descrição, tipo e valor padrão.
     *
     * @param string $coluna Nome da coluna.
     * @param string $tabela Nome da tabela.
     * @param string $esquema Nome do esquema.
     */
    public static function buscarDadosDaColuna($coluna, $tabela, $esquema = '')
    {
        $msql = new MSQL();
        $msql->setTables('pg_class c
               INNER JOIN pg_namespace AS n ON ( n.oid = c.relnamespace )
               INNER JOIN pg_attribute AS a ON ( a.attrelid = c.oid )
               INNER JOIN pg_type AS t ON ( a.atttypid = t.oid )
                LEFT JOIN pg_attrdef AS def ON ( def.adrelid = c.oid AND a.attnum = def.adnum )
                LEFT JOIN pg_description AS d ON ( d.objoid = c.oid AND d.objsubid = a.attnum )');

        $msql->setColumns("CASE WHEN d.description <> '' THEN d.description ELSE a.attname END,
                          format_type(t.oid, null) as typname,
                          pg_get_expr(def.adbin,def.adrelid)");

        $msql->setWhere("c.relkind = 'r'
                     AND n.nspname NOT LIKE 'pg\\_%'
                     AND n.nspname != 'information_schema'
                     AND a.attnum > 0
                     AND NOT a.attisdropped
                     AND a.attname = ?
                     AND c.relname = ?");

        $parametros = array($coluna, $tabela);

        if ($esquema)
        {
            $msql->setWhere('n.nspname ILIKE ?');
            $parametros[] = $esquema;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

        $infoColuna = new bInfoColuna();
        list(
                $infoColuna->label,
                $infoColuna->tipo,
                $infoColuna->valorPadrao
                ) = current($resultado);

        return $infoColuna;
    }

    /**
     * Busca as chaves estrangeiras da tabela.
     *
     * @todo Unir com obterChavesEstrangeiras.
     * @param string $tabela Tabela da qual se quer obter a lista de colunas.
     * @param string $esquema Esquema do qual a tabela faz parte. Padrão é public.
     * @param character $tipo Tipo de relação. Usar constantes TIPO_*.
     * @return array Matriz com os dados das chaves estrangeiras.
     */
    public static function buscarChavesEstrangeirasDaTabela($tabela, $esquema = 'public')
    {
        $msql = new MSQL();
        $msql->setTables("pg_catalog.pg_class AS c
                LEFT JOIN pg_namespace AS n 
                       ON (n.oid = c.relnamespace)
               INNER JOIN pg_catalog.pg_constraint AS rel
                       ON (c.oid=rel.conrelid)
                LEFT JOIN pg_catalog.pg_class AS toTable
                       ON (toTable.oid = rel.confrelid)
                LEFT JOIN pg_namespace AS toSchemaName
                       ON (toSchemaName.oid = toTable.relnamespace)
                LEFT JOIN pg_catalog.pg_attribute AS fk_col
                       ON fk_col.attrelid = rel.conrelid AND (conkey @> ARRAY[ fk_col.attnum ] AND position(fk_col.attnum::text in array_to_string(conkey, ' ')) <>0 )
                LEFT JOIN pg_catalog.pg_attribute AS fk_col2
                       ON fk_col2.attrelid = rel.confrelid AND (conkey @> ARRAY[ fk_col.attnum ] AND position(fk_col2.attnum::text in array_to_string(confkey, ' ')) <>0 )");

        $msql->setColumns("DISTINCT n.nspname   AS from_schema_name,
                           c.relname            AS from_table_name,
                           fk_col.attname       AS from_column_name,
                           toSchemaName.nspname AS to_schema_name,
                           toTable.relname      AS to_table_name,
                           fk_col2.attname      AS to_column_name,
                           fk_col.attnotnull    AS obrigatorio");

        $msql->setWhere("rel.contype='f'
                      AND c.relkind = 'r'
                      AND n.nspname NOT LIKE 'pg\\_%'
                      AND n.nspname != 'information_schema'
                      AND c.relname = ?");

        $parametros = array($tabela);

        if (strlen($esquema))
        {
            $msql->setWhere('n.nspname ILIKE ?');
            $parametros[] = $esquema;
        }

        return bBaseDeDados::consultar($msql, $parametros);
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
        
        $resultado = bBaseDeDados::consultar($msql, $parametros);
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
     * Função genérica que busca tabela do banco de dados.
     * 
     * @param string $esquema Nome do esquema.
     * @param string $nomeTabela Nome da tabela.
     * @return array de stdClass Lista de tabelas.
     */
    public static function listarTabelas($esquema = NULL, $nomeTabela = NULL)
    {
        $msql = new MSQL();
        $msql->setTables('pg_catalog.pg_tables');
        $msql->setColumns('schemaname,
                        tablename,
                        tableowner,
                        tablespace,
                        hasindexes,
                        hasrules,
                        hastriggers');

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('schemaname = ?');
            $parametros[] = $esquema;
        }

        if ($nomeTabela)
        {
            $msql->setWhere('tablename = lower(?)');
            $parametros[] = $nomeTabela;
        }

        $tabelas = bBaseDeDados::consultar($msql, $parametros);

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
        $msql = new MSQL();
        $msql->setTables("pg_catalog.pg_attribute a
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
                      AND af.attnum = ct.confkey[1])");

        $msql->setColumns('n.nspname AS esquema,
                           cl.relname AS tabela,
                           a.attname AS coluna,
                           ct.conname AS chave,
                           nf.nspname AS esquema_ref,
                           clf.relname AS tabela_ref,
                           af.attname AS coluna_ref,
                           pg_get_constraintdef(ct.oid) AS criar_sql');

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('n.nspname = ?');
            $parametros[] = $esquema;
        }

        if ($tabela)
        {
            $msql->setWhere('cl.relname = lower(?)');
            $parametros[] = $tabela;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

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
        $msql = new MSQL();
        $msql->setTables('pg_catalog.pg_indexes');
        $msql->setColumns('schemaname,
                           tablename,
                           indexname,
                           tablespace,
                           indexdef');

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('schemaname = ?');
            $parametros[] = $esquema;
        }

        if ($tabela)
        {
            $msql->setWhere('tablename = lower(?)');
            $parametros[] = $tabela;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

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
        $msql = new MSQL();
        $msql->setColumns('conname,
                           consrc');

        $msql->setTables("pg_catalog.pg_attribute a
                 JOIN pg_catalog.pg_class cl
                   ON (a.attrelid = cl.oid AND cl.relkind = 'r')
                 JOIN pg_catalog.pg_constraint ct
                   ON (a.attrelid = ct.conrelid)
                 JOIN pg_catalog.pg_tables t
                   ON (cl.relname = t.tablename)
                  AND ct.conkey[1] = a.attnum");

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('t.schemaname = ?');
            $parametros[] = $esquema;
        }

        if ($nomeTabela)
        {
            $msql->setWhere('cl.relname = lower(?)');
            $parametros[] = $nomeTabela;
        }

        if ($check)
        {
            $msql->setWhere('conname = lower(?)');
            $parametros[] = $check;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

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
        $msql = new MSQL();
        $msql->setTables('pg_proc B');
        $msql->setColumns("'CREATE OR REPLACE FUNCTION ' || proname || '(' ||
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
                    ' \$BODY\$ language ' || ( SELECT lanname FROM pg_language l WHERE l.oid = b.prolang ) || ';\n'");

        $msql->setWhere('pronamespace = 2200');

        $parametros = array();
        if ($funcao)
        {
            $msql->setWhere('lower(b.proname ) = lower(?)');
            $parametros[] = $funcao;
        }

        return bBaseDeDados::consultar($msql, $parametros);
    }

    /**
     * Lista as funções do banco.
     * 
     * @return array of stdClass Lista de funções.
     */
    public static function listarFuncoes()
    {
        $msql = new MSQL();
        $msql->setTables('pg_proc B');
        $msql->setColumns("lower(proname) as function,
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
                            ) as params");

        $msql->setWhere('pronamespace = 2200');
        $msql->setOrderBy('1');

        $resultado = bBaseDeDados::consultar($msql);

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
        $msql = new MSQL();
        $msql->setTables('pg_views');
        $msql->setColumns('*');

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('schemaname = ?');
            $parametros[] = $esquema;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

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
        $msql = new MSQL();
        $msql->setColumns('sequence_schema,
                        sequence_name,
                        data_type,
                        numeric_precision,
                        numeric_precision_radix,
                        numeric_scale,
                        maximum_value,
                        minimum_value,
                        increment,
                        cycle_option');

        $msql->setTables('information_schema.sequences');

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('sequence_schema = ?');
            $parametros[] = $esquema;
        }

        if ($sequencia)
        {
            $msql->setWhere('sequence_name = lower(?)');
            $parametros[] = $sequencia;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

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
        $msql = new MSQL();
        $msql->setTables('information_schema.triggers');
        $msql->setColumns('trigger_schema as schema,
                           trigger_name as name,
                           event_manipulation as event,
                           event_object_schema as eventSchema,
                           event_object_table as table,
                           action_statement as action,
                           action_orientation as orientation');

        $parametros = array();

        if ($esquema)
        {
            $msql->setWhere('event_object_schema = ?');
            $parametros[] = $esquema;
        }

        if ($tabela)
        {
            $msql->setWhere('event_object_table = ?');
            $parametros[] = $tabela;
        }

        if ($gatilho)
        {
            $msql->setWhere('trigger_name = ?');
            $parametros[] = $gatilho;
        }

        $resultado = bBaseDeDados::consultar($msql, $parametros);

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
     * Obtém os esquemas presentes na base.
     *
     * @param string $tabela - Nome da tabela que precisa obter o esquema.
     * 
     * @return array Vetor para ser utilizado em componentes do tipo combo.
     */
    public static function listarEsquemas($tabela = null)
    {
        $msql = new MSQL();
        $msql->setTables('pg_namespace
               LEFT JOIN pg_description
                      ON pg_description.objoid = pg_namespace.oid');

        $msql->setColumns('nspname, 
                           CASE WHEN description IS NOT NULL THEN description ELSE nspname END');

        $where = '';
        if ( strlen($tabela) > 0 )
        {
            $msql->addLeftJoin('pg_class', 'pg_namespace.oid = pg_class.relnamespace');
            $where = ' AND pg_class.relname = ? ';
            $msql->addParameter($tabela);
        }
        
        $msql->setWhere("nspname NOT LIKE 'pg_%' 
                   AND nspname <> 'information_schema'" . $where);
        
        return bBaseDeDados::consultar($msql);
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

        return bBaseDeDados::executar($sql);
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

        return bBaseDeDados::executar($sql);
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
        return bBaseDeDados::executar("CREATE SEQUENCE $nomeSequencia;");
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

        return bBaseDeDados::executar($sql);
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
        else
        {
            return FALSE;
        }
    }

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

        $msql = new MSQL();

        $msql->setTables("pg_class ct 
                     JOIN pg_namespace cns on ct.relnamespace = cns.oid and cns.nspname = '$schema' 
                     JOIN pg_inherits i on i.inhrelid = ct.oid and ct.relname = '$tabela' 
                     JOIN pg_class bt on i.inhparent = bt.oid 
                     JOIN pg_namespace bns on bt.relnamespace = bns.oid");

        $msql->setColumns('bt.relname as table_name,
                          bns.nspname as table_schema');

        $result = bBaseDeDados::consultar($msql);

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

    /**
     * Busca as chaves primárias da tabela.
     *
     * @param string $tabela Tabela da qual se quer obter a lista de colunas.
     * @param string $esquema Esquema do qual a tabela faz parte. Padrão é public.
     * @param character $tipo Tipo de relação. Usar constantes TIPO_*.
     * @return array Matriz com os dados das chaves primárias.
     */
    public static function buscarChavesPrimariasDaTabela($tabela, $esquema = 'public')
    {
        $msql = new MSQL();
        $msql->setTables("pg_catalog.pg_class AS c
              INNER JOIN pg_catalog.pg_constraint AS rel
                      ON (c.oid = rel.conrelid)
               LEFT JOIN pg_catalog.pg_attribute AS fk_col
                      ON fk_col.attrelid = rel.conrelid AND (position(fk_col.attnum::text in array_to_string(conkey, ' ')) <>0 )
              INNER JOIN pg_type AS t 
                      ON (fk_col.atttypid = t.oid)
               LEFT JOIN pg_attrdef AS def 
                      ON (def.adrelid = c.oid AND fk_col.attnum = def.adnum)
               LEFT JOIN pg_namespace AS n 
                      ON (n.oid = c.relnamespace)");

        $msql->setColumns('DISTINCT fk_col.attname AS column_name, 
                           format_type(t.oid, null) as typname');

        $msql->setWhere("c.relkind = 'r'
                            AND n.nspname NOT LIKE 'pg\\_%'
                            AND n.nspname != 'information_schema'
                            AND c.relname = ?");

        $parametros = array($tabela);

        if (strlen($esquema))
        {
            $msql->setWhere('n.nspname ILIKE ?');
            $parametros[] = $esquema;
        }

        return bBaseDeDados::consultar($msql, $parametros);
    }

    /**
     * Método estático para obter comentário da tabela.
     * 
     * @param String $nomeDaTabela Nome da tabela.
     * @return String Comentário da tabela. 
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

        $resultado = bBaseDeDados::consultar($msql, array($nomeDaTabela));

        return ucfirst($resultado[0][0]);
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
    
    public static function obterRelacionamentos($tabela, $tabelaEstrangeira = NULL)
    {
        $msql = new MSQL();
        $msql->setTables("pg_catalog.pg_attribute a   
            JOIN pg_catalog.pg_class cl ON (a.attrelid = cl.oid AND cl.relkind = 'r')
            JOIN pg_catalog.pg_namespace n ON (n.oid = cl.relnamespace)   
            JOIN pg_catalog.pg_constraint ct ON (a.attrelid = ct.conrelid AND   
                 ct.confrelid != 0 AND ct.conkey[1] = a.attnum)   
            JOIN pg_catalog.pg_class clf ON (ct.confrelid = clf.oid AND clf.relkind = 'r')
            JOIN pg_catalog.pg_namespace nf ON (nf.oid = clf.relnamespace)   
            JOIN pg_catalog.pg_attribute af ON (af.attrelid = ct.confrelid AND   
                 af.attnum = ct.confkey[1])");
        $msql->setColumns('a.attname AS atributo, clf.relname AS tabela_ref, af.attname AS atributo_ref, n.nspname AS esquema');
        $msql->setWhere('cl.relname = ?');
        $msql->addParameter($tabela);
        
        if ( $tabelaEstrangeira )
        {
            $msql->setWhere('clf.relname = ?');
            $msql->addParameter($tabelaEstrangeira);
        }
        
        $data = bBaseDeDados::consultar($msql);
        
        $relacionamentos = array();
        foreach($data as $key => $rel)
        {
            $relacionamentos[$key] = new stdClass();
            $relacionamentos[$key]->atributo = $rel[0];
            $relacionamentos[$key]->tabela_ref = $rel[1];
            $relacionamentos[$key]->atributo_ref = $rel[2];
            $relacionamentos[$key]->esquema = $rel[3];
        }
        
        return $relacionamentos;
    }

}

?>
