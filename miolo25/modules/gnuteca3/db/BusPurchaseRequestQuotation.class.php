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
 * This file handles the connection and actions for gtcLibraryUnit table
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 01/06/2011
 *
 **/
class BusinessGnuteca3BusPurchaseRequestQuotation extends GBusiness
{
    public $MIOLO;
    public $module;
    public $colsNoId;

    public $purchaseRequestId,
           $supplierId,
           $value,
           $observation,
           $insertData,
           $removeData;

    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->tables   = 'gtcPurchaseRequestQuotation';
        $this->id       = 'purchaseRequestId, 
                           supplierId';
        $this->colsNoId = 'value,
                           observation';
        
        $this->columns  = $this->id . ',' . $this->colsNoId;
    }
    

    /**
     * Obtém a quotação da solicitação de compra
     *
     * @param (int) chave primária do registro
     *
     * @return (object) contém o registro obtido
     *
     **/
    public function getPurchaseRequestQuotation($purchaseRequestId, $supplierId)
    {
        $this->clear();
        if ( $purchaseRequestId && $supplierId )
        {
            $data = array($purchaseRequestId, $supplierId);
            $this->setWhere('purchaseRequestId' . ' = ?');
            $this->setWhere('supplierId'. ' = ?');
        }
        
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject=true);

        $data = new stdClass();
        if ($rs[0])
        {
			$data = $rs[0];
        }

        $this->setData($data);
        
        return $data;
	}

    /**
     * Busca as quotações de solicitações de compras
     *
     * @param (string) coluna de ordenação dos registros
     *
     * @return (array) contendo os dados que vão compor a grid
     **/
    public function searchPurchaseRequestQuotation($orderBy = 'purchaseRequestId', $toObject = false)
    {
        $this->clear();

        if ( $this->purchaseRequestId )
        {
            $this->setWhere('purchaseRequestId = ?' );
            $data[] = $this->purchaseRequestId;
        }
        
        if ( $this->supplierId )
        {
            $this->setWhere('A.supplierId = ?');
            $data[] = $this->supplierId;
        }
        
        if ( $this->value )
        {
            $this->setWhere('value = ?');
            $data[] = $this->value;
        }
        
        if ( $this->observation )
        {
            $this->setWhere('lower(observation) like (lower(?))');
            $data[] = $this->observation;
        }

        $this->setColumns($this->columns);
    
        $this->setTables($this->tables);
        
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, $toObject);

        return $rs;
    }

    /**
     * Insere uma nova quotação de solicitação de compra
     *
     * @return True se funcionou
     *
     **/
    public function insertPurchaseRequestQuotation()
    {
        $this->clear();
      
        $this->setTables($this->tables);
        $this->setColumns($this->id . ',' . $this->colsNoId);
        $sql = $this->insert( $this->associateData($this->columns) );

        return $this->execute($sql);
    }

    /**
     * Atualiza o registro de quotação de solicitação de compra
     *
     * @return (boolean): True se teve sucesso
     *
     **/
    public function updatePurchaseRequestQuotation()
    {
        if ( $this->removeData )
        {
            return $this->deletePurchaseRequestQuotation($this->purchaseRequestId, $this->supplierId);
        }
        elseif ( $this->insertData )
        {
            return $this->insertPurchaseRequestQuotation();
        }
        else
        {
            $data = $this->associateData( $this->colsNoId . ',' . $this->id );

            $this->clear();
            $this->setWhere('purchaseRequestId = ?');
            $this->setWhere('supplierId = ?');
            $this->setColumns($this->colsNoId);
            $this->setTables($this->tables);
            $sql = $this->update($data);
            
            $rs  = $this->execute($sql);

            return $rs;
        }
    }

    /**
     * Apaga a quotação de solicitação de compra
     *
     * @param (int) chave primária da requisição
     *
     * @return (boolean) true se apagou
     *
     **/
    public function deletePurchaseRequestQuotation($purchaseRequestId, $supplierId)
    {
        $data = array($purchaseRequestId, $supplierId);

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('purchaseRequestId = ?');

        if ( $supplierId )
        {
            $this->setWhere('supplierId = ?');
        }
        
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        
        return $rs;
    }

}
?>