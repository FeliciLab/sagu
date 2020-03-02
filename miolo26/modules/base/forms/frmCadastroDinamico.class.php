<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Fermilab.
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
 * Formulário de gerenciamento de cadastri dinâmico.
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 16/08/2012
 */
class frmCadastroDinamico extends bFormCadastro
{

    public function __construct($parametros)
    {
        parent::__construct(_M('Cadastro dinâmico', MIOLO::getCurrentModule()), $parametros);
    }

    public function definirCampos()
    {
        parent::definirCampos();
        $this->setTitle($this->tipo->obterComentarioDaTabela());
        
        if ( MUtil::isFirstAccessToForm() )
        {
            MSubDetail::clearData('tabelaReferenciada');
        }
        
        $campos[] = new MTextField('cadastrodinamicoid', NULL, _M('Código'), 10);
        $campos[] = new MTextField('identificador', NULL, _M('Identificador'), 50);
        $campos[] = new MTextField('referencia', NULL, _M('Referência'), 50);
        $campos[] = new MTextField('modulo_', NULL, _M('Módulo'), 20);
        
        // Validadores.
        $validador = array( );
        $validador[] = new MRequiredValidator('identificador', '', 50);
        $validador[] = new MRequiredValidator('referencia');
        $validador[] = new MRequiredValidator('modulo_', '', 20);

        $camposTabelaReferenciada = array();
        $camposTabelaReferenciada[] = new MTextField('referencia', NULL, _M('Referência'), T_DESCRICAO);
        
        $colunasTabelaReferenciada[] = new MGridColumn( _M('Tabela referênciada'), 'left', TRUE, NULL, TRUE, 'referencia' );
       
        $validadorTabelaReferenciada = array();
        $validadorTabelaReferenciada[] = new MRequiredValidator('referencia');
        
        $campos[] = $tabelaReferenciada = new MSubDetail('tabelaReferenciada', _M('Campos da busca dinâmica'), $colunasTabelaReferenciada, $camposTabelaReferenciada, array('remove') );
        $tabelaReferenciada->setValidators($validadorTabelaReferenciada);
        
        $this->addFields($campos);
        $this->setValidators($validador);
    }
    
}

?>