<?php

/************************************************
*                                               *
*  TScounter                                    *
*                                               *
*  by Thomas Schuster                           *
*  http://www.TSinter.net                       *
*                                               *
*  file: class_support.php                      *
*  version: 3.2                                 *
*  license: GNU General Public License          *
*  created: 20.04.2002                          *
*  email: admin@TSinter.net                     *
*                                               *
*                                               *
*  This is an advanced counter in OO-design     *
*  which uses both, session management AND      *
*  ip comparison with a reload restriction,     *
*  to recognize different visitors of a         *
*  website. It recognizes over 100 spiders and  *
*  robots and does not count them as visitors.  *
*  It counts the impressions for every webpage  *
*  of the website and track the visitors way.   *
*                                               *
*  Copyright (c) 2001-2002 TSinter.net          *
*  All rights reserved.                         *
*                                               *
************************************************/

class SupportFunctions
{

    //--------------------------------//
    // This class provides some basic //
    // functionality, which is often  //
    // used by other scripts.         //
    //--------------------------------//

    /*
    ** Function: _checkMinimumVersion
    ** Input: STRING version
    ** Output: BOOLEAN
    ** Description: Check if the current php version is equal
    **              or newer than the minimum required version.
    */
    public function _checkMinimumVersion($version)
    {

        # Get rid of the points.
        $minimum_version = preg_replace("%\.%", "", $version);
        $current_version = preg_replace("%\.%", "", phpversion());

        if ($current_version < $minimum_version)
            return FALSE;
        else
            return TRUE;
    }

    /*
    ** Function: _cookieCheck
    ** Input: VOID
    ** Output: BOOLEAN
    ** Description: Checks the existence of a session variable. If the session
    **              variable exists, the client browser supports cookies.
    */
    public function _cookieCheck()
    {
        global $cookie;

        if (!isset($cookie))
        {
            $cookie = 1;
            return FALSE;
        }
        else
            return TRUE;
    }

    /*
    ** Function: _writeLine
    ** Input: STRING file, STRING line, CHAR mode
    ** Output: INTEGER message_id
    ** Description: Write a string to a file. The mode is
    **              specified like in the fopen function.
    */
    public function _writeLine($file, $line, $mode)
    {
        $fp = fopen($file, $mode) or die("Error while _writeLine to $file");

        if (flock($fp, LOCK_EX))
        {
            if ($mode == "a")
                fseek($fp, 0, SEEK_END);

            fputs($fp, $line);

            # Successful execution
            $message_id = 1;
        }
        else
        {
            # LOCK_EX error
            $message_id = 2;
        }

        if (flock($fp, LOCK_UN))
            fclose($fp);
        else
        {
            # LOCK_UN error
            $message_id = 3;
        }

        return $message_id;
    }

    /*
    ** Function: _writeArray
    ** Input: STRING file, STRING line, CHAR mode
    ** Output: INTEGER message_id
    ** Description: Write an array to a file. The mode is 
    **              specified like in the fopen function.
    */
    public function _writeArray($file, $array, $mode)
    {
        $fp = fopen($file, $mode) or die("Error while _writeArray to $file");

        if (flock($fp, LOCK_EX))
        {
            for ($i = 0; $i < sizeof($array); $i++)
                fputs($fp, $array[$i]);

            # Successful execution
            $message_id = 1;
        }
        else
        {
            # LOCK_EX error
            $message_id = 2;
        }

        if (flock($fp, LOCK_UN))
            fclose($fp);
        else
        {
            # LOCK_UN error
            $message_id = 3;
        }

        return $message_id;
    }

    /*
    ** Function: _incrementIfEqual
    ** Input: STRING string, &STRING array[], INTEGER index_equal, INTEGER index_increment
    ** Output: INTEGER changed
    ** Description: Increment a specified array variable, if two strings are equal.
    */
    public function _incrementIfEqual($string, &$array, $index_equal, $index_increment)
    {
        $changed = 0;

        for ($i = 0; $i < sizeof($array); $i++)
        {
            $line = explode("|", $array[$i]);

            if (!strcmp($string, $line[$index_equal]))
            {
                $changed = 1;
                $line[$index_increment] = $line[$index_increment] + 1;
            }

            $array[$i] = implode("|", $line);
        }

        return $changed;
    }

