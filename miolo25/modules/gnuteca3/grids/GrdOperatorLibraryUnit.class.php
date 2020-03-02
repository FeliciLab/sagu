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
 * Class created on 06/01/2009
 *
 **/
class GrdOperatorLibraryUnit extends GSearchGrid
{
    public function __construct($data)
    {
    	global $MIOLO, $module, $action;

        $columns = array(
            new MGridColumn(_M('idUser',  $module), MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Operador',  $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Nome', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Bibliotecas', $module), MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Bibliotecas', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
        );

        parent::__construct($data, $columns);

        $args['idUser'] = '%0%';
        $args['operator'] = '%1%';
        $args['function'] = 'update';
        $hrefUpdate = $MIOLO->getActionURL($module, $action, null, $args);
        $args['function'] = 'delete';
        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setRowMethod($this, 'checkValues');
    }


    public function checkValues($i, $row, $actions, $columns)
    {
    	if ( !$columns[4]->control[$i]->value )
    	{
    		$columns[4]->control[$i]->setValue( _M('Todas bibliotecas', $this->module) );
    	}
    }

}
?>
