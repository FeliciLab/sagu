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
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Finguenbaun [jader@solis.coop.br]
 *
 * @since
 * Class created on 11/08/2010
 *
 **/
$MIOLO->uses('handlers/define.inc.php','gnuteca3'); //adiciona constantes
$possibleEvents = array('statusAbout', 'statusFeatures', 'statusChangeLoggedLibrary', 'statusLibraryChanged');
$event = GUtil::getAjaxFunction();

if ( in_array( $event, $possibleEvents))
{
    GStatusBar::$event();
}

class GStatusBar extends MDiv
{
    public $cols;

    public function __construct($cols = null)
    {
        parent::__construct('statusBar', null, 'mStatusbar');

        $module = 'gnuteca3';

        //para pode verificar usuário logado
        $this->manager->getBusiness( 'gnuteca3', 'BusAuthenticate');
        $this->cols = $cols;
        $login = $this->manager->getLogin();
        
        if ($login && $this->manager->getCurrentModule() == 'gnuteca3' )
        {
            $this->addInfo( GOperator::getOperatorName($this->manager->getLogin()->id, true) );

            if ( GOperator::getLibraryUnitLogged() )
            {
                $url = new MImageLink( 'changeLibraryUnit', '' , 'javascript:'.GUtil::getAjax('statusChangeLoggedLibrary',null, null, null, 'gnuteca') , Gutil::getImageTheme('libraryUnit-16x16.png') );
                $this->addInfo( $url , 'divChangeLoggedLibrary', _M('Trocar unidade logada. Unidade atual:', 'gnuteca3' ) . ' ' . GOperator::getLibraryNameLogged() );
            }
        }
        else
        {
            //caso precise aqui pode ir o formulário de login simples
        }

        $userLogin = BusinessGnuteca3BusAuthenticate::checkAcces();

        $this->addInfo( new MImageLink('about', '' , 'javascript:'.GUtil::getAjax('statusAbout') , Gutil::getImageTheme('info-16x16.png') ), '', _M('Sobre','gnuteca3') );
        $this->addInfo( new MImageLink('logout', '' , 'javascript:'.GUtil::getActionLink('logout',null,'gnuteca'), Gutil::getImageTheme('exit-16x16.png') ), '', _M('Sair','gnuteca3') );
    }

    public function addInfo($info, $divId = null, $alt = '')
    {
        $this->cols[] = new MDiv( null, ' ', 'statusBarSeparator' ); //adiciona separador
        
        if ( $info && $alt )
        {
            $info->addAttribute('alt', $alt);
            $info->addAttribute('title', $alt );
        }

        $this->cols[] = new MDiv( $divId, $info, 'statusBarItem' );
    }

    public function clear()
    {
        unset($this->cols);
    }

    public function generate()
    {
        $this->setInner($this->cols);

        return parent::generate();
    }

    public static function statusLibraryChanged()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busLibraryUnit   = $MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');

        $changeLibraryUnitId = MIOLO::_REQUEST('changeLibraryUnitId');

