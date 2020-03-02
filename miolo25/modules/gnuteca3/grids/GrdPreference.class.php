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
 * Class created on 28/07/2008
 *
 **/
class GrdPreference extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $columns = array(
            new MGridColumn( _M('Módulo', $this->module),      MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Parâmetro', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Conteúdo', $this->module),    MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Descrição', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Tipo', $this->module),        MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Grupo', $this->module),       MGrid::ALIGN_LEFT,  null, null, true, BusinessGnuteca3BusDomain::listForSelect('ABAS_PREFERENCIA', false, true), true ),
            new MGridColumn( _M('Ordem', $this->module),       MGrid::ALIGN_LEFT,  null, null, false, null, true ),
            new MGridColumn( _M('Etiqueta', $this->module),    MGrid::ALIGN_LEFT,  null, null, true, null, true ),
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'      => 'update',
            'moduleConfig'  => '%0%',
            'parameter'     => '%1%'
        );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        $args = array(
            'function'      => 'delete',
            'event'         =>'tbBtnDelete_click',
            'moduleConfig'  => '%0%',
            'parameter'     => '%1%'
        );

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setRowMethod($this, 'rowMethod');
    }

    public function rowMethod($i, $row, $actions, $columns)
    {
        $columns[2]->control[$i]->setValue('<pre>'.htmlentities($columns[2]->control[$i]->getValue(),null, 'UTF-8' ).'</pre>'  );
    }
}
?>