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
 * Componente de barra de status.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Class created on 13/01/2012
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('bMainMenu.js', 'base');

class bStatusBar extends MDiv
{
    /**
     * @var array Dados a serem exibidos na direita da barra de status.
     */
    public $cols;

    /**
     * @var MDiv Div do botão do menu de favoritos.
     */
    public $bookmarks;

    /**
     * @var MDiv Div do botão do menu de mais acessados.
     */
    public $mostAccessed;

    /**
     * @var boolean Indica se usuário está autenticado.
     */
    public $authenticated = false;

    /**
     * Construtor do componente de barra de status.
     *
     * @param array $cols Dados a serem exibidos na direito da barra de status.
     */
    public function __construct($cols = null)
    {
        parent::__construct(NULL, NULL, 'm-statusbar');
//        $this->addStyleFile('m_themeelement.css');
        $this->cols = $cols;

        $login = $this->manager->getLogin();

        if ( $login->id )
        {
            $this->authenticated = true;
            
            $online = (time() - $login->time) / 60;
            
            $this->addInfo(_M('Usuário') . ': ' . $login->id);
            $this->addInfo(_M('Ativo desde') . ': ' . Date('H:i', $login->time));
            $this->addInfo(_M('Data') . ': ' . Date('d/m/Y', $login->time));
            $this->addInfo(_M('Versão') . ': ' . $this->getSaguVersion());

            $this->bookmarks = new MDiv('sBookmarksButton', _M('Favoritos'), 'm-bookmark-statusbar');
            $this->bookmarks->addAttribute('onclick', 'sbookmarksmenu.show();');
            
            $this->mostAccessed = new MDiv('sMostAccessedButton', _M('Mais Acessados'), 'm-mostaccessed-statusbar');
            $this->mostAccessed->addAttribute('onclick', 'smostaccessedmenu.show();');
        }
    }

    /**
     * Gera o código JavaScript para a criação dos itens no menu de favoritos.
     *
     * @return string Código JavaScript para a criação dos itens.
     */
    public function createBookmarkMenu()
    {
        $code = '';

        $ui = $this->manager->getUI();
        $login = $this->manager->getLogin();
        
        $data->login = $login->id;

        $busAccess = $this->manager->getBusiness('base', 'BusAccess');
        $busModule = $this->manager->getBusiness('base', 'module');


        // Bookmark
        if ( MIOLO::_request('event') == 'resetBookmark' )
        {
            $busAccess->deleteAccess($data->login, NULL, true);
        }
        else
        {
            // FIXME: verificar necessidade de filtro por módulo
            // Caso esteja na tela principal não filtra por módulo
            /*if ( $module != 'sagu2' )
            {
                $data->moduleAccess = $module;
            }*/

            $data->isBookmark = true;
            $bookmarks = $busAccess->searchAccess($data);
        }

        if ( count($bookmarks)>0 )
        {

            foreach ( $bookmarks as $bookmark )
            {
                list($login, $module, $label, $icon, $action, $count) = $bookmark;

                $busModule->getById($module);
                $moduleName = strlen($busModule->nome) > 0 ? $busModule->nome : $module;

                // FIXME: código atual só aceita classes CSS
                // Caso não exista o ícone, exibe o default-16x16.png
                if ( !file_exists($this->manager->GetModulePath($module, null) . 'html/images/' . $icon) )
                {
                    $icon = 'default-16x16.png';
                }

                $image = $ui->getImage($id, $ico);

                $label = _M($label);
                $url = $this->manager->getActionURL($module, $action);
                $code .= "sbookmarksmenu.addItem('$action', '$label', '$url', 'm-bookmark-image');";
//                            var_dump($code);
//                            var_dump('aqui?');
            }
        }

		return $code;
    }

    /**
     * Gera código JavaScript para a criação dos itens do menu de mais acessados.
     *
     * @return type 
     */
    public function createMostAccessedMenu()
    {
        $code = '';

        $ui = $this->manager->getUI();
        $login = $this->manager->getLogin();

        $data->login = $login->id;

        $busAccess = $this->manager->getBusiness('base', 'BusAccess');
        $busModule = $this->manager->getBusiness('base', 'module');

        // Visited
        if ( MIOLO::_request('event') == 'reset' )
        {
            $busAccess->deleteAccess($data->login);
        }
        else
        {
            // FIXME: verificar necessidade de filtro por módulo
            // Caso esteja na tela principal não filtra por módulo
            /*if ( $module != 'sagu2' )
            {
                $data->moduleAccess = $module;
            }*/
        
            $data->isBookmark = false;
            $links = $busAccess->searchAccess($data);

            foreach ( (array)$links as $link )
            {
                list($login, $module, $label, $icon, $action, $count) = $link;

                $busModule->getById($module);
                $moduleName = strlen($busModule->nome) > 0 ? $busModule->nome : $module;

                // FIXME: código atual só aceita classes CSS
                // Caso não exista o ícone, exibe o default-16x16.png
                if ( !file_exists($this->manager->GetModulePath($module, null) . 'html/images/' . $icon) )
                {
                    $icon = 'default-16x16.png';
                }

                $label = _M($label);
                $url = $this->manager->getActionURL($module, $action);
                $code .= "smostaccessedmenu.addItem('$action', '$label', '$url', 'm-mostaccessed-image');";
            }
        }

        return $code;
    }

    public function addInfo($info)
    {
        $span = new MSpan('', $info, NULL);
        $this->cols[] = $span;
    }

    public function clear()
    {
        unset($this->cols);
    }

    public function generate()
    {
        if ( $this->authenticated )
        {
            $divLeft = new MDiv(NULL, array( $this->bookmarks, $this->mostAccessed ), 'm-statusbar-left');
            $divRight = new MDiv(NULL, $this->cols, 'm-statusbar-right');
            $this->setInner(new MDiv(NULL, array($divLeft, $divRight)));

            $bookmarksJS = $this->createBookmarkMenu();
            $mostAccessedJS = $this->createMostAccessedMenu();

            $module = MIOLO::getCurrentModule();
            $action = MIOLO::getCurrentAction();
            $bookmarkURL = $this->manager->getActionURL($module, $action, null, array('function' => 'search', 'event' => 'bookmark'));


            $onload = <<<JS
dojo.ready(function(){
    sbookmarksmenu = new bMainMenu();
    sbookmarksmenu.setup('sBookmarksMenu', 'sBookmarksButton');
    $bookmarksJS
    sbookmarksmenu.addChild(new dijit.MenuSeparator());
    sbookmarksmenu.addChild(new dijit.MenuItem({
        label:'Adicionar/Remover', 
        onClick:function() {
            GotoURL('$bookmarkURL'.replace(/&amp;/g,"&"));
        },
        iconClass:"m-bookmark-add-remove"
    }));

    smostaccessedmenu = new bMainMenu();
    smostaccessedmenu.setup('sMostAccessedMenu', 'sMostAccessedButton');
    $mostAccessedJS
});
JS;
            $this->page->onload($onload);
        }

        return parent::generate();
    }
    
    public function getSaguVersion()
    {
        $MIOLO = MIOLO::getInstance();        
        $module = 'basic';
        
        if(file_exists($MIOLO->getModulePath($module, "VERSION")))
        {


            $version = file($MIOLO->getModulePath($module, "VERSION"));                
            $v = explode('.', $version[0]);

            return trim($v[0] . '.' . $v[1] . ' ( ' . $v[2] . ' )');
        }
        else
        {
            return 'Versão não encontrada';
        }    
         
    }
}

?>
