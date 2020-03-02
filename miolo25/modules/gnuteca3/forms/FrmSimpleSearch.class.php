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
 * */
class FrmSimpleSearch extends GForm
{
    public $busLibraryUnit;
    public $busLoan;
    public $busLoanType;
    public $busMaterialType;
    public $busSearchableField;
    public $busSearchFormat;

    /**
     * Bussines de autenticação
     *
     * @var BusinessGnuteca3BusAuthenticate
     */
    public $busAuthenticate;
    public $busDictionary;
    public $busExemplaryControl;
    public $busExemplaryStatus;
    public $busMaterialControl;
    public $busMaterial;
    public $busGenericSearch;
    public $busReserve;
    public $busReserveStatus;
    public $busReserveType;
    public $busPerson;
    public $busOperatorLibraryUnit;
    public $busBond;
    public $busPreference;
    public $busRight;
    public $busMaterialPhysicalType;
    public $librarys;
    public $options;
    public $exemplarys = array( );
    public $isMaterialMovement; //se é ou não circulação de material
    public $calledReserveConfirm;
    public $_termControl = 0;

    public function __construct($options)
    {
        $_SESSION['externalSearch'] = null; //limpa a posição externalSearch
        $this->options = $options;
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');
        $this->busDictionary = $MIOLO->getBusiness($module, 'BusDictionary');
        $this->busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
        $this->busMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $this->busMaterialType = $MIOLO->getBusiness($module, 'BusMaterialType');
        $this->busGenericSearch = $MIOLO->getBusiness($module, 'BusGenericSearch2');
        $this->busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busLoan = $MIOLO->getBusiness($module, 'BusLoan');
        $this->busLoanType = $MIOLO->getBusiness($module, 'BusLoanType');
        $this->busPerson = $MIOLO->getBusiness($module, 'BusPerson');
        $this->busReserve = $MIOLO->getBusiness($module, 'BusReserve');
        $this->busReserveStatus = $MIOLO->getBusiness($module, 'BusReserveStatus');
        $this->busReserveType = $MIOLO->getBusiness($module, 'BusReserveType');
        $this->busSearchableField = $MIOLO->getBusiness($module, 'BusSearchableField');
        $this->busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        $this->busPreference = $MIOLO->getBusiness($module, 'BusPreference');
        $this->busBond = $MIOLO->getBusiness($module, 'BusBond');
        $this->busRight = $MIOLO->getBusiness($module, 'BusRight');
        $this->busOperatorLibraryUnit = $MIOLO->getBusiness($module, 'BusOperatorLibraryUnit');
        $this->busMaterialPhysicalType = $MIOLO->getBusiness($module, 'BusMaterialPhysicalType');

        $MIOLO->getClass($module, 'controls/GTree');
        $MIOLO->getClass($module, 'controls/GSearchMenu');
        $MIOLO->getClass($module, 'GPDF');
        $MIOLO->getClass($module, 'GMail');
        $MIOLO->getClass($module, 'report/rptSimpleSearch');
        $MIOLO->getClass($module, 'controls/GMaterialDetail');
        $MIOLO->getClass('gnuteca3', 'controls/GReserveWeb');
        $MIOLO->uses('classes/GAdvancedFilters.class.php', $module);

        //Nao chamar __construct e outros
        if ( $option->from == 'FAVORITES' )
        {
            return;
        }

        parent::__construct('Pesquisa', $this->module);
        
        if ( $this->primeiroAcessoAoForm() && strlen(MIOLO::_REQUEST('event')) == 0 )
        {
            $this->setFocus('termText[]');
        }

        if ( !$this->getEvent() )
        {
            $this->setCurrentSubForm('');
        }

        if ( !$this->getCurrentSubForm() || $this->getEvent() == 'gridReserve' )
        {
            $controlNumber = MIOLO::_REQUEST('_id') ? MIOLO::_REQUEST('_id') : MIOLO::_REQUEST('controlNumber');
        }
        else
        {
            if ( $this->getEvent() == 'subForm' && GUtil::getAjaxEventArgs() )
            {
                $subForm = GUtil::getAjaxEventArgs();
            }
            else
            {
                $subForm = $this->getCurrentSubForm();
            }

            //caso não encontre das formas padrão pega do request
            if ( !$subForm && MIOLO::_REQUEST('subForm') )
            {
                $subForm = ucfirst(MIOLO::_REQUEST('subForm'));
            }

            $this->subForm($subForm);
        }

        if ( $this->options->noDefineFields )
        {
            $this->keyDownHandler(27, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123);//MaterialMovement js keycodes
        }
        else
        {
            $this->keyDownHandler();
        }

        //só chama o defineFields caso não tenha evento, evita processar informações desnecessárias
        if ( !$options->noDefineFields && (!$this->getEvent() || $this->getEvent() == 'btnLoginUpper_click' ) )
        {
            //Chama subform caso for chamado via handler
            $subForm = ucfirst(trim(MIOLO::_REQUEST('subForm')));
            $this->defineFields(null, $subForm ? true : false);

            if ( strlen($subForm) > 0 )
            {
                $this->getControlById('searchFields')->setInner($this->subForm($subForm, true));
            }
        }

        //$this->page->onLoad('gnutecaSearch.changeAddTermStatus()');
        $this->isMaterialMovement = (MIOLO::_REQUEST('action') == 'main:materialMovement');

        if ( $this->externalSearch() )
        {
            $this->page->onLoad("if(dojo.byId('mContainerTopmenu')){ dojo.byId('mContainerTopmenu').style.display = 'none'; }");
            $this->setExternalSearch();
        }

        //mostra barra superior caso usuário esteja logado e tenha alguma permissão
        if ( GOperator::isLogged() && GOperator::hasSomePermission() )
        {
            $this->jsShow('gUpperBar');
        }
        else
        {
            $this->jsHide('gUpperBar');
        }

        //caso tenha integração
        if ( defined('SOCIAL_INTEGRATION') && MUtil::getBooleanValue(SOCIAL_INTEGRATION) == DB_TRUE )
        {
            //obtem conteúdo da integração de redes sociais e extrai os javascripts para adição via miolo
            preg_match_all('/<script[^>]+src="([^"]+)/', SOCIAL_CONTENT, $scripts);

            $scripts = $scripts[1]; //pega a parte importante do preg_match

            if ( is_array($scripts) )
            {
                foreach ( $scripts as $line => $script )
                {
                    //adiciona scripts externos
                    $this->page->addExternalScript($script);
                }
            }
        }
    }

    /**
     * função que verifica se é uma consulta feita atravez de outro software
     *
     * @return unknown
     */
    function externalSearch()
    {
        return (MIOLO::_REQUEST('action') == 'main:search:externalSearch') || $_SESSION['externalSearch'];
    }

    function setExternalSearch()
    {
        $_SESSION['externalSearch'] = TRUE;
    }

    public function defineFields($args = null, $subForm = null)
    {
        $fields = $this->getFields($args, null, $subForm);

        if ( $this->options->noDefineFields )
        {
            return $fields;
        }
        else
        {
            $this->setFields($fields, false, false);
        }
    }

    public function changeFormContent($args)
    {
        $fields = $this->getFields($args, true);
        $this->setResponse($fields, 'searchFields');
    }

    public function btnLoginUpper_click($args, $subForm = false)
    {
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();

        $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
        $busAuthenticate = $MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');

        $personId = $busPerson->insertPersonLdapWhenNeeded($args->personIdUpper,$args->baseLdap,$args->passwordUpper);
        //Se for autenticacao pelo moodle, utiliza o login digitado no campo Usuario na minha biblioteca.
        $personId = ($MIOLO->getConf('login.classUser') == 'gAuthMoodle')? $args->personIdUpper : $personId;

        //faz a autenticação
        $busAuthenticate->authenticate($personId, $args->passwordUpper);

        if ( $busAuthenticate->getUserCode() )
        {
            //recarrega para que apareça as opções do usuário
            $args = array( 'subForm' => 'MyLibrary' );
            $url = $MIOLO->getActionURL('gnuteca3', $action, null, $args); //monta a URL para minha biblioteca

            $MIOLO->page->onLoad("window.location = '{$url}';");

            //se for ajax
            if ( !$subForm )
            {
                $this->setResponse(null, 'limbo');
            }
        }
        else
        {
            GForm::error(_M('Falha ao logar, verifique usuário e senha digitados.'));
        }
    }

    /**
     * Retorna os campos superiores, de usuário logado
     * @return MDiv
     */
    public function getLoggedFields()
    {
        $link = new MLink(null, '[Sair]', 'index.php?module=gnuteca3&action=main:logout&redirect_action=main:search:simpleSearch&goto_direct=1&loginType=' . LOGIN_TYPE_USER);
        $link->setClass('mLinkSimpleSearch');
        Gutil::accessibility($link, 40, _M('Sair', $this->module));
        $fields[0] = new MDiv('', 'Olá <b>' . $this->busAuthenticate->getUserName() . '</b> - ' . $link->generate());
        $fields[0]->setClass('mLabelSimpleSsearch');

        $fields[] = new MSeparator('</br>');

        $fields[] = $link = new MDiv('', new MLink('about', _M('Sobre'), 'javascript:' . Gutil::getAjax('statusAbout'), ''));
        $link->setClass('mLabelSimpleSsearch');

        return $fields;
    }

    public function getFields($args, $returnFieldsToAjax = false, $subForm)
    {
        $topFields[0] = new MDiv('divPesquisa', '');

        $busHelp = $this->MIOLO->getBusiness('gnuteca3', 'BusHelp');

        //só mostra botão de ajuda se tiver ajuda cadastrada
        if ( $busHelp->getFormHelp('FrmSimpleSearch', MIOLO::_REQUEST('formContentId')) instanceof stdClass )
        {
            $topFields[1] = new MDiv('divAjudaEx', "<a id='divAjuda' href='javascript:gnuteca.help();' onclick='gnuteca.help();' alt='Ajuda' title='Ajuda'></a>");
        }

        $lines[] = new GContainer('topFields', $topFields);

        $isMaterialMovement = (MIOLO::_REQUEST('action') == 'main:materialMovement');

        //verifica se o usuário está logado ou se precisa mostrar formulário de login
        if ( !$this->busAuthenticate->getUserCode() )
        {
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                $fieldsLogin[] = $codLabel = new MLabel(_M('Usuário:'), null, true);
            }
            else
            {
                $fieldsLogin[] = $codLabel = new MLabel(_M('Código:'), null, true);
            }

            $codLabel->setClass('mLabelSimpleSearch');
            $fieldsLogin[] = $personId = new MTextField('personIdUpper');
            $personId->setClass('mTextField  mTextFieldSimpleSearch');
            $personId->addAttribute('onPressEnter', GUtil::getAjax('btnLoginUpper_click'));
            $fieldsLogin[] = $passwordLabel = new MLabel(_M('Senha:'), null, true);
            $passwordLabel->setClass('mLabelSimpleSearch');
            $fieldsLogin[] = $password = new MPasswordField('passwordUpper');
            $password->addAttribute('onPressEnter', GUtil::getAjax('btnLoginUpper_click'));
            $password->setClass('mTextField  mTextFieldSimpleSearch');
            $fieldsLogin[] = $btnLoginUpper = new MButton('btnLoginUpper', _M('Autenticar'), ':btnLoginUpper_click');
            $first = new MDiv('firstLineDiv', $fieldsLogin);

            $bases = BusinessGnuteca3BusAuthenticate::listMultipleLdap();

            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
            {
                $secondF[] = $label = new MLabel(_M('Base:'), null, true);
                $label->setClass('mLabelSimpleSearch');
                $secondF[] = $baseLdap = new GSelection('baseLdap', '', '', $bases, false, '', '', true);
                Gutil::accessibility($baseLdap, 40);
            }
            $secondF[] = $link = new MDiv('', new MLink('about', _M('Sobre'), 'javascript:' . Gutil::getAjax('statusAbout'), ''));
            $link->setClass('mLabelSimpleSsearch');

            $second = new GContainer('secondLineDiv', $secondF);

            $upperUserLogin = new MVContainer('upperUserLogin', array( $first, $second ));

            Gutil::accessibility($personId, 40);
            Gutil::accessibility($password, 40);
            Gutil::accessibility($btnLoginUpper, 40);
        }
        else
        {
            $upperUserLogin = new GContainer('upperUserLogin', $this->getLoggedFields());
        }

