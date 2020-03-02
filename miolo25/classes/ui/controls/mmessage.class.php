<?php

/**
 * Message class.
 * This class implements a simply but nice way to show messages to the user. Note that this class uses Dojo's features, 
 * so it's dependent on that toolkit. 
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br],
 *         Armando Taffarel Neto [taffarel@solis.coop.br],
 *         Vilson Cristiano Gartner [vilson@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Vilson Cristiano Gartner [vilson@solis.coop.br]
 *
 * @since
 * Creation date 2009/04/17
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2009 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in SVN repository: http://www.miolo.org.br
 *
 */

/*
 * Class to show a message.
 * This classes can be used to show messages to the user, using Dojo's features. On your form, 
 * you need only to add a field, which will be used to show the message, and than when you want, 
 * create a new MMessage object and it's done.  
 * 
 * @example
 * <code>
 * ...
 * $fields[] = MMessage::getMessageContainer();
 * $this->setFields($fields);
 * ...
 * new MMessage("Error message string here.", MMessage::TYPE_ERROR);
 * </code>
 * 
 */
$MIOLO = MIOLO::getInstance();
$MIOLO->page->addDojoRequire( 'dojo.fx' );
$MIOLO->page->addScript('m_message.js');

class MMessage
{
    // Message types
    const TYPE_SUCCESS = 'Success';
    const TYPE_INFORMATION = 'Information';
    const TYPE_WARNING = 'Warning';
    const TYPE_ERROR = 'Error';

    const CSS_CLASS = 'mMessage mMessage';
    const MSG_CONTAINER_ID = 'messageDiv';
    const MSG_DIV_ID = 'mainMessageDiv';

    /**
     * Returns a message, formated according the type.
     *
     * @param (string) $mensagem Message to show
     * @param (string) $type Message type, TYPE_INFORMATION, TYPE_WARNING or TYPE_ERROR
     * @param (boolean) $display Defines if the message will be displayed,
     * otherwise, message is registered in session to be displayed on the next event
     * @param (string) $msgContainer Id of the div/container where the message will be displayed
     * @param (boolean) $animate Sets if the message must be animated
     */
    public function __construct($message, $type = self::TYPE_INFORMATION, $display = true, $msgContainer = self::MSG_CONTAINER_ID, $animate = true)
    {
        $MIOLO = MIOLO::getInstance();

        if ( is_array($message) )
        {
            $message = implode($message, '<br/>');
        }

        $box = new MDiv(self::MSG_DIV_ID, $message, self::CSS_CLASS . $type);

        if ( $display )
        {
            $jsCode = "mmessage.show('$msgContainer', '$animate');";
            $jsCode .= " mmessage.connectHideEvents('". self::MSG_DIV_ID ."', '$animate'); ";
            $MIOLO->page->onload($jsCode);
            $MIOLO->ajax->setResponse($box, $msgContainer);
        }
        else
        {
            // TODO: some kind of multi message support, adding them to the session
            $session = new MSession(self::MSG_DIV_ID);
            $session->setValue('lastMessage', $message);
            $session->setValue('lastType', $type);
            $session->setValue('lastAnimate', $animate);
        }
    }

    /**
     * Returns session object that could have messages
     *
     * @return (object) MSession
     */
    public static function getMessageSession()
    {
        return new MSession(self::MSG_DIV_ID);
    }

    /**
     * Clear the last registered message in session
     */
    public static function clearLastMessage()
    {
        $session = new MSession(self::MSG_DIV_ID);
        $session->setValue('lastMessage', '');
        $session->setValue('lastType', '');
        $session->setValue('lastAnimate', '');
    }

    /**
     * Returns a div with a message to be usesd statically
     *
     * @return (object) MDiv
     */
    public static function getStaticMessage($name, $message, $type = self::TYPE_SUCCESS)
    {
        return new MDiv($name, $message, self::CSS_CLASS . $type);
    }

    /**
     * Create a container for the messages. If a message is registered in session, returns it in the container.
     *
     * @return (object) Container (MDiv) with a message, if it's registered in session.
     */
    public static function getMessageContainer()
    {
        $MIOLO = MIOLO::getInstance();

        $session = self::getMessageSession();
        $type = $session->getValue('lastType');
        $message = $session->getValue('lastMessage');

        $content = '';

        // if a message exists in session, return it
        if ( $type && $message )
        {
            $animate = $session->getValue('lastAnimate');
            $content = new MDiv(self::MSG_DIV_ID, $message, self::CSS_CLASS . $type);
            
            self::clearLastMessage();

            $jsCode = "mmessage.show('". self::MSG_DIV_ID ."', '$animate');";
            $jsCode .= "mmessage.connectHideEvents('". self::MSG_DIV_ID ."', '$animate');";
            $MIOLO->page->onload($jsCode);
        }

        return new MDiv(self::MSG_CONTAINER_ID, $content);
    }
}

/**
 * Inserts a success message on form
 * 
 */
class MMessageSuccess extends MMessage
{
    /**
     * @param (string) $mensagem Message to show
     * @param (boolean) $display Defines if the message will be displayed,
     * otherwise, message is registered in session to be displayed on the next event
     */
    public function __construct($message, $display = true)
    {
        parent::__construct($message, self::TYPE_SUCCESS, $display);
    }
}

/**
 * Inserts an information message on form
 *
 */
class MMessageInformation extends MMessage
{
    /**
     * @param (string) $mensagem Message to show
     * @param (boolean) $display Defines if the message will be displayed,
     * otherwise, message is registered in session to be displayed on the next event
     */
    public function __construct($message, $display = true)
    {
        parent::__construct($message, self::TYPE_INFORMATION, $display);
    }
}

/**
 * Inserts a warning message on form
 *
 */
class MMessageWarning extends MMessage
{
    /**
     * @param (string) $mensagem Message to show
     * @param (boolean) $display Defines if the message will be displayed,
     * otherwise, message is registered in session to be displayed on the next event
     */
    public function __construct($message, $display = true)
    {
        parent::__construct($message, self::TYPE_WARNING, $display);
    }
}

/**
 * Inserts an error message on form
 *
 */
class MMessageError extends MMessage
{
    /**
     * @param (string) $mensagem Message to show
     * @param (boolean) $display Defines if the message will be displayed,
     * otherwise, message is registered in session to be displayed on the next event
     */
    public function __construct($message, $display = true)
    {
        parent::__construct($message, self::TYPE_ERROR, $display);
    }
}

?>
