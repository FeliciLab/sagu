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
 * Library Unit search form
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
 * Class created on 29/07/2008
 *
 **/
class FrmWorkflowTransitionSearch extends GForm
{
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $MIOLO;
    public $business;
    public $busWorkflowStatus;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->setAllFunctions('WorkflowTransition', array('__functionS','nameS','previousWorkflowStatusIdS','nextWorkflowStatusIdS'), array('previousWorkflowStatusId','nextWorkflowStatusId'));
        $this->setTransaction('gtcConfigWorkflow');
        $this->busWorkflowStatus = $this->MIOLO->getBusiness( 'gnuteca3', 'BusWorkflowStatus');
        parent::__construct();
    }

    public function mainFields()
    {
        $listWorkflowStatus = $this->busWorkflowStatus->listWorkflowStatus();

        $workflowList = BusinessGnuteca3BusDomain::listForSelect('WORKFLOW',false,true);
        $currentWorkflow = array_keys($workflowList);

        $fields[] = $workflowId = new GSelection('workflowIdS', null, _M('Workflow',$this->module), $workflowList,null, null, null,null);
        $fields[] = new MDiv('divWorkflow',$this->getWorkflowStatusField( null ) );
        $fields[] = new MTextField('__functionS', null, _M('Função',$this->module),FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTextField('__nameS', null, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);

        $workflowId->addAttribute('onchange',GUtil::getAjax('changeWorkflow'));

        $this->setFields( $fields );
    }

    public function changeWorkflow($args)
    {
        $fields[] = $this->getWorkflowStatusField( $args->workflowIdS );
        $this->setResponse($fields, 'divWorkflow');
    }

    public function getWorkflowStatusField( $workflowId )
    {
        $statusList = $this->busWorkflowStatus->listWorkflowStatus( $workflowId );

        //Label estado anterior
        $lblPreviousWorkflowStatusIdS = new MLabel(_M('Estado anterior', $this->module).":");
        $lblPreviousWorkflowStatusIdS->setWidth(FIELD_LABEL_SIZE);
        $lblPreviousWorkflowStatusIdS->setClass('mCaptionRequired');

        //Label estado seguinte
        $lblNextWorkflowStatusIdS = new MLabel(_M('Estado seguinte', $this->module).":");
        $lblNextWorkflowStatusIdS->setWidth(FIELD_LABEL_SIZE);
        $lblNextWorkflowStatusIdS->setClass('mCaptionRequired');

        //Criação dos campos
        $previousWorkflowStatusIdS = new GSelection('previousWorkflowStatusIdS', null, null, $statusList ,null, null, null,null);
        $nextWorkflowStatusIdS = new GSelection('nextWorkflowStatusIdS', null, null,  $statusList,null, null, null,null) ;

        //Monta Campos em Div's porque da problema com css required do label
        $fields[] = new MDiv('previousWorkflowId',array($lblPreviousWorkflowStatusIdS,$previousWorkflowStatusIdS));
        $fields[] = new MDiv('nextWorkflowId',array($lblNextWorkflowStatusIdS,$nextWorkflowStatusIdS));

        $fields = new MFormContainer('workflowFields',$fields);
        
        return $fields;
    }



}
?>