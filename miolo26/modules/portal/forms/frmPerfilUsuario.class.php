<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);
$MIOLO->uses('types/PrtCamposConfiguraveisPessoa.class.php', $module);

class frmPerfilUsuario extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Perfil', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
        $prtCampos = new PrtCamposConfiguraveisPessoa();
        
        $campos = $prtCampos->buscar();

        if ( $campos )
        {
            $fields['dp'] = new MBaseGroup('baseFields', _M('Dados do perfil'), $this->criaCamposConfiguraveis($campos, $this->personid));
            $fields['dp']->setShowLabel(false);

            $fields['btnSalvar'] = MUtil::centralizedDiv(array(new MButton('btnSalvar', 'Salvar', MUtil::getAjaxAction('salvar'))));
            $fields[] = new MDiv();
        }
        
        $camposTrocaSenha['info'] = new MLabel(_M('Para alterar sua senha, informe a nova senha nos campos abaixo.'));
        
        $clear = new MDiv("", "");
        $clear->addStyle("clear", "both");
        
        $camposTrocaSenha[] = $clear;

        $label = new MLabel(_M('Senha atual:'));
        $label->addStyle('width', '200px');
        $label->addStyle('text-align', 'right');
        $txt = new MPasswordField('senhaAtual');
        $txt->addStyle('width', '200px');        
        $camposTrocaSenha[] = new MHContainer('contSenha', array($label, $txt));
        
        $camposTrocaSenha[] = new MSpacer();
        
        $label = new MLabel(_M('Nova senha:'));
        $label->addStyle('width', '200px');
        $label->addStyle('text-align', 'right');
        $txt = new MPasswordField('senha');
        $txt->addStyle('width', '200px');        
        $camposTrocaSenha[] = new MHContainer('contSenha', array($label, $txt));
        
        $camposTrocaSenha[] = new MSpacer();
        
        $label = new MLabel(_M('Confirmar nova senha:'));
        $label->addStyle('width', '200px');
        $label->addStyle('text-align', 'right');
        $txt = new MPasswordField('confirmaSenha');
        $txt->addStyle('width', '200px');        
        $camposTrocaSenha[] = new MHContainer('contSenha', array($label, $txt));
        
        $camposTrocaSenha[] = new MSpacer();
                
        $camposTrocaSenha[] = new MButton('btnTrocaSenha', _M('Trocar senha'));
        
        $campos = new MDiv('divCampos', $camposTrocaSenha, '', 'align="center"');
        
        $fields['ts'] = new MBaseGroup('baseTrocaSenha', _M('Trocar senha'), array($campos));
        $fields['ts']->setShowLabel(false);
        
	parent::addFields($fields);
    }
    
    private function criaCamposConfiguraveis($campos, $personid)
    {
        $MIOLO = MIOLO::getInstance();        
        
        $prtCampos = new PrtCamposConfiguraveisPessoa();
        $dados = $prtCampos->obterDadosDaPessoa($personid);

        $fields = array();

        $estado = null;

        foreach ( $campos as $campo )
        {
            $campoConf = AcdCamposConfiguraveisPessoa::campos($campo->campo);
            $campoKey = strtolower($campoConf['key']);
            $value = $dados[0][$campoKey];


            //Se for campos senha, não insere-o
            if($campoKey == 'password' )
            {
                continue;
            }
            
            if ( strlen($value) == 0 )
            {
                foreach ( $dados as $dado )
                {
                    // POG HARDCODE porque na acdcamposconfiguraveispessoa o campo é chamado de 'RG'
                    // e na basdocumenttype é chamado de 'IDENTIDADE' =(
                    if ( $campo->campo == 'RG' )
                    {
                        $campo->campo = 'IDENTIDADE';
                    }

                    if ( $campo->campo == $dado['tipodocumento'] )
                    {
                        $value = $dado['valordocumento'];
                    }

                    if ( $campo->campo == 'ORG' && $dado['tipodocumento'] == 'IDENTIDADE')
                    {
                        $value = $dado['organ'];
                    }
                }
            }
            
            $label = new MLabel($campoConf['label'] . ':');
            $label->addStyle('width', '200px');
            $label->addStyle('text-align', 'right');



            if ( $campo->campo == 'CIDADE' )
            {
                $busCity = $MIOLO->getBusiness('basic', 'BusCity');
                //$value = $busCity->getCity($value)->name;
                $estado = $busCity->getCity($value)->stateId;
            }


            if ( $campoConf['key'] == 'specialNecessityId' )
            {
                $busSpecialNecessity = $MIOLO->getBusiness('basic', 'BusSpecialNecessity');
                $txt = new MSelection($campoConf['key'], $value, NULL, $busSpecialNecessity->listSpecialNecessity());
            } else if ($campoConf['key'] == 'locationTypeId') {
                $busLocationType = new BusinessBasicBusLocationType();
                $txt = new MSelection($campoConf['key'], $value, NULL, $busLocationType->listLocationType());
            } else if ($campoConf['key'] == 'maritalStatusId') {
                $busMS = new BusinessBasicBusMaritalStatus();
                $txt = new MSelection($campoConf['key'], $value, NULL, $busMS->listMaritalStatus());
            } else if ($campoConf['key'] == 'cityId') {
                $cidade = new BusinessBasicBusCity();
                $txt = new MSelection($campoConf['key'], $value, NULL, $cidade->listCity());
            } else if ($campoConf['key'] == 'countryId') {
                $businessCountry = new BusinessBasicBusCountry();
                $txt = new MSelection($campoConf['key'], 'BRA', NULL, $businessCountry->listCountry());
            } else if ($campoConf['key'] == 'stateId') {
                $busState = new BusinessBasicBusState();
                $txt = new MSelection($campoConf['key'], $estado, NULL, $busState->listState());
            } else
            {
                $txt = new MTextField($campoConf['key'], $value, null, 50);
            }

            $txt->setJsHint(utf8_encode($campoConf['hint']));
            $txt->addStyle('width', '400px');

            //Altera o hint padrão, quando o campo for senha.
            if($campoConf['label'] == 'Senha')
            {
                $txt->setJsHint('Informe a senha (caso fique em branco, n&atilde;o ser&aacute; alterada)');
            }

            /*
            if ( !MUtil::getBooleanValue($campo->editavel) )
            {
                $txt->setReadOnly(TRUE);
            }
            */

            if ($campoConf['key'] == 'personName' || $campoConf['key'] == 'rg' || $campoConf['key'] == 'CPF' || $campoConf['key'] == 'rgOrgao')
            {
                $txt->setReadOnly(TRUE);
            }
            else
            {
                if ( MUtil::getBooleanValue($campo->validador) )
                {
                    $txt->setValidator(new MRequiredValidator($campoConf['key']));
                }
            }

            $fields[$campoConf['key']] = new MHContainer('cont_' . $campoConf['key'], array($label, $txt));
        }                
        
        return $fields;
    }
    
    public function salvar($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $salvar = true;
        $erros = array();
        
        $prtCampos = new PrtCamposConfiguraveisPessoa();
        
        $campos = $prtCampos->buscar();
        
        foreach ( $campos as $campo )
        {
            $campoConf = AcdCamposConfiguraveisPessoa::campos($campo->campo);
            $campoKey = strtolower($campoConf['key']);
            if( $campoKey == 'password' )
            {
                continue;
            }
            
            if ( MUtil::getBooleanValue($campo->editavel) )
            {
                if ( strlen($campoConf['validador']) > 0 )
                {
                    $validador = new $campoConf['validador'];
                    if ( $campoConf['key'] == 'CPF' )
                    {
                        $valorCampo = $args->{$campoConf['key']};
                    }
                    elseif ( $campoConf['key'] == 'zipCode' )
                    {
                        $len = strlen($args->{$campoConf['key']});
                        if ( $len > 0 && $len != 9 )
                        {
                            $salvar = false;
                            $erros[] = $campoConf['label'] . ' - O valor deve respeitar a máscara 99999-999';
                        }
                    }
                    else
                    {
                        $valorCampo = str_replace(array(' ', '(', ')', '-'), '', $args->{$campoConf['key']});
                    }
                    
                    if ( !$validador->validate($valorCampo) )
                    {
                        $salvar = false;
                        $erros[] = $campoConf['label'] . ' - ' .  $validador->getError();
                    }
                }
            }
        }

        if ( $salvar )
        {
            $prtUsuario = new prtUsuario();
            
            $args->personId = $this->personid;
            if ( $prtUsuario->salvarDadosPerfil($args) )
            {
                new MMessageSuccess(_M('Dados do perfil atualizados.'));
            }
            else
            {
                new MMessageWarning(_M('Não foi possível salvar os dados do perfil.'));
            }
        }
        else
        {
            new MMessageWarning(_M('Por favor, verifique os dados informados.<br>') . implode('<br>', $erros));
        }
    }
    
    public function btnTrocaSenha_click($args)
    {
        if ( (strlen($args->senha) > 0 && strlen($args->confirmaSenha) > 0) && ($args->senha == $args->confirmaSenha) && (prtUsuario::verificaSenhaDoUsuario($args->senhaAtual) == true) )
        {
            $MIOLO = MIOLO::getInstance();
            $login = $MIOLO->getLogin();
            $prtUsuario = new prtUsuario();
                      
            try
            {
                if ( $prtUsuario->trocarSenhaUsuario($login->idkey, $args->senha, true) )
                {
                    new MMessageSuccess(_M('A sua senha de acesso foi alterada com sucesso.'));
                }
                else
                {
                    new MMessageWarning(_M('Não foi possível alterar a sua senha.'));
                }
            }
            catch ( Exception $e )
            {
                new MMessageWarning($e->getMessage());
            }
        }
        else
        {
            if ( !prtUsuario::verificaSenhaDoUsuario($args->senhaAtual) )
            {
                new MMessageWarning('A senha atual está incorreta. Verifique o valor informado e tente novamente.');
            }
            elseif ( strlen($args->senha) == 0 )
            {
                new MMessageWarning(_M('O campo \'Nova senha\' não pode ficar em branco.'));
            }
            elseif ( strlen($args->confirmaSenha) == 0 )
            {
                new MMessageWarning(_M('O campo \'Confirmar nova senha\' não pode ficar em branco.'));
            }
            else
            {
                new MMessageWarning(_M('As senhas informadas não conferem.'));
            }
        }
    }

}

?>
