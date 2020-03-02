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

class FrmIntegrationClientSearch extends GForm
{

    public $MIOLO;
    public $module;
    public $busIntegrationClient; 

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->setAllFunctions('IntegrationClient', 'integrationClientId', 'integrationClientId');
        $this->busIntegrationClient = $this->MIOLO->getBusiness($this->module, 'BusIntegrationClient');
        
        parent::__construct();
    }
    
    public function mainFields()
    {
        $fields[] = new MTextField('integrationClientId', $this->integrationClientId->value, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('nameClient', $this->nameClient->value, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('hostClient', $this->hostName->value, _M('Endereço', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('emailClient', $this->emailClient->value, _M('E-mail', $this->module), FIELD_DESCRIPTION_SIZE);
        
        $lblQntdMaterials = new MLabel(_M('Quantidade de materiais:' , $this->module));
        $txtQntdMaterials1 = new MTextField('countMaterials1', $this->countMaterials1->value, '', FIELD_ID_SIZE);
        $txtQntdMaterials2 = new MTextField('countMaterials2', $this->countMaterials2->value, '', FIELD_ID_SIZE);
        $fields[] = new GContainer('htcCountMaterials' , array($lblQntdMaterials, $txtQntdMaterials1,  $txtQntdMaterials2));
        
        $lblQntdExemplaries = new MLabel(_M('Quantidade de exemplares:' , $this->module));
        $txtQntdExemplaries1 = new MTextField('countExemplaries1', $this->countExemplaries1->value, '', FIELD_ID_SIZE);
        $txtQntdExemplaries2 = new MTextField('countExemplaries2', $this->countExemplaries2->value, '', FIELD_ID_SIZE);
        $fields[] = new GContainer('htcCountExemplaries' , array($lblQntdExemplaries, $txtQntdExemplaries1,  $txtQntdExemplaries2));
        
        $showValues = $this->busIntegrationClient->getStatusWorkflow('INTEGRATION'); //Implementar com o select de status necessário
        
        $fields[] = new GSelection('status', $this->status->value, _M('Status', $this->module), $showValues);
        $fields[] = new MCalendarField('lastDate', $this->lastDate->value, _M('Última sincronização', $this->module));

        //toolbar
        $this->getToolBar();
        $fields[] =  $this->_toolBar;
        $this->_toolBar->disableButton( array('tbBtnNew'));
        
        $this->setFields($fields);
    }
}
?>
