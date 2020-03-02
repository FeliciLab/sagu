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
 * Classe que constrói a barra de ferramentas.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 26/06/2012
 */
class bBarraDeFerramentas extends MBaseGroup
{
    /**
     * Constantes dos nomes dos botões.
     */
    const BOTAO_INSERIR = 'bfInserir';
    const BOTAO_EDITAR = 'bfEditar';
    const BOTAO_SALVAR = 'bfSalvar';
    const BOTAO_REMOVER = 'bfRemover';
    const BOTAO_BUSCAR = 'bfBuscar';
    const BOTAO_IMPRIMIR = 'bfImprimir';
    const BOTAO_REDEFINIR = 'bfRedefinir';
    const BOTAO_SAIR = 'bfSair';

    /**
     * @var array Botões da barra de ferramentas.
     */
    protected $botoes;

    /**
     * @var string Tipo de exibição padrão dos botões.
     */
    private $tipo = MToolbar::TYPE_ICON_TEXT;

    /**
     * Barra de ferramentas personalizada para o FermiLab.
     */
    public function __construct()
    {
        //ID para tornar a barra de ferramentas vertical (classes css do tema modern)
        parent::__construct('toolbar', '');

        $modulo = MIOLO::getCurrentModule();
        $chave = MIOLO::_REQUEST('chave');
        $formId = $this->page->getFormId();
        $url = $this->manager->getActionURL($modulo, null, null, array( 'chave' => $chave ));


        // Botão inserir
        $paramaters = array(
            'chave' => $chave,
            'funcao' => FUNCAO_INSERIR
        );

        $eventoURL = $this->manager->getActionURL($modulo, null, null, $paramaters);

        $this->adicionarBotaoPadrao(self::BOTAO_INSERIR, _M('Novo'), $eventoURL, _M('Clique para inserir um novo registro', $modulo), 'new-20x20.png', 'new-disabled-20x20.png');
        
        
        // Botão editar
        $evento = self::BOTAO_EDITAR . ':click';
        $eventoURL = "javascript:miolo.doAjax('$evento','','{$formId}');";
        $this->adicionarBotaoPadrao(self::BOTAO_EDITAR, _M('Editar'), $eventoURL, _M('Clique para editar o registro selecionado', $modulo), 'bf-editar-on.png', 'bf-editar-off.png');

        
        // Botão salvar
        $evento = self::BOTAO_SALVAR . ':click';
        $eventoURL = "javascript:miolo.doAjax('botaoSalvar_click','','__mainForm'); return false;";
//        $eventoURL = "javascript:miolo.doAjax('$evento','','{$formId}');";
        $this->adicionarBotaoPadrao(self::BOTAO_SALVAR, _M('Salvar'), $eventoURL, _M('Clique para salvar', $modulo), 'save-20x20.png', 'save-disabled-20x20.png');

        // Botão remover
        $evento = self::BOTAO_REMOVER . ':click';
        $eventoURL = "javascript:miolo.doAjax('$evento','','{$formId}');";
        $this->adicionarBotaoPadrao(self::BOTAO_REMOVER, _M('Deletar'), $eventoURL, _M('Clique para remover o registro selecionado', $modulo), 'delete-20x20.png', 'delete-disabled-20x20.png');


        // Botão buscar
        $paramaters = array(
            'chave' => $chave,
            'funcao' => FUNCAO_BUSCAR
        );

        $eventoURL = $this->manager->getActionURL($modulo, null, null, $paramaters);
        $this->adicionarBotaoPadrao(self::BOTAO_BUSCAR, _M('Procurar'), $eventoURL, _M('Clique para ir a página de busca', $modulo), 'search-20x20.png', 'search-disabled-20x20.png');


        // Botão sair
        $funcao = MIOLO::_REQUEST('funcao');
        
        // Redireciona para home.
        $eventoURL = $this->manager->getConf('home.url');      
        $this->adicionarBotaoPadrao(self::BOTAO_SAIR, _M('Fechar'), $eventoURL, _M('Clique para sair do formulário', $modulo), 'exit-20x20.png', 'exit-disabled-20x20.png');

        $this->setShowChildLabel(false);
    }

