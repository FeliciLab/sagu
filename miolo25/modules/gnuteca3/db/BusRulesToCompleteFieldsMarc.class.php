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
 * gtcRulesToCompleteFieldsMarc business
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
 *
 * @since
 * Class created on 01/12/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusRulesToCompleteFieldsMarc extends GBusiness
{
    public $rulesToCompleteFieldsMarcId;
    public $category;
    public $originField;
    public $fateField;
    public $affectRecordsCompleted;

    public $rulesToCompleteFieldsMarcIdS;
    public $categoryS;
    public $originFieldS;
    public $fateFieldS;
    public $affectRecordsCompletedS;
    
    
    public function __construct()
    {
        $table = 'gtcRulesToCompleteFieldsMarc';
        $pkeys = 'rulesToCompleteFieldsMarcId';
        $cols  = 'category,
                  originField,
                  fateField,
                  affectRecordsCompleted';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertRulesToCompleteFieldsMarc()
    {
        return $this->autoInsert();
    }


    public function updateRulesToCompleteFieldsMarc()
    {
        return $this->autoUpdate();
    }


    public function deleteRulesToCompleteFieldsMarc($rulesToCompleteFieldsMarcId)
    {
        return $this->autoDelete($rulesToCompleteFieldsMarcId);
    }


    public function getRulesToCompleteFieldsMarc($rulesToCompleteFieldsMarcId)
    {
        $this->clear();
        return $this->autoGet($rulesToCompleteFieldsMarcId);
    }


    public function searchRulesToCompleteFieldsMarc($object = false)
    {
        unset($this->rulesToCompleteFieldsMarcId); //estava ocorrendo bug pos-busca no formulario
        $this->clear();
        $filters = array(
            'rulesToCompleteFieldsMarcId' => 'equals',
            'category'                    => 'equals',
            'originField'                 => 'ilike',
            'fateField'                   => 'ilike',
            'affectRecordsCompleted'      => 'equals'
        );
        return $this->autoSearch($filters, $object);
    }


    public function listRulesToCompleteFieldsMarc()
    {
        return $this->autoList();
    }
}
?>
