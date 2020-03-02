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
 * Basic employee handler.
 * Contains the menus to access person registers
 *
 * @author Alessandro Rodrigues da Cruz [cpd@facimed.edu.br]
 * 
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 * 
 * @since
 * Class created on 17/03/2011
 *
 **/

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Assunto', $module), $module, $action);
access::insert('Assunto', $action, 'default-16x16.png');

if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmSubject', A_ACCESS, true, true);

    $content = $ui->getForm($module,'FrmSubjectSearch', $data);
    $content->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
}
else
{
    switch ( $function )
    {
        case 'insert':
            $MIOLO->checkAccess('FrmSubject', A_INSERT, true, true);
        break;
        case 'update':
            if (strlen(MIOLO::_request('event')) > 0 || $_POST['__EVENTTARGETVALUE'] == 'btnSave:click')
            {
                $MIOLO->checkAccess('FrmSubject', A_UPDATE, true, true);
            }
            else
            {
                $MIOLO->checkAccess('FrmSubject', A_ACCESS, true, true);
            }
        break;
        case 'delete':
            $MIOLO->checkAccess('FrmSubject', A_DELETE, true, true);
        break;
    }

    $i=1;
    $steps[$i++] = new SStepInfo('FrmSubject',  _M('Assunto', $module), $module);
    $steps[$i++] = new SStepInfo('FrmSubjectSector',  _M('Encaminhamentos', $module), $module);
    $steps[$i++] = new SStepInfo('FrmRequiredDocument',  _M('Documentos exigidos', $module), $module);
    
    // se tem campos customizados, exibe o passo
    if ( BasCustomField::existeCamposCustomizados() )
    {
        $steps[$i++] = new SStepInfo('FrmInformacoesExtras',  _M('Informações extras', $module), $module);
    }

    $content = SStepByStepForm::getCurrentForm($steps);
}

$theme->clearContent();
$theme->insertContent($content);
?>
