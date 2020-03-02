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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
class GSubForm
{
    const DIV_SEARCH = 'divSearchSub';
    
    //protected $name;
    /**
     * Listagem de campos do formulário (principais)
     * @var array
     */
    protected $fields;
    /**
     * Atalho para o miolo
     * @var MIOLO
     */
    protected $manager;
    /**
     * Modulo do projeto
     *
     * @var string
     */
    protected $module ='gnuteca3';
    /**
     * Listagem de validadores
     *
     * @var array
     */
    protected $validators;
    /**
     * Título do formulário
     * @var string
     */
    private $title;
    /**
     * Nome do arquivo da grid
     *
     * @var string
     */
    protected $gridName;
    /**
     * Metodo de pesquisa
     *
     * @var string
     */
    protected $gridSearchMethod;
    /**
     * Dados passados para a grid
     *
     * @var stdClass
     */
    protected $gridParams;

    public function __construct( $title )
    {
        try
        {
            $this->title   = $title;
            $this->manager = MIOLO::getInstance();
            $this->module  = 'gnuteca3';

            $this->subFormEventHandler(); //controle de eventos
            $this->manager->page->onload('dojo.parser.parse();'); //para calendar e outros campos dojo

            //caso o metodo checkAccess existir verifica permissão
            if ( method_exists( $this, 'checkAcces') )
            {
                if ( !$this->checkAcces() )
                {
                    throw new Exception( _M('Sem permissão para acessar esse formulário','gnuteca3') );
                }
            }
            else
            {
                throw new Exception( _M('Formulário sem transação definida é necessário definir uma transação de segurança! Contate o administrador!','gnuteca3') );
            }
        }
        catch ( Exception $e )
        {
            GForm::error( $e->getMessage() );
        }
    }
    
    /**
     * A autenticação padrão da minha biblioteca, estar logado.
     *
     * @return boolean
     */
    public function checkAcces()
    {
        return BusinessGnuteca3BusAuthenticate::checkAcces();
    }

    /**
     * Verifica se o formulário precisa de login do usuário pesquisador/aluno
     *
     * isUserLoginNeeded = é necessário login do usuário
     * 
     * Os evals 
     *
     * @return boolean
     */
    public static function isUserLoginNeeded( $className = null )
    {
        if ( !$className )
        {
            return true;
        }
        else
        {
            //e na 5.3 assim, precisa ser com eval, pois de outra forma resulta em parser_error na versão 5.2
            eval('$ok = '.$className.'::isUserLoginNeeded();');
                
            return $ok;
        }
    }
    
    /**
     * Gerencia os eventos do subformulário
     */
    public function subFormEventHandler()
    {
        $event = $this->getEvent();
        $action = MIOLO::getCurrentAction();

        if ( !$event || $event == 'subForm' )
        {
            $this->createFields();
        }
        else
        {
            if ( method_exists($this, $event) )
            {
                $this->$event( GUtil::getAjaxEventArgs() );
            }
        }
    }

    /**
     * Valida o formulário, caso necessário
     *
     * @return boolean false caso existam erros de validação
     */
    public function validate()
    {
        $validators = $this->getValidators();

        $js = "gnuteca.cleanValidatorsMessage();";

        if ( $validators )
        {
            $gValidator = new GValidators();
            $gValidator->setValidators($validators);
            $errors = $gValidator->validate( );

            if ( $errors && is_array( $errors ) )
            {
                foreach ( $errors as $fieldid => $msg )
                {
                   $js .= "gnuteca.addValidatorMessage('$fieldid','$msg');";
                }

                $this->manager->page->onload($js);
                $firstError = array_keys($errors);

                $js = GUtil::getCloseAction( true ) . " gnuteca.setFocus('{$firstError[0]}'); ";
    			GForm::error( implode('<br>', $errors) , $js , _M( 'Validação', $this->module ) );

                return false;
            }
        }

        return true;
    }

    /**
     * Define o título do subformulário
     * 
     * @param string $title 
     */
    public function setTitle( $title )
    {
        $this->title = $title;
    }

    /**
     * Obtem o tipo do subformulário
     *
     * @return string o tipo do subformulário
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Define os validadores
     * @param array $validators
     */
    public function setValidators( array $validators )
    {
        $this->validators = $validators;
    }

