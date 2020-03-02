<?php
header("Content-type: text/html; charset=utf-8");
$url    = $miolo->getConf('home.url');
if (strlen($url) == 0)
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    $scriptName = substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/'));
    $url = "$protocol://{$_SERVER['HTTP_HOST']}$scriptName";
}
$action = $miolo->getPage()->action;
$id     = $miolo->getPage()->name;
$lang   = strtolower(str_replace('_', '-', $miolo->getConf('i18n.language')));
include_once($miolo->getAbsolutePath('handlers/debugFunctions.inc.php', 'gnuteca3'));
include_once($miolo->getAbsolutePath('handlers/gnutecaClasses.inc.php', 'gnuteca3'));
include_once($miolo->getAbsolutePath('handlers/define.inc.php', 'gnuteca3'));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?=$miolo->getConf('theme.title')?></title>
        <link rel="stylesheet" type="text/css" href="themes/<?php echo $theme->id ?>/gnuteca.css">
        <link rel="stylesheet" type="text/css" href="file.php?folder=searchTheme&file=search.css">
        <link rel="icon" href="file.php?folder=searchTheme&file=favicon.png" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="Content-Language" content="<?php echo $lang ?>" />
        <meta name="Generator" content="MIOLO Version Miolo 2.5; http://www.miolo.org.br http://www.solis.coop.br">
        <meta name="version" content="<?php echo GUtil::getVersion() .'.'. GUtil::getSubVersion(); ?>">
        <meta name="description" content="O Gnuteca é um sistema de automação e gestão de bibliotecas, utilizada para catalogação o sistema MARC21, software livre desenvolvido pela Solis." />
        <meta name="keywords" content="b jgd z ee, gnuteca, solis, sistema de biblioteca, gestão de bibliotecas, automação de bibliotecas" />
        <script type="text/javascript"> djConfig={usePlainJson:true, parseOnLoad:true, preventBackButtonFix: false, locale: '<?php echo $lang ?>'}</script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/dojoroot/dojo/dojo.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_miolo.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_hash.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_page.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_ajax.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_encoding.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_box.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_form.js"></script>
        <script type="text/javascript" src="<?php echo $url ?>/scripts/m_md5.js"></script>
        <script type="text/javascript" src="index.php?module=gnuteca3&action=scripts:gmainmenu.js"></script>
        <script type="text/javascript" src="index.php?module=gnuteca3&action=scripts:Gnuteca.js"></script>
        <script type="text/javascript">miolo.loadDeps(); miolo.configureHistory("<?php echo $action ?>");dojo.addOnLoad(miolo.initHistory);</script>
    </head>
    <body class="mThemeBody" onload="window.carregaMenu()">
        <div id="<?php echo $id ?>">
            <div id="stdout" class="mStdOut"></div>
            <div id="mLoadingMessageBg"></div>
            <div id="mLoadingMessage">
                <div id="mLoadingMessageImage">
                    <div id="mLoadingMessageText" tabindex ="-1" alt="Carregando" title="Carregando">Por favor aguarde...</div>
                </div>
            </div>
            <div id="gUpperBar" class="mThemeContainerTop">
                <?php $theme->setElementClass('content', 'mThemeContainerContentFullAjax');?>
                <div id="gMainMenu" class="gMainMenu">
                    <?php
                        //é trocada a instância do $MIOLO->perms, pois neste ponto a classe correta ainda não foi instanciada.
                        $permsClass = $miolo->getConf('login.perms');
                        if ( file_exists($miolo->getAbsolutePath( 'classes/security/' . strtolower($permsClass) . '.class.php' )) )
                        {
                            $prefix = 'security/';
                            $module = null;
                        }
                        else
                        {
                            $prefix = 'classes/';
                            $module = 'gnuteca3';
                        }
                        $miolo->uses($prefix . strtolower($permsClass).'.class.php', $module);
                        $miolo->perms = new $permsClass();
                        $sysMenu = new GMainMenu();
                        echo "<script>" . $sysMenu->inicializa() . "</script>";
                    ?>
                    <div id="gMainMenuInner"></div>
                </div>
                <div class="mTopmenuSeparator"></div>
                <div id="navbar" class="navbar"></div>
                <?php $statusBar = new GStatusBar(); echo $statusBar->generate();?>
            </div>
            <div id="__mainForm__scripts" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true"></div>
            <div id="__mainForm" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true"></div>
        </div>
    </body>
</html>
