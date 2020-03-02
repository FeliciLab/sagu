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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GTree');

$possibleEvents = array('reExecuteBackgroundTask');
$event = GUtil::getAjaxFunction();

if ( in_array( $event, $possibleEvents))
{
    try
    {
        BackgroundTaskLogWidget::$event((object)$_REQUEST);
    }
    catch ( Exception $e )
    {
        GForm::error( $e->getMessage() );
    }
}

class BackgroundTaskLogWidget extends GWidget
{
    public $busNews;

    public function __construct()
    {
        $this->module   = 'gnuteca3';
        parent::__construct('BackgroundTaskLogWidget', _M('Tarefas em segundo plano que falharam hoje', $this->module), "javascript:alert('');",'news-16x16.png' );
        $this->setTransaction('gtcBackgroundTaskLog');
    }

    public function widget()
    {
        $this->manager->getClass('gnuteca3', 'backgroundTasks/GBackgroundTask');
        
        $bus      = $this->manager->getBusiness($this->module, 'BusBackgroundTaskLog');
        $bus      = new BusinessGnuteca3BusBackgroundTaskLog();

        //utilizado para que o filtro da busca seja efetuado e o pdf e csv funcionem corretamente
        $fields[] = new MHiddenField('status[]', GBackgroundTask::STATUS_ERROR);
        $fields[] = new MHiddenField('status[]', GBackgroundTask::STATUS_RERROR);
        $fields[] = new MHiddenField('beginDateS', GDate::now()->getDate(GDate::MASK_DATE_DB));
        $bus->statusS = array( GBackgroundTask::STATUS_ERROR, GBackgroundTask::STATUS_RERROR );
        $bus->beginDateS = GDate::now()->getDate(GDate::MASK_DATE_DB);

        $data = $bus->searchBackgroundTaskLog();

        if ( count($data) > 0 )
        {
            $grid = $this->manager->getUI()->getGrid($this->module, 'GrdBackgroundTaskLog');
            $grid->pageLength = 0;
            $grid->setData( $data );

            $treeData[0]->content = $grid;
            $treeData[0]->title = _M('Visualizar','gnuteca3');

            $tree = new GTree( 'sdfsfd' , $treeData);
            $tree->setClosed(true);

            $fields[] = $tree;
        }
        else
        {
            $fields[] = new MDiv('','Sem falhas.');
        }

        $this->setControls($fields);
    }

    public function reExecuteBackgroundTask( $args )
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getUI()->getGrid('gnuteca3', 'GrdBackgroundTaskLog');
        GrdBackgroundTaskLog::reExecuteBackgroundTask($args);

        $MIOLO->ajax->setResponse( new BackgroundTaskLogWidget(), 'backgroundTaskContainer' ); //atualiza o widget
    }
}
?>