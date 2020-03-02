<?php

/************************************************
*                                               *
*  TSstatistics                                 *
*                                               *
*  by Thomas Schuster                           *
*  http://www.TSinter.net                       *
*                                               *
*  file: class_counter.php                      *
*  version: 2.6                                 *
*  license: GNU General Public License          *
*  created: 13.04.2002                          *
*  email: admin@TSinter.net                     *
*                                               *
*                                               *
*  Object oriented traffic analyser. Needs no   *
*  database. Tracks the visitors of a website.  *
*  Filters out over 100 robots. Reload restric- *
*  tion. Displays hits per hour/day/month,      *
*  various toplists, all graphical. Auto back-  *
*  up. Administration center.                   *
*                                               *
*  Copyright (c) 2001-2002 TSinter.net          *
*  All rights reserved.                         *
*                                               *
************************************************/

class TScounter extends SupportFunctions
{

    //-----------------------------------------//
    // Set the reload restriction (in seconds) //
    //-----------------------------------------//
    public $reloadRestriction = 300;

    //-----------------------------------------//
    // Set the start value of the visitors     //
    //-----------------------------------------//
    public $visitorStartValue = 1;

    //-----------------------------------------//
    // Do not change anything below!           //
    //-----------------------------------------//
    public $log_data;
    public $spider_library;
    public $ip;
    public $sessionID;
    public $pageImpressions = 1;
    public $visitors;
    public $webpage;

    public $user_agent;
    public $currentIndex;

    public $data_dir;

    public $hour;
    public $day;
    public $month;
    public $year;

    /*
    ** Function: TScounter
    ** Input: STRING class_path
    ** Output: VOID
    ** Description: This is the constructor of the class.
    */
    public function TScounter($url_root, $file_path, $data_path)
    {
        $this->hour = date("G");
        $this->day = date("j");
        $this->month = date("n");
        $this->year = date("Y");

        # Get the ip of the client browser.
        if (getenv("HTTP_X_FORWARDED_FOR"))
        {

            # Client uses a proxy server.
            $client_ip = getenv("HTTP_X_FORWARDED_FOR");
            $this->ip = substr($client_ip, 0, strpos($client_ip, ','));

            if ($this->ip == "")
                $this->ip = getenv("HTTP_CLIENT_IP");
        }
        else
        {

            # Client does not use proxy.
            $this->ip = getenv("REMOTE_ADDR");
        }

        # Check the php version.
        if ($this->_checkMinimumVersion("4.1.0"))
        {

            # php version is at least 4.1.0
            $this->webpage = $_SERVER["PHP_SELF"];
            $this->user_agent = $_SERVER["HTTP_USER_AGENT"];

            if (isset($_COOKIE["PHPSESSID"]))
                $this->sessionID = $_COOKIE["PHPSESSID"];
        }
        else
        {

            # php version is older than 4.1.0
            global $HTTP_SERVER_VARS;
            global $HTTP_COOKIE_VARS;
            $this->webpage = $HTTP_SERVER_VARS["PHP_SELF"];
            $this->user_agent = $HTTP_SERVER_VARS["HTTP_USER_AGENT"];

            if (isset($HTTP_COOKIE_VARS["PHPSESSID"]))
                $this->sessionID = $HTTP_COOKIE_VARS["PHPSESSID"];
        }

        # Get the relative path of the webpage from the document root.
        if (sizeof($subdirs = explode("/", $url_root)) > 0)
        {
            $webpath = explode("/", $this->webpage);
            $this->webpage = "";

            for ($i = sizeof($webpath) - 1; $i >= sizeof($subdirs); $i--)
                $this->webpage = "/" . $webpath[$i] . $this->webpage;
        }

        if ($data_path === null)
        {
            $data_path = $file_path . '/data';
        }

        $this->data_dir = $data_path;

        # Set the path to the data files.
        $this->log_data["track"] = $this->data_dir . "track_" . $this->month . "_" . $this->year . ".log";
        $this->log_data["hits"] = $this->data_dir . "hits.log";

        $this->spider_library = $this->data_dir . "spider.lib";

        # Place the pointer at the last user track.
        $this->currentIndex = sizeof(file($this->log_data["track"])) - 1;

        if ($this->currentIndex < 0)
            $this->currentIndex = 0;
    }

