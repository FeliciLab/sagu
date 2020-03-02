<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 *
 * Este arquivo é parte do programa Gnuteca.
 *
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Class
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
class ThemeGnutecaClassic extends MTheme
{
    public function init()
    {
        $navBar = new MNavigationBar();
        $navBar->setSeparator( new MDiv(null, null, 'mTopmenuSeparator') );
        $this->setElement('navigation', $navBar, 'mContainerTopMenu');
    }

    public function generate($element='')
    {
        $method = "generate" . $this->layout;
        $this->manager->trace("Theme generate: " . $method);
        return $this->$method($element);
    }

    public function getTemplate()
    {
        $pathName = $this->manager->getConf('home.themes') . '/gnutecaClassic/template/';
        $template = new MBTemplate($pathName);
        $template->set('miolo', $this->manager);
        $template->set('theme', $this);
        $template->set('form', $this->manager->getPage()->getFormId());
        return $template;
    }

    public function generateBase()
    {
        $template = $this->getTemplate();
        return $template->fetch('base.php');
    }

    public function generateContent()
    {
        $this->setElementClass('content', 'mThemeContainerContentFullAjax');
        $this->page->onload('gnuteca.closeAction();');
        return $this->generateElement('content');

    }

    public function generateNavBar()
    {
        if ( $this->getElement('navigation')->hasOptions())
        {
            echo $this->generateElement('navigation');
        }
    }

    public function getWebForm($templateFile)
    {
        $template = $this->getTemplate();
        $content = $template->fetch($templateFile);
        return $this->manager->getPage()->generateForm($content);
    }

    public function generateDefault($element)
    {
        $webForm = $this->getWebForm('default.php');
        $elements[$element] = $webForm->generate();
        //$this->manager->page->onload( "gnuteca.closeAction();");
        return $elements;
    }


    public function generateDynamic($element)
    {
        $elements[$element.'_content'] = $this->generateContent();

        if ($this->hasMenuOptions())
        {
           $elements[$element.'_menu'] = $this->generateMenu();
        }

        return $elements;
    }

    public function generateWindow()
    {
        $webForm = $this->getWebForm('window.php');
        $formId = $this->manager->getPage()->getFormId();
	    $elements[$formId] = $webForm->generate();
        return $elements;
    }

    public function generateLookup()
    {
        $html = $this->generateElement('content');
        $div = new MDiv('m-container', $html);
        $div->addStyle('width', '100%');
        return $this->painter->generateToString($div);
    }

    public function generatePopup()
    {
        $page = $this->manager->getPage();
        $this->setElementId('content', 'mThemeContainerContentPopup');
        $html = $this->generateElement('content');
        $divContainer = new MDiv('', $html,  'mContainer');
        $divContainer->addStyle('width', '100%');
        return $this->painter->generateToString($divContainer);
    }

    public function generatePrint()
    {
        $this->generateDefault();
    }

    public function generateDOMPdf()
    {
        $html = $this->generateElement('content');
        $div = new MDiv('mThemeContainer', $html);
        $div->addStyle('width', '100%');
        return $this->painter->generateToString($div);
    }
}
?>
