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
 * PrefixSuffix form
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
 * Class created on 06/01/2009
 *
 **/
class FrmPrefixSuffix extends GForm
{
    public $module;


    public function __construct()
    {
        $this->module = MIOLO::getCurrentModule();
        $this->setAllFunctions('PrefixSuffix', 'prefixSuffixId', array('prefixSuffixId', 'fieldId', 'subFieldId', 'content', 'type'), array('prefixSuffixId'));
        parent::__construct();
    }


    /**
     * Default method to define fields
     **/
    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[]       = new MTextField('prefixSuffixId', $this->prefixSuffixId->value, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);
        }

        $fields['fieldId'] = new MTextField('fieldId', $this->fieldId->value, _M('Campo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['fieldId']->addAttribute('maxLength', '3');
        $fields['subFieldId'] = new MTextField('subFieldId', $this->subFieldId->value, _M('Subcampo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields['subFieldId']->addAttribute('maxLength', '1');
        $fields[] = new MTextField('content', $this->content->value, _M('Conteúdo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('type', $this->type->value, _M('Tipo', $this->module), $this->business->listTypes(), null, null, null, TRUE);
        $validators[] = new MIntegerValidator('fieldId', '', 'optional', _M('Campo inválido.'));
        $validators[] = new MRequiredValidator('fieldId');
        $validators[] = new MRequiredValidator('subFieldId');
        $validators[] = new MRequiredValidator('content');

        $this->setFields($fields);
        $this->setValidators($validators);
	}


    public function loadFields()
    {
        $this->business->getPrefixSuffix( MIOLO::_REQUEST('prefixSuffixId'));
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
