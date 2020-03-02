<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * This file groups
 *
 * @author Leovan Tavares da Silva [leovan] [leovan@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 14/06/2006
 *
 **/

#FIXME: Utilizar por enquanto, futuramente esta tela sera alterada
//para usar 1gb no formulário de permissoes
ini_set('memory_limit', '1024M');
//aumenta o tamanho maximo de post de itens
ini_set('max_input_vars', 5000);

$home = 'main:groups';
$navbar->AddOption(_M('Grupos',$module),$module,$self);
$ui = $MIOLO->GetUI();

$function = MIOLO::_REQUEST('function');
$data->home = $home;

if ($function == 'insert' || $function == 'update' || $function == 'delete')
{
    $MIOLO->checkAccess('frmGroup', A_ADMIN, true, true);
    $content = $ui->GetForm($module,'frmGroup');
}
else
{
    $MIOLO->checkAccess('frmGroup', A_ADMIN, true, true);
    $content = $ui->GetForm($module,'frmGroupSearch', $data);
}

//Theme receives the contents of the form
if ($theme->page->generateMethod != 'generateAjax')
{
    $theme->clearContent();
    $theme->insertContent($content);
}

?>
