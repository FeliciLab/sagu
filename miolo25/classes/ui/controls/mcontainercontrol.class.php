<?php

class MContainerControl extends MDiv
{
    public $controls;
    public $controlsId;


    public function __construct( $name = NULL )
    {
        parent::__construct( $name );
        $this->controls   = new MObjectList();
        $this->controlsId = new MObjectList();
    }

    /** 
     * The clone method.
     * It is used to clone controls, avoiding references to same controls.
     */
    public function __clone()
    {
        parent::__clone();
        $this->controls   = clone $this->controls; 
        $this->controlsId = clone $this->controlsId; 
    }

    //
    // Controls
    //
    protected function _AddControl($control, $pos = 0, $op = 'add')
    {
        if(is_array($control))
        {
            foreach($control as $c)
            {
                $this->_AddControl($c);
            }
        }
        elseif ( $control instanceof MControl )
        {
            if ( $op == 'add' )
            {
                $this->controlsId->add($control, $control->getId() );
                $this->controls->add($control);
            }
            elseif ( $op == 'ins' )
            {
                $this->controlsId->add($control, $control->getId() );
                $this->controls->insert($control, $pos);
            }
            elseif ( $op == 'set' )
            {
                $this->controlsId->set( $control->getId(), $control );
                $this->controls->set($pos, $control);
            }

            $control->parent = $this;
        }
        elseif ( ! is_null($control) )
        {
            if ( ! is_object($control) )
            {
                throw new EControlException(
                          _M("Using non-object with _AddControl;<br/>type: " . gettype($control) . ';<br/>value: ' . $control
                              . ';<br/>Try using MLabel control instead'));
            }
            else
            {
                throw new EControlException(_M('Using non-control with _AddControl; class: ' . get_class($control).'; name: '.$control->name.'; id: '.$control->id));
            }
        }
    }

    public function addControl($control)
    {
        $this->_AddControl($control);
    }

    public function insertControl($control, $pos = 0)
    {
        $this->_AddControl($control, $pos, 'ins');
    }

    public function setControl($control, $pos = 0)
    {
        $this->_AddControl($control, $pos, 'set');
    }

    public function setControls($controls)
    {
        $this->clearControls();

        foreach ( $controls as $c )
            $this->addControl($c);
    }

    public function getControls()
    {
        return $this->controls->items;
    }

    public function getControl($pos)
    {
        return $this->controls->get($pos);
    }

    public function getControlById($id)
    {
        return $this->controlsId->get($id);
    }

    public function findControlById($id)
    {
        $k = NULL;
        $controls = $this->controlsId->items;
        foreach ( $controls as $c )
        {
            if ( $c->id == $id )
            {
                return $c;
            }

            elseif ( $c instanceof MContainerControl )
            {
                if ( ($k = $c->findControlById($id)) != NULL )
                {
                    break;
                }
            }
        }
        return $k;
    }

    public function setControlById($control, $id)
    {
        $this->controlsId->set($id, $control);
    }

    public function clearControls()
    {
        $this->controls->clear();
        $this->controlsId->clear();
    }

    public function generateInner()
    {
        if ( $this->inner == '' )
        {
            if ( $this->controls->hasItems() )
            {
                $this->inner = $this->controls->items;
            }
        }
    }

}

?>