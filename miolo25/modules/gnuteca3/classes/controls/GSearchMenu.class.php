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
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @since
 * Class created on 06/11/2008
 *
 **/

class GSearchMenu extends MDiv
{
	public function __construct()
	{
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $busFormContent = $MIOLO->getBusiness($module, 'BusFormContent');
        $busFormContent->formContentType = FORM_CONTENT_TYPE_ADMINISTRATOR;
        $search = $busFormContent->searchFormContent(TRUE);

        $div = new MDiv('myLibrary',null, 'GSearchMenuMyLibrary');
        $inner['myLibrary'] = new MLink('linkMyLibrary', '', 'javascript:' . GUtil::getAjax('subForm', 'MyLibrary'), $div->generate());
        
	    if ( is_array( $search ) )
        {
            foreach ( $search as $line => $info )
            {
            	if ($info->name != 'materialMovement')
            	{
	                $url = 'index.php?module='.$module.'&action=main:search:simpleSearch&formContentId='.$info->formContentId.'&formContentTypeId='.$info->formContentType;
	                $link = new MLink('link'.$info->formContentId, $info->name, $url);
	                $link->setClass('GSearchMenuButton');
                    $link->addAttribute('tabindex','10');
	                $inner[] = new MDiv($info->formContentId, $link);
	                $inner[] = new MDiv(null, null, 'GSearchMenuSeparator');
            	}
            }
        }

        if ( MUtil::getBooleanValue(GB_INTEGRATION ) )
        {
            $url = $url = 'index.php?module='.$module.'&action=main:search:simpleSearch&subForm=GoogleBook';
            $link = new MLink('linkGoogleBook', 'GoogleBook', $url );
            $link->setClass('GSearchMenuButton');
            $link->addAttribute('tabindex','10');
            $inner[] = new MDiv('divLinkGoogleBook', $link);
            $inner[] = new MDiv(null, null, 'GSearchMenuSeparator');
        }

        if ( GPerms::checkAccess('gtcZ3950', null, false) )
        {
            $url = $url = 'index.php?module='.$module.'&action=main:search:simpleSearch&subForm=Z3950';
            $link = new MLink('linkZ3950', 'Z3950', $url );
            $link->addAttribute('tabindex','10');
            $link->setClass('GSearchMenuButton');
            $inner[] = new MDiv('divLinkZ3950', $link);
            $inner[] = new MDiv(null, null, 'GSearchMenuSeparator');
        }

        if ( MUtil::getBooleanValue(FBN_INTEGRATION) )
        {
            $url = 'index.php?module='.$module.'&action=main:search:simpleSearch&subForm=FBN';
            $link = new MLink('linkFBN', 'B. Nacional', $url );
            $link->addAttribute('tabindex','10');
            $link->setClass('GSearchMenuButton');
            $inner[] = new MDiv('divLinkFBN', $link);
            $inner[] = new MDiv(null, null, 'GSearchMenuSeparator');
        }

        parent::__construct('searchListPanel', $inner,  'm-panel-body GSearchMenu');
	}
}
?>