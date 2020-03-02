<?php

class MPermsAvinst extends MPerms
{
    private $auth;
    public  $perms;

    public function __construct()
    {
        parent::__construct();

        $this->auth  = $this->manager->getAuth();
        $this->perms = array( A_ACCESS  => "READ",
                              A_INSERT  => "WRITE",
                              A_EXECUTE => "ADMIN" );
    }

    public function checkAccess($transaction, $perm, $deny = false, $group = false)
    {
        if ( $this->auth->isLogged() )
        {
            $login   = $this->auth->getLogin();  // MLogin object
            $isAdmin = $login->isAdmin(); // Is administrator?

            $ok = @in_array($perm, (array) $this->getRights($login->id, $transaction));

            if( ! $ok && $group )
            {
                $groups = $this->getGroupsAllowed($transaction, $perm);
                $ok     = sizeof( array_intersect($groups, $login->groups) ) > 0;
            }
        }

        if ( ! $ok && $deny )
        {
            $msg = _M('Access Denied') . "<br><br>\n" .
                      '<center><big><i><font color=red>' . _M('Transaction: ') . "$transaction ($perm)</font></i></big></center><br><br>\n" .
                   _M('Please inform a valid login/password to access this content.') . "<br>";

            $users  = $this->getUsersAllowed($transaction, $perm);
            $groups = $this->getGroupsAllowed($transaction, $perm);

            if ($users)
            {
                $msg .= "<br/>\n" . _M('Users with access rights') . ":<ul><li>" . implode('<li>', $users) . '</ul>';
            }
            if ($groups)
            {
                $msg .= "<br/>\n" . _M('Groups with access rights') . ":<ul><li>" . implode('<li>', $groups) . '</ul>';
            }

            $go    = $this->manager->history->back('action');
            $error = Prompt::error($msg, $go, $caption, '');
            $error->addButton( _M('   Login   '), $this->manager->getActionURL($this->manager->getConf('login.module'),'login',null,array('return_to'=>urlencode($this->manager->history->top()))), '');

            $this->manager->prompt($error,$deny);
        }

        return $ok;
    }

    public function getTransactionRights($transaction, $login)
    {
        $user = $this->manager->getBusinessMAD('user');
        $user->getByLogin($login);

        return $user->getTransactionRights($transaction);
    }

    public function getRights($login, $transaction)
    {
        $transaction = strtolower($transaction);
        $objLogin = $this->auth->getLogin(); // MLogin object
        $this->manager->loadMADConf();
        $db = $this->manager->getDatabase('admin');

        $rights = $objLogin->rights[$transaction];
        if ( !$rights )
        {
            $sql = "SELECT t.m_transaction, a.rights FROM miolo_user u, miolo_groupuser g, miolo_access a, miolo_transaction t WHERE u.iduser = g.iduser AND g.idgroup = a.idgroup AND a.idtransaction = t.idtransaction AND u.login = '$login'";

            $result = $db->query($sql)->result;

            $allRights = array();
            if ( is_array($result) )
            {
                foreach ( $result as $r )
                {
                    $allRights[strtolower($r[0])][] = $r[1];
                }
            }
            $objLogin->setRights($allRights);
            $rights = $allRights[$transaction];
        }
        return $rights;
    }

    public function getGroups($login)
    {
        $user = $this->manager->getBusinessMAD('user');
        $user->getByLogin($login);

        return $user->getArrayGroups();
    }

    public function getUsersAllowed($transaction, $perm = A_ACCESS)
    {
        $this->manager->loadMADConf();

        $db  = $this->manager->getDatabase('admin');
        $sql = "select distinct u.login from miolo_user u, miolo_groupuser g, miolo_access a, miolo_transaction t where u.iduser = g.iduser and g.idgroup = a.idgroup and a.idtransaction = t.idtransaction and lower(t.m_transaction) = '" . strtolower($transaction) ."' and a.rights='$perm'";

        $result = $db->query($sql)->result;
        $users  = array();

        if ( $result )
        {
            foreach($result as $user)
            {
                $users[] = $user[0];
            }
        }

        return $users;
    }

    public function getGroupsAllowed($transaction, $perm = A_ACCESS)
    {
        $this->manager->loadMADConf();

        $db  = $this->manager->getDatabase('admin');
        $sql = "select g.m_group from miolo_group g, miolo_access a, miolo_transaction t where g.idgroup = a.idgroup and a.idtransaction = t.idtransaction and lower(t.m_transaction) = '" . strtolower($transaction) ."' and a.rights='$perm'";

        $result = $db->query($sql)->result;
        $groups = array();

        if ( $result )
        {
            foreach($result as $group)
            {
                $groups[] = $group[0];
            }
        }

        return $groups;
    }
}
?>
