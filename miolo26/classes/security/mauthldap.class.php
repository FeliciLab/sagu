<?php
class MAuthLdap extends MAuth
{
    public $login;  // objeto Login
    public $iduser; // iduser do usuario corrente
    public $module; // authentication module;
    public $conn; //the ldap connection
    public $connUser; //inicia conexao com o usuario e não o padrao do conf
    
    public function connect()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        
        $host = $MIOLO->getConf('login.ldap.host');
        $port = $MIOLO->getConf('login.ldap.port');
        $user = $MIOLO->getConf('login.ldap.user');
        $pass = $MIOLO->getConf('login.ldap.password');
        
        if ( strlen($port) > 0 )
        {
            $this->conn = ldap_connect($host, $port);
            $this->connUser = ldap_connect($host, $port);
        }
        else
        {
            $this->conn = ldap_connect($host);
            $this->connUser = ldap_connect($host);
        }
        
        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connUser, LDAP_OPT_PROTOCOL_VERSION, 3);
        $r    = ldap_bind($this->conn, $user, $pass);

        if( ! $r )
        {
            $prompt = _M('Error on ldap connection!',$module);
            print($prompt);
            exit;
        }
        return true;
    }

    public function __destruct()
    {
        ldap_close($this->conn);
    }

    public function __construct()
    {
        parent::__construct();
        $this->connect();
    }


    public function authenticate($user, $pass, $log=true)
    {
        $MIOLO     = $this->manager;
        $base      = $MIOLO->getConf('login.ldap.base');
        $custom    = $MIOLO->getConf('login.ldap.custom');
        $schema    = $MIOLO->getConf('login.ldap.schema');
        $attr      = $MIOLO->getConf('login.ldap.userName');
        $l         = $MIOLO->getConf('login.ldap.login');
        $idPerson  = $MIOLO->getConf('login.ldap.idperson');
        $vars   = array(
                        '%domain%'  =>$_SERVER['HOST_NAME'], 
                        '%login%'   =>$user, 
                        '%password%'=>md5($pass),
                        'AND('      =>'&(',
                        'OR('       =>'|(',
                    );
        switch($schema)
        {
            case 'miolo':
                $search = '(&(login='.$user.')(password='.md5($pass).'))';
                $login  = false;
                break;
            case 'system':
                $search = 'uid='.$user;
                $login  = true;
                break;
            case 'ad':
                $search = 'sAMAccountName='.$user;
                $login  = true;
                break;
            default:
                if($custom)
                {
                    $search = strtr($custom, $vars);
                }
                else
                {
                    $search = strtr('(&(|(uid=%login%)(login=%login%))(objectClass=mioloUser))', $vars);
                }
                $login = null;
        }
        $sr= ldap_search( $this->conn, $base, $search, array('dn', $attr, 'password', 'mioloGroup', $l, $idPerson, 'userPassword' ));
        
        $info = ldap_get_entries($this->conn, $sr);

        for($i=0; $i < $info['count']; $i++)
        {
            $bind = $exists = false;
            if( $info[$i]['dn'] )
            {
                if( ! $login )
                {
                    // LDAP
                    if ( strlen($info[$i]['password'][0]) )
                    {
                        $passwordColumn = 'password';
                    }
                    // Active Directory
                    elseif ( strlen($info[$i]['userpassword'][0]) )
                    {
                        $passwordColumn = 'userpassword';
                    }

                    $exists = $info[$i][$passwordColumn][0] == md5($pass);
                    
                    if ( !$exists )
                    {
                        // Se ainda não encontrou, verifica se no ldap ad não está em md5
                        if ( strlen($info[$i]['userpassword'][0]) )
                        {
                            $passwordColumn = 'userpassword';
                            $senhaDigitada = self::pwdEncrypt($pass);
                            
                            $exists = $info[$i][$passwordColumn][0] == $senhaDigitada["userPassword"];
                        }
                    }
                }
                if( !$exists && (($login) || is_null($login)) && strlen($pass)>0 )
                {
                    $bind   = ldap_bind($this->connUser, $info[$i]['dn'], $pass);
                }
                if( $bind || $exists )
                {
                    $r = true;
                    break;
                }
            }
        }
        if($l) $user = $info[$i][$l][0];

        $groups = array();
        if($info[$i]['miologroup']['count'] > 0)
        {
            unset($info[$i]['miologroup']['count']);
            $groups = $info[$i]['miologroup'];
        }
        
        if($log && $r)
        {
            $login = new MLogin($user,
                                $pass,
                                $info[$i][$attr][0],
                                0);
            $login->setIdPerson( $info[$i][$idPerson][0] );
            $login->setGroups($groups);
            $this->setLogin($login);
        }
        return $r;
    }
    
    /**
     * Retorna a senha formatada para ldap ad
     * 
     * @param string $newPassword
     * @return type
     */
    public static function pwdEncrypt($pw)
    {
        $newpw = '';
        $pw = "\"" . $pw . "\"";
        $len = strlen($pw);
        
        for ( $i = 0; $i < $len; $i++ )
        {
            $newpw .= "{$pw{$i}}\000";
        }
        
        return base64_encode($newpw);
    }
    
    /**
     * Retorna o dn completo de um usuário do ldap.
     * 
     * @param ldap_connect() $ldap_conn
     * @param String $user_name
     * @return String
     */
    public static function getUserDn($ldapConn, $userName) 
    {
        $MIOLO = MIOLO::getInstance();
        $basedn = $MIOLO->getConf("login.ldap.base");
        $searchResults = ldap_search($ldapConn, $basedn, $userName);
        
        if ( !is_resource($searchResults) )
        {
            $MIOLO->error(_M("Error in search results."));
        }

        $entry = ldap_first_entry($ldapConn, $searchResults);
        
        return ldap_get_dn($ldapConn, $entry);
    }
}
?>
