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
 * Class created on 08/12/2008
 **/
$content            = null;
$busOperationLoan   = $MIOLO->getBusiness($module,'BusOperationLoan');
$content            = $busOperationLoan->getReceiptsText();

$theme->clearContent();
$txt = new MText('txt');
$txt->setValue('<pre>'.$content.'</pre>');
$MIOLO->page->onload("document.getElementById('frm__mainForm_container_top').style.display = 'none';");
$MIOLO->page->onload("document.getElementById('frm__mainForm_container_top').style.height = '0';");
$MIOLO->page->onload("document.getElementById('frm__mainForm_navbar').style.display = 'none';");
$MIOLO->page->onload("document.getElementById('frm__mainForm_navbar').style.height = '0';");
$MIOLO->page->onload("document.getElementById('frm__mainForm_bottom').style.height = '0';");
$MIOLO->page->onload("document.getElementById('frm__mainForm_bottom').style.display = 'none';");
$MIOLO->page->onload("document.getElementById('content').style.margin = '0';");
$MIOLO->page->onload("document.getElementById('txt').style.margin = '0';");

$print = MUtil::getBooleanValue( MIOLO::_REQUEST('print') ? MIOLO::_REQUEST('print') : true );
$close = MUtil::getBooleanValue( MIOLO::_REQUEST('close') ? MIOLO::_REQUEST('close') : true );

if ( $print )
{
    $MIOLO->page->onload("print();");
}
if ( $close )
{
    $MIOLO->page->onload("close();");
}

$theme->insertContent( $txt);

?>