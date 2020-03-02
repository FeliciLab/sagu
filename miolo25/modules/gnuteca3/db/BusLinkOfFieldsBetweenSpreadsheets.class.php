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
 * gtcLinkOfFieldsBetweenSpreadsheets business
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
 * Class created on 02/12/2008
 *
 **/
class BusinessGnuteca3BusLinkOfFieldsBetweenSpreadsheets extends GBusiness
{
    public $linkOfFieldsBetweenSpreadsheetsId;
    public $category;
    public $level;
    public $tag;
    public $categorySon;
    public $levelSon;
    public $tagSon;
    public $type;

    public $linkOfFieldsBetweenSpreadsheetsIdS;
    public $categoryS;
    public $levelS;
    public $tagS;
    public $categorySonS;
    public $levelSonS;
    public $tagSonS;
    public $typeS;

    public function __construct()
    {
        $table = 'gtcLinkOfFieldsBetweenSpreadsheets';
        $pkeys = 'linkOfFieldsBetweenSpreadsheetsId';
        $cols  = 'category,
			      level,
			      tag,
			      categorySon,
			      levelSon,
			      tagSon,
			      type';

        parent::__construct($table, $pkeys, $cols);
    }

    public function insertLinkOfFieldsBetweenSpreadsheets()
    {
        return $this->autoInsert();
    }

    public function updateLinkOfFieldsBetweenSpreadsheets()
    {
        return $this->autoUpdate();
    }

    public function deleteLinkOfFieldsBetweenSpreadsheets($linkOfFieldsBetweenSpreadsheetsId)
    {
        return $this->autoDelete($linkOfFieldsBetweenSpreadsheetsId);
    }

    public function getLinkOfFieldsBetweenSpreadsheets($linkOfFieldsBetweenSpreadsheetsId)
    {
        $this->clear();
        return $this->autoGet($linkOfFieldsBetweenSpreadsheetsId);
    }

    public function searchLinkOfFieldsBetweenSpreadsheets($object = false)
    {
        $this->clear();
        $filters = array(
            'linkOfFieldsBetweenSpreadsheetsId' => 'equals',
            'category'      => 'equals',
            'level'         => 'equals',
            'tag'           => 'ilike',
            'categorySon'   => 'equals',
            'levelSon'      => 'equals',
            'tagSon'        => 'ilike',
            'type'          => 'equals'
        );

        $args = $this->addFilters($filters);
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($args);
        $rs  = $this->query($sql, $object);

        return $rs;
    }

    public function listLinkOfFieldsBetweenSpreadsheets()
    {
        return $this->autoList();
    }

    public function listTypes()
    {
        return array
        (
            1 => 'Copy',
            2 => 'Reference'
        );
    }


    /**
     * Enter description here...
     *
     * @param int $controlNumberFather
     * @param char(2) $sonCategory
     */
    public function getLinksByControlNumberFather($controlNumberFather, $sonCategory, $sonLevel, $type = null)
    {
        $businessMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');

        $this->clear();

        $this->category     = $businessMaterialControl->getCategory($controlNumberFather);
        $this->level        = $businessMaterialControl->getLevel($controlNumberFather);
        $this->categorySon  = $sonCategory;
        $this->levelSon     = $sonLevel;

        if ( !is_null($type) )
        {
            $this->typeS = $type; //Definição do tipo de referencia. 1 = Cópia; 2 = Referencia
        }

        $r = $this->searchLinkOfFieldsBetweenSpreadsheets(true);

        return $r;
    }

    function clean()
    {
         $this->linkOfFieldsBetweenSpreadsheetsId   =
         $this->category                            =
         $this->level                               =
         $this->tag                                 =
         $this->categorySon                         =
         $this->levelSon                            =
         $this->tagSon                              =
         $this->type                                = null;
    }
}
?>
