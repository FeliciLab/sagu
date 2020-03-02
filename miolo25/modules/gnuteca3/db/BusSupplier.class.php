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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 01/12/2008
 *
 **/
class BusinessGnuteca3BusSupplier extends GBusiness
{
    public $supplierId;
    public $name;
    public $companyName;

    public  $supplierIdS,
            $nameS,
            $companyNameS,
            $cnpjS,
            $locationS,
            $neighborhoodS,
            $beginDateS,
            $endDateS,
            $observationS,
            $bankDepositS,
            $contactS,
            $tab,
            $cityS;

    public $table;
    public $busSupplierTypeAndLocation;
    public $busMaterial;

    public function __construct()
    {
        $this->table = 'gtcSupplier';
        $pkeys = 'supplierId';
        $cols  = 'name,
                  companyName';

        parent::__construct($this->table, $pkeys, $cols);

        $this->busSupplierTypeAndLocation = $this->MIOLO->getBusiness($this->module, "BusSupplierTypeAndLocation");
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, "BusMaterial");
    }

    public function getSupplier($supplierId)
    {
    	return $this->autoGet($supplierId);
    }

    public function getSupplierCompleteData($supplierId)
    {
        $obj->supplier = $this->getSupplier($supplierId);
        $obj->TypeLocation = $this->busSupplierTypeAndLocation->getSupplierTypeAndLocationValueForm($supplierId);

        return $obj;
    }

    /**
     * Procura um fornecedor
     *
     */
    public function searchSupplier()
    {
        $busSupplierTypeAndLocation = $this->MIOLO->getBusiness($this->module, "BusSupplierTypeAndLocation");

        parent::clear();
        parent::setColumns("A.supplierid,
                            A.name,
                            B.type,
                            B.name,
                            B.companyname,
                            B.cnpj,
                            B.location,
                            B.neighborhood,
                            B.city,
                            B.zipCode,
                            B.phone,
                            B.fax,
                            B.alternativePhone,
                            B.email,
                            B.alternativeEmail,
                            B.contact,
                            B.site,
                            B.observation,
                            B.bankDeposit,
                            B.date");

        parent::setTables("$this->table A INNER JOIN $busSupplierTypeAndLocation->tables B USING (supplierid)");
        parent::setOrderBy("supplierId");

        $args = null;
        
        if($supplierId = MIOLO::_REQUEST('supplierIdS'))
        {
            $this->setWhere("A.supplierid = ?");
            $args[] = $supplierId;
        }
        if($this->nameS)
        {
            $this->setWhere("(lower(A.name) like lower(?) OR lower(B.name) like lower(?))");
            $args[] = "%$this->nameS%";
            $args[] = "%$this->nameS%";
        }
        if($this->companyNameS)
        {
            $this->setWhere("(lower(A.companyName) like lower(?) OR lower(B.companyName) like lower(?))");
            $args[] = "%$this->companyNameS%";
            $args[] = "%$this->companyNameS%";
        }
        if($this->cnpjS)
        {
            $this->setWhere("lower(B.cnpj) like lower(?)");
            $args[] = "%$this->cnpjS%";
        }
        if($this->locationS)
        {
            $this->setWhere("lower(B.location) like lower(?)");
            $args[] = "%$this->locationS%";
        }
        if( $this->neighborhoodS)
        {
            $this->setWhere("lower(B.neighborhood) like lower(?)");
            $args[] = "%$this->neighborhoodS%";
        }
        if($this->contactS)
        {
            $this->setWhere("lower(B.contact) like lower(?)");
            $args[] = "%$this->contactS%";
        }
        if ( $this->observationS )
        {
            $this->setWhere("lower(B.observation) like lower(? || '%')");
            $args[] = $this->observationS;
        }
        if( $this->bankDepositS)
        {
            $this->setWhere("lower(B.bankDeposit) like lower(?)");
            $args[] = "%$this->bankDepositS%";
        }
        if ( $this->beginDateS)
        {
            $this->setWhere("date(B.date) >= ?");
            $args[] = "%$this->beginDateS%";
        }
        if ($this->endDateS)
        {
            $this->setWhere("date(B.date) <= ?");
            $args[] = "%$this->endDateS%";
        }

        if( $this->cityS )
        {
            $this->setWhere("lower(B.city) like lower(?)");
            $args[] = $this->cityS.'%';
        }

        $sql = parent::select($args);
        $res = parent::query($sql);

        if(!$res)
        {
            return false;
        }

        $resul = array();
        
        foreach ($res as $i => $v)
        {
            $resul[$v[0]][0] = $v[0];
            $resul[$v[0]][1] = $v[1];
            
            $index = array_search($v[2], array(2 => 'c', 3 => 'p', 4 => 'd'));
            $resul[$v[0]][$index] = implode("||", $v);
        }

        //refaz o índice do array
        $result = array();
        if ( is_array($result) )
        {
            $key = 0;
            foreach( $resul as $i=> $v)
            {
                $result[$key] = $v;
                $key++;
            }
        }

        return $result;
    }

    /**
     * Insert supplier
     *
     * @return boolean
     */
    public function insertSupplier()
    {
        $this->supplierId = $this->getNextSupplierId();
        $ok = parent::autoInsert();
        if ($ok)
        {
            foreach ($this->tab as $type => $fields)
            {
                if(!is_array($fields))
                {
                    continue;
                }
                $this->busSupplierTypeAndLocation->clean();
                foreach ($fields as $fieldsName => $value)
                {
                    $this->busSupplierTypeAndLocation->supplierId   = $this->supplierId;
                    $this->busSupplierTypeAndLocation->type         = strtolower($type);
                    $this->busSupplierTypeAndLocation->$fieldsName  = $value;
                }
                $this->busSupplierTypeAndLocation->insertSupplierTypeAndLocation();
            }
            return true;
        }
        return false;
    }



    /**
     * UPDATE supplier
     *
     * @return boolean
     */
    public function updateSupplier()
    {
        $ok = parent::autoUpdate();
        if ($ok)
        {
            foreach ($this->tab as $type => $fields)
            {
                if(!is_array($fields))
                {
                    continue;
                }
                $this->busSupplierTypeAndLocation->clean();
                foreach ($fields as $fieldsName => $value)
                {
                    $this->busSupplierTypeAndLocation->supplierId   = $this->supplierId;
                    $this->busSupplierTypeAndLocation->type         = strtolower($type);
                    $this->busSupplierTypeAndLocation->$fieldsName  = $value;
                }
                $this->busSupplierTypeAndLocation->updateSupplierTypeAndLocation();
            }
            return true;
        }
        return false;
    }




    public function deleteSupplier($supplierId)
    {
        //Variavel para verificar se não há materiais com fornecedores
        $possuiFornecedores = 0;
        
        //Verifica se não há fornecedores na gtcmaterial, campo 947.a
        $this->busMaterial->fieldid = '947';
        $this->busMaterial->subfieldid = 'a';
        $this->busMaterial->content = $supplierId;
        
        $resp = $this->busMaterial->searchMaterial();
        foreach($resp as $respFornec)
        {
            if($respFornec[6] == $supplierId)
            {
                $possuiFornecedores++;
                continue;
            }
        }
        
        if($possuiFornecedores > 0)
        {
            throw new Exception("Este fornecedor está ligado à $possuiFornecedores materiais.\nNão é possível exclui-lo.");
        }

        $data = array($supplierId);
        
        $this->busSupplierTypeAndLocation->deleteSupplierTypeAndLocation($supplierId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('supplierId = ?');
        $sql = $this->delete($data);
        
        try
        {
            $rs  = $this->execute($sql);
        }
        catch (Exception $e)
        {
            throw new Exception("Falha ao remover fornecedor.");
        }
        
        return $rs;        
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getCurrentId()
    {
        parent::clear();

        $query = parent::query("SELECT currval('seq_gtcsupplier_supplierid')");

        if(!$query)
        {
            return 0;
        }

        $this->supplierId = $query[0][0];
        return $query[0][0];
    }


    /**
     * Limpa os atributos da classe
     */
    public function clean()
    {
        $this->supplierId=
        $this->name=
        $this->companyName=
        $this->supplierIdS=
        $this->nameS=
        $this->companyNameS= null;
    }

    /**
    * Return the next value to be inserted.
    * If you want a cross Database function you need treat this in other way.
    *
    */
    public function getNextSupplierId()
    {
        $sql = "SELECT NEXTVAL('seq_gtcsupplier_supplierid');";
        $rs  = $this->query($sql);
        return $rs[0][0];
    }
}
?>
