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
 */
class GToolBar extends MToolbar
{
    const BUTTON_FORMCONTENT = 'btnFormContent';
    const BUTTON_WORKFLOW = 'btnWorkflow';
    const BUTTON_RELATION = 'btnRelation';
    
    public $formContent = false;
    public $transaction = null;
    protected $workflowId = '';
    protected $worflowTableId = '';
    protected $relations = array();
    /**
     * Objeto do formulário que a criou
     * @var MForm
     */
    protected $form;
    
    public function __construct($name='toolBar', $url = null, $type = MToolbar::TYPE_ICON_ONLY)
    {
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();
        $url    = $url ? $url : MIOLO::getInstance()->getActionURL('gnuteca3', $action);
        $name   = $name ? $name : 'toolBar';

        parent::__construct($name, $url, $type);
        MBaseForm::$showHints = false;

        $this->removeButtons( MToolBar::BUTTON_EXIT );
        $this->removeButtons( MToolBar::BUTTON_PRINT  );
        
        if ( $this->getButton(MToolBar::BUTTON_RESET) )
        {
            $this->getButton(MToolBar::BUTTON_RESET)->setUrl('javascript:gnuteca.clearForm();');
        }
        
        $event = $url . "&{$MIOLO->page->getFormId()}__EVENTTARGETVALUE=" . GToolBar::BUTTON_NEW . ':click';
        $eventURL = $event . '&function=insert';
        $this->getButton(GToolBar::BUTTON_NEW)->setUrl("javascript:gnuteca.doLink('$eventURL');");
        
        $event = $url . "&{$MIOLO->page->getFormId()}__EVENTTARGETVALUE=" . GToolBar::BUTTON_SEARCH . ':click';
        $eventURL = $event . '&function=search';
        $this->getButton(GToolBar::BUTTON_SEARCH)->setUrl("javascript:gnuteca.doLink('$eventURL');");
        
    }
    
    /**
     * Define o formulário que chamou a toolbar,
     * utilizado em algumas condições
     * 
     * @return MForm form
     */
    public function setForm( MForm $form )
    {
        $this->form = $form;
    }
    
    /**
     * Retorna o formulário que chamou a toolbar
     * 
     * @return MForm form
     */
    public function getForm( )
    {
        return $this->form;
    }

    /**
     * Adiciona uma relação.
     * Um item do menu do botão de  relações.
     *
     * @param string $label título
     * @param string $image link da imagem, url
     * @param string $link qualquer função ajax ou não
     * @param string $transaction transação de permissão a verificar
     * @param string $function transação/function de permissão a verificar
     */
    public function addRelation( $label , $image = null , $link = null , $transaction = null, $function = null  )
    {
        if ( $transaction )
        {
            //caso não tenha permissão e a transação esteja definida não permite inserção de relação
            if ( ! GPerms::checkAccess( $transaction, $function, false ) )
            {
                return false;
            }
        }

        $relation = new stdClass();
        $relation->label = $label;
        $relation->image = $image;
        $relation->link = $link;
        
        $this->relations[] = $relation;
    }

    /**
     * Retorna o objeto de um botão;
     *
     * @param string $buttonId, pode usar as constantes de botão
     * @return GToolBar
     */
    public function getButton($buttonId)
    {
        return $this->toolBarButtons[$buttonId];
    }

