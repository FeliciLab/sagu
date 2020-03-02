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
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 14/10/2010
 *
 **/
class GrdFile extends GSearchGrid
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
            new MGridColumn(_M('Absolute', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Diretório', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Basename', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Nome do arquivo', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Extensão', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Tamanho', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Tipo', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Tipo mime', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Vínculo', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Última alteração', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
        );

        parent::__construct($data, $columns);
        $args = array( 'function' => 'delete','filePath'=> '%0%' );

        $this->addActionIcon( _M('Download', $this->module), GUtil::getImageTheme('table-down.png'), GUtil::getAjax('gridDownloadFile',"%1%/%2%"));
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setIsScrollable();
        $this->setRowMethod($this, 'checkValues');
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        //desativa as ações caso seja diretório
        if ( $columns[6]->control[$i]->getValue() == 'dir' )
        {
            $actions[0]->disable();
            $actions[1]->disable();
        }
        else
        {
            $actions[0]->enable();
            $actions[1]->enable();
        }
    }


}
?>
