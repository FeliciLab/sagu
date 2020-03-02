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
 * Schedule task form
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 01/07/2010
 *
 **/

class FrmScheduleTask extends GForm
{
    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module  = MIOLO::getCurrentModule();
        
        $this->setAllFunctions('ScheduleTask', array('scheduleTaskId', 'taskId', 'scheduleCycleId', 'parametersS'), array('scheduleTaskId', 'scheduleCycleId', 'taskId', 'parameters'), array('scheduleTaskId', 'taskId', 'scheduleCycleId', 'parameters'));
        parent::__construct();
    }


    public function mainFields()
    {
    	$content = _M('Valor: valores separados por', $this->module). ' ";"<br>'.
                   _M('Parâmetros: separados por', $this->module).' "|" <br>'.
                   _M('Unidades de biblioteca: separadas por', $this->module).' ","';
        $fields[] = new MDiv('divHelp', $content, 'reportDescription');
        
    	if ( $this->function != 'insert' )
    	{
            $scheduleTaskId = new MTextField('scheduleTaskId', $this->holidayId->value, _M('Agendar tarefa',$this->module), FIELD_ID_SIZE);
            $scheduleTaskId->setReadOnly(true);
            $fields[] = $scheduleTaskId;
    	}
    	$busTask = $this->MIOLO->getBusiness($this->module, 'BusTask');
    	$fields[] = $taskId = new GSelection('taskId', '', _M('Tarefa',$this->module), $busTask->listTask());
        $taskId->addAttribute('onchange', GUtil::getAjax('changeTaskInformation'));
        $validators[]   = new MRequiredValidator('taskId');

    	$fields[] = $cycle = new GSelection('scheduleCycleId', '', _M('Ciclo do agendamento',$this->module), $this->business->listScheduleCycles());
        $cycle->addAttribute('onchange', GUtil::getAjax('changeCycleInformation'));
        $validators[]   = new MRequiredValidator('scheduleCycleId');

    	$descriptionLabel = new MLabel(_M('Descrição:'), $this->module);
    	$descriptionLabel->setWidth(FIELD_LABEL_SIZE);
    	$hintLabel = new MDiv('divHintDescription');
    	$fields[] = new GContainer('cont1', array($descriptionLabel, new MTextField('description', '', '', FIELD_DESCRIPTION_SIZE), $hintLabel));
        $validators[]   = new MRequiredValidator('description');

    	$valueLabel = new MLabel(_M('Valor:', $this->module));
    	$valueLabel->setWidth(FIELD_LABEL_SIZE);
    	$hintValue = new MDiv('divHintValue');
    	$fields[] = new GContainer( 'cont2', array($valueLabel, new MTextField('cycleValue', '', '', FIELD_DESCRIPTION_SIZE), $hintValue));
        $validators[]   = new MRequiredValidator('cycleValue');

    	$lbl = new MLabel(_M('Está ativo', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $isEnable = new MRadioButtonGroup('enable', null, GUtil::listYesNo(1), DB_TRUE, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctIsEnable', array($lbl, $isEnable));

    	$parametesrLabel = new MLabel(_M('Parâmetros:', $this->module));
    	$parametesrLabel->setWidth(FIELD_LABEL_SIZE);
    	$hintParameters = new MDiv('divHintParameters');
    	$fields[] = new GContainer('cont3', array($parametesrLabel, new MTextField('_parameters', '', '', FIELD_DESCRIPTION_SIZE), $hintParameters));

        $this->setFields($fields);
        $this->setValidators($validators);

        //preenche os labels de ajuda
        $this->page->onload( GUtil::getAjax('changeTaskInformation') );
        $this->page->onload( GUtil::getAjax('changeCycleInformation') );
    }


    /**
     * Method called by ajax for alter task information
     *
     * @param (object) $args
     */
    public function changeTaskInformation( $args )
    {
    	$busTask = $this->MIOLO->getBusiness($this->module, 'BusTask');
    	$task = $busTask->getTask($args->taskId);
    	if ( $task )
    	{
    	   if (!$args->scheduleTaskId)
    	   {
    	       $this->page->onload("dojo.byId('description').value = '{$task->description}';");
    	   }
    	   $labelParam = new MLabel($task->parameters);
    	   $this->setResponse($labelParam, 'divHintParameters');
    	}
    	else
    	{
            $this->setResponse('', 'limbo');
    	}
    }


    /**
     * Method called by ajax for alter schedule cycle information
     *
     * @param (object) $args
     */
    public function changeCycleInformation( $args )
    {
    	if ( is_string($value = $this->business->listValuetypeForScheduleCycles($args->scheduleCycleId)) )
    	{
	    	$labelValue = new MLabel($this->business->listValuetypeForScheduleCycles($args->scheduleCycleId));
    	}
    	else
    	{
    		$labelValue = '';
    	}
    	$this->setResponse($labelValue, 'divHintValue');
    }
   
    public function getData ()
    {
        $data = parent::getData();
        $data->parameters = $data->_parameters;

        return $data;
    }

    public function loadFields()
    {
        parent::loadFields();
        $this->business->_parameters = $this->business->parameters;
        $this->setData($this->business);
    }

    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        //Obtem parameters e seta na sessao para poder fazer a acao de busca apos editar o material.
        $_REQUEST['parameters'] = $data->_parameters;
        parent::tbBtnSave_click($sender, $data, $errors);
    }    
}
?>
