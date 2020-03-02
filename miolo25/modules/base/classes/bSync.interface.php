<?php

/**
 * Interface de arquivos de sincronização
 */
interface bSync 
{
    /**
     * Efetua a sincroniazação
     */
    public function syncronize();
    
    /**
     * Retorna um array com os arquivos de sincronização de base do módulo informado.
     * @param string $module
     * @return array 
     */
    public static function listSyncFiles($module);
}
?>