        $this->busFormContent = new BusinessGnuteca3BusFormContent();
        $this->busFormContent->formContentType = $this->getFormContentTypeId();

        if ( MIOLO::_REQUEST('action') == 'main:materialMovement' && !MIOLO::_REQUEST('formContentTypeId_current') )
        {
            $this->busFormContent->formContentType = 1;
            $this->busFormContent->nameS = 'materialMovement';
        }
        else
        {
            $this->busFormContent->formContentId = $this->getFormContentId();
        }

        if ( !$subForm )
        {
            $formContent = $this->busFormContent->loadFormValues('frmsimplesearch', TRUE); //nesta caso tem que ser hardcode frmsimplesearch em função de estar dentro da circulação
        }

        //verifica de acordo com preferências se é ou não para permitir pesquisa em todas unidades
        if ( GOperator::isLogged() )
        {
            $searchAllLibrary = MUtil::getBooleanValue(SIMPLE_SEARCH_ALL_LIBRARYS_OPERATOR);
        }
        else
        {
            $searchAllLibrary = MUtil::getBooleanValue(SIMPLE_SEARCH_ALL_LIBRARYS_PERSON);
        }

        // CASO O SISTEMA NAO ESTEJA UTILIZANDO AUTENTICAÇÃO
        if ( MUtil::getBooleanValue($this->MIOLO->getConf('options.authenticate')) == false )
        {
            $searchAllLibrary = MUtil::getBooleanValue(SIMPLE_SEARCH_ALL_LIBRARYS_OPERATOR);
        }

        if ( !$this->options->noDefineFields && !$this->externalSearch() )
        {
            $upperMenu[] = $myMenu = new GUserMenu();

            //caso tenha menu esquerdo adiciona uma classe extra nessa divs para poder posicioná-la
            if ( $this->getFrmLogin()->isAuthenticated() && !MIOLO::_REQUEST('subForm') )
            {
                $myMenu->setClass('extraClass');
            }

            $upperMenu[] = new GSearchMenu();
        }

        //Library unit
        $this->busLibraryUnit->onlyWithAccess = true;
        $librarys = $this->busLibraryUnit->listLibraryUnitForSearch(true);
        $this->librarys = $librarys;

        //coloca na sessão para poder utilizar na grid
        $_SESSION['libraryCount'] = count($librarys);

        //Se não tiver um id definido no primeiro registro é porque é o registro 
        //de 'Todas unidades' e ele não deve ser levado em conta.
        if ( empty($this->librarys[0][0]) )
        {
            $_SESSION['libraryCount']--;
        }

        //se somente tiver uma unidade não mostra o selection
        if ( count($librarys) == 1 )
        {
            $libraryUnitId = new MHiddenField('libraryUnitId', $librarys[0][0]);
        }
        else
        {
            //da preferência ao valor do cookie, caso contrário obtem do conteúdo do formulário
            $libraryValue = $_COOKIE['libraryUnitId'] ? $_COOKIE['libraryUnitId'] : $formContent['libraryUnitId'] ;
            $lblLibraryUnit = new MLabel(_M('Biblioteca', $this->module));
            $libraryUnitId = new GSelection('libraryUnitId', $libraryValue, null, $librarys, null, null, null, true);
            $libraryUnitId->addAttribute('onchange', 'var expiration_date = new Date(); expiration_date.setFullYear(expiration_date.getFullYear() + 30); document.cookie = \'libraryUnitId=\'+this.value+\'; expires=\'+expiration_date.toGMTString()+\';\';');
            Gutil::accessibility($libraryUnitId, 20, _M('Unidade de biblioteca', $this->module));
        }

        $hctLibraryUnit = new MContainer('hctLibraryUnit', array( $lblLibraryUnit, $libraryUnitId ), 'none', MFormControl::FORM_MODE_SHOW_NBSP);
        $advFilter[$advFilterIndex] = new MDiv('advFilter0', '');
        $advFilterIndex = '0';

        $lblAdvancedFilters = new MLabel(_M('Filtros avançados', $this->module));
        $lblAdvancedFilters->setWidth('150px');
        $lblAdvancedFilters->setBold(TRUE);
        
        #######################################
        //Obtem campos de filtro avançado
        
        $camposAvancados = getFilterList();
        
        //Realiza iteração nos campos, para definir campos nativos, e campos novos
        foreach ($camposAvancados as $cA => $tt)
        {
            //Caso for um campo novo
            if(is_array($tt))
            {
                //Para cada campo novo, adiciona seu Id e sua descrição.
                foreach($tt as $CASE)
                {
                    $fieldAdvId = $CASE[1][0];
                    $fieldAdvDesc = $CASE[1][1];
                    
                    $camposAdv[] = array($fieldAdvId, $fieldAdvDesc);
                }
            }
            else
            {
                //$cA é identificador
                //$tt é a descrição
                $camposAdv[] = array($cA, $tt);
            }
        }
        #######################################
        
        //$_advancedFilters = new GSelection('advancedFilters', null, null, getFilterList(), null, null, null, false);
        $_advancedFilters = new GSelection('advancedFilters', null, null, $camposAdv, null, null, null, false);
        Gutil::accessibility($_advancedFilters, 20, _M('Filtros avançados', $this->module));
        
        
        
        $btnAdd = new MImageButton('addAdvFilters', null, 'javascript:' . GUtil::getAjax('addAdvFilter'), GUtil::getImageTheme('add-16x16.png'));
        Gutil::accessibility($btnAdd, 20, _M('Adicionar filtros avançados', $this->module));
        $btnClear = new MImageButton('clearAdvFilters', null, "javascript:gnutecaSearch.clearAdvFilters();", GUtil::getImageTheme('clear-16x16.png'));
        Gutil::accessibility($btnClear, 20, _M('Esconder filtros avançados', $this->module));
        $lines[] = new MHiddenField('advFilterControl', $advFilterIndex);

        $accurateTerm = new MCheckBox('accurateTerm', true, null, MUtil::getBooleanValue($formContent['accurateTerm']), _M('Termo exato', $this->module));
        Gutil::accessibility($accurateTerm, 20, _M('Termo exato', $this->module));

        $advancedFiltersField = new GContainer('hctAdvancedFilters', array( $lblAdvancedFilters, $_advancedFilters, $btnAdd, $btnClear, $accurateTerm ));
        
        //Tipo de material
        $listMaterialType = $this->busMaterialType->listMaterialType(null, !GOperator::isLogged());

        if ( !is_array($listMaterialType) )
        {
            $listMaterialType = array( ); //para evitar erro no array_merge
        }

        //caso for pesquisa de periódico mostra só o id referente a isso
        if ( $formContent['letterField'] )
        {
            //Pega todos os tipos de materiais que são considerados coleção de periodico
            $materialTypeIdPeriodicCollection = explode(",", trim(MATERIAL_TYPE_ID_PERIODIC_COLLECTION));

            //busca pela descrição certa
            if ( is_array($listMaterialType) )
            {
                //Adiciona todos os tipos de materiais que são colecao nos dados da combo
                foreach ( $listMaterialType as $line => $materialType )
                {
                    if ( in_array($materialType[0], $materialTypeIdPeriodicCollection) )
                    {
                        $types[$materialType[0]] = $materialType[1];
                    }
                }
            }

            $listMaterialType = $types;
        }
        else
        {
            $listMaterialTypeExtra[''] = _M('Todos', $this->module);
            $listMaterialType = array_merge($listMaterialTypeExtra, $listMaterialType);
        }

        $flds[] = new MLabel(_M('Tipo do material', $this->module));
        $flds[] = $materialTypeId = new GSelection('materialTypeId', $formContent['materialTypeId'], null, $listMaterialType, null, null, null, true);
        $materialTypeId->addAttribute('tabindex', '20');
        $materialType = new GContainer('hctMaterialType', $flds);

        //campos pesquisaveis
        if ( !GOperator::isLogged() )
        {
            $this->busSearchableField->onlyWithAccess = true;
        }

        $listTermType = $this->busSearchableField->listSearchableField(true);

        //adicionar Filtrar por na label da primeira opção
        foreach ( $listTermType as $line => $term )
        {
            $listTermType[$line] =  $term;
            break;
        }

        //adiciona filtro fixo de expressão
        $listTermType = array_merge($listTermType, array( '0' => _M('Expressão', $this->module) ));
        $termType = new GSelection('termType[]', $formContent['termType[]'], null, $listTermType, null, null, null, true);
        $termType->addAttribute('onchange', 'gnutecaSearch.changeAddTermStatus()');
        $termType->addAttribute('style', 'width:127px');
        Gutil::accessibility($termType, 20, _M('Campo a pesquisar', $this->module));

        $conditions['LIKE'] = _M('Contém', $this->module);
        $conditions['START'] = _M('Inicia com', $this->module);
        $conditions['END'] = _M('Termina com', $this->module);
        $conditions['='] = _M('Igual', $this->module);
        $conditions['<='] = _M('Menor ou igual', $this->module);
        $conditions['>='] = _M('Maior ou igual', $this->module);
        $conditions['!='] = _M('Diferente', $this->module);
        
        $termConditionS = new GSelection('termCondition[]', $formContent['termCondition[]'], null, $conditions, null, null, null, true);
        $termConditionS->addAttribute('tabindex', '20');
        $termConditionS->addAttribute('alt', _M('Condição a pesquisar', $this->module));
        $termConditionS->addAttribute('title', _M('Condição a pesquisar', $this->module));
        $termConditionS->setClass('mSelection termCondition', true);

        if ( !$isMaterialMovement && !MUtil::getBooleanValue(SIMPLE_SEARCH_SHOW_TERM_CONDITION) && SIMPLE_SEARCH_SHOW_TERM_CONDITION != 'SIMPLE_SEARCH_SHOW_TERM_CONDITION' )
        {
            $termConditionS->addStyle('display', 'none');
        }