    public function generate()
    {
        $MIOLO  = MIOLO::getInstance();
		$module = MIOLO::getCurrentModule();
        
        $busHelp = $MIOLO->getBusiness( 'gnuteca3', 'BusHelp');
        $busHelp = new BusinessGnuteca3BusHelp();
        $help = $busHelp->getFormHelp( get_class($this->form) );
       
        if ( $help->help )
        {
            $img = GUtil::getImageTheme('toolbar-help.png' );
            $goto = GUtil::getAjax('help');
            $this->addButton('btnHelp', _M('Ajuda', $this->module), $goto, _M('Acessa a ajuda deste formulário', $module), TRUE, $img, $img);
        }

        //botão gerador de teste unitário
        if ( MUtil::getBooleanValue( $MIOLO->getConf('gnuteca.debug') ) && ( MIOLO::_REQUEST('function') == 'insert' || MIOLO::_REQUEST('function') == 'update')  )
        {
            $img = GUtil::getImageTheme('toolbar-generateUnitTest.png' );
            $this->addButton('btnGenerateUnitTest', _M('Gera teste unitário', $this->module), 'javascript:'.GUtil::getAjax('generateUnitTest'), _M('Gera teste unitário', $module), TRUE, $img,$img);

            $img = GUtil::getImageTheme('toolbar-executeUnitTest.png' );
            $this->addButton('btnExecuteUnitTest', _M('Executa teste unitário', $this->module), 'javascript:'.GUtil::getAjax('executeUnitTest'), _M('Executa teste unitário', $module), TRUE, $img,$img);
        }

        $function = MIOLO::_REQUEST('function');

        if ( $function == 'search' || $function =='detail' || !$function )
		{
            if ( $this->toolBarButtons[MToolBar::BUTTON_SAVE] )
            {
                $this->disableButtons(array(MToolBar::BUTTON_SAVE));
            }

            if ( $this->toolBarButtons[MToolBar::BUTTON_DELETE] )
            {
                //$this->disableButtons(array(MToolBar::BUTTON_DELETE));
            }
        }

		if ( $function == 'insert' && $this->toolBarButtons[MToolBar::BUTTON_DELETE])
		{
			$this->disableButton(MToolBar::BUTTON_DELETE);
        }
        
        $this->addRelationButton();

        $toolBarChanger = GToolBar::toolBarChanger();
        $generate = $toolBarChanger->generate() . "<div id='toolBarContainer' class='toolBarContainer'><table class='toolBarTable'><tr><td>".parent::generate()."</td></tr></table></div>";
       
        MBaseForm::$showHints = true;
 
        return $generate;
    }

