<?php
class MAuthDbMD5 extends MAuth
{
    public function authenticate($userId, $challenge, $response)
    {
        $this->manager->logMessage("[LOGIN] Authenticating $user MD5");
        $login = NULL;

        try
        {
            $user = $this->manager->getBusinessMAD('user');
            $user->getByLogin($userId);

            if ($user->validatePasswordMD5($challenge,$response))
            {
                $login = new MLogin($user);

                if ($this->manager->getConf("options.dbsession"))
                {
                    $session = $this->manager->getBusinessMAD('session');
                    $session->lastAccess($login);
                    $session->registerIn($login);
                }

                $this->setLogin($login);
                $this->manager->logMessage("[LOGIN] Authenticated $userId MD5");
            }
            else
            {
                $this->manager->logMessage("[LOGIN] $userId NOT Authenticated MD5");
            }
        }
        catch( Exception $e )
        {
            $this->manager->logMessage("[LOGIN] $userId NOT Authenticated MD5 - " . $e->getMessage());
        }

        return $login;
    }
}
?>