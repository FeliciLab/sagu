<?php
/**
 *  Formulário herdado pelas grids de pesquisa na avaliação institucional
 *
 * @author Andre Chagas Dias [andre@solis.com.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/07/12
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

/*
 * Class AGrid
 *
 */
class AGrid extends MGrid
{
    public $table;
    
    public function __construct($class, $data, $columns, $href, $pageLength = 15, $index = 0, $name = '', $useSelecteds = true, $useNavigator = true)
    {
        parent::__construct($data, $columns, $href, $pageLength, $index, $name, $useSelecteds, $useNavigator);
        $this->table = DB_PREFIX_TABLE . '_' . str_replace('grdsearch' . DB_PREFIX_TABLE, '', strtolower($class));
        
        if( AVinst::checkTableDependencies($this->table) ) // Verifica a tabela do banco de dados é referenciada por outras
        {
            $this->setRowMethod($this, 'myRowMethod'); // Habilita o myRowMethod para desbilitar o delete da grid quando ouver dependências
        }                
    }
    
    public function myRowMethod($i, $row, $actions, $columns)
    {
        if ( AVinst::checkTableDependencies($this->table,$row[0]) ) // Checa se o registro tem dependencias na base de dados
        {
            $actions[1]->disable(); // Desbilita a ação exluir do registro
        }
        else
        {
            $actions[1]->enable(); // Habilita a ação exluir do registro
        } 
    }
}