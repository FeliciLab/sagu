<?php
/**
 * <--- Copyright 2005-2014 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe Business para BusBiometry
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 25/02/2014
 * 
 **/

class BusinessGnuteca3BusBiometry extends GBusiness
{
    
    //Campos da tabela
    public $personId;
    public $biometry;
    public $key;
    

    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'personId,
                           biometry,
                           key';
        
        $this->id = 'personId';
        $this->columns  = $this->colsNoId;
        $this->tables   = 'basBiometry';
    }


    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getBiometry($personId)
    {
        $data = array($personId);
        $this->clear();
        $this->setColumns(' personId,
                            biometry,
                            key');
        
        $this->setTables(' basBiometry ');
        
        $this->setWhere('personId = ?');
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);
        
        $this->setData($rs[0]);
        return $this;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchBiometry($toObject = FALSE)
    {
        $this->clear();
        
        if ( $v = $this->personId )
        {
            $this->setWhere('personId = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->biometry )
        {
            $this->setWhere('biometry = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->key )
        {
            $this->setWhere('key = ?');
            $data[] = $v;
        }
        
        $this->setColumns(' personId,
                            biometry,
                            key');
        
        $this->setTables('basBiometry');

        $sql = $this->select($data);
        
        $rs  = $this->query($sql, $toObject);
        
        return $rs;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertBiometry()
    {
        $data = array(
            $this->personId,
            $this->biometry,
            $this->key
        );
        
        $this->setColumns(' personId,
                            biometry,
                            key');
        
        $this->setTables('basBiometry');

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateBiometry()
    {
        $data = array(
            $this->personId,
            $this->biometry,
            $this->key
        );
        
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('personId = '.$this->personId);
        
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $moduleConfig (string): Primary key for deletion
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteBiometry($personId)
    {
        $this->clear();
        
        $tables  = 'basBiometry';
        $where   = 'personId = ?';
        $data = array($personId);

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        
        $rs  = $this->execute($sql);
        return $rs;
    }
    
    public function reInserir($pId, $bHash)
    {
        //Coloca o ID para a identificação da pessoa
        $this->busBiometry->personId = $pId; 
        //Coloca o objeto BASE64 para a criação da lista para identificação
        $this->busBiometry->biometry = $bHash;
        //Cria o md5 do objeto, que será útil na sincronização
        $this->busBiometry->key =  md5($bHash);
                
        //Deleta a pessoa, caso a mesma já exista na base
        $this->busBiometry->deleteBiometry($pId);
        //Insere a pessoa novamente
        $this->busBiometry->insertBiometry();
    }
    
    
    
    public function verificaIntegridade($id, $key)
    {
        $this->personId = $id;
        
        $obj = $this->getKey();
        
        if($obj[0] != NULL)
        {
            if($key == $obj[0])
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
        
    }
    
    public function getKey($toObject = FALSE)
    {
        $this->clear();
        
        if ( $v = $this->personId )
        {
            $this->setWhere('personId = ?');
            $data[] = $v;
        }
        
        $this->setColumns(' key');
        
        $this->setTables('basBiometry');

        $sql = $this->select($data);
        
        $rs  = $this->query($sql, $toObject);
        
        return $rs[0];
    }
    
}
?>