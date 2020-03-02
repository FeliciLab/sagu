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
 *
 * Library Unit form
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
 * Class created on 29/07/2008
 *
 **/
class FrmWorkflowTransition extends GForm
{
    /** @var BusinessGnuteca3BusWorkflowTransition */
    public $business;
    /** @var BusinessGnuteca3BusWorkflowStatus */
    public $busWorkflowStatus;
    public $myDiv;
    
    public function __construct()
    {
        
        $this->MIOLO = MIOLO::getInstance();
        $this->setAllFunctions('WorkflowTransition', null, array('previousWorkflowStatusId','nextWorkflowStatusId'), array('previousWorkflowStatusId','nextWorkflowStatusId','workflowId'));
        $this->setTransaction('gtcConfigWorkflow');
        $this->busWorkflowStatus = $this->MIOLO->getBusiness('gnuteca3','BusWorkflowStatus');
        parent::__construct();
    }


    public function mainFields()
    {
        $workflowList = BusinessGnuteca3BusDomain::listForSelect('WORKFLOW',false,true);

        $currentWorkflow = array_keys($workflowList);
        $currentWorkflow = $currentWorkflow[0];
        $fields[] = $workflowId = new GSelection('workflowId', null, _M('Workflow',$this->module), $workflowList,null, null, null,true);
        $fields[] = $containerWorkflow = $this->myDiv =new MDiv('divWorkflow',$this->getWorkflowStatusField( $currentWorkflow ) );
        $workflowId->addAttribute('onchange',GUtil::getAjax('changeWorkflow'));

        if ( $this->function != 'insert' )
        {
            $workflowId->setReadOnly(true);
        }
        
        $fields[] = new MTextField('__function',  null, _M('Função',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('__name', null, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);
        
        $validators[] = new MRequiredValidator('previousWorkflowStatusId');
        $validators[] = new MRequiredValidator('nextWorkflowStatusId');
        $validators[] = new MRequiredValidator('__name');

        $this->setFields($fields);
        $this->setValidators($validators);
    }


	public function loadFields()
	{
        parent::loadFields();

        $this->myDiv->setInner( $this->getWorkflowStatusField( $this->business->workflowId , true) );
        $this->business->__function = $this->business->function;
        $this->business->__name = $this->business->name;
        $this->setData( $this->business );
	}

    public function getData ()
    {
        $data = parent::getData();
        $data->function = $data->__function;
        $data->name = $data->__name;

        return $data;
    }

    public function changeWorkflow($args)
    {
        $fields[] = $this->getWorkflowStatusField( $args->workflowId );
        $this->setResponse($fields, 'divWorkflow');
    }

    public function getWorkflowStatusField( $workflowId , $inUpdate = false )
    {
        $statusList = $this->busWorkflowStatus->listWorkflowStatus( $workflowId );

        //Label estado anterior
        $lblPreviousWorkflowStatusId = new MLabel(_M('Estado anterior', $this->module).":");
        $lblPreviousWorkflowStatusId->setWidth(FIELD_LABEL_SIZE);
        $lblPreviousWorkflowStatusId->setClass('mCaptionRequired');

        //Label estado seguinte
        $lblNextWorkflowStatusId = new MLabel(_M('Estado seguinte', $this->module).":");
        $lblNextWorkflowStatusId->setWidth(FIELD_LABEL_SIZE);
        $lblNextWorkflowStatusId->setClass('mCaptionRequired');

        //Criação dos campos
        $previousWorkflowStatusId = new GSelection('previousWorkflowStatusId', null, null, $statusList ,null, null, null,true);
        $nextWorkflowStatusId = new GSelection('nextWorkflowStatusId', null, null,  $statusList,null, null, null,true) ;


        //situação especial para montage de campos quando editando
        if ( $inUpdate )
        {
            $previousWorkflowStatusId->setValue($this->business->previousWorkflowStatusId);
            $previousWorkflowStatusId->setClass('mReadOnly');
            $this->jsDisabled('previousWorkflowStatusId');
            
            $nextWorkflowStatusId->setValue($this->business->nextWorkflowStatusId);
            $nextWorkflowStatusId->setClass('mReadOnly');
            $this->jsDisabled('nextWorkflowStatusId');
        }

        //Monta Campos em Div's porque da problema com css required do label
        $fields[] = new MDiv('previousWorkflowId',array($lblPreviousWorkflowStatusId,$previousWorkflowStatusId));
        $fields[] = new MDiv('nextWorkflowId',array($lblNextWorkflowStatusId,$nextWorkflowStatusId));
        
        $fields = new MFormContainer('workflowFields',$fields);

        return $fields;
    }

    

}
?>