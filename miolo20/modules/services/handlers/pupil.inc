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
 * Pupil services handler.
 * Used to access the pupil services
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2007
 */

$home = 'main:pupil';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Serviços para o aluno', $module), $module, $home, null, array('function' => 'resetStack'));

// Obtém o usuário da pessoa logada e obtém os dados dela
$mioloUserName = trim($MIOLO->getLogin()->id);
$busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
$personData = $busPerson->getPersonByMioloUserName($mioloUserName);

$contract = new AcdContract();
$getContract = $contract->findActiveContractByPerson($personData->personId);
$contractId = $getContract->contractId;
$matricula = null;
$enrollConfig = null;

if ( strlen($contractId) > 0 )
{
    $matricula = new Matricula($contractId, Matricula::MATRICULA_NORMAL); // FIXME Deve ser visto quando pegar NORMAL e WEB.
    $enrollConfig = $matricula->obterConfiguracoes();
}

if ( !$busPerson->isPersonStudent($personData->personId) )
{
    $MIOLO->error(_M('Você deve ser aluno para acessar este conteúdo.', $module));
}
elseif ( !$MIOLO->invokeHandler($module, 'pupil/' . $context->shiftAction()) )
{
    $btnClose = $MIOLO->getActionURL('sagu2', 'main');

    $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');

    if ( is_null($personData) )
    {
        $MIOLO->error(_M('Não foi possível obter os dados do usuário @1.', $module, $mioloUserName));
    }
    else
    {
        $pnlPupil = new MActionPanel('pnlPupil', _M('Aluno', $module), NULL, $btnClose, $MIOLO->getUI()->getImage($module, 'pupil-16x16.png'));
        $pnlPupil->setControlSize('100px', '70px');

        unset($menuItem);
        $menuItem = array();

        $busContract = $MIOLO->getBusiness('academic', 'BusContract');

        $menuItem[] = array(_M('Consulta financeira', $module), 'financialConsultation-32x32.png', "$home:financialSituation", 'FrmFinancialSituation');

        // Verifica se tem contrato ativo, caso tenha libera os menus
        if ( $busContract->checkStudentActiveContract($returnMessageError = false) )
        {
            $menuItem[] = array(_M('Notas', $module), 'notes-32x32.png', "$home:notes", 'FrmNotes');
            $menuItem[] = array(_M('Frequências', $module), 'frequency-32x32.png', "$home:frequency", 'FrmFrequencyPupil');
            $menuItem[] = array(_M('Consulta acadêmica', $module), 'scholarshipDescription-32x32.png', "$home:academicConsultation", 'FrmAcademicConsultation');
            $menuItem[] = array(_M('Solicitações de serviços', $module), 'documentRequest-32x32.png', "main:documentRequest", 'FrmDocumentRequestServices');
            $menuItem[] = array(_M('Visualização de calendário acadêmico', $module), 'academicCalendar-32x32.png', "$home:academicCalendarReport", 'FrmAcademicCalendarReport');
            $menuItem[] = array(_M('Documentos pendentes', $module), 'missingDocuments-32x32.png', "$home:missingDocuments", 'FrmMissingDocuments');
            $menuItem[] = array(_M('Agenda pessoal', $module), 'personalAgenda-32x32.png', "main:personalAgenda", 'FrmPersonalAgenda');
            
            // Exibe ou nao menu de Confirmacao de matricula
            if ( $enrollConfig instanceof AcdEnrollConfig && $getContract->alunoVerificaConfirmacaoPortal($enrollConfig) )
            {
                $preMatriculas = AcdEnroll::searchPreEnrollCurricularComponents( (object) array('contractId' => $contractId) );
                
                if ( count($preMatriculas) > 0 )
                {
                    $menuItem[] = array(_M('Confirmação de matrícula', $module), 'accept32x32.png', "main:pupil:confirmEnroll", 'FrmConfirmEnroll');
                }
            }
            
            $contratos = AcdContract::listAvailableContractsForEnroll($personData);            
            foreach($contratos as $contrato)
            {
                $periodId = $contrato[8];
                $isFreshMen = $busContract->isFreshmanByPeriod($contrato[0], $periodId);
                
                $matricula = new Matricula($contrato[0], Matricula::MATRICULA_WEB);
                $learningPeriodId = $contrato[7];

                $matricula->definirLearningPeriodId($learningPeriodId, Matricula::MATRICULA_WEB);
                if($matricula->matriculaAberta($isFreshMen))
                {
                    $menuItem[] = array(_M('Matrícula', $module), 'enrollWeb-32x32.png', "$home:enrollWeb", 'FrmEnrollWeb');
                    break;
                }
            }
        }

        $sortMenuItem = SAGU::arraySort($menuItem, 0);
        foreach ( $sortMenuItem as $m )
        {
            if( $MIOLO->checkAccess($m[3], A_ACCESS, false, true) )
            {
                $pnlPupil->addAction($m[0], $MIOLO->getUI()->getImage($module, $m[1]), $module, $m[2]);
            }
        }

        $pnlPupil->addAction('Voltar', $MIOLO->getUI()->getImage($module, 'back-32x32.png'), $module,'main:services');

        $theme->appendContent($pnlPupil);
        $sep = new MSeparator();
        $theme->appendContent($sep);
    }
}
?>
