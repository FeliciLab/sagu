<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 *
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class BusinessGnuteca3BusDinamicReport extends GBusiness
{
    public $table;
    public $columns;
    public $filters;
    public $orderBy;
    public $orderType;
    
    function __construct()
    {
        parent::__construct();
    }
    
    public function executeDinamicReport()
    {
        $columns = implode(', ' , $this->columns);
        
        $sql = "SELECT $columns from $this->table \n";
        
        $filters = $this->filters;
        
        if ( is_array( $filters ) )
        {
            $sql .= " WHERE \n";
            
            foreach ( $filters as $line => $filter )
            {
                if ( $line > 0 )
                {
                    $sql .= ' '. $filter->type . ' ';
                }
                
                $sql .= $filter->column . ' ' . $filter->condition. " '". $filter->filter. "'\n";
            }
        }
        
        if ( $this->orderBy )
        {
            $sql .= 'ORDER BY '. $this->orderBy . ' ' . $this->orderType;
        }
       
        return $this->query($sql);
    }
}
?>