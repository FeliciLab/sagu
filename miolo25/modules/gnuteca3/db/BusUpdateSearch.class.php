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
 * This file handles update material
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 24/11/2010
 *
 **/
class BusinessGnuteca3BusUpdateSearch extends GBusiness
{
    public $MIOLO;
    public $module;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
    }


    /**
     * Método público que busca a data da última atualização da tabela de pesquisa
     */
    public function getLastUpdateSearch()
    {
    	$this->clear();
    	$this->setTables('gtcSearchTableUpdateControl');
    	$this->setColumns('lastUpdate');
    	
    	$sql = $this->select();
        $rs  = $this->query($sql, true);
    
        return $rs[0];
    }
    
    /**
     * Atualiza a tabela de pesquisa
     */
    public function updateSearch()
    {
    	$rs = $this->query("SELECT gtcfnc_updatesearchmaterialviewtablebool()");
    	
    	return $rs[0][0] == DB_TRUE ? true : false;
    }
    
    /**
     * Método público para atualizar registro de material na tabela de pesquisa.
     * 
     * @param int $controlNumber Número de controle.
     * @param int $controlNumberFather Número de controle do pai.
     * @return boolean Positivo caso não tenha problemas no processo.
     */
    public function updateSearchForMaterial($controlNumber, $controlNumberFather=NULL)
    {
        $table = 'gtcSearchMaterialView';
        $process = array();
        
        $controlNumbers = array($controlNumber);
        
        // Inclui número de controle do pai se tiver.
        if ( $controlNumberFather )
        {
            $controlNumbers[] = $controlNumberFather;
        }
        
        // Atualiza o conteúdo da tabela de busca do número de controle e do número de controle do pai.
        foreach ( $controlNumbers as $controlNumber )
        {
            $arguments = array($controlNumber);

            // Apaga registro na tabela de pesquisa.
            $this->clear();
            $this->setTables($table);
            $this->setWhere('controlNumber = ?');
            $process[] = $this->execute( $this->delete($arguments) );

            // Insere registro na tabela de pesquisa.
            $this->clear();
            $this->setTables('searchMaterialView');
            $this->setColumns('*');
            $this->setWhere('controlNumber = ?');
            $process[] = $this->execute($sql = 'INSERT INTO gtcSearchMaterialView ' . $this->select($arguments) );
        }
        
        return !in_array(false, $process);
    }
    
}
?>
