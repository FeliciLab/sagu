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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 01/07/2010
 *
 **/
class GrdScheduleTask extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $home = 'main:configuration:scheduletask';

        $columns = array(
            new MGridColumn( _M('Agendar tarefa', $this->module),MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Tarefa', $this->module),         MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Ciclo', $this->module),        MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Descrição', $this->module),  MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Valor', $this->module),        MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Ativo', $this->module),       MGrid::ALIGN_LEFT,  null, null, true, GUtil::listYesNo(), true ),
            new MGridColumn( _M('Parâmetro', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true )
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'      => 'update',
            'scheduleTaskId'  => '%0%'
        );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        $args = array(
            'function'      => 'delete',
            'scheduleTaskId'  => '%0%'
        );

        $args = array(
            'function'      => 'execute',
            'scheduleTaskId'  => '%0%'
        );
        $imgExecute = GUtil::getImageTheme('execute-16x16.png');

        $imgLog = GUtil::getImageTheme('report-16x16.png');

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->addActionIcon(_M('Executar tarefa', $this->module), $imgExecute, GUtil::getAjax('executeScript', $args) );
        $this->addActionIcon(_M('Visualizar histórico', $this->module), $imgLog, GUtil::getAjax('showLogOfTask', $args) );
    }
}
?>