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
 * Class GForm, extends the default MForm,
 * including default form configuration and some usefull functions.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/11/2008
 *
 **/
session_start();
$MIOLO->getClass('gnuteca3', 'GUtil');
$MIOLO->getClass('gnuteca3', 'controls/GGridActionSelect');

//coloca os argumentos ajax no $_REQUEST para ser pego pelo MIOLO::_REQUEST
if ( GUtil::getAjaxEventArgs() )
{
    $ajaxArgs =  ( array ) GUtil::decodeJsArgs(GUtil::getAjaxEventArgs());

    if ( is_array( $ajaxArgs ))
    {
        $_REQUEST = array_merge( $_REQUEST, $ajaxArgs );
    }
}

class GForm extends MForm
{
	const GDIV = 'divForm';
	const DIV_SEARCH = 'divSearch';

    /**
     * Atalho para framework
     * @var MIOLO
     */
	public $MIOLO;
    /**
     * Módulo da aplicação 'gnuteca3';
     * @var string
     */
	public $module;
	public $_action;
	public $function;
	public $event;
    /**
     * @deprecated
     * @var MUi
     */
	public $ui;
	public $searchFunction;
	public $getFunction;
	public $deleteFunction;
	public $updateFunction;
	public $insertFunction;
	public $transaction;
	public $pKeys;
	public $saveArgs;
	public $business;
	public $busName;
	public $gridName;
	public $gridFilters;
    /**
     * Barra de ferramentas do sistema
     * 
     * @var GToolbar
     */
	public $_toolBar;
	public $keyDown;
	public $gValidator;
    /**
     * Conteúdo do formulário
     * 
     * @var BusinessGnuteca3BusFormContent
     */
	public $busFormContent;
	public $imageClose;
	public $keyDownHandlerArray;
    public $isFormSearch;
    public $forceFormContent;
    public $frmLogin;
    public $forceCreateFields;
    /**
     * Código do workflow
     *
     * @var string
     */
    private $workflowId = '';
    public static $gFocus; #define o campo que possui o foco

    
    /**
     * Default form construct
     *
     * @param string $title the title of the form
     * @param boolean $loadFields
     */
	public function __construct($title = NULL, $loadFields = true)
	{
        //para funcionar o autocomplete no Netbeans
        if ( false )
        {
            $this->MIOLO = new MIOLO();
            $this->manager = new MIOLO();
            $this->page = new MPage();
        }

        //FIXME avaliar a real necessidade deste segundo parametro $loadFields = true
        $this->function     = MIOLO::_REQUEST('function');
        $this->event        = MIOLO::_REQUEST('event');
        $this->MIOLO        = MIOLO::getInstance();
        $this->module       = 'gnuteca3';
        $this->_action      = MIOLO::getCurrentAction();
        $this->ui           = $this->MIOLO->getUI();
        $this->gValidator   = new GValidators();
        $this->gValidator->setForm($this);
        $this->busFormContent = $this->MIOLO->getBusiness($this->module, 'BusFormContent');
        $this->imageClose     = GUtil::getImageTheme('exit-16x16.png');

        //caso tiver apertado no botão de toolbar de busca, limpa os campos de filtro
        //FIXME isso foi feito de forma rápida para o sistema entrar no ar, deve ser avaliada uma resolução mais seguro
        if ( GUtil::getAjaxFunction() == 'tbBtnSearch:click' )
        {
            if ( is_array( $this->gridFilters) )
            {
                foreach ( $this->gridFilters as $line => $filter )
                {
                    $_REQUEST[$filter] = '';
                }
            }
        }

        parent::__construct( $title );

        $this->setLabelWidth(FIELD_LABEL_SIZE); //define tamanho padrão para label @deprecated
        $this->defaultButton = false; //esconde botão de post padrão do miolo

        //faz eventHandler caso tenha acesso
        if ( $this->checkAccess() )
        {
            $this->eventHandler();
        }

        //1 . caso seja postado e o evento seja edit:click - caso de normal
        //2 . caso não seja postado e nao tenha evento - ou seja abrindo direto pela url em uma aba nova
        $canDoLoadFields = ( $this->page->isPostBack() && $this->getEventAjax() == 'edit:click' ) ||
                           ( !$this->page->isPostBack() && !$this->getEventAjax() );

        //verifica necessidade de executar o loadFields, ou seja carregar os dados do registro a editar
        if ( $loadFields && //se é para fazer loadFields
             $this->event != 'tbBtnSave:click'&& //caso não for botão de salvar
             $this->updateFunction && //caso exista função de update definida no formulário
             $this->function == 'update' //caso a função do formulário seja update
             && $canDoLoadFields )
        {
            $this->loadFields();
        }

        //define form para funções javascript exemplo '__mainForm'
        $this->page->onload("frm = '{$this->page->getFormId()}';");

        //para funcionar o fileUpload
        $this->enctype='multipart/form-data';
        $this->page->setEnctype($this->enctype);
	}

