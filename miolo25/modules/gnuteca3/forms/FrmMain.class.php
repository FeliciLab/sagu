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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/

$MIOLO->uses('widgets/NewsWidget.class.php','gnuteca3');
$MIOLO->uses('widgets/BackgroundTaskLogWidget.class.php','gnuteca3');
$MIOLO->uses('widgets/ActiveUserWidget.class.php','gnuteca3');

class FrmMain extends GForm
{
    public function __construct()
    {
        parent::__construct( _M('Principal', $this->module) );
    }

    public function mainFields()
    {
        if ( !GOperator::isLogged() )
        {
            $this->setFields(array());
            return;
        }
        
        $left[] = new NewsWidget();
        $left[] = new MDiv('backgroundTaskContainer',new BackgroundTaskLogWidget());

        $controls[] = $left = new MDiv('leftWidgets', $left );
        $left->setWidth('70%');
        $left->addStyle('float', 'left');
        $controls[] = $activeUser = new ActiveUserWidget();
        $activeUser->setWidth('20%');
        $fields[] = new GContainer('', $controls);
       
        $this->setFields($fields);
    }

    /**
     * A permissão desta interface é baseada em estar ou não logado.
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return GOperator::isLogged( ) && GOperator::hasSomePermission() ;
    }
}
?>