        $termText = new MTextField('termText[]', $formContent['termText[]'], null, FIELD_DESCRIPTION_SIZE - 2);
        $termText->setClass('mTextField mTextFieldTermText');
        Gutil::accessibility($termText, 20, _M('Termo a pesquisar. Pressione enter para executar a busca.', $this->module));

        $termOpt = new MHiddenField('termOpt[]', null, null);
        $addTerm = new MImageButton('addTerm', '', 'javascript:' . GUtil::getAjax('addTerm'), GUtil::getImageTheme('add-16x16.png'));
        Gutil::accessibility($addTerm, 20, _M('Adicionar filtro a pesquisa', $this->module));
        //Gutil::accessibility( $addTerm->image ,20, _M('Adicionar filtro a pesquisa', $this->module) );
        $extraTerm0 = new MDiv('extraTerms0', array( $termType, $termConditionS, $termText, $termOpt, $addTerm ));
        $searchTerms[] = new MDiv('extraTermsContainer', array( $extraTerm0 ));

        //caso exista dados do operador, monta campos necessários
        if ( $formContent && $this->getEvent() != "addTerm" )
        {
            //dados da base (save form content)
            eval('$termType= ' . $formContent['termType'] . ';');
            eval('$termText= ' . $formContent['termText'] . ';');
            eval('$termOpt = ' . $formContent['termOpt'] . ';');

            if ( $formContent['termCondition'] )
            {
                eval('$termCondition = ' . $formContent['termCondition'] . ';');
            }

            //monta campos vindos das preferências do usuário
            if ( is_array($termType) )
            {

                foreach ( $termType as $line => $info )
                {
                    if ( $line > 0 )
                    {
                        $tempArgs = new StdClass();
                        $tempArgs->return = true;
                        $tempArgs->termType = $termType[$line];
                        $tempArgs->termText = $termText[$line];
                        $tempArgs->termOpt = $termOpt[$line];
                        $tempArgs->termCondition = $termCondition[$line];
                        $searchTerms[] = $this->addTerm($tempArgs);
                        $tempArgs->termControl++;
                    }
                }
            }
        }
        $searchTerms[] = new MDiv('divExtraTerms' . ($this->getTermControl() + 1));
        $bgrSearchTerms = new GContainer('searchTerms', $searchTerms);
        $lines[] = new MHiddenField('termControl', $this->getTermControl());

        //BaseGroup do lado direito
        $rightFields[] = $advancedFiltersField;
        //$rightFields[] = new MDiv('divLogin', $this->getLoginButton());

        $args = new StdClass();

        if ( GOperator::isLogged() )
        {
            $args->formContentTypeId = FORM_CONTENT_TYPE_OPERATOR;
            $rightFields[] = new MDiv('divFormContent' . FORM_CONTENT_TYPE_OPERATOR, $this->getFormContentFields($args));
        }
        if ( GPerms::checkAccess('gtcSearchAdministrator', null, false) )
        {
            $args->formContentTypeId = FORM_CONTENT_TYPE_ADMINISTRATOR;
            $rightFields[] = new MDiv('divFormContent' . FORM_CONTENT_TYPE_ADMINISTRATOR, $this->getFormContentFields($args));
        }

        $bgrRight = new MBaseGroup('bgrRight', NULL, $rightFields);
        $bgrRight->setClass('simpleSearchContainerRight');

        $busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        
        $value = $formContent['searchFormat'];
        
        if ( !$value )
        {
            $value = SIMPLE_SEARCH_SEARCH_FORMAT_ID;
        }

        $searchFormat = new GSelection('searchFormat', $value, null, $busSearchFormat->listSearchFormat(false, GOperator::isLogged() ? false : true), false, null, null, true);
        Gutil::accessibility($searchFormat, 20, _M('Formato de Visualização da Pesquisa', $this->module));
        $searchFormat = new GContainer('searchFormatContainer', array( new MLabel(_M('Formato', $this->module)), $searchFormat ));

        $vctLeft = new MVContainer('vctLeft', array( $hctLibraryUnit, $materialType, $sfLabel, $bgrSearchTerms, $termCheck, $searchFormat ));
        $vctLeft->setClass('simpleSearchContainerLeft');
        $hctMisc = new GContainer('hctMisc', array( $vctLeft, $bgrRight ));
        $lines[] = $hctMisc;

        //Advanced filters box
        //le as preferências do usuário e monta os botões necessários, parte da pesquisa especial
        if ( $formContent['exemplaryStatusId'] )
        {
            $advFilterIndex++;
            $tempArgs = new StdClass();
            $tempArgs->advancedFilters = 'AFMaterialStatus';
            $tempArgs->return = true;
            $tempArgs->exemplaryStatusId = $formContent['exemplaryStatusId'];
            $tempArgs->advFilterControl = $advFilterIndex;
            $advFilter[$advFilterIndex] = new MDiv('advFilter' . $advFilterIndex, $this->addAdvFilter($tempArgs));
        }

        if ( array_key_exists('limit', $formContent) )
        {
            $advFilterIndex++;
            $tempArgs = new StdClass();
            $tempArgs->advancedFilters = 'AFLimit';
            $tempArgs->return = true;
            $tempArgs->limit = $formContent['limit'];
            $tempArgs->advFilterControl = $advFilterIndex;
            $advFilter[$advFilterIndex] = new MDiv('advFilter' . $advFilterIndex, $this->addAdvFilter($tempArgs));
        }

        if ( array_key_exists('editionYearFrom', $formContent) || array_key_exists('editionYearTo', $formContent) )
        {
            $advFilterIndex++;
            $tempArgs = new StdClass();
            $tempArgs->advancedFilters = 'AFEditionYear';
            $tempArgs->return = true;
            $tempArgs->editionYearFrom = $formContent['editionYearFrom'];
            $tempArgs->editionYearTo = $formContent['editionYearTo'];
            $tempArgs->advFilterControl = $advFilterIndex;
            $advFilter[$advFilterIndex] = new MDiv('advFilter' . $advFilterIndex, $this->addAdvFilter($tempArgs));
            $advFilterIndex++;
        }

        if ( $formContent['orderField'] || $formContent['orderType'] )
        {
            $advFilterIndex++;
            $tempArgs = new StdClass();
            $tempArgs->advancedFilters = 'AFOrder';
            $tempArgs->return = true;
            $tempArgs->orderField = $formContent['orderField'];
            $tempArgs->orderType = $formContent['orderType'];
            $tempArgs->advFilterControl = $advFilterIndex;
            $advFilter[$advFilterIndex] = new MDiv('advFilter' . $advFilterIndex, $this->addAdvFilter($tempArgs));
            $advFilterIndex++;
        }

        if ( $formContent['letter'] )
        {
            $advFilterIndex++;
            $tempArgs = new StdClass();
            $tempArgs->advancedFilters = 'AFLetter';
            $tempArgs->return = true;
            $tempArgs->letter = $formContent['letter'];
            $tempArgs->letterField = $formContent['letterField'];
            $tempArgs->advFilterControl = $advFilterIndex;
            $advFilter[$advFilterIndex] = new MDiv('advFilter' . $advFilterIndex, $this->addAdvFilter($tempArgs));
            $advFilterIndex++;
        }

        if ( array_key_exists('aquisitionFrom', $formContent) || array_key_exists('aquisitionTo', $formContent) )
        {
            $advFilterIndex++;
            $tempArgs = new StdClass();
            $tempArgs->advancedFilters = 'AFAquisition';
            $tempArgs->return = true;
            $tempArgs->aquisitionFrom = $formContent['aquisitionFrom'];
            $tempArgs->aquisitionTo = $formContent['aquisitionTo'];
            $tempArgs->advFilterControl = $advFilterIndex;
            $advFilter[$advFilterIndex] = new MDiv('advFilter' . $advFilterIndex, $this->addAdvFilter($tempArgs));
            $advFilterIndex++;
        }

        $advancedFiltersContent = null;
        $advancedFiltersContent[0][] = new MDiv('advancedFiltersContent', $advFilter);
        $lines[] = new MSeparator();
        $bgrAdvFilter = new Div('advFilterContainer', $this->mountContainers($advancedFiltersContent));
        $bgrAdvFilter->addStyle('display', 'none');
        $lines[] = $bgrAdvFilter;

        //Search format
        /* if ( SIMPLE_SEARCH_SEARCH_FORMAT_STRING != '' && SIMPLE_SEARCH_SEARCH_FORMAT_STRING != 'SIMPLE_SEARCH_SEARCH_FORMAT_STRING' )
          {
          $lines[] = new MSeparator();

          } */

        //$controls = $searchButtons->getControls();
        //define acessibilidade para cada um dos controles
        /* if ( is_array( $controls ) )
          {
          foreach ( $controls as $line => $control )
          {
          Gutil::accessibility( $control, 20 );
          }
          } */

        //$lines[] = new MDiv('divSearchFormat_', $searchButtons);

        if ( $this->options->noDefineFields )
        {
            $buttonClear = new MButton('btnClear', '<b>[ESC]</b> ' . _M('Limpar', $this->module), "javascript:gnuteca.clearForm()", GUtil::getImageTheme('clear-16x16.png'));
        }

        $btnSearch = new MButton('btnSearch', _M('BUSCAR', $this->module), ':searchFunction', GUtil::getImageTheme('search-16x16.png'));
        Gutil::accessibility($btnSearch, 20, _M('Buscar', $this->module));
        $btnSearch = new MDiv('btnSearchEx', $btnSearch);
        Gutil::accessibility($btnSearch, -1);
        $lines[] = $btnSearch;

        //div da dica de termo a ser pesquisado
        $lines[] = $divRelatedTerms = new MDiv('divRelatedTerms', '');
        $divRelatedTerms->addStyle('display', 'none');

        $args = (Object) $_REQUEST;
        $args->return = TRUE;

        //Utilizado para pesquisar pelo endereço. Desta forma dá para enviar no e-mail de aquisição o link para visualizar o material
        if ( MIOLO::_REQUEST('controlNumber', 'GET') )
        {
            $gridArgs = (Object) $_REQUEST;
            $gridArgs->return = true;
            $this->_action = '';

            $gridArgs->termCondition = Array( '=' );
            $gridArgs->termText = Array( MIOLO::_REQUEST('controlNumber', 'GET') );
            $gridArgs->termOpt = Array( '' );
            $gridArgs->termType = Array( '001.a' );

            $grid = $this->btnSearch_click($gridArgs);
        }

        $lines[] = new MDiv('divGridSimpleSearch', $grid);
        $lines[] = new MDiv('limbo', '');
        $fields[] = $lines;
        $fields[] = new MHiddenField('formContentTypeId_current');
        $fields[] = new MHiddenField('initialStatusConfirmed');

        if ( !$this->isMaterialMovement )
        {
            if ( defined('SEARCH_THEME_TOP') )
            {
                $searchThemeTop = SEARCH_THEME_TOP;
            }

            $banner = new MDiv('banner', $searchThemeTop, 'searchBanner'); //imagem de topo para pesquisa simples
            $div[] = new MDiv('searchTop', $banner, 'searchTop');
        }

