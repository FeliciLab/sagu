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
 * @author Luiz Gilberto Gregory Filho
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @since
 * Class created on 08/10/2008
 *
 **/
$function   = MIOLO::_REQUEST('function');

//Sempre que não for pesquisa ou delete ou vier com algo na função
if ( ( $function != 'search') &&  ( $function != 'delete') && (strlen($function) > 0) )
{
    $data->controlNumber = MIOLO::_REQUEST('controlNumber');
    $frm = 'FrmMaterial';
}
else
{
    $frm = 'FrmMaterialSearch';
}

$content = $MIOLO->getUI()->getForm( $module, $frm, $data );

//modificado em função das permissões
if ( $function == 'new' || $function == 'addChildren' || $function == 'addFasciculo' )
{
    $function = 'insert';
}

try
{
    if ( $content->checkAccess() )
    {
        $theme->setContent($content);
        createBreadCrumb();
    }
    else
    {
        $error = Prompt::error(_M('É necessário possuir a permissão "gtcMaterial" ou "gtcPreCatalogue" para acessar o formulário!', 'gnuteca3'), $go, _M('Erro de permissão', 'gnuteca3'));
        $this->manager->prompt($error,$deny);
    }
}
catch (Exception $ex)
{
    GForm::error( $ex->getMessage() );
}
?>