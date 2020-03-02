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
 * Contém as solicitações
 *
 * @author Alessandro Rodrigues da Cruz [cpd@facimed.edu.br]
 *
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 * 
 * @since
 * Class created on 19/03/2011
 *
 **/

$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Solicitação', $module), $module, $action);
access::insert('Solicitação', $action, 'default-16x16.png');

switch ( $function )
{
    case 'update':
        $MIOLO->checkAccess('ptcFrmRequest', A_ACCESS, true, true);

        $content = $ui->getForm($module,'FrmRequestEdit', $data);
    break;
    case 'delete':
        $MIOLO->checkAccess('ptcFrmRequest', A_DELETE, true, true);

        $content = $ui->getForm($module,'FrmRequestEdit', $data);
    break;
    default:
        $MIOLO->checkAccess('ptcFrmRequest', A_INSERT, true, true);

        $i=1;
        $steps[$i++] = new SStepInfo('FrmRequest',  _M('Solicitação', $module), $module);
        $steps[$i++] = new SStepInfo('FrmDocument',  _M('Documentos', $module), $module);
        
        // verifica se deve criar o passo Informacoes adicionais, de campos personalizados
        $data = sstepbystepform::getAllStepDataStatic();
        $subjectId = $data['subjectId'];
        
        if ( strlen($subjectId) > 0 )
        {
            $customFieldIds = BasCustomField::getCustomFieldIdsBySubject($subjectId);
            
            if ( count($customFieldIds) > 0 )
            {
                $steps[$i++] = new SStepInfo('FrmInfoAdicional', _M('Informações adicionais', $module), $module);
            }
        }

        $content = SStepByStepForm::getCurrentForm($steps);
    break;
}

$theme->clearContent();
$theme->insertContent($content);
?>