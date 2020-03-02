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
 * Classe que representa uma coluna de uma tabela.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 20/08/2012
 *
 */

class bInfoColuna
{
    /**
     * @var string 
     */
    public $esquema = 'public';

    /**
     * @var string 
     */
    public $tabela;

    /**
     * @var string 
     */
    public $nome;

    /**
     * @var string 
     */
    public $tipo;

    /**
     * @var string 
     */
    public $titulo;

    /**
     * @var string 
     */
    public $obrigatorio;

    /**
     * @var string 
     */
    public $valorPadrao;

    /**
     * @var string 
     */
    public $tamanho;

    /**
     * @var character Informa se é chave estrangeira (f) ou chave primária (p).
     */
    public $restricao;

    /**
     * @var string 
     */
    public $fkEsquema;

    /**
     * @var string 
     */
    public $fkTabela;

    /**
     * @var string 
     */
    public $fkColuna;

    /**
     * @var string 
     */
    public $valoresPossiveis;

    /**
     * @var string 
     */
    public $editavel;

    /**
     * @var string 
     */
    public $visivel;

    /**
     * @var string 
     */
    public $filtravel;

    /**
     * @var string 
     */
    public $exibirNaGrid;

    /**
     * @var string 
     */
    public $parametros;

    /**
     * @var boolean 
     */
    public $chave;

    /**
     * @var string Nome do atributo da classe (type ou business) pelo qual a coluna é representada.
     */
    public $atributo;

    /**
     * @var string Nome do campo no formulário.
     */
    public $campo;

    /**
     * Constantes para os tipos de coluna.
     */
    const TIPO_TEXTO = 'character varying';
    const TIPO_CHAR = 'character';
    const TIPO_TEXTO_LONGO = 'text';
    const TIPO_INTEIRO = 'integer';
    const TIPO_INTEIRO_LONGO = 'bigint';
    const TIPO_DECIMAL = 'real';
    const TIPO_DOUBLE = 'double precision';
    const TIPO_LISTA = 'list';
    const TIPO_DATA = 'date';
    const TIPO_TIMESTAMP = 'timestamp without time zone';
    const TIPO_BOOLEAN = 'boolean';
    const TIPO_NUMERIC = 'numeric';

    /**
     * @return array Lista os tipo de campos suportados.
     */
    public static function listarTipos()
    {
        return array(
            self::TIPO_TEXTO => _M('Texto'),
            self::TIPO_TEXTO_LONGO => _M('Texto longo'),
            self::TIPO_INTEIRO => _M('Integer'),
            self::TIPO_DECIMAL => _M('Decimal'),
            self::TIPO_NUMERIC => _M('Numérico'),
            self::TIPO_LISTA => _M('Lista'),
            self::TIPO_DATA => _M('Data'),
            self::TIPO_TIMESTAMP => _M('Timestamp'),
            self::TIPO_BOOLEAN => _M('Boolean')
        );
    }

    /**
     *
     * @return boolean
     */
    public function eChavePrimaria()
    {
        return $this->restricao == 'p';
    }
    
    /**
     * 
     * @return Boolean Se � ou n�o chave estrangeira
     */
    public function eChaveEstrangeira()
    {
        return strlen($this->fkTabela) > 0;
    }
}

?>