    /**
     * Adiciona um botão no formato padrão da classe.
     *
     * @param string $nome Identificador do botão.
     * @param string $titulo Rótulo.
     * @param string $evento Evento.
     * @param string $dica Dica.
     * @param string $imagemAtivo Nome da imagem quando ativo.
     * @param string $imagemInativo Nome da imagem quando inativo.
     */
    public function adicionarBotaoPadrao($nome, $titulo, $evento, $dica, $imagemAtivo, $imagemInativo)
    {
        $tema = $this->manager->theme->id;
        $UI = $this->manager->getUI();

        $imagemAtivo = $UI->getImageTheme($tema, $imagemAtivo);
        $imagemInativo = $UI->getImageTheme($tema, $imagemInativo);
        $this->botoes[$nome] = new MToolBarButton($nome, $titulo, $evento, $dica, true, $imagemAtivo, $imagemInativo, NULL, $this->tipo);
    }

    /**
     * Adiciona um botão customizado
     *
     * @param string $nome Identificação do MToolbarButton.
     * @param string $titulo Título do botão.
     * @param string $url URL da ação do botão.
     * @param string $jsSugestao Sugesão do botão.
     * @param boolean $ativo Status do botão.
     * @param string $ativoImagem URL da imagem do botão ativo.
     * @param string $desativadoImagem URL da imagem do botão quando desativado.
     * @param string $method @deprecated
     * @param string $tipo Tipo do botão, que pode ser: MToolBar::TYPE_ICON_ONLY, MToolBar::TYPE_ICON_TEXT or MToolBar::TYPE_TEXT_ONLY.
     */
    public function addButton($nome, $titulo, $url, $jsSugestao, $ativo, $ativoImagem, $desativadoImagem, $tipo=MToolBar::TYPE_ICON_ONLY)
    {
        $this->botoes[$name] = new MToolBarButton($nome, $titulo, $url, $jsSugestao, $ativo, $ativoImagem, $desativadoImagem, NULL, $tipo);
    }

    /**
     * Método para mostrar botões.
     *
     * @param array $names Identificação dos botões.
     */
    public function showButtons(array $names)
    {
        foreach ( $names as $name )
        {
            $this->showButton($name);
        }
    }

    /**
     * Show a button.
     *
     * @param string $name Button name.
     */
    public function showButton($name)
    {
        $this->botoes[$name]->show();
    }

    /**
     * Hide buttons.
     *
     * @param array $names Button names.
     */
    public function hideButtons(array $names)
    {
        foreach ( $names as $name )
        {
            $this->hideButton($name);
        }
    }

    /**
     * Hide a button.
     *
     * @param string $name Button name.
     */
    public function hideButton($name)
    {
        $this->botoes[$name]->hide();
    }

    /**
     * Enable buttons.
     *
     * @param array $names Button names.
     */
    public function enableButtons(array $names)
    {
        foreach ( $names as $name )
        {
            $this->enableButton($name);
        }
    }

    /**
     * Enable a button.
     *
     * @param string $name Button name.
     */
    public function enableButton($name)
    {
        $this->botoes[$name]->enable();
    }

    /**
     * Disable buttons.
     *
     * @param array $names Button names.
     */
    public function disableButtons(array $names)
    {
        foreach ( $names as $name )
        {
            $this->disableButton($name);
        }
    }

    /**
     * Disable one button.
     *
     * @param string $name Button name.
     */
    public function disableButton($name)
    {
        $this->botoes[$name]->disable();
    }

    /**
     * Set button type.
     * 
     * @param string $type Button type: MToolBar::TYPE_ICON_ONLY, MToolBar::TYPE_ICON_TEXT or MToolBar::TYPE_TEXT_ONLY.
     */
    public function setType($type=MToolBar::TYPE_ICON_ONLY)
    {
        foreach ( $this->botoes as $tbb )
        {
            $tbb->setType($type);
        }
    }

    /**
     * Add custom control to toolbar
     *
     * @param object $control MControl instance.
     * @param string $name Control name.
     */
    public function addControl($control, $name=NULL)
    {
        parent::addControl($control);

        if ( $name )
        {
            $this->botoes[$name] = $control;
        }
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        parent::__construct($this->name, '', $this->botoes);

        parent::generateInner();
    }
}

?>
