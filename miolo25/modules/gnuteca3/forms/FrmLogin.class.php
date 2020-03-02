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
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
class FrmLogin extends GForm
{
    public $auth;
    public $MIOLO;
    public $module;
    public $busLibraryUnit;
    public $busAuthenticate;
    public $busPerson;
    public $args;

    public function __construct($args)
    {
        $this->args     = $args;
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = 'gnuteca3';

        $this->busLibraryUnit   = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busAuthenticate  = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busPerson        = $this->MIOLO->getBusiness($this->module, 'BusPerson');

        if ( $this->getLoginType() == LOGIN_TYPE_ADMIN )
        {
            $label = _M('Acesso administrativo', $this->module);
        }
        else
        {
            $label = _M('Login Gnuteca (Usuário)', $this->module);
        }
            
        parent::__construct( $label );
        $this->setIcon(GUtil::getImageTheme('login-16x16.png'));

        if ($this->getLoginType() != LOGIN_TYPE_USER_AJAX)
        {
            if ($this->page->isPostBack())
            {
                
            }
        }

        $this->setClass("loginForm");
    }


    public function mainFields($return = false)
    {
        $ui = $this->MIOLO->getUI();
        $loginType = $this->getLoginType();
        //Sempre pedir o código do usuário na Circulação de material
        $isAuthenticated = $this->isMaterialMovement() ? false : $this->isAuthenticated();
        $return_to = $this->getFormValue('return_to', MIOLO::_Request('return_to'));

        $fields[] = MMessage::getMessageContainer();

        if ( ! MIOLO::_REQUEST('uid') )
        {
            $this->errors = null;
        }

        if(!$return_to)
        {
            $return_to = $this->MIOLO->history->top();
        }

        
        //escolhe label de acordo com situação
        if ( $loginType == LOGIN_TYPE_ADMIN )
        {
            $userLabel = _M('Operador', $this->module);
        }
        else if ( ($loginType == LOGIN_TYPE_USER || $loginType == LOGIN_TYPE_USER_AJAX ) )
        {
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                $userLabel = _M('Usuário', $this->module);
            }
            else
            {
                $userLabel = _M('Código', $this->module);
            }
        }
        
        $labelLogin = new MLabel( $userLabel );
        $labelLogin->setWidth(DEFAULT_LABEL_SIZE);
        $uid = new MTextField('uid', $this->MIOLO->auth->login->iduser, null, 20);
        $uid->setClass('mTextUserField');
        $uid->setReadOnly($isAuthenticated);
        $fields[] = new GContainer('hctLogin', array($labelLogin, $uid));

        if (!$this->isMaterialMovement())
        {
            $labelPasswd = new MLabel(_M('Senha', $this->module));
            $labelPasswd->setWidth(DEFAULT_LABEL_SIZE);
            $pwd = new MPasswordField('pwd', '', null, 20);
            $pwd->setClass('mTextPasswdField');
            $fields[] = new GContainer('hctPasswd', array($labelPasswd, $pwd));
        }

        if ($loginType == LOGIN_TYPE_ADMIN)
        {
            $label = new MLabel( _M('Biblioteca', $this->module) );
            $libraryUnitId = new GSelection('libraryUnitId', $_COOKIE['libraryUnitId'], null, $this->busLibraryUnit->listLibraryUnit(false, false), null, null, null, true);
            //Altera tempo de expiraçao para 30 anos.
            $libraryUnitId->addAttribute('onchange', 'var expiration_date = new Date(); expiration_date.setFullYear(expiration_date.getFullYear() + 30); document.cookie = \'libraryUnitId=\'+this.value+\'; expires=\'+expiration_date.toGMTString()+\';\';');
            $libraryUnitId->setClass('mTextLibraryField');
            $fields[] = new GContainer('hctLibraryUnit', array($label, $libraryUnitId));
        }
        
        if ($loginType == LOGIN_TYPE_USER   )
        {
            $bases =  BusinessGnuteca3BusAuthenticate::listMultipleLdap();
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
            {
                $fields[] = $baseF = new GSelection('baseLdap', '', _M('Base:', $this->module), $bases, false, '','', true);
                $baseF->setClass('mTextLibraryField');
            }
        }
        elseif ($loginType == LOGIN_TYPE_USER_AJAX)
        {
            $bases =  BusinessGnuteca3BusAuthenticate::listMultipleLdap();
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
            {
                $fields[] = $baseF = new GSelection('baseLdapAjax', '', _M('Base:', $this->module), $bases, false, '','', true);
                $baseF->setClass('mTextLibraryField');
            }
        }
        
        $this->jsSetFocus('uid', false); //seta foco no campo código
        $fields[] = new MHiddenField('tries', '');
        $fields[] = new MHiddenField('return_to', $return_to);
        $fields[] = new MHiddenField('redirect_action', $this->getFormValue('redirect_action', MIOLO::_REQUEST('redirect_action')));
        $fields[] = new MHiddenField('loginType', $loginType);

