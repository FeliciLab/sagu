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
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * 
 * @since
 * Class created on 13/09/2011
 *
 **/

class FrmHelp extends GForm
{

    public $MIOLO;
    public $module;
    public $busFile;
    public $fields;
    public $subFormHolder;
    public $editor;

    public function __construct()
    {
        $this->setAllFunctions('Help', null, 'helpId', 'helpId');

        $this->module = MIOLO::getCurrentModule();
        $this->MIOLO = MIOLO::getInstance();
        $this->busFile = $this->MIOLO->getBusiness($this->module, 'BusFile');
        $this->editor = new GEditor( '_hP', '',_M('Ajuda', $this->module));
        parent::__construct();
    }

    public function mainFields()
    {
        if ( MIOLO::_REQUEST('function') != "insert")
        {
            $this->fields[] = new MTextField('helpId', null, _M('Código',$this->module), FIELD_ID_SIZE,null,null,true);
        }
        
        $this->fields[] = $form = new GSelection('_form', null, _M('Formulário', $this->module), $this->busFile->listForms(), null, null, null, TRUE);
        $form->addAttribute('onChange', GUtil::getAjax('changeForm'));
        
        if ( MIOLO::_REQUEST('function') == "update" )
        {
            $form->setReadOnly(true);
        }
        
        if ( MIOLO::_REQUEST('_form') && MIOLO::_REQUEST('__subForm') )
        {
            $this->fields[] = $this->getSubFormField(MIOLO::_REQUEST('_form'), MIOLO::_REQUEST('__subForm'));
        }
        else
        {
            $this->subFormHolder = new MDiv('divSubForm' ,'' ); //Adiciona div que vai ficar esperando loadFields ou changeForm para verificar se é para passar o campo subForm para dentro dela
            $this->fields[] = $this->subFormHolder;            
        }
        
        $this->fields[] = $isActive = new GRadioButtonGroup('isActive', _M('Ativo', $this->module) , GUtil::listYesNo(1), DB_TRUE );
        $this->fields[] = $editor = $this->editor;
        $editor->addCustomButton(_M('Adicionar ajuda de campo', $this->module), GUtil::getAjax('selectHelpField'), GUtil::getImageTheme('fieldLink-16x16.png') );

        $this->setFields($this->fields);

        $validators[] = new MRequiredValidator('_form', _M('Formulário', $this->module));
        $validators[] = new MRequiredValidator('isActive', _M('Ativo', $this->module));
        $validators[] = new MRequiredValidator('help', _M('Ajuda', $this->module));
        
        $this->setValidators($validators);
    }
    
    /**
     * Função AJAX que mostra campo do subForm se a opção de formulário escolhida
     * for FrmSimpleSearch
     *
     * @param stdClass $args
     */
    public function changeForm ($args)
    {
        $fields = $this->getSubFormField($args->_form);
        $this->setResponse($fields, 'divSubForm');
    }

    /**
     * Esta função retorna o selection de subformulário caso a opção FrmSimpleSearch.class.php
     * do selection de formulários
     * 
     * @param string $selectedForm
     * @param string $selectedSubForm
     * @return MDiv
     */
    public function getSubFormField($selectedForm = null, $selectedSubForm = null)
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( $selectedForm == 'FrmSimpleSearch' ) //Se formulário pesquisa simples selecionado
        {
            $subForms = $this->busFile->listForms(true);
            
            //busca as pesquisas do admin
            $busFormContent = $MIOLO->getBusiness('gnuteca3', 'BusFormContent');
            $busFormContent->formContentTypeS = FORM_CONTENT_TYPE_ADMINISTRATOR;
            $search = $busFormContent->searchFormContent(true);
            
            if ($search)
            {
                foreach ($search as $v)
                {
                    $subForms[$v->formContentId] = 'Frm' . $v->name;
                }
            }

            //Mostra o selection de subformulários.
            $lblSubForm = new MLabel(_M('Subformulário', $this->module).":");

            //Se for update
            if ( MIOLO::_REQUEST('function') == "update" )
            {
                //Cria um campo invisivem com dado que será salvado no banco. (Não está funcionando utilizar apenas um GSelection.)
                $subFormHidden = new MTextField('__subForm', null, _M('Subformulário', $this->module), FIELD_ID_SIZE, null, null, true);
                $subFormHidden->setVisibility(false);
                $subFormHidden->setValue($selectedSubForm);                            
                //Cria campo visivel não editável apenas para mostrar o texto com o valor do subformulário.
                $subFormVisible = new GSelection('subFormHidden', null, _M('Subformulário', $this->module), $subForms, null, null, null, true);
                $subFormVisible->setReadOnly(true);
            }
            else //Se for inserção não precisa fazer workaround nenhum.
            {
                $subFormVisible = new GSelection('__subForm', null, _M('Subformulário', $this->module), $subForms, null, null, null, true);
            }

            $subFormVisible->setValue($selectedSubForm);            
            $divSubForm = new MDiv('subFormField', array($lblSubForm,$subFormVisible,$subFormHidden));
        }

