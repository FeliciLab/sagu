<?php

/**
 * Dialogs classes.
 * Implementation of the prompt class for generating common dialogs.
 * 
 * @author Vilson Cristiano Gartner [author] [vgartner@gmail.com]
 * @author Thomas Spriestersbach    [author] [ts@interact2000.com.br]
 * 
 * \b Maintainers: \n
 * Vilson Cristiano Gartner [author] [vilson@solis.coop.br]
 *
 * @package ui
 * @subpackage controls
 *
 * @since 
 * This class was created 2001/08/14
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres
 *
 * \b Copyright: \n 
 *   CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil
 *   Copyleft (L) 2005-present SOLIS, Lajeado/RS - Brasil
 * 
 * @license  
 *   Licensed under GPL (see COPYING.TXT or FSF at www.fsf.org for
 *   further details)
 *
 * @version $id$
 */
class MPrompt extends MInputControl
{
    /**
     * Information type message
     */
    const MSG_TYPE_INFORMATION  = 'information';
    
    /**
     * Error type message
     */
    const MSG_TYPE_ERROR = 'error';
    
    /**
     * Confirmation type message 
     */
    const MSG_TYPE_CONFIRMATION = 'confirmation';

    /**
     * Question type message 
     */
    const MSG_TYPE_QUESTION = 'question';
    
    /**
     * Prompt type message 
     */
    const MSG_TYPE_PROMPT = 'prompt';

    /**
     * Alert type message
     */
    const MSG_TYPE_ALERT = 'alert';

    public $caption;
    public $message;
    public $buttons;
    public $icon;
    public $type = MPrompt::MSG_TYPE_PROMPT;
    public $box;
    public $close;
    
    /**
     * This is the constructor of the class.
     * Use the setType method to specify the type of the dialog.
     * 
     * @see setType
     * 
     * @param (string) $caption Title of the box
     * @param (string) $message Message for the prompt message
     * @param (string) $icon    URL of the image to display on the message
     *
     * @example
     * \code
     *     $dialog = new MPrompt('Information', 'Miolo is a nice framework :-)' );
     * \endcode
     * 
     * @return (void)
     */
    public function __construct($caption = null, $message = null, $icon = '/images/error.gif')
    {
        parent::__construct(NULL, NULL);
        $this->caption = $caption;
        $this->message = $message;
        $this->icon = $icon;

        if (!$this->caption)
        {
            $this->caption = _M('Alert');
        }

        if (!$this->message)
        {
            $this->message = _M('Unknown reason');
        }
    }

    public static function alert($msg, $goto = '', $event = '')
    {
        $MIOLO = MIOLO::getInstance();
        $prompt = new MPrompt(_M('Alert'), $msg, $MIOLO->url_home . '/images/alert.gif');
        $prompt->setType(MPrompt::MSG_TYPE_ALERT);

        if ( isset($goto) && $goto != 'NONE' )
        {
            $prompt->addButton( 'OK', $goto, $event);
        }

        return $prompt;
    }

    public static function error($msg = '', $goto = '', $caption = '', $event = '')
    {
        if (!$caption)
        {
            $caption = _M('Error');
        }

        $prompt = new MPrompt($caption, $msg);
        $prompt->setType(MPrompt::MSG_TYPE_ERROR);

        if ($goto != 'NONE' && isset($goto))
        {
            $prompt->addButton( _M('Back'), $goto, $event);
        }

        return $prompt;
    }

    public static function information($msg, $goto = '', $event = '')
    {
        $MIOLO = MIOLO::getInstance();

        $prompt = new MPrompt(_M('Information'), $msg, $MIOLO->url_home . '/images/information.gif');
        $prompt->setType(MPrompt::MSG_TYPE_INFORMATION);

        if ($goto != 'NONE' && isset($goto))
        {
            $prompt->addButton( 'OK', $goto, $event);
        }

        return $prompt;
    }

    public static function confirmation($msg, $gotoOK = '', $gotoCancel = '', $eventOk = '', $eventCancel = '')
    {
        $MIOLO = MIOLO::getInstance();

        $prompt = new MPrompt(_M('Confirmation'), $msg, $MIOLO->url_home . '/images/attention.gif');
        $prompt->setType(MPrompt::MSG_TYPE_CONFIRMATION);
        
        $prompt->addButton( 'OK', $gotoOK, $eventOk);
        $prompt->addButton( _M('Cancel'), $gotoCancel, $eventCancel);


        return $prompt;
    }

    public static function question($msg, $gotoYes = '', $gotoNo = '', $eventYes = '', $eventNo = '')
    {
        $MIOLO = MIOLO::getInstance();

        $prompt = new MPrompt(_M('Question'), $msg, $MIOLO->url_home . '/images/question.gif');
        $prompt->setType(MPrompt::MSG_TYPE_QUESTION);
        
        $prompt->addButton(_M('Yes'), $gotoYes, $eventYes);
        $prompt->addButton(_M('No'), $gotoNo, $eventNo);

        return $prompt;
    }

    /**
     * Sets the type of the message. Use the MPrompt::MSG_TYPE_??? constants as parameter
     *
     * @param (string) $type 
     */
    public function setType( $type = MPrompt::MSG_TYPE_INFORMATION )
    {
        $this->type = $type;
    }

    /**
     * Adds a button to the prompt dialog.
     *
     * @param (string) $label Button label
     * @param (string) $href  Url address which will be open when the button is clicked  
     * @param (string) $event A event which will be attached to the button
     */
    public function addButton($label, $href, $event = '')
    {
        $this->buttons[] = array ($label, $href, $event);
    }

    public function generateInner()
    {
        $content = '';

        if ( ! is_array($this->message) )
        {
            $this->message = array($this->message);
        }

        $m = new MUnorderedList('',$this->message);

        $textBox = new MDiv('', $m, 'mPromptBoxText');

//        $content = '&nbsp;';

        if ($this->buttons)
        {
            foreach ($this->buttons as $button)
            {
//                $label = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$button[0].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $label = $button[0];
                $goto = $button[1];
                $event = $button[2];
                $name = $this->name;

                if ( strpos($goto, 'javascript:') === 0 )
                {
                    $onclick = "$goto;";
                }
                elseif ( $goto != '' )
                {
                    $onclick = "go:$goto" . (($event != '') ? "&event=$event" : "");
                }
                else
                {
                    if ( $event != '' )
                    {
                        $eventTokens = explode(';', $event);
                        $onclick = "javascript:miolo.doPostBack('{$eventTokens[0]}','{$eventTokens[1]}','{$this->formId}');";
                    }
                }

                $b = new MButton($name, $label, $onclick);
                $b->setClass('button');
                $content[] = $b->generate();
            }

            $b = new MUnorderedList('',$content);
            $buttonBox = new MDiv('', $b, 'mPromptBoxButton');
        }
        else
        {
            $buttonBox = new MSpacer('20px');
        }

        $this->close = $onclick;
        $type = ucfirst($this->type);
        $c = new MVContainer('',array($textBox,$buttonBox));
        $c->setClass("mPromptBoxBody mPromptBox{$type}");
        $this->inner = $c;
	}

	function generate()
    {
        $this->generateInner();
        $type = ucfirst($this->type);
        $this->box = new MBox($this->caption, $this->close, '');
        $this->box->boxTitle->setClass("mPromptBoxTitle mPromptBoxTitle{$type}");
        $this->box->setControls(array($this->inner));
        
        $id = $this->getUniqueId();
        $prompt = new MDiv("pb$id",new MDiv($id,$this->box,"mPromptBox"),"mPrompt");
        
        return $prompt->generate();
    }
}

?>

