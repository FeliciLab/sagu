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
 * Class created on 08/04/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/

class GrdRequestChangeExemplaryStatusAccess extends GSearchGrid
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
        $this->home     = 'main:configuration:requestChangeExemplaryStatus';
        $this->columns = array
        (
            new MGridColumn(_M('Código do grupo', $this->module),       MGrid::ALIGN_RIGHT,  null, null, false,  null, true),
            new MGridColumn(_M('Grupo', $this->module),            MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Código do estado do exemplar', $this->module),       MGrid::ALIGN_RIGHT,  null, null, false,  null, true),
            new MGridColumn(_M('Estado do exemplar',      $this->module), MGrid::ALIGN_LEFT,     null, null, true, null, true ),
        );

        parent::__construct($data, $this->columns);

        $args_upd = array
        (
            'function'      => 'update',
            'basLinkId'     => '%0%',
        );

        $href_upd = $this->MIOLO->getActionURL
        (
            $this->module,
            $this->MIOLO->getCurrentAction(),
            null,
            $args_upd
        );
        $this->addActionUpdate( $href_upd );

        $args_del = array
        (
            'function'              => 'delete',
            'basLinkId'             => '%0%',
            'exemplaryStatusId'     => '%2%',
        );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args_del) );
        $this->setIsScrollable();
    }

    /**
     * Este generate é especifico para a grid de acesso de alteração de estados de exemplar, porque os itens que
     * vão ser excluídos precisam ser encontrados manualmente na grid.
     *
     * @return string
     */

    public function generate()
    {
        $this->setPrimaryKey(array('basLinkId'=> '0', 'exemplaryStatusId'=>'2')); //Define primarykeys com indices apontando para as colunas da grid especificas para este caso.

        return parent::generate();
    }

}
?>
