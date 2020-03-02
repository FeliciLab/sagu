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
 * Formulário principal do Base.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 14/12/2011
 */
class bForm extends MForm
{
    /**
     * @var bBarraDeFerramentas Barra de ferramentas do sistema.
     */
    protected $barraDeFerramentas;

    /**
     * @var string Nome do módulo.
     */
    protected $modulo;
    
    /**
     * @var string Função do formulário. 
     */
    protected $funcao;
    
    /**
     * @var bTipo Instância do tipo. 
     */
    protected $tipo;
    
    /**
     * @var boolean Flag que define se será feito event handler 
     */
    public static $fazerEventHandler;
    
    /**
     * Método construtor do formulário.
     * 
     * @param string $titulo Título do formulário.
     * @param aray $parametros Parametros enviados pelo manipulador.
     */
    public function __construct($titulo, $parametros)
    {
        try
        {
            $this->modulo = $parametros['modulo'];
            $this->funcao = $parametros['funcao'];

	    if($parametros['tipo'])
	    {
            	$this->instanciarTipo($parametros['tipo']);
            
                if ( strlen($titulo) == 0 )
        	{
                    $titulo = $this->tipo->obterComentarioDaTabela();
	        }
            }

            parent::__construct($titulo);

            if ( $this->barraDeFerramentas )
            {
                // Classe que faz o espaçamento necessário para o uso da toolbar vertical.
                $this->setClass('verticalToolbarPadding');
            }
                
            if ( !self::$fazerEventHandler )
            {
    	        $this->eventHandler();
                self::$fazerEventHandler = true;
            }

            // TODO: remova-me
            $barraDeNavegacao = $this->manager->getTheme()->getElement('navigation');
            $barraDeNavegacao->addOption($titulo, $this->modulo, 'main');

            $this->setJsValidationEnabled(false);
            $this->setShowPostButton(false);
        }
        catch ( MDatabaseException $e )
        {
            MUtil::debug($e->getMessage());
            //$this->exibirErro($e->getMessage());
        }
        catch ( MValidationException $e )
        {
            // Obtém as mensagens do validador.
            $mensagens = $e->getMessages();
            $javascript = 'mvalidator.removeAllErrors();';
            
            if ( is_array($mensagens) )
            {
                foreach ( $mensagens as $campo => $mensagem )
                {
                    $javascript .= "mvalidator.addErrorToField('$mensagem', '$campo');";
                }
            }
            
            // Adiciona as mensagens ao lado dos campos que estão inválidos.
            $this->page->onload($javascript);
            
            // Resposta ajax para que validação seja ajax.
            $this->setResponse(NULL, 'responseDiv');
        }
        catch ( Exception $e )
        {
//            MUtil::debug($e->getMessage());
            $this->exibirErro($e->getMessage());
        }
    }

    /**
     * Método para criação de campos do formulário.
     * 
     * @param boolean $barraDeFerramentas Flag booleana para mostrar ou não a barra de ferramentas.
     */
    public function createFields($barraDeFerramentas = true)
    {
        parent::createFields();
        
        // Evento que ativam o definir campos.
        $eventos[] = 'botaoBuscar_click';
        $eventos[] = 'tbBtnNew:click';
        $eventos[] = 'bfEditar:click';
        $eventos[] = 'bfRemover:click';
        $eventos[] = 'bfExplorar:click';
        $eventos[] = 'bfCancelar:click';
        $eventos[] = 'botaoSalvar_click';
        $eventos[] = 'confirmarExclusao';
        $eventos[] = 'confirmarExclusao';
        
        // Somente chama os campos principais caso seja necessário.
        if ( MUtil::isFirstAccessToForm() || in_array( MIOLO::_REQUEST(MUtil::getDefaultEvent()), $eventos ) || substr(MIOLO::_REQUEST(MUtil::getDefaultEvent()), 0, 11) == 'mlinkbutton' )
        {
            try
            {
                $this->definirCampos();
            }
            catch (Exception $e)
            {
                new MMessageWarning($e->getMessage());
            }
        }
    }
    
