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
 * LinkOfFieldsBetweenSpreadhseet form
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
class FrmLinkOfFieldsBetweenSpreadsheets extends GForm
{
	public $MIOLO;
	public $module;
	public $busMarcTagListingOption;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
    	$this->busMarcTagListingOption = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListingOption');
        $this->setAllFunctions('LinkOfFieldsBetweenSpreadsheets', array('categoryS', 'levelS','linkOfFieldsBetweenSpreadsheetsIdS'), 'linkOfFieldsBetweenSpreadsheetsId', array('category', 'level', 'tag','tagSon'));
        parent::__construct();
    }

    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $linkOfFieldsBetweenSpreadsheetsId = new MTextField('linkOfFieldsBetweenSpreadsheetsId', null, _M('Código', $this->module), FIELD_ID_SIZE);
            $linkOfFieldsBetweenSpreadsheetsId->setReadOnly(TRUE);
            $fields[] = $linkOfFieldsBetweenSpreadsheetsId;
            $validators[] = new MRequiredValidator('linkOfFieldsBetweenSpreadsheetsId');
        }

        $listCategory = $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY');
        $listLevel    = $this->busMarcTagListingOption->listMarcTagListingOption('LEVEL');

        $fields[]     = new GSelection('category', null, _M('Categoria', $this->module),      $listCategory);
        $fields[]     = new GSelection('level', null, _M('Nível', $this->module),         $listLevel);
        $fields[]     = new MTextField('tag',   null, _M('Etiqueta', $this->module), FIELD_ID_SIZE);
        $fields[]     = new GSelection('categorySon',  null, _M('Categoria filho', $this->module),      $listCategory);
        $fields[]     = new GSelection('levelSon', null, _M('Nível do filho', $this->module),         $listLevel);
        $fields[]     = new MTextField('tagSon', null, _M('Etiqueta filho', $this->module),    FIELD_ID_SIZE);
        $fields[]     = new GSelection('type', null, _M('Tipo', $this->module),          $this->business->listTypes());

        $this->setFields($fields);

        $validators[] = new MRequiredValidator('category');
        $validators[] = new MRequiredValidator('level');
        $validators[] = new MRequiredValidator('tag');
        $validators[] = new MRequiredValidator('categorySon');
        $validators[] = new MRequiredValidator('levelSon');
        $validators[] = new MRequiredValidator('tagSon');
        $validators[] = new MRequiredValidator('type');
        
        $this->setValidators($validators);
    }
}
?>