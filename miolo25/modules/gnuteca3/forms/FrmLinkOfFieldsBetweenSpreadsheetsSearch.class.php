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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/06/2011
 *
 **/
class FrmLinkOfFieldsBetweenSpreadsheetsSearch extends GForm
{
	public $MIOLO;
	public $module;
	public $busMarcTagListingOption;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
    	$this->busMarcTagListingOption = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListingOption');
        $this->setAllFunctions('LinkOfFieldsBetweenSpreadsheets', array( 'linkOfFieldsBetweenSpreadsheetsIdS','categoryS', 'levelS','tagS','tagSonS'), 'linkOfFieldsBetweenSpreadsheetsId');
        parent::__construct();
    }

    public function mainFields()
    {
        $listCategory = $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY');
        $listLevel    = $this->busMarcTagListingOption->listMarcTagListingOption('LEVEL');

        $fields[] = new MTextField('linkOfFieldsBetweenSpreadsheetsIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('categoryS', null, _M('Categoria', $this->module), $listCategory);
        $fields[] = new GSelection('levelS', null, _M('Nível', $this->module), $listLevel);
        $fields[] = new MTextField('tagS',   null, _M('Etiqueta', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('categorySonS',  null, _M('Categoria filho', $this->module), $listCategory);
        $fields[] = new GSelection('levelSonS', null, _M('Nível do filho', $this->module), $listLevel);
        $fields[] = new MTextField('tagSonS', null, _M('Etiqueta filho', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('typeS', null, _M('Tipo', $this->module), $this->business->listTypes());

        $this->setFields($fields);
    }
}
?>