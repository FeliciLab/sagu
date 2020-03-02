<?php
interface ITransaction
{
    public function getById($id);
    public function getByName($transaction);
    public function getUsersAllowed($action = A_ACCESS);
    public function getGroupsAllowed($action = A_ACCESS);
}
?>