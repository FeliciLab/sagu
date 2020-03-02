<?php

/**
 * Dialog component.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2012/03/14
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

class MDialog extends MDiv
{
    /**
     * Default div id to put the dialog.
     */
    const DEFAULT_CONTAINER = 'mDialogContainer';

    /**
     * @var MFormContainer HTML element that represents the content of the dialog (dijit.layout.ContentPane).
     */
    private $contentPane;

    /**
     * Dialog constructor.
     *
     * @param string $name Id of the dialog.
     * @param string $title Title of the dialog.
     * @param array $controls Components to put inside the content area.
     */
    public function __construct($name, $title, $controls)
    {
        parent::__construct($name);

        $this->contentPane = new MFormContainer(NULL, $controls);
        $this->contentPane->addAttribute('dojoType', 'dijit.layout.ContentPane');

        $this->page->addDojoRequire('dijit.form.Form');
        $this->page->addDojoRequire('dijit.Dialog');
        $this->page->addDojoRequire('dijit.layout.ContentPane');
        $this->addAttribute('dojoType', 'dijit.Dialog');
        $this->addAttribute('title', $title);
        $this->addAttribute('preventBodyScroll', 'true');

        // FIXME: Dojo bug
        if ( $this->manager->checkMobile() )
        {
            $this->addAttribute('draggable', 'false');
        }
    }

    /**
     * @param string $width Set the CSS width.
     */
    public function setWidth($width)
    {
        $this->addStyle('width', $width);
    }

    /**
     * @param string $height Set the CSS height.
     */
    public function setHeight($height)
    {
        $this->addStyle('height', $height);
    }

    /**
     * @return MDiv Get the default container to put the dialog.
     */
    public static function getDefaultContainer()
    {
        return new MDiv(self::DEFAULT_CONTAINER, NULL);
    }

    /**
     * Display the dialog. Can olny be called in AJAX requests.
     *
     * @param string $div Div id to put the dialog.
     */
    public function show($div=self::DEFAULT_CONTAINER)
    {
        $this->manager->ajax->setResponse($this, $div);
    }

    /**
     * Close dialog.
     *
     * @param string $dialogName Dialog name.
     */
    public static function close($dialogName)
    {
        $jsCode = " dijit.byId('$dialogName').hide();";

        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onLoad($jsCode);
    }

    /**
     * @return string Generate the HTML format of the component.
     */
    public function generate()
    {
        $this->setInner($this->contentPane);
        $formId = $this->page->getFormId();

        $js = <<<JS
var dialog = dijit.byId('$this->name');
if ( dialog )
{
    dialog.destroy();
}

miolo.webForm.hideScroll();
dojo.parser.parse(dojo.byId('$this->name').parentNode);
dialog = dijit.byId('$this->name');

dojo.connect(dijit.byId('$this->name'), 'show', miolo.webForm.hideScroll);
dojo.connect(dijit.byId('$this->name'), 'hide', miolo.webForm.showScroll);
dojo.connect(dijit.byId('$this->name'), 'destroy', miolo.webForm.showScroll);

dialog.show();

// Fix to put dialog controls inside the form
dojo.byId('frm_$formId').appendChild(dojo.byId('$this->name'));
JS;

        $this->page->onload($js);
        return parent::generate();
    }
}

?>