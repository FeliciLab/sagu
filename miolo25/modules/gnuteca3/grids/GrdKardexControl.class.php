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
 * @author Luiz Gilberto Gregory F [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 18/11/2008
 *
 **/
class GrdKardexControl extends GSearchGrid
{
    public $actionAddChild;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->setColumns( array(
            new MGridColumn(_M('Número de controle',    $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Informações',      $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true)
        ) );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, array ( 'function' => 'update', 'controlNumber' => '%0%') );
        
        //troca as permissões a fim de bloquear update e delete com gtcKardexControl
        $this->transaction = 'gtcMaterial';
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', array ( 'function' => 'delete','controlNumber' => '%0%' )) );
        $this->transaction = 'gtcKardexControl';

        $hrefAddChild = $this->MIOLO->getActionURL($this->module, $this->action, null, array ( 'function' => 'addChildren', 'controlNumber' => '%0%','leaderString' => '' ));

        if ( GPerms::checkAccess('gtcMaterial', 'insert', false ) )
        {
            $this->actionAddChild = $this->addActionIcon( _M('Adicionar fascículo', $this->module), GUtil::getImageTheme('addChild-16x16.png'), $hrefAddChild );
        }

        $this->addActionIcon(_M('Detalhes', $this->module), GUtil::getImageTheme('detail-16x16.png'), GUtil::getAjax('openMaterialDetail', '%0%' ) );

        $this->setRowMethod($this, 'checkValues');
        $this->setIsScrollable();
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        $controlNumber = $columns[0]->control[$i]->getValue();
        $this->adjustChildAction( $this->actionAddChild , $controlNumber );
    }
}
?>