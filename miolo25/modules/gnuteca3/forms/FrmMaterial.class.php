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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 08/10/2008
 *
 * */
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
$MIOLO->getClass('gnuteca3', 'controls/GDictionaryField');
$MIOLO->getClass('gnuteca3', 'RFID');
$MIOLO->getClass('gnuteca3', 'GWorkflow');

class FrmMaterial extends GForm
{
    /** @var BusinessGnuteca3BusCataloge  */
            public $business,
            $businessMaterialControl,
            $busMaterialHistory,
            $businessTag,
            $localFields,
            $grid,
            $event,
            $url,
            $aux,
            $upBox,
            $repetitiveFields,
            $repeatFieldRequired,
            $requiredFields,
            $controlNumber,
            $leaderFields,
            $spreadsheet,
            $tabs,
            $imagePlusUrl = null,
            $imageMinusUrl = null,
            $busMarcTagListing,
            $busPrefixSuffix,
            $busSeparator;
    protected $fieldType;

    /**
     * Verifica se o operador pode ou não editar a ajuda dos campos.
     * @var boolean
     */
    public $canEditHelp;

    const DIV_CATALOGUE = "divCataloge";
    const DIV_SPREADSHEET = "divSpreadsheet";
    const REPEAT_FIELD_PREFIX_NAME = "gtcRepeatField_";

