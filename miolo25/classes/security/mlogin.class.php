<?php

class MLogin
{
    /**
     * Attribute Description.
     */
    public $id; // login at db

    /**
     * Attribute Description.
     */
    //  var $password;   // user password 

    /**
     * Attribute Description.
     */
    public $time; // login time

    /**
     * Attribute Description.
     */
    public $user; // full user name

    /**
     * Attribute Description.
     */
    public $userData; // an array of data chunks associated to module

    /**
     * Attribute Description.
     */
    public $idkey; // iduser at db

    /**
     * Attribute Description.
     */
    public $idsector; // 

    /**
     * Attribute Description.
     */
    public $isAdmin;

    /**
     * Attribute Description.
     */
    public $idsession;

    /**
     * Attribute Description.
     */
    public $rights;

    /**
     * Attribute Description.
     */
    public $groups;

    /**
     * Attribute Description.
     */
    public $idperson;

    /**
     * Attribute Description.
     */
    public $lastAccess;

    public $weakPass;
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $id (tipo) desc
     * @param $password (tipo) desc
     * @param $user (tipo) desc
     * @param $idkey (tipo) desc
     * @param $setor' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($user='', $password='', $name='', $idusuario='', $setor = '')
    {
        if ($user instanceof MBusiness) // a user object
        {
            $this->setUser($user);
        }
        else
        {
            $this->id = $user;
            //      $this->password = $password; 
            $this->user = $name;
            $this->idkey = $idusuario;
            $this->idsector = $setor;
            $this->isAdmin = false;
        }
        $this->time = time();
    }

    public function setUser($user)
    {
        $this->id = $user->login;
        //      $this->password = $$user->password; 
        $this->user = $user->getName();
        $this->idkey = $user->getId();
//        $this->idsector = $user->getIdSector();
//        $this->idperson = $user->getIdPerson();
        $this->setGroups($user->getArrayGroups());
        $this->setRights($user->getRights());
        $this->weakPass = $user->weakPassword();
    }

    public function getUserData($module)
    {
        return $this->userData[$module];
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $module (tipo) desc
     * @param $data (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setUserData($module, $data)
    {
        $this->userData[$module] = $data;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $rights (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setRights($rights)
    {
        $this->rights = $rights;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $groups (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        $this->isAdmin(array_key_exists('ADMIN', $groups));
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $isAdmin (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function isAdmin($isAdmin = NULL)
    {
        if ($isAdmin != NULL)
        {
            $this->isAdmin = $isAdmin;
        }

        return $this->isAdmin;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $idperson (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setIdPerson($idperson)
    {
        $this->idperson = $idperson;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $lastaccess (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;
    }

    public function isModuleAdmin($module)
    {
        $group = 'ADMIN'.strtoupper($module);
        return array_key_exists($group, $this->groups);
    }
}
?>
