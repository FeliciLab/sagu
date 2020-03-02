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
 * Report handler
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 29/08/2008
 *
 **/
$ui = $MIOLO->getUI();

$reportId = MIOLO::_REQUEST('reportId');
$business = $MIOLO->getBusiness( $module, 'BusReport');
$data = $business->getReport( $reportId );

if ( count((array) $data) == 1 )
{
    $this->manager->error(_M('Relatório inexistente.', $this->module));
}
else
{

    if ( $data->script )
    {
        $MIOLO->uses('forms/FrmAdminReport.class.php', 'gnuteca3');

        if ( GUtil::checkSyntax($data->script) )
        {
            
            //Se existir clase do relatorio customizado
            if ( class_exists("FrmCustomReport", false) )
            {
                //instancia a classe
                $content = new FrmCustomReport();
            }
            else if ( !class_exists("FrmCustomReport", false) )//se nao existir ai sim chama a classe
            {
                //importa a classe vinda da base de dados.
                eval( $data->script );
                $content = new FrmCustomReport();
            }
            else //Caso contrario
            {
                //Usa o adminreport padrao
                $content = $ui->getForm($module, 'FrmAdminReport', $data);
            }
        }
    }
    else
    {
        $content = $ui->getForm($module, 'FrmAdminReport', $data);
    }

    $transaction = 'gtcAdminReport' . ucfirst($info->permission);

    if (GPerms::checkAccess($transaction))
    {
        $content->setIcon(GUtil::getImageTheme('report-16x16.png'));
        $theme->clearContent( );
        $theme->insertContent( $content );
        createBreadCrumb();
    }
}
?>