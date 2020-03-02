<?
class MPermsLdap extends MPerms
{
    private $auth;
    public  $perms;

    public function __construct()
    {
        parent::__construct();
        $this->auth = $this->manager->getAuth();
        $this->perms = array
            (
            A_ACCESS  => "SELECT",
            A_INSERT  => "INSERT",
            A_DELETE  => "DELETE",
            A_UPDATE  => "UPDATE",
            A_EXECUTE => "EXECUTE",
            A_ADMIN   => "SYSTEM"
            );
    }

    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    public function checkAccess($module, $action, $deny = false, $group = false)
    {
        if ($this->auth->isLogged())
        {
            $login       = $this->auth->getLogin();  // MLogin object
            $isAdmin     = $login->isAdmin(); // Is administrator?
            $rights      = $login->rights[$module]; // user rights
            if( ! $rights )
            {
                $login->setRights( $this->getRights($login->id) );
            }
            $ok = @in_array($action, $login->rights[$module] );

            if(!$ok && $group)
            {
                $groups = $this->getGroupsAllowed($module, $action);
                $ok = sizeof(array_intersect($groups, $login->groups)) > 0;
            }
        }

        if (!$ok && $deny)
        {
        
            $msg = _M('Access Denied') . "<br><br>\n" . 
                   '<center><big><i><font color=red>' . _M('Transaction: ') . "$transaction</font></i></big></center><br><br>\n" .
                   _M('Please inform a valid login/password to access this content.') . "<br>";

            $users = $this->getUsersAllowed($module, $action);

            if ($users)
            {
                $msg .= "<br><br>\n" . _M('Users with access rights') . ":<ul><li>" . implode('<li>', $users) . '</ul>';
            }

            $go = $this->manager->history->back('action'); 
            $error = Prompt::error($msg, $go, $caption, '');
            $error->addButton(_M('   Login   '), $this->manager->getActionURL($this->manager->getConf('login.module'),'login',null,array('return_to'=>urlencode($this->manager->history->top()))), '');
            $this->manager->prompt($error,$deny);
            //$this->manager->error($msg, $go);
        }
        return $ok;
    }

    public function getTransactionRights($transaction, $login)
    {
        $user = $this->manager->getBusinessMAD('user');
        $user->getByLogin($login);
        return $user->getTransactionRights($transaction);
    }

    public function getRights($login)
    {
        $MIOLO  = $this->manager;
        $base   = $MIOLO->getConf('login.ldap.base');
        $filter = "(&(objectClass=mioloUserPermission)(login=$login))";
        
        $MIOLO->auth->connect();

        $sr     = ldap_search($MIOLO->auth->conn, $base, $filter, array('miolomodulename', 'miolomoduleaction') );
        $info   = ldap_get_entries($MIOLO->auth->conn, $sr);

        $rights = array();
        for($i=0; $i<$info['count']; $i++)
        {
            $module = $info[$i]['miolomodulename'][0];
            $rights[$module] = array();
            for($j=0; $j<$info[$i]['miolomoduleaction']['count']; $j++)
            {
                $rights[$module][] = $info[$i]['miolomoduleaction'][$j];
            }
        }
        return $rights;
    }

    public function getGroups($login)
    {
        $user = $this->manager->getBusinessMAD('user');
        $user->getByLogin($login);
        return $user->getArrayGroups();
    }

    public function getUsersAllowed($module, $action = A_ACCESS)
    {
        $MIOLO  = $this->manager;
        $base   = $MIOLO->getConf('login.ldap.base');
        $filter = "(&(objectClass=mioloUserPermission)(mioloModuleName=$module)(mioloModuleAction=$action))";
        $sr     = ldap_search($MIOLO->auth->conn, $base, $filter, array('login') );
        $info   = ldap_get_entries($MIOLO->auth->conn, $sr);

        $users = array();
        for($i=0; $i<$info['count']; $i++)
        {
            $users[] = $info[$i]['login'][0];
        }
        return $users;
    }

    public function getGroupsAllowed($module, $action = A_ACCESS)
    {
        $MIOLO  = $this->manager;
        $base   = $MIOLO->getConf('login.ldap.base');
        $filter = "(&(objectClass=mioloGroupPermission)(mioloModuleName=$module)(mioloModuleAction=$action))";
        $sr     = ldap_search($MIOLO->auth->conn, $base, $filter, array('miologroup') );
        $info   = ldap_get_entries($MIOLO->auth->conn, $sr);

        $groups = array();
        for($i=0; $i<$info['count']; $i++)
        {
            $groups[] = $info[$i]['miologroup'][0];
        }
        return $groups;
    }
}
?>
