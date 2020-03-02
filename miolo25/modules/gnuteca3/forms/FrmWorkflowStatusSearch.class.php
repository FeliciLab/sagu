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
 * Library Unit search form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
$MIOLO->uses('db/BusOperatorGroup.class.php', 'gnuteca3');
class FrmWorkflowStatusSearch extends GForm
{
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;
    public $MIOLO;
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->setAllFunctions('WorkflowStatus', array('workflowIdS','__nameS','initialS','__transactionS'), array('workflowStatusId'));
        $this->setTransaction('gtcConfigWorkflow');
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MIntegerField('workflowStatusIdS', null, _M('Código',$this->module), FIELD_DESCRIPTION_SIZE );
        $fields[] = new GSelection('workflowIdS', null, _M('Workflow',$this->module),BusinessGnuteca3BusDomain::listForSelect('WORKFLOW',false,true) );
        $fields[] = new MTextField('__nameS', null, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('initialS', null, _M('Inicial',$this->module), GUtil::getYesNo());
        $fields[] = new GSelection('__transactionS', null, _M('Transação',$this->module), BusinessGnuteca3BusOperatorGroup::listTransactions() );

        $this->setFields( $fields );
    }

}
?>