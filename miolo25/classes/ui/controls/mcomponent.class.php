<?php
abstract class MComponent
{
    /**
     * @var MIOLO A shortcut to Miolo.
     */
    public $manager;

    /**
     * @var MPage Page handler. 
     */
    public $page;

    /**
     * @var MComponent The component instance.
     */
    public $owner;

    /**
     * @var string Component name.
     */
    public $name;

    /**
     * @var string Component styke class.
     */
    public $className;

    /**
     * Component constructor.
     *
     * @param string $name Component name.
     */
    public function __construct($name=NULL)
    {
        $this->manager = MIOLO::getInstance();
        $this->page = $this->manager->getPage();
        $this->className = strtolower(get_class($this));
        $this->name = $name;
        $this->owner = $this;
    }

    /**
     * @param string $name Set component name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string Get component name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a manager (MIOLO) instance.
     *
     * @return MIOLO instance
     */
    public function getManager()
    {
        return $this->manager;
    }
}
?>