        return $divSubForm;
    }
    
    public function getData ()
    {
        $data = parent::getData();
        
        $data->form = $data->_form;
        $data->subForm = $_REQUEST['__subForm'];
        $data->help = $data->_hP;

        return $data;
    }

    public function loadFields()
    {
        parent::loadFields();

        $this->business->_form = $this->business->form;
        $this->business->__subForm = $this->business->subForm;
        $this->business->_hP = $this->business->help;
        $this->subFormHolder->setInner( $this->getSubFormField($this->business->form, $this->business->subForm) ); //Caso o formulário seja FrmSimpleSearch , adiciona o campo subformulário no carregamento da edição do registro.

        $this->setData($this->business);

    }

    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        
        parent::tbBtnSave_click($sender, $data, $errors);
    }
    
    /**
     * Seleciona campo do qual será adicionado a ajuda
     * 
     * @param stdClass ajaxArgs 
     */
    public function selectHelpField($args)
    {
        $MIOLO = MIOLO::getInstance();
        $form = $args->_form; 
        if ( strlen($form) == 0 )
        {
            throw new Exception( _M('É necessário selecionar um formulário', 'gnuteca3'));
        }
        elseif ( $form == 'FrmHelp' || $form == 'FrmSimpleSearch' || $form == 'FrmAdminReport' ) //FIXME: da erro do miolo quando adicionado para este formulário
        {
            $this->information(_M('Esta interface não possui suporte para adicionar ajuda nos campos', 'gnuteca3'));
            return;
        }
        
        $objectForm = $MIOLO->getUI()->getForm('gnuteca3', $form);
        $objectForm->mainFields();
        
        $fields = $objectForm->fields;
        $controlFields = array();
        if ( is_array( $fields) )
        {
            foreach( $fields as $field )
            {
                //se não for campo escondido e toolbar
                if ( ($field instanceof MInputControl ) && ( !($field instanceof MHiddenField) ) && ( !($field instanceof MButton) ) && (( !($field instanceof GToolBar) )) )
                {
                    $controlFields[$field->id] = strlen($field->label) > 0 ? $field->label : $field->id;
                }
            }
        }
        
        $controls[] =  $fieldId = new GSelection('fieldId',null, _M('Campo','gnuteca3'), $controlFields);
        $fieldId->addAttribute('onChange', "dojo.byId('fieldLabel').value = this.options[this.selectedIndex].text;");
        $controls[] = $label =  new MTextField('fieldLabel');
        $label->addStyle('display', 'none');
        
        $content = GUtil::alinhaForm($controls);
        $content[] = new MSeparator();
        
        $buttons = array();
        $buttons[] = GForm::getCloseButton();
        $buttons[] = new MButton( 'btnApply', _M('Aplicar','gnuteca3'), ':addHelpField', Gutil::getImageTheme('accept-16x16.png') );
        $content[] = new MDiv('divButton', $buttons);

        GForm::injectContent( $content, false, _M('Selecionar campo', 'gnuteca3') );
    }
    
    /**
     * Adiciona o label no GEditor para que conteúdo seja editado
     * @param stdClass ajaxArgs 
     */
    public function addHelpField($args)
    {
        if ( strlen($args->fieldId) == 0 )
        {
            throw new Exception( _M('Informe um campo válido', 'gnuteca3'));
        }
        
        $js = "javascript:CKEDITOR.instances['_hP'].insertHtml('<div id = \'h{$args->fieldId}\' > #{$args->fieldLabel}:</div>'); //insere conteúdo HTML no editor
                          meditor.connection['_hP'] = dojo.connect(miolo.webForm, 'onSubmit', function () //função para fazer o connect
                                                      { 
                                                          CKEDITOR.instances['_hP'].updateElement(); 
                                                          return true;
                                                      });". 
                          GUtil::getCloseAction();
        $this->page->onload($js);
        
        $this->setResponse(null, 'limbo');
    }
    
}
?>