    /*
    ** Function: _replace_nl
    ** Input: STRING string
    ** Output: STRING return_result
    ** Description: Replace \n with html <br />.
    */
    public function _replace_nl($string)
    {
        $lines = explode("\n", $string);
        $return_result = "";

        # while there are strings with \n at the end
        for ($i = 0; $i < count($lines); $i++)
        {
            # delete unneccessary characters
            $lines[$i] = trim($lines[$i]);

            # add <br /> if there is no <br />, some people with html-skills already
            # add a <br> or a <br /> and press the enter key

            if ((substr($lines[$i], -4) != '<br>') && (substr($lines[$i], -4) != '<br />'))
                $lines[$i] .= '<br />';

            $return_result .= $lines[$i];
        }

        return $return_result;
    }

    /*
    ** Function: _getDirArray
    ** Input: STRING path
    ** Output: STRING dir_array[]
    ** Description: Get an array with all filenames of a directory.
    */
    public function _getDirArray($path)
    {
        $dir_array = array(
            );

        # Load directory into an array.
        $handle = opendir($path);

        # Fill the array with the filenames.
        while (($file = readdir($handle)) !== FALSE)
        {
            if ($file != "." && $file != "..")
                $dir_array[sizeof($dir_array)] = $file;
        }

        # Clean up and sort.
        closedir($handle);

        return $dir_array;
    }

    /*
    ** Function: _getListOfFiles
    ** Input: STRING path_absolute, STRING path_relative, STRING match_string
    ** Output: STRING files[]
    ** Description: Get a list of all files of a directory which name matches
    **              the match_string. Also get the filesizes.
    */
    public function _getListOfFiles($path_absolute, $path_relative, $match_string)
    {

        # Load directory into an array.
        $handle = opendir($path_absolute);

        # Fill the array with the filenames and filesizes.
        $i = 0;

        while (($file = readdir($handle)) !== FALSE)
        {
            if (strstr($file, $match_string))
            {
                $files[$i]["name"] = $file;
                $files[$i]["path"] = $path_relative . $file;
                $files[$i]["size"] = round(filesize($path_absolute . $file) / 1024, 2);
                $i++;
            }
        }

        # Clean up and sort.
        closedir($handle);

        return $files;
    }

    /*
    ** Function: _getSortedArray
    ** Input: STRING unsorted_array[], INTEGER sort_index
    ** Output: STRING sorted_array[]
    ** Description: Sort a multi-dimensional array. Each dimension is treated
    **              like a column of a table. The $column specifies the
    **              primary column to sort by.
    */
    public function _getSortedArray($unsorted_array, $column)
    {
        for ($i = 0; $i < sizeof($unsorted_array); $i++)
        {
            $sort_line = explode("|", $unsorted_array[$i]);

            # Find out how many columns exist. Only four columns are allowed.
            # Important: The last column contains always the \r\n !

            for ($j = 0; $j < sizeof($sort_line); $j++)
            {
                # Fill the columns of the table.
                if (strcmp("n/a", $sort_line[0]))
                    $sorted_array[$j][$i] = $sort_line[$j];
                else
                    $undefined_line = explode("|", $unsorted_array[$i]);
            }

            if ($column == 0)
            {
                $index_1 = 1;
                $index_2 = 2;
            }
            else if ($column == 1)
            {
                $index_1 = 0;
                $index_2 = 2;
            }
            else if ($column == 2)
            {
                $index_1 = 0;
                $index_2 = 1;
            }

            switch (sizeof($sort_line))
                {
                case 2:
                    array_multisort($sorted_array[$column]);

                    break;

                case 3:
                    array_multisort($sorted_array[$column], SORT_NUMERIC, SORT_DESC, $sorted_array[$index_1]);

                    break;

                case 4:
                    array_multisort($sorted_array[$column], SORT_NUMERIC, SORT_DESC, $sorted_array[$index_1], $sorted_array[$index_2]);

                    break;
                }
        }

        if (isset($undefined_line))
        {
            for ($i = 0; $i < sizeof($undefined_line); $i++)
                $sorted_array[$i][sizeof($sorted_array[$i])] = $undefined_line[$i];
        }

        return $sorted_array;
    }

