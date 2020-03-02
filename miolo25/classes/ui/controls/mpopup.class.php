<?php

/**
 * Popup component.
 *
 * Note: more than one popup cannot be displayed at the same time.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/08/09
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2010-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_popup.js');

class MPopup extends MDiv
{
    /**
     * Default container id where the popup must be rendered.
     */
    const CONTAINER_ID = 'mPopupResponse';

    /**
     * Default id for the prompt field.
     */
    const PROMPT_FIELD_ID = 'mPopupPromptField';

    /**
     * @var string Define the element id where the popup must be rendered.
     */
    private static $responseContainer = self::CONTAINER_ID;

    /**
     * @var mixed Popup content. 
     */
    public $content;

    /**
     * @var string Title.
     */
    public $title;

    /**
     * @var boolean Whether to show the close button.
     */
    private $showCloseButton = true;

    /**
     * MPopup constructor.
     *
     * @param string $name Fields container id
     * @param array $content Array of fields to be shown on the popup
     * @param string $label Title string
     */
    public function __construct($name, $content, $title)
    {
        parent::__construct(NULL, NULL, 'mPopupInner');

        $this->name = $name;
        $this->content = $content;
        $this->title = $title;
    }

    /**
     * Creates a div to put the popup upon.
     * You really need to add this to your fields to get the MPopup component working.
     *
     * @return MDiv
     */
    public static function getPopupContainer()
    {
        return new MDiv(self::$responseContainer, NULL);
    }

    /**
     * @param string $responseContainer Set the element id where the popup must be rendered.
     */
    public static function setResponseContainer($responseContainer)
    {
        self::$responseContainer = $responseContainer;
    }

    /**
     * @return string Set the element id where the popup will be rendered.
     */
    public static function getResponseContainer()
    {
        return self::$responseContainer;
    }

    /**
     * Generates and returns a div for the title.
     *
     * @param string $title Title string
     * @param boolean $closeButton Defines wheter or not to create a close button
     * @return MDiv Div instance representing the title bar
     */
    private static function getTitle($title, $closeButton = true)
    {
        $fields[] = new MDiv('', $title, 'mPopupTitle');

        if ( $closeButton )
        {
            $fields['close'] = new MDiv(NULL, NULL, 'mPopupClose');
            $fields['close']->addAttribute('onclick', 'mpopup.remove();');
        }
        return new MDiv('popupTitle', $fields, 'mBoxTitle mPopupTitleDiv');
    }

    /**
     * Returns all the default fields to create the popup.
     *
     * @param string $name Fields container id.
     * @param array $content Array of fields to be shown on the popup.
     * @param string $label Title string.
     * @param boolean $closeButton Whether to show the close button.
     * @return array Components array with the title bar and the popup body.
     */
    private static function getFields($name, $content, $label, $closeButton=true)
    {
        $body = new MContainer($name, $content, 'vertical', MFormControl::FORM_MODE_SHOW_NBSP);
        $body->setClass('mPopupBody');

        // FIXME: VERIFICAR NECESSIDADE DE TANTA DIV
        $bodyBox = new MDiv('p1', $body, 'mPopupBodyBox');
        $bodyBoxOuter = new MDiv('p2', $bodyBox, 'mPopupBodyBoxOuter');

        return array( self::getTitle($label, $closeButton), $body );
    }

    /**
     * Returns the prompt fields to create a prompt popup.
     *
     * @param string $message Message to be shown
     * @param string $action Ajax action to be called when clicking de OK button
     * @param string $defaultValue Default value of the prompt field
     * @return array Components array with the title bar and the popup body with prompt fields
     */
    protected static function getPromptFields($message, $action, $defaultValue)
    {
        $fields[] = new MLabel($message);
        $fields[] = new MTextField(self::PROMPT_FIELD_ID, $defaultValue, null);

        $buttons[] = new MButton('btnConfirm', _M('OK'), $action);
        $fields[] = new MDiv(NULL, $buttons, 'mPopupButtonsDiv');

        return $fields;
    }

    /**
     * Returns the alert fields to create a alert popup.
     *
     * @param string $message Message to be shown
     * @param string $action Ajax action to be called on clicking the OK button
     * @return array Components array with the title bar and the popup body with alert fields
     */
    protected static function getAlertFields($message, $action)
    {
        $fields[] = new MLabel($message);
        $buttons[] = new MButton('btnConfirm', _M('OK'), $action);
        $fields[] = new MDiv(NULL, $buttons, 'mPopupButtonsDiv');
        return $fields;
    }

    /**
     * Returns the confirmation fields to create a confirmation popup.
     *
     * @param string $message Message to be shown
     * @param string $actionYes Ajax action to be called on clicking the Yes button
     * @param string $actionNo Ajax action to be called on clicking the No button
     * @return array Components array with the title bar and the popup body with confirmation fields
     */
    protected static function getConfirmFields($message, $actionYes, $actionNo)
    {
        $fields[] = new MLabel($message);
        $buttons[] = new MButton('btnRefuse', _M('No'), $actionNo);
        $buttons[] = new MButton('btnConfirm', _M('Yes'), $actionYes);
        $fields[] = new MDiv(NULL, $buttons, 'mPopupButtonsDiv');
        return $fields;
    }

    /**
     * Returns the popup and the background on rendered format.
     *
     * @param string $inner The popup already rendered
     * @return (string) Rendered popup and background concatenated
     */
    private static function getRendered($inner)
    {
        $background = new MDiv('mPopupBackground');
        $table = '<table id="mPopup"><tr><td>' . $inner . '</td></tr></table>';
        return $background->generate() . $table;
    }

    /**
     * Displays a prompt popup.
     *
     * @param string $message Message to be shown
     * @param string $label Title string
     * @param string $action Ajax action to be called on clicking the OK button
     * @param string $defaultValue Default value of the prompt field
     */
    public static function prompt($message, $label = NULL, $action = ':promptConfirmation', $defaultValue = NULL)
    {
        self::show('mPopupPrompt', self::getPromptFields($message, $action, $defaultValue), $label);
    }

    /**
     * Displays an alert popup.
     *
     * @param string $message Message to be shown
     * @param string $label Title string
     * @param string $action Ajax action to be called on clicking the OK button
     */
    public static function alert($message, $label = NULL, $action = 'javascript:mpopup.remove();')
    {
        self::show('mPopupAlert', self::getAlertFields($message, $action), $label);
    }

    /**
     * Displays a confirmation popup.
     *
     * @param string $message Message to be shown
     * @param string $label Title string
     * @param string $actionYes Ajax action to be called on clicking the Yes button
     * @param string $actionNo Ajax action to be called on clicking the No button
     */
    public static function confirm($message, $label = NULL, $actionYes = ':confirmAction', $actionNo = 'javascript:mpopup.remove();')
    {
        self::show('mPopupConfirm', self::getConfirmFields($message, $actionYes, $actionNo), $label);
    }

    /**
     * Displays a popup without needing to instantiate it.
     *
     * @param string $name Fields container id
     * @param array $content Array of fields to be shown on the popup
     * @param string $label Title string
     * @param boolean $closeButton Whether to show the close button.
     */
    public static function show($name, $content, $label, $closeButton=true)
    {
        $inner = new MDiv(NULL, self::getFields($name, $content, $label, $closeButton), 'mPopupInner');
        $popup = self::getRendered($inner);

        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload(" mpopup.configureClose(); mpopup.show(); ");
        $MIOLO->ajax->setResponse($popup, self::$responseContainer);
    }

    /**
     * Sets to remove the opened popup on loading the page.
     */
    public static function remove()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload("mpopup.remove();");
    }

    /**
     * @param boolean $showCloseButton Set whether to show the close button.
     */
    public function setShowCloseButton($showCloseButton)
    {
        $this->showCloseButton = $showCloseButton;
    }

    /**
     * @return boolean Get whether is to show the close button.
     */
    public function getShowCloseButton()
    {
        return $this->showCloseButton;
    }

    public function generate()
    {
        $fields = self::getFields($this->name, $this->content, $this->title, $this->showCloseButton);
        $this->setInner($fields);

        $this->page->onload(" mpopup.configureClose(); mpopup.show(); ");
        return self::getRendered(parent::generate());
    }
}


