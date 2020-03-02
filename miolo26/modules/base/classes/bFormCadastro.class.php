<?php

/**
 * <--- Copyright 2012 de Solis - Cooperativa de Soluções Livres Ltda.
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
 * Formulário genérico de edição, inserção e exploração do Base.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 27/06/2012
 */
class bFormCadastro extends bForm
{
    /**
     * Método para criação de campos específicos dos formulários de edição e inserção.
     * 
     * @param boolean $barraDeFerramentas Flag booleana para mostrar ou não a barra de ferramentas.
     */
    public function definirCampos($barraDeFerramentas=TRUE)
    {
        parent::definirCampos($barraDeFerramentas);

        if ( $this->barraDeFerramentas )
        {
            $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_INSERIR);
            $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
            $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
        }

        if ( MUtil::isFirstAccessToForm() )
        {
            $this->addField(new MHiddenField('labSaveFormModificado', NULL));
            $this->page->onload("window.labSaveFormVerificador = dojo.connect(document, 'onchange', function (e) { dojo.byId('labSaveFormModificado').value = 'true'; dojo.disconnect(window.labSaveFormVerificador); } );");
        }
    }

    /**
     * Sobrescrito método addFields para alterar visualização dos campos conforme função atual.
     *
     * @param array $fields Instâncias de componentes.
     */
    public function addFields($campos, $informarValorChavePrimaria = FALSE)
    {
        if ( !$informarValorChavePrimaria )
        {
            if ( $this->tipo )
            {
                $chavesPrimarias = $this->tipo->obterChavesPrimarias();
            }
            
            if ( $this->funcao == FUNCAO_INSERIR )
            {
                // Retira o campo da tela quando tiver chave primária sequencial. Ex: código.
                if ( strlen($chavesPrimarias['sequencial']) )
                {
                    foreach ( $campos as $chave => $campo )
                    {
                        if ( in_array($campo->name, $chavesPrimarias) )
                        {
                            unset($campos[$chave]);
                        }
                    }
                }
            }
            elseif ( $this->funcao == FUNCAO_EDITAR )
            {
                foreach ( $campos as $campo )
                {
                    // Seta só os campos que são chave primária como somente leitura.
                    if ( in_array($campo->name, $chavesPrimarias) && method_exists($campo, 'setReadOnly') )
                    {
                        $campo->setReadOnly(TRUE);
                    }
                }
            }
            elseif ( $this->funcao == FUNCAO_EXPLORAR )
            {
                // Seta todos os campos como somente leitura.
                foreach ( $campos as $campo )
                {
                    $campo->setReadOnly(TRUE);
                }
            }
        }
        
        if (  $this->funcao != FUNCAO_EXPLORAR )
        {
            $campos[] = $this->obterBotoesPadrao();
        }
        
        $campos[] = new MDiv('', '<br/><br/><br/><br/><br/><br/>');
        
        parent::addFields($campos);
    }
    
    /**
     * Obtém os botões padrões do formulário de cadastro.
     * 
     * @param boolean $botaoCancelar Adicionar botão cancelar.
     * @param boolean $botaoSalvar Adicionar botão salvar.
     * @return MDiv contendo os botões. 
     */
    protected function obterBotoesPadrao($botaoCancelar=TRUE, $botaoSalvar=TRUE)
    {
        $botoes = array();

        if ( $botaoCancelar )
        {
            // Botão cancelar.
            $imagem = $this->manager->getUI()->getImageTheme(NULL, 'botao_cancelar.png');
            $botoes[] = new MButton('botaoCancelar', _M('Cancelar'), NULL, $imagem);
        }
        
        if ( $botaoSalvar )
        {
            // Botão salvar.
            $imagem = $this->manager->getUI()->getImageTheme(NULL, 'botao_salvar.png');
            $botoes[] = new MButton('botaoSalvar', _M('Salvar'), ':botaoSalvar_click', $imagem);
        }
        
        // Adiciona botão buscar no formulário
        return MUtil::centralizedDiv($botoes);
    }

    /**
     * Método para salvar os dados do formulário, é chamado ao clicar no botão "Salvar".
     */
    public function botaoSalvar_click()
    {
        if ( $this->validate() )
        {    
            $this->tipo->definir($this->getData());

            // Define a função do formulário.
            $this->tipo->definirFuncao(MIOLO::_REQUEST('funcao'));
            
            try
            {
                bBaseDeDados::iniciarTransacao();
                
                $resultado = $this->tipo->salvar();
                
                bBaseDeDados::finalizarTransacao();
            }
            catch ( Exception $e )
            {
                new MMessageWarning($e->getMessage());
                return;
            }
            
            if ( $this->funcao == FUNCAO_INSERIR )
            {
                if ( $resultado )
                {
                    $this->limparIndicacaoDeFormModificado();
                    new MMessageSuccess(_M('Registro inserido com sucesso.'), FALSE);
                    
                    $parametros = array (
                        'chave' => MIOLO::_REQUEST('chave'),
                        'funcao' => $this->funcao
                    );
                    
                    $url = $this->manager->getActionURL($this->modulo, $this->manager->getCurrentAction(), '', $parametros);
                    $this->page->redirect( $url );
                    
                }
                else
                {
                    new MMessageWarning(_M('Não foi possível inserir o registro.'));
                }
            }
            elseif ( $this->funcao == FUNCAO_EDITAR )
            {
                if ( $resultado )
                {
                    $this->limparIndicacaoDeFormModificado();
                    new MMessageSuccess(_M('Registro editado com sucesso.'));
                }
                else
                {
                    new MMessageWarning(_M('Não foi possível editar o registro.'));
                }
            }
        }
        else
        {
            new MMessage(_M('Verifique os dados informados.'), MMessage::TYPE_WARNING);
        }
    }

    /**
     * Método para cancelar digitação de dados no formulário. Se o valor de algum campo foi alterado, questiona o
     * usuário se ele realmente deseja cancelar.
     */
    public function botaoCancelar_click()
    {
        if ( MIOLO::_REQUEST('labSaveFormModificado') )
        {
            MPopup::confirm(
                _M('Os dados do formulário foram modificados. Tem certeza que deseja cancelar?'),
                _M('Cancelar'),
                ':cancelar'
            );
        }
        else
        {
            $this->cancelar();
        }
    }

    /**
     * Cancela a edição/inserção de dados, redirecionando o usuário à tela de busca.
     */
    public function cancelar()
    {
        $args = array(
            'chave' => MIOLO::_REQUEST('chave'),
            'funcao' => FUNCAO_BUSCAR,
        );

        $this->page->redirect($this->manager->getActionURL($this->modulo, 'main', NULL, $args));
    }

    /**
     * Limpa o campo que indica que formulário foi modificado.
     */
    public function limparIndicacaoDeFormModificado()
    {
        bJavascript::definirValor('labSaveFormModificado', '');
    }

    /**
     * Método chamado para carregar os dados dos campos do formulário ao editar.
     */
    public function onLoad()
    {
        if ( ( $this->funcao == FUNCAO_EDITAR || $this->funcao == FUNCAO_EXPLORAR) && MUtil::isFirstAccessToForm() )
        {
            // Obtém os valores das chaves primárias passadas por URL.
            $chavesPrimarias = $this->tipo->obterChavesPrimarias();

            $dados = new stdClass();

            foreach ( $chavesPrimarias as $chavesPrimaria )
            {
                $dados->$chavesPrimaria = MIOLO::_REQUEST($chavesPrimaria);
            }

            $this->tipo->definir($dados);
            $this->tipo->popular();
            
            // Obtém os dados do tipo.
            $dados = $this->tipo->obter();

            // Obtém os dados de todas tipos relacionados.
            $dadosDosTiposRelacionados = $this->tipo->obterDadosTiposRelacionados();
            
            // Percorre os campos para verificar se existe id's de campos que são palavras reservadas.
            foreach ( $this->fields as $campo )
            {
                // Obtém nome do campo.
                $nome = $campo->getName();
                
                if ( substr($nome, strlen($nome) -1, 1) == '_' )
                {
                    $indice = substr($nome, 0, strlen($nome) -1);
                    $dados->$nome = $dados->$indice;
                }
                
                // Carrega os dados das MSubDetail.
                if ( $campo instanceof MSubDetail )
                {
                    // Obtém o id da MSubDetail.
                    $idSubDetail = $campo->getName();
                    
                    // Obtém os dados do tipo.
                    $dadosSubDetail = $dadosDosTiposRelacionados[$idSubDetail];
                    
                    // Seta os dados na MSubDetail.
                    MSubDetail::setData($dadosSubDetail, $idSubDetail);
                }
            }

            // Seta os dados no formulário.
            $this->setData($dados);
        }
    }
    
    /**
     * Método chamado ao editar um registro da MSubDetail.
     * 
     * @param string $args Argumentos do ajax. 
     */
    public static function editFromTable($args)
    {
        MSubDetail::editFromTable($args);
    }
   
    /**
     * Método chamado ao mover um registro para baixo na MSubDetail.
     * 
     * @param string $args Argumentos do ajax. 
     */
    public static function downFromTable($data)
    {
        MSubDetail::downFromTable($data);
    }
    
    /**
     * Método chamado ao mover um registro para cima na MSubDetail.
     * 
     * @param string $args Argumentos do ajax. 
     */
    public static function upFromTable($data)
    {
        MSubDetail::upFromTable($data);
    }
    
    /**
     * Método chamado ao limpar uma MSubDetail.
     * 
     * @param string $args Argumentos do ajax. 
     */
    public static function clearTableFields($args)
    {
        MSubDetail::clearTableFields($args);
    }
    
    /**
     * Método chamado ao adicionar um registro da MSubDetail.
     * 
     * @param string $args Argumentos do ajax. 
     */
    public static function addToTable($data)
    {
        MSubDetail::addToTable($data);
    }
    
    /**
     * Método chamado ao remover um registro da MSubDetail.
     * 
     * @param string $args Argumentos do ajax. 
     */
    public static function removeFromTable($args)
    {
        MSubDetail::removeFromTable($args);
    }
       
}

?>