    /**
     * Função chamada automaticamente pelo MForm,
     * no nosso caso, é feita uma chamada ao mainsFields, caso seja necessário
     */
    public function createFields()
    {
        //evento que ativam o mainFields
        $available[] = 'tbBtnSearch:click';
        $available[] = 'tbBtnNew:click';
        $available[] = 'edit:click';

        //somente chama os campos principais caso seja necessário
        if ( !$this->MIOLO->page->isPostBack() || $this->forceCreateFields || in_array( $this->getEventAjax(), $available ) )
        {
            // Desliga a webcam do usuário para futuro uso.
            $this->page->onload("
                if(typeof WebcamCapture !== 'undefined')
                {            
                        WebcamCapture.reset();

                }

            ");
            
            $this->mainFields();
        }
    }

    /**
     * Função que efetua a montagem dos campos iniciais.
     * Deve ser montada em cada form.
     */
    public function mainFields()
    {
        
    }

    /**
     * Aumenta a acessibilidade para campos.
     * Passa a label para o alt e title para que as dicas funcionem corretamente.
     *
     * @param array $fields de campos
     * @return array de campos
     */
    public static function accessibility( $fields )
    {
        if ( is_array( $fields ) )
        {
            foreach ( $fields as $line => $field )
            {
                if ( $field->label && ! $field instanceof MDiv )
                {
                    $label = strip_tags( str_replace(':','',$field->label) );

                    //só adiciona alt e title caso já não existirem
                    if ( !$field->getAttribute('title') )
                    {
                        $fields[$line]->addAttribute( 'alt',$label );
                        $fields[$line]->addAttribute( 'title',$label );
                    }
                }
            }
        }

        return $fields;
    }


	/**
	 * Este função insere os campos. Tambem gerencia a toolbar. Ser for um form de Search, este metodo insere a grid tambem
	 *
	 * @param array $fields
	 * @param boolean $displayToolBar
	 * @param boolean $formContent
	 */
	public function setFields( $fields, $displayToolBar = true, $formContent = TRUE )
	{
           
        $fields = GForm::accessibility($fields);
		$function = MIOLO::_REQUEST('function');
		$search = ($this->searchFunction && ($function == 'search' || !$function));
		$this->setDisplayToolBar( $displayToolBar );
        $this->keyDownHandler(113,115,116,117,118);

		//se for search insere botoes e checagem de tela
		if ( $search )
		{
            $btnSearch = new MButton('btnSearch', _M('Buscar', $this->module), ':searchFunction', GUtil::getImageTheme('search-16x16.png'));
			$fields[]  = new MDiv('btnSearchEx', $btnSearch );
			$fields[]  = new MSeparator();
			$fields[]  = $divSearch = new MDiv(self::DIV_SEARCH);
		}

		//Controla toolbar
		if ($this->displayToolbar() && ($this->insertFunction || $this->updateFunction || $search || $this->forceFormContent ))
		{
			$this->getToolBar($formContent);

			$fields = array_merge(array($this->_toolBar ), $fields);
		}

		parent::setFields($fields);
        //faz montar a grid pelo link da paginação
		if ( $search && $this->getEvent() != 'searchFunction' )
		{
			$divSearch->setInner( $this->getGrid() );
		}

        //cria campos necessários para o funcionamento do sistema
		parent::addField( new MHiddenField('GRepetitiveField', ' '));
		parent::addField( new MHiddenField('arrayItemTemp'));
		parent::addField( new MHiddenField('keyCode') ); //teclas pressionada
        parent::addField( new MHiddenField('isModified') ); //teclas pressionada
        parent::addField( new MHiddenField('functionMode') ); //teclas pressionada

		$this->checkValidatorsCaption();

		//verifica se é pra carregar o conteúdo do formulário
	    if ((MUtil::getBooleanValue(FORM_CONTENT)) && (GOperator::isLogged()) && ($formContent) && ($this->isInsert() || $this->isSearch()) )
        {
            $this->busFormContent->loadFormValues($this);
        }
	}

    /**
     * Verifica modificações no formulário.
     * Caso existam modificações mostra diálogo de confirmação
     */
    public function verifyModified($args=null)
    {
        // listagem de funções que não devem bloquear acesso
        $jumpList[] = 'tbBtnSave:click';
        $jumpList[] = 'forceAddToTable';
        $jumpList[] = 'AddToTable';
        $jumpList[] = 'generateUnitTest';
        $jumpList[] = 'executeUnitTest';    
        $jumpList[] = 'tbBtnFormContent:click';
        
        if ( in_array( GUtil::getAjaxFunction() , $jumpList ) )
        {
            return false;
        }
        
        if ( $args && is_string( $args ) )
        {
            $functionToCall = $args;
        }
        else
        {
            $functionToCall = GUtil::getLastAjax();
        }

        $isModified = MUtil::getBooleanValue( MIOLO::_REQUEST('isModified'));
        $functionMode = MIOLO::_REQUEST('functionMode');

        if ( $isModified && $functionMode == 'manage' )
        {
            $action = "javascript:dojo.byId('isModified').value=''; ".$functionToCall.'; gnuteca.closeAction();';
            GPrompt::question( _M('Você possui dados modificados no formulário, tem certeza que deseja descartá-los?','gnuteca3'), $action );
        }
    }

    /**
     * Define o campoe escondido de modificação
     * 
     * @param boolean $isModified
     */
    public function setModified( $isModified = false )
    {
        $isModified = $isModified ? 't' : '';
        $this->manager->page->onload("dojo.byId('isModified').value ='$isModified';");
    }

    /**
     * Função chamada ao apertar em novo na toolbar
     *
     * @param stdClass $args
     */
    public function tbBtnNew_click($args)
    {
        $this->verifyModified();
    }

    /**
     * Função chamada ao apertar em busca na toolbar
     *
     * @param stdClass $args
     */
    public function tbBtnSearch_click($args)
    {
        $this->verifyModified();
    }

    /**
     * Retorna o modo do formulário busca ou inserção
     *
     * @return string
     */
    public function getFormMode()
    {
        $formName = strtolower(get_class($this));

        if (stripos( $formName , 'search') > 0 )
        {
            return 'search';
        }

        return 'manage';
    }

	
	/**
	 * Define a classe de regras de negócio
	 *
	 * @param string $business
	 */
	public function setBusiness($business)
	{
		$MIOLO  				= MIOLO::getInstance();
		$module 				= MIOLO::getCurrentModule();

		$this->business 		= $MIOLO->getBusiness($module, $business);
		$this->busName  	 	= $business;
		$_SESSION['busName'] 	= $business;
	}

    /**
     * Adiciona um validador
     *
     * @param MValidator $validator
     */
    public function addValidator($validator)
    {
        $this->gValidator->addValidator($validator);
        $this->checkValidatorsCaption();
    }

	
	/**
	 * Seta os validadores do formulario
	 *
	 * @param unknown_type $validators
	 */
	public function setValidators($validators)
	{
        #verificação de segurança
        if ( is_array($validators))
        {
            $this->gValidator->setValidators($validators);
            $this->checkValidatorsCaption();
        }
	}


    /**
	 * Seta o nome da grid e seus filtros
	 *
	 * @param string $gridName
	 * @param unknown_type $gridFilters
	 */
	public function setGrid($gridName, $gridFilters = null)
	{
		$this->gridName    		= $gridName;
		$_SESSION['gridName'] 	= $gridName;

		if ($gridFilters)
		{
			$this->gridFilters 			= $gridFilters;
			$_SESSION['gridFilters'] 	= $gridFilters;
		}
	}

	
	/**
	 * Seta a função que sera executada ao clicar no botão btnSearch
	 *
	 * @param unknown_type $searchFunction
	 */
	public function setSearchFunction($searchFunction)
	{
		$this->searchFunction 		= $searchFunction;
		$_SESSION['searchFunction'] = $searchFunction;
	}

	
	/**
	 * Seta o foco em um determinado campo.
	 *
	 * @param string $fieldId
	 */
	public function setFocus($fieldId)
	{
		GForm::jsSetFocus($fieldId);
        GForm::$gFocus = $fieldId; //define o campo que o possui foco
	}

    /**
     * Retorna o identificador do campo que é definido o foco
     * @return string Retorna o identificador do campo que é definido o foco
     */
    public function getFocus()
    {
        return GForm::$gFocus;
    }

	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $getFunction
	 */
	public function setGetFunction($getFunction)
	{
		$this->getFunction   = $getFunction;
	}

	
	/**
	 * Seta a função de delete
	 *
	 * @param string $deleteFunction
	 */
	public function setDeleteFunction($deleteFunction)
	{
		$this->deleteFunction = $deleteFunction;
	}

	
	/**
	 * Seta a função de inserção de dados
	 *
	 * @param string $insertFunction
	 */
	public function setInsertFunction($insertFunction)
	{
		$this->insertFunction = $insertFunction;
	}

	
	/**
	 * Seta a função de atualização
	 *
	 * @param string $updateFunction
	 */
	public function setUpdateFunction($updateFunction)
	{
		$this->updateFunction = $updateFunction;
	}

	
	/**
	 * Seta a transação
	 *
	 * @param string $transaction
	 */
	public function setTransaction($transaction)
	{
		$this->transaction = $transaction;
	}

    /**
     * Define o código do workflow para este formulário.
     *
     * @param string $workflowId
     */
    public function setWorkflow( $workflowId )
    {
        $this->workflowId = $workflowId;
    }

    /**
     * Retorna o código do workflow para este formulário.
     * @return string o código do workflow para este formulário.
     */
    public function getWorkflow( )
    {
        return $this->workflowId;
    }

    /**
     * Define amostragem da barra de ferramentas
     * @param boolean $display amostragem da barra de ferramentas
     */
	public function setDisplayToolBar( $display = true )
	{
	   $_SESSION['displayToolbar'] = $display;
	}

    /**
     * Verifica se seta exibindo a toolbar
     * FIXME essa função está com nome fora de padrão deveria ser getDisplayToolbar
     *
     * @return boolean
     */
    public function displayToolbar()
    {
        return (!$_SESSION['displayToolbar']) ? false : true;
    }

	
	/**
	 * Seta todas a funçãoes
	 *
	 * @param unknown_type $alias
	 * @param unknown_type $gridFilters
	 * @param unknown_type $primaryKeys
	 * @param unknown_type $saveArgs
	 */
	public function setAllFunctions($alias, $gridFilters=NULL, $primaryKeys=NULL, $saveArgs=NULL )
	{
		$this->setBusiness('Bus'.$alias);
		$this->setSearchFunction('search'.$alias);
		$this->setInsertFunction('insert'.$alias);
		$this->setGetFunction('get'.$alias);
		$this->setDeleteFunction('delete'.$alias);
		$this->setUpdateFunction('update'.$alias);
		$this->setTransaction('gtc'.$alias);

		if ($gridFilters)
		{
			$this->setGrid('Grd'.$alias, $gridFilters);
		}
		if ($primaryKeys)
		{
			$this->setPrimaryKeys($primaryKeys);
		}
		if ($saveArgs)
		{
			$this->setSaveArgs($saveArgs);
		}
	}

	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $args
	 */
	public function setSaveArgs($args)
	{
		$this->saveArgs = $args;

		if (!is_array($this->saveArgs) )
		{
			$this->saveArgs = array($this->saveArgs );
		}
	}

	
	/**
	 * Seta as chaves primarias
	 *
	 * @param unknown_type $pKeys
	 */
	public function setPrimaryKeys($pKeys)
	{
		$this->pKeys = $pKeys;

		if (!is_array($this->pKeys) )
		{
			$this->pKeys = array($this->pKeys);
		}
	}

	
    /**
     *  retorna a função de busca
     *
     * @return string
     */
	public function getSearchFunction()
	{
		$searchFunction = $this->searchFunction;
		if ( !$searchFunction)
		{
			$searchFunction = $_SESSION['searchFunction'];
		}
		return $searchFunction;
	}

	
	/**
	 * Retorna o nome do Business
	 *
	 * @return string
	 */
	public function getBusName()
	{
		return strlen($this->busName) ? $this->busName : $_SESSION['busName'];
	}

	
	/**
	 * Retorna o nome da grid
	 *
	 * @return string
	 */
	public function getGridName()
	{
		return strlen($this->gridName) ? $this->gridName : $_SESSION['gridName'];
	}

	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function getGridFilters()
	{
		return $this->gridFilters ? $this->gridFilters : $_SESSION['gridFilters'];
	}

	
	/**
	 * retorna o evento
	 *
	 * @return unknown
	 */
	public function getEvent()
	{
    	$event1 = GForm::getEventAjax();
		$event2 = MIOLO::_REQUEST('event');

		return strlen($event1) ? $event1 : (strlen($event2) ? $event2 : '');
	}

	public function getEventAjax()
	{
		$MIOLO = MIOLO::getInstance();
		return MIOLO::_REQUEST($MIOLO->page->getFormId() . '__EVENTTARGETVALUE');
	}

	
	/*
	 * Returns previous level action url
	 *
	 * @return (String)
	 */
	public function getPreviousActionURL()
	{
		return $this->MIOLO->getActionURL($this->module, substr($this->_action, 0, strrpos($this->_action, ':')));
	}

	
	/**
	 * retorna a url da ação corrente com evento search
	 *
	 * @return unknown
	 */
    public function getGotoCurrentActionAndSearchEventUrl()
    {
        return GUtil::getCloseAction(true) . GUtil::getAjax('searchFunction');
    }

    
	/**
	 * Obtem o elemento/objeto da grid
	 *
	 * @param stdClass $data stdClass do post
	 * @param array $gridData array de dados
	 * @return unknown
	 */
	public function getGrid( $data = null, $gridData = null )
	{
        $MIOLO        = MIOLO::getInstance();
		$module       = MIOLO::getCurrentModule();
		$gridName 	  = $this->getGridName();
		$filter 	  = $this->getGridFilters();
		$business     = $this->business;

        $tmpData = new StdClass();
        $tmpData->transaction = $this->transaction; //pega transação
        $grid = $MIOLO->getUI()->getGrid($module, $gridName,$tmpData); //pega grid passando a transação
        $grid->setTransaction( $this->transaction );

        if ( $this->pKeys ) //Se tiver primary keys
        {
            $x = 0; //inicia contador
            
            foreach ( $this->pKeys as $pKey) //Para cada chave primária
            {
                $primaryKeys[$pKey] = $x; //Cria relacionamento do campo da chave primária com a coluna da grid.
                $x++; //incrementa
            }

            $grid->setPrimaryKey($primaryKeys); //Seta chave primária.
        }
        
        $grid->setWorkflow( $this->workflowId );
        $grid->parent = $this;

        //se for gnuteca grid ativa o botão de delete se ele existir
        if ( $grid instanceof GSearchGrid && $this->_toolBar)
        {
            if ( $this->_toolBar->getButton(ToolBar::BUTTON_DELETE) )
            {
                $this->_toolBar->enableButtons(array(MToolBar::BUTTON_DELETE));
            }
        }

		//se não tiver instanciado o Business ainda, instancia-o
		if ( !is_object( $business ) )
		{
			$busName  = $this->getBusName(); //pega nome do bus
			$business = $this->MIOLO->getBusiness($this->module, $busName);
		}

		//define como form de busca
        $business->isFormSearch = TRUE;
		$searchFunction = $this->getSearchFunction(); //pega nome da função de busca

        //relação de filtros definidos no formulário
        $filter  = !is_array($filter)  ? array($filter)    : $filter;
        
        //detecta se foi aplicado algum filtro
		if (is_array( $filter ) )
		{
			foreach ( $filter as $val )
			{
				if ( MIOLO::_REQUEST( $val ) || MIOLO::_REQUEST( $val . 'S' ) )
				{
					$ok = true;
                    break;
				}
			}
		}

		//esta data são os dados de filtro, passados para o Business
        $data = !$data ?  ($this->getData() ) : $data;

        //monta uma relação dos eventos que devem chamar a montagem da grid
        //FIXME isso deve ser melhorado
        $availableFunctions[] = 'searchFunction';
        $availableFunctions[] = 'generateGridPdf';
        $availableFunctions[] = 'generateGridCSV';
        
		$makeSearch = in_array($this->getEventAjax(), $availableFunctions);
        
        //só executa a busca caso tenha sido aplicado algum filtro, e o formulário tenha sido postado e tenha filtros
        if ( ( $makeSearch || $ok ) && $this->getEventAjax() != 'tbBtnSearch:click' )
		{
			$data = $this->getData(); //pega mais dados
            
            if ( $this->deleteFunction && ($this->getEvent() == 'tbBtnDelete_click') )
            {
                $opts = $this->getOpts(TRUE);

                foreach ( $opts as $key => $val )
                {
                    unset( $data->$key );
                }
            }

            //forca o pagina a ser a primeira quando o usario apertou no botao btnSearch
            /*if ( GUtil::getAjaxEventArgs() == '' ? true : false )
            {
                //só vai funcionar caso tenha count > 0
                $count = $business->count( $searchFunction );
            }
            else
            {
                $count = MIOLO::_REQUEST('gridCount');
            }

            if ( $count )
            {
                $grid->setCount( $count );
                $business->setApplyLimitAndOffset( true ); //aplica o limit , order e offset
            }*/

            // define os dados da busca no bus
            $business->setData( $data );

            //chama função do bus e seta em $gridData
            $gridData = (!$gridData) ? call_user_func(array($business, $searchFunction)) : $gridData;
            //seta os dados resultantes na grid
            $grid->setData( $gridData );
            
            // ------------Temporario-------------
            $operator = GOperator::getOperatorId();

            $buffer = "----1-----\n";
            $buffer .= "Hora: " . GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
            $buffer .= "\nGrid: " . $_SESSION['gridName'] . "\n";
            $buffer .= print_r($_REQUEST, true);

            $sessao = $_SESSION;
            unset($sessao['menuItems']);
            unset($sessao['login']);
            $buffer .= print_r($sessao, true);

            $buffer .= print_r("\nOK: " . $ok, true);
            $buffer .= print_r("\nmakeSearch: " . $makeSearch, true);
            $buffer .= "\n----2-----\n\n\n";

            file_put_contents("/var/www/html/gnuteca/modules/gnuteca/html/files/tmp/{$operator}.debug", $buffer, FILE_APPEND);

            // ---------------------------------
            
		}
		else
		{
			$grid->emptyMsg = ''; //mensagem de vazio falsa, entra aqui quando ainda não apertou em buscar
		}

		return $grid;
	}

	
	/**
	 * verifica se a fução é search
	 *
	 * @return boolean
	 */
	public function isSearch()
	{
		return (($this->function == '' || $this->function == 'search')) || ($this->getEvent() == 'searchFunction');
	}

	
	/**
	 * verifica se a função é insert
     * Em alguns casos o insert é usado como new
	 *
	 * @return boolean
	 */
    public function isInsert()
    {
        //em alguns casos o insert é usado como new
        return ( $this->function == 'insert' || $this->function == 'new' );
    }

    
    /**
     * Check o acesso
     *
     * @return boolean se tem ou não acesso ao formulário
     */
	public function checkAccess()
	{
		if ( ! $this->transaction )
        {
            throw new Exception ( _M('Formulário sem transação definida é necessário definir uma transação de segurança! Contate o administrador!') );
        }

    	return GPerms::checkAccess($this->transaction, $this->function);
	}


    /**
     * Adicionar tipo de validador no campo, para que o miolo interprete
     * que é um campo requerido, colocando a classe e o * devido.
     */
	public function checkValidatorsCaption()
	{
		$validators = $this->gValidator->getValidators();
        
		foreach ($validators as $v)
		{
			if ($v->type == 'required')
			{
				$field = $this->GetField($v->field);
                
				if ($field instanceof MCalendarField)
				{
					$field->validator = '';
					$field->validator->checker = 'DATEDMY';
				}
				if (is_object($field))
				{
					$field->validator->type = 'required';
				}
			}
		}
	}

    /**
	 * Recebe um conteudo a sera exibido na tela bloqueando o que esta por baixo.
	 *
	 * Esta função pode ser usada de forma estática.
	 *
	 * @param object $content conteúdo a mostrar na caixa, qualquer coisa, desde string até objetos do miolo
	 * @param boolean $closeButton se é para adicionar automaticamente o botão de fechar, pode-se adicionar um js ao botão passando uma string no lugar de um boolean
	 * @param string or boolean $form true para criar automaticamente um formulário para o conteúdo, string para definir o título da janela
	 * @param string $formWidth
	 */
	public static function injectContent($content, $closeButton = false, $form = true, $formWidth = null)
	{
        GPrompt::injectContent($content, $closeButton, $form, $formWidth);
    }
	
	public static function getCloseButton( $extraJavaScript = null )
	{
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $js = GUtil::getCloseAction();

        //Adiciona js extra na acao de close
        if ( $extraJavaScript)
        {
            $js .= '; ' . $extraJavaScript;
        }

        $btn = new MButton('btnClose', _M('Fechar','gnuteca3'), $js, GUtil::getImageTheme('exit-16x16.png' ) );
        $btn->addAttribute('onblur',"gnuteca.setFocus('popupTitle',true);"); //faz com que o foco volte ao título da janela
        $btn->addAttribute('alt',"Fecha a janela atual;");         //acessibilidade
        $btn->addAttribute('title',"Fecha a janela atual;");

        return $btn;
	}

	
	/**
	 * Cria um tela de informação para o usuário com um botão OK.
	 *
	 * @param String $msg
	 * @param String $goto
	 * @param String $event
	 * @param boolean $gotoIsJs
	 */
	public static function information( $msg, $goto ='javascript:gnuteca.closeAction();' )
	{
            GPrompt::information($msg, $goto);
	}

	
	/**
	 * Gera um tela de quetão para o usuário com dois botões, Sim e Não.
	 *
	 * @param string $msg
	 * @param url action $gotoYes
	 * @param url action $gotoNo
	 * @param unknown_type $closeButton
	 * @param unknown_type $return
	 * @return unknown
	 */
	public static function question( $msg, $gotoYes, $gotoNo = 'javascript:gnuteca.closeAction();' )
	{
		GPrompt::question($msg, $gotoYes, $gotoNo);
	}


    /**
     * Gera uma tela de erro.
     *
     * @param string $msg
     * @param string $goto
     * @param string $caption
     * @param string $event
     */
	public function error( $msg, $goto = 'javascript:gnuteca.closeAction();' , $caption ='Erro')
	{
        GPrompt::error($msg, $goto, $caption);
	}

	
    /**
    * Função chamada quando usuário aperta F1  ou botão de help
    **/
	public function help($args=NULL)
	{
        $MIOLO   = MIOLO::getInstance();
        $module  = MIOLO::getCurrentModule();
        if ( get_class($this) == 'FrmLogin' )
        {
            return false;
        }
        
        $busHelp = $MIOLO->getBusiness( 'gnuteca3', 'BusHelp');
        $busHelp = new BusinessGnuteca3BusHelp();
        
        if ( $this instanceof GSubForm )
        {
            $form = 'FrmSimpleSearch';
            $subform = get_class($this);
        }
        else
        {
            $form = get_class($this);

            //se formulário for pesquisa, busca a pesquisa administrativa selecionada
            if ( $form == 'FrmSimpleSearch' )
            {
                $subform = MIOLO::_REQUEST('formContentId'); //busca o id da pesquisa
            }
        }

        $help = $busHelp->getFormHelp( $form, $subform );
        
        $content = $help->help;
        
        $fields[] = new MDiv('helpContent', $content);

        //rola a página até a ajuda do campo
        if ( is_string($args) && strlen($args) > 0 )
        {
            $MIOLO->page->onload( "if ( dojo.byId('h{$args}') )
                                  {
                                      dojo.byId('h{$args}').focus(); 
                                      dojo.query('.mFormBody')[0].scrollTop = dojo.byId('h{$args}').offsetTop - 100;
                                   }");
        }
        
        //Obtem permissão de alteração de ajuda.
        $helpUpdateAccess = GPerms::checkAccess( 'gtcHelp', 'update', false );
        //Obtem permissão de inserção de ajuda.
        $helpInsertAccess = GPerms::checkAccess( 'gtcHelp', 'insert', false );
        
        //caso tenha permissão de edição, mostra botão para editar a ajuda
        if ( $helpUpdateAccess || $helpInsertAccess )
        {
            //Se já tem um ajuda cadastrada e tem permissão para alterar ajuda.
            if ( $help->helpId && $helpUpdateAccess )
            {
                //Cria opção que levará para a edição da ajuda.
                $opts = array('function' => 'update',
                              'helpId' => $help->helpId);
                $actionMessage = _M('editar','gnuteca3');
            }
            elseif ( !$help->helpId && $helpInsertAccess ) //Senão verifica se tem permissão para inserir ajuda
            {
                $opts = array('function' => 'insert',
                              '_form' => $form,
                              '__subForm' => $subform);                                
                $actionMessage = _M('inserir','gnuteca3');
            }
            
            $link = new MLink('linkEdit', _M('aqui', 'gnuteca3'), $MIOLO->getActionURL('gnuteca3', 'main:configuration:help', null, $opts));

            $fields[] = new MSeparator();
            $fields[] = new MSeparator();

            $fields[] = $divEdit = $edit = new MDiv('divEdit', _M('Clique @1 para @2 a ajuda.', 'gnuteca3', $link->generate(), $actionMessage));
            $edit->addStyle('text-align', 'left');
            $edit->addStyle('padding-top', '20px');                    
        }
        
        GForm::injectContent($fields, true, _M('Ajuda', $module) );
      
    
	}

	/**
	 * Mostra um diálogo de ajuda com as funções que podem ser utilizada no GFunction
	 *
	 * @param unknown_type $function
	 */
	public function _showFunctionHelp($function=null)
	{
		$this->MIOLO->getClass($this->module, 'GFunction');
		$GF             = new GFunction();
		$helpFunctions  = $GF->helpFunctions($function);
		$data           = array();

		foreach ($helpFunctions as $key => $val)
		{
			if ($val->parameter)
			{
				$nParamenter = 0;

				foreach ($val->parameter as $pKey=>$par)
				{
					$nParamenter++;
					$val->parameter[$pKey] = 'P' . $nParamenter . ': ' . $par;
				}
			}

			$val->parameter = (count($val->parameter)) ? implode('<br>', $val->parameter) : '-';
			$val->example   = htmlentities($val->example,null, 'UTF-8');
			$data[] = array($key, $val->description, $val->parameter, $val->example, $val->return);
		}

		$titles = array
		(
    		_M('Função',    $this->module),
    		_M('Descrição', $this->module),
    		_M('Parâmetro',   $this->module),
    		_M('Exemplo',     $this->module),
    		_M('Devolução',      $this->module)
		);

		$table    = new MTableRaw(null, $data, $titles);
        $table->setAlternate(true);

        $this->injectContent( $table->generate(), true, _M('Ajuda', $this->module) );
	}

	
	/**
	 * Função que é chamada ao clicar no botão btnSearch
	 *
	 * @param unknown_type $args
	 */
	public function searchFunction($args)
	{
        $args = GForm::corrigeEventosGrid($args);
        
        //Os historicos da circulacao de material possuem um searchFunction especifico
        //FIXME isso não pode ser assim
        if ( !MIOLO::_REQUEST('isSearchFunctionCirculation') ) 
        {
            $this->setResponse( $this->getGrid( $args ), GForm::DIV_SEARCH );
        }
	}

	
    /**
     * A grid original do MIOLO foi reformulada na questao paginacao e ordenacao.
     * Para que a funcao searchFunction funcione corretamente, deve ser chamado esta funcao para corrigir estes eventos.
     *
     * Atentar para o fato de que esta funcao é utilizada em varios outros formularios que possuem sua propria searchFunction()
     * Essa função também altera o $_POST;
     *
     * @param string $args
     * @return stdClass
     */
    public static function corrigeEventosGrid( $args )
    {
        //Limpar o evento, pois após o delete, ao clicar na busca ou próxima página, estava tentando apagar novamente.
        $_REQUEST['event'] = '';

        if ( is_string( $args ) )
        {
            $_args = GUtil::decodeJsArgs($args);
            $args = (object)$_REQUEST;
            foreach ($_args as $key => $val)
            {
                $args->$key = $val;
                $_POST[$key] = $val;
            }
        }
		else
		{
       	    //forca o pagina a ser a primeira quando o usario apertou no botao btnSearch
       	    $_POST['pn_page'] = 1;
		}

        return $args;
    }

    
	/**
	 * Carrega os dados dos campos
	 *
	 */
	public function loadFields()
	{
		if ($this->getFunction)
		{
			call_user_func_array(array($this->business, $this->getFunction), $this->getOpts(false) );
			$this->setData( $this->business );
		}
	}

    public function setData( $data, $doRepetitiveField = false )
    {
        parent::setData($data);

        if ( $doRepetitiveField )
        {
            if ( is_array( $this->fields ) )
            {
                foreach ( $this->fields as $line => $field )
                {
                    if ( $field instanceof GRepetitiveField)
                    {
                        $name       = $field->name;
                        $dataField  = $data->$name;
                        
                        GRepetitiveField::setData($dataField, $name);
                    }
                }
            }
        }
    }

	/**
	 * Method chamado quando o botão save é clicado.
	 *
	 * @param null $sender
	 * @param object $data
	 * @param array $errors
	 * @param miolo business object $business
	 * @param nusiness method $method
	 * @return booleans
	 */
	public function tbBtnSave_click($sender = NULL, $data = NULL, array $errors = NULL, $business = null, $method = null)
	{
        if ( !$this->pKeys )
        {
            throw new Exception( _M('Chave primária não definida.','gnuteca3') );
        }
        
        $this->mainFields(); //FIXME isso foi feito para funcionar validadores, analisar uma forma melhor

        $data = !is_null($data) ? $data : $this->getData();
        $function = MIOLO::_REQUEST('function');

        if ( !$this->validate($data, $errors) )
        {
            return false;
        }

        $business = is_null($business) ? $this->business: $business;
        $business->setData( $data );

        $optsYes    = array( 'event'=>'tbBtnNew_click', 'function' => $function, 'pn_page' => 1 );
        $gotoYes    = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsYes);
        $optsNo     = array( 'function'=>'search' , 'pn_page' => 1 );
        $optsNo     = array_merge($optsNo, $this->getOptsSave() );

        //faz urlencode de cada atributo para passar via get na mensagem de finalização
        if ( is_array( $optsNo ) )
        {
            foreach ( $optsNo as $line => $opts )
            {
                $optsNo[$line] = urlencode( $opts );
            }
        }

        $gotoNo = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsNo);

        if ( ( $this->insertFunction || $method ) && ( $function == 'insert' ) )
        {
            $method = is_null($method) ? $this->insertFunction : $method;
            $business->beginTransaction();
            $ok = call_user_func(array($business, $method));
            $key = $this->pKeys[0];

            if ( $this->workflowId )
            {
                //medida de segurança para bussines incompatíveis com workflow
                if ( ! $business->$key )
                {
                    $business->rollbackTransaction();
                    throw new Exception( _M('Impossível iniciar workflow sem código da tabela. É necessário corrigir o "@1" para que isto funcione. Contate o administrador do sistema.', 'gnuteca3', $this->busName ));
                }

                $tableId = $business->$key; //obtem código da tabela retornado do insert;
                $this->manager->getClass('gnuteca3', 'GWorkflow');
                $worflowOk = GWorkFlow::instance( $this->workflowId, $this->transaction, $tableId );

                if ( !$worflowOk )
                {
                    $business->rollbackTransaction();
                    throw new Exception( _M('Impossível inserir worlflow! A instância inválidou resultado!', 'gnuteca3') );
                }

                $workflowMsg = _M('Workflow iniciado com sucesso!', 'gnuteca3');
            }

            //caso tenha acontecido a excessão acima, não faz o commit no banco
            $business->commitTransaction();

            $keyValue = $business->$key;

            //caso tenha a chave que vem do banco, inclui na listagem
            if ( $keyValue )
            {
                $optsNo[$key.'S'] = $keyValue;
                $gotoNo = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsNo);
            }

            if($ok)
            {
                //limpa a sessão das repetitivefield
                if ( $this->fields )
                {
                    foreach ( $this->fields as $line => $field )
                    {
                        if ( $field instanceof GRepetitiveField)
                        {
                            GRepetitiveField::clearData($field->name);
                            $_REQUEST[$name] = null; //coloca no request para funcionar com MIOLO::_REQUEST
                        }
                    }
                }

                $msg = $workflowMsg ? $workflowMsg . '<br/>' . MSG_RECORD_INSERTED : MSG_RECORD_INSERTED;
                $this->question( $msg , $gotoYes, $gotoNo);

                return true;
            }
        }
        elseif ( ($this->updateFunction || $method ) && ($function == 'update') )
        {
            $method = is_null($method) ? $this->updateFunction : $method;
            $business->beginTransaction();
            $ok  = call_user_func(array($business, $method));
            $business->commitTransaction();

            if($ok)
            {
                //limpa a sessão das repetitivefield
                if ( $this->fields )
                {
                    foreach ( $this->fields as $line => $field )
                    {
                        if ( $field instanceof GRepetitiveField)
                        {
                            GRepetitiveField::clearData($field->name);
                            $_REQUEST[$name]   = null; //coloca no request para funcionar com MIOLO::_REQUEST
                        }
                    }
                }

                $this->information( MSG_RECORD_UPDATED, $gotoNo);

                return true;
            }
        }

