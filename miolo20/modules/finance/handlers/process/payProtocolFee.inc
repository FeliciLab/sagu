<?php

/**
 * <--- Copyright 2011-2011 de Facimed - Faculdade de Ciências Biométricas de Cacoal.
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
 * Finance income forecast generation handler.
 * Contains the menus to access income forecast generation process
 *
 * @author Leovan Tavares da Silva [leovan@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Equipe SAGU [sagu2@solis.coop.br]
 *
 * @since
 * Class created on 24/03/2011
 *
 **/

if (MIOLO::_REQUEST('event') == 'downloadVoucher_click')
{
    $voucher = MIOLO::_request('voucher');
    $voucher = unserialize(base64_decode($voucher));
    SAGU::returnAsFile('comprovante.txt', $voucher, 'application/txt');
}
else
{
    $module = MIOLO::getCurrentModule();
    $action = 'main:process:payProtocolFee';
    $title = _M('Receber taxa', $module);
    $managementForm = 'FrmPayProtocolFee';
    $searchForm = 'FrmPayProtocolFee';

    SAGU::handle($module, $action, $title, $searchForm, $managementForm, array('checkAccess' => FALSE));
}

?>
