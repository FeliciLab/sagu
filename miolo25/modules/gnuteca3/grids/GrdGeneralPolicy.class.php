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
 * Class created on 04/08/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdGeneralPolicy extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $home           = 'main:configuration:policy';
        $columns = array(
            new MGridColumn(    'Privilege group code',                           MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Grupo de privilégio', $this->module),                MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Código do vínculo', $this->module),                        MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Vínculo', $this->module),                           MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Limite de empréstimo', $this->module),                  MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de reserva', $this->module),               MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de reserva para nível inicial', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true )
        );
        parent::__construct($data, $columns);
        $args['function']           = 'update';
        $args['privilegeGroupId']   = '%0%';
        $args['linkId']             = '%2%';
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
    }

    /**
     * Este generate é especifico para a grid de politicas gerais, porque os itens que
     * vão ser excluídos precisam ser encontrados manualmente na grid.
     *
     * @return string
     */

    public function generate()
    {
        $this->setPrimaryKey(array('privilegeGroupId'=> '0', 'linkId'=>'2')); //Define primarykeys com indices apontando para as colunas da grid especificas para este caso.

        return parent::generate();
    }

}
?>