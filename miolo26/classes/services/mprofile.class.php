<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MProfile extends MService
{
    /**
     * Attribute Description.
     */
    private $profile;

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
     * @returns (tipo) desc
     *
     */
    public function profileTime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return $sec + $msec;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $name (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function profileEnter($name)
    {
        $this->profile[$name][0] = $this->profileTime(); // current time stamp
        $this->profile[$name][1] = 0;                    // accumulated usage time
        $this->profile[$name][2] = true;                 // state: active
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $name (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function profileExit($name)
    {
        $now = $this->profileTime();
        $usage = $now - $this->profile[$name][0];

        $this->profile[$name][0] = $now;    // current time stamp
        $this->profile[$name][1] += $usage; // accumulated usage time
        $this->profile[$name][2] = false;   // state: inactive
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function profileDump()
    {
        if ($this->profile)
        {
            $total = 0;
            $text = '';

            foreach (array_keys($this->profile)as $name)
            {
                // is profile still active - terminate first
                if ($this->profile[$name][2])
                {
                    $this->profileExit($name);
                }

                $time = $this->profile[$name][1];

                $usage = sprintf("%.3f", $time);
                $msg = "[PROFILE]$name: $usage sec";
                $this->log->logMessage($msg);
                $text .= $msg . '\n';
                $total += $time;
            }

            $total = sprintf("%.3f", $total);
            $msg = "[PROFILE]Total: $total sec";
            $this->log->logMessage($msg);
            $text .= $msg . '\n';
            return $text;
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getProfileDump()
    {
        if ($this->profile)
        {
            $total = 0;

            $html = "<p><b>Profile Information:</b><br>\n"
                        . "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";

            foreach (array_keys($this->profile)as $name)
            {
                // caso profile est? ativo; termina o primeiro 
                if ($this->profile[$name][2])
                {
                    $this->profileExit($name);
                }

                $usage = sprintf("%.3f", $this->profile[$name][1]);

                $html .= "<tr><td>&nbsp;&nbsp;$name:</td><td align=\"right\">&nbsp;$usage&nbsp;sec</td></tr>\n";
            }

            $html .= "</table>\n";
        }

        return $html;
    }
}
?>