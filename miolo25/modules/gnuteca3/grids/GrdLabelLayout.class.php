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
 * Grid for Label Layout Listing
 *
 * @author Luiz G. Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 29/07/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/

class GrdLabelLayout extends GSearchGrid
{
    /**
     * Class Attributes
     */

    public  $MIOLO,
            $module,
            $home;

    public $columns;

    /**
     * Constructor Method
     *
     */

    function __construct($data)
    {
        //$this->getInstance();
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->home     = 'main:configuration:labelLayout';
        
//        $this->getColumns();
        $this->columns = array
        (
            new MGridColumn( _M('Código',                 $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Descrição',          $this->module), MGrid::ALIGN_LEFT,     null, null, true, null, true ),
            new MGridColumn( _M('Margem superior',           $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Margem esquerda',          $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Espaco Vertical',     $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Espaço horizontal',   $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Altura',               $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Largura',                $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Linhas',                $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Colunas',              $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Formato da página',          $this->module), MGrid::ALIGN_CENTER,   null, null, true, null, true ),
        );

        parent::__construct($data, $this->columns);

        //$this->getOptions();
        $args_upd = array
        (
            'function'      => 'update',
            'labelLayoutId' => '%0%',
        );

        $href_upd = $this->MIOLO->getActionURL(
            $this->module,
            $this->MIOLO->getCurrentAction(),
            null,
            $args_upd
        );

        $args_del = array(
            'function'      => 'delete',
            'labelLayoutId' => '%0%',
        );

        $this->setIsScrollable();

        $this->addActionUpdate( $href_upd );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args_del) );
    }
  
}
?>