    function __construct($data)
    {
        $MIOLO = MIOLO::getInstance();
        $this->canEditHelp = GPerms::checkAccess('gtcTagHelp', 'insert', false);
        $this->setBusiness('BusCataloge');
        $this->setInsertFunction('save');
        $this->businessMaterialControl = $MIOLO->getBusiness('gnuteca3', 'BusMaterialControl');
        $this->businessTag = $MIOLO->getBusiness('gnuteca3', 'BusTag');
        $this->busMaterialHistory = $MIOLO->getBusiness('gnuteca3', 'BusMaterialHistory');
        $this->busMarcTagListing = $MIOLO->getBusiness('gnuteca3', 'BusMarcTagListing');
        $this->busPrefixSuffix = $MIOLO->getBusiness('gnuteca3', 'BusPrefixSuffix');
        $this->busSeparator = $MIOLO->getBusiness('gnuteca3', 'BusSeparator');

        $this->function = MIOLO::_REQUEST('function');
        $this->event = GUtil::getAjaxFunction();
        $this->imagePlusUrl = GUtil::getImageTheme('plus-8x8.png');
        $this->imageMinusUrl = GUtil::getImageTheme('minus-8x8.png');

        //determina algumas variáveis no bus e no formulário
        $this->business->controlNumberFather = null;
        $this->business->controlNumber = null;
        $this->business->preCatalogue = $this->isPreCatalogue();
        $this->business->cameFromPreCat = $this->isPreCatalogue();
        //$this->fieldType = 'simple';
        $this->fieldType = 'default';

        switch ( $this->function )
        {
            case 'update':

                $this->controlNumber = $data->controlNumber;

                if ( !is_null($this->controlNumber) && $this->event != "saveSpreadsheet" && $this->event != "createSpreadsheetFields" )
                {
                    $this->business->setLoadFields();
                    $this->business->controlNumber = $this->controlNumber;
                }
                break;


            case 'duplicate':

                $this->controlNumber = $data->controlNumber;
                $this->business->setDuplicateMaterial(true);

                if ( !is_null($this->controlNumber) && $this->event != "saveSpreadsheet" )
                {
                    $this->business->controlNumber = $this->controlNumber;
                }

                break;

            case 'addChildren' :

                $this->business->controlNumberFather = $data->controlNumber;
                break;

            case 'insert':
            default :

                $this->controlNumber = $data->controlNumber;
                break;
        }

        parent::__construct();

        $this->keyDownHandler(112, 113, 114, 115, 116, 117, 118, 119);
        //cria variáveis JS para componente +/-
        $this->addJsCode("var imagePlusURL    = '$this->imagePlusUrl';
                          var imageMinusURL   = '$this->imageMinusUrl';");


        $this->setLabelWidth('300px');

        $this->addJsCode("
        expandRetractContainer = function(ContainerID, ImageID)
        {
            var st = dojo.byId(ContainerID).style.display;

            try
            {
                dojo.byId(ContainerID).style.display  = (st == 'none') ? 'block' : 'none';
            }
            catch(e){}

            try
            {
                dojo.byId(ImageID).src = (st == 'none') ? imageMinusURL    : imagePlusURL;
            }
            catch(e){}
        }
        ");


        $this->setClass("catalogueForm"); //usado no gnuteca.css
    }

    /**
     * Retorna se esta ou não na pré-catalogação
     *
     * @return boolean retorna se esta ou não na pré-catalogação
     */
    public function isPreCatalogue()
    {
        $isPre = MIOLO::_REQUEST('wasSaved') == 'p';
        return MIOLO::getCurrentAction() == 'main:catalogue:preCatalogue' || $isPre;
    }

    /**
     * Cria os campos de form leader
     * Primeira interface que aparece para o usuário
     *
     * @return void
     */
    function mainFields()
    {

        //Testar se tem a preferencia de utilizar o RFID
        if(RFID_INTEGRATION == DB_TRUE)
        {
            $nCookie = RFID_COOKIE;
            
            if(! isset($_COOKIE[$nCookie]))
            {
                $op = RFID::getTerms();
                $texto = "<p align='center'>A preferência <b>RFID_INTEGRATION</b> esta <b>habilitada</b>.<br>".
                         "Identifique o terminal que você esta utilizando.<br>".
                         "Após salvar, será gerado um <b>cookie permanente</b> em seu computador.<br>".
                         "Caso o mesmo seja apagado, será necessário identificar novamente.";
                                
                $termIdent[] = new MDiv('texto', $texto);
                $termIdent[] = new MLabel('Identificação:');
                $termIdent[] = $cmb = new GContainer(null, array( new GSelection('termDir', null, null, $op, false, false, null, false)));
                $termIdent[] = $bt = new MButton('btnSaveCookie', _M('Salvar', $this->module), GUtil::getAjax('makeCookieDir', ''), GUtil::getImageTheme('accept-16x16.png'));
                $bt->addAttribute('title', _M('Salvar terminal de acesso no navegador.', $this->module));
                
                //Aqui irá definir o cookie que será usado para definir o diretório da comunicação com o RFID
                $this->injectContent($termIdent, false, _M("Identificação de terminal:"), '500px');
            }
            else
            {
                $valor = $_COOKIE[$nCookie];
            }
        }
        //Fim de teste de RFID
        
        
        //limpa todos os campos repetitivos da catalogação
        $repetitiveSession = $_SESSION['GRepetitiveField'];

        if ( is_array($repetitiveSession) )
        {
            foreach ( $repetitiveSession as $repetitive => $data )
            {
                if ( stripos($repetitive, 'gtcRepeatField_') === 0 )
                {
                    GRepetitiveField::clearData($repetitive);
                }
            }
        }

        $leaderFields = $this->business->getLeaderFields();

        if ( is_array($leaderFields) )
        {
            foreach ( $leaderFields as $index => $content )
            {
                $fields[] = $this->addDefaultField($content, false, false); //este é sempre default
            }
        }

        //controlar quando é leaderFields
        $fields['leaderFields'] = new MTextField('leaderFields', DB_TRUE);
        $fields['leaderFields']->addStyle('display', 'none');

        $fields[] = new MButton('btnNext', _M('Próximo', $this->module), ":createSpreadsheetFields", Gutil::getImageTheme('next-16x16.png'));

        $div[] = new MDiv(self::DIV_CATALOGUE, $fields);
        $this->setFields($div, true);
        
        //Testar se tem a preferencia de utilizar o RFID
        if(RFID_INTEGRATION == DB_TRUE)
        {
            //Recebe o nome do cookie
            $nCookie = RFID_COOKIE;
            
            //Verifica se o cookie já está setado
            if(! isset($_COOKIE[$nCookie]))
            {
                //Caso não esteja, chama o método para setar o cookie
                $this->page->onload("miolo.doAjax('showMessageCookie', '', '__mainForm');");
            }
            else
            {
                //Caso esteja, atribui o valor
                $valor = $_COOKIE[$nCookie];
            }
        }
    }
    
    /**
     * Método ajax para escolha do terminal.
     * Este método é utilizado na Integração com RFID
     * Criado por: Tcharles Silva
     */
    public function showMessageCookie()
    {
        $op = RFID::getTerms();
        $texto = "<p align='center'>A preferência <b>RFID_INTEGRATION</b> esta <b>habilitada</b>.<br>".
                 "Identifique o terminal que você esta utilizando.<br>".
                 "Após salvar, será gerado um <b>cookie permanente</b> em seu computador.<br>".
                 "Caso o mesmo seja apagado, será necessário identificar novamente.";

        $termIdent[] = new MDiv('texto', $texto);
        $termIdent[] = new MLabel('Identificação:');
        $termIdent[] = $cmb = new GContainer(null, array( new GSelection('termDir', null, null, $op, false, false, null, false)));
        $termIdent[] = $bt = new MButton('btnSaveCookie', _M('Salvar', $this->module), GUtil::getAjax('makeCookieDir', ''), GUtil::getImageTheme('accept-16x16.png'));
        $bt->addAttribute('title', _M('Salvar terminal de acesso no navegador.', $this->module));

        //Aqui irá definir o cookie que será usado para definir o diretório da comunicação com o RFID
        $this->injectContent($termIdent, false, _M("Identificação de terminal:"), '500px');
    }
    
    
    /**
     * Salva cookie para identificar terminal
     * Este método é utilizado na Integração com RFID
     * Criado por: Tcharles Silva
     */
    public function makeCookieDir()
    {
        $var = $this->getFormValue("termDir");
        if(empty($var))
        {
            if(!$var === 0)
            {
                return;
            }
        }
        $var++;
        $cookieValue = "termDir".$var;
        
        //Necessário fechar o prompt
        GPrompt::question(_M("Você irá se identificar como terminal de acesso #$var. <br>Salvar?"), 'javascript:' . GUtil::getAjax('gravaCookie', $cookieValue));
    }

    /**
     * Grava o Cookie no terminal
     * Este método é utilizado na Integração com RFID
     * Criado por: Tcharles Silva
     */
    public function gravaCookie($termDir)
    {
        //Criar o cookie do diretório
        $res = setcookie(RFID_COOKIE, $termDir);
        
        if($res)
        {
            $MIOLO = MIOLO::getInstance();
            $endereco = $MIOLO->getCurrentURL();
            $this->page->redirect($endereco);
        }else
        {
            GPrompt::error("Não foi possível gravar a identificação.");
        }
    }    
    

    /**
     * Evento chamado após apertar no "next" da primeira aba, realmente monta
     * o formulário de catalogação
     */
    function createSpreadsheetFields($args)
    {
        
        $this->imagePlusUrl = Gutil::getImageTheme('plus-8x8.png');
        $this->imageMinusUrl = Gutil::getImageTheme('minus-8x8.png');
        $this->localFields = null;

        //define os parametros na classe Catalogue
        $this->business->setFormValueLeaderFields($args);
        $leaderString = $this->business->getLeaderString();

        $this->business->controlNumber = $args->controlNumber;
        $this->spreadsheet = $this->business->getSpreadsheet(); // busca a planilha correspondente
        
        $this->localFields = null;

        if ( $this->function == "update" && !$this->isPreCatalogue() )
        {
            $entryDate = new GDate($this->businessMaterialControl->getEntraceDate($args->controlNumber));
            $lastChange = new GDate($this->businessMaterialControl->getLastChangeDate($args->controlNumber));
            $lastChange = $lastChange->generate();
        }
        else
        {
            $entryDate = GDate::now();
        }

        //para funcionar corretamente quando for adicionar um filho
        if ( MIOLO::_REQUEST('function') == 'addChildren' || MIOLO::_REQUEST('function') == 'duplicate' )
        {
            unset($args->controlNumber);
        }

        if ( $args->controlNumber && !$this->isPreCatalogue() )
        {
            $firstOperator = $this->busMaterialHistory->firstOperator($args->controlNumber);
            $lastOperator = $this->busMaterialHistory->lastOperator($args->controlNumber);

            if ( !$firstOperator )
            {
                $firstOperator = _M("Sem operador registrado", $this->module);
            }

            if ( !$lastOperator )
            {
                $lastOperator = _M("Sem revisões registradas", $this->module);
            }
        }
        else
        {
            $firstOperator = GOperator::getOperatorId();
        }

        $entryDate = new MTextField('entryDate', $entryDate->generate(), _M("Entrada", $this->module), (FIELD_DESCRIPTION_SIZE / 2 ) - 4, '', null, true);
        $firstOperator = new MTextField('firstOperator', $firstOperator, null, (FIELD_DESCRIPTION_SIZE / 2), null, null, true);
        $lastChange = new MTextField('lastChange', $lastChange, _M("Última alteração", $this->module), (FIELD_DESCRIPTION_SIZE / 2 ) - 4, '', null, true);
        $lastOperator = new MTextField('lastOperator', $lastOperator, null, (FIELD_DESCRIPTION_SIZE / 2), null, null, true);

        $staticFields[] = new GContainer('', new MTextField('controlNumber', $args->controlNumber, _M("Número de controle", $this->module), FIELD_DESCRIPTION_SIZE, '', null, true));
        $staticFields[] = new GContainer('', new MTextField('marcLeader', $leaderString, _M("Líder", $this->module), FIELD_DESCRIPTION_SIZE, '', null, true));
        $staticFields[] = new GContainer('containerFirstOperator', array( $entryDate, $firstOperator ));
        $staticFields[] = new GContainer('containerLastOperator', array( $lastChange, $lastOperator ));

        $this->localFields[] = $group = $this->addTagGroup('staticField', null, $staticFields, false);
        $inner = $group->getInner();
        $inner[1]->addStyle('width', '100%');

        //campo que controla se ja foi clicado no salvar e se foi salva na pré ou normal
        $this->localFields['wasSaved'] = new MTextField('wasSaved');
        $this->localFields['wasSaved']->addStyle('display', 'none');

        $this->getSpreadsheetFields();
        $hidden = $this->business->getHiddenDefaultFields();
        
        if ( is_array($hidden) )
        {
            foreach ( $hidden as $eti => $cont )
            {
                foreach ( $cont as $subf => $value )
                {
                    $this->localFields[] = new MHiddenField("spreeadsheetField_{$eti}_{$subf}_defaultValue", $value);
                }
            }
        }

        $this->setResponse(new MDiv(self::DIV_SPREADSHEET, $this->localFields), self::DIV_CATALOGUE);
        //faz parser de campos data, sem isso os campos MCalendarField não funcionam
        $this->page->onload('dojo.parser.parse();');
        //reinicia valores de cópia de campos marc, sem isso a cópia de campos não funciona
        $this->page->onload("gnuteca.autoCompleteMarcFieldsCopy = Array();");
        //altera a toolbar
        $toolbar = $this->getToolBar(null, true);

        $this->setResponse(new MDiv('divTool', $toolbar), 'toolBarContainer');
    }

    function getSpreadsheetFields()
    {
        $this->controlNumber = MIOLO::_REQUEST('controlNumber');

        $tabControl = new GTabControl('tabControlCatalogue');

        //Define spreadsheets na sessao para ser obtido no editFromTable() - ref. ticket #5908
        $session = new MSession();
        $session->set('spreadsheets', $this->spreadsheet);

        $businessLinkOfFields = $this->MIOLO->getBusiness($this->module, 'BusLinkOfFieldsBetweenSpreadsheets');

        foreach ( $this->spreadsheet as $tabName => $etiquetas )
        {
            $tabGroups = null;

            foreach ( $etiquetas as $etiquetaId => $etiContent )
            {
                $subFields = $etiContent->subFields;
                $tabFields = null;
                $repetitiveFields = null;
                $permiteRepetitiveField = ($etiContent->isRepetitive == 't');

                if ( !$subFields )
                {
                    continue;
                }
                
                // Adiciona os indicadores
                if ( $etiContent->indicadores )
                {
                    
                    foreach ( $etiContent->indicadores as $i => $indValues )
                    {
                        $indValues->type = FIELD_TYPE_COMBO;
                        $tabFields[] = $this->addMaterialField($indValues, true, false, false);
                    }
                }
                
                $displayGroupForce = false;
                // Verifica se Tem repetitive field ca TAg
                if ( $permiteRepetitiveField )
                {
                    if ( $etiquetaId == '949' ) //hardcoded
                    {
                        //Linha modificada para Integraçao com RFID
                        if(RFID_INTEGRATION == DB_TRUE)
                        {
                            $opts = array( 'gravaTag', 'edit', 'remove', 'up', 'down', 'updateButton', 'includeButton' );
                        }else
                        {
                            $opts = array( 'edit', 'remove', 'up', 'down', 'updateButton', 'includeButton' );
                        }
                        
                    }
                    else
                    {
                        $opts = array( 'edit', 'remove', 'up', 'down', 'updateButton' );
                    }

                    $this->repetitiveFields[$etiquetaId] = new GRepetitiveField(self::REPEAT_FIELD_PREFIX_NAME . $etiquetaId, $etiContent->name, null, true, $opts, 'vertical');
                    $this->repetitiveFields[$etiquetaId]->clearData();
                    $this->repetitiveFields[$etiquetaId]->setOverflowWidth("85%");
                    $this->repetitiveFields[$etiquetaId]->actionCelWidth = 80;
                    $this->repetitiveFields[$etiquetaId]->setValidators();
                    
	            // Percorre os subfields que tem repetitive, e adiciona em um array para o setData do repetitive. Feito para os casos 773.w e 520.a, pois alguns subcampos são repetitivos e outros não.		
                    $repetitiveLoadValueFields = array();
                    
                    if ( $this->function == 'update' )
                    {
                        foreach ( $subFields as $subFieldsId => $subFContent )
                        {
                            if ( $subFContent->isRepetitive == DB_TRUE )
                            {
                                // Prepara o conteúdo.
                                if ( $subFContent->loadValue )
                                {
                                    foreach ( $subFContent->loadValue as $line => $loadValue )
                                    {
                                        $positionObject = $subFContent->fieldName;
                                        $repetitiveLoadValueFields[$line]->$positionObject = $loadValue->$positionObject;
                                    }
                                }

                                // Prepara o sufixo.
                                if ( $subFContent->loadSuffix )
                                {
                                    foreach ( $subFContent->loadSuffix as $line => $suffixValue )
                                    {
                                        $positionObject = 'suffix_' . $subFContent->fieldName;
                                        $repetitiveLoadValueFields[$line]->$positionObject = $suffixValue;
                                    }
                                }

                                // Prepara o prefixo.
                                if ( $subFContent->loadPrefix )
                                {
                                    foreach ( $subFContent->loadPrefix as $line => $prefixValue )
                                    {
                                        $positionObject = 'prefix_' . $subFContent->fieldName;
                                        $repetitiveLoadValueFields[$line]->$positionObject = $prefixValue;
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        $repetitiveLoadValueFields = $etiContent->loadValue;
                    }
                    
                    if ( $this->function != 'insert' && count($repetitiveLoadValueFields) )
                    {
                        $displayGroupForce = true;
                        $this->repetitiveFields[$etiquetaId]->setData($repetitiveLoadValueFields);
                    }

                    $repetitiveFieldsValidators = null;
                }
                
                // Percorre os subfields da TAG
                foreach ( $subFields as $subFieldsId => $subFContent )
                {
                    if ( $subFContent->isActive == 'f' )
                    {
                        continue;
                    }

                    $subFContent->hasRepetitive = $permiteRepetitiveField;
                    $subFContent->fieldId = $etiquetaId;

                    $readOnly = $this->checkReadOnly($subFContent);

                    if ( $permiteRepetitiveField && $subFContent->isRepetitive == DB_TRUE )
                    {
                        //Verifica se o campo repetitivo é do tipo reference, não pode deixar as opções visíveis
                        $businessLinkOfFields->categorySonS = $this->business->getCategory();
                        $businessLinkOfFields->levelSonS =  $this->business->level;
                        $businessLinkOfFields->tagSonS =  "$etiquetaId.$subFieldsId";
                        $businessLinkOfFields->typeS =  2; // Se for referencia
                        $linkReferences = $businessLinkOfFields->searchLinkOfFieldsBetweenSpreadsheets(true);

                        //Se campo da planilha tiver um link do tipo referencia com uma planilha pai
                        if ( !empty($linkReferences) )
                        {
                            //Desabilita botoes e ações do repetitive
                            $this->repetitiveFields[$etiquetaId]->readonly = true;                            
                        }
                        
                        $repetitiveFields[] = $this->addRepetitiveField($subFContent, $readOnly);
                        
                        if ( isset($subFContent->repeatFieldValidator) && is_array($subFContent->repeatFieldValidator) )
                        {
                            foreach ( $subFContent->repeatFieldValidator as $typeValidator )
                            {
                                switch ( $typeValidator )
                                {
                                    case 'required' :
                                        $repetitiveFieldsValidators[] = new MRequiredValidator($subFContent->fieldName, $subFContent->label);
                                        break;
                                    case 'unique' :
                                        $repetitiveFieldsValidators[] = new GnutecaUniqueValidator($subFContent->fieldName, $subFContent->label);
                                        break;
                                    case 'date' :
                                        $repetitiveFieldsValidators[] = new GnutecaDateValidator($subFContent->fieldName, $subFContent->label);
                                        break;
                                }
                            }
                        }
                    }
                    else
                    {
                        $tabFields[] = $this->addMaterialField($subFContent, ($etiquetaId != MARC_FIXED_DATA_FIELD), $readOnly, false);
                    }

                    if ( isset($subFContent->workValidator) && is_array($subFContent->workValidator) )
                    {
                        $this->business->localValidator[] = $subFContent;
                    }

                    if ( isset($subFContent->formValue) && strlen($subFContent->formValue[0]) ||
                            isset($subFContent->baseValue) && strlen($subFContent->baseValue[0]) ||
                            isset($subFContent->defaultValue) && count($subFContent->defaultValue) ||
                            isset($subFContent->loadValue) && count($subFContent->loadValue)
                    )
                    {
                        $displayGroupForce = true;
                    }
                }

                // Se tem repetitive fields, adiciona eles no grupo de campos.
                if ( !is_null($repetitiveFields) )
                {
                    //Variável $etiquetaId tem os valores das etiquetas com campo repetitivo
                    $tabFields[] = $this->generateRepetitiveFields($etiquetaId, $repetitiveFields);

                    if ( !is_null($repetitiveFieldsValidators) )
                    {
                        $this->repetitiveFields[$etiquetaId]->setValidators($repetitiveFieldsValidators);
                    }
                }

                // Adiciona o grupo de campos
                $tabGroups[] = $this->addTagGroup($etiquetaId, $etiContent, $tabFields, $displayGroupForce);
            }
            
            $tabId = 'tab' . str_replace(" ", "_", $tabName);
            $tabName = $etiquetas['tabName'] ? $etiquetas['tabName'] : $tabName;
            $tabControl->addTab($tabId, $tabName, $tabGroups);
        }


        //inicia a parte de capa/cover
        $GFileUploader = new GFileUploader('Anexe a capa do material', false, null, 'coverMaterial');
        GFileUploader::setLimit(1, 'coverMaterial'); //somente uma imagem por capa
        GFileUploader::clearData('coverMaterial');
        GFileUploader::setExtensions(array( 'png', 'jpg', 'jpeg', 'gif' ), array('php', 'class', 'js'), 'coverMaterial');

        $cFields[] = $GFileUploader;

        if ( MUtil::getBooleanValue(GB_INTEGRATION) )
        {
            $cFields[] = new MDiv('', new MButton('getCoverFromGoogle', _M('Obter capa do Google'), ':getCoverFromGoogle', GUtil::getImageTheme('google-16x16.png')));
        }

        //carrega a capa
        if ( $this->function == "update" && !is_null($this->controlNumber) )
        {
            $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
            $busFile->folder = $this->isPreCatalogue() ? 'coverpre' : 'cover'; //escolhe a pasta certa para escolher a imagem
            $busFile->fileName = $this->controlNumber . '.'; //o ponto garante a escolha da imagem certa
            $GFileUploader->setData($busFile->searchFile(true), 'coverMaterial');
        }

        $tabControl->addTab('tabCover', _M('Capa', $this->module), $cFields);

        $this->localFields[] = $tabControl;

        $this->business->saveContent();
    }

    /**
     * Importa capa do google para o material atual
     *
     * @param stdClass $args
     */
    public function getCoverFromGoogle($args)
    {
        $controlNumber = $args->controlNumber;

        if ( !$controlNumber )
        {
            throw new Exception(_M('É necessário salvar o material antes de obter a capa!', 'gnuteca3'));
        }

        $isbnFieldId = new GString('spreeadsheetField_' . MARC_ISBN_TAG);
        $isbnFieldId->replace('.', '_');

        $isbn = $args->$isbnFieldId;

        $busGoogle = $this->MIOLO->getBusiness('gnuteca3', 'BusGoogleBook');
        $img = $busGoogle->getCover($isbn);

        if ( $img )
        {
            $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
            //usa png como extensão temporária
            $path = BusinessGnuteca3BusFile::getAbsoluteFilePath('tmp', $controlNumber, 'png');
            //salva capa em arquivo temporários
            $ok = $busFile->streamToFile($img, $path, null, true);

            if ( !$ok )
            {
                throw new Exception(_M('Impossível salvar capa no caminho tempporário "@1"', 'gnuteca3', $path));
            }

            //obtem arquivo do servidor
            $file = $busFile->getFile('tmp/' . $controlNumber . '.png');

            if ( !$file )
            {
                throw new Exception(_M('Impossível encontrar arquivo após obtenção!', 'gnuteca3'));
            }

            $fields[] = new MDiv('', _M('Verifique a imagem abaixo, caso esteja correta clique no botão confirmar', 'gnuteca3'));
            $fields[] = new MDiv('', _M('Lembre-se que essa imagem irá substituir a capa atual, caso exista!', 'gnuteca3'));
            $fields[] = new MDiv('', _M('Algumas capas podem existir somente em resolução baixa.', 'gnuteca3'));
            $fields[] = new MImage('imgCoverGoogle', '', $file->mioloLink);
            $buttons[] = new MButton('btnGetCoverConfirm', _M('Confirmar'), GUtil::getAjax('getCoverFromGoogleConfirm'), GUtil::getImageTheme('accept-16x16.png'));
            $buttons[] = GForm::getCloseButton();

            $fields[] = new MDiv('divButtons2', $buttons);

            $this->injectContent($fields, false, true, '600px !Iimportant');
        }
        else
        {
            throw new Exception(_M('Não foi possível encontrar a capa para o ISBN @1.', 'gnuteca3', $isbn));
        }
    }

    /**
     * Importa capa do google para o material atual
     *
     * @param stdClass $args
     */
    public function getCoverFromGoogleConfirm($args)
    {
        $controlNumber = $args->controlNumber;

        $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
        $file = $busFile->getFile('tmp/' . $controlNumber . '.png');
        $realExtension = $busFile->getRealExtensionForImage($file);

        if ( !$realExtension )
        {
            $realExtension = 'png';
        }

        $newPath = BusinessGnuteca3BusFile::getAbsoluteFilePath('cover', $controlNumber, $realExtension);
        //move o arquivo temporário para o novo
        $ok = copy($file->absolute, $newPath);

        if ( !$ok )
        {
            throw new Exception(_M('Impossível mover arquivo de capa para pasta oficial', 'gnuteca3'));
        }

        //remove o arquivo temporário
        unlink($file->absolute);

        $this->information(_M('Capa obtida com sucesso!', 'gnuteca3'));
        GFileUploader::setData(array( $file ), 'coverMaterial');
        GFileUploader::generateTable('coverMaterial'); //atualiza a tabela
    }

    /**
     * Verifica se deve setar readonly algum campo
     *
     * @param object $subFContent
     * @return boolean
     */
    function checkReadOnly($subFContent)
    {
        if ( $subFContent->tag == MARC_EXEMPLARY_EXEMPLARY_STATUS_TAG )
        {
            //return true;
        }
        elseif ( $subFContent->tag == MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_TAG && $this->function == 'update' )
        {
            //return true;
        }

        if ( isset($subFContent->workValidator) && is_array($subFContent->workValidator) )
        {
            if ( in_array("readonly", $subFContent->workValidator) )
            {
                return true;
            }
        }

        return $subFContent->readOnly;
    }

    /**
     * Cria um container de abrir/fechar
     *
     * @param string $tagNumber número da tag
     * @param stdClass $tagObject objeto com os dados da tag
     * @param array $fields array de campos
     * @param boolean $displayBlockForce força display block, ou seja, mostrar a caixa
     * @return MVContainer
     */
    function addTagGroup($tagNumber, $tagObject, $fields, $displayBlockForce = false)
    {
        if ( $tagNumber == MARC_FIXED_DATA_FIELD )
        {
            array_unshift($fields, $separator);
            return new MVContainer("contTagGroup-$tagNumber", $fields);
        }

        $fieldType = $this->fieldType;

        //caso o tagObject for nulo é o campos estáticos
        if ( is_null($tagObject) )
        {
            $leaderFields = $this->business->getLeaderFields();
            $label = $this->business->getCategory() . ': ' . $leaderFields['000-6']->fieldContent->options[MIOLO::_REQUEST('000-6')] . ' - ' . $leaderFields['000-7']->fieldContent->options[MIOLO::_REQUEST('000-7')] . ' - ' . $leaderFields['000-17']->fieldContent->options[MIOLO::_REQUEST('000-17')];
            $tagObject->name = $label;
            $labelValue = $label;
            $tagObject->baseGroupDisplay = 'none';
            $fieldType = 'default';
        }
        else
        {
            $labelValue = "{$tagNumber} - {$tagObject->name}";
            // o .# é de campo principal
            $help = $this->getHelpField('spreeadsheetField_' . $tagNumber . '_#', $tagNumber . '.#', $tagObject->help);
        }

        if ( $fieldType == 'default' )
        {
            $imagePlus = new MImage("iconTagGroup-$tagNumber", null, $displayBlockForce ? $this->imageMinusUrl : $this->imagePlusUrl);
            $imagePlus->addAttribute("style", "margin-top:4px; margin-right: 4px; float:left");
            $label = new MDiv($tagObject->name, '<b>' . $labelValue . '</b>');

            $containerH = new GContainer("containerLabelFieldsGroup", array( $imagePlus, $label ));
            $containerH->addAttribute('onclick', "expandRetractContainer('bgTagGroup-{$tagNumber}','iconTagGroup-{$tagNumber}')");
            $containerH->addStyle('cursor', 'pointer');
            $containerH->addStyle('float', 'left');

            $containerTagGroup = new MBaseGroup("bgTagGroup-$tagNumber", null, $fields, 'vertical', 'css', MControl::FORM_MODE_SHOW_NBSP);
            $containerTagGroup->addStyle('display', $displayBlockForce ? "block" : $tagObject->baseGroupDisplay );
            $containerTagGroup->addStyle('float', 'left');

            //a div com todo conteúdo
            $groupConteiner = new MDiv("contTagGroup-$tagNumber", array( $separator, $containerH, $help, $containerTagGroup ));
        }
        else
        {
            //$fields[] = $help; //TODO FIXME corrigir
            $groupConteiner = new MDiv("contTagGroup-$tagNumber", $fields);
        }

        return $groupConteiner;
    }

    /**
     * Constroi a barra de ferramentas personalizada da catalogação.
     *
     * @param booloean $formContent conteúdo do formulário padrão
     * @param boolean $spreadSheet diferencia se é campos leader os planilha
     * @return GToolBar
     */
    public function getToolBar($formContent, $spreadSheet = false, $save = false)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $wasSaved = MIOLO::_REQUEST('wasSaved');

        $imageMaterialMoviment = GUtil::getImageTheme('materialMovement-32x32.png');
        $this->_toolBar = new GToolBar('toolBar', $MIOLO->getActionURL($module, $action));

        //verifica se existe um código leader na url, caso tiver, é porque está inserindo um material diferente do padrão
        $leaderString = MIOLO::_REQUEST('leaderString');

        if ( $leaderString )
        {
            $url = $this->MIOLO->getActionURL('gnuteca3', "main:catalogue:material&frm__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=dinamicMenu&leaderString={$leaderString}");
        }
        else
        {
            $url = $this->MIOLO->getActionURL('gnuteca3', 'main:catalogue:material&frm__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=insert');
        }

        //refaz botão new para entrar na inserção de material sempre
        $imageNew = GUtil::getImageTheme('toolbar-new.png');
        $this->_toolBar->addButton(MToolBar::BUTTON_NEW, null, $url, _M('Clique para inserir um novo registro'), true, $imageNew, $imageNew);

        $imageSave = GUtil::getImageTheme('toolbar-save.png');
        $this->_toolBar->addButton("spreadSheetSave", null, ":saveSpreadsheet", _M("Salvar F3", $this->module), true, $imageSave, $imageSave);

        $imageSavePre = GUtil::getImageTheme('toolbar-savePreCatalogue.png');
        $this->_toolBar->addButton("spreadSheetSavePreCatalogueButton$incrementName", null, ":saveSpreadsheetPreCatalogue", _M("Salvar na Pré-catalogação F8", $this->module), true, $imageSavePre, $imageSavePre);

        if ( $formContent )
        {
            $this->_toolBar->setFormContent($formContent);
        }

        if ( !$spreadSheet )
        {
            $this->_toolBar->disableButtons(array( "spreadSheetSave", "spreadSheetSavePreCatalogueButton$incrementName" ));
        }
        else //campos da planilha
        {
            if ( !$this->isPreCatalogue() && $this->function == 'update' )
            {
                $this->_toolBar->disableButtons(array( "spreadSheetSavePreCatalogueButton$incrementName" ));
            }

            if ( WF_PURCHASE_REQUEST_CLASS == 'wfPurchaseRequestDefault' )
            {
                if ( !$this->isPreCatalogue() )
                {
                    $this->_toolBar->addRelation(_M('Solicitação de compras', 'gnuteca3'), GUtil::getImageTheme('purchaseRequest-16x16.png'), 'javascript:' . Gutil::getAjax('relationPurchaseRequest'));
                }
                else
                {
                    if ( $this->isPreCatalogue() && ( $this->function == 'update' || $wasSaved ) )
                    {
                        $this->_toolBar->addRelation(_M('Solicitação de compras', 'gnuteca3'), GUtil::getImageTheme('purchaseRequest-16x16.png'), 'javascript:' . Gutil::getAjax('relationPurchaseRequest'));

                        if ( !$save && $this->toolBarButtons[GToolBar::BUTTON_RELATION] )
                        {
                            $this->_toolBar->disableButtons(array( GToolBar::BUTTON_RELATION ));
                        }
                    }
                }
            }
        }

        //remover botões
        $this->_toolBar->removeButtons(array( MToolBar::BUTTON_SAVE, MToolBar::BUTTON_DELETE, MToolBar::BUTTON_RESET, "btnFormContent" ));

        //desativa botão de salvar na pré-catalogação
        if ( $save )
        {
            $this->_toolBar->disableButtons(array( 'spreadSheetSavePreCatalogueButton' ));
        }

        return $this->_toolBar;
    }

    /**
     * Relação com solicitação de compras
     *
     * @param stdClass $args
     */
    public function relationPurchaseRequest($args)
    {
        //só deixa relacionar caso já esteja salvo ou tenha número de controle sem ser da pré
        if ( !( $args->wasSaved == DB_TRUE || ( $args->controlNumber && !$this->isPreCatalogue() ) ) )
        {
            throw new Exception(_M("Impossível relacionar materiais novos ou na pré-catalogação!", 'gnuteca3'));
        }

        $fields[] = new MSpan('', _M('Informe o código da solicitação', 'gnuteca3'));
        $controls[] = new MTextField('purchaseRequestId', '', '', FIELD_ID_SIZE);

        //define se a caixa deve ser marcada ou não
        $checkExternalId = false; //padrão false

        $busFormContent = $this->MIOLO->getBusiness('gnuteca3', 'BusFormContent');

        //busca conteúdo do formulário
        $busFormContent = new BusinessGnuteca3BusFormContent();
        $busFormContent->operatorS = GOperator::getOperatorId();
        $busFormContent->formEqualS = 'frmmaterial';
        $busFormContent->formContentType = FORM_CONTENT_TYPE_OPERATOR;
        $formContent = $busFormContent->searchFormContent(true);
        $formContent = $formContent[0];

        if ( $formContent->formContentId )
        {
            //de posse do conteúdo do formulário busca detalhes
            $formContent = $busFormContent->getFormContent($formContent->formContentId);
            $formContentDetail = $busFormContent->formContentDetail;

            //passa pelos detalhes procurando o campo especifico
            if ( is_array($formContentDetail) )
            {
                foreach ( $formContentDetail as $line => $detail )
                {
                    if ( $detail->field == 'externalId' )
                    {
                        $checkExternalId = MUtil::getBooleanValue($detail->value);
                    }
                }
            }
        }

        $controls[] = new MCheckBox('externalId', 'externalId', '', $checkExternalId);
        $controls[] = new MLabel(_M('Código externo', 'gnuteca3'));

        $fields[] = new MHContainer('', $controls);

        $buttons[] = new MButton('btnYes', _M('Confirmar', 'gnuteca3'), GUtil::getAjax('relationPurchaseRequestConfirm'), GUtil::getImageTheme('accept-16x16.png'));
        $buttons[] = GForm::getCloseButton();

        $fields[] = new MDiv('', $buttons);

        $this->setFocus('purchaseRequestId');

        $this->injectContent($fields, false, _M('Confirmar relação com solicitação de compra', 'gnuteca3'), '350px');
    }

    /**
     * Confirmação da relação com solicitação de compras
     *
     * @param stdClass $args
     */
    public function relationPurchaseRequestConfirm($args)
    {
        $this->MIOLO->getClass('gnuteca3', 'workflow/wfPurchaseRequest');

        $ok = wfPurchaseRequest::relationWithMaterialCirculation($args->purchaseRequestId, $args->controlNumber, $args->externalId == 'externalId' ? true : false );

        if ( $ok )
        {
            $this->information(_M('Relação efetuada com sucesso!', 'gnuteca3'));
        }
        else
        {
            throw new Exception(_M('Impossível efetuar a relação.'));
        }
    }

    /**
     * Mostra ajuda da tag (campo marc)
     *
     * @param string $args a tag em si, exemplo 100.a
     */
    public function showHelp($args)
    {
        $tag = explode('.', MIOLO::_REQUEST('tag'));
        $busTag = $this->MIOLO->getBusiness('gnuteca3', 'BusTag');
        $result = $busTag->getTag($tag[0], $tag[1]);

        if ( !$result )
        {
            $this->information(_M('Impossível encontrar a Tag', $this->module));
            return;
        }

        if ( GPerms::checkAccess('gtcTagHelp', 'insert', false) && !MIOLO::_REQUEST('edit') )
        {
            $buttons[] = new MButton('btnEditHelp', _M('Editar', $this->module), Gutil::getAjax('showHelp', array( 'edit' => true, 'tag' => MIOLO::_REQUEST('tag') )), Gutil::getImageTheme('save'));
        }

        if ( !MIOLO::_REQUEST('edit') )
        {
            $content[] = new MDiv('', $result->help);
        }
        else
        {
            $content[] = new MDiv('helpContentDiv', $editor = new MEditor('helpContent', $result->help));
            $editor->disableElementsPath();
            $buttons[] = new MButton('btnSaveHelp', _M('Salvar', $this->module), Gutil::getAjax('saveHelp', array( 'tag' => MIOLO::_REQUEST('tag') )) . "javascript:meditor.remove('helpContent'); ", Gutil::getImageTheme('save'));
            $js = "javascript:meditor.remove('helpContent'); ";
        }

        $js .= GUtil::getCloseAction();
        $buttons[] = new MButton('btnClose', _M('Fechar'), $js, GUtil::getImageTheme('exit-16x16.png'));
        $content[] = new MDiv('', $buttons);

        $this->injectContent($content, false, _M('Ajuda para o subcampo', $this->module) . ' ' . $tag[0] . '.' . $tag[1] . ' - ' . $result->description);
    }

    /**
     * Salva as informações de ajuda.
     *
     * @param stdClass $args
     * @return null
     */
    public function saveHelp()
    {
        $tag = explode('.', MIOLO::_REQUEST('tag'));
        $busTag = $this->MIOLO->getBusiness('gnuteca3', 'BusTag');
        $busTag = new BusinessGnuteca3BusTag();
        $result = $busTag->getTag($tag[0], $tag[1]);
        $busTag->tag = null; //para não fazer update dos filhos
        $urlHelpImage = GUtil::getImageTheme('help-16x16');

        if ( $busTag->help == MIOLO::_REQUEST('helpContent') )
        {
            $this->information(_M('Nada foi modificado.', $this->module));
            return;
        }

        $msg = _M('Ajuda salva com sucesso!.', $this->module);

        if ( !MIOLO::_REQUEST('helpContent') )
        {
            $urlHelpImage = GUtil::getImageTheme('helpAdd-16x16.png');
            $msg = _M('Ajuda removida com sucesso.', $this->module);
        }

        $busTag->help = MIOLO::_REQUEST('helpContent');
        $busTag->updateTag();

        //atualiza a imagem de ajuda
        $this->page->onload("dojo.byId('help_spreeadsheetField_{$tag[0]}_{$tag[1]}Img').src='$urlHelpImage';");
        $this->information($msg);
    }

    /**
     * Retorna a diferença entre dois textos, função utilizada no histórico
     * @param string $previousContent conteúdo anterior
     * @param string $currentContent conteúdo atual
     * @return string conteúdo html
     */
    public function diffText($previousContent, $currentContent)
    {
        if ( $previousContent == $currentContent && $currentContent === '0' )
        {
            return '0';
        }

        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/Diff.php', $module);
        $MIOLO->uses('classes/Text/Diff/Renderer.php', $module);
        $MIOLO->uses('classes/Text/Diff/Renderer/inline.php', $module);

        $diff = new Text_Diff('native', array( array( $previousContent ), array( $currentContent ) ));
        $renderer = new Text_Diff_Renderer_inline();
        return $renderer->render($diff);
    }

    /**
     * Mostra histórico da tag (campo marc)
     *
     * @param stdClass $args
     *
     */
    public function showHistory($args)
    {
        $controlNumber = MIOLO::_REQUEST('controlNumber');

        $tag = GUtil::getAjaxEventArgs();
        $tag = explode('.', $tag);

        $this->busMaterialHistory->clean();
        $this->busMaterialHistory->controlNumber = $controlNumber;
        $this->busMaterialHistory->fieldId = $tag[0];
        $this->busMaterialHistory->subFieldId = $tag[1];
        $historyField = $this->busMaterialHistory->getMaterialHistory();

        $columns = array
            (
            _M("Revisão", $this->module),
            _M("Operador", $this->module),
            _M("Data", $this->module),
            _M("Tipo", $this->module),
            _M("Alterações", $this->module),
        );

        $table = new MTableRaw(null, null, $columns, 'tableMaterialHistory');
        $table->setAlternate(true);
        $table->setWidth("100%");
        $table->setCellAttribute(0, 0, "width", "10%");
        $table->setCellAttribute(0, 1, "width", "25%");

        $arrayActions = GUtil::getDbActionList();

        $tbCols = array( _M("Itens", $this->module), _M("Anterior", $this->module), );

        $allIndicators = $this->busMarcTagListing->getAllIndicators();
        //obtém o indicador 1
        $indicators1 = $allIndicators[$this->busMaterialHistory->fieldId . '-I1'];
        if ( $indicators1 )
        {
            $options = $indicators1->options;
            $indicators1 = array( );
            foreach ( $options as $key => $option )
            {
                $indicators1[$option->option] = $option->description;
            }
        }

        //obtém o indicador 2
        $indicators2 = $allIndicators[$this->busMaterialHistory->fieldId . '-I2'];
        if ( $indicators2 )
        {
            $options = $indicators2->options;
            $indicators2 = array( );
            foreach ( $options as $key => $option )
            {
                $indicators2[$option->option] = $option->description;
            }
        }

        //obtém prefixos
        $prefix = $this->busPrefixSuffix->getPrefixForTag(GUtil::getAjaxEventArgs(), true);

        //obtém sufixos
        $suffix = $this->busPrefixSuffix->getSuffixForTag(GUtil::getAjaxEventArgs(), true);

        //obtém separadores
        $separator = $this->busSeparator->getSeparatorByTag(GUtil::getAjaxEventArgs());

        foreach ( $historyField as $object )
        {
            unset($tableArray);
            $changed = false;
            $tableArray[] = array( _M("Linha", $this->module) . ":", $object->currentLine );
            $changed = true;

            //indicador 1
            if ( $object->previousIndicator1 != $object->currentIndicator1 )
            {
                if ( is_array($indicators1) )
                {
                    $object->previousIndicator1 = $indicators1[$object->previousIndicator1];
                    $object->currentIndicator1 = $indicators1[$object->currentIndicator1];
                }

                $tableArray[] = array( _M("Indicador 1", $this->module) . ":", $this->diffText($object->previousIndicator1, $object->currentIndicator1) );
                $changed = true;
            }

            //indicador 2
            if ( $object->previousIndicator2 != $object->currentIndicator2 )
            {
                if ( is_array($indicators2) )
                {
                    $object->previousIndicator2 = $indicators2[$object->previousIndicator2];
                    $object->currentIndicator2 = $indicators2[$object->currentIndicator2];
                }

                $tableArray[] = array( _M("Indicador 2", $this->module) . ":", $this->diffText($object->previousIndicator2, $object->currentIndicator2) );
                $changed = true;
            }

            //conteúdo
            if ( $object->previousContent !== $object->currentContent )
            {
                $tableArray[] = array( _M("Conteúdo", $this->module) . ":", $this->diffText($object->previousContent, $object->currentContent), '' );
                $changed = true;
            }

            //prefixo
            if ( $object->previousprefixid != $object->currentprefixid )
            {
                if ( is_array($prefix) )
                {
                    $object->previousprefixid = $prefix[$object->previousprefixid];
                    $object->currentprefixid = $prefix[$object->currentprefixid];
                }

                $tableArray[] = array( _M('Prefixo', $this->module) . ":", $this->diffText($object->previousprefixid, $object->currentprefixid), '' );
            }

            //sufixo
            if ( $object->previoussuffixid != $object->currentsuffixid )
            {
                if ( is_array($suffix) )
                {
                    $object->previoussuffixid = $suffix[$object->previoussuffixid];
                    $object->currentsuffixid = $suffix[$object->currentsuffixid];
                }

                $tableArray[] = array( _M('Sufixo', $this->module) . ":", $this->diffText($object->previoussuffixid, $object->currentsuffixid), '' );
            }

            //separador
            if ( $object->previousseparatorid != $object->currentseparatorid )
            {
                if ( is_array($separator) )
                {
                    $object->previousseparatorid = $separator[$object->previousseparatorid];
                    $object->currentseparatorid = $separator[$object->currentseparatorid];
                }

                $tableArray[] = array( _M('Separador', $this->module) . ":", $this->diffText($object->previousseparatorid, $object->currentseparatorid), '' );
            }


            $tableChanges = new MTableRaw(NULL, $tableArray, $tbCols);
            $tableChanges->setAlternate(true);
            $tableChanges->addAttribute('width', '100%');

            #limpa a tabela caso não exista nenhuma modificação
            if ( !$changed )
            {
                $tableChanges = null;
            }

            $date = new GDate($object->data);
            $table->array[] = array
                (
                $object->revisionNumber,
                $object->operator,
                $date->getDate(GDate::MASK_TIMESTAMP_USER),
                array( $arrayActions[$object->chancesType] ),
                $tableChanges,
            );
        }

        $tagTitle = $this->businessTag->getTag($tag[0], $tag[1]);
        $this->injectContent($table->generate(), true, _M('Histórico do material', $this->module) . ' - ' . $controlNumber . ' : ' . $tag[0] . '.' . $tag[1] . ' - ' . $tagTitle->description);
    }

    /**
     * Trabalha o bloco de campo na interface
     *
     * Cria cada um dos campos da catalogação
     *
     * @param $addSeparator não é mais utilizado @deprecated
     *
     * */
    function addDefaultField($object, $doubleLabel = false, $addSeparator = true, $readOnly = false, $isRepetitive = false)
    {
        $value = null;
        $n = $object->fieldName;

        list($etiqueta, $s) = explode(".", $object->tag);

        $setDefaultValue = (isset($object->defaultValue[0]->$n)) && ($isRepetitive || $this->function != 'update');
        $setLoadValue = (isset($object->loadValue[0]->$n)) && ($this->function == 'update' || $this->function == 'duplicate') && (!$isRepetitive);

        if ( $setDefaultValue )
        {
            $value = $object->defaultValue[0]->$n;
        }
        if ( $setLoadValue )
        {
            $value = $object->loadValue[0]->$n;
        }
        if ( $isRepetitive && $readOnly )
        {
            $value = '';
        }

        $label = $doubleLabel ? '<b>' . $object->tag . ' </b> - ' . $object->label : $object->label;
        $labelObject = new MLabel($label);
        $labelObject->width = '300px';
        $principalField = $this->getCatalogueField($object, $value, $readOnly);
        $field[] = new GContainer(null, array( $labelObject, $principalField ));
        
        //caso o campo tenha histórico e não seja pré-catalogação, duplicação também não mostra botão de histórico
        if ( $object->history && !$this->isPreCatalogue() && MIOLO::_REQUEST('function') != 'duplicate' )
        {
            
            $attr = array( 'alt' => _M('Histórico', $this->module), 'title' => _M('Histórico', $this->module) );
            $attrClearField = array( 'alt' => _M("Clique aqui para limpar o campo {$object->tag} - {$object->label}", $this->module), 'title' => _M("Clique aqui para limpar o campo {$object->tag} - {$object->label}", $this->module) );
            $field[] = new MImageLink("clearField_{$object->fieldName}", null, "javascript:gnutecaSearch.clearTextField('$object->fieldName');", GUtil::getImageTheme('reset.png'), $attrClearField);
            $field[] = $image = new MImageLink("history_{$object->fieldName}", null, "#", GUtil::getImageTheme('history.png'), $attr);
            $image->setGenerateOnClick(false); 
            $image->addAttribute('onclick', GUtil::getAjax('showHistory', $object->tag) . ' return false;');
        }

        //caso o campo tenha prefixo
        if ( $object->prefix )
        {
            $value = isset($object->loadPrefix[0]) && strlen($object->loadPrefix[0]) ? $object->loadPrefix[0] : null;
            $prefixField = new GSelection("prefix_{$object->fieldName}", $value, _M("Prefixo", $this->module), $object->prefix, false, '', '', false);
            $prefixField->addAttribute('style', 'width:60px');
            $field[] = $prefixField;
        }

        //caso o campo tenha sufixo
        if ( $object->suffix )
        {
            $value = isset($object->loadSuffix[0]) && strlen($object->loadSuffix[0]) ? $object->loadSuffix[0] : null;
            $suffixField = new GSelection("suffix_{$object->fieldName}", $value, _M("Sufixo", $this->module), $object->suffix, false, '', '', false);
            $suffixField->addAttribute('style', 'width:60px');
            $field[] = $suffixField;
        }

        //caso o campo tenha separador
        if ( $object->separator )
        {
            $value = isset($object->loadSeparator[0]) && strlen($object->loadSeparator[0]) ? $object->loadSeparator[0] : null;
            $separatorField = new GSelection("separator_{$object->fieldName}", $value, _M("Separador", $this->module), $object->separator);
            $separatorField->addAttribute('style', 'width:60px');
            $field[] = $separatorField;
        }

        $field[] = $this->getHelpField($object->fieldName, $object->tag, isset($object->help) && strlen($object->help));

        //adiciona botão de obter itemNumber ao lado do campo
        if ( $object->tag == MARC_EXEMPLARY_ITEM_NUMBER_TAG )
        {
            $busReport = MIOLO::getInstance()->getBusiness('gnuteca3', 'BusReport');

            //Exibe botão de próximo número se tiver o relatório de geração de numero de tombo, do usuário ou padrão
            if ( $busReport->getReport('NEXT_ITEMNUMBER')->parameters || $busReport->getReport('NEXT_ITEMNUMBER_USER')->parameters )
            {
                $function = GUtil::getAjax('getItemNumber') . '; return false;';
                $attr = array( "onclick" => "javascript:$function", 'alt' => _M('Sugerir número do exemplar', 'gnuteca3'), 'title' => _M('Sugerir número do exemplar', 'gnuteca3') );
                $field[] = $img = new MImageLink("help_{$object->fieldName}", null, "#", GUtil::getImageTheme('catalogue.png'), $attr);
                $img->setGenerateOnClick(false);
            }
        }

        //Adiciona camp de enviao de arquivo no 856.u
        if ( $object->tag == MARC_NAME_SERVER )
        {
            $field[] = new MFileField(MARC_NAME_SERVER);
        }

        return new GContainer("HcontainerField_{$object->tag}", $field);
    }

    public function addMaterialField($object, $doubleLabel = false, $readOnly = false, $isRepetitive = false)
    {
        if ( $this->fieldType == 'default' )
        {
            return $this->addDefaultField($object, $doubleLabel, null, $readOnly, $isRepetitive);
        }
        else
        {
            //Esta função não está sendo utilizada, ela provém de testes para criar uma catalogação facilitada.
            return $this->addSimpleField($object, $readOnly, $isRepetitive);
        }
    }

    /**
     * Trabalha o bloco de campo na interface
     *
     * Cria cada um dos campos da catalogação
     * Função semelhante a addDefaultField, mas é de testes para a catalogação facilitada
     * não está sendo utilizada no momento.
     * 
     * @param $addSeparator não é mais utilizado @deprecated
     *
     * */
    function addSimpleField($object, $readOnly = false, $isRepetitive = false)
    {
        $value = null;
        $n = $object->fieldName;

        list($etiqueta, $s) = explode(".", $object->tag);

        $setDefaultValue = (isset($object->defaultValue[0]->$n)) && ($isRepetitive || $this->function != 'update');
        $setLoadValue = (isset($object->loadValue[0]->$n)) && ($this->function == 'update' || $this->function == 'duplicate') && (!$isRepetitive);

        if ( $setDefaultValue )
        {
            $value = $object->defaultValue[0]->$n;
        }
        if ( $setLoadValue )
        {
            $value = $object->loadValue[0]->$n;
        }
        if ( $isRepetitive && $readOnly )
        {
            $value = '';
        }

        $label = new MLabel($object->label);
        $label->width = '300px';
        $field[] = $label;
        $principalField = $this->getCatalogueField($object, $value, $readOnly);
        $principalField->addAttribute('alt', strip_tags($object->tag . ' - ' . $object->label));
        $principalField->addAttribute('title', strip_tags($object->tag . ' - ' . $object->label));
        //$field[]            = new GContainer( null, array($labelObject, $principalField) );
        $field[] = $principalField;

        //caso o campo tenha histórico e não seja pré-catalogação, duplicação também não mostra botão de histórico
        if ( $object->history && !$this->isPreCatalogue() && MIOLO::_REQUEST('function') != 'duplicate' )
        {
            $attr = array( 'alt' => _M('Histórico', $this->module), 'title' => _M('Histórico', $this->module) );
            $field[] = $image = new MImageLink("history_{$object->fieldName}", null, "#", GUtil::getImageTheme('history.png'), $attr);
            $image->setGenerateOnClick(false); //retira o onClick padrão do MImageLink
            $image->addAttribute('onclick', GUtil::getAjax('showHistory', $object->tag) . ' return false;');
        }

        //caso o campo tenha prefixo
        if ( $object->prefix )
        {
            $value = isset($object->loadPrefix[0]) && strlen($object->loadPrefix[0]) ? $object->loadPrefix[0] : null;
            $prefixField = new GSelection("prefix_{$object->fieldName}", $value, '', $object->prefix, false, '', '', false);
            $prefixField->addAttribute('style', 'width:60px');
            $prefixField->addAttribute('alt', _M("Prefixo", $this->module));
            $prefixField->addAttribute('title', _M("Prefixo", $this->module));

            $field[] = $prefixField;
        }

        //caso o campo tenha sufixo
        if ( $object->suffix )
        {
            $value = isset($object->loadSuffix[0]) && strlen($object->loadSuffix[0]) ? $object->loadSuffix[0] : null;
            $suffixField = new GSelection("suffix_{$object->fieldName}", $value, '', $object->suffix, false, '', '', false);
            $suffixField->addAttribute('style', 'width:60px');
            $suffixField->addAttribute('alt', _M("Sufixo", $this->module));
            $suffixField->addAttribute('title', _M("Sufixo", $this->module));

            $field[] = $suffixField;
        }

        //caso o campo tenha separador
        if ( $object->separator )
        {
            $value = isset($object->loadSeparator[0]) && strlen($object->loadSeparator[0]) ? $object->loadSeparator[0] : null;
            $separatorField = new GSelection("separator_{$object->fieldName}", $value, '', $object->separator);
            $separatorField->addAttribute('style', 'width:60px');
            $separatorField->addAttribute('alt', _M("Separador", $this->module));
            $separatorField->addAttribute('title', _M("Separador", $this->module));

            $field[] = $separatorField;
        }

        $field[] = $this->getHelpField($object->fieldName, $object->tag, isset($object->help) && strlen($object->help));

        //adiciona botão de obter itemNumber ao lado do campo
        if ( $object->tag == MARC_EXEMPLARY_ITEM_NUMBER_TAG )
        {
            $busReport = MIOLO::getInstance()->getBusiness('gnuteca3', 'BusReport');

            if ( $busReport->getReport('NEXT_ITEMNUMBER')->parameters || $busReport->getReport('NEXT_ITEMNUMBER_USER')->parameters )
            {
                $function = GUtil::getAjax('getItemNumber') . '; return false;';
                $attr = array( "onclick" => "javascript:$function", 'alt' => _M('Sugerir número do exemplar', 'gnuteca3'), 'title' => _M('Sugerir número do exemplar', 'gnuteca3') );
                $field[] = $img = new MImageLink("help_{$object->fieldName}", null, "#", GUtil::getImageTheme('catalogue.png'), $attr);
                $img->setGenerateOnClick(false);
            }
        }

        //Adiciona camp de enviao de arquivo no 856.u
        if ( $object->tag == MARC_NAME_SERVER )
        {
            $field[] = new MFileField(MARC_NAME_SERVER);
        }

        return new MHContainer("HcontainerField_{$object->tag}", $field);
    }

    /**
     * Auto preenchimento do número de exemplar
     */
    public function getItemNumber()
    {
        $data->libraryUnitId = MIOLO::_REQUEST('spreeadsheetField_' . str_replace('.', '_', MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG));

        if ( !$data->libraryUnitId )
        {
            throw new Exception(_M('É necessário selecionar uma unidade. Campo da unidade = @1', 'gnuteca3', MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG));
        }

        $busReport = MIOLO::getInstance()->getBusiness('gnuteca3', 'BusReport');
        
        if( $busReport->getReport('NEXT_ITEMNUMBER_USER')->parameters )
        {
            $result = $busReport->executeReport('NEXT_ITEMNUMBER_USER', $data);
        }
        else
        {
            $result = $busReport->executeReport('NEXT_ITEMNUMBER', $data);
        }

        $itemNumber = $result[0][0];

        if ( !$itemNumber )
        {
            throw new Exception(_M('Impossível encontrar número de controle!', 'gnuteca3'));
        }
        else
        {
            $fieldId = 'spreeadsheetField_' . str_replace('.', '_', MARC_EXEMPLARY_ITEM_NUMBER_TAG);
            GForm::jsSetValue($fieldId, $itemNumber);
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Obtem um campo de ajuda;
     *
     * @param string $name nome do campo de ajuda (será concatenado mais coisas)
     * @param string $tag a tag 100.a por exemplo
     * @param boolean $hasHelp
     * @return MImageLink pode retornar nulo.
     */
    public function getHelpField($name, $tag, $hasHelp)
    {
        //descarta ajuda para campos 000
        if ( stripos($name, '000') === 0 )
        {
            return null;
        }

        //descarta ajuda para campos 008 e indicadores
        if ( stripos($tag, '008') === 0 || stripos($tag, 'IND') === 0 )
        {
            return null;
        }

        if ( $this->canEditHelp )
        {
            $imageHelp = $hasHelp ? GUtil::getImageTheme('help-16x16.png') : GUtil::getImageTheme('helpAdd-16x16.png');
        }
        //caso não tenha permissão de edição e o campo tenha ajuda
        else
        {
            $imageHelp = $hasHelp ? GUtil::getImageTheme('help-16x16.png') : null;
        }

        if ( $imageHelp )
        {
            $imageHelp = new MImageLink('help_' . $name, null, 'javascript:' . GUtil::getAjax('showHelp', array( 'tag' => $tag )), $imageHelp);
            $imageHelp->addAttribute('alt', _M('Ajuda', $this->module));
            $imageHelp->addAttribute('title', _M('Ajuda', $this->module));
            $imageHelp->addStyle('margin-left', '5px'); //meio hardocode mais resolve bem o problema
            $imageHelp->image->id = 'help_' . $name . 'Img';
        }

        return $imageHelp;
    }

    /**
     * Retorna o campo trabalhado para adicionar no form
     *
     * @param object field $object
     * @param string $value
     * @param boolean $readOnly
     * @return object field
     *
     *
     */
    private function getCatalogueField($object, $value = '', $readOnly = false)
    {
        switch ( $object->type )
        {
            case FIELD_TYPE_COMBO :

                $opts = $object->fieldContent->options ? $object->fieldContent->options : array( );
                $field[] = new MSelection($object->fieldName . ($readOnly ? "_ro" : ""), $value, '', $opts, true);

                //FIXME em função de um bug do miolo (que não gera o campo escondido)
                if ( $readOnly )
                {
                    $field[] = $hidden = new MTextField($object->fieldName, $value);
                    $hidden->addStyle('display', 'none');
                }

                if ( $readOnly )
                {
                    $field[0]->setReadOnly(true);
                }

                return new GContainer(null, $field);

            case FIELD_TYPE_DATE :

                $field = new MCalendarField($object->fieldName, $value, '');

                if ( $readOnly )
                {
                    $field->addAttribute("readonly", "readonly");
                }

                return $field;

            case FIELD_TYPE_MULTILINE :

                $field = new MMultiLineField($object->fieldName, $value, '', null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

                if ( $readOnly )
                {
                    $field->addAttribute("readonly", "readonly");
                }

                return $field;

            case FIELD_TYPE_DICTIONARY :

                $lookUp = new GDictionaryField($object->fieldName, $value, '');
                $lookUp->setContext($this->module, $this->module, "Dictionary", 'filler', $object->fieldName, "{$object->fieldName}_Filter_DictionaryNumber,{$object->fieldName}_Filter_DictionaryContent", false);
                $btn = new MButtonAjax('btn_clear', 'Limpa', $updateElement, $parameter, $method);
                //quando dicionário for readonly ainda é possível utilizar o lookup
                if ( $object->hasDictionary->readOnly == DB_TRUE )
                {
                    $lookUp->addAttribute("readonly", "readonly");
                }

                //quando o campo for readonly
                if ( $readOnly )
                {
                    $lookUp->setReadOnly(true);
                }

                //campos escondidos utilizados pelo lookup
                $hiddenField1 = new MHiddenField("{$object->fieldName}_Filter_DictionaryNumber", $object->hasDictionary->dictionaryId);
                $hiddenField2 = new MHiddenField("{$object->fieldName}_Filter_DictionaryContent", $value);

                if ( $object->rulesToComplete )
                {
                    $originalField = str_replace(".", "_", "spreeadsheetField_{$object->rulesToComplete->originField}");
                    $affectRecordsCompleted = $object->rulesToComplete->affectRecordsCompleted == DB_TRUE ? 'true' : 'false'; //parametro par javascript

                    $lookUp->addAttribute("onkeyup", "return gnuteca.autoCompleteMarcFields(event,'{$object->rulesToComplete->originField}','{$object->rulesToComplete->fateField}',{$affectRecordsCompleted});");
                }
                
                return new GContainer("lookUpContainer_{$object->fieldName}", array( $lookUp, $hiddenField1, $hiddenField2 ));                                

            case FIELD_TYPE_LOOKUP :
                $related = ereg("DescON", $object->lookUp->Desc) ? "{$object->fieldName},lookUpDesc_{$object->fieldName}_ro" : "{$object->fieldName}";
                $size = ereg("DescON", $object->lookUp->Desc) ? FIELD_LOOKUPFIELD_SIZE : FIELD_DESCRIPTION_SIZE;

                $lookUp = new GLookupTextField($object->fieldName, $value, '', $size);
                $lookUp->setContext($this->module, $this->module, $object->lookUp->Name, 'filler', $related, '', false);
                $lookUp->baseModule = $this->module;

                if ( $readOnly )
                {
                    $lookUp->setReadOnly(true);
                }

                $lookUpDesc = null;

                if ( ereg("DescON", $object->lookUp->Desc) )
                {
                    $lookUpDesc = new MTextField("lookUpDesc_{$object->fieldName}_ro", null, null, FIELD_DESCRIPTION_LOOKUP_SIZE, null, null, true);
                    $lookUp->setAutoComplete(true);
                }

                return new GContainer("lookUpContainer_{$object->fieldName}", array( $lookUp, $lookUpDesc ));

            case MARC_PERIODIC_INFORMATIONS:

                if($this->business->getLevel() == "#")
                {
                    if ( ereg(" - ", $value) )
                    {
                        $fValues = explode(' - ', $value);
                        $fValues1 = explode(',', $fValues[1]);
                        $fValues = explode(',', $fValues[0]);
                        $fValues = array($fValues[0], $fValues[1], '', $fValues1[0], $fValues1[1], $fValues1[2]);
                    }
                    else
                    {
                        $fValues = explode(')-', $value);
                        $fValues1 = explode(' ', trim($fValues[0]));
                        $fValues1[1] = str_replace(',' , '' , $fValues1[1]);
                        if(!ereg('Vol.', $fValues1[0]) && !ereg('no.' , $fValues1[0]) && $fValues1[1])
                        {
                            $fValues1[5] = $fValues1[1];
                        }
                        
                        if(!ereg('Vol.', $fValues1[0]))
                        {
                            $fValues1[5] = $fValues1[3];
                            $fValues1[4] = $fValues1[2];
                            $fValues1[2] = $fValues1[0];
                            $fValues1[3] = $fValues1[1];
                            $fValues1[0] = 'Vol. ';
                            $fValues1[1] = '';
                        }
                        
                        if (!ereg('no.' , $fValues1[2]))
                        {
                            $fValues1[5] = $fValues1[3];
                            $fValues1[4] = $fValues1[2];
                            $fValues1[2] = 'no. ';
                            $fValues1[3] = '';
                        }
                        
                        $fValues2 = $fValues[1];
                        $fValues2 = explode(', ', $fValues2);
                        if(!$fValues2[1] && !ereg('v.', $fValues2[0]))
                        {
                            $fValues2a = explode(' ' , $fValues2[0]);
                            $fValues2[0] = '';
                        }
                        else
                        {
                            $fValues2a = explode(' ' , $fValues2[1]);
                        }
                        
                        $fValues1[1] = str_replace('(', ' ', $fValues1[1]);
                        
                        if(trim($fValues1[5]))
                        {
                            $fValues1[4] = $fValues1[4] . ' ';
                        }

                        $fValues = array($fValues1[0] . ' ' . $fValues1[1], $fValues1[2] . ' ' . $fValues1[3], $fValues1[4] . $fValues1[5] . ')-', $fValues2[0], $fValues2a[0] . ' ' . $fValues2a[1], trim($fValues2a[2]));
                    }

                    $n = $v = $pt = $supl = '';

                    $lv = $this->business->getLevel() == "#" ? "Vol." : "v.";
                    $ln = $this->business->getLevel() == "#" ? "no." : "n.";

                    if ( ereg("^v\.", trim($fValues[0])) || ereg("^Vol\.", trim($fValues[0])))
                    {
                    }
                    else
                    {
                        $fValues[1] = $fValues[0];
                        $fValues[0] = '';
                    }
                    for ($x = 0; $x <= 5 ; $x++)
                    {
                        if (!$fValues[$x])
                        {
                            if ($x == 0)
                            {
                                $fValues[$x] = 'Vol.';
                            }
                            if ($x == 1)
                            {
                                $fValues[$x] = 'no.';
                            }
                            if ($x == 2)
                            {
                                $fValues[$x] = ';';
                            }
                            if ($x == 3)
                            {
                                $fValues[$x] = 'v.';
                            }
                            if ($x == 4)
                            {
                                $fValues[$x] = 'no.';
                            }
                            if ($x == 5)
                            {
                                $fValues[$x] = ';';
                            }
                        }
                    }

                    foreach ( $fValues as $vx )
                    {

                        if ( ereg("^v\.", trim($vx)) || ereg("^Vol\.", trim($vx)) )
                        {
                            $v[] = trim(str_replace(array( "v.", "Vol." ), "", $vx));
                        }
                        elseif ( ereg("^n\.", trim($vx)) || ereg("^no\.", trim($vx)) )
                        {
                            $n[] = trim(str_replace(array( "n.", "no." ), "", $vx));
                        }
                        else
                        {
                            $pt[] = trim(str_replace(";", "" , $vx));
                        }
                    }
                }
                else
                {
                    $fValues = explode(',', $value);
                    
                    $n = $v = $pt = $supl = '';

                    $lv = $this->business->getLevel() == "#" ? "Vol." : "v.";
                    $ln = $this->business->getLevel() == "#" ? "no." : "n.";
                    foreach ( $fValues as $vx )
                    {

                        if ( ereg("^v\.", trim($vx)) || ereg("^Vol\.", trim($vx)) )
                        {
                            $v[] = trim(str_replace(array( "v.", "Vol." ), "", $vx));
                        }
                        elseif ( ereg("^n\.", trim($vx)) || ereg("^no\.", trim($vx)) )
                        {
                            $n[] = trim(str_replace(array( "n.", "no." ), "", $vx));
                        }
                        else
                        {
                            $pt[] = trim($vx);
                        }
                    }

                }
                
                $fieldV = new MTextField("especialField_" . MARC_PERIODIC_INFORMATIONS . "_{$object->fieldName}[]", $v[0], '', 10);
                $fieldN = new MTextField("especialField_" . MARC_PERIODIC_INFORMATIONS . "_{$object->fieldName}[]", $n[0], '', 10);
                $fieldA = new MTextField("especialField_" . MARC_PERIODIC_INFORMATIONS . "_{$object->fieldName}[]", $pt[0], '', 15); 
                // Os campos abaixo são usados somente na coleção
                $fieldV2 = new MTextField("especialField_" . MARC_PERIODIC_INFORMATIONS . "_{$object->fieldName}[]", $v[1], '', 10);
                $fieldN2 = new MTextField("especialField_" . MARC_PERIODIC_INFORMATIONS . "_{$object->fieldName}[]", $n[1], '', 10);
                $fieldA2 = new MTextField("especialField_" . MARC_PERIODIC_INFORMATIONS . "_{$object->fieldName}[]", $pt[1], '', 15);

                //quando o campo for readonly
                if ( $readOnly )
                {
                    $fieldV->setReadOnly(true);
                    $fieldN->setReadOnly(true);
                    $fieldA->setReadOnly(true);
                    $fieldV2->setReadOnly(true);
                    $fieldN2->setReadOnly(true);
                    $fieldA2->setReadOnly(true);
                }
                
                        
                        
                $field1 = new GContainer(null, array( new MDiv(null, $lv), $fieldV ));
                $field2 = new GContainer(null, array( new MDiv(null, $ln),$fieldN ));
                $field3 = new GContainer(null, array( new MDiv(null, _M('ano', $this->module)), $fieldA ));
                
                if($this->business->getLevel() == "#")
                {
                    $field4 = new MDiv('', "-");
                    $field5 = new GContainer(null, array( new MDiv(null, 'v.'), $fieldV2 ));
                    $field6 = new GContainer(null, array( new MDiv(null, 'no.'), $fieldN2 ));
                    $field7 = new GContainer(null, array( new MDiv(null, _M('ano', $this->module)), $fieldA2 ));
                    
                    $field9 = new GContainer("lookUpContainer_{$object->fieldName}", array( $field1, $field2, $field3, $field4 )); 
                    $field10 = new GContainer('', array($field5, $field6, $field7));
                    
                    return new MVContainer("lookUpContainer_{$object->fieldName}", array( $field9, $field10));
                }
                else
                {
                    return new GContainer("lookUpContainer_{$object->fieldName}", array( $field1, $field2, $field3 ));
                }
                
            case FIELD_TYPE_TEXT :
            default
                :
                $field = new MTextField($object->fieldName, $value, '', FIELD_DESCRIPTION_SIZE, '', null, $readOnly);

                if ( $object->rulesToComplete )
                {
                    $originalField = str_replace(".", "_", "spreeadsheetField_{$object->rulesToComplete->originField}");
                    $affectRecordsCompleted = $object->rulesToComplete->affectRecordsCompleted == DB_TRUE ? 'true' : 'false'; //parametro par javascript

                    $field->addAttribute("onkeyup", "return gnuteca.autoCompleteMarcFields(event,'{$object->rulesToComplete->originField}','{$object->rulesToComplete->fateField}',{$affectRecordsCompleted});");
                }

                return $field;
        }
    }

    /**
     *
     */
    function addRepetitiveField($object, $readOnly = false)
    {
        $tableFields[] = $this->addMaterialField($object, true, $readOnly, true);
        
        $this->checkFieldFunction($object, $tableFields);

        $columns[] = new MGridColumn("{$object->tag}<br>{$object->label}", 'left', true, null, true, $object->fieldName);

        if ( $object->prefix )
        {
            $columns[] = new MGridColumn(_M("Prefixo", $this->module), 'left', true, null, true, "prefix_{$object->fieldName}");
        }
        if ( $object->suffix )
        {
            $columns[] = new MGridColumn(_M("Sufixo", $this->module), 'left', true, null, true, "suffix_{$object->fieldName}");
        }
        if ( $object->separator )
        {
            $columns[] = new MGridColumn(_M("Separador", $this->module), 'left', true, null, true, "separator_{$object->fieldName}");
        }

        return array( 'fields' => $tableFields, 'columns' => $columns );
    }

    public function checkFieldFunction($object, &$tableFields)
    {
        switch ( $object->tag )
        {
            case MARC_EXEMPLARY_ORIGINAL_LIBRARY_UNIT_ID_TAG :
                list($f, $s) = explode(".", MARC_EXEMPLARY_ORIGINAL_LIBRARY_UNIT_ID_TAG);
                $function = "\$data->{$object->fieldName} = \$data->spreeadsheetField_{$f}_" . MARC_EXEMPLARY_LIBRARY_UNIT_ID_SUBFIELD . ";";
                $function = new MTextField("Gnuteca3RepetitiveAddFunction_ro[]", $function, null, 1);
                $function->addAttribute("style", "display:none");
                $tableFields[] = $function;
                break;
        }
    }

    function generateRepetitiveFields($etiquetaId, $rFields)
    {
        $f = null;
        $validators = null;

        foreach ( $rFields as $valores )
        {
            foreach ( $valores as $index => $objects )
            {
                foreach ( $objects as $obj )
                {
                    eval("\$f->{$index}[] = \$obj;");
                }
            }
        }

        $field[] = new MVContainer("containerV$etiquetaId", $f->fields);

        $this->repetitiveFields[$etiquetaId]->setFields($field);
        $this->repetitiveFields[$etiquetaId]->setColumns($f->columns);
        
        $div = new MDiv("repeatId$etiquetaId", $this->repetitiveFields[$etiquetaId], null, array( "style" => "width:95%;" ));
        
        return $div;
    }

    /**
     * Definição de campo marca para forcenecedor. MARC_PERIODIC_INFORMATIONS
     * Normalmente 362.a.
     * TODO otimizar
     *
     * @param object $args o objeto do post
     * @return object
     *
     */
    public function prepareEspecialFields($args)
    {
        foreach ( $args as $fieldName => $content )
        {
            if ( ereg("^especialField_", $fieldName) )
            {
                $cont = '';

                foreach ( $content as $c )
                {
                    if ( strlen(trim($c)) )
                    {
                        $cont.= "$c, ";
                    }
                }

                $fName = preg_replace('/especialField_[0-9]{3}_[0-9a-zA-z]{1}_/', "", $fieldName);
                eval("\$args->{$fName} = \$cont;");
            }
        }
        return $args;
    }

    /**
     * Funçao que executa o salvamento da capa do material
     *
     * @param integer $controlNumber número de controle
     * @param string $folder pasta deve ser cover ou coverpre
     */
    public function saveCover($controlNumber = null, $folder = 'cover')
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
        $busCatalogue = $this->MIOLO->getBusiness('gnuteca3', 'BusCataloge');

        if ( !$controlNumber )
        {
            $controlNumber = $busCatalogue->getControlNumberFromSession();
        }

        /**
         *  $controlNumberPost = Número de controle que será o da gtcmaterial quando o botão de salvar for pressionado.
         *  Se for pressionado o botão para salvar na pré-catalogação, $controlNumberPost = $controlNumber
         */
        $controlNumberPost = MIOLO::_REQUEST('controlNumber', 'POST');

        //passando da pré-catalogação para catalogação definitiva
        if ( ( $folder == 'cover' && $this->isPreCatalogue() ) || ( $controlNumberPost && ( $controlNumberPost != $busCatalogue->getControlNumberFromSession() ) ) )
        {
            $busFile->folder = 'coverpre';
            $busFile->fileName = $controlNumberPost . '.'; //o ponto garante que só aparece a imagem certa
            $coverPre = $busFile->searchFile(true); //Obtém informações da imagem
            $coverPre = $coverPre[0]; //Sempre usa o primeiro arquivo encontrado, isso evita problemas de pegar uma imagem errada.
            
            //Obtém extensão do arquivo da capa  após último "." .
            $ext = explode('.', $coverPre->basename); 
            $ext = $ext[count($ext) - 1]; 
            
            //Realiza a movimentação para o novo arquivo
            $ori = $busFile->getAbsoluteFilePath('coverpre', $controlNumberPost,$ext);
            $dst = $busFile->getAbsoluteFilePath('cover', $busCatalogue->getControlNumberFromSession(),$ext);

            if ( file_exists($ori) )
            {
                //move o arquivo
                rename($ori, $dst);
                //ajeita os dados para remontar a tabela no fim
                $folder = 'cover';
                $controlNumber = $busCatalogue->getControlNumberFromSession();
            }
        }

        $coverData = GFileUploader::getData('coverMaterial');

        if ( $coverData )
        {
            foreach ( $coverData as $i => $file )
            {
                // Remove imagem do servidor.
                if ( $file->removeData )
                {
                    $filePath = $busFile->getAbsoluteFilePath($folder, $controlNumber, $file->extension);
                    $busFile->deleteFile($filePath);
                }
                else
                {
                    $lastChange = new GDate($file->lastChange);

                    // Insere no servidor somente se o arquivo for adicionado na repetitive.
                    if ( $file->insertData )
                    {
                        // Remove a capa existente
                        foreach ( array( 'png', 'jpg', 'jpeg', 'gif' ) as $extension )
                        {            
                            $filePath = $busFile->getAbsoluteFilePath($folder, $controlNumber, $extension);
                            $busFile->deleteFile($filePath);
                        }
                        
                        //obtem extensão real do arquivo
                        $ext = explode('.', $file->basename);
                        $ext = $ext[count($ext) - 1];
                        //extensão padrão
                        $ext = $ext ? $ext : 'png';
                        $file->basename = $controlNumber . '.' . $ext;
                        $busFile->folder = $folder;
                        $busFile->files = array( $file );
                        $busFile->insertFile(); //insere o arquivo
                    }
                }
            }

            GFileUploader::clearData('coverMaterial');
        }
        else
        {
            foreach ( array( 'png', 'jpg', 'jpeg', 'gif' ) as $extension )
            {            
                $filePath = $busFile->getAbsoluteFilePath($folder, $controlNumber, $extension);
                $busFile->deleteFile($filePath);
            }
        }

        //le novamente do disco e define no componente
        $busFile->folder = $folder;
        $busFile->fileName = $controlNumber . '.'; //o ponto garante que só aparece a imagem certa
        GFileUploader::setData($busFile->searchFile(true), 'coverMaterial');
        GFileUploader::generateTable('coverMaterial'); //atualiza a tabela
    }

    public function tbBtnSave_click($args)
    {
        $this->saveSpreadsheet($args);
    }

    /**
     * Modifica comportamento padrão do campo repetitivo, nesse caso, removendo sempre os dados da tabela.
     * Não é usado o ->removeData.
     *
     * @param stdClass $data dados do post
     */
    function removeFromTable($data)
    {
        $MIOLO = MIOLO::getInstance();
        $object = $data->GRepetitiveField;

        if ( $object == 'generalUploader' )
        {
            parent::removeFromTable($data);
        }
        else
        {
            unset($_SESSION['GRepetitiveField'][$object][$data->arrayItemTemp]);
            $MIOLO->ajax->setResponse(GRepetitiveField::generate(false, $object), 'div' . $object);
        }
    }

    function forceAddToTable($args, $object = null, $errors = null)
    {
        $repetitive = explode('.', MARC_NAME_SERVER);
        $repetitive = 'gtcRepeatField_' . $repetitive[0];

        //muda o conteúdo do campo, preenchendo o nome automáticamente
        if ( $args->GRepetitiveField == $repetitive && $args->uploadInfo )
        {
            $fieldId = 'spreeadsheetField_' . str_replace('.', '_', MARC_NAME_SERVER);
            $file = explode(';', $args->uploadInfo);
            $args->$fieldId = $file[0];
        }
        
        autoForceAddAction($args, $object, $errors);
    }

    /**
     * Salva a catalogação
     *
     * @param stdClass $args
     * @return void
     */
    function saveSpreadsheet($args)
    {
        //garante que tenha que ser permissão de update ou inser
        $function = MIOLO::_REQUEST('function') == 'update' ? 'update' : 'insert';
        
        if ( GPerms::checkAccess('gtcMaterial', $function, false) )
        {
            $argsx = $this->getData(true);

            //prepara dados para campos com MARC_PERIODIC_FIELD
            $argsx = $this->prepareEspecialFields($argsx);
            $argsx->controlNumber = MIOLO::_REQUEST('controlNumber', 'POST');
            
            //valida a planilha
            //FIXME essa validação deveria ser feita no formulário da forma padrão (criação de MValidators)
            $valid = $this->business->validatorSpreadsheetFields($argsx);

            if ( is_array($argsx->gtcRepeatField_949) )
            {
                $ok = $this->business->verifyRepeatItemNumber($argsx->gtcRepeatField_949, $argsx->controlNumber);
            }
            
            // Valida workflow de solicitação de compras da Univates.
            if ( WF_PURCHASE_REQUEST_CLASS == 'wfPurchaseRequestUnivates' && is_array($argsx->gtcRepeatField_949) )
            {
                $busPurchaseRequest = $this->MIOLO->getBusiness('gnuteca3', 'BusPurchaseRequest');
                foreach ( $argsx->gtcRepeatField_949 as $exemplary )
                {
                    if ( strlen($exemplary->spreeadsheetField_949_s) > 0 )
                    {
                        $solicitacaoDeCompra = $busPurchaseRequest->getPurchaseRequestByExternalId($exemplary->spreeadsheetField_949_s);
                        if ( strlen($solicitacaoDeCompra) > 0 )
                        {
                            // Atualiza o status para "Catalogada"
                            $futureStatus = GWorkflow::getFutureStatus('PURCHASE_REQUEST', 'gtcPurchaseRequest', $solicitacaoDeCompra);
                            
                            if ( !$futureStatus )
                            {
                                $this->business->addError(_M('O número da solicitação de compra @1 não possui estado futuro!', $this->module, $exemplary->spreeadsheetField_949_s));
                            }
                        }
                    }
                }
            }

            //obtem os erros de validação do busness
            $errors = $this->business->getErrors();

            //Verifica se um array foi passado e se tem algum valor definido dentro dele
            if ( is_array($errors) && !empty($errors) )
            {
                //chama função de validação padrão, passando erros do business
                $this->validate(NULL, $errors);
                return;
            }

            $makeUpload = $this->saveExtraFiles(); //upload do 856.u
            //caso tenha feito o upload atualiza os argumentos do post
            if ( $makeUpload )
            {
                $repetitiveMarcNameServer = explode('.', MARC_NAME_SERVER);
                $repetitiveMarcNameServer = 'gtcRepeatField_' . $repetitiveMarcNameServer[0];
                $argsx->$repetitiveMarcNameServer = GRepetitiveField::getData($repetitiveMarcNameServer);
            }

            $r = $this->business->setFormValues($argsx);
            
            // Clona objeto caso tenha que fazer rollback;
            $cloneBusiness = clone $this->business;
            
            try
            {
                //manda os dados pro banco
                $this->business->beginTransaction();

                $newControlNumber = $this->business->save();

                $updateWorkflow = false;

                if ( WF_PURCHASE_REQUEST_CLASS == 'wfPurchaseRequestUnivates' && is_array($argsx->gtcRepeatField_949) )
                {
                    $busPurchaseRequest = $this->MIOLO->getBusiness('gnuteca3', 'BusPurchaseRequest');
                    foreach ( $argsx->gtcRepeatField_949 as $exemplary )
                    {
                        if ( strlen($exemplary->spreeadsheetField_949_s) > 0 )
                        {
                            $solicitacaoDeCompra = $busPurchaseRequest->getPurchaseRequestByExternalId($exemplary->spreeadsheetField_949_s);
                            if ( strlen($solicitacaoDeCompra) > 0 )
                            {
                                // Atualiza o status para "Catalogada"
                                $updateWorkflow = GWorkflow::changeStatus('PURCHASE_REQUEST', 'gtcPurchaseRequest', $solicitacaoDeCompra, '100008', '');
                            }
                        }
                    }
                }

                $this->business->commitTransaction();
            }
            catch( Exception $e )
            {
                // Faz rollback de subfields alterados no save.
                $this->business->spreadSheet = $cloneBusiness->spreadSheet;
                
                // Salva conteúdo na sessão.
                $this->business->saveContent();
             
                // Desfaz transação na base de dados.
                $this->business->rollbackTransaction();
                
                // Emite erro em tela.
                $this->error($e->getMessage());
                return;
            }

            // Atualiza conteúdo da tabela de pesquisa.
            if ( !$this->business->preCatalogue )
            {
                $arguments = new stdClass();
                $arguments->controlNumber = $this->business->controlNumber;
                $arguments->controlNumberFather = $this->business->controlNumberFather;
                $this->MIOLO->getClass('gnuteca3', 'backgroundTasks/GBackgroundTask');
                GBackgroundTask::executeBackgroundTask('updateSearchTable', $arguments);
            }

            if ( $newControlNumber )
            {
                $lastOperator = $this->busMaterialHistory->lastOperator($newControlNumber);

                if ( !$lastOperator )
                {
                    $lastOperator = _M("Sem revisões registradas", $this->module);
                }

                $this->jsSetValue('lastOperator', $lastOperator);
            }

            $this->jsSetValue('controlNumber', $newControlNumber);
            $this->jsSetValue('lastChange', GDate::now());
            $this->jsSetValue($this->business->getWorkNumberFieldName(), $this->business->getWorkNumberValue()); //só vai ser chamado caso tenha o workNumberField
            $this->jsSetValue('wasSaved', DB_TRUE); //joga o valor 't' no campo escondido

            $this->setResponse($this->getToolBar(null, true, true), 'toolBarContainer');

            $this->saveCover();
            $this->setModified(false);
            
            $this->injectContent($this->getSaveContent()->generate(), false, _M('Material salvo', $this->module));
        }
        else
        {
            $this->error(_M('Você não possui permissão para salvar na catalogação.', $this->module));
        }
    }

    /**
     * Faz o upload de arqvuios extras, no caso o MARC_NAME_SERVER (856.u) por padrão.
     *
     * @return boolean se realmente enviou arquivos ou não
     */
    public function saveExtraFiles()
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
        $repetitiveMarcNameServer = explode('.', MARC_NAME_SERVER);
        $repetitiveMarcNameServer = 'gtcRepeatField_' . $repetitiveMarcNameServer[0];
        $uploadFiles = GRepetitiveField::getData($repetitiveMarcNameServer);

        if ( is_array($uploadFiles) )
        {
            $busFile->folder = 'material';
            $busFile->files = $uploadFiles;
            $busFile->insertFile(); //insere o arquivo

            foreach ( $uploadFiles as $line => $info )
            {
                if ( $info->tmp_name )
                {
                    $fieldId = 'spreeadsheetField_' . str_replace('.', '_', MARC_NAME_SERVER);
                    $value = BusinessGnuteca3BusFile::getValidFilename($info->$fieldId);
                    $uploadFiles[$line]->$fieldId = $this->MIOLO->getConf('home.url') . '/file.php?folder=material&file=' . $value;
                    $uploadFiles[$line]->tmp_name = null;
                }
            }

            GRepetitiveField::setData($uploadFiles, $repetitiveMarcNameServer);
            //atualiza componente
            $this->MIOLO->ajax->setResponse(GRepetitiveField::getTable($repetitiveMarcNameServer), 'div' . $repetitiveMarcNameServer);

            return true;
        }

        return false;
    }

    /**
     * Monta div com conteúdo mostra após o salvamento (dados do material)
     *
     * @return MDiv com conteúdo mostra após o salvamento (dados do material)
     */
    public function getSaveContent($preCatalogue = false)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $function = MIOLO::_REQUEST('function');

        $busCatalogue = $MIOLO->getBusiness($module, 'BusCataloge');
        $busMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $busKardexControl = $MIOLO->getBusiness($module, 'BusKardexControl');

        list($cat, $lev) = explode("-", SPREADSHEET_CATEGORY_FASCICLE);
        $controlNumberFather = $busCatalogue->getTagValue(MARC_ANALITIC_ENTRACE_TAG);

        if ( $busCatalogue->getCategory() == $cat && $busCatalogue->getLevel() == $lev )
        {
            $unidades = $busCatalogue->getTagValue(MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG, true);

            if ( $unidades )
            {
                $kardex = $busKardexControl->getKardexOfMaterial($controlNumberFather[0], $unidades);
            }

            if ( $kardex )
            {
                $columsn[] = _M("Código do assinante", $module);
                $columsn[] = _M("Unidade de biblioteca", $module);
                $columsn[] = _M("Tipo de aquisição", $module);
                $columsn[] = _M("Publicação", $module);

                foreach ( $kardex as $index => $kardexContent )
                {
                    $content[$index][0] = $kardexContent->codigoDeAssinante;
                    $content[$index][1] = $busMaterial->relationOfFieldsWithTable(MARC_KARDEX_LIBRARY_UNIT_ID_TAG, $kardexContent->libraryUnitId, true);

                    $aquisitionType = $busMaterial->getContentTag($controlNumberFather[0], MARC_KARDEX_ACQUISITION_TYPE_TAG, $kardexContent->line);
                    $publication = $busMaterial->getContentTag($controlNumberFather[0], MARC_KARDEX_PUBLICATION_TAG, $kardexContent->line);

                    $content[$index][2] = $busMaterial->relationOfFieldsWithTable(MARC_KARDEX_ACQUISITION_TYPE_TAG, $aquisitionType, true);
                    $content[$index][3] = $busMaterial->relationOfFieldsWithTable(MARC_KARDEX_PUBLICATION_TAG, $publication, true);
                }

                $table = new MTableRaw("Kardex", $content, $columsn);
                $table->addAttribute("style", "width:100%");

                $fields[] = new MDiv("publicacao", $table);
            }
        }

        //info
        $table = new MTableRaw(_M("Informações da obra", $module), null, array( 'Campos', 'Conteúdos' ), 'tableWorkInfo');
        $table->setAlternate(true);
        $table->addAttribute("Style", "width:100%");
        $table->setCellAttribute(0, 0, "width", "35%");
        $table->setCellAttribute(0, 1, "width", "64%");

        $spreadsheet = $busCatalogue->getSpreadsheetFromSession();
        $controlNumber = $busCatalogue->getControlNumberFromSession();

        $table->array[] = array( MARC_CONTROL_NUMBER_TAG . " - " . _M("Número de controle", $module), $controlNumber );

        if ( $spreadsheet )
        {
            foreach ( $spreadsheet as $tab => $spEti )
            {
                foreach ( $spEti as $etiqueta => $objEti )
                {
                    if ( !$objEti->subFields || $etiqueta == MARC_EXEMPLARY_FIELD || $etiqueta == MARC_KARDEX_FIELD )
                    {
                        continue;
                    }

                    foreach ( $objEti->subFields as $subfield => $object )
                    {
                        $value = null;

                        foreach ( $object->formValue as $linha => $valor )
                        {
                            if ( $etiqueta == MARC_FIXED_DATA_FIELD && "$etiqueta.$subfield" != MARC_FIXED_DATA_TAG )
                            {
                                continue;
                            }

                            if ( strlen($valor) )
                            {
                                if ( isset($object->fieldContent) && isset($object->fieldContent->options) )
                                {
                                    if ( isset($object->fieldContent->options[$valor]) && strlen($object->fieldContent->options[$valor]) )
                                    {
                                        $valor = $valor . " - " . $object->fieldContent->options[$valor];
                                    }
                                }

                                if ( isset($object->prefix) && is_array($object->prefix) && isset($object->loadPrefix[$linha]) && strlen($object->loadPrefix[$linha]) )
                                {
                                    $valor = "{$object->prefix[$object->loadPrefix[$linha]]}$valor";
                                }
                                if ( isset($object->suffix) && is_array($object->suffix) && isset($object->loadSuffix[$linha]) && strlen($object->loadSuffix[$linha]) )
                                {
                                    $valor.= "{$object->suffix[$object->loadSuffix[$linha]]}";
                                }

                                $d = new MDiv("", "$valor");
                                $value.= $d->generate();
                            }
                        }

                        if ( !is_null($value) )
                        {
                            $label = "{$object->name}" . (strlen($object->label) ? " - {$object->label}" : "" );
                            $table->array[] = array( $label, $value );
                        }
                    }
                }
            }
        }

        $fields[] = new MDiv("divWorkInfos", $table);

        //exemplary
        $spreadsheet = $busCatalogue->getSpreadsheetFromSession();
        $c = 0;

        if ( $spreadsheet )
        {
            foreach ( $spreadsheet as $tab => $spEti )
            {
                foreach ( $spEti as $etiqueta => $objEti )
                {
                    if ( !$objEti->subFields || ($etiqueta != MARC_EXEMPLARY_FIELD && $etiqueta != MARC_KARDEX_FIELD) )
                    {
                        continue;
                    }

                    $colCount = count($objEti->subFields);

                    foreach ( $objEti->subFields as $subfield => $object )
                    {
                        $tableColumns[$c] = "{$object->tag}<br>{$object->label}";

                        foreach ( $object->formValue as $linha => $valor )
                        {
                            if ( strlen($valor) )
                            {
                                if ( isset($object->fieldContent) && isset($object->fieldContent->options) )
                                {
                                    if ( isset($object->fieldContent->options[$valor]) && strlen($object->fieldContent->options[$valor]) )
                                    {
                                        $valor = $valor . " - " . $object->fieldContent->options[$valor];
                                    }
                                }
                            }
                            else
                            {
                                $valor = ' ';
                            }

                            //completa a quantidade de colunas no array
                            if ( !$tableArray[$linha] )
                            {
                                $tableArray[$linha] = array_fill(0, $colCount, '');
                            }

                            $tableArray[$linha][$c] = $valor;
                        }

                        $c++;
                    }
                }
            }
        }

        $table = new MTableRaw(_M("Exemplar", $module), $tableArray, $tableColumns, 'tableExemplaryInfo');
        $table->setAlternate(true);
        $table->addAttribute("Style", "width:100%");

        //botões inferiores
        $businessSpreadsheet = $this->MIOLO->getBusiness($this->module, "BusSpreadsheet");

        //pega o menu conforme o tipo do material
        $type = $this->businessMaterialControl->getTypeOfMaterial($controlNumber);

        if ( $type )
        {
            //aqui os selects estão com else, então só executa 1
            if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_BOOK )
            {
                $menu = $businessSpreadsheet->getMenus('BA', '4', false); //false faz pegar mesmo que esteja escondido/sem nome
            }
            else if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_BOOK_ARTICLE )
            {
                $menu = $businessSpreadsheet->getMenus('BA', '4', false);
            }
            else if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION )
            {
                $menu = $businessSpreadsheet->getMenus('SE', '4', false);
            }
            else if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION_FASCICLE )
            {
                $menu = $businessSpreadsheet->getMenus('SA', '4', false);
            }
            else if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION_ARTICLE )
            {
                $menu = $businessSpreadsheet->getMenus('SA', '4', false);
            }
        }

        //se tem menu de filho adiciona o botão
        if ( $menu )
        {
            $menu = $menu[0];
            $menuoption = str_replace("#", "*", $menu->menuoption);

            //se não encontrou nome para o menu, coloca nome padrão
            if ( !$menu->menuname )
            {
                $menu->menuname = _M('Novo filho', $this->module);
            }

            $controlNumberForButton = $controlNumber;

            //Se salvou um artigo de fasciculo ou de livro pega o número de controle do pai.
            if ( $type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION_ARTICLE || $type == BusinessGnuteca3BusMaterialControl::TYPE_BOOK_ARTICLE )
            {
                $controlNumberForButton = $controlNumberFather[0];
            }

            $buttons['newChildren'] = new GRealLinkButton('newChildren', $menu->menuname, "main:catalogue:material&function=addChildren&controlNumber={$controlNumberForButton}&leaderString=$menuoption", GUtil::getImageTheme('addChild-16x16.png'));
        }

        //link para duplicar o material
        $buttons['duplicateMaterial'] = new GRealLinkButton('duplicateMaterial', _M('Duplicate material', $this->module), "main:catalogue:material&function=duplicate&controlNumber={$controlNumber}", GUtil::getImageTheme('duplicateMaterial-16x16.png'));

        //link para pesquisa
        if ( !$preCatalogue )
        {
            $args = array( 'controlNumber' => $this->business->controlNumber, 'searchFormat' => SIMPLE_SEARCH_SEARCH_FORMAT_ID );
            $buttons[] = new GRealLinkButton('gotoSearch', _M('Pesquisa', $this->module), 'main:search:simpleSearch', GUtil::getImageTheme('search-16x16.png'), $args);
        }

        //link para fechar a janela
        $buttons[] = GForm::getCloseButton();

        $fields[] = new MDiv("divExemplaryInfos", $table);
        $fields[] = new MSeparator('<br/>');
        $fields[] = new MSeparator('<br/>');
        $fields['contButtons'] = new MDiv('divButtons', $buttons);
        $fields['contButtons']->addStyle('float', 'left');
        $fields['contButtons']->addStyle('width', '100%');

        return new MDiv('materialSaveContent', $fields);
    }

