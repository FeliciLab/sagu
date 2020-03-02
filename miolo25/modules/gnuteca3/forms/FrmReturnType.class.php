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
 * Return type form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 25/05/2009
 *
 **/
class FrmReturnType extends GForm
{
    public $module;

    public function __construct()
    {
        $this->module = MIOLO::getCurrentModule();
        $this->setAllFunctions('ReturnType', 'returnTypeId', array('returnTypeId', 'description'), array('returnTypeId','description'));
        parent::__construct();
    }

    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[]      = new MTextField('returnTypeId', $this->returnTypeId->value, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);
            $validators[]  = new MRequiredValidator('returnTypeId');
        }

        $validators[]   = new MRequiredValidator('description');
        $fields[]       = $description = new MTextField('description', $this->description->value, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        //adicionado em function de problemas ao adicionar enter no formulário
        $description->addAttribute('onpressenter', GUtil::getCloseAction( true ) );

        $lbl = new MLabel(_M('Forçar recibo de devolução somente por e-mail', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $sendMailReturnReceipt = new MRadioButtonGroup('sendMailReturnReceipt', null, GUtil::listYesNo(1), 0, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctPrintReturnReceipt', array($lbl, $sendMailReturnReceipt));        
        
        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function loadFields()
    {
        $this->business->getReturnType( MIOLO::_REQUEST('returnTypeId'));
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