    /*
    ** Function: _getSumOfColumn
    ** Input: STRING table[], INTEGER column
    ** Output: INTEGER sum
    ** Description: Sum up every entry of a specified column of a table.
    */
    public function _getSumOfColumn($table, $column)
    {
        $sum = 0;

        for ($i = 0; $i < sizeof($table); $i++)
        {
            $table_line = explode("|", $table[$i]);
            $sum += $table_line[$column];
        }

        return $sum;
    }

    /*
    ** Function: _listArray
    ** Input: STRING array[]
    ** Output: STRING str
    ** Description: List the key-value-pairs of an array.
    */
    public function _listArray($array)
    {
        while (list($key, $value) = each($array))
        {
            $str .= "<b>$key:</b> $value<br />\n";
        }

        return $str;
    }

    /*
    ** Function: _backup
    ** Input: STRING log_data[], STRING date
    ** Output: INTEGER message_id;
    ** Description: Backup specified files and delete old backups.
    */
    public function _backup($log_data, $backup_dir, $backup_log)
    {
        $message_id = 0;

        # Get the date of the last backups
        $backup_file = file($backup_log);
        $backup_line = explode("|", $backup_file[0]);
        $timeOfLastBackup = $backup_line[0];

        if (!file_exists($backup_dir))
        {

            # Create backup directory. 
            mkdir($backup_dir, 0755);
        }

        while (list($log_name, $log_path) = each($log_data))
        {
            $path_original = pathinfo($log_path);

            # Specify path and name of backup files.
            if ($this->_checkMinimumVersion("4.1.0"))
                $bak_path = $backup_dir . basename($path_original["basename"], ".log") . ".bak";
            else
            {
                $basename = basename($path_original["basename"]);
                $basename = substr($basename, 0, -4);
                $bak_path = $backup_dir . $basename . ".bak";
            }

            if (copy($log_path, $bak_path))
            {

                # Files successfully backed up.

                # Note time of execution.
                $backup_file[0] = "time of last backup|" . time() . "|\r\n";

                if (!isset($backup_file[1]))
                    $backup_file[1] = "atomatic backup made?|0|\r\n";

                $this->_writeArray($backup_log, $backup_file, "w");
                $message_id = 1;
            }
        }

        return $message_id;
    }

    /*
    ** Function: _delete
    ** Input: STRING file
    ** Output: VOID
    ** Description: Delete a file in the server.
    */
    public function _delete($file)
    {

        # Try the standard unix command.
        $delete = unlink($file);

        if (file_exists($file))
        {
            # Server might run under Windows.

            $filesys = eregi_replace("/", "\\", $file);
            $delete = system("del $filesys");

            clearstatcache();

            if (file_exists($file))
            {

                # Set the file access permission explicitly.
                $delete = chmod($file, 0775);
                $delete = unlink($file);
                $delete = system("del $filesys");
            }
        }
    }

    /*
    ** Function: _processLogin
    ** Input: STRING password_data, STRING name, STRING pass
    ** Output: INTEGER message_id
    ** Description: Login function. Sets a session variable if user is valid.
    */
    public function _processLogin($password_data, $name, $pass)
    {

        # Search name in data file. If name exists,
        # check password.
        $temp_file = file($password_data);

        for ($i = 0; $i < sizeof($temp_file); $i++)
        {
            $temp_line = explode("|", $temp_file[$i]);

            if (!strcmp($temp_line[0], $name))
            {
                # Name does exist.
                $crypt_pass = crypt($pass, CRYPT_BLOWFISH);

                if (!strcmp($temp_line[1], $crypt_pass))
                {
                    # Password is correct.

                    session_register("logged_in");

                    # Check the php version.
                    if ($this->_checkMinimumVersion("4.1.0"))
                    {

                        # php version is at least 4.1.0
                        $_SERVER["logged_in"] = TRUE;
                    }
                    else
                    {

                        # php version is older than 4.1.0
                        global $HTTP_SERVER_VARS;
                        $HTTP_SERVER_VARS["logged_in"] = TRUE;
                    }

                    # Welcome message
                    $message_id = 1;
                }
                else
                {

                    # Wrong password
                    $message_id = 2;
                }
            }
            else
            {

                # Wrong user name
                $message_id = 3;
            }
        }

        return $message_id;
    }

