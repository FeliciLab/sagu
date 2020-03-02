<?php
$url = $miolo->getConf('home.url');
$title = $miolo->getConf('theme.title');
$action = $miolo->getPage()->action;
$id = $miolo->getPage()->name;
$lang = strtolower(str_replace('_', '-', $miolo->getConf('i18n.language')));
$charset = $miolo->getConf('options.charset');
$favicon = $theme->getFavicon();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/m_common.css">
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/dojo.css">
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/miolo.css">
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/m_themeelement.css">
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/m_uriimages.css">

<?php echo $favicon; ?>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />
<meta name="Generator" content="MIOLO Version Miolo 2.5; http://www.miolo.org.br">

<script type="text/javascript" src="<?php echo $url ?>/scripts/dojoroot/dojo/dojo.js" 
        data-dojo-config="usePlainJson:true, parseOnLoad:false, preventBackButtonFix:false, locale:'<?php echo $lang ?>'"></script>

<script type="text/javascript" src="<?php echo $url ?>/scripts/m_miolo.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_hash.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_page.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_ajax.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_encoding.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_box.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_form.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/m_md5.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/jscookmenu/jscookmenu.js"></script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/jscookmenu/jscookmenu_<?php echo $miolo->getConf('options.mainmenu.style')?>.js"></script>
<script src="<?php echo $url?>/scripts/jquery/jquery-1.7.2.min.js"></script>
<script src="<?php echo $url?>/scripts/jquery/dojo-fix.js"></script>

<script type="text/javascript">
<!--
miolo.loadDeps();
miolo.configureHistory("<?php echo $action ?>");
dojo.addOnLoad(miolo.initHistory);
//-->
</script>

</head>
<body class="mThemeBody">
<!-- begin of page -->
<!--<div id="<?php echo $id ?>">-->
<div id="page100Percent">
<div id="stdout" class="mStdOut"></div>

<div id="mLoadingMessageBg"></div>
<div id="mLoadingMessage">
    <div id="mLoadingMessageImage">
        <div id="mLoadingMessageText">Carregando...</div>
    </div>
</div>

<!-- begin of form __mainForm -->
<div id="__mainForm__scripts" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true">
</div>
<div id="__mainForm" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true">
</div>
<!-- end of form __mainForm -->
</div>
<!-- end of page -->
</body>
</html>
