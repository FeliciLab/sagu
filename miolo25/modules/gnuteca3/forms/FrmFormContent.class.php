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
 * FormContent form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 07/04/2009
 *
 **/
class FrmFormContent extends GForm
{
	public $MIOLO;
	public $module;
	public $busFormContentType;


    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busFormContentType = $this->MIOLO->getBusiness($this->module, 'BusFormContentType');
        $this->setAllFunctions('FormContent', null, 'formContentId', 'formContentId');
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('_formContentDetail');
        }
    }


    public function mainFields()
    {   
        if ($this->function == 'update')
        {
            $formContentId = new MTextField('formContentId', NULL, _M('Código', $this->module), FIELD_ID_SIZE);
            $formContentId->setReadOnly(TRUE);
            $fields[] = $formContentId;
        }

        $fields[] = new MTextField('operator', NULL, _M('Operador', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('_form', NULL, _M('Formulário',$this->module), FIELD_DESCRIPTION_SIZE);
        $validators[] = new MRequiredValidator('_form');
        $fields[] = new MTextField('_name', NULL, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('description', NULL, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MSelection('formContentType', NULL, _M('Tipo do conteúdo do formulário', $this->module), $this->busFormContentType->listFormContentType());
        $validators[] = new MRequiredValidator('formContentType');

        //gtcFormContentDetail repetitive
        unset($columns, $flds, $valids);
        $flds[] = new MTextField('field', null, _M('Campo', $this->module), FIELD_ID_SIZE);
        $valids[] = new GnutecaUniqueValidator('field', null, 'required');
        $flds[] = new MTextField('value', null, _M('Valor', $this->module), FIELD_DESCRIPTION_SIZE);
        $valids[] = new MRequiredValidator('value');
        $columns[] = new MGridColumn(_M('Campo', $this->module), MGrid::ALIGN_LEFT, true, true, true,  'field');
        $columns[] = new MGridColumn(_M('Valor', $this->module), MGrid::ALIGN_LEFT, true, true, true,  'value');
        $detail = new GRepetitiveField('_formContentDetail', _M('Detalhe', $this->module), $columns, $flds);
        $detail->setUpdateButton(TRUE);
        $detail->setValidators($valids);
        $fields[] = $detail;

        $this->setFields($fields);
        $this->setValidators($validators);
	}


    public function loadFields()
    {
        $this->business->getFormContent( MIOLO::_REQUEST('formContentId') );
        $this->business->_name = $this->business->name;
        $this->business->_form = $this->business->form;
        $this->setData($this->business);
        $this->_formContentDetail->setData($this->business->formContentDetail);
    }


    public function tbBtnSave_click($sender = NULL)
    {
        $data = $this->getData();
        $data->form  = $data->_form;
        $data->name  = $data->_name;
        $data->formContentDetail = GRepetitiveField::getData('_formContentDetail');
        
        /**
         * Retira espaços caso o usuário preencha o campo field com algum.
         */
        foreach( $data->_formContentDetail as $key => $value )
        {
            $newValueField = trim( $value->field );
            $data->_formContentDetail[$key]->field = $newValueField;
        } 
        
        parent::tbBtnSave_click($sender, $data);
    }
}
?>
