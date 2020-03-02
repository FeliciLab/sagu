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
class BusinessGnuteca3BusPurchaseRequestMaterial extends GBusiness
{
    public $MIOLO;
    public $module;
    public $colsNoId;

    public $purchaseRequestId,
           $fieldId,
           $subfieldId,
           $content;

    function __construct()
    {
        parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->tables   = 'gtcPurchaseRequestMaterial';
        $this->id       = 'purchaseRequestId,
                           fieldId, 
                           subfieldId';
        $this->colsNoId = 'content';
        
        $this->columns  = $this->id . ',' . $this->colsNoId;
    }
    
    /**
     * Obtém os materiais da solicitação de compra
     *
     * @param (int) chave primária do registro
     * @param (int) chave primária do registro
     * @param (int) chave primária do registro
     * 
     * @return (object) contém o registro obtido
     *
     **/
    public function getPurchaseRequestMaterial($purchaseRequestId, $fieldId, $subfieldId)
    {
        $this->clear();
        if ( $purchaseRequestId && $supplierId && $subfieldId )
        {
            $data = array($purchaseRequestId, $fieldId, $subfieldId);
            $this->setWhere('purchaseRequestId' . ' = ?');
            $this->setWhere('fieldId'. ' = ?');
            $this->setWhere('subfieldId'. ' = ?');
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
     * Busca os materiais de solicitações de compras
     *
     * @param (string) coluna de ordenação dos registros
     * @param (boolean) transformar em objeto
     *
     * @return (array) contendo os dados que vão compor a grid
     **/
    public function searchPurchaseRequestMaterial($orderBy = 'purchaseRequestId', $toObject = false)
    {
        $this->clear();

        if ( $this->purchaseRequestId )
        {
            $this->setWhere('purchaseRequestId = ?' );
            $data[] = $this->purchaseRequestId;
        }
        
        if ( $this->fieldId )
        {
            $this->setWhere('A.fieldId = ?');
            $data[] = $this->fieldId;
        }
        
        if ( $this->subfieldId )
        {
            $this->setWhere('subfieldId = ?');
            $data[] = $this->subfieldId;
        }
        
        if ( $this->content )
        {
            $this->setWhere('lower(content) like (lower(?))');
            $data[] = $this->content;
        }

        $this->setColumns($this->columns);
    
        $this->setTables($this->tables);
        
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, $toObject);

        return $rs;
    }

    /**
     * Insere um material de solicitação de compra
     *
     * @return True se funcionou
     *
     **/
    public function insertPurchaseRequestMaterial()
    {
        $this->clear();
        
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $sql = $this->insert( $this->associateData($this->columns) );

        return $this->execute($sql);
    }

    /**
     * Atualiza o registro de material de solicitação de compra
     *
     * @return (boolean): True se teve sucesso
     *
     **/
    public function updatePurchaseRequestMaterial()
    {
        return $this->insertPurchaseRequestMaterial();
    }

    /**
     * Apaga o material de solicitação de compra
     *
     * @param (int) chave primária da requisição
     *
     * @return (boolean) true se apagou
     *
     **/
    public function deletePurchaseRequestMaterial($purchaseRequestId = null, $fieldId = null, $subfieldId = null)
    {
        $this->clear();
        if ( $purchaseRequestId )
        {
            $this->setWhere('purchaseRequestId = ?');
            $data[] = $purchaseRequestId;
        }
        
        if ( $fieldId )
        {
            $this->setWhere('fieldId = ?');
            $data[] = $fieldId;
        }
        
        if ( $subfieldId )
        {
            $this->setWhere('subfieldId = ?');
            $data[] = subfieldId;
        }
       
        $this->setTables($this->tables);
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        
        return $rs;
    }
    
    /**
     * Busca o(s) purchaseRequestId(s) de acordo com o material passado por parametro
     * @param $dinamicFields (array) material 
     * return (array) com id(s) das requisições
     */
    public function searchPurchaseRequestIdOfPurchaseRequestMaterial($dinamicFields)
    {
        $param = array();
        $this->clear();
        $this->setColumns('distinct(purchaseRequestId)');
        $this->setTables($this->tables);

        $first = true;
        if ( is_array($dinamicFields) )
        {
            foreach( $dinamicFields as $tag => $value )
            {
                if ( strlen($value) > 0 )
                {
                    $tag = explode('.', $tag);
                    
                    if ( $first )
                    {
                        $this->setWhere('(fieldId = ? AND subfieldId = ? AND upper(unaccent(content)) like upper(unaccent(?)))');
                        $param[] = $tag[0];
                        $param[] = $tag[1];
                        $param[] = '%' . $value . '%';
                    }
                    else
                    {
                        $this->setWhere("purchaseRequestId IN ( SELECT purchaseRequestId FROM gtcPurchaseRequestMaterial WHERE (fieldId = '{$tag[0]}' AND subfieldId = '{$tag[1]}' AND upper(unaccent(content)) like upper(unaccent('%{$value}%'))))");
                    }
                    
                    $first = false;
                }
            }
        }
        
        $result = $this->query( $this->select($param) );
        $newResult = array();
        
        if ( is_array($result) && !empty($result) ) //Se for array e não estiver vazio
        {
            foreach( $result as $value ) 
            {
                $newResult[] = $value[0]; //Adiciona numero de controle
            }
        }
        else //Se não tiver número de controle
        {
            return null; //Adiciona nulo para não retornar nada na query
        }
        
        return $newResult;
    }
}
?>