    /*
    ** Function: _log
    ** Input: VOID
    ** Output: VOID
    ** Description: Log the page impressions and visits.
    */
    public function _log($category)
    {
        switch ($category)
            {
            case "impression":
                $subject = $this->webpage;

                break;

            case "visit":
                $subject = $category;

                break;
            }

        # This Function checks whether there is already an entry for the webpage
        # in the hits_data. If there is one, it will increment the number
        # of page impressions. If there is none, it will initialize the number of
        # page impressions of that webpage with 1. Same procedure for logging the
        # visits, except that the start value can be modified if prefered.

        $log_file = file($this->log_data["hits"]);

        if ($this->_incrementIfEqual($subject, $log_file, 0, 1) != 1)
        {
            # There is no entry yet, so we have to add a new one.
            if (!strcmp($subject, "visit"))
                $start_value = $this->visitorStartValue;
            else
                $start_value = 1;

            $line = $subject . "|" . $start_value . "|\r\n";
            $this->_writeLine($this->log_data["hits"], $line, "a");
        }
        else
        {
            # Write the already changed array back to the hits_data.
            $this->_writeArray($this->log_data["hits"], $log_file, "w");
        }
    }

    /*
    ** Function: _getNumberOf
    ** Input: VOID
    ** Output: INTEGER
    ** Description: Get the number of page impressions of actual webpage or
    **              the number of visitors of the website.
    */
    public function _getNumberOf($category)
    {
        switch ($category)
            {
            case "impression":
                $subject = $this->webpage;

                break;

            case "visit":
                $subject = "visit";

                break;
            }

        $log_file = file($this->log_data["hits"]);

        for ($i = 0; $i < sizeof($log_file); $i++)
        {
            $log_line = explode("|", $log_file[$i]);

            if (!strcmp($log_line[0], $subject))
            {
                return $log_line[1];
                break;
            }
        }
    }

    /*
    ** Function: _processPageRequest
    ** Input: BOOLEAN cookie_support
    ** Output: VOID
    ** Description: Does all necessary work to count visitors,
                    page impressions and stores the user track.
    */
    public function _processPageRequest($cookie_support)
    {
        if (!$this->_identifyClientAs("spider"))
        {
            # Start the main process.

            switch ($cookie_support)
                {
                case TRUE:

                    # User was already counted, cookies are supported.
                    if ($this->_compare("sessionID") || $this->_compare("ip"))
                    {

                        # Add the actual webpage to the user track and
                        # increase the page impressions.

                        $this->_trackVisitor("old visitor");
                        $this->_log("impression");
                    }
                    else
                    {

                        # We have to search the corresponding user
                        # track of the actual user in the logfile.
                        # If the index is < 0, we have reached the
                        # end of the logfile. We log the new visitor,
                        # which has never visited us before.

                        $this->currentIndex--;

                        if ($this->currentIndex >= 0)
                            $this->_processPageRequest(TRUE);
                        else
                        {
                            $this->_log("visit");
                            $this->_trackVisitor("new visitor");
                            $this->_log("impression");
                        }
                    }

                    break;

                case FALSE:

                    # No session id generated yet. Either this is
                    # the first page request of the visitor or the
                    # client browser does not support cookies.

                    if ($this->_compare("ip"))
                    {

                        # Client browser does not support cookies but
                        # we can track the visitor by the IP.

                        $this->_trackVisitor("old visitor");
                        $this->_log("impression");
                    }
                    else if ($this->_reloadRestriction())
                    {

                        # Another visitor has requested the website during
                        # the period of time defined as reload restriction.
                        # It is possible that the visitor we are actually
                        # tracking has already been counted during that reload
                        # restriction. To check this, we search the IP in the
                        # older user tracks until the elapsed time is higher
                        # than the reload restriction.

                        # If the index is < 0, we have reached the
                        # end of the logfile. We log the new visitor,
                        # which has never visited us before.

                        $this->currentIndex--;

                        if ($this->currentIndex >= 0)
                            $this->_processPageRequest(FALSE);
                        else
                        {
                            $this->_log("visit");
                            $this->_trackVisitor("new visitor");
                            $this->_log("impression");
                        }
                    }
                    else
                    {

                        # A new visitor has entered the website.

                        $this->_log("visit");
                        $this->_trackVisitor("new visitor");
                        $this->_log("impression");
                    }

                    break;
                }
        }
    }

