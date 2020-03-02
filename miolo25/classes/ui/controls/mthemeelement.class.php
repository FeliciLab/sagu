<?php

class MThemeElement extends MContainerControl
{
    public function __construct($name = NULL, $content = '', $class = '')
    {
        parent::__construct($name);
        $this->setInner($content);
        $this->setClass($class);
    }

    public function clear($halted = false)
    {
        if (!$halted)
        {
            $this->clearControls();
        }
    }

    public function get($key = 0)
    {
        return $this->getElement($key);
    }

    public function set($element, $id = '', $key = NULL)
    {
        $this->setElement($element, $key, 's');
        $this->setId($id);
    }

    public function insert($element, $key = NULL, $halted = false)
    {
        if (!$halted)
        {
            $this->setElement($element, $key, 'i');
        }
    }

    public function append($element, $key = NULL, $halted = false)
    {
        if (!$halted)
        {
            $this->setElement($element, $key, 'a');
        }
    }

    public function count()
    {
        return $this->controlsCount;
    }

    public function setElement($element, $key = NULL, $op = 's')
    {
        if (is_array($element))
        {
            $this->clear();

            foreach ($element as $e)
            {
                $this->setElement($e, $key, 'a');
            }
        }
        else
        {
            switch ($op)
                {
                case 's':
                    $this->clear();

                    $this->addControl($element);
                    break;

                case 'a':
                    $this->addControl($element);
                    break;

                case 'i':
                    $this->insertControl($element);
                    break;
                }
        }
    }

    public function getElement($key)
    {
        return $this->getControl($key);
    }

    public function getElementById($key)
    {
        return $this->getControlById($key);
    }

    public function space($space = '20px')
    {
        $this->append(new MSpacer($space));
    }

    public function generateInner()
    {
        $this->setId($this->getId());
        $this->setClass($this->getClass());
        $controls = $this->getControls();
        $this->inner = $this->painter->generateToString($controls);
        return $this->inner;
    }
}
?>