    /**
     * adiciona botão de relações caso tenha alguma
     */
    private function addRelationButton()
    {
        //só cria botão se tiver relações e o botão não existir
        if ( is_array($this->relations ) && count( $this->relations ) > 0 && !$this->toolBarButtons[GToolBar::BUTTON_RELATION] )
        {
            $img = GUtil::getImageTheme('toolbar-relation.png');
            $imgD = GUtil::getImageTheme('toolbar-relation-disabled.png');

            $btn = new GToolBarButton( GToolBar::BUTTON_RELATION, _M('Relações', $module), null, null, true, $img, $imgD, NULL, MToolBar::TYPE_ICON_ONLY ) ;
            $menuRelation = new GToolbarMenu('relationMenu');
            
            foreach ( $this->relations as $key => $relation )
            {
                $menuRelation->addItem( $relation->label, $relation->image, $relation->link );
            }

            $btn->addMenu( $menuRelation );
            $this->addCustomButton( $btn );
        }
    }
    
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        //Verifica se tem permissção para inserir o registro
		if ($this->transaction)
		{
			if (!GPerms::checkAccess($this->transaction, 'insert', false))
			{
				$this->disableButton(MToolBar::BUTTON_NEW);
			}
            
			if (!GPerms::checkAccess($this->transaction, 'delete', false))
			{
				$this->disableButton(MToolBar::BUTTON_DELETE);
			}
		}
    }

    public function setFormContent($formContent)
    {
        $this->formContent = $formContent;

        if ( MUtil::getBooleanValue( FORM_CONTENT ) && ( GOperator::isLogged() ) && $formContent )
		{
			$img            = GUtil::getImageTheme('toolbar-config.png' );
            $imgDisabled    = GUtil::getImageTheme('toolbar-config-disabled.png' );
            $goto           = GUtil::getAjax('tbBtnFormContent:click');
            $this->addButton('btnFormContent', _M('Conteúdo do formulário', $this->module), $goto, _M('Salvar conteúdo do usuário', $module), TRUE, $img, $imgDisabled);
		}
    }

    /**
     * Adiciona botão personalizado
     *
     * @param MToolBarButton $button
     */
    public function addCustomButton( MToolBarButton $button )
    {
        $this->toolBarButtons[$button->name] = $button;
    }

    /**
     * Define o código do workflow
     *
     * @param string $workflowId
     */
    public function setWorkflow( $workflowId )
    {
        $this->workflowId = $workflowId;

        if (  GOperator::isLogged() && $workflowId && MIOLO::_REQUEST('function') == 'update' )
		{
			$img  = GUtil::getImageTheme('toolbar-workflow.png' );
            $imgD = GUtil::getImageTheme('toolbar-workflow-disabled.png' );
            $t = GToolBar::BUTTON_WORKFLOW;
            $btn = new GToolBarButton( GToolBar::BUTTON_WORKFLOW, _M('Workflow', $module), null, null, true, $img, $imgD, NULL, MToolBar::TYPE_ICON_ONLY ) ;

            $this->manager->getClass('gnuteca3', 'GWorkflow');
            $instance = GWorkflow::getCurrentStatus( $this->workflowId, $this->transaction, $this->worflowTableId );

            //parametros para as funções ajax
            $linkArgs = new stdClass();
            $linkArgs->workflowId = $this->workflowId;
            $linkArgs->tableName = $this->transaction;
            $linkArgs->worflowTableId = $this->worflowTableId;

            $workflowMenu = new GToolbarMenu( 'toolbarMenuWorkflow');

            if ( $instance )
            {
                $futureStatus = GWorkflow::getFutureStatus( $this->workflowId, $this->transaction, $this->worflowTableId );

                $workflowMenu->addItem( _M( 'Estado atual: @1' ,'gnuteca3', $instance->statusName) ,Gutil::getImageTheme('system-16x16.png'));
                $workflowMenu->addSeparator();
             
                $linkArgs->workflowInstanceId = $instance->workflowInstanceId;

                if ( is_array( $futureStatus ) )
                {
                    foreach ( $futureStatus as $key => $status )
                    {
                        $linkArgs->nextWorkflowStatusId = $status->nextWorkflowStatusId;

                        $workflowMenu->addItem( $status->name, Gutil::getImageTheme('workflow-16x16.png'), 'javascript:'.Gutil::getAjax( 'changeWorkflowStatus', $linkArgs) );
                    }
                }
                else
                {
                    $workflowMenu->addItem( _M('Nenhuma transição possível neste momento.','gnuteca3') );
                }

                $workflowMenu->addSeparator();
                $workflowMenu->addItem( _M('Histórico','gnuteca3'), Gutil::getImageTheme('history.png'),'javascript:'.Gutil::getAjax( 'workflowHistory', $linkArgs) );
            }
            else
            {
                $workflowMenu->addItem( _M('Sem workflow relacionado.','gnuteca3')  );
                $workflowMenu->addItem( _M('Iniciar workflow','gnuteca3'), Gutil::getImageTheme('workflow-16x16.png'), 'javascript:'.Gutil::getAjax( 'createWorkflow', $linkArgs)  );
            }

            $btn->addMenu( $workflowMenu );
            $this->addCustomButton( $btn );
		}
    }

    /**
     * Define o código da tabela relacionada
     *
     * @param string $worflowTableId
     */
    public function setWorflowTableId( $worflowTableId )
    {
        $this->worflowTableId = $worflowTableId;
    }

    /**
     * Remove one or more buttons
     *
     * @param $name (string or array) Button's name
     */
    public function removeButtons($name)
    {
        if ( is_array($name) )
        {
            foreach ( $name as $n )
            {
                unset($this->toolBarButtons[$n]);
            }
        }
        else
        {
            unset($this->toolBarButtons[$name]);
        }
    }

    /**
     * Função que cria o botão e os js necessários para esconder/mostrar a toolbar.
     *
     * Essa função é estática devido a forma de construção da Catalogação.
     * Quando a catalogação form padronizada (Usar o GForm) não será
     * mais necessário esta função ser estática
     * @return MButton
     *
     */
    public static function toolBarChanger()
    {
        $MIOLO = MIOLO::getInstance();

        $imgChangerLeft  = GUtil::getImageTheme('toolBarChangerLeft.png');
        $imgChangerRight = GUtil::getImageTheme('toolBarChangerRight.png');

        $MIOLO->page->onload("//se o estado do changer for none esconde a toolbar
        gnuteca.imgChangerLeft  = '$imgChangerLeft';
        gnuteca.imgChangerRight = '$imgChangerRight';
        gnuteca.hideToolBar(); ");

        return new MButton('toolbarChanger','&nbsp','javascript:gnuteca.toolBarChanger();',$imgChangerLeft);
    }

    /**
     * Método que desativa campo por javascript
     *
     * @param (String) id do botão a ser desativado
     */
    public static function jsDisableButton($id)
    {
        $MIOLO = MIOLO::getInstance();

        $js = "var sonElement = document.getElementById('{$id}'); //obtém o link
               var image = sonElement.childNodes[1]; //obtém a imagem
               var fatherElement = sonElement.parentNode; //obtém a div pai
               fatherElement.appendChild(image); //adiciona a imagem
               fatherElement.removeChild(sonElement); //remove o link
               fatherElement.className = 'mToolbarButtonDisabled';"; //adiciona a classe css 'disabled

        $MIOLO->page->onload($js);
    }
}

