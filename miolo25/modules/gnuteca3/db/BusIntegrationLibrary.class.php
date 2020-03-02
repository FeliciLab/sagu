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
 * Classe referente ao Cadastro de unidade de Biblioteca
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 23/04/2014
 *
 **/


class BusinessGnuteca3BusIntegrationLibrary extends GBusiness
{
    public $integrationLibraryId;
    public $integrationServerId;
    public $libraryUnitId;
    
    function __construct()
    {
        $this->colsNoId = 'integrationServerId, 
                           libraryUnitId';
        
        $this->id = 'integrationLibraryId';
        $this->columns  = 'integrationLibraryId, ' . $this->colsNoId;
        $this->tables   = 'gtcIntegrationLibrary';
        
        parent::__construct();

    }

    public function insertIntegrationLibrary($intServer, $libUnit)
    {
        //Verifica se há informação para ser gravada
        if($intServer && $libUnit)
        {
            //Instancia objeto data, com os dados do formulário
            $data = array($this->integrationServerId = $intServer,
                          $this->libraryUnitId = $libUnit
                          );

            $this->clear();


            $this->setColumns('integrationServerId, 
                                   libraryUnitId');

            $this->setTables('gtcIntegrationLibrary');  

            //$sql = $this->insert($data) . ' RETURNING *';
            $sql = $this->insert($data);

            $rs  = $this->query($sql);

            return $rs;
        }
        return false;
    }

    public function updateIntegrationLibrary()
    {
        $data = array(
            $this->integrationLibraryId,
            $this->integrationServerId,
            $this->libraryUnitId
        );
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('integrationLibraryId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }

    public function getIntegrationLibrary($integrationLibraryId)
    {
        $data = array($integrationLibraryId);
        $this->clear();
        $this->setColumns('integrationLibraryId,
                           integrationServerId, 
                           libraryUnitId');
        
        $this->setTables('gtcIntegrationLibrary');
        
        $this->setWhere('integrationLibraryId = ?');
        
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);
        
        $this->setData($rs[0]);
        
        return $this;
    }


    public function searchIntegrationLibrary($toObject = FALSE)
    {
        $this->clear();

        if ($this->integrationLibraryId)
        {
            $this->setWhere('integrationLibraryId = ?');
            $data[] = $this->integrationLibraryId;
        }
        
        if ($this->integrationServerId)
        {
            $this->setWhere('integrationServerId = ?');
            $data[] = $this->integrationServerId;
        }

        if ($this->libraryUnitId)
        {
            $this->setWhere('libraryUnitId = ?');
            $data[] = $this->libraryUnitId;
        }
        

        $this->setColumns('integrationLibraryId,
                           integrationServerId,
                           libraryUnitId');
        
        $this->setTables('gtcIntegrationLibrary');
        $this->setOrderBy('integrationLibraryId');
        $sql = $this->select($data);
        
        return $this->query($sql, ($toObject ? TRUE : FALSE));
    }


    public function listIntegrationLibrary()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function deleteIntegrationLibrary($integrationLibraryId)
    {
        $this->clear();
        $tables  = 'gtcIntegrationLibrary';
        $where   = 'integrationLibraryId = ?';
        $data = array($integrationLibraryId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        
        return $rs;
    }
}
?>
