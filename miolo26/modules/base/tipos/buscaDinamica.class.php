<?php

/**
 * <--- Copyright 2005-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
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
 * Classe que representa a tabela de busca dinâmica.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 16/08/2012
 *
 */
class buscaDinamica extends bTipo
{
    
    public function __construct($chave)
    {
        parent::__construct($chave);
        $this->tiposRelacionados[] = 'campoBuscaDinamica';
    }

    /**
     * Obtém os dados das colunas a serem utilizadas pela busca dinâmica.
     *
     * @param string $modulo Módulo.
     * @param string $identificador Identificador.
     * @return array Vetor com objetos do tipo bInfoColuna.
     */
    public static function buscarDadosDasColunas($modulo, $identificador)
    {
        $msql = new MSQL();
        $msql->setColumns("pg_attribute.attname AS id,
                           campobuscadinamica.tipo,
                           campobuscadinamica.nome AS titulo,
                           pg_attribute.attnotnull AS obrigatorio,
                           campobuscadinamica.valorPadrao,
                           CASE WHEN pg_attribute.atttypmod > 4 THEN ( pg_attribute.atttypmod - 4 ) ELSE NULL END AS tamanho,
                           pg_constraint.contype AS restricao,
                           toSchema.nspname AS fkEsquema,
                           toTable.relname AS fkTabela,
                           toColumn.attname AS fkColuna,
                           campobuscadinamica.valoresPossiveis,
                           campobuscadinamica.editavel,
                           campobuscadinamica.visivel,
                           campobuscadinamica.filtravel,
                           campobuscadinamica.exibirNaGrid,
                           campobuscadinamica.parametros,
                           campobuscadinamica.chave,
                           buscadinamica.modulo,
                           buscadinamica.ordenar,
                           pg_namespace.nspname AS esquema,
                           pg_class.relname AS tabela,
                           pg_namespace.nspname || '__' || pg_class.relname || '__' || pg_attribute.attname AS campo");
         
        $msql->setTables("pg_attribute 
               INNER JOIN pg_class 
                       ON pg_class.oid = pg_attribute.attrelid
                      AND pg_class.relkind in ('r', 'v')
               INNER JOIN pg_namespace
                       ON pg_namespace.oid = pg_class.relnamespace
               INNER JOIN campobuscadinamica
                       ON pg_namespace.nspname = split_part(campobuscadinamica.referencia, '.', 1)
                      AND pg_class.relname = split_part(campobuscadinamica.referencia, '.', 2)
                      AND pg_attribute.attname = split_part(campobuscadinamica.referencia, '.', 3)
               INNER JOIN buscadinamica
                       ON buscadinamica.buscadinamicaid = campobuscadinamica.buscadinamicaid
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
        
        $msql->setWhere('lower(buscadinamica.modulo) = lower(?)
                     AND lower(buscadinamica.identificador) = lower(?)');
                
        $parametros = array($modulo, $identificador);        
        
        $msql->setOrderBy('campobuscadinamica.posicao,
                           campobuscadinamica.nome');
//        mutil::flog($msql->select($parametros));

        $resultado = bBaseDeDados::consultar($msql, $parametros);
        
        $colunas = array();
        
        foreach ( $resultado as $linha )
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
                $coluna->valoresPossiveis,
                $coluna->editavel,
                $coluna->visivel,
                $coluna->filtravel,
                $coluna->exibirNaGrid,
                $coluna->parametros,
                $coluna->chave,
                $coluna->modulo,
                $coluna->ordenar,
                $coluna->esquema,
                $coluna->tabela,
                $coluna->campo
            ) = $linha;

            $chave = "$coluna->esquema.$coluna->tabela.$coluna->nome";
            $colunas[$chave] = $coluna;
        }
        
        return $colunas;
    }
}

?>