        $errorMsg = MSG_RECORD_ERROR;

        if (method_exists($business, 'getErrors') && ($errors = $business->getErrors()))
        {
            $errorMsg = implode('<br>', $errors);
        }

        $this->error( $errorMsg, 'javascript:'. GUtil::getCloseAction(), _M('Erro',$this->module) );

        return false;
	}


    /**
     * Valida os dados e seta classes de erros de validação nos campos
     *
     * @param stdClass $data objeto do post
     * @param array erros forçados
     * @param boolean $prompt força mostrar erro com prompt
     * @return boolean
     */
    public function validate($data, $extrasErrors = null, $prompt = true )
    {
        if ( $extrasErrors )
        {
            $this->gValidator->errors = $extrasErrors;
        }

        $errors = $this->gValidator->validate( $data );

		if ( $errors && is_array( $errors ) )
		{
            $MIOLO = MIOLO::getInstance();
            $js = "gnuteca.cleanValidatorsMessage();";

            if ( is_array($errors) )
            {
                foreach ( $errors as $fieldid => $msg )
                {
                   $js .= "gnuteca.addValidatorMessage('$fieldid','$msg');";
                }
            }

            $MIOLO->page->onload($js);
            $firstError = array_keys($errors);
            
            if ( ! $prompt === false )
            {
                $js = GUtil::getCloseAction( true ) . " gnuteca.setFocus('{$firstError[0]}'); ";
                $this->error( implode('<br>', $errors) , $js , _M( 'Validação', $this->module ) );
            }
            
			return false;
		}

        return true;
    }
	
	/**
	 * Função chamada ao pressionar botão de salvar dados do formulário.
	 *
	 * @param stdClass $sender
	 */
	public function tbBtnFormContent_click($sender = NULL)
	{
		$data = $this->getData();
        
		if ( $this->busFormContent->saveFormValues($this, $data) )
		{
		  $this->information(_M('Configurações salvas', $this->module), GUtil::getCloseAction(true) );
		}
		else 
		{
			$this->error( _M('Erro salvando configurações', $this->module), GUtil::getCloseAction(true) );
		}
	}

	public function getOpts($key=False, $getArgs=FALSE)
	{
		$data = $this->pKeys;
		if ($getArgs)
		{
			$data = array_merge($data, $this->saveArgs ? $this->saveArgs : array());
		}

		foreach ($data as $line => $info)
		{
			if ($key)
			{
				$args[$info] = MIOLO::_REQUEST($info);
				$args[$info.'S'] = MIOLO::_REQUEST($info);
			}
			else
			{
				$args[] = urldecode( MIOLO::_REQUEST($info) );
			}
		}
        
		return $args;
	}

    /*
     * Função específica para retornar as opções do salvar. Esta função
     * somente retorna os campos com 'S' concatenado. Não leva em consideração
     * os campos do insert/update.
     */
    public function getOptsSave()
    {
        $data = $this->pKeys;
        $data = array_merge($data, $this->saveArgs ? $this->saveArgs : array());

        foreach ($data as $line => $info)
        {
            $args[$info.'S'] = MIOLO::_REQUEST($info);
        }

        return $args;
    }

	/**
     * Evento acionado quando o usuario seleciona Delete na toolbar
	 **/
	public function tbBtnDelete_click( $sender = NULL )
	{
            // Se tiver valor no evento, foi clicado na ação de excluir da linha da grid.
            $verificarCheckBoxes = !(strlen($_REQUEST['__mainForm__EVENTARGUMENT']) > 0);
	    //Pega o número da página atual
        foreach ($_REQUEST as $key => $val)
        {
            if (preg_match("/(.*)_action/", $key))
            {
                $urlAction = explode('&', $val);
                foreach ($urlAction as $strAction)
                {
                    list($_key, $_val) = explode('=', $strAction);
                    if ($_key == 'pn_page' || $_key == 'gridName')
                    {
                        $opts[$_key] = urldecode($_val);
                    }
                }
            }
        }

		if ( $this->deleteFunction )
		{
            $checkBoxName = 'select'.$this->gridName;

            if ( is_array($_REQUEST[$checkBoxName]) && !$_REQUEST[$this->pKeys[0]] ) //Se tem checkboxes selecionados e não foi clicado na ação de excluir da grid.
            {
                $gridSelectData = array_values( $_REQUEST[$checkBoxName] ); //Pega os valores dos checkboxes selecionados
                $opts['gridData'] = implode(',', $gridSelectData); //junta os valores por virgula ','
            }

			$opts['event']       = 'tbBtnDelete_confirm';
			$opts['function']    = 'delete';

            //exclusão pela toolbar na edição
            if ( $this->function == 'update' )
            {
                $verificarCheckBoxes = false;
                $opts['toolbarEdit'] = true;
            }

			//If ->isAjaxEvent = TRUE, current event = UPDATE, otherwise grid
			if ($this->manager->isAjaxEvent)
			{
				$opts['isUpdateEvent'] = true;
			}

			$args = $this->getOpts(true);

			if ( is_array($args) )
			{
				$opts  =  array_merge($opts , $this->getOpts(true) );
            }
            
            $existemFiltros = ( $opts[$this->pKeys[0]] || count($gridSelectData) > 0 ); //Se tiver sido passado um pkey ou tiver algum checkbox selecionado significa que foi passado algum filtro.
            if ( $verificarCheckBoxes )
            {
                // Se foi clicado no excluir da toolbar, é necessário verificar se foi selecionado algum registro.
                $existemFiltros = count($gridSelectData) > 0;
            }

            if ( ! $existemFiltros )
            {
                $this->information('É necessário selecionar ao menos um registro para remoção!', null, null, true);
                return;
            }

            //obtem a página atual da grid
            $opts['pn_page'] = MIOLO::_REQUEST('gridPage');
			$this->question( MSG_CONFIRM_RECORD_DELETE , 'javascript:'.GUtil::getAjax( 'tbBtnDelete_confirm' , $opts ) );
		}
	}

    /**
     * Função criada para remover registros da grid sem ter de escrever código duplicado na função tbBtnDelete_confirm
     * @param array $opts
     * @return boolean que diz se a exclusão ocorreu corretamente.
     */
    protected function deleteRegister($opts)
    {
        $ok = call_user_func_array(array($this->business, $this->deleteFunction), $opts ); //Remove o registro da grid.
        $id = $opts[$this->pKeys[0]]; //Pega o id da chave primária
        
        if ( !$id ) //Se tiver id
        {
            $id = $opts[0]; //Atribui para usar na exclusão da instancia do workflow;
        }

        if ( $ok ) //Se conseguiu excluir o registro
        {
            //remove workflow relacionado caso possível
            if ( $this->workflowId )
            {
                $this->manager->getClass('gnuteca3', 'GWorkflow');
                $worflowOk = GWorkFlow::deleteInstance( $this->workflowId, $this->transaction, $id  ); //Deleta instancia do workflow
            }
        }

        return $ok;
    }
    
    /**
     * Evento ativado quando o usuário seleciona "Sim" no botão da caixa de dialogo de confirmação da exclusão.
     **/
    public function tbBtnDelete_confirm($sender=NULL)
	{
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $gridData = explode(',',$_REQUEST['gridData']);

        if ( $this->deleteFunction )
        {
            $this->business->beginTransaction();
            
            if ( count($gridData) > 0 )
            {
                $exclude = true; //Flag que define se é para excluir o registro

                if ( (count($gridData) >= 0) && (strlen($gridData[0]) > 0) ) //Se tem algum valor dentro do array gridData
                {
                    foreach ( $gridData as $line ) //Faz remoção pelos checkbox
                    {
                        $argument = explode('|@|',$line);//Separa argumentos diferentes
                        $opts = null; //reseta opts

                        foreach ($argument as $value) //Para cada argumento e seu respectivo valor
                        {
                            $args = explode('=',$value); //Separa o nome do argumento de seu respectivo valor

                            if ( $args[1] ) //Se existe um argumento com valor para ser excluído
                            {

                                $opts[$args[0]] = $args[1]; //Prepara array para exclusão pelo call_user_func_array
                            }
                            else //Se existe um argumento com valor não definido
                            {
                                $exclude = false; //Nega exclusão
                            }
                        }
                        
                        if ( $exclude ) //Se não houve nenhum argumento com valor em branco passado
                        {
                            $ok = $this->deleteRegister($opts);
                        }
                    }
                }
                else //Se não tem valor dentro de $gridData quer dizer que é uma exclusão pela grid.
                {
                    //Garante que caso não tenha havido uma remoção pelos checkbox seja feita uma remoção pelo link
                    $ok = $this->deleteRegister($this->getOpts(false));
                }

                $this->business->commitTransaction();
            }


            if ( $ok )
            {
                $args['pn_page']    = MIOLO::_REQUEST('pn_page');
                $args['gridName']   = MIOLO::_REQUEST('gridName');

                //caso a exclusão for através da toolbar na edição
                if ( MIOLO::_REQUEST('toolbarEdit')  )
                {
                    $action = MIOLO::getCurrentAction();
                    $args['function'] = 'search';

                    $goto = $MIOLO->getActionURL($module, $action, null, $args);
                }
                else
                {
                    $goto = 'javascript:'.GUtil::getAjax('searchFunction', $args) . GUtil::getCloseAction();
                }

                $this->information( MSG_RECORD_DELETED, $goto);
            }
            else
            {
                $this->error( MSG_RECORD_ERROR, $this->MIOLO->getActionURL($this->module, $this->action) , _M('Erro', $this->module) );
            }
        }
	}

	
	/**
	 * Retorna toolbar padrão, de acordo com a função definida
	 *
	 * @param unknown_type $formContent
	 */
	public function getToolBar($formContent = TRUE)
	{
        $key = $this->pKeys[0];
		$this->_toolBar = new GToolBar();
        $this->_toolBar->setFormContent( $formContent );
        $this->_toolBar->setTransaction( $this->transaction );
        $this->_toolBar->setForm( $this );
        $this->_toolBar->setWorflowTableId( MIOLO::_REQUEST($key)  ); //define código da tabela
        $this->_toolBar->setWorkflow( $this->workflowId ); //define workflow
    }

	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $lines
	 * @return unknown
	 */
	public function mountContainers($lines)
	{
		if ($lines && is_array($lines) )
		{
			foreach ($lines as $line => $info)
			{
				$fields[$line]= new MHContainer('container'.$line, $info);
			}
		}
        
		return $fields;
	}

	
	function removeFromTable( $args )
	{
		autoRemoveAction( $args );
	}

    function downloadFromTable( $args )
	{
        GRepetitiveField::downloadFromTable($args);
	}
	
	function addToTable( $args, $object = null, $errors = null )
	{
               autoAddAction( $args, $object, $errors, false );
	}
	
	function forceAddToTable( $args, $object = null, $errors = null )
	{
                $args->arrayItemTemp = null;
		autoForceAddAction( $args, $object, $errors );
	}
	
	function upFromTable($args)
	{
		autoUpAction($args);
	}
	
	function downFromTable($args)
	{
		autoDownAction($args);
	}
	
	function editFromTable($args)
	{
		autoEditAction($args);
	}
        
        function gravaTag($args)
	{
		autoGravaTagAction($args);
	}
	
	function clearTableFields($args)
	{
		autoClearAction($args);
	}
	
	/**
    * Método chamado para incluir
    */ 
    function forceIncludeToTable( $args, $object = null, $errors = null )
    {
    	$args->arrayItemTemp = null;
        autoAddAction( $args, $object, $errors , false);
    }
	
   
	/**
	 * The value of a Field
	 *
	 * @param $fieldId the id of the field
	 * @param $value the value you want in field
	 */
	public static function jsSetValue($fieldId, $value)
	{
		$MIOLO = MIOLO::getInstance();

        //caso for um objeto chama o generate
        if ( is_object( $value ) && method_exists( $value, 'generate' ) )
        {
            $value = $value->generate();
        }

        if ( $fieldId )
        {
            $MIOLO->page->onLoad('element = dojo.byId(\''.$fieldId.'\'); if (element) { element.value = \''.$value.'\';}');
        }
	}

	
	/**
	 * Disable a field
	 *
	 * @param $fieldId the id of the field
	 * @param $disable true if you want to disable, false if you want to enable
	 */
	public function jsSetDisable($fieldId, $disable = TRUE)
	{
        $disable = $disable ? 'true' : 'false';
        $this->page->onload("field = dojo.byId('$fieldId'); if (field) { field.disabled = $disable; }");
	}

	
	/**
	 * Check a field
	 *
	 * @param $fieldId the id of the field
	 * @param $value true if you want to disable, false if you want to enable
	 */
	public function jsSetChecked($fieldId, $value = TRUE)
	{
        $value = $value ? 'true' : 'false';
        $this->page->onload('field = dojo.byId(\''.$fieldId.'\');  if (field) { field.checked = '.$value.'};' );
	}

	
	/**
	 * The focus on a specific field
	 *
	 * @param string id do campo
     * @param boolean se aplica no momento ou aguarda um momento para carregamento da página
	 */
	public static function jsSetFocus( $fieldId, $now = true )
	{
            $MIOLO = MIOLO::getInstance();
            GForm::$gFocus = $fieldId; //define variavel de foco denifido
            $now = $now ? 'true' : 'false'; //parser para javascript
            $MIOLO->page->onLoad("gnuteca.setFocus('{$fieldId}', $now );");
	}
        
        /**
         * Define o foco no primeiro campo visível da lista enviada por parâmetro.
         * 
         * @param array $fields Lista de possíveis campos.
         */
        public static function setFocusInFirstInput(array $fields)
        {
            if ( count($fields) > 0 )
            {
                $MIOLO = MIOLO::getInstance();

                $js = "elements = ['" . implode("','",$fields) . "'];
                    for ( i = 0; i < elements.length; i++) 
                    { 
                        element = dojo.byId(elements[i]);
                        if ( element.tagName == 'INPUT' || element.tagName == 'SELECT' || element.tagName == 'TEXTAREA' )
                        { 
                            if ( element.type != 'hidden' ) 
                            {
                                gnuteca.setFocus(element.id,true);
                                break;
                            } 
                        }
                    }
                ";

                $MIOLO->page->onLoad($js);
            }
        }

	
	/**
	 * Make a field readOnly or not. Note that this function change the class of the field to
	 *
	 * @param $fieldId the id of the field
	 * @param $readOnly true if in to do readOnly
	 *
	 */
	public function jsSetReadOnly($fieldId, $readOnly=TRUE)
	{
		if ($readOnly)
		{
			$this->page->onload('field = dojo.byId(\''.$fieldId.'\'); if (field) { field.readOnly = true; }' );
			$this->jsSetClass($fieldId, 'mReadOnly');
		}
		else
		{
			$js = 'field = dojo.byId(\''.$fieldId.'\') ; if (field) { field.readOnly = false; }';
			$this->page->onload( $js );
			$this->jsSetClass($fieldId, 'mTextField');
		}
	}

	
	/**
	 * Change a class of a field
	 *
	 * @param $fieldId the id of the field
	 * @param $class the class to change
	 */
	public function jsSetClass($fieldId, $class)
	{
		$js =  'element = document.getElementById(\''.$fieldId.'\'); if (element) element.className = \''.$class.'\';';
		$this->page->onLoad($js);
	}

	
	/**
	 * Define the inner content of an html element
	 *
	 * @param string $fieldId the id of the field
	 * @param string $inner the contente to put
	 */
	public static function jsSetInner($fieldId, $inner)
	{
        $MIOLO = MIOLO::getInstance();

		//aspas e linhas novas para o javascript
		$inner = str_replace("\n", '\n', $inner );
		$inner = str_replace("'", "\'", $inner );
		$MIOLO->page->onLoad('field = dojo.byId(\''.$fieldId.'\') ; if (field) { field.innerHTML = \''.$inner.'\'; } ');
	}

	
	/**
	 * Show a hided field (set display block)
	 *
	 * @param string $fieldId the id of the field to showed
     * * @param boolen to show the label of checkbox field
	 */
	public function jsShow( $fieldId, $label = null )
	{
        $label = $label ? 'true' : 'false';
        $this->page->onLoad( "gnuteca.setDisplay( '{$fieldId}', {$label}, 'block' )" );
	}

	
	/**
	 * Hides a field (set display none)
	 *
	 * @param string $fieldId the id of the field to hide
     * @param boolen to hide the label of checkbox field
	 */
	public function jsHide($fieldId, $label = null )
	{
        $label = $label ? 'true' : 'false';
        $this->page->onLoad( "gnuteca.setDisplay( '{$fieldId}', {$label}, 'none' )");

	}

	
	/**
	 * Disable a field
	 *
	 * @param string $fieldId
	 * @param boolean $disabled to disable or not (not means enabled)
	 */
	public function jsDisabled( $fieldId, $disabled = true )
	{
		if ( $disabled )
		{
			$disabled = 'true';
		}
		else
		{
			$disabled = 'false';
		}

		$this->page->onload('element = document.getElementById(\''.$fieldId.'\'); if ( element) { element.disabled = '.$disabled.';}');
	}

	
	/**
	 * Enabled a disabled field
	 *
	 * @param string $fieldId
	 */
	public function jsEnabled( $fieldId)
	{
		$this->jsDisabled($fieldId, false);
	}

	
	/**
	 * Verifica se é um novo cadastro
	 *
	 *     Este método foi reescrito pois esta inconsistente.
	 *     O Correto mesmo é verificar o evento, como esta na variavel $clickNew
	 *
	 */
    public static function primeiroAcessoAoForm()
    {
        $MIOLO      = MIOLO::getInstance();
    	$args 		= (object) $_REQUEST;

        $firstTime	= isset($args->__EVENTTARGETVALUE);
        $event      = $MIOLO->page->getFormId() . '__EVENTTARGETVALUE';
        $clickNew   = $args->$event == 'tbBtnNew:click';

        return ( $firstTime || $clickNew );
    }

    /**
     * Enter description here...
     *
     */
	public function keyDownHandler()
	{
		$array                        = func_get_args(); //pega os parametros passados na funï¿½ï¿½o
        $this->keyDownHandlerArray    = $array;

		//monta array javascript
		$string = null;

		if ( is_array( $array ) )
		{
			foreach ($array as $line => $info)
			{
				$string .= '"'.$info.'",';
			}

			$string     = substr($string,0, strlen($string)-1);
			$this->page->onload("codes = new Array($string);");
		}

		$this->page->onload("frm = '{$this->page->getFormId()}';");
	}

	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $args
	 */
	public function onkeydown112($args=NULL) //F1 help
	{
		$this->help($args);
	}

	
	/**
	 * Function called when user clicks F2
	 *
	 */
	public function onkeydown113()
	{
        if(!$this->displayToolbar())
        {
            return ;
        }

		$MIOLO  = MIOLO::getInstance();
		$module = MIOLO::getCurrentModule();
		$action = MIOLO::getCurrentAction();
		$formId = $this->page->getFormId();

		$opts[$formId.'__EVENTTARGETVALUE'] = 'tbBtnNew:click';
		$opts['function'] = 'insert';

		$url = $MIOLO->getActionURL($module, $action, null, $opts);
		$MIOLO->page->redirect($url);
	}

	
	/**
	 * Evento acionado ao pressionar botão de salvamento F3
	 *
	 */
	/*public function onkeydown114() //F3 save
	{
		$MIOLO      = MIOLO::getInstance();
		$formId     = $this->page->getFormId();
		$function   = MIOLO::_REQUEST('function');
        
		if ( $function == 'insert' || $function == 'update' )
		{
			$this->page->onload('miolo.doPostBack(\'tbBtnSave:click\',\'\',\''.$formId.'\');');
		}

        $this->setResponse('','limbo');
	}

	
	/**
	 * Enter description here...
	 *
	 */
	public function onkeydown115() //F4 delete
	{
            $MIOLO      = MIOLO::getInstance();
            $formId     = $this->page->getFormId();
            $function   = MIOLO::_REQUEST('function');
            
            if ( $function == 'search' || $function == 'update'|| $function == '' )
            {
                $this->page->onload( GUtil::getAjax('tbBtnDelete:click') );
            }

            $this->setResponse('','limbo');
	}

	
	/**
	 * Enter description here...
	 *
	 */
	public function onkeydown116() //F5 search
	{
		if(!$this->displayToolbar())
		{
            return;
		}

		$MIOLO  = MIOLO::getInstance();
		$module = MIOLO::getCurrentModule();
		$action = MIOLO::getCurrentAction();
		$formId = $this->page->getFormId();

		$opts[$formId.'__EVENTTARGETVALUE'] = 'tbBtnSearch:click';
		$opts['function'] = 'search';

		$url = $MIOLO->getActionURL($module, $action, null, $opts);
		$function   = MIOLO::_REQUEST('function');
                $MIOLO->page->onload("gnuteca.doLink('$url');");
                $this->setResponse(null, 'limbo');
            }

	
	/**
	 * Enter description here...
	 *
	 */
	public function onkeydown117() // F6 print
	{
        $this->setResponse('','limbo');
	}

	
	/**
	 * Enter description here...
	 *
	 */
	public function onkeydown118() //F7 clear
	{
		$formId = $this->page->getFormId();
		$this->page->onload('javascript:gnuteca.clearForm();');

        $this->setResponse('','limbo');
	}

    /**
     * Define o foco caso não tenha sido definido
     *
     * Retorna a string para o httml
     *
     * @return <string> o httml
     *
     */
    public function generate()
    {
        $MIOLO  = MIOLO::getInstance();
        $page   = $MIOLO->getPage();

        //FIXME o gnuteca usa validação própria, então removemos o script de validação do miolo
       /* $url    = $MIOLO->getConf('home.url').'/scripts/m_validate.js' ; #url do javascript de validação
        $index  = $page->scripts->find($url); //localiza script na lista de scripts

        if ( $index || $index === 0 )
        {
            $page->scripts->delete($index); //remove-o: isso evita adicionar javascript não necessário no miolo
        }*/
      
        $focus = $this->findFirstFocus( $this->fields );
        $this->setFocus( $focus  );

        //atualiza a navbar somente no primeira acesso ao form
        if ( !$this->page->isPostBack() || $this->forceCreateFields || $this->getEventAjax() == 'edit:click' )
        {
            global $navbar;
            $this->setResponse( $navbar, 'navbar');
        }

        //define o campo escondido de modo de função
        $functionMode = $this->getField('functionMode');
        
        if ( $functionMode )
        {
            $functionMode->setValue( $this->getFormMode() );
        }

        $this->setModified(false); //limpa modificação pelo usuário

        return parent::generate();
    }

    
    /**
     * Encontra o primeiro campo que pode receber o foco
     *
     * @param <array> $fields array de campos, se passar nulo é pego os campos do formulário
     * @return <string> id do campo que deve receber o focos
     */
    public static function findFirstFocus($fields = null)
    {
        //passa pelos campos procurando um campo para definir o foco, somente caso não tenha sido definido
        if ( is_array( $fields ) && ! GForm::$gFocus )
        {
            foreach  ($fields as $line => $field )
            {
                //vale lembrar que MTextField, pegando os que extende esta classe também como o MCalandarField
                if ( ( $field instanceof MTextField || $field instanceof MSelection) && !$field->readonly && !$field instanceof MHiddenField )
                {
                    return $field->name;
                }

                //procura dentro do container caso necessário
                if ( $field instanceof MContainer && !$field instanceof MToolBar )
                {
                     $focus = self::findFirstFocus( $field->getControls());

                     //só retorna se encontrou o focus
                     if ( $focus )
                     {
                         return $focus;
                     }
                }

                //procura dentro de um array caso o seja
                if ( is_array($field))
                {
                    return self::findFirstFocus($array);
                }
            }
        }
    }


    /**
     * Trigger para identificar evento de geracao PDF na grid (tratado no GGrid)
     *
     * @param unknown_type $args
     */
    public function generateGridPdf($args)
    {
        $this->searchFunction($args);
    }

    
    /**
     * Trigger para identificar evento de geracao CSV na grid (tratado no GGrid)
     *
     * @param unknown_type $args
     */
    public function generateGridCSV($args)
    {
        $this->searchFunction($args);
    }

    
    /**
     * Verificacao para corrigir bug de ordenacao/filtro
     *
     */
    public function checkOrderEvent()
    {
        $filter  = MIOLO::_REQUEST('__filter');
        $order = MIOLO::_REQUEST('orderby');

        if (isset($filter) || isset($order))
        {
            $this->setResponse('', 'divResponse');
            return true;
        }
        
        return false;
    }

    
    public function getFrmLogin( $loginType = LOGIN_TYPE_USER_AJAX )
    {
        if (!$this->frmLogin)
        {
            $args->loginType = LOGIN_TYPE_USER_AJAX;
            $this->frmLogin  = $this->MIOLO->getUI()->getForm($this->module, 'FrmLogin', $args);
        }
    
        return $this->frmLogin;
    }

    
    public static function isGenerateDocumentEvent()
    {
        $event = strtoupper(GForm::getEvent());
        return  (($event == 'GENERATEGRIDPDF') || ($event == 'GENERATEGRIDCSV'));
    }
    
    
    /**
     * Método reescrito para funcionar com ajax
     * 
     * @param (boolean) quando true, os dados das RepetitiveFields vem filtrados, com dados dos seus próprios controles
     */
    public function getData($filterRepetitiveFields = false)
    {
    	$data = parent::getData();

    	if (sizeof((array)$data) == 0)
    	{
            //FIXME encontrar onde o código é necessário, após isso, poderá ser removido
            /*if (!$this->manager->isAjaxEvent)
            {
            	return null;
            }*/
            $data = (object) $_REQUEST;
            $trashes = array(
                'PHPSESSID',
                '__MIOLOTOKENID',
                '__ISAJAXEVENT',
                '__ISAJAXCALL',
                '__FORMSUBMIT',
                'frm__mainForm__FORMSUBMIT', //miolo25 do gnuteca32
                'frm__mainForm__EVENTARGUMENT',
                '__mainForm__EVENTARGUMENT',
                'frm__mainForm__ISPOSTBACK',
                '__mainForm__ISPOSTBACK',
                'cpaint_response_type',
                'frm__mainForm__VIEWSTATE',
                '__mainForm__VIEWSTATE',
                'keyCode',
                'module',
                'action',
                'frm__mainForm__EVENTTARGETVALUE',
                '__mainForm__EVENTTARGETVALUE',
                'function',
                '__ISFILEUPLOAD',
                '__THEMELAYOUT' );
            
            //tira o lixo do getData
            foreach( $data as $key=>$value)
            {
            	if ( in_array($key, $trashes) )
            	{
            		unset($data->$key);
            	}
            	elseif( preg_match('/frm.*_action/', $key, $found) )
            	{
                    unset($data->$found[0]);
            	}
            }
    	}

        //getData das GRepetitiveField
    	$session = $this->MIOLO->getSession();
        $repetitives = array_keys($session->getValue('GRepetitiveField') ? $session->getValue('GRepetitiveField') : array() );
        
        if ( is_array($repetitives) )
        {
            foreach ( $repetitives as $i => $repetitive )
            {
                //FIXME em algum ligar está setando vazio na sessão
                if ( strlen($repetitive) > 0 )
                {
                    //faz getData da repetitiveField
                    if (!$filterRepetitiveFields)
                    {
                        $data->$repetitive = GRepetitiveField::getData($repetitive); //getData normal da repetitive, todos os valores do form
                    }
                    else
                    {
                        $data->$repetitive = GRepetitiveField::getDataOnlySelfControls($repetitive); //getData com dados filtrados, somente dos controls da repetitive em questão
                    }

                    $_REQUEST[$repetitive] = $data->repetitive; //joga dados da repetitiveField no $_REQUEST
                }
            }
        }
        
    	return $data;
    }

    
    /**
     * Para funcionar os campos de dicionário,
     * foi adicionada no gnuteca form pois os includes não funcionavam corretamente;
     * FIXME aguardar uma posição da equipe do miolo quando a eventos ajax em componentes
     */
    public function onkeyUpDictionary($args)
    {
        GDictionaryField::onkeyUpDictionary( (object) $_REQUEST );
    }

    /**
     * Post de upload de arquivo. Função criada para suportar upload de arquivo;
     * FIXME aguardar uma posição da equipe do miolo quando a eventos ajax em componentes
     *
     * @return void
     */
    public function addFile_click( $args )
    {
        return GFileUploader::addFile( );
    }

    /**
     * Monta um diálogo de confirmação de troca de estado do workflow. Solicitado comentários.
     *
     * @param stdClass $args
     */
    public function changeWorkflowStatus( $args )
    {
        $args = GUtil::decodeJsArgs( $args );
        $this->manager->getClass('gnuteca3', 'GWorkflow');
        $possibleFutureStatus = GWorkFlow::getFutureStatus($args->workflowId, $args->tableName, $args->worflowTableId);

        //segurança contra hackeio de parâmetros
        if ( is_array( $possibleFutureStatus  ) )
        {
            foreach ( $possibleFutureStatus as $key => $status )
            {
                if ( $status->nextWorkflowStatusId == $args->nextWorkflowStatusId)
                {
                    $futureStatus = $status;
                }
            }
        }
        else
        {
            //caso não encontre o estado futuro
            throw new Exception( _M('Impossível encontrar estado futuro!') );
        }

        //caso não encontre o estado futuro
        if ( !$futureStatus )
        {
            throw new Exception( _M('Impossível encontrar estado futuro!') );
        }

        $fields[] = new MSpan('',_M('Observações/Motivo','gnuteca3').':');
        $fields[] = $comment = new MMultiLineField( 'comment', null, null, 15, 10, 15 ,null);
        $comment->addStyle('width', '98%');
        $this->setFocus('comment');

        $action = Gutil::getAjax( 'confirmChangeWorkflowStatus' , $args );
        $buttons[] = new MButton( 'btnYes', _M('Trocar estado','gnuteca3'), $action, Gutil::getImageTheme('accept-16x16.png') );
        $buttons[] = GForm::getCloseButton();

        $fields[] = new MDiv('',$buttons);

        $this->injectContent( $fields, false , _M('Confirmar troca de estado', 'gnuteca3') .' - '. $futureStatus->name, '500px');
    }

    /**
     * Executa o salvamento do formulário e a troca de estado.
     *
     * @param stdClass $args
     */
    public function confirmChangeWorkflowStatus( $args )
    {
        $this->tbBtnSave_click( );
        $args = GUtil::decodeJsArgs( $args );
        $args->comment = MIOLO::_REQUEST('comment');

        $this->manager->getClass('gnuteca3', 'GWorkflow');
        $possibleFutureStatus = GWorkFlow::getFutureStatus($args->workflowId, $args->tableName, $args->worflowTableId);
        $ok = GWorkFlow::changeStatus( $args->workflowId, $args->tableName, $args->worflowTableId , $args->nextWorkflowStatusId, $args->comment);

        $opts = array( 'function'=>'search' , 'pn_page' => 1 );
        $opts = array_merge( $opts, $this->getOpts(true, true) );
        $goto = $this->MIOLO->getActionURL($this->module, $this->_action, null, $opts );

        $this->information( 'Troca de estado efetuado com sucesso!', $goto );
    }

    /**
     * Executa o salvamento do formulário e instancia workflow
     *
     * @param stdClass $args
     */
    public function createWorkflow( $args )
    {
        $this->tbBtnSave_click( );
        $args = GUtil::decodeJsArgs( $args );
        $args->comment = MIOLO::_REQUEST('comment');

        $this->manager->getClass('gnuteca3', 'GWorkflow');
        $instance = GWorkFlow::instance( $args->workflowId, $args->tableName, $args->worflowTableId);

        $opts = array( 'function'=>'search' , 'pn_page' => 1 );
        $opts = array_merge( $opts, $this->getOpts(true, true) );
        $goto = $this->MIOLO->getActionURL($this->module, $this->_action, null, $opts );

        $this->information( 'Workflow iniciado com sucesso!', $goto );
    }

    /**
     * Mostra histórico do workflow.
     *
     * @param stdClass $args
     */
    public function workflowHistory( $args )
    {
        $args = GUtil::decodeJsArgs( $args );
       
        $this->manager->getClass('gnuteca3', 'GWorkflow');
        $history = GWorkFlow::getHistory($args->workflowId, $args->tableName, $args->worflowTableId ? $args->worflowTableId : $args->tableId );
        
        if ( is_array( $history ) )
        {
            foreach ( $history as $key => $item )
            {
                $table[$key] = array($item->date, $item->operator, $item->statusName, $item->comment);
            }
        }
        else
        {
            $this->information( _M('Sem histórico para este workflow.','gnuteca3') );
            return true;
        }

        $colTitle[] = _M('Data','gnuteca3');
        $colTitle[] = _M('Operador','gnuteca3');
        $colTitle[] = _M('Estado','gnuteca3');
        $colTitle[] = _M('Comentários','gnuteca3');

        $fields[] = new MTableRaw( null, $table, $colTitle, 'workflowHistory', true);

        $this->injectContent( $fields , true, _M('Histórico da instancia do Workflow @1','gnuteca3',$item->workflowInstanceId ) );
    }

    /**
     * Gera um teste unitário baseado em template e dados do formulário
     *
     * @param stdClass $data dados do formulário
     */
    public function generateUnitTest($data)
    {
        $alias = ucfirst( str_replace(array('bus','Bus'), '', $this->getBusName()) );
        $template = GUtil::generateUnitTest( $this->getData(), $alias );

        $fields[] = $unitTestTemplate = new MMultiLineField('unitTestTemplate', $template, '', 30, 20, 30);
        $unitTestTemplate->addStyle('width','99%');

        $fields[] = new GContainer( '', new MTextField('filePath', "Test{$alias}.class.php" ,_M('Caminho do arquivo','gnuteca3'), FIELD_DESCRIPTION_SIZE ) );

        $buttons[] = new MButton('btnSaveUnitTest', _M('Salvar teste unitário','gnuteca3'), Gutil::getAjax('saveUnitTest'), Gutil::getImageTheme('save-16x16.png'));
        $buttons[] = GForm::getCloseButton();

        $fields[] = new MDiv('',$buttons);

        $this->injectContent( $fields, false, _M('Geração de teste unitário','gnuteca3') );
    }

    /**
     * Salva teste unitário no servidor
     *
     * @param stdClass $data dados do formulário
     */
    public function saveUnitTest($data)
    {
        $serverPath = $this->MIOLO->getConf('home.modules').'/gnuteca3/unittest/';

        $filePath = $serverPath . $data->filePath;

        $alias = ucfirst( str_replace(array('bus','Bus'), '', $this->getBusName()) );
        $fileContent = $data->unitTestTemplate;

        if ( is_writable($serverPath ) )
        {
            $ok = file_put_contents( $filePath, $fileContent );

            if ( $ok )
            {
                chmod($filePath, 0777 );
                $this->information( _M('Teste unitário salvo com sucesso no caminho: @1', 'gnuteca3',$filePath));
            }
            else
            {
                throw new Exception ( _M('Erro ao salvar teste unitário no caminho: @1', 'gnuteca3',$filePath) );
            }
        }
        else
        {
            throw new Exception ( _M('Sem permissão para gravar no caminho: @1', 'gnuteca3',$filePath) );
        }
    }

    /**
     * Executa o teste unitário padrão do formulário
     */
    public function executeUnitTest()
    {
        $alias = ucfirst( str_replace(array('bus','Bus'), '', $this->getBusName()) );
        $result = GUtil::executeUnitTest( 'Test'.$alias.'.class.php', true );

        $this->injectContent( nl2br($result[1]) , true, _M('Resultado do teste unitário','gnuteca3') );
    }
}
?>