    /*
    ** Function: _setPassword
    ** Input: STRING data_dir, STRING password_data, STRING user, STRING pass
    ** Output: VOID
    ** Description: Adds a user to a user database.
    */
    public function _setPassword($data_dir, $password_data, $user, $pass)
    {
        if (!file_exists($data_dir))
            mkdir($data_dir, 0755);
        else
            chmod($data_dir, 0755);

        $crypted_password = crypt($pass, CRYPT_BLOWFISH);
        $password_line = $user . "|" . $crypted_password . "|\r\n";
        return $this->_writeLine($password_data, $password_line, "a");
    }

    /*
    ** Function: _getFormatedTime
    ** Input: INTEGER timestamp, STRING mode
    ** Output: STRING
    ** Description: Returns a formated date string.
    */
    public function _getFormatedTime($timestamp, $mode)
    {
        switch ($mode)
            {
            case "minute":
                return date("j.n.Y, H:i", $timestamp);

                break;

            case "day":
                return date("j.n.Y", $timestamp);

                break;
            }
    }

    /*
    ** Function: _defineJumpMenu
    ** Input: VOID
    ** Output: VOID
    ** Description: Defines a java script jump menu.
    */
    public function _defineJumpMenu()
    {

        # This function displays JavaScript code
        # and has to be called between the <head>
        # tags of the webpage.

        echo("<script language=\"javascript\" type=\"text/javascript\">\n");
        echo("<!--\n");
        echo("function JumpMenu(targ,selObj,restore){\n");
        echo("  eval(targ+\".location='\"+selObj.options[selObj.selectedIndex].value+\"'\")\n");
        echo("  if (restore) selObj.selectedIndex=0\n");
        echo("}\n");
        echo("//-->\n");
        echo("</script>\n");
    }

    /*
    ** Function: _definePopUpWindow
    ** Input: VOID
    ** Output: VOID
    ** Description: Defines a java script popup.
    */
    public function _definePopUpWindow()
    {
        echo("<script Language=\"JavaScript\" type=\"text/javascript\">\n");
        echo("<!--\n");
        echo("function popup(url, name, width, height)\n");
        echo("{\n");
        echo("settings=\n");
        echo("\"toolbar=no,location=no,directories=no,\"+\n");
        echo("\"status=no,menubar=no,scrollbars=yes,\"+\n");
        echo("\"resizable=yes,width=\"+width+\",height=\"+height;\n");
        echo("MyNewWindow=window.open(url,name,settings);\n");
        echo("}\n");
        echo("//-->\n");
        echo("</script>\n");

        return 0;
    }

    /*
    ** Function: _displayJumpMenu
    ** Input: STRING option_selected, STRING array, STRING variable
    ** Output: VOID
    ** Description: Displays a java script menu.
    */
    public function _displayJumpMenu($option_selected, $array, $variable)
    {
        echo("<select class=\"jumpMenu\" name=\"jumpMenu\" onchange=\"JumpMenu('parent',this,0)\">");
        echo("  <option selected=\"selected\">" . $option_selected . "</option>");

        # Now display all entries from the array.
        # The newest entry is on top.

        for ($i = sizeof($array) - 1; $i >= 0; $i--)
        {
            echo("<option value=\"$this->webpage?" . $variable . "=" . $array[$i]["link"] . "\">");
            echo($array[$i]["name"]);
            echo("</option>");
        }

        echo("</select>");
    }
}
?>
