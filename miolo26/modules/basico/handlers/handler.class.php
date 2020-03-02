<?php
class HandlerBasico extends Handler
{
    function init()
    {
        parent::init();
        $this->manager->Trace(__METHOD__);
    }
    
    public function dispatch($handler)
    {           
        //if(!$this->manager->getLogin()->id)
        //if(!prtUsuario::obtemUsuarioLogado())
        if(!$this->manager->getLogin()->id)
        {
            $return = parent::dispatch('login');
        }
        else
        {
            $return = parent::dispatch($handler);
        }
        
        return $return;
    }
}
?>
