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
 * MaterialPhysicalType business
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 19/02/2009
 *
 **/
class BusinessGnuteca3BusMaterialPhysicalType extends GBusiness
{
    public $materialPhysicalTypeId;
    public $description;
    public $image;
    public $observation;

    public $materialPhysicalTypeIdS;
    public $descriptionS;
    public $imageS;
    public $observationS;


    public function __construct()
    {
    	$table = 'gtcMaterialPhysicalType';
    	$pkeys = 'materialPhysicalTypeId';
    	$cols  = 'description,
                  image,
                  observation';
        parent::__construct($table, $pkeys, $cols);
    }


    public function listMaterialPhysicalType($forCatalogue = false)
    {
        $rs = $this->autoList();

        if(!$rs || !$forCatalogue)
        {
            return $rs;
        }

        foreach ($rs as $i => $v)
        {
            $r[$i]->option      = $v[0];
            $r[$i]->description = $v[1];
        }

        return $r;
    }


    public function getMaterialPhysicalType($materialTypeId)
    {
        $this->clear();
        return $this->autoGet($materialTypeId);
    }


    public function searchMaterialPhysicalType($toObject = FALSE)
    {
        /*$filters = array(
            'materialPhysicalTypeId'    => 'equals',
            'description'               => 'ilike',
            'observation'               => 'ilike'
        );
        $this->clear();
        return $this->autoSearch($filters, $toObject);*/
        $this->clear();
        $this->setColumns('materialPhysicalTypeId, description, observation');
        $this->setTables('gtcMaterialPhysicalType');
        if ( $this->materialPhysicalTypeIdS )
        {
            $this->setWhere('materialPhysicalTypeId = ?');
            $data[] = $this->materialPhysicalTypeIdS;
        }
        if ($this->descriptionS)
        {
            $this->descriptionS = str_replace(' ','%', $this->descriptionS);
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . strtolower($this->descriptionS) . '%';
        }
        if ($this->observationS)
        {
            $this->observationS = str_replace(' ','%', $this->observationS);
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = '%' . strtolower($this->observationS) . '%';
        }
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        return $rs;
    }


    public function insertMaterialPhysicalType()
    {
        return $this->autoInsert();
    }


    public function updateMaterialPhysicalType()
    {
        return $this->autoUpdate();
    }


    public function deleteMaterialPhysicalType($materialPhysicalTypeId)
    {
        return $this->autoDelete($materialPhysicalTypeId);
    }
}
?>
