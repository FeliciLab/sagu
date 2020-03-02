<?php
    $login = $MIOLO->getLogin();
    // Get access to the User Interface classes/methods.
    // We need getUI to be able to call getForm
    $ui = $MIOLO->getUI();
    $home = 'main';
    $module = 'avinst';
    $navbar->setLabelHome(_M('Avaliação Institucional'));
    $navbar->home = $module;
    $theme->clearContent();
    $function = MIOLO::_REQUEST('function');
    $shiftAction = $context->shiftAction();
    
    // Ajustando para que olhe diretamente nos rights do usuário - ticket #38491
    // DB_RIGHT_ROOT tem que ser AVINST
    // DB_TRANSACTION_ROOT tem que ser 31
    $temPermissao = (in_array(DB_RIGHT_ROOT, $login->rights[strtolower(DB_TRANSACTION_ROOT)]));

    if ($MIOLO->getPerms()->checkAccess( DB_TRANSACTION_ROOT, DB_RIGHT_ROOT ) || $MIOLO->getPerms()->checkAccess( DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN ) || $temPermissao )
    {
        $MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
        $menuPrincipal= $theme->getMenu('menu');
        $menuPrincipal->setTitle('Menu');
        $menuPrincipal->addOption('Home', 'avinst', 'main', '', '', 'home-16x16.png');
        $menuPrincipal->addSeparator('-');

        // Cadastros
        $menuCadastros = $menuPrincipal->getMenu('cadastros');
        $menuCadastros->setTitle('Cadastros','entries-16x16.png');
        $menuCadastros->addOption('Avaliações', 'avinst', 'main:avaAvaliacao', '', '', 'evaluations-16x16.png');
        $menuCadastros->addOption('Formulários', 'avinst', 'main:avaFormulario', '', '', 'form-16x16.png');
        $menuCadastros->addOption('Questões', 'avinst', 'main:avaQuestoes', '', '', 'questions-16x16.png');
        $menuCadastros->addOption('Categorias', 'avinst', 'main:avaCategoria', '', '', 'folderopened0.gif');
        
        if ( $temPermissao || $MIOLO->getPerms()->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT) )
        {
            $menuCadastros->addOption('Configurações', 'avinst', 'main:avaConfig', '', '', 'config-16x16.png');
            $menuCadastros->addOption('Granularidades', 'avinst', 'main:avaGranularidade', '', '', 'granularity-16x16.png');
            $menuCadastros->addOption('Perfis', 'avinst', 'main:avaPerfil', '', '', 'profiles-16x16.png');
            $menuCadastros->addOption('Serviços', 'avinst', 'main:avaServico', '', '', 'services-16x16.png');        
            //$menuCadastros->addGroupOption(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT, 'Usuários', 'admin', 'main:users', '', '', 'groups-16x16.png');
            $menuCadastros->addOption('Componentes', 'avinst', 'main:avaWidget', '', '', 'widgets-16x16.png');                
        }

        // Processos
        $menuProcessos = $menuPrincipal->getMenu('processos');
        $menuProcessos->setTitle('Processos','process-16x16.png');
        $menuProcessos->addOption('Replicar formulário', 'avinst', 'main:avaReplicarFormulario', '', '', 'replicate_forms-16x16.png');
        $menuProcessos->addOption('Definir tipo de granularidade', 'avinst', 'main:avaDefinirTipoDeGranularidade', '', '', 'replicate_forms-16x16.png');
        $menuProcessos->addOption('Popular opções de questões', 'avinst', 'main:avaPopularOpcoesDeQuestoes', '', '', 'replicate_forms-16x16.png');
//        $menuProcessos->addOption('Envio de e-mails', 'avinst', 'main:avaMail', '', '', 'emails-16x16.png');        
        
        if ( $temPermissao || $MIOLO->getPerms()->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT) )
        {
            $menuProcessos->addOption('Testar serviço', 'avinst', 'main:avaTestaServico', '', '', 'test-services-16x16.png');
        }
        
//        $menuProcessos->addGroupOption(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT, 'Atualizar totalizadores', 'avinst', 'main:avaAtualizaTotalizadores', '', '', 'update-totals-16x16.png');
        // Ambiente
//        $menuAmbiente = $menuPrincipal->getMenu('ambiente');
//        $menuAmbiente->setTitle('Ambiente','user_environment-16x16.png');
//        $menuAmbiente->addOption('Simular ambiente com outro login', 'avinst', 'main:avaAnalisaFormulario', '', '', 'anonymous_respondent-16x16.png');
        
        $menuPrincipal->addSeparator('-');

        // Logout
        $authmodule = $MIOLO->getConf('login.module');
        $menuPrincipal->addOption('Sair', 'avinst', 'logout', '', NULL, 'exit_button-16x16.png');
    }    
        
    if (strlen($shiftAction)>0)
    {
        switch ($shiftAction)
        {
            case 'menu':
                echo "Menu";
                break;
            default:
                $MIOLO->invokeHandler($module,$shiftAction);
                break;
        }
    }
    else
    {
        $ui = $MIOLO->getUI();
        $formPanel = $ui->getForm($module, 'frmDashboard');
        $theme->appendContent($formPanel);
    }
?>
