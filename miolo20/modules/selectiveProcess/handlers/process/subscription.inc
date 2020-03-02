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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 03/01/2011
 *
 **/

$module = SAGU::getFileModule(__FILE__);
$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Inscrição', $module), $module, $action);
access::insert('Inscrição', $action, 'default-16x16.png');

if ( (strlen($function) == 0) || ($function == 'search') )
{
    // faz checkAccess somente se login.check for true
    $MIOLO->checkAccess('FrmSubscription', A_ACCESS, true, true);
    $content = $ui->getForm($module, 'FrmSubscriptionSearch', $data);
}
else
{
    //Faz checkAccess somente se chamada não for do módulo de serviço
    if( !SAGU::userIsFromServices() )
    {
        // faz checkAccess somente se login.check for true
        switch ( $function )
        {
            case 'insert':
                if ( $MIOLO->getConf('login.check') == 'true' )
                {
                    $MIOLO->checkAccess('FrmSubscription', A_INSERT, true, true);
                }
            break;
            case 'update':
                if (strlen(MIOLO::_request('event')) > 0)
                {
                    $MIOLO->checkAccess('FrmSubscription', A_UPDATE, true, true);
                }
                else
                {
                    $MIOLO->checkAccess('FrmSubscription', A_ACCESS, true, true);
                }
            break;
            case 'delete':
                $MIOLO->checkAccess('FrmSubscription', A_DELETE, true, true);
            break;
        }
    }
    
    $i=1;
    $steps[$i++] = new SStepInfo('FrmSubscription', _M('Inscrição', $module), $module);
    // Condicao recebida de FrmSubscription
    if ( strlen(MIOLO::_REQUEST('step1done')) > 0 )
    {
        //Se tiver no módulo de serviço entra no passo de autenticação de usuário
        if( SAGU::userIsFromServices() && (!strlen($MIOLO->getLogin()->id)>0))
        {
            $steps[$i++] = new SStepInfo('FrmSubscriptionAuthentication', _M('Identificação', $module), $module);
        }     
        
        if (SprSelectiveProcess::verificarExisteMensagemInicial(MIOLO::_REQUEST('stepId')))
        {
            $steps[$i++] = new SStepInfo('FrmWelcomeMessage', _M('Mensagem de boas vindas', $module), $module);
        }
        
        $steps[$i++] = new SStepInfo('FrmPersonalInformation', _M('Informações pessoais', $module), $module);
        // Condicao recebida de FrmSubscription
        if ( !(strlen(MIOLO::_REQUEST('disable_document')) > 0) )
        {
            $steps[$i++] = new SStepInfo('FrmSubscriptionDocument', _M('Documentos necessários', $module), $module);
        }
        $steps[$i++] = new SStepInfo('FrmSubscriptionOption', _M('Opções de curso', $module), $module);
        // Condição recebida de FrmSubscription
        if ( !(strlen(MIOLO::_REQUEST('disable_socioeconomic')) > 0) )
        {
            $steps[$i++] = new SStepInfo('FrmSocioeconomicQuestion', _M('Questionário socioeconômico', $module), $module);
        }
        $steps[$i++] = new SStepInfo('FrmSubscriptionFinalize', _M('Fim', $module), $module);
    }

    $content = SStepByStepForm::getCurrentForm($steps);
}

$theme->clearContent();
$theme->insertContent($content);
?>