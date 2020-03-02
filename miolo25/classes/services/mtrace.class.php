<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MTrace extends MService
{
    /**
     * Attribute Description.
     */
    private $trace;

    /**
     * Attribute Description.
     */
    private $log;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = $this->manager->log;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $msg (tipo) desc
     * @param $file (tipo) desc
     * @param $line=0 (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function trace($msg, $file = '', $line = 0)
    {
        $message = $msg;
        if ($file != '') $message .= " [file: $file] [line: $line]";
        $this->trace[] = $message;
        $this->log->logMessage('[TRACE]' . $message);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function traceDump()
    {
        if ($this->trace)
        {
            $html = "<p><b>Tracing Information:</b>\n" . "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";

            foreach ($this->trace as $t)
                $html .= "<tr><td>&nbsp;&nbsp;$t</td></tr>\n";

            $html .= "</table>\n";
        }

        return $html;
    }

    public function traceStack($file = '', $line = 0)
    {
        try
        {
            throw new Exception;
        }
        catch ( Exception $e )
        {
            $strStack = $e->getTraceAsString();
        }
        $this->trace($strStack,$file,$line);
        return $strStack; 
    }

}
?>