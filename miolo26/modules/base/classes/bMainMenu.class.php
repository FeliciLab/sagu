<?php

/**
 * <--- Copyright 2005-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Componente de menu principal.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Class created on 12/01/2012
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('bMainMenu.js', 'base');
$MIOLO->uses('classes/bUtil.class.php', 'base');
$MIOLO->uses('types/AdmMioloTransaction.class.php', 'admin');
$MIOLO->uses('classes/bStatusBar.class.php', 'base');

class bMainMenu extends MDiv
{
    const ID = 'bMainMenu';
    const ID_LOGO = 'bMainMenuLogo';
    const ID_DADOS = 'bMainMenuData';
    const ID_JS = 'bMainMenuJS';
    const ID_ACAO = 'bMainMenuNavAction';
    const ID_ACESSO_RAPIDO = 'bMainMenuQuickAccess';

    private $logo;
    
    /**
     * @var array
     */
    private $dados;
    
    
    private $navegacao;
    
    /**
     * Indica se deve utilizar cache para o menu (util para debug)
     * 
     * @var boolean
     */
    private $useCache = true;

    public function __construct()
    {
        parent::__construct(self::ID, '', 'm-main-menu');

        $ui = $this->manager->getUI();

        $carregandoDiv = new MDiv('bMainMenuCarregandoDiv', _M('Carregando...', 'basic'));
        $carregandoDiv->addStyle('display', 'none');
        $carregandoDiv->addStyle('z-index', '1000');
        $carregandoDiv->addStyle('position', 'fixed');
        $carregandoDiv->addStyle('padding', '6px 10px');
        $carregandoDiv->addStyle('color', 'white');

        $img = $ui->getImageTheme($this->manager->theme->id, 'logo.png');
        $this->logo = new MImage(self::ID_LOGO, NULL, $img);
        $this->logo->addAttribute('onclick', "if (!bmainmenu) { loadMenu(true); } else { bmainmenu.show(); }");
        $logoDiv = new MDiv(self::ID_LOGO . 'Div', $this->logo, 'logo');

        $this->popular();
        $this->gerarNavegacao();
        
        $this->setInner(array( $carregandoDiv, $logoDiv, $this->navegacao ));
    }

    /**
     * Obtem URL de menu, fazendo tratamentos especificos quando necessários.
     *
     * @return string
     */
    public static function getMenuURL($modulo, $acao, $subPath = null)
    {
        $MIOLO = MIOLO::getInstance();
        $dispatch = null;        
        $confURL = $MIOLO->getConf('home.url'); // Ex.: http://meusagu.edu.br/sagu26/

        if ( $acao == 'logout' )
        {
            $url = $MIOLO->getActionURL('admin', $acao);
        }
        else
        {        
            if ( bUtil::isMiolo2() ) // É versao antiga do miolo (2.0)
            {
                // Concatena o subpath na URL, caso exista
                $dispatch = $confURL . ( $subPath ? '/' . $subPath : null );
            }
            else // É versao do miolo > 2.0
            {
                if ( strlen($subPath) > 0 )
                {
                    $dispatch = null; // Mantem a URL atual
                }
                else
                {
                    // Corta o subpath da URL
                    $dispatch = dirname($confURL) . '/index.php'; // URL sem o path
                }
            }
            
            if ( $modulo == 'pedagogico' )
            {
                $dispatch = str_replace('miolo26', 'miolo20', $dispatch);
            }

            $url = $MIOLO->getActionURL($modulo, $acao, null, null, $dispatch);
        }

        return $url;
    }

    /**
     * Popula o componente com os dados necessários para a criação do menu.
     * Armazena os dados na sessão para evitar requisições a base de dados.
     */
    public function popular()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $sessao = $this->manager->getSession();
        $sessionId = $this->id . self::ID_DADOS;
        $sessionData = $sessao->getValue($sessionId);

        $filters = new stdClass();
        $filters->onlyWithPerms = true;

        // Foi removido o cache devido a estar havendo problemas de as vezes nao refazer a consulta        
        $dados = AdmMioloTransaction::listRecords($filters);
        if ( $dados )
        {

            // Gera botao de logout
            $actLogout = 'base|logout';
            $img = $MIOLO->getUI()->getImageTheme($MIOLO->theme->id, 'logout_-16x16.png');
            $dados[$actLogout] = current($dados);
            $dados[$actLogout]['modulo'] = 'base';
            $dados[$actLogout]['temFilho'] = false;
            $dados[$actLogout]['descricao'] = "<img src=\"{$img}\"></img> ". _M('Sair', $module);

            // Gera opções do acesso rápido
            foreach ( $dados as $moduloAcao => $item )
            {
                // Ações do acesso rápido não são geradas para sub-menus
                if ( $item['temFilho'] )
                {
                    continue;
                }

                list($modulo, $acao) = explode('|', $moduloAcao);
                $codigo = $item['codigo'];
                $pai = $item['pai'];
                
                $niveis = array();
                
                while ( $acao )
                {
                    $niveis[] = $acao;

                    $acao = explode(':', $acao);
                    end($acao);
                    unset($acao[key($acao)]);
                    $acao = implode(':', $acao);
                }

                $niveis = array_reverse($niveis);
                $descricaoAcessoRapido = array();

                foreach ( $niveis as $nivel )
                {
                    $acaoAtual = $dados["$modulo|$nivel"];

                    if ( !$acaoAtual )
                    {
                        $acaoAtual = $dados["$modulo|main:$nivel"];
                    }

                    $descricaoAcessoRapido[] = $acaoAtual['descricao'];
                }

                $dados[$moduloAcao]['acessoRapido'] = implode(' :: ', $descricaoAcessoRapido);
            }

            $sessao->setValue($sessionId, $dados);
        }
        
        $this->dados = $dados;
    }

    /**
     * Obtém o código JavaScript para a criação dos itens e sub-menus do menu principal.
     * 
     * @return string Código JS.
     */
    public function gerarItensJS()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $sessao = $this->manager->getSession();

        // Obtém da sessão para evitar processamento repetido
        //$itensJS = $sessao->getValue($this->id . self::ID_JS);

        if ( !$itensJS )
        {
            $itensJS = array();

            foreach ( (array)$this->dados as $moduloAcao => $item )
            {
                list($modulo, $acao) = explode('|', $moduloAcao);
                $codigo = $item['codigo'];
                $descricao = $item['descricao'];
                $pai = $item['pai'];
                $acessoRapido = $item['acessoRapido'];
                $subPath = $item['subpath'];

                if ( $item['temFilho'] )
                {
                    $itensJS[] = "bmainmenu.addSubMenu('{$codigo}', '$descricao', null, false, '{$pai}');";
                }
                else
                {
                    if ( strlen($pai) == 0 && substr($descricao, 0, 1) == '*' )
                    {
                        continue;
                    }
                    //$acao = $this->manager->getActionURL($modulo, $acao);
                    $acao = bMainMenu::getMenuURL($modulo, $acao, $subPath);
                    $itensJS[] = "bmainmenu.addItem('{$codigo}', '{$descricao}', '{$acao}', null, false, '{$pai}', '{$acessoRapido}');";
                }
            }

            $itensJS = implode("\n", $itensJS);
            $sessao->setValue($this->id . self::ID_JS, $itensJS);
        }

        return $itensJS;
    }

    /**
     * @return string Gera navegação via sub-menus.
     */
    public function gerarNavegacao()
    {
        $acoes = array();
        $chaveMode = false;
        $modulo = $this->manager->getCurrentModule();
        $acao = $acaoPrincipal = $this->manager->getCurrentAction();
        $args = $this->manager->getCurrentURL();
        
        $args = explode("&amp;", $args);        
        $arg  = explode("=", $args[2]);
        if ( ( $arg[0] == "reportid" ) || ( $arg[0] == "report" ) )
        {
            $args = "&" . $args[2];  
        }
        elseif (($acao == 'action=main') && (strlen(MIOLO::_REQUEST('chave'))>0))
        {
            $chaveMode = true;
            $chaveData = MIOLO::_REQUEST('chave');
            $args = '&chave='.$chaveData;
        }
        else
        {
            $args = null;
        }
        $acao .= $args;
        $acaoPrincipal .= $args;
        if ( $acao )
        {
            if ($chaveMode)
            {
                $niveis[] = $acao;
                $niveis[] = 'main';
            }
            else
            {
                while ( $acao )
                {
                    $niveis[] = $acao;

                    $acao = explode(':', $acao);
                    end($acao);
                    unset($acao[key($acao)]);
                    $acao = implode(':', $acao);
                }
            }
            $niveis = array_reverse($niveis);
            foreach ( $niveis as $nivel )
            {
                $acaoAtual = $this->dados["$modulo|$nivel"];
                if ( !$acaoAtual )
                {
                    $acaoAtual = $this->dados["$modulo|main:$nivel"];
                }
                // Suporte a acoes com chave= no xml miolo_transaction
                if ( !$acaoAtual )
                {
                    $acaoAtual = $this->dados["$modulo|$nivel&chave=" . MIOLO::_REQUEST('chave')];
                }

                if ( $acaoPrincipal != $nivel && $acaoPrincipal != "main:$nivel" && isset($acaoAtual['descricao']) )
                {
                    $id = self::ID_ACAO . $acaoAtual['codigo'];
                    $acoes[] = $acao = new MDiv($id, $acaoAtual['descricao'], 'm-main-menu-navbar-item m-main-menu-navbar-item-clickable');
                    $acao->addAttribute('onclick', "if (!bmainmenu) { loadMenu(true, '{$id}', '{$acaoAtual['codigo']}'); } else { bmainmenu.show('{$id}', '{$acaoAtual['codigo']}'); }");

                    $url = $this->manager->getActionURL($modulo, $nivel);
                    $acao->addAttribute('ondblclick', "GotoURL'$url';");

                    $acoes[] = new MDiv(NULL, '::', 'm-main-menu-navbar-separator');
                }
                elseif ( isset($acaoAtual['descricao']) )
                {
                    $acoes[] = new MDiv($id, $acaoAtual['descricao'], 'm-main-menu-navbar-item');
                }
            }
        }

        if ( count($acoes) == 0 )
        {
            $acoes = '&nbsp;';
        }

        $this->navegacao = new MDiv(NULL, $acoes, 'm-main-menu-navbar');
    }

    /**
     * @return string Gera recurso de acesso rápido.
     */
    public function gerarAcessoRapido()
    {
        $fields = array();

        $modulo = MIOLO::getCurrentModule();
        $url = $this->manager->getActionURL($modulo, 'main');

        $imagem = $this->manager->getUI()->getImageTheme($this->manager->theme->id, 'home.png');        
        $home = new MImageLink(NULL, '', "javascript:document.location.assign('$url');", $imagem);
        $home->addAttribute('title', _M('Ir para a página inicial'));

        $label = new MLabel(_M('Acesso rápido'));
        $label->setClass('m-label');

        $acessoRapido = new MTextField(self::ID_ACESSO_RAPIDO, _M('O que você deseja procurar?'), NULL, 30);
        $acessoRapido->addAttribute('onkeyup', "if (!bmainmenu) { loadMenu(false); setTimeout(function() { bmainmenu.quickaccess(event, '" . self::ID_ACESSO_RAPIDO . "'); }, 0); } else { bmainmenu.quickaccess(event, '" . self::ID_ACESSO_RAPIDO . "'); }");
        $acessoRapido->addAttribute('onClick', 'document.getElementById(\'bMainMenuQuickAccess\').value = \'\';');
        $acessoRapido->addAttribute('onBlur', 'backWhat();');
        $acessoRapido->addAttribute('onkeypress', 'return handleEnter(this, event);');
        $acessoRapido->addAttribute('style', 'color: #999');
        $acessoRapido->addAttribute("onFocus", "this.style.color = '#222';");        
        
        $this->page->addJsCode('
            function backWhat()
            {
                var value = document.getElementById(\'bMainMenuQuickAccess\').value;
                if( value == \'\' )
                {
                    document.getElementById(\'bMainMenuQuickAccess\').value = "O que você deseja procurar?";
                    document.getElementById(\'bMainMenuQuickAccess\').style.color = "#999";
                }
            } 
        ');
        
        $this->page->AddJsCode("
            function handleEnter(field, event) 
            {
                var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
        if ( keyCode == 13 ) 
                {                
                    var i;
                    for ( i = 0; i < field.form.elements.length; i++ )
                    {
                        if ( field == field.form.elements[i] )
                        {
                            break;
                        }
                    }
                    i = (i + 1) % field.form.elements.length;
                    field.form.elements[i-1].focus();
                    return false;
        } 
        else
                {
                    return true;
                }
            } 
        ");

        $fields[] = new MSpan(NULL, $home);
        $fields[] = $label;
        $fields[] = new MSpan(NULL, $acessoRapido);
        
        $div = new MDiv(NULL, $fields, 'm-main-menu-quickaccess');

        return $div->generate();
    }

    /**
     * @return string Gera o menu principal e seus recursos.
     */
    public function generate()
    {
        $itensJS = $this->gerarItensJS();

        $onload = <<<JS
window.loadMenu = function (show, actionNodeId, id) {
    if ( !actionNodeId )
    {
        dojo.style('bMainMenuLogoDiv', 'visibility', 'hidden');
        dojo.style('bMainMenuCarregandoDiv', 'display', 'block');
    }
    setTimeout(function() {
        dojo.byId("bMainMenuLogoDiv").setAttribute('onmouseover', '');
        bmainmenu = new bMainMenu();
        bmainmenu.setup('$this->id', '{$this->logo->id}');
        $itensJS
        bmainmenu.startup();
        if ( show )
        {
            if ( actionNodeId )
            {
                bmainmenu.show(actionNodeId, id);
            }
            else
            {
                bmainmenu.show();
            }
        }
        if ( !actionNodeId )
        {
            dojo.style('bMainMenuLogoDiv', 'visibility', 'visible');
            dojo.style('bMainMenuCarregandoDiv', 'display', 'none');
        }
    }, 0);
}
JS;
        $this->page->onload($onload);

        return parent::generate() . $this->gerarAcessoRapido();
    }
}

?>
