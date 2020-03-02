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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
  * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/01/2011
 *
 **/
class FrmBackgroundTaskLogSearch extends GForm
{
    public function __construct()
    {
    	$this->setAllFunctions('BackgroundTaskLog', array('backgroundTaskLogId'), 'backgroundTaskLogId');
    	parent::__construct();
    }

    
    public function mainFields()
    {
    	$fields[] = new MIntegerField('backgroundTaskLogIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        
    	$labBeginDate = new MLabel(_M('Data inicial', $this->module));
        $beginsDate = new MTimesTampField('beginDateS');
        $beginsDateFinal = new MTimesTampField('beginDateFinalS');
        $fields[] = new GContainer('containerDateBegin', array ($labBeginDate, $beginsDate, $beginsDateFinal));
        
        $labFinalDate = new MLabel('Data final', $this->module);
        $endDate = new MTimesTampField('endDateS');
        $endDateFinal = new MTimesTampField('endDateFinalS');
        $fields[] = new GContainer ('containerDateEnd', array($labFinalDate, $endDate, $endDateFinal));
        
        
        $fields[] = new MTextField('labelS', null, _M('Tarefa', $this->module) , FIELD_DESCRIPTION_SIZE );
        $fields[] = new GSelection('statusS', null, _M('Estado', $this->module), BusinessGnuteca3BusDomain::listForSelect('BACKGROUND_TASK_STATUS'));
        $fields[] = new MTextField('messageS', null, _M('Mensagem', $this->module) , FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTextField('operatorS', null, _M('Operador', $this->module) , FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTextField('argsS', null, _M('Argumentos', $this->module) , FIELD_DESCRIPTION_SIZE );

        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $busLibraryUnit->filterOperator = TRUE;
        $busLibraryUnit->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade', $this->module), $busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);

        $this->setFields($fields);
        $this->_toolBar->disableButtons(array(MToolBar::BUTTON_NEW, MToolBar::BUTTON_DELETE));
    }

    public function reExecuteBackgroundTask(  )
    {
        $args = (object) $_REQUEST;
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getUI()->getGrid('gnuteca3', 'GrdBackgroundTaskLog');
        GrdBackgroundTaskLog::reExecuteBackgroundTask($args);
    }
}
?>