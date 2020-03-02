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
 * Class created on 25/09/2008
 *
 **/
class GrdSpreadsheet extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->MIOLO->getClass($this->module, 'controls/GTree');
        $columns = array(
            new MGridColumn(_M('Categoria', $this->module),               MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Nível', $this->module),                  MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Planilha', $this->module),                  MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Validador da obra', $this->module),         MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Validador de campos repetitivos', $this->module), MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Valor padrão', $this->module),          MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Nome', $this->module),     MGrid::ALIGN_LEFT, null, null, true, null, true),
        );

        parent::__construct($data, $columns);

        //Make update action
        $args = array(
            'function'  => 'update',
            'category'  => '%0%',
            'level'     => '%1%'
        );
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        //Make delete action
        $args = array(
            'function'  => 'delete',
            'category'  => '%0%',
            'level'     => '%1%'
        );

        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setIsScrollable();
        $this->setRowMethod($this, 'rowMethod');
    }




    public function rowMethod($i, $row, $actions, $columns)
    {
        $spreadsheetTree['spreadsheet']->title = _M('Ver planilha', $this->module);
        $spreadsheetTree['spreadsheet']->content = '<pre>'. nl2br($columns[2]->control[$i]->getValue() ) .'</pre>';
        $tree = new GTree('treeField', $spreadsheetTree);
        $tree->setClosed(TRUE);
        $columns[2]->control[$i]->setValue( $tree->generate() );
    }
}
?>
