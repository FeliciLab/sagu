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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
  *
 * @since
 * Class created on 14/07/2011
 *
 **/
class GPrompt extends MPrompt
{
    public function  __construct( $caption = null, $message = null, $icon = '/images/error.gif' )
    {
        if ( !is_object( $message ) )
        {
            $msg = str_replace('"',"'",$message) ;
            $message = new MSpan( 'popupTitle', $msg, 'popupTitleInner' );
            $message->addAttribute( 'alt',   trim( strip_tags( $msg ) ) );
            $message->addAttribute( 'title', trim( strip_tags( $msg ) ) );
            $message->generate();
        }

        parent::__construct( $caption, $message, $icon );
    }

    public static function information($msg, $goto = 'javascript:gnuteca.closeAction();' )
    {
        $button= new MButton('btnYes', _M( 'Confirmar','gnuteca3' ), GPrompt::parseGoto($goto) , GUtil::getImageTheme( 'accept-16x16.png') );
        $button->addAttribute('onblur', "gnuteca.setFocus('popupTitle');"); //faz com que o foco volte ao título da janela

        $prompt = new GPrompt( _M('Informação', 'gnuteca3'), $msg);
        $prompt->setType( GPrompt::MSG_TYPE_INFORMATION);
        $prompt->addButton($button);

        self::injectContent( $prompt ,false, false);
    }

    public static function question($msg, $gotoYes = '', $gotoNo = 'javascript:gnuteca.closeAction();' )
    {
        $btnYes = new MButton('btnYes', _M( 'Sim','gnuteca3' ), GPrompt::parseGoto($gotoYes), GUtil::getImageTheme( 'accept-16x16.png') );
        $prompt = new GPrompt( _M('Confirmação', 'gnuteca3'), $msg );
        $prompt->setType( GPrompt::MSG_TYPE_QUESTION );
        $prompt->addButton($btnYes);
        $prompt->addNegationButton( $gotoNo );

        self::injectContent( $prompt ,false, false);
    }

    public static function error( $msg = '', $goto = 'javascript:gnuteca.closeAction();', $caption = 'Error' )
    {
        $prompt = new GPrompt( $caption ? $caption : 'Error' , $msg );
        $prompt->setType( MPrompt::MSG_TYPE_ERROR );

        $prompt->addCloseButton( $goto );

        self::injectContent( $prompt ,false, false);
    }

    /**
     * Adiciona um botão utilizando classe MButton
     * 
     * @param MButton $button
     *
     */
    public function addButton( MButton $button )
    {
        $this->buttons[] = $button;
    }

    /**
     * Adiciona botão padrão de negação
     */
    public function addNegationButton($gotoNo)
    {
        $btnNo = new MButton('btnClose', _M( 'Não','gnuteca3' ), GPrompt::parseGoto($gotoNo), GUtil::getImageTheme( 'error-16x16.png') );
        $btnNo->addAttribute('onblur',"gnuteca.setFocus('popupTitle');"); //faz com que o foco volte ao título da janela
        
        $this->addButton($btnNo);
    }

    /**
     * Adiciona botão padrao de fechar
     */
    public function addCloseButton($goto)
    {
        $button = new MButton('btnClose', _M( 'Fechar','gnuteca3' ), GPrompt::parseGoto($goto) , GUtil::getImageTheme( 'exit-16x16.png'));
        $button->addAttribute('onblur',"gnuteca.setFocus('popupTitle');"); //faz com que o foco volte ao título da janela
        
        $this->addButton( $button );
    }

