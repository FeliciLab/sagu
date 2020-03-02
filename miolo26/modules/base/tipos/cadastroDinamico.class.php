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
 * Classe que representa a tabela de cadastro dinâmico.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Class created on 16/08/2012
 *
 */
class cadastroDinamico extends bTipo
{

    public function __construct($chave)
    {
        parent::__construct($chave);
        $this->tiposRelacionados[] = 'tabelaReferenciada';
    }
    
    /**
     * Verifica se identificador existe na base.
     *
     * @param string $identificador Identificador do cadastro dinâmico.
     * @return boolean Retorna positivo caso tenha um cadastro dinâmico para o identificador.
     */
    public static function verificarIdentificador($modulo, $identificador)
    {
        $msql = new MSQL();
        $msql->setTables('cadastrodinamico');
        $msql->setColumns('count(*)');
        $msql->setWhere('identificador = ?');
        $msql->setWhere('modulo = ?');
        
        $retorno = bBaseDeDados::consultar($msql, array($identificador, $modulo));

        return ($retorno[0][0] > 0);
    }

    /**
     * Método público para popular o cadastro dinâmico através do módulo e identificador.
     * 
     * @param string $modulo Módulo do cadastro dinâmico.
     * @param string $identificador Identificador do cadastro dinâmico.
     */
    public function popularPorIdentificador($modulo, $identificador)
    {
        $filtro = new stdClass();
        $filtro->modulo = $modulo;
        $filtro->identificador = $identificador;
        
        $resultado = $this->buscar($filtro, 'cadastrodinamicoid');

        if ( is_array($resultado) )
        {
            $this->definir($resultado[0]);
            $this->popular();
        }
    }
    
    /**
     * Obtém as tabelas que são relacionadas do a tabela principal.
     * 
     * @return array Vetor com o nome das tabelas que são relacionadas. 
     */
    public function obterTabelasRelacionadas()
    {
        $tabelasRelacionadas = $this->dadosTiposRelacionados['tabelaReferenciada'];
        
        if ( is_array($tabelasRelacionadas) )
        {
            $referencias = array();
            foreach ( $tabelasRelacionadas as $tabelaRelacionada )
            {
                $referencia = explode('.', $tabelaRelacionada->referencia);
                $referencias[] = end($referencia);
            }
            
            return $referencias;
        }
    }
}

?>