        //div para os menus superiores
        if ( $this->getEvent() != 'changeFormContent' )
        {
            $div[] = new MDiv('upperMenu', $upperMenu);
        }

        //menu lateral esquerdo
        if ( $returnFieldsToAjax )
        {
            return $fields;
        }

        if ( !$isMaterialMovement && $this->getEvent() != 'changeFormContent' )
        {
            if ( $this->busAuthenticate->getUserCode() )
            {
                $args->formContentTypeId = FORM_CONTENT_TYPE_SEARCH;
                $fieldsUS = $this->getFormContentFields($args);
                unset($fieldsUS['label']);
                $fieldsUS = new MDiv('divFormContent' . $args->formContentTypeId, $fieldsUS);
                $userSearchInner = new MDiv('pesquisaPersonalizada', $fieldsUS, 'simpleSearchUserSearchInner');

                $userSearch[0] = new MDiv('userSearch', $userSearchInner, 'simpleSearchUserSearch');
            }

            $div[] = new MDiv('leftMenu', $userSearch, 'simpleSearchLeftMenu');
        }

        $div[] = $upperUserLogin;

        //não monta novidades caso for circulação de material
        if ( !$this->isMaterialMovement )
        {
            $news = $this->getNews();
            $newClass = 'newsBox';
        }

        $div[] = new MDiv('newsBox', $news, $newClass);
        $div[] = new MDiv('searchFields', $fields, 'simpleSearchFields');

        if ( !$this->isMaterialMovement )
        {
            if ( defined('SEARCH_THEME_FOOTER') )
            {
                $searchThemeFooter = SEARCH_THEME_FOOTER;
            }

            $searchFooter = new MDiv('searchFooterContent', $searchThemeFooter, 'searchFooterContent');
            $div[] = new MDiv('searchFooter', $searchFooter, 'searchFooter');
        }