    /**
     * Obtem os validadores do formulário
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Define os campos do formulário
     *
     * @param array $fields o array com os campos
     * @param boolean $isSearchMode caso modo de busca, algumas funcionalidades são automáticas
     */
    public function setFields($fields, $isSearchMode = false)
    {
        $fields = GForm::accessibility( $fields );

        //caso seja modo de busca adiciona botões e divs necessárias
        if ( $isSearchMode )
        {
            $fields[] = $this->getBtnSearch();
            $fields[] = new MSeparator();
            $fields[] = new MDiv( self::DIV_SEARCH );
        }

        //cria o título e o botão de ajuda
        if ( $this->title )
        {
            $title[] = new MSpan('subFormTitle', $this->title, 'subFormTitle');
            
            $busHelp = MIOLO::getInstance()->getBusiness('gnuteca3', 'BusHelp');
            
            //só mostra botão de ajuda se tiver ajuda cadastrada
            if ( $busHelp->getFormHelp('FrmSimpleSearch', get_class($this)) instanceof stdClass )
            {
                $title[] = $divAjudaEx = new MDiv( 'divAjudaEx', "<a id='divAjuda' href='javascript:gnuteca.help();' onclick='gnuteca.help();' alt='Ajuda' title='Ajuda' style='margin-top:-30px;'></a>");
            }
         
            $upperFields[] = new MDiv('upperFields', $title);

            $fields = array_merge( $upperFields, $fields );
        }

        $this->fields = $fields;

        //coloca * nos campos requeridos
        $validators = $this->getValidators();

        //coloca * nos campos com validadores
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
     * Retorna um array com os campos do formulário
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Obtem o campo especifico do formulário
     *
     * @param string $fieldId
     * @return MControl
     */
    public function getField( $fieldId )
    {
        return Gutil::getField($fieldId, $this->fields);
    }

    /**
     * Função padrão de ajuda
     * 
     * @param stdClass $args
     */
    public function help($args)
    {
        GForm::help($args);
    }

    /**
     * Esta função foi feita para podermos obter o botão correto nos formulários fora do padrão.
     * 
     * @return MDiv com o botão de ajuda dentro
     */
    public function getBtnSearch()
    {
        $btnSearch = new MButton('btnSearch', _M('BUSCAR', $this->module), ':searchFunctionSub', GUtil::getImageTheme('search-16x16.png'));
        $btnSearch = new MDiv('btnSearchEx', $btnSearch );
        
        return $btnSearch;
    }

    public function eventHandler()
    {
        //does nothing
    }

    public function addJsCode($js)
    {
        $this->manager->page->addJsCode($js);
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

    public function searchFunction()
    {
        GForm::corrigeEventosGrid( GUtil::getAjaxEventArgs() );
        $this->searchFunctionSub();
    }

    public function searchFunctionSub()
    {
        $this->validate();
        $this->setResponse( $this->getGrid(), self::DIV_SEARCH );
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

    public function setResponse($controls, $element)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->ajax->setResponse( $controls, $element );
    }

    //verificar forma melhor e menos hardocde de recarregar tela atual
    public function getCloseAndReloadAction()
    {
        $subForm = FrmSimpleSearch::getCurrentSubForm();
        return GUtil::getCloseAction(true) . ';' . GUtil::getAjax('subForm', $subForm);
    }

    /**
     * Obtem objeto da grid, ja devidamente populado com dados caso necessario
     *
     * @return MGrid
     */
    public function getGrid()
    {
        $data   = $this->getData();
        $grid   = $this->getGridObject();
        
		if ( $grid && ($data->__FORMSUBMIT) || ( MIOLO::_REQUEST($this->manager->page->getFormId() . '__FORMSUBMIT') ) )
		{
            $this->business->setData( $data );
            $gridData = $this->getGridData();

            if ( $gridData )
            {
                $grid->setData( $gridData );
            }
            else
            {
                GForm::information( _M('Registros não encontrados!', 'gnuteca3' ) );
                $grid->emptyMessage = '';
            }
		}

		return $grid;
    }

    /**
     * Retorna os dados do post/formulário
     *
     * @return stdClass
     */
    public function getData()
    {
        return (object)$_REQUEST;
    }

    /**
     * Obtem dados da grid por consulta no banco
     *
     * @return array
     */
    public function getGridData()
    {
        if (method_exists($this->business, $this->gridSearchMethod))
        {
            $result = call_user_method($this->gridSearchMethod, $this->business);
        }

        return $result;
    }

    /**
     * Obtem objeto da grid do MIOLO
     *
     * @return MGrif
     */
    public function getGridObject()
    {
        if ( $this->module && $this->gridName)
        {
            return $this->manager->getUI()->getGrid($this->module, $this->gridName, $this->gridParams);
        }

        return null;
    }

    /**
     * Verifica se a tela esta sendo acessada pela primeira vez
     * 
     * @return (boolean)
     */
    public function firstAccess()
    {
    	return GUtil::getAjaxFunction() == 'subForm';
    }

    /**
     * Mostra novidades dentro do subformulário
     */
    public function showNew()
    {
        FrmSimpleSearch::showNew();
    }
    
    /**
     * Método de autenticação chamado pelo login do banner
     */
    public function btnLoginUpper_click()
    {
        FrmSimpleSearch::btnLoginUpper_click((object) $_REQUEST, true);
        $this->setResponse(null, 'limbo');
    }
}
?>