    /**
     * Função que salva o material na pré-catalogação
     *
     * @param stdClass $args
     */
    function saveSpreadsheetPreCatalogue($args)
    {
        //garante que tenha que ser permissão de update ou inser
        $function = MIOLO::_REQUEST('function') == 'update' ? 'update' : 'insert';

        if ( GPerms::checkAccess('gtcPreCatalogue', $function, false) )
        {
            $args = $this->getData(true);
            $argsx = $this->prepareEspecialFields($args);
            $r = $this->business->setFormValues($argsx);
            $NC = $this->business->savePreCatalogue();
            $this->saveCover(null, 'coverpre');
            $this->jsSetValue('controlNumber', $NC);
            $this->jsSetValue('lastChange', GDate::now());
            $this->jsSetValue($this->business->getWorkNumberFieldName(), $this->business->getWorkNumberValue());

            $this->injectContent($this->getSaveContent(true)->generate(), false, _M('Material salvo', $this->module));
        }
        else
        {
            $this->error(_M('Você não possui permissão para gravar na pré-catalogação.', $this->module));
        }
    }
    /*
     * Método reescrito chamado ao apertar F2
     *
     */

    public function onkeydown113()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        //verifica se existe um código leader na url, caso tiver, é porque está inserindo um material diferente do padrão
        $leaderString = MIOLO::_REQUEST('leaderString');

