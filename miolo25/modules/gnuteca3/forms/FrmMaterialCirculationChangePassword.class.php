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
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 12/01/2009
 *
 **/
class FrmMaterialCirculationChangePassword extends FrmMaterialCirculationChangeStatus
{
    public function changePassword( $args = NULL )
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->setMMType('changePassword');
        $this->changeTab('changePassword');

        $busPerson = $MIOLO->getBusiness('gnuteca3','BusPerson');
        $changePersonPermissions = $busPerson->getPersonChangePermissions();
        
        if (MUtil::getBooleanValue($changePersonPermissions->tabMain) == MUtil::getBooleanValue(DB_FALSE) )
        {
            GForm::information(_M('Não é possível alterar a senha da pessoa. A aba Geral deve ser ativada na preferência "CHANGE_WRITE_PERSON".', $module));
        }
        else if ( MY_LIBRARY_AUTHENTICATE_LDAP == DB_TRUE )
        {
            GForm::information(_M('A funcionalidade está desabilitada, pois a autenticação utiliza servidor LDAP.', $module));
        }
        else
        {
            $fields = array();
            $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');
            $fields[] = $password = new MPasswordField('password', null,  _M('Senha',$module));
            $password->addAttribute('onPressEnter', GUtil::getAjax('saveChangePasswordOnKeyDown'));
            $fields[] = $retype = new MPasswordField('retype' ,null, _M('Redigite a senha',$module));
            $retype ->addAttribute('onPressEnter', GUtil::getAjax('saveChangePassword'));
            $fields[] = new GContainer('divButton', array($btnChangePassword = new MButton('btnChangePassword', _M('Alterar senha', $module), ':saveChangePassword', GUtil::getImageTheme('keys-16x16.png'), true)));
            $btnChangePassword->addAttribute('onClick', GUtil::getAjax('saveChangePassword'));
            $fields[] = new MImage('imgKey128', null, GUtil::getImageTheme('keys-128x128.png'), null);
            $field = new MVContainer('',$fields);
            $field->formMode = MControl::FORM_MODE_SHOW_SIDE;
            
            return $this->addResponse( $field, $args );
        }
    }

    public function saveChangePassword($args)
    {
        $module = MIOLO::getCurrentModule();
        $this->jsSetValue('password','');
        $this->jsSetValue('retype','');

        if (!$args->personId)
        {
            $this->error(_M('Código da pessoa é inválido.', $module) );
            return;
        }

        if ( ($args->password != $args->retype) || !$args->password || !$args->retype )
        {
            $this->error( _M('Por favor verifique sua digitação. Senha e Redigite senha são diferentes ou inválidas.', $module) );
            return;
        }
        else
        {
            $ok = $this->busAuthenticate->changePassword($args->personId, $args->password, $args->retype);
        }

        if ($ok)
        {
            $this->information( _M('Senha alterada com sucesso!', $module) );
        }
        else
        {
            $this->jsSetFocus('password');
            $this->error( _M('Alteração de senha falhou!', $module) );
        }
    }

    public function saveChangePasswordOnKeyDown($args)
    {
        if ( !$args->password)
        {
            $this->jsSetFocus('password');
        }
        if ( !$args->personId)
        {
            $this->jsSetFocus('personId');
        }
        if ($args->personId && $args->password)
        {
            $this->jsSetFocus('retype');
        }
        $this->setResponse('','limbo');
    }
}
?>
