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
 * This file lookup
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
 
$MIOLO->history->pop();
$lookup = new MLookup();

$text   = MIOLO::_Request('selText');
if ( $text && $text != 'undefined')
{
    $lookup->setSelectionText( $text );
}
$MIOLO->conf->loadConf( $lookup->module );
$MIOLO->getTheme( $MIOLO->GetConf('theme.lookup') );

$file = $MIOLO->GetModulePath($lookup->module,'db/lookup.class');
if ( file_exists( $file ) )
{
   $ok = $MIOLO->Uses('/db/lookup.class',$lookup->module);
}
else
{
   $ok = $MIOLO->Uses('/classes/lookup.class',$lookup->module);
}
$MIOLO->Assert($ok,_M('Arquivo modules/@1/classes/lookup.class não encontrado.', 'admin') . '<br/>' .
                   _M('Este arquivo deve implementar a classe Business@1Lookup', 'admin', $lookup->module) .
                   _M('contendo o método Lookup@1.','admin', $lookup->item) );
$page->AddScript('m_lookup.js');
$page->SetTitle(_M('Janela de busca','admin'));
$businessClass = "Business{$lookup->module}Lookup";
$lookupMethod = "Lookup{$lookup->item}";

//Carregar autoload
require_once $MIOLO->getConf('home.modules') . '/modules.inc';

$object = new $businessClass();
$object->$lookupMethod($lookup);
$page->SetAction($lookup->href);

$theme = $MIOLO->GetTheme($MIOLO->GetConf('theme.lookup'),'lookup');
$theme->SetContent($lookup->generate( ));
$theme->SetLayout('lookup');
?>