    /*
    ** Function: _trackVisitor
    ** Input: STRING mode
    ** Output: VOID
    ** Description: Track the user through the website.
    */
    public function _trackVisitor($mode)
    {
        $log_file = file($this->log_data["track"]);

        switch ($mode)
            {
            case "new visitor":

                # Start a new user track.
                $user_track = "|" . $this->ip . "|" . time() . "|" . time() . "|" . $this->webpage . "|\r\n";

                # Add the new user track to the logfile.
                $this->_writeLine($this->log_data["track"], $user_track, "a");
                break;

            case "old visitor":

                # Add the actual webpage to the existing user track.
                # Try to add the session id to the user track.

                $user_track = explode("|", $log_file[$this->currentIndex]);

                if (isset($this->sessionID))
                    $user_track[0] = $this->sessionID;

                # Track the time of the actual request.
                $user_track[3] = time();

                $user_track[sizeof($user_track) - 1] = $this->webpage . "|\r\n";
                $log_file[$this->currentIndex] = implode("|", $user_track);

                # Write the changed user track back to the logfile.
                $this->_writeArray($this->log_data["track"], $log_file, "w");
                break;
            }
    }

    /*
    ** Function: _compare
    ** Input: STRING subject
    ** Output: BOOLEAN
    ** Description: Returns TRUE if two compared subjects are equal.
    */
    public function _compare($subject)
    {
        $log_file = file($this->log_data["track"]);

        $user_track = explode("|", $log_file[$this->currentIndex]);

        switch ($subject)
            {
            case "ip":

                # Compare the object ip with the ip
                # of the user track.

                if (isset($user_track[1]) && !strcmp($user_track[1], $this->ip))
                    return TRUE;
                else
                    return FALSE;

                break;

            case "sessionID":

                # Compare the object session id with the
                # session id stored in the user track.

                if (isset($this->sessionID) && !strcmp($user_track[0], $this->sessionID))
                    return TRUE;
                else
                    return FALSE;

                break;
            }
    }

    /*
    ** Function: _reloadRestriction
    ** Input: VOID
    ** Output: BOOLEAN
    ** Description: Returns TRUE if the time elapsed between the previous
                    page request and the actual page request is smaller
                    than the period of time defined as reloadRestriction.
    */
    public function _reloadRestriction()
    {
        $log_file = file($this->log_data["track"]);
        $user_track = explode("|", $log_file[$this->currentIndex]);

        if (isset($user_track[3]) && (time() - $user_track[3] < $this->reloadRestriction))
            return TRUE;
        else
            return FALSE;
    }

    /*
    ** Function: _identifyClientAs
    ** Input: VOID
    ** Output: STRING spiderline[1] or FALSE
    ** Description: Check if the current visitor is a member of a category.
    */
    public function _identifyClientAs($category)
    {
        switch ($category)
            {
            case "spider":
                $library_data = $this->spider_library;

                $subject = $this->user_agent;
                break;
            }

        $library_file = file($library_data);

        # Compare the spider library with the user_agent.
        # If there are any matches, the current page has
        # been requested by a spider or a robot.

        for ($i = 0; $i < sizeof($library_file); $i++)
        {
            $library_line = explode("|", $library_file[$i]);

            if (eregi($library_line[0], $subject))
                return $library_line[1];
        }

        return FALSE;
    }
}
?>
