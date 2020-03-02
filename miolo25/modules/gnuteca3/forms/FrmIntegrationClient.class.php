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
 * @author Lucas Gerhardt [lucas_gerhardt@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 14/04/2014
 *
 * */

class FrmIntegrationClient extends GForm
{

    public $MIOLO;
    public $module;
    public $busIntegrationClient;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busIntegrationClient = $this->MIOLO->getBusiness($this->module, 'BusIntegrationClient');
        $this->setAllFunctions('IntegrationClient', null, 'integrationClientId', array('nameClient'));
        $this->setWorkflow( 'INTEGRATION' );
        
        parent::__construct();
    }
    
    public function mainFields()
    {
        $clientId = new MTextField('integrationClientId', $this->integrationClientId->value, _M('Código', $this->module), FIELD_ID_SIZE);
        $clientId->setReadOnly(TRUE);
        $fields[]     = $clientId;
        $fields[] = new MTextField('nameClient', '', _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('hostClient', '', _M('Endereço', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('emailClient', '', _M('E-mail', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('initialAmountClientMaterials', '', _M('Quantidade de materiais', $this->module), FIELD_ID_SIZE, NULL, NULL, TRUE);
        $fields[] = new MTextField('initialAmountClientExemplarys', '', _M('Quantidade de exemplares', $this->module), FIELD_ID_SIZE, NULL, NULL, TRUE);
        $fields[] = new MTextField('periodicity', '', _M('Periodicidade de sincronização', $this->module), FIELD_DESCRIPTION_SIZE, NULL, NULL, TRUE);
        
        $columns = array( new MGridColumn( _M('Código', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
                          new MGridColumn( _M('Data', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
                          new MGridColumn( _M('Quantidade de materiais', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
                          new MGridColumn( _M('Quantidade de exemplares', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ));
        
        $fields[] = new MLabel('Últimas sincronizações:');
        $fields[] = new MDiv(_M('Últimas sincronizações:', $this->module));
        
        $gridData = $this->busIntegrationClient->getSynchronizations();

        $titles = array( _M('Código', $this->module),
                         _M('Data', $this->module),
                         _M('Quantidade de materiais', $this->module),
                         _M('Quantidade de exemplares', $this->module));
        
        $fields[] = $table = new MTableRaw( NULL, $gridData , $titles);
	$table->addStyle('width','100%');
        
        //toolbar
        $this->getToolBar();
        $fields[] =  $this->_toolBar;
        $this->_toolBar->disableButton( array('tbBtnNew'));
        
        $this->setFields($fields);
    }
}
?>
