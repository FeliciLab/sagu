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
 * @author Nataniel I. da Silva [nataniel@solis.com.br]
 *
 * @version $Id$
 *
 * @since
 * Class created on 08/07/2014
 */

define('TRANSFERENCIA_EM_LOTE', 'em_lote');
define('TRANSFERENCIA_UNITARIA', 'unitaria');

$module = SAGU::getFileModule(__FILE__);
$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmTransferencia', A_ADMIN, true, true);

    $content = $ui->getForm($module,'FrmTransferenciaBusca', $data);
    $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
}
else
{
    $i=1;
    $steps[$i++] = new SStepInfo('FrmTransferenciaDadosContratuais', _M('Dados contratuais', $module), $module);
    $steps[$i++] = new SStepInfo('FrmTransferenciaNotas', _M('Notas e avaliações', $module), $module);
    $steps[$i++] = new SStepInfo('FrmTransferenciaResumo', _M('Resumo', $module), $module);

    /*SAGU::handle($module, $action, _M('Transferência de ocorrência de curso', $module), null, $steps, array(
        'transactionName' => 'FrmTransferencia',
    ));
    
     * 
     */
    $content = SStepByStepForm::getCurrentForm($steps);
}

$theme->clearContent();
$theme->insertContent($content);

?>

