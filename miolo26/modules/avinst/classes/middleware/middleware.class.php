<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of iMiddleware
 *
 * @author william
 */

class middleware 
{
    
    const ADIANTI_MIDDLEWARE = 'adianti';
    const SAGU_MIDDLEWARE = 'asagu';
    
    const MIDDLEWARE_NEW = 0;
    const MIDDLEWARE_OPEN = 1;
    const MIDDLEWARE_SETTED = 2;
    const MIDDLEWARE_FETCHED = 3;
    
    protected $calls;
    private $status = self::MIDDLEWARE_NEW;

    //
    // Middleware
    //
    public static function newFromClass($middlewareClass)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/middleware/'.$middlewareClass.'/'.$middlewareClass.'.class.php', 'avinst');
        if (class_exists($middlewareClass))
        {
            return new $middlewareClass();
        }
    }    
    
    //
    // Adiciona uma chamada na classe de comunicação
    //
    public function addCalls($widgetName, $params)
    {
        $this->status = self::MIDDLEWARE_SETTED;
    }
    
    public function fetchCalls()
    {
        return false;
    }
    
    //
    // Chama a função para retornar as chamadas
    //
    public function getCallData()
    {
        if ($this->status == self::MIDDLEWARE_SETTED)
        {
            $this->content = $this->fetchCalls();
            $this->status = self::MIDDLEWARE_FETCHED;
        }
        if ($this->status == self::MIDDLEWARE_FETCHED)
        {
            return $this->content;
        }
        return false;
    }
}
?>
