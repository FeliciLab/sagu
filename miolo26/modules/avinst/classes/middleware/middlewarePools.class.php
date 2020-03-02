<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Esta classe serve para guardar uma estrutura de informações para serem enviadas 
 * aos middlewares, como uma "pilha" de instruções a serem executadas para o middleware
 *
 * @author william
 */
class middlewarePools
{
    const ADIANTI_POOL = 'adianti';
    
    const MIDDLEWARE_POOL_OPEN = 1;
    const MIDDLEWARE_POOL_SETTED = 2;
    const MIDDLEWARE_POOL_FETCHED = 3;
    
    private $poolData;
    private $status;
    private $poolType;
    
    //
    // Pool to construct middleare
    //
    public function __construct($poolType)
    {
        
        $this->poolData = array();
        $this->poolType = $poolType;
        $this->status = self::MIDDLEWARE_POOL_OPEN;
    }
    
    //
    // Adiciona ao poolData o widget
    //
    public function addPool($widget, $poolData)
    {
        if (!is_array($this->poolData[$widget]))
        {
            $this->poolData[$widget] = array();
        }
        $this->poolData[$widget] = array_merge($this->poolData[$widget], $poolData);
        $this->status = self::MIDDLEWARE_POOL_SETTED;
    }
    
    //
    // Processa o pool
    //
    public function processPool()
    {
        if ($this->status == self::MIDDLEWARE_POOL_SETTED)
        {
            if (is_array($this->poolData))
            {
                foreach ($this->poolData as $widget => $poolWidget)
                {
                    if (is_array($poolWidget))
                    {
                        foreach ($poolWidget as $poolName => $poolParams)
                        {
                            $localPool[$poolName] = $poolParams;
                        }
                        unset($pos);
                        unset($poolParams);
                    }
                }
                unset($widget);
                unset($poolWidget);
            }
            
            // Aqui, tenta executar a chamada para o conjunto de dados
            if (is_array($this->poolData))
            {
                $MIOLO = MIOLO::getInstance();
                $poolClassName = $this->poolType;
                $MIOLO->uses('classes/middleware/'.$poolClassName.'/'.$poolClassName.'.class.php', 'avinst');
                $poolClass = new $poolClassName();
                $poolClass->setPool($localPool);
                $data = $poolClass->processPools($localPool);
                if (is_array($data))
                {
                    $returnData = array();
                    foreach ($this->poolData as $widget => $poolWidget)
                    {
                        if (is_array($poolWidget))
                        {
                            foreach ($poolWidget as $poolName => $poolParams)
                            {
                                $returnWidget[$widget][$poolName] = $data[$poolName];
                            }
                        }
                    }
                    return $returnWidget;
                }
            }
        }
        return false;
    }
}

?>