        if ( !$changeLibraryUnitId )
        {
            $goto = 'javascript:'.GUtil::getCloseAction();
            GForm::error( _M('Erro ao trocar unidade de biblioteca.'), $goto ,_M('Troca de unidade de biblioteca', $module) );
        }
        else
        {
            $login                  = $MIOLO->auth->getLogin();
            $login->libraryUnitId   = MIOLO::_REQUEST('changeLibraryUnitId');
            $login->libraryName     = $busLibraryUnit->getLibraryUnit($login->libraryUnitId)->libraryName;
            $MIOLO->auth->setLogin($login);

            //atualiza barra de estados
            $url = new MImageLink( 'changeLibraryUnit', '' , 'javascript:'.GUtil::getAjax('statusChangeLoggedLibrary') , Gutil::getImageTheme('libraryUnit-16x16.png') );
            $alt = _M('Trocar unidade logada. Unidade atual:', 'gnuteca3' ) . ' ' . GOperator::getLibraryNameLogged();
            $url->addAttribute('alt',$alt);
            $url->addAttribute('title',$alt);

            $MIOLO->ajax->setResponse( $url, 'divChangeLoggedLibrary');

            //monta link para recarregar formulário atual
            $url = $MIOLO->getConf('home.url') . '/' .$MIOLO->getConf('options.index') . '?module=gnuteca3&action='.MIOLO::getCurrentAction();

            if ( MIOLO::_REQUEST('function'))
            {
                $url .= '&function='.MIOLO::_REQUEST('function');
            }
            
            //Se conseguiu trocar de unidade define o cookie da unidade conforme a unidade escolhida para 30 anos.
            setcookie('libraryUnitId', $changeLibraryUnitId, 2147483647 );

            // Seta mensagem de mudança de unidade de biblioteca logada.
            self::changeLibraryUnitSucess( $url );
        }
    }
    
    /**
     * Método que cria a mensagem de confirmação de alteraçã de unidade de biblioteca.
     * 
     * @param string $url URL para ondeo sistema será redirecionado ao confirmar a mensagem ou apertar a tecla "ESC'.
     */
    private static function changeLibraryUnitSucess($url)
    {
        $module = MIOLO::getCurrentModule();
        
        // Botão de confirmação.
        $buttonYes= new MButton('btnYes', _M( 'Confirmar','gnuteca3' ), GPrompt::parseGoto($url) , GUtil::getImageTheme( 'accept-16x16.png') );
        $buttonYes->addAttribute('onblur', "gnuteca.setFocus('popupTitle');"); //faz com que o foco volte ao título da janela

        // Botão escondido que será executado ao apertar a tecla "ESC".
        $buttonNo = GForm::getCloseButton();
        $buttonNo->addAttribute('onclick', "miolo.doHandler('{$url}','__mainForm'); return false");
        $buttonNo->addStyle('display', 'none');
        
        // Cria Prompt de confirmação.
        $prompt = new GPrompt( _M('Informação', 'gnuteca3'), _M('Unidade de biblioteca alterada com sucesso.', $module ));
        $prompt->setType( GPrompt::MSG_TYPE_INFORMATION);
        $prompt->addButton($buttonYes);
        $prompt->addButton($buttonNo);

        GPrompt::injectContent($prompt, false, false);
    }

    public static function statusChangeLoggedLibrary()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $busLibraryUnit   = $MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');

        $fields[] = $libraryUnitId = new GSelection('changeLibraryUnitId', GOperator::getLibraryUnitLogged(), _M('Selecione a unidade', $module), $busLibraryUnit->listLibraryUnit(false, true), null, null, null, true);
        $libraryUnitId->setClass('mTextLibraryField');

        $buttons[] = new MButton('btnChange', _M('Trocar'), 'javascript:'.GUtil::getAjax("statusLibraryChanged"), GUtil::getImageTheme('accept-16x16.png'));
        $buttons[] = GForm::getCloseButton();

        $fields[] = new MDiv('changeButtons', $buttons);

        $container = new MVContainer('changeFields', $fields);

        $image = GUtil::getImageTheme('libraryUnit-16x16.png');
        $image = new MImage('libraryUnit', null, $image);

        GForm::injectContent($container, false, $image->generate() . _M('Troca de unidade de biblioteca', $module) ,'550px');
    }

    public static function statusAbout()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'gnuteca3';
        
        $logoGnuteca    = GUtil::getImageTheme('logos/gnuteca.png');
        $logoSolis      = GUtil::getImageTheme('logos/solis.png');
        $header[]       = $image = new MImageLink('linkG', null, 'http://gnuteca.solis.coop.br',$logoGnuteca);

        $image->setGenerateOnClick(false);
        $image->setTarget(MLink::TARGET_BLANK);
        
        $header[]   = new MDiv('title', 'Gnuteca','aboutTitle');
        $header[]   = $image = new MImageLink('linkS', null, 'http://www.solis.coop.br', $logoSolis);

        $image->setGenerateOnClick(false);
        $image->setTarget(MLink::TARGET_BLANK);

        $features = new MLink(null, _M('Funcionalidades', $module), 'javascript:'.GUtil::getAjax('statusFeatures') );

        $fields[] = new MDiv('header', $header);
        $subversion = GUtil::getSubVersion();
        if ($subversion)
        {
            $subversion = ' (' . $subversion . ')';
        }
        $fields[] = new MDiv('version', _M('Versão', $module) . ' ' . GUtil::getVersion()  . $subversion . ' - ' .  $features->generate() );

        $devel[] = array('<b>Coordenador</b>',         'Jamiel Spezia');
        $devel[] = array('<b>Analista</b>',            'Jamiel Spezia');
        $devel[] = array('',                           'Jader Osvino Fiegenbaum');
        $devel[] = array('<b>Analista Univates</b>',   'Willian Walmorbida');
        $devel[] = array('<b>Desenvolvedor</b>',       'Jader Osvino Fiegenbaum');
        $devel[] = array('<b>Testes</b>',              'Willian Walmorbida');
        $devel[] = array('',                           'Jonas Correia da Rosa');
        $devel[] = array('<b>Documentador</b>',        'Jader Osvino Fiegenbaum');
        $devel[] = array('<b>Bibliotecária</b>',       'Ana Paula Monteiro');
        $devel[] = array('<b>Instrutor</b>',           'Paulo Koetz');
        $devel[] = array('<b>Gestor de conta</b>',     'Fernando Kochhann');
        $devel[] = array('',                           'Larri Benedetti Pereira');
        $devel[] = array('',                           'Paulo Koetz');
        $devel[] = array('',                           'Samuel Koch');
        $devel[] = array('<b>Gerente de negócios</b>', 'Luciano Klein');

        $table      = new MTableRaw( _M('Equipe atual',$module), $devel, array( _M('Função',$module), _M('Pessoa',$module) ) );
        $fields[]   = new MDiv('devel',  $table,'m-tableraw') ;
        $fields[]   = new MDiv('opensource','O Gnuteca é uma solução em software livre, desenvolvido e mantido pela <br/> Solis - Cooperativa de Soluções Livres em parceria com Univates - Centro Universiário.');
        $fields[]   = new MDiv('opensource', 'Contato: <a href="mailto:negocios@solis.coop.br">negocios@solis.com.br<a> (+55 51 3714-7043)');

        $logoTEC    = GUtil::getImageTheme('logos/tecnologias.png');
        $fields[]   = new Mdiv( 'tec',new MImage('m', null, $logoTEC ));

        GForm::injectContent($fields, true, _M('Sobre o Gnuteca', $module) ,'600px');
    }

    public function statusfeatures()
    {
        $content = GUtil::getChangeLog();

        $content = explode("\n", $content);

        if ( is_array($content) )
        {
            foreach ( $content as $line => $info)
            {
                if ( stripos($info, ':')  )
                {
                    $content[$line] = '<b>'.$info.'</b>';
                }

                $content[$line] = str_replace(' ', '&nbsp;', $content[$line]);
            }
        }

        $fields[] = new MSeparator('<br/>');
        $fields[] = new MDiv('features', new MTableRaw(null, $content) , 'mTableraw');


        GForm::injectContent($fields, true, _M('Funcionalidades ...', $module) ,'550px');
    }
}
?>