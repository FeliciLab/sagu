<?php
interface IUser
{
    public function getId();
    public function getName();
    public function getById($id);
    public function getByLogin($login);
    public function getByLoginPass($login,$pass);
    public function getRights();
    public function getTransactionRights($transaction);
    public function getArrayGroups();
}
?>