        if ( $leaderString )
        {
            $url = $MIOLO->getActionURL($module, "main:catalogue:material&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=dinamicMenu&leaderString={$leaderString}");
        }
        else
        {
            $url = $MIOLO->getActionURL($module, "main:catalogue:material&__mainForm__EVENTTARGETVALUE=tbBtnNew:click&function=new");
        }

        $MIOLO->page->redirect($url);
    }

    /**
     * Método reescrito para chamar o método que salvar a catalogação
     *
     */
    public function onkeydown114($args) //F3 save
    {
        if ( !$args->leaderFields )
        {
            $this->saveSpreadsheet($args);
        }
        else
        {
            $this->setResponse(null, 'limbo');
        }
    }

    /**
     * Chama a pré-catalogação ao apertar a tecla F9 . Tecla de Atalho.
     *
     */
    public function onkeydown119($args) //F8 pre-catalogue
    {
        if ( (in_array($this->function, array( 'insert', 'new', 'dinamicMenu' )) || $this->isPreCatalogue()) && ($args->wasSaved != DB_TRUE ) && (!$args->leaderFields) )
        {
            $this->saveSpreadsheetPreCatalogue($args);
        }
        else
        {
            $this->setResponse(null, 'limbo');
        }
    }

    /**
     * Método reescrito para não limpar com a tecla F7
     *
     */
    public function onkeydown118()
    {
        $this->setResponse('', 'limbo');
    }

    public function checkAccess()
    {
        return GPerms::checkAccess('gtcMaterial', null, false) || GPerms::checkAccess('gtcPreCatalogue', null, false);
    }
}

?>
