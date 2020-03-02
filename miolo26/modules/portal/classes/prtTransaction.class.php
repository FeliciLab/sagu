<?php

class prtTransaction
{
    
    public function __construct()
    {
    }
    
    public function obterTransacoesDeDocumentosDoPortal($perfil = NULL)
    {
        $sql = new MSQL();
        $sql->setTables('miolo_transaction');
        $sql->setColumns('idtransaction, m_transaction, nametransaction, idmodule, parentm_transaction, action');
        $sql->setWhere('idmodule = ?');
        $sql->addParameter('portal');
        $sql->setWhere("action ilike '%generateReport%'");
        
        if ( strlen($perfil) > 0 )
        {
            $sql->setWhere("parentm_transaction ilike '%{$filtros->prefil}%'");
        }

        $return = bBaseDeDados::consultar($sql);

        return $return;
    }
}

?>
