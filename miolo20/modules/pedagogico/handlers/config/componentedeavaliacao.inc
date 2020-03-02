<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda.
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
 * Handler Modelos de Avaliação
 *
 * @author Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @since
 * Class created on 29/08/2013
 */

$module = MIOLO::getCurrentModule();
$title = _M('Componente de avaliação', $module);

$modeloDeAvaliacaoId = MIOLO::_REQUEST('modeloDeAvaliacaoId');
$modeloDeAvaliacao = new AcpModeloDeAvaliacao($modeloDeAvaliacaoId);

switch ( $modeloDeAvaliacao->tipoDeDados )
{
    case AcpModeloDeAvaliacao::TIPO_NOTA:
        $form = 'FrmComponenteDeAvaliacaoNota';
    break;
    case AcpModeloDeAvaliacao::TIPO_CONCEITO:
        $form = 'FrmComponenteDeAvaliacaoConceito';
    break;
    case AcpModeloDeAvaliacao::TIPO_PARECER:
        $form = 'FrmComponenteDeAvaliacaoParecer';
    break;
    case AcpModeloDeAvaliacao::TIPO_NENHUM:
        $form = 'FrmComponenteDeAvaliacaoNenhum';
    break;
}
SAGU::handle($module, $action, $title, $form, $form, array('transactionName' => 'FrmModeloDeAvaliacao'));
?>