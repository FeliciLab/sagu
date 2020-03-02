<?php
class MState
{
    public $stateVars;
    public $viewState = '';
    private $idElement;

    public function __construct($formid)
    {
        $this->idElement = $formid.'__VIEWSTATE';
        $this->stateVars = array();
        $this->viewState = '';
    }

    public function set($var, $value, $component_name = '')
    {
        if (!$component_name)
        {
            $this->stateVars[$var] = $value;
        }
        else
        {
            $this->stateVars[$component_name][$var] = $value;
        }
    }

    public function get($var, $component_name = '')
    {

        if (!$component_name)
        {
            return $this->stateVars[$var];
        }
        else
        {
            return $this->stateVars[$component_name][$var];
        }
    }

    public function loadViewState()
    {
        $this->viewState = MIOLO::_REQUEST($this->idElement);

        if ($this->viewState)
        {
            $s = base64_decode($this->viewState);
            $this->stateVars = unserialize($s);
        }
    }

    public function saveViewState()
    {
        if ($this->stateVars)
        {
            $s = serialize($this->stateVars);
            $this->viewState = base64_encode($s);
        }
    }

    public function getViewState()
    {
        return $this->viewState;
    }

    public function getIdElement()
    {
        return $this->idElement;
    }
}
?>