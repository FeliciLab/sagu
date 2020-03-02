<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MSession extends MService
{
    /**
     * Attribute Description.
     */
    public     $id = "";

    /**
     * Attribute Description.
     */
    public     $name = "";

    /**
     * Attribute Description.
     */
    public     $cookie_path = '/';

    /**
     * Attribute Description.
     */
    public     $cookiename = "PHPSESSID";

    /**
     * Attribute Description.
     */
    public     $lifetime = 0;

    /**
     * Attribute Description.
     */
    public     $cookie_domain = '';

    /**
     * Attribute Description.
     */
    public     $mode = "cookie"; ## We propagate session IDs with cookies

    /**
     * Attribute Description.
     */
    public     $fallback_mode = "cookie"; ## if fallback_mode is also 'ccokie'

    ## we enforce session.use_only_cookie
    /**
     * Attribute Description.
     */
    public     $trans_id_enabled = false;

    /**
     * Attribute Description.
     */
    public     $allowcache = 'nocache';

    /**
     * Attribute Description.
     */
    private $db = NULL;

    /**
     * Attribute Description.
     */
    private $host;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $name' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($name = '')
    {
        parent::__construct();
        $this->setName($name);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $sid (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function start($sid = NULL)
    {
        try
        {
            if ($this->mode == "cookie" && $this->fallback_mode == "cookie")
            {
                ini_set("session.use_only_cookie", "1");
            }

            $this->put_headers();

            if ($sid != NULL)
            {
                $this->setId($sid);
            }

            if ($this->manager->getConf('session.handler') == 'db')
            {
                $this->db = $this->manager->getDatabase('miolo');
                $this->host = $_SERVER['REMOTE_ADDR'];
                session_set_save_handler(
                    array($this, 'open'),
                    array($this, 'close'),
                    array($this, 'read'),
                    array($this, 'write'),
                    array($this, 'delete'),
                    array($this, 'garbage')
                ); 
            }

            $MIOLO = MIOLO::getInstance();
            $scriptName = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));

            /*
             * If session is not shared and the URL address contains an alias, 
             * define a new name for each alias.
             */
            if ( ($MIOLO->getConf('session.shared') == 'false') && count($scriptName) > 1 )
            {
                // Removes index.php
                unset($scriptName[end(array_keys($scriptName))]);

                $sessionName = implode('', $scriptName);
                $path = implode('/', $scriptName);
                $lifeTime = time() + $MIOLO->getConf('session.timeout');

                session_name($sessionName);
                setcookie($sessionName, $_COOKIE[$sessionName], $lifeTime, "/$path");
            }

            session_start();
            $this->id = session_id();

            if (!isset($_SESSION['timestamp']))
            {
                $_SESSION['timestamp'] = time();
            }

            return $ok;
        }
        catch( EMioloException $e )
        {
            throw $e;
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function destroy()
    {
       // Unset all of the session variables.
       $_SESSION = array();
       // If it's desired to kill the session, also delete the session cookie.
       // Note: This will destroy the session, and not just the session data!
       if (isset($_COOKIE[session_name()])) 
       {
           setcookie(session_name(), '', time()-42000, '/');
       }
       // Finally, destroy the session.
       session_destroy();
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function checkTimeout()
    {
        $timeout = $this->manager->getConf('session.timeout');
        // If 0, we are not controling session
        if ( $timeout == 0 )
        {
            return;
        }
        $timestamp = time();
        $difftime = $timestamp - $_SESSION['timestamp'];
        $this->timeout = ($difftime > ($timeout * 60));
        $_SESSION['timestamp'] = $timestamp;
        if ($this->timeout)
        {
            $this->destroy();
            throw new ETimeOutException();
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $name (tipo) desc
     * @param ' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setName($name = '')
    {
        if ($name != '')
        {
            $name = ("" == $this->cookiename) ? 'MIOLOSESSID' : $this->cookiename;
            $name = (string)$name;
            $this->name = session_name($name);
        }
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $sid (tipo) desc
     * @param ' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setId($sid = '')
    {
        if ($sid != '')
        {
            $sid = (string)$sid;
            $this->id = session_id($sid);
        }
        return $this->id;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_names (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function register($var_names)
    {
        if (!is_array($var_names))
        {
            // spaces spoil everything
            $var_names = trim($var_names);
            $var_names = explode(",", $var_names);
        }

        foreach ($var_names as $key => $value)
        {
            global $$value;
            $_SESSION[$value] = $$value;
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_name (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function isRegistered($var_name)
    {
        $var_name = trim($var_name); // to be sure
        return isset($_SESSION[$var_name]);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_names (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function unregister($var_names)
    {
        $ok = true;

        foreach (explode(',', $var_names)as $var_name)
        {
            $var_name = trim($var_name);
            $_SESSION[$var_name] = NULL;
        }

        return $ok;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_name (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getValue($var_name)
    {
        $var_name = trim($var_name); // to be sure
        return (isset($_SESSION[$var_name])) ? $_SESSION[$var_name] : NULL;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_name (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function get($var_name)
    {
        return $this->getValue($var_name);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_name (tipo) desc
     * @param $value (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setValue($var_name, $value)
    {
        $var_name = trim($var_name); // to be sure
        $_SESSION[$var_name] = $value;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $var_name (tipo) desc
     * @param $value (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function set($var_name, $value)
    {
        $this->setValue($var_name, $value);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function freeze()
    {
        session_commit();
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function put_headers()
    {
        # set session.cache_limiter corresponding to $this->allowcache.
        switch ($this->allowcache)
            {
            case "passive":
            case "public":
                session_cache_limiter("public");
                break;
            case "private":
                session_cache_limiter("private");
                break;
            default:
                session_cache_limiter("nocache");
                break;
            }
    }

    // db methods

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function open()
    {
        return TRUE;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function close()
    {
        $this->db = NULL;
        return TRUE;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function read($id)
    {
        $sql = new sql('sessiondata', 'miolo_session', "(sid=?)");
        $sql->setParameters($id);
        $query = $this->db->getQuery($sql);

        if (!$query->eof())
        {
            $data = stripslashes($query->fields('sessiondata'));
        }

        return $data;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     * @param $data (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function write($id, $data)
    {
        $iduser = $this->manager->auth->iduser;
        $sql = new sql('idsession,sid,sessiondata', 'miolo_session', "(sid=?)");
        $sql->setParameters($id);
        $query = $this->db->getQuery($sql);

        if ($query->eof())
        {
            $idsessao = $this->db->getNewId('seq_miolo_session', 'miolo_sequence');
            $ts = $this->manager->getSysTime();
            $sql = new sql('idsession,tsin,tsout,iduser,host,sid,sessiondata', 'miolo_session');
            $this->db->execute($sql->insert(array($idsessao, $ts, '', $iduser, $this->host, $id, $data)));
        }
        else
        {
            $sql = new sql('iduser,sessiondata', 'miolo_session', 'sid=?');
            $this->db->execute($sql->update(array($iduser, $data, $id)));
        }

        return TRUE;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function delete($id)
    {
        $sql = new sql('tsout', 'miolo_session', 'sid=?');
        $this->db->execute($sql->update(array(date('Y/m/d H:i:s'), $id)));
        return TRUE;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $lifetime (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function garbage($lifetime)
    {
        return TRUE;
    }
}
?>
