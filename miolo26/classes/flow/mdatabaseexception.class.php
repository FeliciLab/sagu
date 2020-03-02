<?php

/**
 * Database exception class
 * Currently this exception is generated only on throwError method at PostgresConnection
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/10/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solu��es Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2010 SOLIS - Cooperativa de Solu��es Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */
class MDatabaseException extends Exception
{
    const UNKNOWN_ERROR_CODE = 0;
    const KNOWN_ERROR_CODE = 1; // Error code for treated messages

    public function __construct($message, $code=NULL)
    {
        parent::__construct($message, $code);
        $this->generateMessage();
    }

    /**
     * Generate the error message according to the cause
     *
     * @param (string) $svnInfo
     * @return (string) Message
     */
    public function generateMessage($svnInfo = '')
    {
        $MIOLO = MIOLO::getInstance();
        switch ( $this->code )
        {
            case self::KNOWN_ERROR_CODE:

                $this->message = ucfirst($this->message);

                if ( MUtil::getBooleanValue($MIOLO->getConf('options.backtrace')) )
                {
                    $trace = $this->getTrace();
                    $query = $trace[3]['args'][0];

                    $this->message .= '<br /><br />' . _M('Query') . ": $query" . '<br /><br />';
                    $this->message .= $this->getTraceAsString();
                    $this->message .= '<br/>';
                    $this->message .= $svnInfo;
                }

                break;


            case self::UNKNOWN_ERROR_CODE:
            default:

                $Message = ucfirst($this->message);
                $this->message = _M('Database error occurred') . ': ';
                
                if ( MUtil::getBooleanValue($MIOLO->getConf('options.backtrace')) )
                {
                    $trace = $this->getTrace();
                    $query = $trace[3]['args'][0];

                    $this->message .= '<br /><br />' . _M('Query') . ": $query" . '<br /><br />';
                    $this->message .= $this->getTraceAsString();
                    $this->message .= '<br/>';
                    $this->message .= $svnInfo;
                }
        }
        
        return $this->message;
    }

    /**
     * Used in unittest errors, makes the MDatabaseException echoable and printable
     *
     * @return String   Error message
     */
    public function __toString()
    {
        $MIOLO = MIOLO::getInstance();

        $trace = $this->getTrace();
        $query = $trace[3]['args'][0];

        $message = ucfirst($this->getMessage());
        $message .= "\n\n" . _M('Query') . ": $query";

        if ( MUtil::getBooleanValue($MIOLO->getConf('options.backtrace')) )
        {
            $message .= "\n\n" . $this->getTraceAsString();
        }

        return $message;
    }

}
?>
