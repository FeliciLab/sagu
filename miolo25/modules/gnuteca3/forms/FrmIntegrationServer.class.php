<?php

/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe WebService para GnutecaAutomação
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 * 
 * 
 *
 * @since
 * Class created on 04/11/2013
 * 
 **/

class FrmIntegrationServer extends GForm
{
    public $MIOLO;
    public $module;
    
    public $businessLibraryUnit;
    public $busIntegrationServer;
    public $busIntegrationLibrary;
    public $busScheduleTask;
    
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        
        $this->setAllFunctions('IntegrationServer', null, 'integrationServerId', array('nameClient'));
        $this->setWorkflow( 'INTEGRATION' );
        
        $this->businessLibraryUnit = $this->MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
        $this->busIntegrationServer = $this->MIOLO->getBusiness('gnuteca3', 'BusIntegrationServer');
        $this->busIntegrationLibrary = $this->MIOLO->getBusiness('gnuteca3', 'BusIntegrationLibrary');
        $this->busScheduleTask = $this->MIOLO->getBusiness('gnuteca3', 'BusScheduleTask');
        
        parent::__construct();
    }

    /*
     * Criado por Tcharles Silva
     * Em: 14/04/2014
     * Ultima atualização: 14/04/2014
     * Motivo:
     *      Tela de cadastro de Server Integrador
     */
    public function mainFields()
    {
    //LImpa valores da GRepetitive
        if ( GForm::primeiroAcessoAoForm() )
        {
            GRepetitiveField::clearData('unidadesBiblioteca');
        }
        
        if($this->function == 'insert')
        {
            if(strlen(INTEGRATION_MY_URL) > 0)
            {
                $fields[] = $cod = new MIntegerField('integrationServerId', $this->integrationServerId->value, _M('',$this->module), FIELD_ID_SIZE, NULL, NULL, TRUE);
                $cod->addStyle('display', 'none');

                //Método que o botão verificar irá chamar:
                $ajaxAction = 'javascript:'.GUtil::getAjax('btn_Verify_click', $this->fields).';';

                //Campo de Usuario, Enderço e Server, junto ao botão de verificar que fará chamada ao WebService
                $topFields[] = new MLabel('Endereço :');
                $topFields[] = $hostServe = new MTextField('hostServer', $this->hostServer->value, _M('',$this->module), FIELD_DESCRIPTION_SIZE, null, null);
                $hostServe->addAttribute('onPressEnter', 'javascript:'.GUtil::getAjax('btn_Verify_click', $this->fields).';');
                $topFields[] = new MButton('btnVerificar', _M('Verificar', $this->module), $ajaxAction );

                //Só irá aparecer se pegar informações do Server da Biblioteca Virtual
                $campoBox[] = new MTextField('nameServer', $this->nameServer->value,  _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE, null, null, TRUE);
                $campoBox[] = new MTextField('emailServer', $this->emailServer->value,  _M('E-mail',$this->module), FIELD_DESCRIPTION_SIZE, null, null, TRUE);
                $campoBox[] = new MTextField('qtObras', $this->qtObras->value, _M('Quantidade de obras',$this->module), FIELD_DESCRIPTION_SIZE, null, null, TRUE);

                //Primeiro campo
                $fields[] = new MContainer('boxTop', $topFields);
                //Segundo campo
                $infoserver = new MBaseGroup('serverbox', 'Informações do servidor', $campoBox);

                //Esconder o campo de informações do server, pois o mesmo esta vazio
                $infoserver->addStyle('display', 'none');
                $fields[] = $infoserver;

                //Campo do Cliente
                $fields[] = new MTextField('nameClient', $this->nameClient->value,  _M('Nome da bib. solicitante',$this->module), FIELD_DESCRIPTION_SIZE, "Nome da sua biblioteca", null);
                $fields[] = new MTextField('emailClient', EMAIL_ADMIN,  _M('E-mail',$this->module), FIELD_DESCRIPTION_SIZE, "E-mail da sua biblioteca. Definido na preferência EMAIL_ADMIN", null);
                $fields[] = new MTextField('user', $this->user->value,  _M('Usuário',$this->module), '20x');
                $fields[] = new MPasswordField('password', $this->password->value, _M('Senha', $this->module));
                $fields[] = new GSelection('periodicity', '', _M('Ciclo do agendamento',$this->module), $this->busScheduleTask->listScheduleCycles());
                
                //* ----- Campo Repetitivo para Unidade de Biblioteca
                //
                //  Obtem todas as unidades
                $unidades = $this->businessLibraryUnit->listLibraryUnit();
                //    
                //  Define campo repetitivo
                $fields[] = $unidadesBiblioteca = new GRepetitiveField('unidadesBiblioteca', _M('Unidades de biblioteca', $this->module), NULL, NULL, array('remove'));
                //    
                //  Define Label
                $label = new MLabel(_M('Unidade de biblioteca', $this->module));
                //     
                //  Define o campo de seleção da Repetitive
                $field = new GSelection('libraryUnitId', $this->libraryUnitId->value, NULL, $unidades, false, false, false, false);
                //    
                //  Define no Container, a label e o campo de seleção
                $fieldUnidades[] = new GContainer('teste', array($label, $field));
                //    
                //  Seta os campos
                $unidadesBiblioteca->setFields($fieldUnidades);
                //    
                //  Define uma nova coluna
                $columns[] = new MGridColumn( _M('Unidade de biblioteca', 'gnuteca3'), 'left', true, '', false, 'libraryUnitId');
                $columns[] = new MGridColumn(_M('Unidade de biblioteca', 'gnuteca3'), 'left', true, '', true, 'libraryName');

                //  Seta as colunas
                $unidadesBiblioteca->setColumns($columns);
                //    
                //* ----- Fim do campo repetitivo    

                //* -----Validador de campos
                //
                //$validators[] = new MRequiredValidator('libraryUnitId');
                //$validators[] = new GnutecaUniqueValidator('libraryUnitId');

                $validators[] = new MRequiredValidator('hostServer', 'Endereço do servidor', '', 'Você quer cadastrar sem informar o servidor? É melhor parar por aqui, vá tomar um café e volte com mais atenção.');
                $validators[] = new MRequiredValidator('nameClient', 'Nome do servidor', '', 'Por gentileza, identifique o nome da sua bíblioteca.');
                $validators[] = new MRequiredValidator('user', 'Usuário', '', 'Coloque um usuário.');
                $validators[] = new MRequiredValidator('password', 'Senha', '', 'Coloque uma senha.');
                $validators[] = new MRequiredValidator('periodicity', 'Período', '', 'Identifique um período.');
                //*----- Fim do validador de campos
            }
            else
            {
                throw new Exception("Ooops! Você precisa da preferência INTEGRATION_MY_URL definida para continuar.");
            }
        }
        if($this->function == 'update')
        {
                
                $fields[] = $cod = new MIntegerField('integrationServerId', $this->integrationServerId->value, _M('Código:',$this->module), FIELD_ID_SIZE, NULL, NULL, TRUE);
                
                $topFields[] = new MLabel('Endereço :');
                $topFields[] = new MTextField('hostServer', $this->hostServer->value, _M('',$this->module), FIELD_DESCRIPTION_SIZE, "Endereço da biblioteca servidor", null);
                
                //Só irá aparecer se pegar informações do Server da Biblioteca Virtual
                $campoBox[] = new MTextField('nameServer', $this->nameServer->value,  _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE, null, null, TRUE);
                $campoBox[] = new MTextField('emailServer', $this->emailServer->value,  _M('E-mail',$this->module), FIELD_DESCRIPTION_SIZE, null, null, TRUE);
                //$campoBox[] = new MTextField('qtObras', $this->qtObras->value, _M('Quantidade de Obras',$this->module), FIELD_DESCRIPTION_SIZE, null, null, TRUE);
                
                //Primeiro campo
                $fields[] = new MContainer('boxTop', $topFields);
                //Segundo campo
                $fields[] = new MBaseGroup('serverbox', 'Informações do servidor', $campoBox);
                
                //Campo do Cliente
                $fields[] = new MTextField('nameClient', $this->nameClient->value,  _M('Nome da bib. solicitante',$this->module), FIELD_DESCRIPTION_SIZE, "Nome da sua biblioteca", null);
                $fields[] = new MTextField('emailClient', EMAIL_ADMIN,  _M('E-mail',$this->module), FIELD_DESCRIPTION_SIZE, "E-mail da sua biblioteca. Definido na preferência EMAIL_ADMIN", null);
                $fields[] = new MTextField('user', $this->user->value,  _M('Usuário',$this->module), '20x');
                $fields[] = new MPasswordField('password', $this->password->value, _M('Senha', $this->module));
                $fields[] = new GSelection('periodicity', '', _M('Ciclo do agendamento',$this->module), $this->busScheduleTask->listScheduleCycles());
                
                $var = $this->getData();
                
                //Monta objeto com o libraryUnit
                $this->busIntegrationLibrary->integrationServerId = $var->integrationServerId;
                $vey = $this->busIntegrationLibrary->searchIntegrationLibrary(TRUE);
                
                //* ----- Campo Repetitivo para Unidade de Biblioteca
                //
                //  Obtem todas as unidades
                $unidades = $this->businessLibraryUnit->listLibraryUnit();
                //    
                //  Define campo repetitivo
                $fields[] = $unidadesBiblioteca = new GRepetitiveField('unidadesBiblioteca', _M('Unidades de biblioteca', $this->module), NULL, NULL, array('remove'));
                //    
                //  Define Label
                $label = new MLabel(_M('Unidade de biblioteca', $this->module));
                //     
                //  Define o campo de seleção da Repetitive
                $field = new GSelection('libraryUnitId', $this->libraryUnitId->value, NULL, $unidades, false, false, false, false);
                //    
                //  Define no Container, a label e o campo de seleção
                $fieldUnidades[] = new GContainer('teste', array($label, $field));
                //    
                //  Seta os campos
                $unidadesBiblioteca->setFields($fieldUnidades);
                //    
                //  Define uma nova coluna
                $columns[] = new MGridColumn( _M('Unidade de biblioteca', 'gnuteca3'), 'left', true, '', false, 'libraryUnitId');
                $columns[] = new MGridColumn(_M('Unidade de biblioteca', 'gnuteca3'), 'left', true, '', true, 'libraryName');

                //  Seta as colunas
                $unidadesBiblioteca->setColumns($columns);
                
                $validators[] = new MRequiredValidator('hostServer', 'Endereço do servidor', '', 'Você quer cadastrar sem informar o servidor? É melhor parar por aqui, vá tomar um café e volte com mais atenção.');
                $validators[] = new MRequiredValidator('nameClient', 'Nome do servidor', '', 'Por gentileza, identifique o nome da sua bíblioteca.');
                $validators[] = new MRequiredValidator('user', 'Usuário', '', 'Coloque um usuário.');
                $validators[] = new MRequiredValidator('password', 'Senha', '', 'Coloque uma senha.');
                $validators[] = new MRequiredValidator('periodicity', 'Período', '', 'Identifique um período.');
        }
        
    //Define os validatores
        $this->setValidators($validators);

    //Seta os campos
        $this->setFields($fields);
        
    }
    
    //Função que é chamada ao adicionar uma Bin
    public function forceAddToTable($args)
    {
        $args->libraryName = $this->businessLibraryUnit->getLibraryName($args->libraryUnitId);
        parent::forceAddToTable($args);
    }
    
    public function tbBtnSave_click()
    {
        $data = parent::getData();
        //Caso esteja em branco
        if(empty($data->unidadesBiblioteca))
        {
            throw new Exception("Você precisa adicionar pelo menos uma unidade de biblioteca.");
        }
        parent::tbBtnSave_click(NULL, $data);
        $this->business->getWorkflowInstanceByTableId();
        $this->business->completeInsert();
        //Aqui abrirá requisição ao cliente, passando os dados
        $clientInstanceId = $this->business->abreRequisicaoAoCliente();
        
        $this->business->atualizaClientInstanceId($clientInstanceId[0]);
        
        $this->setResponse(NULL, 'limbo');
    }
    
    public function btn_Verify_click($param)
    {
        $url = $param->hostServer;
        if(!empty($url))
        {
            $class = "gnuteca3WebServicesIntegrationServer";

            $clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=$class";
            $clientOptions["uri"] = "$url";
            $clientOptions["encoding"] = "UTF-8";
            
            $cliente = new SoapClient(NULL, $clientOptions);

            $result[] = $cliente->infoGnutecaVirtual();
            
            $nome = $result[0][0];
            $email = $result[0][1];
            $qtdObras = $result[0][2];
            
            //Povoar campos
            $this->page->onLoad("dojo.byId('nameServer').value = '$nome';");
            $this->page->onLoad("dojo.byId('emailServer').value = '$email';");
            $this->page->onLoad("dojo.byId('qtObras').value = '$qtdObras';");
            
            //Mostra o campo
            $this->page->onLoad("dojo.style('serverbox', { 'display' : 'block' });");
            
            $this->setResponse(NULL, 'limbo');
            $this->setFocus('nameClient');
        }
    }
}
?>