        return $div;
    }

    /**
     * Obtem as novidades permitidas.
     * 
     * @return MDiv
     */
    public function getNews()
    {
        $busNews = $this->manager->getBusiness($this->module, 'BusNews');
        $busNews = new BusinessGnuteca3BusNews();
        $busNews->listforUser = true;
        $place = BusinessGnuteca3BusNews::PLACE_TYPE_SEARCH;
        $subForm = $this->getCurrentSubForm();

        //troca para o local de minha biblioteca caso for a situação
        if ( Gutil::getAjaxFunction() == 'subForm' && ($subForm != 'GoogleBook') && ($subForm != 'Z3950') && ($subForm != 'FBN') )
        {
            $place = BusinessGnuteca3BusNews::PLACE_TYPE_MY_LIBRARY;
        }

        $news = $busNews->getActiveByPlace($place);

        if ( is_array($news) )
        {
            foreach ( $news as $line => $new )
            {
                $inner = null;
                $content = new MDiv('newTitle' . $new->newsId, $new->title1, 'newTitle');
                Gutil::accessibility($content, 20);
                //tira conteúdo html, separa as palavras considerando 75 caracteres, e transforma em um array
                $explode = explode("#!@#", wordwrap(strip_tags($new->news), 75, '#!@#', true)); //#!@# é o separador
                $content = $content->generate() . $explode[0] . ' ...'; //separa somente a primeira linha
                $inner[] = new MDiv('newContent' . $new->newsId, $content, 'newContent');
                $link = new MLink('newReadMoreLini' . $new->newsId, _M('Leia mais...', $this->module), 'javascript:' . Gutil::getAjax('showNew', array( 'newsId' => $new->newsId )));
                Gutil::accessibility($link, 20, $new->title1);
                $inner[] = new MDiv('newReadMore' . $new->newsId, $link, 'newReadMore');
                $result[$line] = new MDiv('new' . $new->newsId, $inner, 'newItem');
            }

            //mostra a div, pois pode estar escondida
            $this->jsShow('newsBox');
        }
        else
        {
            //esconde a div, mas mantem ela no form para o caso de ter notícias na minha biblioteca
            $this->jsHide('newsBox');
        }

        return $result;
    }

    public static function showNew()
    {
        $MIOLO = MIOLO::getInstance();
        $newsId = MIOLO::_REQUEST('newsId');

        if ( !$newsId )
        {
            GForm::error(_M('É necessário informar um código de novidade.', $this->module));
            return;
        }

        $busNews = $MIOLO->getBusiness('gnuteca3', 'BusNews');
        $busNews = new BusinessGnuteca3BusNews();
        $busNews->listforUser = true;
        $busNews->newsIdS = $newsId;

        $news = $busNews->getActiveByPlace(array( BusinessGnuteca3BusNews::PLACE_TYPE_SEARCH, BusinessGnuteca3BusNews::PLACE_TYPE_MY_LIBRARY ));
        $news = $news[0];

        if ( !$news )
        {
            GForm::information(_M('Acesso não permitido a notícia @1.', 'gnuteca3', $newsId));
        }
        else
        {
            GForm::injectContent($news->news, true, GDate::construct($news->date)->generate() . ' - ' . $news->title1);
        }
    }

    /**
     * Ao clicar na estrela dos favoritos
     *
     * @param object $args stdclass miolo ajax object
     */
    public function btnFavorites_click($args)
    {
        if ( $this->getFrmLogin()->isAuthenticated() )
        {
            if ( is_array($args->selectgrdSimpleSearch) )
            {
                $busFavorite = $this->MIOLO->getBusiness($this->module, 'BusFavorite');

                foreach ( $args->selectgrdSimpleSearch as $line => $info )
                {
                    $data = new StdClass();
                    $data->controlNumber = $info;

                    $userId = $this->busAuthenticate->getUserCode();

                    if ( strlen($userId) > 0 )
                    {
                        $data->personId = $userId;
                    }
                    else
                    {
                        $data->personId = $this->getFormValue('uid', MIOLO::_REQUEST('uid')) ? $this->getFormValue('uid', MIOLO::_REQUEST('uid')) : $args->personId;
                    }

                    $data->entraceDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
                    $favorite = $busFavorite->getFavorite($data->personId, $data->controlNumber);

                    if ( !$favorite->personId ) //verifica se o favorito ja esta adicionado
                    {
                        $busFavorite->setData($data);
                        $busFavorite->insertFavorite();
                    }
                }

                $this->information(_M('Favoritos inseridos com sucesso!', $this->module));
            }
            else
            {
                $this->error(_M('Por favor selecione algum item para adicionar ao favoritos.', $this->module));
            }
        }
        else
        {
            $this->injectContent($this->getFrmLogin()->getLoginFields(), false, _M('Autenticar', $this->module) . ' - ' . _M('Favoritos', $this->module), '600px');
        }
    }

    /**
     * Evento ativado ao pressionar botao Enviar email
     *
     */
    public function btnMail_click($args)
    {
        if ( !($args->selectgrdSimpleSearch) ) //Nenhum item selecionado na grid
        {
            $this->error(_M('Nenhum item selecionado', $this->module));
        }
        else if ( $this->getFrmLogin()->isAuthenticated() )
        {
            if ( MIOLO::_REQUEST('action') == 'main:materialMovement' )
            {
                $this->busAuthenticate->logoff();
                $personId = $this->getFormValue('uid', MIOLO::_REQUEST('uid')) ? $this->getFormValue('uid', MIOLO::_REQUEST('uid')) : $args->uid;
            }
            else
            {
                $personId = $this->busAuthenticate->getUserCode();
            }

            $email = $this->busPerson->getPerson($personId)->email;

            if ( !$email )
            {
                $this->error(_M('E-mail da pessoa com código @1 não encontrado', $this->module, $personId));
                return;
            }

            //Tenta gerar o relatorio PDF
            $filename = $this->btnReport_click($args, TRUE);

            if ( !$filename )
            {
                $this->error(_M('Erro ao gerar relatório!', $this->module));
                return false;
            }

            $gf = new GFunction();
            $mail = new GMail();
            $mail->setContent($gf->interpret(EMAIL_SIMPLESEARCH_REPORT_CONTENT));
            $mail->setSubject(EMAIL_SIMPLESEARCH_REPORT_SUBJECT);
            $mail->setAddress($email);
            $mail->addAttachment($filename);
            if ( !$mail->send() )
            {
                $this->error(_M('Erro ao enviar e-mail', $this->module));
            }
            else
            {
                $this->information(_M('E-mail enviado com sucesso!', $this->module));
            }
        }
        else
        {
            $this->injectContent($this->getFrmLogin()->getLoginFields(), false, _M('Enviar e-mail', $this->module), '600px');
        }
    }
    /*
     * Evento ativado ao clicar no botao Gerar PDF
     *
     * @param (object) $sender
     * @param (boolean) $forSendEmail Is manually called for send report to email
     */

    public function btnReport_click($sender, $forSendEmail = FALSE)
    {
        
        //Verifica se algum item foi selecionado
        if ( $sender->selectgrdSimpleSearch )
        {
            $report = new rptSimpleSearch($sender);
            $report->folder = 'tmp';
            $ok = $report->generate(FALSE);

            //Verifica se o arquivo foi criado com sucesso
            if ( $ok && !$forSendEmail )
            {
                $url = $report->getDownloadURL();
                $this->page->onload("javascript:window.open('{$url}');");
                $this->manager->ajax->setResponse('', GForm::GDIV); //resposta nula para funcionar o onload
            }
            else
            {
                $this->error(_M('Erro ao gerar relatório', $this->module));
            }
        }
        else //Nenhum item selecionado na grid
        {
            $this->error(_M('Nenhum item selecionado', $this->module));
        }

        if ( $forSendEmail )
        {
            return BusinessGnuteca3BusFile::getAbsoluteFilePath('tmp', $report->filename);

        }
    }

    /**
     * Adicionar um campo de termo ao sistema de busca (chamada por ajax)
     *
     * @param object $args stdclass ajax do miolo
     */
    public function addTerm($args)
    {
        $this->isMaterialMovement = (MIOLO::_REQUEST('action') == 'main:materialMovement');

        $nextTermControl = $this->getTermControl() + 1;
        $this->_termControl = $nextTermControl;

        $termOpt[] = array( _M('E', $this->module), 'AND' );
        $termOpt[] = array( _M('Ou', $this->module), 'OR' );
        $termOpt[] = array( _M('Não', $this->module), 'NOT' );

        $selectedTermOpt = $args->termOpt;

        //Nao esta mais funcionando
        if ( !$selectedTermOpt )
        {
            $selectedTermOpt = _M('e', $this->module);
        }

        switch ( $args->termOpt )
        {
            case 'OR':
                $selectedTermOpt = 1; //OR
                break;
            case 'NOT':
                $selectedTermOpt = 2; //Not
                break;
            default:
                $selectedTermOpt = 0; //AND
        }

        $termOpt = new MRadioButtonGroup("termOpt[{$nextTermControl}]", null, $termOpt, $selectedTermOpt, null, 'horizontal');
        $termOpt->addAttribute('class', 'termOpt');

        $termControls = $termOpt->getControls();

        //define acessibilidade para cada um dos controles
        if ( is_array($termControls) )
        {
            foreach ( $termControls as $line => $control )
            {
                Gutil::accessibility($control, 20);
            }
        }

        $this->page->onload("dojo.byId('termOpt[{$nextTermControl}]_{$selectedTermOpt}').checked = 'checked'");

        $fields[] = $termOpt;

        $conditions['LIKE'] = _M('Contém', $this->module);
        $conditions['NOT LIKE'] = _M('Não contem', $this->module);
        $conditions['START'] = _M('Inicia com', $this->module);
        $conditions['END'] = _M('Termina com', $this->module);
        $conditions['='] = _M('Igual', $this->module);
        $conditions['<='] = _M('Menor ou igual', $this->module);
        $conditions['>='] = _M('Maior ou igual', $this->module);
        $conditions['!='] = _M('Diferente', $this->module);

        $fields['t'] = new GSelection('termType[]', !is_array($args->termType) ? $args->termType : '', null, $this->busSearchableField->listSearchableField(true), null, null, null, true);
        Gutil::accessibility($fields['t'], 20, _M('Campo a pesquisar', $this->module));
        $fields['t']->addAttribute('style', 'width:127px');
        $fields['c'] = new GSelection('termCondition[]', !is_array($args->termCondition) ? $args->termCondition : '', null, $conditions, null, null, null, true);
        Gutil::accessibility($fields['c'], 20, _M('Condição a pesquisar', $this->module));
        $fields['c']->setClass('mSelection termCondition', true);

        if ( !$this->isMaterialMovement && !MUtil::getBooleanValue(SIMPLE_SEARCH_SHOW_TERM_CONDITION) && SIMPLE_SEARCH_SHOW_TERM_CONDITION != 'SIMPLE_SEARCH_SHOW_TERM_CONDITION' )
        {
            $fields['c']->addStyle('display', 'none');
        }

        $fields['texmText'] = new MTextField('termText[]', !is_array($args->termText) ? $args->termText : '', null, FIELD_DESCRIPTION_SIZE - 2);
        Gutil::accessibility($fields['texmText'], 20, _M('Termo a pesquisar. Pressione enter para executar a busca.', $this->module));
        $fields['texmText']->setClass('mTextField mTextFieldTermText');

        $delete = new MImageButton('delete' . $nextTermControl, '', "javascript:gnutecaSearch.removeTerm($nextTermControl);", GUtil::getImageTheme('delete-16x16.png'));
        Gutil::accessibility($delete, 20, _M('Remove termo', $this->module));
        $fields[] = new MDiv('btnDeteleDiv' . $nextTermControl, $delete, 'btnDeleteTerm');
        $hctTermControl = new GContainer('termControl' . $nextTermControl, $fields);
        $hctTermControl->setClass('divTermControl');
        $this->jsSetValue('termControl', $nextTermControl);

        if ( !$args->return )
        {
            //Create future div @DEPRECATED, ver outra forma de fazer isso, javascript está longe de ser a forma ideal
            $this->page->onLoad("
            el = document.createElement('div');
            el.setAttribute('id', 'divExtraTerms" . ($nextTermControl + 1) . "');
            searchTerms = document.getElementById('searchTerms');
            searchTerms.appendChild(el);
            ");
            $this->setResponse($hctTermControl, 'divExtraTerms' . $nextTermControl);
        }
        else
        {
            return new MDiv('divExtraTerms' . $nextTermControl, $hctTermControl);
        }
    }

    /**
     * Função que é chamada ao clicar no botão btnSearch
     *
     * @param unknown_type $args
     */
    public function searchFunction($args)
    {
        //essa verificação deve ocorrer pois o nome da função é a mesma do subform
        if ( !$this->getCurrentSubForm() )
        {
            $args = GForm::corrigeEventosGrid($args);
            $this->btnSearch_click($args);
        }
    }

    /**
     * Executa a busca
     *
     * @param object $args stdclass ajax do miolo
     * @return requisição ajax
     */
    public function btnSearch_click($args)
    {
        $event = $this->getEvent();
        $module = MIOLO::getCurrentModule();
        
        $allFieldAdv = $args->advFilterControl;
        
        //Coleta todos os campos avançados presentes no formulário
        foreach($args as $afv => $_afv)
        {
            $campo = substr($afv,0, 6);
            //Adiciona o campo, caso for do tipo avançado Numérico
            if($campo == 'afNume') $arrNum[] = $afv;
            
            //Adiciona o campo, caso for do tipo avançado String
            if($campo == 'afStri') $arrStr[] = $afv;
                
            //Adiciona o campo, caso for do tipo avançado Data
            if($campo == 'afData') $arrData[] = $afv;
                
            //Adiciona o campo, caso for do tipo avançado ComboBox
            if($campo == 'afComb') $arrComb[] = $afv;
                
            //Adiciona o campo, caso for do tipo avançado Periodo
            if($campo == 'dateFr') $arrDateFr[] = $afv;
            if($campo == 'dateTo') $arrDateTo[] = $afv;
        }
        
        // Ajustar o array de operações, para terem o mesmo índice dos termos.
        $tmpArray = array();
        foreach( $args->termOpt as $opt )
        {
            $tmpArray[] = $opt;
        }
        $args->termOpt = $tmpArray;

        //forca o pagina a ser a primeira quando o usario apertou no botao btnSearch
        if ( $event == 'btnSearch_click' )
        {
            $_REQUEST['pn_page'] = 1;
        }

        //forca o pagina a ser a primeira quando o usario apertou no botao btnSearch
        if ( GUtil::getAjaxEventArgs() == '' ? true : false )
        {
            $_REQUEST['pn_page'] = 1;
            $_REQUEST['gridCount'] = null;
            $firstTime = true;
        }

        $busGenericSearch = $this->busGenericSearch;
        $fieldsList = $this->busSearchFormat->getVariablesFromSearchFormat($args->searchFormat);
        
        // seta termo exato
        $busGenericSearch->setAccurateTerm($args->accurateTerm == 1);
        
        // CASO UMA PESQUISA SEJA FEITA POR NUMEROS E SEJA UM OPERADOR... NÂO FAZ DISTINÇÂO DE PLANILHAS
        $searchForNumberControlNumber = array_search(MARC_CONTROL_NUMBER_TAG, $args->termType) !== false;
        $searchForNumberItemNumber = array_search(MARC_EXEMPLARY_ITEM_NUMBER_TAG, $args->termType) !== false;
        $searchForNumberWorkNumber = array_search(MARC_WORK_NUMBER_TAG, $args->termType) !== false;
        $searchForNUmber = ($searchForNumberControlNumber || $searchForNumberItemNumber || $searchForNumberWorkNumber) && GOperator::isLogged();

        //exclue planilhas definidas
        if ( SIMPLE_SEARCH_EXCLUDE_SPREEDSHET != 'SIMPLE_SEARCH_EXCLUDE_SPREEDSHET' && !$searchForNUmber )
        {
            $excludeSpreeshet = SIMPLE_SEARCH_EXCLUDE_SPREEDSHET;

            $excludeSpreeshet = explode("\n", $excludeSpreeshet); //tranformar em array separando por linha;

            if ( is_array($excludeSpreeshet) )
            {
                foreach ( $excludeSpreeshet as $line => $info )
                {
                    $busGenericSearch->addMaterialControlWhere("categoryLevel", $info, 'AND', '!=');
                }
            }
        }

        //estados de exemplar a ignorar
        if ( !GOperator::isLogged() && strlen(SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS) )
        {            
            $busGenericSearch->addExemplaryControlWhere("exemplaryStatusId", explode(",", SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS), "AND", "NOT IN", TRUE);
        }
        
        if ( is_array($fieldsList) )
        {
            foreach ( $fieldsList as $line => $info )
            {
                $tag = str_replace('$', '', $info);
                $busGenericSearch->addSearchTagField($tag);
            }
        }

        if ( is_array($args->termText) )
        {
            $relatedTerms = array( );

            foreach ( $args->termText as $line => $info )
            {
                //se for array de condições
                $termType = trim($args->termType[$line]);

                if ( $info && $termType )
                {
                    $operation = $args->termOpt[$line];
                    $busGenericSearch->addMaterialWhereByTag($termType, array( $info ), $operation, $args->termCondition[$line]);
                    $ok = true;

                    //obtém as dicas de pesquisa utilizando os termos relacionados do dicionário
                    if ( SIMPLE_SEARCH_SHOW_RELATED_TERMS == DB_TRUE )
                    {
                        $this->jsSetInner('divRelatedTerms', ''); //limpa a dica
                        //lógica para obter as tags do filtro
                        $terms = array( );

                        if ( strpos($args->termType[$line], ',') )
                        {
                            $terms = explode(',', $args->termType[$line]); //obtém as tags quebrando a string por ","
                        }
                        else
                        {
                            $terms[] = $args->termType[$line]; //obtém a tag atribuindo array de termos
                        }

                        $tags = array( );

                        //obtém as tags
                        foreach ( $terms as $k => $tag )
                        {
                            //verifica se possui tags do tipo "245.a+245."
                            if ( strpos($tag, '+') )
                            {
                                $inter = explode('+', $tag); //quebra novamente por "+"

                                foreach ( $inter as $l => $tag2 )
                                {
                                    $tags[] = $tag2; //adiciona conteúdo no array de tags
                                }
                            }
                            else
                            {
                                $tags[] = $tag; //adiciona conteúdo no array de tags
                            }
                        }

                        //só procura termo relacionado quando termo pesquisdo for maior que 3
                        if ( strlen($info) > 3 )
                        {
                            $relatedTerms[$line] = $this->busDictionary->getRelatedTerms($tags, $info); //obtém termo relacionado das determinadas tags do filtro
                        }
                    }
                }
                else if ( $info )  //se vier expressão
                {
                    $exp = $this->busSearchableField->parseExpression($info);
                    $busGenericSearch->addMaterialWhereByExpression($exp, TRUE);
                }
            }

            $values = '';

            //obtém campos pesquisáveis
            if ( $relatedTerms )
            {
                $links = array( );
                $auxValues = array( );

                foreach ( $relatedTerms as $line => $related )
                {
                    $related = is_array($related) ? $related : array( $related );
                    
                    foreach ( $related as $i => $value )
                    {
                        //controla valores para não repetir sugestão
                        if ( !in_array($value[0], $auxValues) && strlen($value[0]) > 0 )
                        {
                            $link = new MLink('link' . $i . $related->info, $value[0], "javascript:dojo.query('.mTextFieldTermText')[{$line}].value='{$value[0]}'; dojo.byId('btnSearch').click();");
                            $link->setClass('linkRelatedTerms');
                            $links[] = $link->generate();
                            $auxValues[] = $value[0];
                        }
                    }
                }

                //percorre todos itens relacionados e monta a frase de dica
                foreach ( $links as $i => $link )
                {
                    //adiciona "ou" no último termo relacioanado
                    if ( (count($links) > 1) && ($i == (count($links) - 1)) )
                    {
                        $values .= ' ' . _M('ou', $module) . $link;
                    }
                    else //adiciona "," após o termo relacionado
                    {
                        $values .= ', ' . $link;
                    }
                }
            }

            //verifica se existe termo relacionado a palavra pesquisada
            if ( strlen($values) > 0 )
            {
                $this->injectRelatedTerm(substr($values, 2)); //injeta os valores na div de termos relacionados
            }
            else
            {
                $this->jsHide('divRelatedTerms');
            }
            
            //Aqui vai adicionar os campos avançados
            if ( $exp || $ok )
            {
                //se tiver uma unidade selecionada
                if ( $args->libraryUnitId )
                {
                    //$busGenericSearch->filterLibraryUnit($args->libraryUnitId);

                    if ( !ereg(",", $args->libraryUnitId) )
                    {
                        $args->libraryUnitId = is_array($args->libraryUnitId) ? $args->libraryUnitId : array( $args->libraryUnitId );
                        $args->libraryUnitId = implode(",", $args->libraryUnitId);
                    }
                    $busGenericSearch->addExemplaryControlWhere(array( 'libraryUnitId', 'originalLibraryUnitId' ), $args->libraryUnitId, "AND", "IN", TRUE);
                }
                
                /*
                 * Adiciona o campo avançado numérico na pesquisa
                 */
                if(is_array($arrNum))
                {
                    foreach($arrNum as $vNumericValor)
                    {
                        //Obtem o id do campo adicionado
                        $idNumeric = substr($vNumericValor, 9);
                        //Pesquisa para obter o valor da tag desse campo
                        $thisField = $this->busSearchableField->getSearchableField($idNumeric);
                        //Adiciona as tags para variável a ser passada para o addMaterialWhereByTag
                        $tagsNumeric = $thisField->field;
                        
                        //Adiicona na busca
                        $busGenericSearch->addMaterialWhereByTag($tagsNumeric, $args->$vNumericValor);
                    }
                }
                
                /*
                 * Adicionando campo avançado String no campo de busca
                 */
                if(is_array($arrStr))
                {
                    foreach($arrStr as $vStringValor)
                    {
                        //Obtem o id do campo adicionado
                        $idString = substr($vStringValor, 8);
                        //Pesquisa para obter o valor da tag desse campo
                        $thisField = $this->busSearchableField->getSearchableField($idString);
                        //Adiciona as tags para variável a ser passada para o addMaterialWhereByTag
                        $tagsString = $thisField->field;
                        
                        //Adiicona na busca
                        $busGenericSearch->addMaterialWhereByTag($tagsString, $args->$vStringValor);
                    }
                }
                
                /*
                 * Adicionando campo avançado Data no campo de busca
                 */
                if(is_array($arrData))
                {
                    foreach($arrData as $vDataValor)
                    {
                        //Obtem o id do campo adicionado
                        $idData = substr($vDataValor, 6);
                        //Pesquisa para obter o valor da tag desse campo
                        $thisField = $this->busSearchableField->getSearchableField($idData);
                        //Adiciona as tags para variável a ser passada para o addMaterialWhereByTag
                        $tagsData = $thisField->field;
                        
                        //Adiicona na busca
                        $busGenericSearch->addMaterialWhereByTag($tagsData, $args->$vDataValor);
                    }
                }
                
                /*
                 * Adicionando campo avançado ComboBox no campo de busca
                 */
                if(is_array($arrComb))
                {
                    foreach($arrComb as $vComboValor)
                    {
                        //Obtem o id do campo adicionado
                        $idCombo = substr($vComboValor, 10);
                        //Pesquisa para obter o valor da tag desse campo
                        $thisField = $this->busSearchableField->getSearchableField($idCombo);
                        //Adiciona as tags para variável a ser passada para o addMaterialWhereByTag
                        $tagsCombo = $thisField->field;
                        
                        //Adiicona na busca
                        $busGenericSearch->addMaterialWhereByTag($tagsCombo, $args->$vComboValor);
                    }
                }
                
                /*
                 * Trabalha o filtro de período
                 */
                if(is_array($arrDateFr) || is_array($arrDateTo))
                {
                    $posicao = 0;
                    
                    foreach($arrDateFr as $vDateFrom)
                    {
                        //Obtem o id do campo adicionado
                        $idDateFr = substr($vDateFrom, 8);
                        //Pesquisa para obter o valor da tag desse campo
                        $thisField = $this->busSearchableField->getSearchableField($idDateFr);
                        
                        //Adiciona as tags para variável a ser passada para o addMaterialWhereByTag
                        $tagsDateFr = $thisField->field;
                        
                        $dTo = $arrDateTo[$posicao];
                        $arrDatePeriod = array($args->$vDateFrom, $args->$dTo);
                        
                        //Adiicona na busca
                        $busGenericSearch->addMaterialWhereByTag($tagsDateFr, $arrDatePeriod, 'AND', 'BETWEEN');
                        
                        $posicao++;
                    }
                }

                if ( $args->materialTypeId )
                {
                    $busGenericSearch->setMaterialTypeId($args->materialTypeId);
                }

                if ( $args->exemplaryStatusId )
                {
                    $busGenericSearch->addExemplaryControlWhere('exemplaryStatusId', $args->exemplaryStatusId);
                }

                //Pega a ordenacao padrao do sistema
                if ( !($args->orderType && $args->orderField) && (SIMPLE_SEARCH_DEFAULT_ORDER != 'SIMPLE_SEARCH_DEFAULT_ORDER') )
                {
                    list($field, $type) = explode(',', SIMPLE_SEARCH_DEFAULT_ORDER);

                    //Defaults
                    if ( !$field )
                    {
                        $field = 1;
                    }
                    if ( !$type )
                    {
                        $type = 'ASC';
                    }

                    $types = array(
                        'ASC' => SORT_ASC,
                        'DESC' => SORT_DESC
                    );

                    $args->orderField = $field;
                    $args->orderType = $types[$type];
                }

                if ( $args->orderType && $args->orderField )
                {
                    $orderFields = $this->busSearchableField->getDetaisForOrder($args->orderField);
                    $orderField = explode(',', $orderFields->field);   // separa por virgula, caso tenha vários campos

                    foreach ( $orderField as $f )
                    {
                        $fieldsX = explode("+", $f);

                        foreach ( $fieldsX as $f )
                        {
                            list($fi, $su) = explode('.', $f);        // separa field e subfield
                            $fieldType = is_null($orderFields->fieldType) ? $orderFields->fieldType : SORT_STRING;
                            $busGenericSearch->setOrder($fi, $su, $args->orderType, $fieldType);
                        }
                    }
                }

                //trabalha o filtro de ordem
                if ( $args->editionYearFrom || $args->editionYearTo )
                {
                    $args->editionYearFrom = strlen($args->editionYearFrom) ? $args->editionYearFrom : "0";
                    $args->editionYearTo = strlen($args->editionYearTo) ? $args->editionYearTo : date("Y");
                    $busGenericSearch->addMaterialWhereByTag(MARC_PUBLICATION_DATE_TAG, array( $args->editionYearFrom, $args->editionYearTo ), 'AND', 'NUMERIC BETWEEN');
                }

                //trabalha o filtro de aquisição pelos exemplares.
                if ( ( $args->aquisitionFrom) && $args->aquisitionTo )
                {
                    $busGenericSearch->addMaterialWhere( '949', 'y', array($args->aquisitionFrom , $args->aquisitionTo), 'AND', 'BETWEEN');
                }
                else if ( $args->aquisitionFrom )
                {
                    $busGenericSearch->addMaterialWhere( '949', 'y', array( $args->aquisitionFrom ), 'AND', '>=');
                }
                else if ( $args->aquisitionTo )
                {
                    $busGenericSearch->addMaterialWhere( '949', 'y', array( $args->aquisitionTo ), 'AND', '<=');
                }

                //trabalha o filtro por letras
                if ( $args->letter )
                {
                    if ( $args->letter != '#' )
                    {
                        $busGenericSearch->addMaterialWhereByTag($args->letterField, array( $args->letter ), 'AND', 'START');
                    }
                    else
                    {
                        $busGenericSearch->addMaterialWhereByTag($args->letterField, array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ), 'AND', 'START');
                    }
                }

                $data = $busGenericSearch->getWorkSearch(null, $firstTime);

                if ( $busGenericSearch->getCount() )
                {
                    $count = $busGenericSearch->getCount();
                }
                else
                {
                    $count = $_REQUEST['gridCount'];
                }
            }
        }

        $grid = $this->MIOLO->getUi()->getGrid($this->module, 'GrdSimpleSearch');
        $grid->setCount($count);
        $grid->setCSV(FALSE);
        $grid->setData($data);

        $fields[] = $grid;

        //caso analycst esteja ligado e for a primeira página
        if ( ANALYCTS_LOGLEVEL_OUTER > 0 && MIOLO::_REQUEST('pn_page') == 1 )
        {
            $termOpt = MIOLO::_REQUEST('termOpt');
            $termOpt = $termOpt[0];

            $termOpt = $termOpt ? $termOpt : 'and';
            $term = implode(' ' . $termOpt . ' ', MIOLO::_REQUEST('termText'));
            $type = implode(' ' . $termOpt . ' ', MIOLO::_REQUEST('termType'));

            global $startTime;
            $endTime = microtime(true); //obtém o tempo em que terminou a requisição

            $busAnalycts = $this->MIOLO->getBusiness($this->module, 'BusAnalytics');
            $busAnalycts->accessType = BusinessGnuteca3BusAnalytics::ACCESS_TYPE_SEARCH_CONTENT;
            $busAnalycts->logLevel = ANALYCTS_LOGLEVEL_OUTER;
            $busAnalycts->menu = _M('Termo pesquisado', 'gnuteca3');
            $busAnalycts->event = $term;
            $busAnalycts->action = $type;
            $busAnalycts->timeSpent = $endTime - $startTime;

            $busAnalycts->insertAnalytics();
        }

        if ( $args->return && $this->_action != 'main:search:simpleSearch' )
        {
            return $fields;
        }
        else
        {
            if ( $grid->getData() )
            {
                $this->setResponse($fields, 'divGridSimpleSearch');
            }
            else
            {
                $this->setResponse(null, 'divGridSimpleSearch');
                $this->information(_M('Registros não encontrados!', $this->module));
            }
        }
    }

    /**
     * Limpar a busca (ainda não implementado)
     *
     * @param object $args
     */
    public function BtnClean_click($args)
    {
        $this->setResponse('clean', 'divGridSimpleSearch');
    }

    public function showCover($args)
    {
        //FIXME isso deve ser gerenciados diretamente pelo GMaterialDetail
        $this->injectContent(new GMaterialDetail(MIOLO::_REQUEST('coverControlNumber'), null, null, 'cover', 'cover'), true, _M('Capa', $this->module));
    }

    /**
     * Get fields of user form content
     *
     * @param unknown_type $args
     * @return unknown
     */
    public function getFormContentFields($args)
    {
        if ( !$args->formContentTypeId )
        {
            return;
        }
        $this->busFormContent = $this->MIOLO->getBusiness($this->module, 'BusFormContent'); //Clear data
        $this->busFormContent->formS = 'frmsimplesearch';

        if ( $args->formContentTypeId == FORM_CONTENT_TYPE_ADMINISTRATOR )
        {
            $lbl = _M('Pesquisas do admin', $this->module);
            $this->busFormContent->operatorS = '';
            $this->busFormContent->formContentTypeS = FORM_CONTENT_TYPE_ADMINISTRATOR;
        }
        else if ( $args->formContentTypeId == FORM_CONTENT_TYPE_OPERATOR )
        {
            $lbl = _M('Pesquisas de operador', $this->module);
            $this->busFormContent->operatorS = GOperator::getOperatorId();
            $this->busFormContent->formContentTypeS = FORM_CONTENT_TYPE_OPERATOR;
        }
        else if ( $args->formContentTypeId == FORM_CONTENT_TYPE_SEARCH )
        {
            $this->busFormContent->operatorS = $this->busAuthenticate->getUserCode();
            $this->busFormContent->formContentTypeS = FORM_CONTENT_TYPE_SEARCH;
        }

        $lbl = new MLabel($lbl);
        $lbl->setBold(TRUE);

        $search = $this->busFormContent->searchFormContent(TRUE);

        if ( $search )
        {
            foreach ( $search as $v )
            {
                $userContent[] = array( $v->formContentId, $v->name );
            }
        }

        $fields['label'] = $lbl;
        $value = MIOLO::_REQUEST('formContent' . $args->formContentTypeId);
        $formContent = new GSelection('formContent' . $args->formContentTypeId, $value, null, $userContent, null, null, null, false);
        $js = "dojo.byId('termControl').value = ''; document.getElementById('formContentTypeId_current').value = '{$args->formContentTypeId}'";
        $formContent->addAttribute('onchange', "{$js}; " . GUtil::getAjax('changeFormContent'));

        $fields['formContent'] = $formContent;

        if ( $args->formContentTypeId != FORM_CONTENT_TYPE_SEARCH )
        {
            $addFormContent = new MImageButton('addFormcontent', NULL, "{$js}; " . GUtil::getAjax('addFormContent'), GUtil::getImageTheme('add-16x16.png'));
            $addFormContent->addAttribute('title', _M('Adicionar', $this->module));
            $fields['add'] = $addFormContent;
        }

        if ( $userContent )
        {
            $deleteFormContent = new MImageButton('deleteFormcontent', NULL, "{$js}; " . GUtil::getAjax('deleteFormContent'), GUtil::getImageTheme('delete-16x16.png'));
            $deleteFormContent->addAttribute('title', _M('Apagar', $this->module));
            $deleteFormContent->addAttribute('class', 'btnDelete');
            $fields['delete'] = $deleteFormContent;

            $saveFormContent = new MImageButton('saveFormcontent', NULL, "{$js}; " . GUtil::getAjax('saveFormContent'), GUtil::getImageTheme('save-16x16.png'));
            $saveFormContent->addAttribute('title', _M('Salvar', $this->module));
            $fields['save'] = $saveFormContent;
        }

        if ( $args->formContentTypeId == FORM_CONTENT_TYPE_SEARCH )
        {
            $addFormContent = new MDiv('addFormcontent', _M('Adicionar esta pesquisa'), 'addFormContenAddButton', array( 'onclick' => "{$js};" . GUtil::getAjax('addFormContent') ));
            $addFormContent->addAttribute('title', _M('Adicionar', $this->module));
            $fields['add'] = $addFormContent;
        }

        return $fields;
    }

    public function reloadFormContent($args)
    {
        $args->formContentTypeId = $args->formContentTypeId_current;
        $formContent = $this->getFormContentFields($args);

        if ( $args->formContentTypeId != 1 ) //Se não for pesquisa do administrador
        {
            unset($formContent['label']); //Tira o label porque ele estou o layout
        }

        $this->setResponse($formContent, 'divFormContent' . $args->formContentTypeId);
    }

    public function getFormContentId()
    {
        $string = 'formContent' . $this->getFormContentTypeId();
        $id = MIOLO::_REQUEST($string) ? MIOLO::_REQUEST($string) : MIOLO::_REQUEST('formContentId');
        $id = $id ? $id : 1; //segurança padrão
        return $id;
    }

    public function getFormContentTypeId()
    {
        $id = MIOLO::_REQUEST('formContentTypeId_current') ? MIOLO::_REQUEST('formContentTypeId_current') : MIOLO::_REQUEST('formContentTypeId');
        $id = $id ? $id : 1; //segurança padrão
        return $id;
    }

    /**
     * Add user defined form content
     *
     * @param unknown_type $args
     */
    public function addFormContent($args)
    {
        $this->setFocus('formContentName');
        $fields[] = new MLabel(_M('Nome', $this->module) . ':');
        $fields[] = new MTextField('formContentName', null, null, FIELD_DESCRIPTION_SIZE);
        $btnSave = new MButton('btnOk', _M('Salvar', $this->module), ':addFormContent_click', GUtil::getImageTheme('accept-16x16.png'));
        $fields[] = new MDiv('hctbuttons', array( $btnSave, GForm::getCloseButton() ));

        $this->injectContent($fields, false, _M('Conteúdo do formulário', $this->module));
    }

    /**
     * Event called when button Send is clicked
     *
     */
    public function addFormContent_click($args)
    {
        if ( !$args->formContentName )
        {
            $this->error(_M('O campo "@1" é necessário.', $this->module, 'Name'), GUtil::getCloseAction(), null, false);
            return;
        }

        if ( $args->formContentTypeId == FORM_CONTENT_TYPE_SEARCH )
        {
            $this->busFormContent->operatorS = $this->busAuthenticate->getUserCode();
        }
        else
        {
            $this->busFormContent->operatorS = GOperator::getOperatorId();
        }

        //Verificar se o usuário já possui uma pesquisa com este nome
        $this->busFormContent->nameS = $args->formContentName;
        $this->busFormContent->formContentTypeS = $this->getFormContentTypeId();
        $result = $this->busFormContent->searchFormContent();

        if ( $result[0][0] )
        {
            $this->error(_M('Um registro com este mesmo nome foi encontrado na base, utilize um nome diferente.', $this->module), GUtil::getCloseAction(), null, false);
            return;
        }

        //Save data on db
        $args->name = $args->formContentName;

        $this->btnSaveFormContent_click($args);
        $this->reloadFormContent($args);
    }

    public function deleteFormContent($args)
    {
        $type = $args->formContentTypeId_current; //tipo de pesquisa (usuário, admin ou operador)
        $key = "formContent{$type}"; //pesquisa selecionada
        //testa se usuário selecionou alguma pesquisa
        if ( strlen($args->$key) == 0 )
        {
            $this->information(_M('É necessário selecionar um pesquisa', $this->module));
            return;
        }

        $this->question(_M('Confirmar remoção', $this->module), "javascript:" . GUtil::getAjax('deleteFormContent_click'));
    }

    public function deleteFormContent_click($args)
    {
        //Não permite excluir Pesquisa Simples do Form Content do Administrador
        if ( ($args->formContent1 == 1) && ($args->formContentTypeId_current == 1) )
        {
            $this->error(_M('Pesquisa simples não pode ser excluída', $this->module));
        }
        else //para os outros casos permite excluir
        {
            $ok = $this->busFormContent->deleteFormContent($this->getFormContentId());

            if ( $ok )
            {
                $this->information(_M('Conteúdo do formulário removido com sucesso', $this->module));
            }
            else
            {
                $this->error(_M('Erro ao excluir o conteúdo do formulário', $this->module));
            }
        }

        $this->reloadFormContent($args);
    }

    /**
     * Update form content
     *
     * @param unknown_type $args
     */
    public function saveFormContent($args)
    {
        $formContentId = $this->getFormContentId();

        if ( !$formContentId )
        {
            $this->error(_M('Sem conteúdo de formulário para salvar', $this->module));
            return;
        }

        //Save data on db
        $args->name = $this->busFormContent->getFormContent($formContentId)->name;
        $args->doUpdate = TRUE;
        $this->btnSaveFormContent_click($args);
    }

    /**
     * Este metodo monta a tela de requisição de alteração de estado de exemplar
     * Congelamento
     *
     * @param object $argstrue
     */
    public function gridRequestChangeExemplaryStatus($args)
    {
        $this->MIOLO->getClass('gnuteca3', 'controls/GRequestChangeExemplaryStatus');
        $congelado = new GRequestChangeExemplaryStatus();
        $congelado->mainFields($args);
    }

    /**
     * Metodo que finaliza a requisição de alteração de estado de material
     *
     * @param obj $args
     */
    public function finalizeRequest($args)
    {
        $this->MIOLO->getClass('gnuteca3', 'controls/GRequestChangeExemplaryStatus');
        $congelado = new GRequestChangeExemplaryStatus();
        $congelado->finalize($args);
    }

    /**
     * Check if exemplaryStatus is not on ignore list
     *
     * @param array $exemplarys
     * @return array $exemplarys parsed
     */
    public static function checkExemplarysInclude($exemplarys = null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('gnuteca3', 'controls/GMaterialDetail');
        if ( $exemplarys )
        {
            foreach ( $exemplarys as $libraryUnitId => $exemplaryLibrary )
            {
                if ( !$exemplaryLibrary )
                {
                    continue;
                }
                foreach ( $exemplaryLibrary as $exemplaryStatusId => $exemplaryStatus )
                {
                    if ( !GMaterialDetail::checkDisplayExemplary($exemplaryStatusId) )
                    {
                        unset($exemplarys[$libraryUnitId][$exemplaryStatusId]);
                    }
                }
            }
            $null = true;
            foreach ( $exemplarys as $e )
            {
                foreach ( $e as $_e )
                {
                    if ( $_e )
                    {
                        $null = false;
                    }
                }
            }
            if ( $null )
            {
                $exemplarys = null;
            }
        }

        return $exemplarys;
    }

    /**
     * Esta função é chamada add clicar no botão addAdvFilters (+)
     *
     * Ela busca os dados do GSelection advancedFilters e pega o valor selecionado,
     * de acordo com isto ela chama uma função php deste mesmo form.
     *
     * Então se quiseres incluir uma opção a mais no select, inclua-a no array $advancedFilters
     * e crie uma função neste form com o mesmo no da chava (key) do array
     *
     * @param stdclass $args the miolo ajax stdclass object
     */
    public function addAdvFilter($args)
    {
        $function = $args->advancedFilters;
        
        //Obtem, caso for númerico, o id do campo avançado
        $fieldAdvId = $args->advancedFilters;
        
        //Verfifica se é um campo avançado nativo, ou se foi adicionado
        if(is_numeric($function))
        {
            //Define qual é o tipo do campo avançado
            $variable = $this->busSearchableField->getSearchableField($function);
            $function = defineFunction($variable->fieldType);
        }
        
        if ($function && function_exists($function) )
        {
            $result = $function($args);
            //para acessibilidade
            
            if ( is_array($result) )
            {
                foreach ( $result as $line => $field )
                {
                    if ( !$field instanceof MLabel && $field->name )
                    {
                        Gutil::accessibility($field, 20, $field->label);
                    }
                }
                
                $id = $args->advFilterControl + 1;
                $img = new MImageButton('delete' . $this->getTermControl() + 1, '', "javascript:gnutecaSearch.hideElement($id);", GUtil::getImageTheme('delete-16x16.png'));
                Gutil::accessibility($img, 20, _M('Remover filtro avançado', $this->module));
                $result[35] = new MDiv('btnDeleteDiv' . $id, $img, 'btnDelete');
                $this->jsShow('advFilterContainer');
                $this->jsSetValue('advFilterControl', ($args->advFilterControl + 1));
            }
        }
        else
        {
            GForm::error(_M('Por favor selecione um filtro avançado.', 'gnuteca3'));
        }

        $fields[0] = new GContainer('advFilterControl' . ($args->advFilterControl + 1), $result);
        $fields[0]->addAttribute('class', 'advFilterControlContainer');
        $fields[1] = new MDiv('advFilter' . ($args->advFilterControl + 1), '');
        $this->jsShow('advFilterContainer');

        if ( $args->return == true )
        {
            return $fields;
        }
        else if ( $result )
        {
            $this->setResponse($fields, 'advFilter' . $args->advFilterControl);
        }
        else
        {
            $this->setResponse('', 'divResponse');
        }
    }

    /**
     * Função que salva configurações de tela do operador
     *
     * @param object $args
     */
    public function btnSaveFormContent_click($args)
    {
        if ( (GOperator::isLogged()) || ($this->busAuthenticate->getUserCode()) )
        {
            $this->busFormContent = new BusinessGnuteca3BusFormContent();
            $data = array( );
            $data['libraryUnitId'] = $args->libraryUnitId1;
            $data['materialTypeId'] = $args->materialTypeId;
            $data['termType[]'] = $args->termType[0];
            $data['termText[]'] = $args->termText[0];
            $data['termCondition[]'] = $args->termCondition[0];
            $data['termType'] = var_export($args->termType, 1);
            $data['termText'] = var_export($args->termText, 1);
            $data['termCondition'] = var_export($args->termCondition, 1);
            $data['termOpt'] = var_export($args->termOpt, 1);
            $data['showAdvSearch'] = $args->showAdvSearch;
            $data['exemplaryStatusId'] = $args->exemplaryStatusId;
            $data['aquisitionFrom'] = $args->aquisitionFrom;
            $data['aquisitionTo'] = $args->aquisitionTo;
            $data['searchFormat'] = $args->searchFormat;

            if ( isset($args->accurateTerm) )
            {
                $accurateTerm = 1;
            }

            if ( isset($args->letter) && $args->letter == '' )
            {
                $args->letter = " ";
            }

            if ( isset($args->editionYearFrom) && $args->editionYearFrom == '' )
            {
                $args->editionYearFrom = " ";
            }

            if ( isset($args->limit) && $args->limit == '' )
            {
                $args->limit = " ";
            }
            $data['accurateTerm'] = $accurateTerm;
            $data['editionYearFrom'] = $args->editionYearFrom;
            $data['editionYearTo'] = $args->editionYearTo;
            $data['limit'] = $args->limit;
            $data['orderType'] = $args->orderType;
            $data['orderField'] = $args->orderField;
            $data['letter'] = $args->letter;
            $data['letterField'] = $args->letterField;

            //Save data
            $this->busFormContent->name = $args->name;
            $this->busFormContent->doUpdate = $args->doUpdate;
            $this->busFormContent->formContentId = $this->getFormContentId();
            $this->busFormContent->formContentType = $this->getFormContentTypeId();

            $data = (object) $data;

            $this->busFormContent->saveFormValues('frmsimplesearch', $data);
            $this->information(_M('Dados salvos com sucesso.', $this->module));
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Função usada na circulação de material, aparecem alguns botões extras de acordo com a situação
     *
     * Neste caso botão de reserva. Lista as reservas em ordem para um controlNumber
     *
     * @param $args
     * @return ajax response
     */
    public function onkeydown117_120($args)
    {
        //Só mostrar botão se estiver acessando pelo Circulação de material e se estiver listando uma única obra
        if ( $args->action == 'main:materialMovement' && $controlNumber = $args->uniqueItemNumber )
        {
            $fields = new GMaterialDetail($controlNumber, null, null, 'tabReserve', 'reserve', $args);

            // se retornou a tabela é porque tem dados
            if ( $fields->inner[0]->inner[0] instanceof MTableRaw )
            {
                $this->injectContent($fields, false, _M('Reserva', $this->module), '90%');
            }
            //caso contrário retornar mensagem dizendo que não existe reserva
            else
            {
                $this->information(_M('Não há reserva para esta obra.', $this->module), null, null, true);
            }
        }
        else
        {
            //caso não esteja na circulação de material, resposta sem nada pra evitar erros
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Esta função foi criada para que ao pressionar F5 na pesquisa simples o
     * evento do gnuteca seja chamado não fazendo nada.
     * Esta medida se fez necessária porque o FrmSimpleSearch é extendida na Circulação de materiais
     * 
     * @param type $args 
     */
    public function onkeydown116($args)
    {
        $this->setResponse("", 'limbo');
    }

    /**
     * Função usada na circulação de material, aparecem alguns botões extras de acordo com a situação
     *
     * Lista os empréstimos para a obra.
     *
     * @param $args
     * @return ajax response
     */
    public function onkeydown116_120($args)
    {
        //Só mostrar botão se estiver acessando pelo Circulação de material e se estiver listando uma única obra
        if ( $args->action == 'main:materialMovement' && $controlNumber = $args->uniqueItemNumber )
        {
            $fields = new GMaterialDetail($controlNumber, null, null, 'tabLoan', 'loan');

            if ( $fields->inner[0]->inner[0] instanceof MTableRaw )
            {
                $this->injectContent($fields, false, _M('Empréstimo', $this->module), '90%');
            }
            else
            {
                $this->information(_M('Não há empréstimo em aberto para esta obra.', $this->module), null, null, true);
            }
        }
        else
        {
            //resposta sem nada pra evitar erros
            $this->setResponse('', 'limbo');
        }
    }

    public function getTermControl()
    {
        $tc = MIOLO::_REQUEST('termControl');

        if ( $tc )
        {
            return $tc;
        }

        return $this->_termControl;
    }

    /**
     * Gerencia carregamento de subformulários.
     * 
     * @param string $subForm nome do subformulário
     * @param boolean $return se deve ou não retornar os campos
     * @return array array de campos caso return seja true
     */
    public function subForm($subForm, $return = false)
    {
        $args = (Object) $_REQUEST;
        $this->manager->getBusiness('gnuteca3', 'BusAuthenticate');
        $frmLogin = $this->getFrmLogin();

        $args = GUtil::decodeJsArgs($subForm);

        if ( !is_string($args) )
        {
            $subForm = $args->subForm;
        }

        $frm = 'Frm' . $subForm; //nome do SubFormulário
        $this->MIOLO->getClass('gnuteca3', "subform/{$frm}");

        $isUserLoginNeeded = GSubForm::isUserLoginNeeded($frm);

        if ( !$frmLogin->isAuthenticated() && $isUserLoginNeeded )
        {
            $frmLogin->errors = null;

            if ( $return )
            {
                return $frmLogin->getLoginFields();
            }
            else
            {
                $_REQUEST['loginType'] = LOGIN_TYPE_USER_AJAX; //força login por ajax
                $frmLogin = $frmLogin->getLoginFields();

                //força não mostrar erros na primeira visualização
                if ( !MIOLO::_REQUEST('uid') )
                {
                    $frmLogin->errors = null;
                }

                $this->injectContent($frmLogin, false, _M('Autenticar', 'gnuteca3'), '600px');
            }
        }
        else if ( $subForm )
        {
            $form = new $frm();
            $controls = $form->getFields();
            $this->setCurrentSubForm($subForm);
            $this->MIOLO->page->onload("var leftMenu = dojo.byId('leftMenu'); if ( leftMenu ) { leftMenu.style.display='none';} ; var searchPanel = dojo.byId('searchPanel'); if ( searchPanel) {searchPanel.className = searchPanel.className.replace('extraClass',''); }");

            //só define os controls caso existam, isso facilita o uso de $this->error, information e question dentro do subForm
            if ( $controls )
            {
                if ( $return )
                {
                    return $controls;
                }
                else
                {
                    //caso não tenha foco definido defini no título do subform
                    if ( !$this->getFocus() )
                    {
                        $this->setFocus('subFormTitle');
                    }

                    $this->setResponse($controls, 'searchFields');
                }
            }

            //essa variável só existe se tiver acabado de logar
            if ( MIOLO::_REQUEST('pwd') )
            {
                $this->page->onload(GUtil::getCloseAction()); //fecha diálogo
                $this->setResponse($this->getLoggedFields(), 'upperUserLogin'); //atualiza caixa com dados do usuário
            }

            //atualiza notícias quando estive montando formulário
            if ( $form->firstAccess() )
            {
                $this->setResponse($this->getNews(), 'newsBox');
            }
        }
    }

    /**
     * Define o subForm atual
     *
     * @param string $subForm
     */
    public function setCurrentSubForm($subForm)
    {
        $_SESSION['currentSearchSubForm'] = $subForm;
    }

    /**
     * Obtem nome do subform atual
     * 
     * @return string
     */
    public function getCurrentSubForm()
    {
        return $_SESSION['currentSearchSubForm'];
    }

    /**
     * A pesquisa é um formulário público, sempre retorna com acesso
     *
     * @return true;
     */
    public function checkAccess()
    {
        return true;
    }

    /**
     * Exporta registros selecionados em arquivo ISO 2709
     * @param stdClass $args 
     */
    public function btnExportISO2709_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('gnuteca3', 'gIso2709Export');
        $controlNumbers = array( );

        //caso tenha números de controles selecionados, realiza a exportação
        if ( is_array($args->selectgrdSimpleSearch) )
        {
            $object = new gIso2709Export($args->selectgrdSimpleSearch);
            $content = $object->execute();

            $folder = 'tmp';
            $file = 'gnuteca_' . GDate::now()->getTimestampUnix() . '.iso';

            file_put_contents(BusinessGnuteca3BusFile::getAbsoluteServerPath(true) . '/' . $folder . '/' . $file, $content);
            BusinessGnuteca3BusFile::openDownload($folder, $file);
        }
        else
        {
            $this->information(_M('Nenhum item selecionado', 'gnuteca3'));
        }

        $this->setResponse(null, 'limbo');
    }

    /**
     * Injeta o conteúdo do termo relativo a busca atual
     *
     * @param array $term o termo sugestionado por campo
     */
    public function injectRelatedTerm($terms)
    {
        $module = MIOLO::getCurrentModule();

        $string = _M('Você quis dizer: <b>@1</b>', $module, $terms); //string que aparecerá na dica

        $this->jsShow('divRelatedTerms'); //mostra a div
        $this->jsSetInner('divRelatedTerms', $string); //seta o conteúdo
        $this->setResponse(null, 'limbo');
    }
}

?>