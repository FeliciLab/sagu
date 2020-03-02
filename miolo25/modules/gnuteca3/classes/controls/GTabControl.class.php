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

/*
 *  Classe de gerenciamento de Tabs (abas)
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Creation date 2008/12/02
 *
 **/
class GTabControl extends MBaseGroup
{
	protected $tabs = array();

	public function __construct( $name )
	{
		parent::__construct($name);
	}

    /**
     * Add a tab
     *
     * @param string $tabId the id of the tab
     * @param string $tabName the name of the tab ( and the label)
     * @param array $controls array of controls (fields)
     * @param $action $action an ajax action to be called when user clics the tab
     */
	public function addTab($tabId, $tabName, $controls = NULL  , $action = NULL, $ajaxControlName = false, $editable = null)
	{
		$tab       = new StdClass();
                $tab->id   = $tabId;
		$tab->name = $tabName;

		//a propriedade só existe se existir action
		if ( $action )
		{
		  $tab->action  = $action;
		}

                //Se editavel = falso entao deixar campos como readOnly
                if ( !is_null($editable) )
                {
                    $editable = (MUtil::getBooleanValue($editable))? '':'TRUE';
                    
                    foreach ($controls as $control )
                    {
                        //Se o componente ja tiver definido readOnly nao altera o valor
                        if ( !$control->readonly )
                        {
                            $control->setReadOnly($editable);
                        }
                    }
                }
                
		if ( !$ajaxControlName )
		{
                    #determina variavel js com o focus da tab pré-definido
                    $focus = GForm::findFirstFocus($controls);
                    $focus = "var {$tabId}Focus = '$focus';";
                    $this->page->addJsCode($focus);

                    $tabContainer       = new MVContainer($tabId, $controls);
                    $tabContainer->formMode = MControl::FORM_MODE_SHOW_SIDE;
                    $tabContainer->setWidth('100%');
                    $tabContainer->addStyle('display','none');
                    $tab->controls = $tabContainer;
                    $this->tabs[$tabId] = $tab;
                    $this->addControl( $tabContainer );
		}
		else
		{
			$button      = self::getTabButton($tab, $ajaxControlName);
			$bGenerate   = $button->generate();
			$bGenerate   = str_replace( "\n", '\n', $bGenerate ); //troca linha nova do php para javascript
            $bGenerate   = str_replace( "'", "\'",  $bGenerate  ); // retira ' para evitar erros de sintaxe js

            $divTab      = new MVContainer( $tabId, $controls);
            $divTab->formMode = MControl::FORM_MODE_SHOW_SIDE;

            $dGenerate   = $divTab->generate();
            $dGenerate   = str_replace( "\n", '\n', $dGenerate ); //troca linha nova do php para javascript
            $dGenerate   = str_replace( "'", "\'",  $dGenerate  ); // retira ' para evitar erros de sintaxe js

			$this->page->onload("
            document.getElementById('buttons{$ajaxControlName}').innerHTML += '$bGenerate';
			document.getElementById('{$ajaxControlName}').innerHTML += '$dGenerate';
			{$ajaxControlName}Tabs[{$ajaxControlName}Tabs.length] = '{$tabId}';
			gnuteca.changeTab('{$tabId}','{$ajaxControlName}');
			");
		}
	}


    /**
     * Remove a tab from the tab list
     *
     * @param string $tabId
     */
    public function removeTab( $tabId , $ajaxControlName = false)
    {
    	if ( !$ajaxControlName )
    	{
            unset ( $this->tabs[$tabId] );
    	}
    	else
    	{
            $this->page->onload("gnuteca.removeTab('{$tabId}','{$ajaxControlName}');");
    	}
    }

    /**
	 * Disable or enable a tab (o enable não funciona com tabs ajax)
	 *
	 * @param string $tabId
	 * @param boolean  $disabled
	 */
	public function disableTab( $tabId , $disabled = true, $ajaxControlName = null )
	{
		if (!$ajaxControlName)
		{
            $this->tabs[$tabId]->disabled = $disabled;
		}
		else
		{
			if ( $disabled )
			{
		        $jsCode = "gnuteca.disableTab('{$tabId}', true, '{$ajaxControlName}');";
			}
			else
			{
                $jsCode = "gnuteca.disableTab('{$tabId}', false, '{$ajaxControlName}');";
			}
			$this->page->onload($jsCode);
		}
	}


	/**
	 * Return a tab, or all tabs
	 *
	 * @param string $tabId
	 * @return array or object
	 */
	public function getTab( $tabId = NULL )
	{
		if ( $tabId )
		{
		  return $this->tabs[$tabId];
		}
		else
		{
		  return $this->tabs;
		}
	}


	/**
	 * Generate the tab buttons
	 *
	 * @return object MHContainer
	 */
	protected function getTabButton($tab, $ajaxControlName, $selected = false)
	{
        //$tempDiv = new MLink($name, $label, $href, $text, $target, $generateOnClick);
        $tempDiv = new MLink( $tab->id.'Button', $tab->name, "#", $text, $target, $generateOnClick);
		//$tempDiv = new MDiv($tab->id.'Button' , $tab->name );

		//controla se é por ajax ou aba estatica, ou desabilita
		if ( !$tab->disabled)
		{
            $tempDiv->setClass( !$selected ? 'a-tab' : 'a-tab-selected');

            if ( !$tab->action )
            {
                $tempDiv->addAttribute('onclick', "gnuteca.changeTab('$tab->id', '$ajaxControlName'); return false;");
            }
            else
            {
                $tempDiv->addAttribute('onclick', GUtil::getAjax($tab->action) . "; gnuteca.changeTab('$tab->id','$ajaxControlName'); return false; ");
            }
		}
		else
		{
			$tempDiv->setClass('a-tab-disabled');
		}

		return $tempDiv;
	}

	/**
	 * Componente generate method
	 *
	 * @return GTabControl object
	 */
	public function generate()
	{
		$MIOLO = MIOLO::getInstance();

        $this->setControls(null);

		$this->setClass('a-tab-container');

		$innerCode = "{$this->name}Tabs = new Array(); \n";
		$x = 0;

		//monta código para esconder todas tabs
        foreach ($this->tabs as $tabId => $tab)
        {
            $buttonsTemp[$tabId] = $this->getTabButton( $tab , $this->name, $x == 0 ? true : false);

            if ( $x === 0 )
            {
                $tab->controls->addStyle('display','block'); //mostra só a tab 0;
            }

            $this->addControl($tab->controls);
            $innerCode .= "{$this->name}Tabs[{$x}] = '{$tabId}';\n"; //adiciona a tab ao array de tabs

            $x++;
        }

        $buttons['buttons'] = new MDiv('buttons'.$this->name , $buttonsTemp );
        $buttons['buttons']->setClass('a-tab-buttons');

        $MIOLO->page->addJsCode( $innerCode );

        $buttonContainer = new MHContainer( 'tabButtonContainer'.$this->name, $buttons );

		$generate = $buttonContainer->generate();
		$generate.= parent::generate();

		return $generate;
	}

	/**
	 * Mostra só a primeira tab no onload
	 *
	 * @return string o código js
	 */
	public function getOnload()
	{
		$args = (object) $_REQUEST;

		if ( isset( $args->__EVENTTARGETVALUE ) )
		{
			$temp     = array_keys( $this->tabs );
            $onload   = "gnuteca.changeTab('$temp[0]', '{$this->name}');";
		}

        return $onload;
	}

	public function ajaxUpdateTab( $controls, $tab)
	{
        $container = new MVContainer('ajaxFields'.$tab, $controls);
        $container->formMode = MControl::FORM_MODE_SHOW_SIDE;

        $this->setResponse( $container , $tab);
	}
}
?>