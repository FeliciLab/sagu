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
 * Grid News
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
 * Class created on 19/03/2009
 *
 **/
class GrdNews extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $data;
    public $busNews;
    public $busLibrayUnit;


    public function __construct($data)
    {
        $this->MIOLO         = MIOLO::getInstance();
        $this->module        = MIOLO::getCurrentModule();
        $this->action        = MIOLO::getCurrentAction();
        $this->busNews       = $this->MIOLO->getBusiness($this->module, 'BusNews');
        $this->busLibrayUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

        $columns = array(
            new MGridColumn( _M('Código', $this->module),                MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Lugar', $this->module),                 MGrid::ALIGN_CENTER,  null, null, true, $this->busNews->listPlace(), true ),
            new MGridColumn( _M('Título', $this->module),                MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Notícias', $this->module),              MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Data', $this->module),                  MGrid::ALIGN_LEFT,  null, null, true, null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Data inicial', $this->module),          MGrid::ALIGN_LEFT,  null, null, true, null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Data final', $this->module),            MGrid::ALIGN_LEFT,  null, null, true, null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Operador', $this->module),              MGrid::ALIGN_LEFT,  null, null, false, null, true ),
            new MGridColumn(_M('É restrita',  $this->module),            MGrid::ALIGN_CENTER,null, null, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Está ativo',  $this->module),            MGrid::ALIGN_CENTER,null, null, true, GUtil::getYesNo(), true),
            new MGridColumn( _M('Unidade de biblioteca', $this->module), MGrid::ALIGN_LEFT,  null, null, true, $this->busLibrayUnit->listLibraryUnitAssociate(), true ),
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'    => 'update',
            'newsId'      => '%0%'
        );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $this->addActionUpdate( $hrefUpdate );

        $args = array(
            'function'    => 'delete',
            'newsId'      => '%0%'
        );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );

        $args['function'] = 'detail';

        $this->setIsScrollable();

        $imgGroups  = GUtil::getImageTheme('libraryGroup-16x16.png');
        $this->addActionIcon(_M('Grupos', $this->module), $imgGroups, GUtil::getAjax('showGroups', $args) );

        $this->setIsScrollable();
        
        $this->setRowMethod($this, 'checkValues');
    }
    public function checkValues($i, $row, $actions, $columns)
    {
        $data = new GDate($columns[4]->control[$i]->value);
        $columns[4]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));

        $data = new GDate($columns[5]->control[$i]->value);
        $columns[5]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));

        $data = new GDate($columns[6]->control[$i]->value);
        $columns[6]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
    }
}
?>
