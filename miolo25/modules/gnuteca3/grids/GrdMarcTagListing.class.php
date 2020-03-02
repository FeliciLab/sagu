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
 * Grid for Marc Tag Listing
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
 * Class created on 01/ago/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/

class GrdMarcTagListing extends GSearchGrid
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
        $this->home     = 'main:configuration:marcTagListing';

        $this->columns = array
        (
            new MGridColumn( _M('Código',         $this->module), MGrid::ALIGN_RIGHT,    null, null, true, null, true ),
            new MGridColumn( _M('Descrição',  $this->module), MGrid::ALIGN_LEFT,     null, null, true, null, true ),
        );

        parent::__construct($data, $this->columns);

        $args_upd = array
        (
            'function'          => 'update',
            'marcTagListingId'  => '%0%',
        );

        $href_upd = $this->MIOLO->getActionURL(
            $this->module,
            $this->MIOLO->getCurrentAction(),
            null,
            $args_upd
        );

        $args_del = array(
            'function'          => 'delete',
            'marcTagListingId'  => '%0%',
        );

        $this->setIsScrollable();

        $this->addActionUpdate( $href_upd );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args_del) );

        $args['function']           = 'detail';
        $args['item']               = '%0%';

        $img  = GUtil::getImageTheme('report-16x16.png');
        $this->addActionIcon(_M('Opções de listagem para campos Marc', $this->module), $img, GUtil::getAjax('showOptions', $args) );
    }

    
}
?>