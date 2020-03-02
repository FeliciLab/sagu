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
 *  Classe que define os métodos javascript que podem ser utilizados no sistema.
 *          
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 31/07/2012
 */
class bJavascript
{

    /**
     * Método público e estático para definir o foco no campo desejado.
     * 
     * @param string $campoId Id do campo em que o foco será setado.
     * @param boolean $imediato Seta o foco imediatamente caso for verdadeiro.
     */
    public static function definirFoco($campoId, $imediato = TRUE)
    {
        $imediato = $imediato ? 'true' : 'false';
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirFoco('$campoId', $imediato);");
    }

    /**
     * Método público e estático para definir um display para o campo/elemento desejado.
     * 
     * @param string $campoId Id do campo que terá a visualização alterada.
     * @param boolean $rotulo Caso verdadeiro, altera a visualização do rótulo também.
     * @param string $display Valor do "display" para o elemento. Ex: block, none.
     */
    public static function definirVisualizacao($campoId, $rotulo = FALSE, $display = 'block')
    {
        $rotulo = $rotulo ? 'true' : 'false';
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirVisualizacao('$campoId', $rotulo, '$display');");
    }

    /**
     * Altera a visualização atual do elemento.
     * 
     * @param string $divId Id da DIV que terá a visualização alterada.
     * @param string $divImagemId Id da DIV que contém imagens que terá a visualização alterada.
     */
    public static function alterarVisualizacao($divId)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.alterarVisualizacao('$divId', '$divImagemId');");
    }

    /**
     * Método público e estático para esconder elementos.
     * 
     * @param string $elementoId Id do elemento que se deseja esconder.
     */
    public static function esconderElemento($elementoId)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirVisualizacao('$elementoId', 'false', 'none');");
    }

    /**
     * Método público e estático para mostrar elementos.
     * 
     * @param string $elementoId Id do elemento que se deseja mostrar.
     */
    public static function mostrarElemento($elementoId)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirVisualizacao('$elementoId', 'false', 'block');");
    }

    /**
     * Método público e estático para desabilitar o campo desejado.
     * 
     * @param string $campoId Id do campo que se deseja desabilitar.
     */
    public static function desabilitarCampo($campoId)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.desabilitarCampo('$campoId');");
    }

    /**
     * Método público e estático para habilitar o campo desejado.
     * 
     * @param string $campoId Id do campo que se deseja habilitar.
     */
    public static function habilitarCampo($campoId)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.habilitarCampo('$campoId');");
    }

    /**
     * Método público e estático para definir um valor no campo desejado.
     * 
     * @param string $campoId Id do campo que se deseja habilitar.
     * @param string $valor Valor que será setado no campo.
     */
    public static function definirValor($campoId, $valor)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirValor('$campoId', '$valor');");
    }

    /**
     * Método público e estático para definir um conteúdo em determinado elemento.
     * 
     * @param string $elementoId Id do elemento onde o conteúdo será setado.
     * @param string $conteudo Conteúdo que será adicionado no elemento desejado.
     */
    public static function definirConteudo($elementoId, $conteudo)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirConteudo('$elementoId', '$conteudo');");
    }

    /**
     * Método público e estático para definir um campo como somente leitura.
     * 
     * @param string $elementoId Id do elemento onde o conteúdo será setado.
     * @param boolean somenteLeitura Caso verdadeiro, seta o campo somente leitura.
     */
    public static function definirSomenteLeitura($campoId, $somenteLeitura = TRUE)
    {
        $somenteLeitura = $somenteLeitura ? 'true' : 'false';
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.definirSomenteLeitura('$campoId', $somenteLeitura);");
    }

    /**
     * Método público e estático para checar o campo desejado.
     * 
     * @param string $campoId Id do campo que se deseja checar.
     * @param boolean $checar Flag para checar o campo.
     */
    public static function checarCampo($campoId, $checar = TRUE)
    {
        $checar = $checar ? TRUE : FALSE;
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("base.checarCampo('$campoId');");
    }
}

?>