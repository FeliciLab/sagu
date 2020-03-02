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
 * @author Jonas C. Rosa [jonas_rosa@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 03/08/2012
 *
 * */
class BusinessGnuteca3BusDocumentType extends GBusiness
{
    public $documentTypeIdS;
    public $nameS;
    public $maskS;
    public $sexS;
    public $personTypeS;
    public $minAgeS;
    public $maxAgeS;
    public $needDeliverS;
    public $isBlockenRollS;
    public $fillHintS;
    public $documentTypeId;
    public $name;
    public $_name;
    public $mask;
    public $sex;
    public $personType;
    public $minAge;
    public $maxAge;
    public $needDeliver;
    public $isBlockenRoll;
    public $fillHint;

    function __construct()
    {
        parent::__construct('basDocumentType', 'documentTypeId', 'name,
                                mask,
                                sex,
                                personType,
                                minAge,
                                maxAge,
                                needDeliver,
                                isBlockenRoll,
                                fillHint'
        );
    }

    public function listDocumentType($object = FALSE)
    {
        return $this->autoList();
    }

    public function getDocumentType($id)
    {
        $this->clear;
        $get = $this->autoGet($id);
        $this->_name = $this->name;

        return $get;
    }

    public function searchDocumentType($toObject = false)
    {
        $filters = array(
            'documentTypeId' => 'equal',
            'name' => 'ilike',
            'mask' => 'ilike',
            'sex' => 'ilike',
            'personType' => 'ilike',
            'minAge' => 'equal',
            'maxAge' => 'equal',
            'needDeliver' => 'equal',
            'isBlockenRoll' => 'equal',
            'fillHint' => 'ilike'
        );

        $this->clear();
        return $this->autoSearch($filters, $toObject);
    }

    public function insertDocumentType()
    {
        $this->name = $this->_name;
        $this->documentTypeId = null;
        return $this->autoInsert();
    }

    public function updateDocumentType()
    {
        $this->name = $this->_name;
        return $this->autoUpdate();
    }

    public function deleteDocumentType($documentTypeId)
    {
        return $this->autoDelete($documentTypeId);
    }

    public static function listTypePerson($type = 0)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        if ( $type == 0 )
        {
            $data = array(
                'F' => 'Pessoa física',
                'L' => 'Pessoa jurídica'
            );
        }
        elseif ( $type == 1 )
        {
            $data = array(
                array( 'Pessoa física', 'F' ),
                array( 'Pessoa jurídica', 'L' )
            );
        }

        return $data;
    }

    public static function listMascFem($type = 0)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        if ( $type == 0 )
        {
            $data = array(
                'M' => 'Masculino',
                'F' => 'Feminino'
            );
        }
        elseif ( $type == 1 )
        {
            $data = array(
                array( 'Masculino', 'M' ),
                array( 'Feminino', 'F' )
            );
        }

        return $data;
    }
}

?>