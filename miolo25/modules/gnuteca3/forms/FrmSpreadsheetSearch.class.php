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
 * Spreadsheet search form
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
 * Class created on 26/09/2008
 *
 **/


class FrmSpreadsheetSearch extends GForm
{
	public $MIOLO;
	public $module;
    public $busMarcTagListingOption;


    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busMarcTagListingOption = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListingOption');
        $this->setAllFunctions('Spreadsheet', array('category'),array('category','level'));
        parent::__construct();
    }


    public function mainFields()
    {
        $listCategory = $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY');
        $fields[]   = new GSelection('categoryS', $this->categoryS->value, _M('Categoria', $this->module), $listCategory);
        $listLevel  = $this->busMarcTagListingOption->listMarcTagListingOption('LEVEL');
        $fields[]   = new GSelection('levelS', $this->levelS->value, _M('Nível', $this->module), $listLevel);
        $fields[]   = new MTextField('fieldS', $this->fieldS->value, _M('Campo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('requiredS', $this->requiredS->value, _M('Validador da obra', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('repeatFieldRequiredS', $this->repeatFieldRequiredS->value, _M('Validador de campo repetitivo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('defaultValueS', $this->defaultValueS->value, _M('Valor padrão', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('menuNameS', null, _M('Nome do menu', $this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields( $fields );
    }

}
?>