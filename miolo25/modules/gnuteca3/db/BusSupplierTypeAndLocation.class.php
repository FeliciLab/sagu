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
 * gtcSupplierTypeAndLocation business
 *
 * @author Luiz G Gilberto Gregory F [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 20/04/2009
 *
 **/

class BusinessGnuteca3BusSupplierTypeAndLocation extends GBusiness
{

    public  $supplierId,        //  | integer                     | not null default nextval('seq_supplierid'::regclass)
            $type,              //  | character(1)                | not null
            $name,              //  | character varying           |
            $companyName,       //  | character varying           |
            $cnpj,              //  | character varying           |
            $location,          //  | character varying           |
            $neighborhood,      //  | character varying           |
            $city,              //  | character varying           |
            $zipCode,           //  | character varying           |
            $phone,             //  | character varying           |
            $fax,               //  | character varying           |
            $alternativePhone,  //  | character varying           |
            $email,             //  | character varying           |
            $alternativeEmail,  //  | character varying           |
            $contact,           //  | character varying           |
            $site,              //  | character varying           |
            $observation,       //  | text                        |
            $bankDeposit,       //  | text                        |
            $date;              //  | timestamp without time zone |


    public  $supplierIdS,        //  | integer                     | not null default nextval('seq_supplierid'::regclass)
            $typeS,              //  | character(1)                | not null
            $nameS,              //  | character varying           |
            $companyNameS,       //  | character varying           |
            $cnpjS,              //  | character varying           |
            $locationS,          //  | character varying           |
            $neighborhoodS,      //  | character varying           |
            $cityS,              //  | character varying           |
            $zipCodeS,           //  | character varying           |
            $phoneS,             //  | character varying           |
            $faxS,               //  | character varying           |
            $alternativePhoneS,  //  | character varying           |
            $emailS,             //  | character varying           |
            $alternativeEmailS,  //  | character varying           |
            $contactS,           //  | character varying           |
            $siteS,              //  | character varying           |
            $observationS,       //  | text                        |
            $bankDepositS,       //  | text                        |
            $dateS;              //  | timestamp without time zone |

    public $table, $pkeys, $cols, $fullCols;


    public function __construct()
    {
        $this->table  = 'gtcSupplierTypeAndLocation';
        $this->pkeys  = 'supplierId, type';
        $this->cols   =  'name,
                    companyName,
                    cnpj,
                    location,
                    neighborhood,
                    city,
                    zipCode,
                    phone,
                    fax,
                    alternativePhone,
                    email,
                    alternativeEmail,
                    contact,
                    site,
                    observation,
                    bankDeposit,
                    date';

        $this->fullCols = "$this->pkeys, $this->cols";

        parent::__construct($this->table, $this->pkeys, $this->cols);
    }


    /**
     * Insert Supplier Type
     *
     * @return unknown
     */
    public function insertSupplierTypeAndLocation()
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setColumns($this->fullCols);
        $sql = parent::insert($this->associateData($this->fullCols));
        return parent::execute($sql);
    }


    /**
     * Insert Supplier Type
     *
     * @return unknown
     */
    public function updateSupplierTypeAndLocation($supplierId, $type)
    {
        parent::clear();
        parent::setTables   ($this->tables);
        parent::setColumns  ($this->cols);
        parent::setWhere    ("supplierId = ? AND lower(type) = lower(?)");
        $sql = parent::update($this->associateData("$this->cols, $this->pkeys "));
        return parent::execute($sql);
    }



    /**
     * delete supplier type location
     *
     * @param int $supplierId
     * @param char $type (c|d|p)
     */
    public function deleteSupplierTypeAndLocation($supplierId, $type = null)
    {
        parent::clear();
        parent::setTables($this->tables);
        parent::setWhere("supplierId = ?");
        $args[] = $supplierId;

        if(!is_null($type))
        {
            parent::setWhere("type = ?");
            $args[] = $type;
        }

        parent::delete($args);
        return parent::execute();
    }


    public function listSupplierTypeAndLocation($types = array('p', 'd'))
    {
    	parent::clear();
    	parent::setTables($this->tables);
    	parent::setColumns('supplierId, companyName');
    	if ($types)
    	{
    		$tlist = array();
            foreach ((array)$types as $type)
            {
                $tlist[] = "'" . strtolower($type) . "'";
            }
    		parent::setWhere("lower(type) IN (".implode(',', $tlist).")");
    	}
    	parent::setWhere('companyName IS NOT NULL');
    	$sql   = parent::select($args);
    	$query = parent::query($sql);
    	return $query;
    }


    /**
     * retorna os possíveis tipos para fornecedores.
     *
     * @return simple array
     */
    public function getTypes()
    {
        return array('c', 'p', 'd');
    }


    /**
     * retorna o conteudo para setar os valores do form
     *
     */
    public function getSupplierTypeAndLocationValueForm($supplierId, $type = null)
    {
        parent::clear       ();
        parent::setColumns  ($this->fullCols);
        parent::setTables   ($this->tables);
        parent::setWhere    ("supplierId = ?");
        $args[] = $supplierId;

        if(!is_null($type))
        {
            parent::setWhere    ("lower(type) = lower(?)");
            $args[] = $type;
        }

        $sql    = parent::select($args);
        $result = parent::query($sql, true);
        if(!is_null($type))
        {
            return isset($result[0]) ? $result[0] : false;
        }


        return isset($result) ? $result : false;
    }


    /**
     * lipa os  attributos da classe
     *
     */
    public function clean()
    {
        $this->supplierId=        //  | integer                     | not null default nextval('seq_supplierid'::regclass)
        $this->type=              //  | character(1)                | not null
        $this->name=              //  | character varying           |
        $this->companyName=       //  | character varying           |
        $this->cnpj=              //  | character varying           |
        $this->location=          //  | character varying           |
        $this->neighborhood=      //  | character varying           |
        $this->city=              //  | character varying           |
        $this->zipCode=           //  | character varying           |
        $this->phone=             //  | character varying           |
        $this->fax=               //  | character varying           |
        $this->alternativePhone=  //  | character varying           |
        $this->email=             //  | character varying           |
        $this->alternativeEmail=  //  | character varying           |
        $this->contact=           //  | character varying           |
        $this->site=              //  | character varying           |
        $this->observation=       //  | text                        |
        $this->bankDeposit=       //  | text                        |
        $this->date=              //  | timestamp without time zone |
        $this->supplierIdS=        //  | integer                     | not null default nextval('seq_supplierid'::regclass)
        $this->typeS=              //  | character(1)                | not null
        $this->nameS=              //  | character varying           |
        $this->companyNameS=       //  | character varying           |
        $this->cnpjS=              //  | character varying           |
        $this->locationS=          //  | character varying           |
        $this->neighborhoodS=      //  | character varying           |
        $this->cityS=              //  | character varying           |
        $this->zipCodeS=           //  | character varying           |
        $this->phoneS=             //  | character varying           |
        $this->faxS=               //  | character varying           |
        $this->alternativePhoneS=  //  | character varying           |
        $this->emailS=             //  | character varying           |
        $this->alternativeEmailS=  //  | character varying           |
        $this->contactS=           //  | character varying           |
        $this->siteS=              //  | character varying           |
        $this->observationS=       //  | text                        |
        $this->bankDepositS=       //  | text                        |
        $this->dateS=  null;            //  | timestamp without time zone |
    }

}
?>
