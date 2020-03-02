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
 * Separator form
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 25/05/2009
 *
 **/
class FrmSeparator extends GForm
{
	public $MIOLO;
	public $module;
    public $busCataloguingFormat;


    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busCataloguingFormat = $this->MIOLO->getBusiness($this->module, 'BusCataloguingFormat');
        $this->setAllFunctions('Separator', 'separatorId', array('separatorId', 'fieldId', 'subFieldId'), array('separatorId'));
        parent::__construct();
    }


    /**
     * Default method to define fields
     **/
    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[]     = new MTextField('separatorId', $this->separatorId->value, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true );
            $validators[] = new MRequiredValidator('separatorId');
        }

        $cataloguingFormatId = $this->busCataloguingFormat->listCataloguingFormat();

        $fields[] = new GSelection('cataloguingFormatId', $this->cataloguingFormatId->value, _M('Código do formato de catalogação', $this->module), $cataloguingFormatId);
        $fields['fieldId'] = new MTextField('fieldId', $this->fieldId->value, _M('Campo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['fieldId']->addAttribute('maxLength', '3');
        $fields['subFieldId'] = new MTextField('subFieldId', $this->subFieldId->value, _M('Subcampo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['subFieldId']->addAttribute('maxLength', '1');
        $fields[] = new MTextField('content', $this->content->value, _M('Conteúdo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['fieldId2'] = new MTextField('fieldId2', $this->fieldId2->value, _M('Campo 2', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['fieldId2']->addAttribute('maxLength', '3');
        $fields['subFieldId2'] = new MTextField('subFieldId2', $this->subFieldId2->value, _M('Subcampo 2', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['subFieldId2']->addAttribute('maxLength', '1');

        $validators[]   = new MRequiredValidator('cataloguingFormatId');
        $validators[]   = new MRequiredValidator('fieldId');
        $validators[]   = new MRequiredValidator('subFieldId');
        $validators[]   = new MRequiredValidator('content');
        $validators[]   = new MRequiredValidator('fieldId2');
        $validators[]   = new MRequiredValidator('subFieldId2');

        $this->setValidators($validators);
        $this->setFields($fields);
	}


    public function loadFields()
    {
        $this->business->getSeparator( MIOLO::_REQUEST('separatorId'));
        $this->business->form_ = $this->business->form;
        $this->setData($this->business);
    }


    public function tbBtnSave_click($sender = NULL)
    {
        $data = $this->getData();
        $data->form = $data->form_;
        parent::tbBtnSave_click($sender, $data);
    }
}
?>