/**
 * Botão da toolbar com suporte a menu
 */
class GToolBarButton extends MToolBarButton
{
    protected $menu;

    /**
     * Adiciona um objeto do miolo para abrir como menu
     *
     * @param object $menu objetos do miolo
     */
    public function addMenu($menu)
    {
        $this->menu = $menu;
    }

    public function generateInner()
    {
        parent::generateInner();

        if ( $this->menu && $this->enabled )
        {
            $this->hint = ''; //caso tenha menu não tem
            $link = $this->inner->inner;
            $link->href = "javascript:gnuteca.changeDisplay('{$this->menu->name}');";
            $link->addAttribute('onmouseover',"dojo.byId('{$this->menu->name}').style.display='block';");
            $link->addAttribute('onmouseout',"dojo.byId('{$this->menu->name}').style.display='none';");
            
            $inner[] = $this->inner;
            $inner[] = $this->menu;

            $this->setInner( $inner );
        }
    }
}

class GToolbarMenu extends MDiv
{
    public $table;
    public $items;

    public function __construct( $name )
    {
        $this->table = new MTableRaw('', null);
        $this->table->setClass('mToolbarMenu ThemeOffice2003SubMenu');
        $this->table->setAlternate( false );
        
        parent::__construct($name, null, 'mToolbarMenuDiv', $attributes);

        $this->addAttribute('onmouseover',"this.style.display='block';");
        $this->addAttribute('onmouseout',"this.style.display='none';");
        $this->addStyle('display','none');
    }

    /**
     * Adiciona um item ao menu
     *
     * @param string $label
     * @param string $image url
     * @param string $link qualquer função ajax ou não
     */
    public function addItem( $label , $image = null , $link = null )
    {
        //caso tenha link transforma o label em um link
        if ( $link )
        {
            if ( $image )
            {
                $imageObj = new MImageLink( '', $label, $link, $image );
            }

            $finalLabel = new MLink( '', $label , $link , $label);
        }
        else
        {
            //caso tenha imagem
            if ( $image )
            {
                $imageObj = new MImage('', $label, $image);
            }
            
            $finalLabel = $label;
        }

        $item[0] = $imageObj;
        $item[1] = new MDiv('', $finalLabel );

        $this->items[] = $item;
    }

    /**
     * Adiciona um separador a menu
     */
    public function addSeparator()
    {
        $item[0] = '';
        $item[1] = new MDiv('', '','ThemeOffice2003MenuSplit'); //separador

        $this->items[] = $item;
    }

    public function generate()
    {
        $this->table->array = $this->items;
        $inner = "<table style='vertical-align: middle; height: 100%'><tr><td>".$this->table->generate().'</td></tr></table>';
        $this->setInner($inner);
        return parent::generate();
    }
}
?>