    public function definirCampos($barraDeFerramentas)
    {
        $fields = array();
        
        if ( $barraDeFerramentas )
        {
            $fields[] = $this->barraDeFerramentas = new bBarraDeFerramentas();
        }
        
        $fields[] = MMessage::getMessageContainer();
        $fields[] = MPopup::getPopupContainer();
        $fields[] = new MDiv('responseDiv', NULL);
        $fields[] = MDialog::getDefaultContainer();

        parent::addFields($fields);
    }
    
    /**
     * Método reescrito para obter primeiro campo do formulário e definir o foco nele.
     * 
     * @param array Vetor de campos.
     */
    public function addFields($campos)
    {
        parent::addFields($campos);
        
        $primeiroCampoVisivel = $this->obterPrimeiroCampoVisivel($campos);

        if ( $primeiroCampoVisivel && MUtil::isFirstAccessToForm() )
        { 
             bJavascript::definirFoco($primeiroCampoVisivel, FALSE);
        }
    }

    /**
     * Cria uma instância do tipo informado e a define no atributo tipo do formulário.
     *
     * @param string $nomeDoTipo Nome do tipo.
     * @return labType Instância do tipo.
     */
    public function instanciarTipo($nomeDoTipo)
    {
        if ( !$nomeDoTipo )
        {
            return;
        }
               
        
        $this->tipo = bTipo::instanciarTipo($nomeDoTipo, $this->modulo);
    }
    
    /**
     * Método para exibir erros em tela.
     * 
     * @param string $erro Mensagem de erro.
     */
    public function exibirErro( $erro )
    {
        // Primeiro acesso a página.
        if ( MUtil::isFirstAccessToPage() )
        {
            $this->error( $erro );
        }
        else
        {
            // Testa se a mensagem de erro possui mais de uma linha, caso tenha, adiciona a mensagem em uma DIV expandida.
            if ( count(explode("<br />", $erro)) > 1 )
            {
                $divErro = new MExpandDiv( NULL, $erro );
            }
            else
            {
                $divErro = new MDiv( NULL, $erro );
            }
            
            MPopup::remove();
            new MMessageWarning($divErro, MMessage::TYPE_ERROR);
        }
    }
    
    /**
     * Método que obtém o id do primeiro campo de entrada visível (recursiva).
     *
     * @param array Vetor com componentes do MIOLO.
     * @return string Id do primeiro campo vísivel.
     *
     */
    public function obterPrimeiroCampoVisivel($campos)
    {
        foreach ( (array) $campos as $campo )
        {
            if ( $campo instanceof MContainer )
            {
                $campoVisivel = $this->obterPrimeiroCampoVisivel($campo->getControls());
                
                if ( $campoVisivel )
                {
                    return $campoVisivel;
                }
            }
            elseif ( $campo instanceof MDiv )
            {
                $campoVisivel = $this->obterPrimeiroCampoVisivel($campo->getInner());
                
                if ( $campoVisivel )
                {
                    return $campoVisivel;
                }
            }
            elseif ( $campo instanceof MHiddenField || $campo->readonly || $campo instanceof MButton )
            {
                continue;
            }
            elseif ( $campo instanceof MInputControl )
            {
                return $campo->getName();
            }
        }
    }
    
    /**
     *  Método reescrito para atender os campos cujo id é palavra reservada do sistema. Ex: name.
     * 
     * @return FormData Objeto com valores do formulário. 
     */
    public function getData()
    {
        $dados = $this->getAjaxData();
        
        // Percorre os valores do FormData ajustando os valores cujo indice termine com '_'.
        foreach ( $dados as $indice => $dado )
        {
            if ( substr($indice, strlen($indice) -1, 1) == '_' )
            {
                // Retira o "-" no final do indice.
                $novoIndice = substr($indice, 0, strlen($indice) -1);
                $dados->$novoIndice = $dado;
            }
        }
        
        // Suporte ao componente MSubDetail.
        foreach ( $this->fields as $campo )
        {
            if ( $campo instanceof MSubDetail )
            {
                $idSubDetail = $campo->getName();
                $dados->$idSubDetail = MSubDetail::getData($idSubDetail);
            }
            
            if ( $campo instanceof MCheckBox )
            {
                $dados->{$campo->name} = $dados->{$campo->name} == DB_TRUE ? DB_TRUE : DB_FALSE;
            }
        }
        
        return $dados;
    }
    
    public function setNullResponseDiv()
    {
        $this->setResponse(NULL, 'responseDiv');
    }
}

?>
