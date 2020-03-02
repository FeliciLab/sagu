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
 * This file handles the connection and actions for basDomain table
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2010
 *
 **/

/**
 * Class to manipulate the basConfig table
 **/

class BusinessGnuteca3BusDomain extends GBusiness
{
    public $domainId;
    public $sequence;
    public $key;
    public $abbreviated;
    public $label;

    public $domainIdS;
    public $sequenceS;
    public $keyS;
    public $abbreviatedS;
    public $labelS;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = 'gnuteca3';
        parent::__construct();
        $this->id = 'domainId';
        $this->columns = $this->id . ', ' .
                     'sequence,
                      key,
                      abbreviated,
                      label';
        $this->tables  = 'basDomain';
    }

    public function listDomain($domainId, $returnAsObject = false, $returnAssociative = false)
    {
    	$this->clear();
        $this->setColumns('key, label');
        $this->setTables($this->tables);
        $this->setWhere('domainId = ?');
        $this->setOrderBy('domainId, sequence');
        $sql = $this->select(array($domainId));
        $query = $this->query( $sql, $returnAsObject );
        
        if ($returnAssociative && $query)
        {
            $result = array();
            foreach  ($query as $v)
            {
                $result[ $v[0] ] = $v[1];
            }
            return $result;
        }
        else
        {
            return $query;
        }
    }

    public static function listForSelect($domainId,$returnAsObject = false,  $returnAssociative = false)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'gnuteca3';

        $busDomain = $MIOLO->getBusiness($module, 'BusDomain');
        return $result = $busDomain->listDomain($domainId, $returnAsObject, $returnAssociative);
    }
    
     /**
     * Trata os dados do domínio para usar em rádio group
     * @param int $domainId
     * @return array de domínio 
     */
    public static function listForRadioGroup($domainId)
    {
        $domains = self::listForSelect($domainId);
        $newDomain =  array();
        
        if (is_array($domains) )
        {
            foreach( $domains as $key => $domain )
            {
                $newDomain[$key][] = $domain[1]; //descrição
                $newDomain[$key][] = $domain[0]; //id
            }
        }
        
        return $newDomain;
    }

    public function getDomain($domainId, $sequence)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('domainId = ? AND sequence = ?');
        $sql = $this->select(array($domainId, $sequence));
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
    public function searchDomain()
    {
        $this->clear();

        if ( !empty($this->domainIdS) )
        {
            $this->setWhere('lower(domainId) LIKE lower(?)');
            $data[] = $this->domainIdS . '%';
        }
        if ( !empty($this->sequenceS) )
        {
            $this->setWhere('sequence = ?');
            $data[] = $this->sequenceS;
        }
        if ( !empty($this->keyS) )
        {
            $this->setWhere('lower(key) LIKE lower(?)');
            $data[] = $this->keyS;
        }
        if ( !empty($this->abbreviatedS) )
        {
            $this->setWhere('lower(abbreviated) LIKE lower(?)');
            $data[] = $this->abbreviatedS . '%';
        }
        if ( !empty($this->labelS) )
        {
            $this->setWhere('lower(label) LIKE lower(?)');
            $data[] = $this->labelS . '%';
        }

        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('domainId, sequence');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }

    public function insertDomain()
    {
        $data = array(
            $this->domainId,
            $this->sequence,
            $this->key,
            $this->abbreviated,
            $this->label
        );

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $sql = $this->insert($data);
        $rs  = $this->execute($sql);
        
        return $rs;
    }

    public function updateDomain()
    {
        $data = array(
            $this->key,
            $this->abbreviated,
            $this->label,
            $this->domainId,
            $this->sequence
        );

        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('
            key,
            abbreviated,
            label
        ');
        $this->setWhere('domainId = ? AND sequence = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        return $rs;
    }

    public function deleteDomain($domainId, $sequence)
    {
        $data = array(
            $domainId,
            $sequence
        );

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('domainId = ? AND sequence = ?');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);

        return $rs;
    }
}
?>
