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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2008
 *
 **/
$MIOLO = MIOLO::getInstance();

$MIOLO->getClass('gnuteca3', 'controls/GPhotoManager');
$MIOLO->getClass('gnuteca3', 'GSipCirculation');

class FrmPerson extends GForm
{
    public $MIOLO;
    public $module;
    public $busLibraryUnit;
    public $busBiometry;
    private $busBond;
    private $busFile;
    
    
    function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busBiometry = $this->MIOLO->getBusiness($this->module, 'BusBiometry');
        $this->busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');

        $this->setAllFunctions('Person', null, array('personId'), array('personName'));
        parent::__construct();

        //limpa as repetitiveFields
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('personPhone');
            GRepetitiveField::clearData('bond');
            GRepetitiveField::clearData('penalty');
            GRepetitiveField::clearData('personLibraryUnit');
            GRepetitiveField::clearData('documents');
        }
        
        if($this->primeiroAcessoAoForm())
        {
            GPhotoManager::clearData();
                                
        }
        
    }

    public function mainFields()
    {
        $tabControl = new GTabControl('tabControlPerson');
        
        if ( ($this->function == 'insert') && (USER_ESPECIFICAR_CODIGO_MANUALMENTE == DB_TRUE) )
        {
            $fields[] = new MTextField('personId', '', _M('Código', $this->module), FIELD_ID_SIZE);
            $validators[] = new MRequiredValidator('personId');
            $validators[] = new MIntegerValidator('personId');
                        
        }
        elseif($this->function != 'insert')
        {
            $personId = new MTextField('personId', '', _M('Código', $this->module), FIELD_ID_SIZE);
            $personId->setReadOnly(TRUE);
            $fields[]     = $personId;
            $validators[] = new MRequiredValidator('personId');
        }

        $changePersonPermissions = $this->business->getPersonChangePermissions();        
        
        //TODO Criar um component GPersonLookup e trocar em todas as telas que tenham código da cidade.
        $cityIdLabel = new MLabel(_M('Código da cidade', $this->module) . ':');
        $cityIdPerson = new GLookupTextField('cityIdPerson', '', '', FIELD_LOOKUPFIELD_SIZE);

        $cityIdPerson->setContext($this->module, $this->module, 'City', 'filler', 'cityIdPerson,cityNamePerson', '', true);
        $cityIdPerson->baseModule = $this->module;
        $cityNamePerson = new MTextField('cityNamePerson', $cityNamePerson, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $cityNamePerson->setReadOnly(true);

        $busLocationType = $this->MIOLO->getBusiness($this->module, 'BusLocationType');
        $fields[] = new MTextField('personName', $this->personName->value, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE, null, null);
        $fields[] = new MTextField('shortname', $this->shortname->value, _M('Apelido',$this->module), FIELD_DESCRIPTION_SIZE, null, null);
        $fields[] = new GContainer('cityIdContainerPerson', array($cityIdLabel, $cityIdPerson, $cityNamePerson));
        $lblZipCode     = new MLabel(_M('CEP', $this->module) . ':');
        $lblZipCode->setWidth(FIELD_LABEL_SIZE);
        $zipCode        = new MTextField('zipCode', $this->zipCode->value, null, FIELD_ID_SIZE,null,null);
        $lblFormat      = new MLabel(_M('99999999', $this->module) );
        $fields[]       = new GContainer('hctZipCode', array($lblZipCode, $zipCode, $lblFormat));
        $lblLocation = new MLabel(_M('Logradouro', $this->module) );
        $locationTypeId = new GSelection('locationTypeId', null,  null, $busLocationType->listLocationType());

        $location = new MTextField('location', $this->location->value,null, FIELD_DESCRIPTION_SIZE, null, null);
        $fields[] = new GContainer('locationContainer', array($lblLocation, $locationTypeId, $location ));
        $fields[]       = new MTextField('number', $this->number->value, _M('Número',$this->module), FIELD_ID_SIZE,null, null);
        $fields[]       = new MTextField('complement', $this->complement->value, _M('Complemento',$this->module), FIELD_DESCRIPTION_SIZE,null, null);
        $fields[]       = new MTextField('email', $this->email->value, _M('E-mail',$this->module), FIELD_DESCRIPTION_SIZE,null, null);
        $fields[]       = new MTextField('emailAlternative', $this->emailAlternative->value, _M('E-mail alternativo',$this->module), FIELD_DESCRIPTION_SIZE,null, null);
        $fields[]       = new MTextField('url', $this->url->value, _M('Url',$this->module), FIELD_DESCRIPTION_SIZE,null, null);        
        //domínios do sexo
        $fields['sex'] = new GRadioButtonGroup( 'sex', _M('Sexo', $this->module).':' , BusinessGnuteca3BusDomain::listForRadioGroup('SEX'));

        $fields['dateBirth'] = new MCalendarField('dateBirth', null, _M('Data de nascimento', $this->module), FIELD_DATE_SIZE);

        $fields[] = new MTextField('profession', null, _M('Profissão',$this->module), FIELD_DESCRIPTION_SIZE, null, null);
        $fields[] = new MTextField('workPlace', null, _M('Local de trabalho',$this->module), FIELD_DESCRIPTION_SIZE, null, null);
        $fields[] = new MTextField('school', null, _M('Escola',$this->module), FIELD_DESCRIPTION_SIZE, null, null);
        
        //FIXME: quando usado o  id "description", valor não aparece no getData() do form
        $fields['obs'] = new MMultiLineField('obs', NULL, _M('Observação', $this->module), NULL, 5, 65);

        
        $password = new MPasswordField( 'password', '', _M( 'Senha', $admin ), 10, $this->function == 'update' ? _M('Quando não preenchido, mantém a senha anterior', $this->module) : null);

        if(GSipCirculation::usingSmartReader())
        {
            $login = new MTextField('loginU', '', _M('Usuário',$this->module), FIELD_DESCRIPTION_SIZE, _M('Código do cartão', $this->module), null);
        }
        else
        {
            $login = new MTextField('loginU', '', _M('Usuário',$this->module), FIELD_DESCRIPTION_SIZE,null, null);
        }
        
        if ( $changePersonPermissions->tabMain == DB_FALSE )
        {
            $login->setReadOnly(true);
            $password->setReadOnly(true);
            
        }
        
        if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE )
        {
            $bases =  BusinessGnuteca3BusAuthenticate::listMultipleLdap();
            $ldapBase = new GSelection('baseLdap', '', _M('Base', $this->module), $bases, false, '','', true);
            
            if ( $changePersonPermissions->tabMain == DB_FALSE )
            {
                $ldapBase->setReadOnly(true);
                
            }
            
            $fields[] = new MBaseGroup('groupPassword', _M('Autenticação Ldap'), array($password, $login, $ldapBase), 'vertical','css' , MControl::FORM_MODE_SHOW_SIDE );
            $validators[] = new MRequiredValidator('baseLdap');
        }
        else if ( MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN )
        {
            $fields[] = new MBaseGroup('groupPassword', _M('Autenticação Ldap'), array($password, $login), 'vertical','css' , MControl::FORM_MODE_SHOW_SIDE  );
        }
        else
        {
            //Se utilizar leitor de cartão
            if(GSipCirculation::usingSmartReader())
            {
                $fields[] = $login;
            }
            $fields[] = $password;
        }
        
        //campo para selecionar o grupo
        $personGroups = BusinessGnuteca3BusDomain::listForSelect('PERSON_GROUP');
        
        if ( $personGroups != null ) //Se tiver a preferencia PERSON_GROUP
        {
            $fields['personGroup'] = new GSelection('personGroup', null, _M('Grupo', $this->module), $personGroups); //Mostra campo de person Group

        }
        
        $tabControl->addTab('tabMain', _M('Gerais'), $fields, null, false, $changePersonPermissions->tabMain);

        unset($fields);
        $fields[] = $tabControl;
              
        //Bond
        $fldBond[] = new GSelection('linkId', '', _M('Código do grupo de usuário', $this->module), $this->busBond->listBond(true));
        $dateValidate   = new MCalendarField('dateValidate', $this->dateValidate->value, _M('Data de validade', $this->module) . ':', FIELD_DATE_SIZE, null);
        $fldBond[]      = $cont = new GContainer('hctDateValidate', array($lblDV, $dateValidate));
        $fldBond['oldDateValidate'] = new MTextField('oldDateValidate');
        $fldBond['oldDateValidate']->addStyle('display', 'none');
        $fldBond['oldLinkId'] = new MTextField('oldLinkId');
        $fldBond['oldLinkId']->addStyle('display', 'none');        
        $fldBond[]      = new MSeparator();
        $valids[]       = new MDATEDMYValidator('dateValidate', _M('Data de validade', $this->module));
        $valids[]       = new MIntegerValidator('linkId', _M('Código do grupo de usuário', $this->module), 'required');

        //Phone
        $fldPhone[]     = new GSelection('type', null, _M('Tipo', $this->module), BusinessGnuteca3BusDomain::listForSelect('TIPO_DE_TELEFONE'));
        $fldPhone[]     = new MTextField('phone', null, _M('Telefone', $this->module), FIELD_DESCRIPTION_SIZE);
        $validsPhone[]  = new MRequiredValidator('type',_M('Tipo',    $this->module));
        $validsPhone[]  = new GnutecaUniqueValidator('type',_M('Tipo',    $this->module));
        $validsPhone[]  = new MRequiredValidator('phone',_M('Telefone', $this->module));

        $phoneColumns[] = new MGridColumn( _M('Id',    $this->module), 'left', true, null, false, 'phoneId' );
        $phoneColumns[] = new MGridColumn( _M('Tipo',    $this->module), 'left', true, null, false, 'type' );
        $phoneColumns[] = new MGridColumn( _M('Tipo',    $this->module), 'left', true, null, true, 'typeDesc' );
        $phoneColumns[] = new MGridColumn( _M('Telefone', $this->module), 'left', true, null, true, 'phone' );
       
        $telephone = new GRepetitiveField('personPhone', _M('Telefone', $this->module), NULL, NULL, array('remove'));
        $telephone->setFields( $fldPhone );
        $telephone->setValidators( $validsPhone );
        $telephone->setColumns($phoneColumns);

        $tabControl->addTab('tabPhone',_M('Telefones', $this->module),array( $telephone), null, false, $changePersonPermissions->tabPhone);
        
        //documentos
        $busDocumentType = $this->MIOLO->getBusiness($this->module, 'BusDocumentType');
        
        $document = new GRepetitiveField('documents', _M('Documento', $this->module), NULL, NULL, array('edit', 'remove'));
        $fldDocument[] = new GSelection('documentTypeId', null, _M('Tipo', $this->module), $busDocumentType->listDocumentType());
        $fldDocument[] = $old = new MTextField('oldDocumentTypeId');
        $old->addStyle('display', 'none');
        $fldDocument[] = new MTextField('content', NULL, _M('Conteúdo', $this->module), FIELD_DESCRIPTION_SIZE);

        $cityIdDocument = new GLookupTextField('cityIdDocument', '', '', FIELD_LOOKUPFIELD_SIZE);
        $cityIdDocument->setContext($this->module, $this->module, 'City', 'filler', 'cityIdDocument,cityNameDocument', '', true);
        $cityIdDocument->baseModule = $this->module;
        $cityNameDocument = new MTextField('cityNameDocument', $cityNameDocument, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $cityNameDocument->setReadOnly(true);          
        $fldDocument[] = new GContainer('cityIdContainerDocument', array($cityIdLabel, $cityIdDocument, $cityNameDocument));
        

        
        
        $fldDocument[] = new MTextField('organ', null, _M('Orgão', $this->module), FIELD_DESCRIPTION_SIZE);
        $fldDocument[] = new MCalendarField('dateExpedition', null, _M('Data de expedição', $this->module), FIELD_DATE_SIZE);
        $fldDocument[] = new GSelection('isDelivered', DB_FALSE, _M('Está entregue', $this->module), GUtil::listYesNo());
        $fldDocument[] = new GSelection('isExcused', DB_FALSE, _M('É dispensado', $this->module), GUtil::listYesNo());

        $fldDocument[] = new MMultiLineField('observationD', NULL, _M('Observação', $this->module), NULL, 5, 65);
        
        $document->setFields( $fldDocument );

        $documentColumns[] = new MGridColumn( _M('Tipo',    $this->module), 'left', true, null, false, 'documentTypeId' );
        $documentColumns[] = new MGridColumn( _M('Tipo',    $this->module), 'left', true, null, true, 'documentTypeIdDesc' );
        $documentColumns[] = new MGridColumn( _M('Tipo',    $this->module), 'left', true, null, false, 'oldDocumentTypeId' );
        $documentColumns[] = new MGridColumn( _M('Conteúdo',    $this->module), 'left', true, null, true, 'content' );
        $documentColumns[] = new MGridColumn( _M('Cidade',    $this->module), 'left', true, null, false, 'cityId' );
        $documentColumns[] = new MGridColumn( _M('Cidade',    $this->module), 'left', true, null, true, 'cityNameDocument' );
        $documentColumns[] = new MGridColumn( _M('Orgão',    $this->module), 'left', true, null, true, 'organ' );
        $documentColumns[] = new MGridColumn( _M('Data de expedição',    $this->module), 'left', true, null, true, 'dateExpedition' );
        $documentColumns[] = new MGridColumn( _M('Está entregue',    $this->module), 'left', true, null, false, 'isDelivered' );
        $documentColumns[] = new MGridColumn( _M('Está entregue',    $this->module), 'left', true, null, true, 'isDeliveredText' );        
        $documentColumns[] = new MGridColumn( _M('É dispensado',    $this->module), 'left', true, null, false, 'isExcused' );
        $documentColumns[] = new MGridColumn( _M('É dispensado',    $this->module), 'left', true, null, true, 'isExcusedText' );        
        $documentColumns[] = new MGridColumn( _M('Observação',    $this->module), 'left', true, null, true, 'observationD' );

        $document->setColumns($documentColumns);
           
        $validsDocument[] = new MRequiredValidator('documentTypeId', _M('Tipo', $this->module));
        $validsDocument[] = new MRequiredValidator('isDelivered', _M('Está entregue', $this->module));
        $validsDocument[] = new MRequiredValidator('isExcused', _M('É dispensado', $this->module));
        $validsDocument[] = new MRequiredValidator('content', _M('Conteúdo', $this->module));
        $validsDocument[] = new GnutecaUniqueValidator('documentTypeId');
        
        $document->setValidators($validsDocument);

        $tabControl->addTab('tabDocument',_M('Documentos', $this->module),array( $document), null, false, $changePersonPermissions->tabDocument);
        
        //repetitive bond
        $bond = new GRepetitiveField('bond', _M('Vínculo', $this->module), NULL, NULL, array('edit', 'remove'));
        $bond->setFields( $fldBond );
        $bond->setValidators( $valids );

        $tabControl->addTab('tabBond',_M('Vínculos', $this->module),array( $bond), null, false, $changePersonPermissions->tabBond);

        $columns   = null;
        $valids    = null;
        $columns[] = new MGridColumn( _M('Código',    $this->module), 'left', true, null, true, 'linkId' );
        $columns[] = new MGridColumn( _M('Grupo de usuário',    $this->module), 'left', true, null, true, 'linkIdName' );
        $columns[] = new MGridColumn( _M('Data de validade', $this->module), 'left', true, null, true, 'dateValidate' );
        $columns[] = new MGridColumn( _M('Old date validate', $this->module), 'left', true, null, false, 'oldDateValidate' );

        $bond->setColumns($columns);

        //Penalty
        $fldPenalty['penaltyId'] = new MDiv('divPenaltyId', new MTextField('penaltyId'));
        $fldPenalty['penaltyId']->addStyle('display', 'none');
        $fldPenalty[] = new MTextField('observationP', $this->observation->value, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE);
        $valids[]     = new MRequiredValidator('observationP', _M('Observação', $this->module));

        $fldPenalty[] = new MTextField('internalObservation', $this->internalObservation->value, _M('Observação interna', $this->module), FIELD_DESCRIPTION_SIZE, _M('Não é visto pelos usuários', $this->module));
        $fldPenalty[] = new MCalendarField('penaltyDate', $this->penaltyDate->value, _M('Data da penalidade', $this->module), FIELD_DATE_SIZE, null);
        $valids[]     = new MDATEDMYValidator('penaltyDate', _M('Data da penalidade', $tihs->module), 'required');
        $fldPenalty[] = new MCalendarField('penaltyEndDate', $this->penaltyEndDate->value, _M('Data final de penalidade', $this->module) , FIELD_DATE_SIZE, null);

        $lblOperator = new MLabel(_M('Operador', $this->module) . ':');
        $lblOperator->setWidth(FIELD_LABEL_SIZE);
        $operator = new MTextField('operator', GOperator::getOperatorId());
        $operator->setReadOnly(true);
        $fldPenalty[] = new GContainer('hctOperator', array($lblOperator, $operator));
        $valids[] = new MRequiredValidator('operator', _M('Operador', $this->module));

        $lblLibraryUnit = new MLabel(_M('Unidade de biblioteca', $this->module) . ':');
        $lblLibraryUnit->setWidth(FIELD_LABEL_SIZE);
        $this->busLibraryUnit->filterOperator = TRUE;
        $libraryUnitId = new GSelection('libraryUnitId1', null, null, $this->busLibraryUnit->listLibraryUnit());
        $fldPenalty[] = new GContainer('hctLibraryUnit', array($lblLibraryUnit, $libraryUnitId));

        $tablePenalty = new GRepetitiveField('penalty', _M('Penalidade', $this->module), NULL, NULL, array('edit', 'remove'),'vertical' );
        $tablePenalty->setFields($fldPenalty);
        $tablePenalty->setValidators($valids);

        $tabControl->addTab('tabPenalty',_M('Penalidades', $this->module),array( $tablePenalty ), null, false, $changePersonPermissions->tabPenalty);

        unset($columns, $valids);
        $columns[] = new MGridColumn( _M('Código',             $this->module), 'left', true, null, false,'penaltyId' );
        $columns[] = new MGridColumn( _M('Observação',      $this->module), 'left', true, null, true, 'observationP' );
        $columns[] = new MGridColumn( _M('Observação interna', $this->module), 'left', true, null, true, 'internalObservation' );
        $columns[] = new MGridColumn( _M('Data da penalidade',     $this->module), 'left', true, null, true, 'penaltyDate' );
        $columns[] = new MGridColumn( _M('Data final de penalidade', $this->module), 'left', true, null, true, 'penaltyEndDate' );
        $columns[] = new MGridColumn( _M('Operador',         $this->module), 'left', true, null, true, 'operator' );
        $columns[] = new MGridColumn( _M('Código da biblioteca',$this->module), 'left', true, null, false,'libraryUnitId1' );
        $columns[] = new MGridColumn( _M('Unidade de biblioteca',     $this->module), 'left', true, null, true, 'libraryName' );
        $tablePenalty->setColumns($columns);

        if ( CLASS_USER_ACCESS_IN_THE_LIBRARY != 'BusBlockGroupLibraryUnit' )
        {
            //Person library unit
            $labelName = array(
                'BusPersonLibraryUnit'      => _M('Permitir acesso a biblioteca', $this->module),
                'BusNotPersonLibraryUnit'   => _M('Negar acesso a biblioteca', $this->module),
            );
            $labelName = $labelName[CLASS_USER_ACCESS_IN_THE_LIBRARY];
            $this->busLibraryUnit->filterOperator = TRUE;
            $vctPersonLibraryUnit[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit());

            $personLibraryUnit = new GRepetitiveField('personLibraryUnit', $labelName, NULL, NULL, array('edit', 'remove'));
            $personLibraryUnit->setFields($vctPersonLibraryUnit);

            if ( $labelName )
            {
                $tabControl->addTab('tabAccess',_M('Acesso a biblioteca', $this->module),array( $personLibraryUnit ), null, false, $changePersonPermissions->tabAccess);
            }
        
            unset($columns);
            $columns[] = new MGridColumn( _M('Código da biblioteca',$this->module), 'left', true, null, false, 'libraryUnitId' );
            $columns[] = new MGridColumn( _M('Unidade de biblioteca',     $this->module), 'left', true, null, true, 'libraryName' );
            $personLibraryUnit->setColumns($columns);

            $validsAccess[] = new MRequiredValidator('libraryUnitId');
            $validsAccess[] = new GnutecaUniqueValidator('libraryUnitId');

            $personLibraryUnit->setValidators($validsAccess);
        }
        
        //caso a integração de fotos com o sagu esteja ativada não mostra a aba de fotos
        if ( ! MUtil::getBooleanValue(SAGU_PHOTO_INTEGRATION) )
        {            
            $photo[] = new GPhotoManager('photoManager', 'person');
            
            $tabControl->addTab('tabPhoto',_M('Foto', $this->module), $photo, null, false, $changePersonPermissions->tabPhoto);
            
        }
        
        
        
        
        
       
        /* * * * * * * * * * * * * * * * * * * 
        * Aqui será implementada a Biometria  *
         * * * * * * * * * * * * * * * * * * */
        //BIOMETRIA
        if(BIOMETRIC_INTEGRATION == DB_TRUE)
        {
            if($this->function != 'insert')
            {
                $iframe = array();
                $iframe[] = new MDiv('', _M('<font color="orange"><h3><p align="center">Cadastro de Biometria</p></h3></font>'));

                // Se a preferência está habilitada.
                if(BIOMETRIC_PERSON_REGISTER == DB_TRUE)
                {
                    $texto = _M("Certifique-se que o software auxiliar da biometria esta corretamente instalado.<br>
                          Você terá um tempo determinado para escolher a melhor digital a ser cadastrada.<br/>
                          O botão salvar, será ativado após o primeiro cadastro de digital.<br/><br/>
                          Clique sobre o dedo que você deseja cadastrar.<br/><br/>");
                    
                    $msg = _M("Após realizar as operações, por questão de segurança, não esqueça-se de alterar a preferência <b>BIOMETRIC_PERSON_REGISTER</b> para 'f' ao invés de 't'."
                            . " Caso não tenha permissão de acesso, contate o administrador do sistema.");
                    
                    $person = MIOLO::_REQUEST('personId');

                    //Verifica se a pessoa já não tem o registro na base
                    $arr = $this->busBiometry->getBiometry($person);

                    if(!is_null($arr->personId))
                    {
                        $text2 = _M("<font color='red'><b>Atenção, este usuário já tem suas digitais cadastradas.<br>Ao realizar uma nova gravação, suas digitais anteriores serão perdidas.<br><br></b></font>");
                    }

                    $iframe[] = new MDiv('', $text2);
                    $iframe[] = new MDiv('', $texto);
                    $iframe[] = new MDiv('', $msg);

                    $iframe[] = new MDiv('', '<iframe id="iframe01" name="iframe01" src="'. BIOMETRIC_URL . 'index.php?person='.$person.'" style="margin-left: 24px; width:1050px; height:550px; border: none;" scrolling="Auto"></iframe>');

                }
                else
                {
                    $msg = _M("Por questões de segurança, o cadastro biométrico está <b>desativado</b>.<br/>"
                            . "Para ativa-lo, troque a preferência <b>BIOMETRIC_PERSON_REGISTER</b>"
                            . " para 't' ao invés de 'f'. Caso não tenha permissão, contate o adminstrador do sistema.");
                    
                    $iframe[] = new MDiv('', $msg); 
                    
                }
                                
                $tabControl->addTab('tabBiometry',_M('Acesso Biométrico', $this->module), $iframe, null, false, false);
                                
            }
            
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        ///////////////////////////
        
        
        
        
        
        
        
        
        
        //Campos do sagu que serão escondidos no gnuteca.
        $sentEmail = new MHiddenField('sentEmail', $this->sentEmail->value, null, FIELD_ID_SIZE,null,null, null);
        $sentEmail->setVisibility(false);
        $photoId = new MTextField('photoId', $this->photoId->value, null, FIELD_ID_SIZE,null,null, null);
        $photoId->setVisibility(false);
        $isAllowPersonalData = new MHiddenField('isAllowPersonalData', $this->isAllowPersonalData->value, null, FIELD_ID_SIZE,null,null, null);
        $isAllowPersonalData->setVisibility(false);
        $operationProcess = new MHiddenField('operationProcess', $this->operationProcess->value, null, FIELD_ID_SIZE,null,null, null);
        $operationProcess->setVisibility(false);        
        $personDv = new MHiddenField('personDv', $this->personDv->value, null, FIELD_ID_SIZE,null,null, null);
        $personDv->setVisibility(false);           
        $personMask = new MHiddenField('personMask', $this->personMask->value, null, FIELD_ID_SIZE,null,null, null);
        $personMask->setVisibility(false);   
        $neighborhood = new MHiddenField('neighborhood', $this->neighborhood->value, null, FIELD_ID_SIZE,null,null, null);
        $neighborhood->setVisibility(false);
        $dateIn = new MHiddenField('dateIn', $this->dateIn->value, null, FIELD_ID_SIZE,null,null, null);
        $dateIn->setVisibility(false);        
        $fields[] = $sentEmail;
        $fields[] = $photoId;
        $fields[] = $isAllowPersonalData;
        $fields[] = $operationProcess;
        $fields[] = $personDv;
        $fields[] = $personMask;
        $fields[] = $neighborhood;
        $fields[] = $dateIn;
        
        //seta os campos no formulário
        $this->setFields($fields);

        //validadores
        $validators[] = new MRequiredValidator('personName');        
        if ( MUtil::getBooleanValue($changePersonPermissions->tabMain) == TRUE )
        {
            $validators[] = new MRequiredValidator('locationTypeId');
            $validators[] = new MRequiredValidator('location');
        }        
        $validators[] = new MEmailValidator('email', '', '');
        $validators[] = new MEmailValidator('emailAlternative', '', '');
     
        $this->setValidators($validators);
        
        GPhotoManager::loadPhoto(MIOLO::_REQUEST('personId'), 'person');
        
    }


    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        $data->login = $data->loginU;
        $data->cityId = $data->cityIdPerson;

        $data->document = $data->documents; //FIXME: as repetitives não funcionam se usar o id "document", foi usado como "documents"
      
        //trata os dados da repetitive de documentos para salvar
        if ( is_array($data->documents) )
        {
            $data->document = $data->documents; //FIXME: as repetitives não funcionam se usar o id "document", foi usado como "documents"

            foreach ( $data->document as $key => $values )
            {
                if ( ($this->function != 'insert') && ( $values->updateData ) )
                {
                    if ( $values->oldDocumentTypeId != $values->documentTypeId )
                    {
                        $this->error(_M('O tipo de documento não pode ser alterado', $this->module));
                        return;
                    }
                }
                
                $data->document[$key]->obs = $values->observationD;
                $data->document[$key]->cityId = $values->cityIdDocument;
            }
        }        

        $bonds = GRepetitiveField::getData('bond');

        if ( $bonds )
        {
            foreach ( $bonds as $key => $values )
            {
                    //Se não tiver oldDateValidate é porque a data do vinculo é a mesma.
                    if ( empty($bonds[$key]->oldDateValidate) )
                    {
                        $bonds[$key]->oldDateValidate = $bonds[$key]->dateValidate;
                    }
            }            
        }

        $data->bond = $bonds;

        //Parse penalty data
        $penalty = GRepetitiveField::getData('penalty');
        $data->phone = $data->personPhone;
        
        if ($penalty)
        {
        	foreach ($penalty as $v)
        	{
                $v->observation = $v->observationP;
                $v->libraryUnitId = $v->libraryUnitId1;
        	}
        }

        $data->penalty = $penalty;
        
        if (  parent::tbBtnSave_click($sender, $data) )
        {
            if ( ! MUtil::getBooleanValue(SAGU_PHOTO_INTEGRATION) )
            {
                //$this->savePhoto( $this->business->personId, $photoData );
                GPhotoManager::savePhoto('photoManager', $this->business->personId);
                
            }
        }
    }
    
    public function loadFields()
    {
        try
        {
            $data = $this->business->getPerson( MIOLO::_REQUEST('personId') );
            $data->personPhone = $data->phone;
            unset($data->phone);
            
            $data->loginU = $this->business->login;
            //Vem sempre vazio porque se não tiver senha deifinida, mantem a da base.
            $data->password = null;
            
            //Define o código da cidade no repetitive da cidade 
            $data->cityIdPerson = $data->cityId;

            //setData no formulário
            $this->setData($data);
            
            // Para resolver bug #35310.
            $this->page->onLoad("document.getElementById('dateBirth').value = '{$data->dateBirth}';");
            
            //Parse penalty data
            $penalty = $data->penalty;
            if ($penalty)
            {
                foreach ($penalty as $i => $v)
                {
                    $penalty[$i]->observationP = $v->observation;
                	$penalty[$i]->libraryUnitId1 = $v->libraryUnitId;
                }
            }
            
            //setData das repetitive
            GRepetitiveField::setData($this->personPhoneParse($data->personPhone), 'personPhone');
            GRepetitiveField::setData($this->personDocumentParse($data->document, true), 'documents');
            GRepetitiveField::setData($this->personBondParse($data->bond), 'bond');
            GRepetitiveField::setData($penalty, 'penalty');
            GRepetitiveField::setData($data->personLibraryUnit, 'personLibraryUnit');

            //obtem a foto caso existe
            $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
            $busFile->folder    = 'person';
            $busFile->fileName  = MIOLO::_REQUEST('personId').'.';
                        
        }
        catch( EDatabaseException $e )
        {
            $this->error( $e->getMessage() );
        }
    }
    
    public function addToTable($args, $forceMode = FALSE)
    {
        $errors = array();
    	$item = $args->GRepetitiveField;
        
        switch($item)
    	{
    		case 'penalty':
    			$args = $this->penaltyParse($args);
    			break;
    		case 'personLibraryUnit':
    			$args = $this->personLibraryUnitParse($args);
                break;
    		case 'personPhone':
    			$args = $this->personPhoneParse($args);
    			break;
            case 'bond':
                $args = $this->personBondParse($args);
            case 'documents':
                $args = $this->personDocumentParse($args);
                break;    
                
                $arrayItem = $args->arrayItemTemp;
                $data = GRepetitiveField::getData($args->GRepetitiveField);
                if ( is_array($data) )
                {
                    foreach( $data as $key => $value )
                    {
                        //identifica o item da repetitive
                        if ( ($value->arrayItem == $arrayItem) && ($args->__mainForm__EVENTTARGETVALUE == 'addToTable') )
                        {
                            if ( $value->linkId != $args->linkId )
                            {
                                $errors[] = _M('O grupo de usuário não pode ser alterado', $this->module);
                            }
                        }
                    }
                }
                
                break;
    	}
        
        $error = null;
        if ( count($errors) > 0 )
        {
            $error = $errors;
        }

    	($forceMode) ? parent::forceAddToTable($args, null, $error) : parent::addToTable($args, null, $error);

    	if ($item == 'penalty')
    	{
    		$operator = GOperator::getOperatorId();
    		$this->page->onLoad("document.getElementById('operator').value = '{$operator}'");
    	}
    }


    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }


    /**
     * Trata os dados da multa ao adicionar um valor
     * 
     * @param $data
     */    
    function penaltyParse($data)
    {
        if (is_array($data))
        {
            $arrData = array();
            for ($i=0, $c=count($data); $i < $c; $i++)
            {
                $arrData[] = $this->penaltyParse($data[$i]);
            }
            return $arrData;
        }
        else if (is_object($data))
        {

            $data->libraryUnitId = $data->libraryUnitId1;
            
            $data->libraryName    = $this->busLibraryUnit->getLibraryUnit($data->libraryUnitId)->libraryName;
            return $data;
        }
    }


    /**
     * Trata os dados da uniade de biblioteca ao adicionar um valor
     * 
     * @param $data
     */
    public function personLibraryUnitParse($data)
    {
    	if (is_array($data))
    	{
    		$arr = array();
    		foreach ($data as $val)
    		{
    			$arr[] = $this->personLibraryUnitParse($val);
    		}
    		return $arr;
    	}
    	else if (is_object($data))
    	{
    		$data->libraryName = $this->busLibraryUnit->getLibraryUnit($data->libraryUnitId)->libraryName;
            return $data;
    	}
    }
    
    
    /**
     * Trata os dados do telefone ao adicionar um valor
     * 
     * @param $data
     * @return dados tratados
     */
    public function personPhoneParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->personPhoneParse($val);
            }
            
            return $arr;
        }
        else if (is_object($data))
        {
        	$domain = BusinessGnuteca3BusDomain::listForSelect('TIPO_DE_TELEFONE', false, true);
            $data->typeDesc = $domain[$data->type];

            return $data;
        }
    }


    /**
     * Método que trata os dados da repetitive de vínculos
     *
     */
    public function personBondParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->personBondParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
            $link = $this->busBond->listBond();
            
            if ( is_array($link) )
            {
                foreach( $link as $key => $values )
                {
                    if ( $values[0] == $data->linkId )
                    {
                        $data->linkIdName = $values[1];
                        break;
                    }
                }
            }
            
            $dataRepetitive = GRepetitiveField::getDataItem($data->arrayItemTemp, $data->GRepetitiveField);
            $data->oldLinkId = $dataRepetitive->linkId;
            $data->oldDateValidate = $dataRepetitive->dateValidate;

            return $data;
        }
    }
    
     /**
     * Método que trata os dados da repetitive de vínculos
     * 
     */
    public function personDocumentParse($data, $loadFields = false)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->personDocumentParse($val, $loadFields);
            }

            return $arr;
        }
        else if (is_object($data))
        {
            //quando for edição, grava qual o tipo de documento original, pois ele compara no salvar, para ver se o usuário mudou o tipo de documento
            if ( $loadFields )
            {
                $data->observationD = $data->obs;
                $data->oldDocumentTypeId = $data->documentTypeId; //grava o tipo anterior para comparar no salvar, pois o usuário não pode mudar o tipo na edição
            }

            $yesNo = GUtil::getYesNo();
            //Trata texto booleano no repetitive field
            $data->isDeliveredText = $yesNo[$data->isDelivered];
            //Trata texto booleano no repetitive field
            $data->isExcusedText = $yesNo[$data->isExcused];
            
            $busDocumentType = $this->MIOLO->getBusiness($this->module, 'BusDocumentType');
            $busCity = $this->MIOLO->getBusiness($this->module, 'BusCity');
            //obtém o nome do documento
            $documentTypeName = $busDocumentType->getDocumentType($data->documentTypeId)->name;

            $cityName = $busCity->getCity($data->cityId)->name;

            //interpreta nome do tipo de documento
            if ( strlen($documentTypeName) > 0 )
            {
                $data->documentTypeIdDesc = $documentTypeName;
            }
            
            //Interpreta nome da cidade
            if ( strlen($cityName) > 0 )
            {
                $data->cityIdDocument = $data->cityId;
                $data->cityNameDocument = $cityName;
                $data->cityName = $cityName;
            }
            
            return $data;
        }
    }
    
}
?>