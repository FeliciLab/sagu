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
$MIOLO->getClass('gnuteca3', 'backgroundTasks/GBackgroundTask');

class GrdBackgroundTaskLog extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $statusList;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $busLibraryUnit = $this->MIOLO->getBusiness('gnuteca3','BusLibraryUnit');
        $busLibraryUnit = new BusinessGnuteca3BusLibraryUnit();

        $this->statusList = BusinessGnuteca3BusDomain::listForSelect('BACKGROUND_TASK_STATUS',false,true);

        $libraryList = $busLibraryUnit->listLibraryUnit();

        //transforma numa lista linear para ser utilizada na coluna
        if ( is_array( $libraryList ) )
        {
            foreach ( $libraryList as $line => $info)
            {
                $newList[ $info[0] ]  = $info[1];
            }
        }

        $libraryList = $newList;

        $columns = array(
            new MGridColumn( _M('Código', $this->module),MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Data inicial', $this->module),         MGrid::ALIGN_LEFT,  null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn( _M('Data final', $this->module),        MGrid::ALIGN_LEFT,  null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn( _M('Tarefa', $this->module),  MGrid::ALIGN_LEFT,  null, null, false, null, false ),
            new MGridColumn( _M('Tarefa', $this->module),        MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Estado', $this->module),       MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Mensagem', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Operador', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Argumentos', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Unidade', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, $libraryList, true )
        );

        parent::__construct($data, $columns);
        $this->setIsScrollable();
        $this->setRowMethod($this, 'checkValues');

        $this->addActionIcon( _M('Reenviar email','gnuteca3'), Gutil::getImageTheme('email-16x16.png'), Gutil::getAjax('reExecuteBackgroundTask',array( 'backgroundTaskLogId' => '%0%') ) );
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        $beginDate = new GDate($columns[1]->control[$i]->getValue());

        $columns[1]->control[$i]->setValue($beginDate->getDate(GDate::MASK_TIMESTAMP_USER));

        $endDate = new GDate($columns[2]->control[$i]->getValue());

        $columns[2]->control[$i]->setValue($endDate->getDate(GDate::MASK_TIMESTAMP_USER));

        $status = $columns[5]->control[$i]->getValue();

        if ($status == GBackgroundTask::STATUS_SUCESS || $status == GBackgroundTask::STATUS_RESUCESS )
        {
            $status = '<span style="color:green;font-weight:bold">'.$this->statusList[$status].'</span>';
        }

        if ($status == GBackgroundTask::STATUS_ERROR || $status == GBackgroundTask::STATUS_RERROR  )
        {
            $status = '<span style="color:red;font-weight:bold">'.$this->statusList[$status].'</span>';
        }

        if ($status == GBackgroundTask::STATUS_EXECUTION || $status == GBackgroundTask::STATUS_REEXECUTION )
        {
            $status = '<span style="color:blue;font-weight:bold">'.$this->statusList[$status].'</span>';
        }

        $columns[5]->control[$i]->setValue($status);

        $arguments = $columns[8]->control[$i]->getValue();
        $arguments = unserialize($arguments);

        if ( $arguments )
        {
            $arguments = var_export($arguments ,true);
            $MIOLO = MIOLO::getInstance();
            $MIOLO->getClass( 'gnuteca3' ,'controls/GTree' );

            $treeData[0]->content = '<pre>'.$arguments.'</pre>';
            $treeData[0]->title = _M('Argumentos', $module);

            $tree = new GTree('tree'.$i , $treeData);
            $tree->setClosed(true);

            $columns[8]->control[$i]->setValue($tree->generate());
        }
    }

    /**
     * REexecuta tarefa em segundo plano
     *
     * @param stdClass $args do miolo
     */
    public static function reExecuteBackgroundTask( $args )
    {
        try
        {
            $ok = GBackgroundTask::reExecuteTask( $args->backgroundTaskLogId );

            if ( $ok == GBackgroundTask::STATUS_SUCESS || $ok == GBackgroundTask::STATUS_RESUCESS )
            {
                GForm::information( _M('Tarefa reexecutada com sucesso!', 'gnuteca3') );
            }
        }
        catch ( Exception $e )
        {
            throw new Exception ( _M('Falha na reexecucação da tarefa!', 'gnuteca3'). ' '. $e->getMessage() );
        }
    }
}
?>