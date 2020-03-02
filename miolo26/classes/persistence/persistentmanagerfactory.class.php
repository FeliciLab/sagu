<?php

include ('persistentexception.class.php');

class PersistentManagerFactory
{
    private $classMaps = array();
    private $databases = array();
    private $converters = array();
    private $debug = false;
    private $locked = false;
    private $configLoader;
    private $manager;
    public  $miolo;

    public function __construct()
    {
        $this->miolo = MIOLO::getInstance(); 
    }

    public function setConfigLoader($configLoader)
    {
        if ($configLoader == 'XML')
        {
            $this->configLoader = new XMLConfigLoader($this);
        }
    }

    public function getPersistentManager()
    {
        if (!$this->locked)
        {
            $this->manager = new PersistentManager($this);
            $this->locked = true;
        }
        return $this->manager;
    }

    public function addClassMap($name, $classMap)
    {
        $this->classMaps[$name] = $classMap;
    }

    public function getClassMap($param1, $param2 = NULL)
    {
        $numargs = func_num_args();

        if ($numargs == 1)
        {
            if (is_object($param1))
            {
                $module = $param1->_bmodule;
                $name = $param1->_bclass;
            }
        }
        elseif ($numargs == 2)
        {
            $module = $param1;
            $name = $param2;
        }
        else
        {
            throw new EPersistentManagerFactoryException("getClassmap Error!");
        }

        $className = strtolower($module . $name);
        if (is_null($this->classMaps[$className]))
        {
            $classMap = $this->configLoader->getClassMap($module, $name);
            $this->addClassMap($className, $classMap);
        }
        return $this->classMaps[$className];
    }

    public function getConverter($name)
    {
        return $this->converters[$name];
    }

    public function putConverter($name, $converter)
    {
        $this->converters[$name] = $converter;
    }
}
?>