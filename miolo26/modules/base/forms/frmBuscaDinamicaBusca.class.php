<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
 *
 * O Fermilab é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Formulário de busca de cadastro dinâmico.
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 17/08/2012
 */

class frmBuscaDinamicaBusca extends bFormBusca
{
    public function __construct($parametros)
    {
        parent::__construct(_M('Busca de busca dinâmica', MIOLO::getCurrentModule()), $parametros);
    }

    public function definirCampos()
    {
        parent::definirCampos();

        $filtros = array();
        $colunas = array();

        $filtros[] = new MTextField('buscaDinamicaId', NULL, _M('Código'), 10);
        $filtros[] = new MTextField('identificador', NULL, _M('Identificador'), 50);
        $filtros[] = new MTextField('modulo_', NULL, _M('Módulo'), 20);
        
        $this->adicionarFiltros($filtros);

        $colunas[] = new MGridColumn(_M('Código', $this->modulo));
        $colunas[] = new MGridColumn(_M('Identificador', $this->modulo));
        $colunas[] = new MGridColumn(_M('Módulo', $this->modulo));
        
        $this->criarGrid($colunas);
    }
}

?>
