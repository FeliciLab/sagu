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
 * Class created on 28/11/2008
 *
 **/
class GrdSearchFormat extends GSearchGrid
{
    public function __construct($data)
    {
    	global $MIOLO, $module, $action;

    	$yn = GUtil::listYesNo();
        $columns = array(
            new MGridColumn(_M('Código',          $module), MGrid::ALIGN_RIGHT,  null, null, true, null, true),
            new MGridColumn(_M('Descrição',   $module), MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('É restrita', $module), MGrid::ALIGN_CENTER, null, null, true, $yn,  true)
        );

        parent::__construct($data, $columns);

        $args['searchFormatId'] = '%0%';
        $args['function']       = 'update';
        $hrefUpdate = $MIOLO->getActionURL($module, $action, null, $args);
        $args['function']       = 'delete';

        $imgGroup    = GUtil::getImageTheme('report-16x16.png');
        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $args['function'] = 'detail';
        $this->addActionIcon(_M('Grupos', $this->module), $imgGroup, GUtil::getAjax('showGroup', $args) );
    }
}
?>
