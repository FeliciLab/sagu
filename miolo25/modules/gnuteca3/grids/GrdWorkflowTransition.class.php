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
 * Grid
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
 * Class created on 29/07/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdWorkflowTransition extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $columns = array(
            new MGridColumn(_M('Código status anterior', $this->module), MGrid::ALIGN_LEFT, null, null, false, null, true), //Coluna oculta para armazenar o código a ser excluído.
            new MGridColumn(_M('Código status posterior', $this->module), MGrid::ALIGN_LEFT, null, null, false, null, true), //Coluna oculta para armazenar o código a ser excluído.
            new MGridColumn(_M('Workflow', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Status anterior', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Próximo Status', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Função', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Nome', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true)
        );
        
        $this->setTransaction('gtcConfigWorkflow');

        parent::__construct($data, $columns);

        $this->setTransaction('gtcConfigWorkflow');
        $args = array( 'function' => 'update','previousWorkflowStatusId' => '%0%','nextWorkflowStatusId' => '%1%');
        $this->setIsScrollable();
        $this->addActionUpdate( $this->MIOLO->getActionURL($this->module, $this->action, null, $args) );
        $args = array( 'function' => 'delete','previousWorkflowStatusId' => '%0%','nextWorkflowStatusId' => '%1%' );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $args['function'] = 'search';
        
    }

    
}
?>
