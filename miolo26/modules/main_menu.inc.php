<?php

// Put in this file your menus/links for the Main Menu
$session = $MIOLO->session;
$session->setValue("num_mainmenu", 0);

$menu = $theme->getMainMenu();
$menu->setTitle(_M('Main Menu'));

$login = $MIOLO->getLogin();
$adminModule = $MIOLO->mad;
$loginModule = $MIOLO->getConf('login.module');
$startUpModule = $MIOLO->getConf('options.startup');
$commonModule = $MIOLO->getConf('options.common');

$menu->addOption(_M('Common'), $commonModule, 'main', '', '', 'common-16x16.png');

$tutMenu = $menu->getMenu('tutorial');
$tutMenu->setTitle(_M('Example'), 'tutorial-16x16.png', null, 'example', 'main');
$tutMenu->addOption(_M('Validators'), 'example', 'main:validators');
$tutMenu->addOption(_M('ezPDF'), $module, 'main:ezpdf');

$menuControls = $tutMenu->getMenu('controls');
$menuControls->setTitle(_M('Controls'), 'button_properties.png', 'button_edit.png');
$menuControls->addOption(_M('Block Controls'), 'example', 'main:controls:blockcontrols', '', '', 'button_properties.png');
$menuControls->addOption(_M('Menu Controls'), 'example', 'main:controls:menu', '', '', 'button_properties.png');

// generates the main menu from main_menu.xml
$dom = new DOMDocument();
$dom->load($MIOLO->getConf('home.modules') . '/modules_menu.xml');
$xpath = new DOMXPath($dom);
$link = $xpath->query('menu');

foreach ( $link as $l )
{
    $fn = $xpath->query('caption', $l);
    $menuName = $fn->item(0)->firstChild->nodeValue;

    $fn = $xpath->query('module', $l);
    $menuOption = $fn->item(0)->firstChild->nodeValue;

    $fn = $xpath->query('action', $l);
    $menuAction = $fn->item(0)->firstChild->nodeValue;

    $fn = $xpath->query('icon', $l);
    $menuIcon = $fn->item(0)->firstChild->nodeValue;

    $menu->addOption(_M($menuName), $menuOption, $menuAction, '', '', $menuIcon);
}

// Create a new menu
$sysMenu = $theme->getMenu('system');
$sysMenu->setTitle(_M('System'));

if ( $perms->checkAccess($adminModule, A_ADMIN, false) )
{
    $sysMenu->addOption(_M('Administration'), $adminModule, 'main', '', '', 'system-16x16.png');
    $sysMenu->addSeparator('-');
}

if ( $login )
{
    $sysMenu->addOption(_M('Logout'), $loginModule, 'logout', '', '', 'logout-16x16.png');
}
else
{
    $sysMenu->addOption(_M('Login'), $loginModule, 'login', '', '', 'login-16x16.png');
}

$develMenu = $theme->getMenu('devel');
$develMenu->setTitle(_M('Development'));
$develMenu->addLink(_M('MIOLO Project Site'), 'http://www.miolo.org.br', '_blank', 'miolo_icon.png');
$develMenu->addSeparator();

$docMenu = $develMenu->getMenu('documentation');
$docMenu->setTitle(_M('Documentation'));
$docMenu->addLink(_M('Classes Diagram'), 'http://www.miolo.org.br/doc_MIOLO2/html/', '_blank', 'tutorial-16x16.png');
$docMenu->addLink(_M('MIOLO 2 Overview'), 'http://www.miolo.org.br/modules/sites/files/files/site_miolo/miolo2_overview.pdf', '_blank', 'tutorial-16x16.png');
$docMenu->addLink(_M('Reference Guide'), 'http://www.miolo.org.br/modules/sites/files/files/site_miolo/RefGuide.pdf', '_blank', 'tutorial-16x16.png');
$docMenu->addLink(_M('Database - DAO'), 'http://www.miolo.org.br/modules/sites/files/files/site_miolo/miolo20_dao.pdf', '_blank', 'tutorial-16x16.png');

?>