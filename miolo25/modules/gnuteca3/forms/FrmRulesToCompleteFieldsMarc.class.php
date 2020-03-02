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
 * SearchFormat form
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
 * Class created on 28/11/2008
 *
 **/


/**
 * Form to manipulate a preference
 **/
class FrmRulesToCompleteFieldsMarc extends GForm
{
	public $busMarcTagListingOption;
	
	
    public function __construct()
    {
    	global $MIOLO, $module;
    	$this->busMarcTagListingOption = $MIOLO->getBusiness($module, 'BusMarcTagListingOption');
        $this->setAllFunctions('RulesToCompleteFieldsMarc', null, array('rulesToCompleteFieldsMarcId'), array('category', 'affectRecordsCompleted'));
        parent::__construct();
    }


    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[]       = new MTextField('rulesToCompleteFieldsMarcId', null, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
            $validators[]   = new MRequiredValidator('rulesToCompleteFieldsMarcId');
        }

        $fields[]       = new GSelection('category', null, _M('Categoria', $this->module), $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY'));
        $validators[]   = new MRequiredValidator('category');
        $fields[]       = new MMultiLineField('originField', null, _M('Campo de origem', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $validators[]   = new MRequiredValidator('originField');
        $fields[]       = new MMultiLineField('fateField', null, _M('Campo de destino', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $validators[]   = new MRequiredValidator('fateField');
        
        $lbl = new MLabel(_M('Afeta registros concluídos', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $affectRecordsCompleted = new MRadioButtonGroup('affectRecordsCompleted', null, GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctAffectRecordsCompleted', array($lbl, $affectRecordsCompleted));
        
        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>
