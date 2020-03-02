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
 * Grid
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
 * Class created on 01/08/2008
 *
 **/
class GrdConfigReport extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $group      = BusinessGnuteca3BusDomain::listForSelect('REPORT_GROUP',false,true);
        $permission = BusinessGnuteca3BusDomain::listForSelect('REPORT_PERMISSION',false,true);

        $columns = array(
            new MGridColumn(_M('Código', $this->module),       MGrid::ALIGN_RIGHT,  null, null, true, null, true),
            new MGridColumn(_M('Título', $this->module),      MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Descrição', $this->module),MGrid::ALIGN_LEFT,   null, null, false,null, true),
            new MGridColumn(_M('Permissão', $this->module), MGrid::ALIGN_LEFT,   null, null, true, $permission, true),
            new MGridColumn(_M('Sql', $this->module),        MGrid::ALIGN_LEFT,   null, null, false,null, true),
            new MGridColumn(_M('Sub sql', $this->module),    MGrid::ALIGN_LEFT,   null, null, false,null, true),
            new MGridColumn(_M('Script', $this->module),     MGrid::ALIGN_LEFT,   null, null, false,null, true),
            new MGridColumn(_M('Modelo', $this->module),      MGrid::ALIGN_LEFT,   null, null, false,null, true),
            new MGridColumn(_M('Ativo', $this->module),  MGrid::ALIGN_CENTER, null, null, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Grupo', $this->module),      MGrid::ALIGN_CENTER, null, null, true, $group , true),
        );

        parent::__construct($data, $columns);

        $args = array('function' => 'update','reportId' => '%0%');
        $this->setIsScrollable();
        $this->addActionUpdate( $this->MIOLO->getActionURL($this->module, $this->action, null, $args) );

        $args = array('function' => 'delete', 'reportId' => '%0%' );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );

        $args = array('function' => '', 'reportId' => '%0%', 'event' => 'exportReport');
        $this->addActionIcon(_M('Exportar', $this->module), GUtil::getImageTheme('download-16x16.png'), GUtil::getAjax('exportReport', $args) );;
    }
}
?>