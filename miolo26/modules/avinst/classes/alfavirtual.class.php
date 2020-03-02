<?php

/**
 * Classe que acessa as views da alfa_virtual.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 02/02/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class AlfaVirtual
{
    public $db;
    
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $this->db = $MIOLO->getDatabase('alfa_virtual');                
    }
    
    public function searchPessoas( $filters, $returnType = ADataBase::RETURN_ARRAY )
    {
        $sql = 'SELECT ref_pessoa,
                       nome_pessoa,
                       email
                  FROM ava_pessoas';
        
        if( strlen($filters->ref_pessoa) > 0 )
        {
            $where .= ' AND ref_pessoa = ?';
            $args[] = $filters->ref_pessoa;
        }
        
        if( strlen($filters->nome_pessoa) > 0 )
        {
            $where .= ' AND nome_pessoa ILIKE ?';
            $args[] = "%$filters->nome_pessoa%";
        }        
        
        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 4);
        }

        $sql.=' ORDER BY ref_pessoa ';
        $sql = ADatabase::prepare($sql,$args);
        
        if( $returnType  ==  ADatabase::RETURN_SQL )
        {
            return $sql;
        }
        
        $result = $this->db->query($sql)->result;

        if ( $returnType == ADatabase::RETURN_OBJECT )
        {
            $result = AVinst::getArrayOfObjects($result, array('ref_pessoa','nome_pessoa','email'));
        }

        return $result;
    }
}


?>