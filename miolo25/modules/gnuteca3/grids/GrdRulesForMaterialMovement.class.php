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
 * Class created on 04/08/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdRulesForMaterialMovement extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;

    public function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $home           = 'main:configuration:rulesForMaterialMovement';
        $columns = array(
            new MGridColumn( _M('Estado atual', $this->module),    MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Estado atual', $this->module),    MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Operação', $this->module),        MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Operação', $this->module),        MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Local de circulação de material', $this->module), MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Local de circulação de material', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Estado futuro', $this->module),     MGrid::ALIGN_LEFT,   null, null, false, null, false ),
            new MGridColumn( _M('Estado futuro', $this->module),     MGrid::ALIGN_LEFT,   null, null, true, null, true )
        );
        parent::__construct($data, $columns);
        $args['function']                           = 'update';
        $args['currentState']                       = '%0%';
        $args['operationId']                        = '%2%';
        $args['locationForMaterialMovementId']      = '%4%';
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
    }

     /**
     * Este generate é especifico para a grid de regras de movimentação com material, porque os itens que
     * vão ser excluídos precisam ser encontrados manualmente na grid.
     *
     * @return string
     */

    public function generate()
    {
        $this->setPrimaryKey(array('currentState'=> '0', 'operationId'=> '2', 'locationForMaterialMovementId'=> '4' )); //Define primarykeys com indices apontando para as colunas da grid especificas para este caso.

        return parent::generate();
    }
}
?>