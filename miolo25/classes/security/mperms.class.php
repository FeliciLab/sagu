<?php
class MPerms extends MService
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

    public function checkAccess($transaction, $access, $deny = false)
    {
        $module = Miolo::getCurrentModule();

        if ( $this->auth->isLogged() )
        {
            $login          = $this->auth->getLogin();  // MLogin object
            $transaction    = strtoupper($transaction); // Transaction name
            $isAdmin        = $login->isAdmin(); // Is administrator?
            $isModuleAdmin  = $login->isModuleAdmin($module);
            $rights         = (int)$login->rights[$transaction]; // user rights
            $rightsInAll    = (int)$login->rights['ALL']; // user rights in all transactions
            $ok = (($rights & $access) == $access) || (($rightsInAll & $access) == $access) || ($isAdmin) || ($isModuleAdmin);
        }

        if ( (! $ok) && $deny )
        {
            $msg   = _M('Access Denied') . "<br><br>\n" . "<center><big><i><font color=red>" . _M('Transaction: ') . "$transaction</font></i></big></center><br><br>\n" . _M('Please inform a valid login/password to access this content.') . "<br>";
            $users = $this->getGroupsAllowed($transaction, $access);
            
            if ( $users )
            {
                $msg .= "<br><br>\n" . _M('Groups with access rights') . ":<ul><li>" . implode('<li>', $users) . '</ul>';
            }
            
            $MIOLO = $this->manager;
            $go    = $MIOLO->history->back('action'); 
            $error = MPrompt::error($msg, $go, $caption, '');
            $error->addButton(_M('   Login   '), $MIOLO->getActionURL( $MIOLO->getConf('login.module'), '_relogin', null, array('return_to'=>urlencode($MIOLO->history->top()))), '' );
            $MIOLO->prompt($error, $deny);
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
        $user = $this->manager->getBusinessMAD('user');
        $user->getByLogin($login);
        return $user->getRights($transaction);
    }

    public function getGroups($login)
    {
        $user = $this->manager->getBusinessMAD('user');
        $user->getByLogin($login);
        return $user->getArrayGroups();
    }

    public function isMemberOf($login, $group)
    {
        $groups = $this->auth->getLogin()->groups;
        $ok = $groups[strtoupper($group)] || $groups['ADMIN'];
        return $ok;
    }

    public function isAdmin()
    {
        return $this->auth->getLogin()->isAdmin();
    }

    public function getUsersAllowed($trans, $action = A_ACCESS)
    {
        $transaction = $this->manager->getBusinessMAD('transaction');
        $transaction->getByName($trans);
        return $transaction->getUsersAllowed($action);
    }

    public function getGroupsAllowed($trans, $action = A_ACCESS)
    {
        $transaction = $this->manager->getBusinessMAD('transaction');
        $transaction->getByName($trans);
        return $transaction->getGroupsAllowed($action);
    }
}
?>