        $param = GUtil::getAjaxEventArgs();
        $ajaxAction = '';

        if ($loginType == LOGIN_TYPE_USER_AJAX)
        {
            $ajaxAction = GUtil::getAjax($this->getEvent(), $param);
        }
        else
        {
            $ajaxAction = 'javascript:'.GUtil::getAjax('btnLogin_click', $param).';';
        }

        $btnLogin = new MButton('btnLogin', _M('Autenticar', $this->module) , $ajaxAction, GUtil::getImageTheme('accept-16x16.png') );

        if (!$isAuthenticated)
        {
            $buttons[] = $btnLogin;
        }

        $btnLogout = new MButton('btnLogout', _M('Sair', $this->module) ,null, GUtil::getImageTheme('logout-16x16.png') );

        if ($isAuthenticated)
        {
            $buttons[] = $btnLogout;
        }

        if ($loginType == LOGIN_TYPE_USER_AJAX)
        {
            $buttons[] = GForm::getCloseButton();
        }
        
        $fields[] = $hctButtons = new MDiv('hctButtons', $buttons);
        $div = new GContainer('divLogin', $fields);
        $div->setClass('divLogin');
        
        if ($return)
        {
            return array($div);
        }
        else
        {
            $this->setFields($div);
        }
    }

    /**
     * Essa função foi criada pois gerava problemas na pesquisa simples com subform.
     * Dessa forma não chamada função de busca.
     */
    public function searchFunction()
    {
        //não faz nada
    }

    public function getLoginFields()
    {
        $this->errors = null;
        $fields = $this->mainFields(true);

        if ( $this->errors )
        {
            //define o foco na mensagem para leitura de accessibilidade
            GForm::jsSetFocus( MMessage::MSG_DIV_ID ,false);
            $msg = implode('<br>', $this->errors );
            $message= MMessage::getStaticMessage( MMessage::MSG_DIV_ID, $msg , MMessage::TYPE_WARNING );
            $message->addAttribute('alt',strip_tags($msg));
            $message->addAttribute('title',strip_tags($msg));
            $fields = array_merge( array( $message ), $fields);
        }
        
        return $fields;
    }


    public function btnLogin_click()
    {
        $data = $this->getData();
        $max_tries = 3; // Max login tryes

        // autenticar usuario e obter dados do login
        $uid = $this->getFormValue('uid', MIOLO::_REQUEST('uid'));
        $pwd = $this->getFormValue('pwd', MIOLO::_REQUEST('pwd'));
        $loginType = $this->getLoginType();

        $this->MIOLO->logMessage('[LOGIN] Validating login information: ' . $uid);
        
        if ( $loginType == LOGIN_TYPE_USER)
        {        
            $base = $this->getFormValue('baseLdap', MIOLO::_REQUEST('baseLdap')); //obtém a base pelo user
        }
        elseif ($loginType == LOGIN_TYPE_USER_AJAX)
        {
            $base = $this->getFormValue('baseLdapAjax', MIOLO::_REQUEST('baseLdap')); //obtém a base pelo userAjax
        }
        
        if ( $loginType == LOGIN_TYPE_USER || $loginType == LOGIN_TYPE_USER_AJAX ) //Autenticacao USUARIO
        {
            //Obtem login dependendo do modo de autenticação utilizado
            $uid = $this->busPerson->insertPersonLdapWhenNeeded($uid, $base, $pwd);
            
            if ( ((MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN)) &&  $this->MIOLO->getConf('login.classUser') == 'gAuthMoodle' )
            {
                $this->MIOLO->getClass('gnuteca3', 'gauthmoodle');

                $gAuthMoodle = new gAuthMoodle();
                $uids = $gAuthMoodle->synchronizePersonFromMoodle($uid);
            }
            
            if($uids)
            {
                $uid = $uids;
            } 
                
  
            if ( ( $loginType == LOGIN_TYPE_USER_AJAX ) && $this->isMaterialMovement() )
            {
                $info = $this->busPerson->getBasicPersonInformations($uid);

                if ($info->personId)
                {
                    $_REQUEST['personId'] = $uid;
                    $_REQUEST['uid'] = $uid;
                    return true;
                }
                else
                {
                    $this->addError(_M('Usuário não existente!', $this->module));
                    return false;
                }
            }

            $result = $this->busAuthenticate->authenticate($uid, $pwd);

            if (!$result)
            {
                $this->addError( _M('Falha na autenticação. Verifique os dados digitados.', $this->module ));
                
                if ($loginType == LOGIN_TYPE_USER_AJAX)
                {
                    return false;
                }
            }
            else
            {
                $previousUrl = $this->MIOLO->getPreviousUrl();
                if ($previousUrl && strpos($previousUrl, 'myLibrary') )
                {
                    $goto = $previousUrl;
                }
                else
                {
                    $goto = $this->MIOLO->getActionURL($this->module, 'main:myLibrary', null, $opts);
                }

                //Verifica argumentos de redirecionamento passados
                $redirect_action = MIOLO::_REQUEST('redirect_action');
                $return_to = $this->getFormValue('return_to');
                if ($redirect_action)
                {
                    $goto = $this->MIOLO->getActionURL($this->module, $redirect_action);
                }
                else if ($return_to)
                {
                    $goto = $this->MIOLO->getActionURL($this->module, $return_to);
                }

                if ($loginType == LOGIN_TYPE_USER_AJAX)
                {
                    return true;
                }
                else
                {
                    $this->page->redirect($goto);
                }
            }
        }
        else //Autenticacao ADMIN
        {
            if ( $this->MIOLO->auth->authenticate($uid, $pwd,true) )
            {
                //Verificacao de permissao biblioteca
               $busOperatorLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusOperatorLibraryUnit');
               $libperms = $busOperatorLibraryUnit->getOperatorLibraryUnit($uid);

               if ($libperms->operatorLibrary[0]->libraryUnitId) //Se vir alguma unidade, significa que nao tem permissao para todas bibliotecas
               {
                    $found = false;
                    foreach ($libperms->operatorLibrary as $v)
                    {
                       if ($v->libraryUnitId == MIOLO::_REQUEST('libraryUnitId'))
                       {
                           $libraryName = $v->libraryName;
                           $found = true;
                       }
                    }
                    if (!$found)
                    {
                        $this->MIOLO->auth->logout();
                        $this->error(_M('Você não possui permissão para acessar esta biblioteca!', $this->module), GUtil::getCloseAction(true), null, false);
                        return;
                    }
                }

                //Escreve no objeto login a biblioteca
                $login = $this->MIOLO->auth->getLogin();
                $login->libraryUnitId = MIOLO::_REQUEST('libraryUnitId');
                $login->libraryName = $this->busLibraryUnit->getLibraryUnit($login->libraryUnitId)->libraryName;
                $this->MIOLO->auth->setLogin($login);

               //$return_to = $this->getFormValue('return_to');
                $return_to = MIOLO::_REQUEST('return_to','GET');

               if ( $return_to )
               {
                  $url = $return_to;
               }
               else
               {
                  $url = $this->MIOLO->getActionURL($this->module,'main');
               }
               $this->page->onload("window.location='$url'");
            }
            else
            {
               $tries = $this->getFormValue('tries');

               if ( $tries >= $max_tries )
               {
                  $this->error('Erro na identificação do usuário!');
               }
               else
               {
                  $err = 'Erro na identificação do usuário!' . ' - Restam ' . ( $max_tries - $tries) .' ' . 'tentativa(s).';
                  $tries++;
                  //joga o número da tentativa por js, já que o login agora é por ajax
                  $this->page->onload("dojo.byId('tries').value = '{$tries}';");
                  $pwd = null;
                  
                  if ( $err )
                  {
                      $this->error($err, GUtil::getCloseAction(true), null, false);
                  }
               }
            }
        }
    }

    public function btnLogout_click()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->page->redirect($this->MIOLO->getActionURL($this->module, 'main:logout', NULL, array('loginType'=>$this->getLoginType())));
    }

    public function isAuthenticated()
    {
        $loginType = $this->getLoginType();

        if ( $loginType == LOGIN_TYPE_USER || $loginType == LOGIN_TYPE_USER_AJAX )
        {
            $checkAcess = $this->busAuthenticate->checkAcces();
            
            if ( $loginType == LOGIN_TYPE_USER_AJAX && !$checkAcess )
            {
                return $this->btnLogin_click();
            }

            return $checkAcess;
        }
        else
        {
            return $this->MIOLO->auth->isLogged();
        }
    }

    public function getLoginType()
    {
        $loginType = $this->getFormValue('loginType', MIOLO::_REQUEST('loginType'));

        if (!$loginType) //Tipo de login padrao (ADMIN)
        {
            if ($this->args->loginType)
            {
                $loginType = $this->args->loginType;
            }
            else
            {
                $loginType = LOGIN_TYPE_ADMIN;
            }
        }
        
        return $loginType;
    }

    public function isMaterialMovement()
    {
        return (MIOLO::_REQUEST('action') == 'main:materialMovement');
    }

    /**
     * Formulário de login é um formulário público não precisa de login (dah!)
     *
     * @return true;
     */
    public function checkAccess()
    {
        return true;
    }
    
    /**
     * Faz não pedir confirmação de edição
     * 
     * @return string manage 
     */
    public function getFormMode()
    {
        return 'search';
    }
}
?>
