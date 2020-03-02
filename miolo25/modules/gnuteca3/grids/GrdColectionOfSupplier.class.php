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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 20/08/2010
 *
 **/

class GrdColectionOfSupplier extends GAddChildGrid
{
    public  $MIOLO,
            $module,
            $action,
            $busSearchFormat,
            $actionAddChild;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');

        $columns = array
        (
            new MGridColumn(_M('Número de controle', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Informações', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true),
        );

        //foi posto zero para não termos problema com a paginação
        parent::__construct($data, $columns ,null, 0);
        
    	if ( GPerms::checkAccess('gtcMaterial',null, false) )
    	{
            $hrefUpdate = $this->MIOLO->getActionURL('gnuteca3', 'main:catalogue:kardexControl', null, array ( 'function'=> 'update', 'controlNumber' => '%0%' ) ) ;
	        $this->addActionIcon(_M('Atualizar', $this->module), GUtil::getImageTheme('table-edit.png') , $hrefUpdate);

            $hrefAddChild = $this->MIOLO->getActionURL('gnuteca3', 'main:catalogue:material', null, array ( 'function' => 'addChildren', 'controlNumber' => '%0%','leaderString' => '' ) ) ;
	        $this->actionAddChild = $this->addActionIcon(_M('Adicionar fascículo', $this->module), GUtil::getImageTheme('addChild-16x16.png'), $hrefAddChild);
    	}

        $this->addActionIcon(_M('Detalhes', $this->module), GUtil::getImageTheme('detail-16x16.png') , Gutil::getAjax('openMaterialDetail', '%0%' ) );

        $this->setRowMethod($this, 'checkValues');
        $this->setIsScrollable();
    }
   
   
    public function checkValues($i, $row, $actions, $columns)
    {
        $controlNumber = $columns[0]->control[$i]->getValue();
        $this->adjustChildAction($this->actionAddChild, $controlNumber);
    }
}
?>