/**
 * MPopupPrompt
 *
 */
class MPopupPrompt extends MPopup
{
    /**
     * Prompt popup constructor.
     *
     * @param string $message Message to be shown
     * @param string $label Title string
     * @param string $action Ajax action to be called on clicking the OK button
     * @param string $defaultValue Default value of the prompt field
     */
    public function __construct($message, $label = NULL, $action = ':promptConfirmation', $defaultValue = NULL)
    {
        $fields = parent::getPromptFields($message, $action, $defaultValue);
        parent::__construct('mPopupPrompt', $fields, $label);
    }
}


/**
 * MPopupAlert
 *
 */
class MPopupAlert extends MPopup
{
    /**
     * Alert popup constructor.
     *
     * @param string $message Message to be shown
     * @param string $label Title string
     * @param string $action Ajax action to be called on clicking the OK button
     */
    public function __construct($message, $label = NULL, $action = 'javascript:mpopup.remove();')
    {
        $fields = parent::getAlertFields($message, $action);
        parent::__construct('mPopupAlert', $fields, $label);
    }
}


/**
 * MPopupConfirm
 *
 */
class MPopupConfirm extends MPopup
{
    /**
     * Confirmation popup constructor.
     *
     * @param string $message Message to be shown
     * @param string $label Title string
     * @param string $actionYes Ajax action to be called on clicking the Yes button
     * @param string $actionNo Ajax action to be called on clicking the No button
     */
    public function __construct($message, $label = NULL, $actionYes= ':confirmAction', $actionNo = 'javascript:mpopup.remove();')
    {
        $fields = parent::getConfirmFields($message, $actionYes, $actionNo);
        parent::__construct('mPopupConfirm', $fields, $label);
    }
}

?>
