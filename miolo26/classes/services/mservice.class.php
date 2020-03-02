<?php
/**
 * MService class.
 */
class MService
{
    /**
     * @var MIOLO A shortcut to Miolo.
     */
    protected $manager;

    /**
     * MService constructor.
     */
    public function __construct()
    {
        $this->manager = MIOLO::getInstance();
    }
}
?>