    public function generateInner()
    {
        $content = '';

        if ( ! is_array($this->message) )
        {
            $this->message = array($this->message);
        }

        $textBox = new MDiv('', $this->message, 'mPromptBoxText');

        if ($this->buttons)
        {
            foreach ($this->buttons as $button)
            {
                if ( $button instanceof MButton )
                {
                    $b = $button;
                }
                else
                {

                    $label = $button[0];
                    $goto = $button[1];
                    $event = $button[2];
                    $name = $this->name;

                    if ( strpos($goto, 'javascript:') === 0 )
                    {
                        $onclick = "$goto;";
                    }
                    elseif ( $goto != '' )
                    {
                        $onclick = "go:$goto" . (($event != '') ? "&event=$event" : "");
                    }
                    else
                    {
                        if ( $event != '' )
                        {
                            $eventTokens = explode(';', $event);
                            $onclick = "javascript:miolo.doPostBack('{$eventTokens[0]}','{$eventTokens[1]}','{$this->formId}');";
                        }
                    }

                    $b = new MButton($name, $label, $onclick);

                }

                $b->setClass('button');
                $content[] = $b->generate();
            }

            $buttonBox = new MDiv('', $content, 'mPromptBoxButton');
        }
        else
        {
            $buttonBox = new MSpacer('20px');
        }

        $this->close = $onclick;
        $type = ucfirst($this->type);
        $c = new MVContainer('',array($textBox,$buttonBox));
        $c->setClass("mPromptBoxBody mPromptBox{$type}");
        $this->inner = $c;
	}

    /**
     * Trata os dados de um goto de prompt. Código adaptado do mprompt da função generateInner.
     *
     * @param string $goto
     * @return string url tratada
     */
    public static function parseGoto($goto)
    {
        //coloca ação de fechar padrão caso não passe nenhum goto
        $goto = $goto ? $goto : GUtil::getCloseAction(true);
        
        if ( strpos($goto, 'javascript:') === 0 || strpos($goto, 'miolo.doAjax(') === 0 )
        {
            return $goto;
        }
        elseif ( $goto != '' )
        {
            return "go:$goto";
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
        $MIOLO      = MIOLO::getInstance();
		$module     = MIOLO::getCurrentModule();
		$imageClose = GUtil::getImageTheme('exit-16x16.png' );

        #para compatibilidade como PHP 5.2.6
		if (is_object($content) && method_exists($content, 'generate') )
		{
			$content = $content->generate();
		}

        $buttonsContainer =  new MDiv('buttonsContainer');

        //se closeButton for uma string define ela como javascript extra
		if ( is_string( $closeButton ) )
		{
			$extraJavaScript = $closeButton;
		}
        //VERIFICA BOTAO CLOSE, sempre adiciona, para funcionar o ESC, mas esconde caso passe falso
		if ( $closeButton || (is_array($closeButton) && in_array('close', $closeButton)) )
		{
            $buttons['close'] = GForm::getCloseButton( $extraJavaScript );
		}
        else
        {
            $buttons['close'] = GForm::getCloseButton( );
            $buttons['close']->addStyle('display','none');
            $buttonsContainer->addStyle('display','none');
        }
        //VERIFICA BOTAO PRINT @deprecated
		if(is_array($closeButton) && in_array('print', $closeButton))
		{
            $buttons['print'] = new MButton('btnPrint', 'Imprimir', ':printReceipt', GUtil::getImageTheme('document-16x16.png'));
		}

        $buttonsContainer->setInner($buttons);

		$panelFields[]  = new MDiv('content', $content );
		$panelFields[]  = $buttonsContainer;

        //cria um formulário, uma caixa pro conteúdo
		if ($form)
		{
            $tit = is_string($form) ? $form : _M('Mensagem', $module);
            $title = new MSpan( 'popupTitle', $tit);
            $title->addAttribute('alt',  trim(strip_tags( $tit ) ) );
            $title->addAttribute('title', trim(strip_tags( $tit ) ) ) ;
            
			$panel = new MForm( $title );
            $panel->id = 'injectContent';
			$panel->setShowPostButton(false);
			$panel->setFields($panelFields, false);

            if ( ! is_object($formWidth) )
			{
                $panel->addStyle('width', $formWidth);
			}

            $divPromptUp= new Div('divPromptUp', $panel);

    	}
		else
		{
			$divPromptUp= new Div('divPromptUp', array($content, $buttons));
		}

        //se existir um foco definido
        if ( !Gform::$gFocus )
        {
            GForm::jsSetFocus( 'popupTitle');
        }

		$MIOLO->ajax->setResponse( array( new MDiv('divPromptDown') , $divPromptUp), GForm::GDIV );
	}
}
?>