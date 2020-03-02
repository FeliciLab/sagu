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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 08/01/2009
 *
 **/
class GAddChildGrid extends GGrid
{
    /**
     * Ajusta a acao de adicionar filho na grid dependendo do tipo de material e permissao
     *
     * @param GGridActionIcon $action
     * @param unknown_type $controlNumber
     */
    public function adjustChildAction($action, $controlNumber)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        if (!$controlNumber || !$action)
        {
            return;
        }

        //tendo permissão de insert na gtcmaterial ele pega todos os menus em um único sql, guardando em um array
        if ( $this->getMaterialPermission() && !$this->categoryAndLevelMenus )
        {
            if (!$this->busSpreadSheet)
            {
                $this->busSpreadSheet = $MIOLO->getBusiness($module, 'BusSpreadsheet');
            }
            $menus = $this->busSpreadSheet->getMenus( null, null, false ); //select
            if ( is_array( $menus ))
            {
                foreach ( $menus as $line => $menu )
                {
                    $this->categoryAndLevelMenus[$menu->category][$menu->level] = $menu;
                }
            }
        }

        if (!$this->busMaterialControl)
        {
            $this->busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
        }

        //obtém o tipo do material
        if ( $type = $this->busMaterialControl->getTypeOfMaterial($controlNumber) )
        {
            //aqui os selects estão com else, então só executa 1
            if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_BOOK )
            {
                $menu = $this->categoryAndLevelMenus['BA']['4'];
            }
            else
            if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION )
            {
                $menu = $this->categoryAndLevelMenus['SE']['4'];
            }
            else
            if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION_FASCICLE )
            {
                $menu = $this->categoryAndLevelMenus['SA']['4'];
            }

            $extraFields[]   = new MSeparator('<br/>');

            //adiciona o leaderString na URL da ação
            if ($menu)
            {
                $menuoption = str_replace("#", "*", $menu->menuoption);
                $pos = strpos($action->href, 'leaderString=');
                
                if ($pos)
                {
                    $action->href = str_replace('leaderString=', 'leaderString=' . $menuoption, $action->href);
                }
            }
            $action->enable();
        }
        else
        {
            $action->disable();
        }
    }
}
?>