<?php
$url = $miolo->getConf('home.url');
$action = $miolo->getPage()->action;
$id = $miolo->getPage()->name;
$lang = strtolower(str_replace('_', '-', $miolo->getConf('i18n.language')));
$charset = $miolo->getConf('options.charset');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>MIOLO 2.5</title>
<link rel="stylesheet" type="text/css"  href="/scripts/dojoroot/dojox/mobile/themes/android/android.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/mobile.css">

<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no"/>
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta names="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />
<meta name="Generator" content="MIOLO Version Miolo 2.5; http://www.miolo.org.br">

<!-- It is used with Internet Explorer to debug javascript
<script language="javascript" type="text/javascript" src="<?php echo $url ?>/scripts/firebug/firebug-lite.js"></script>
-->

<script type="text/javascript"> djConfig={usePlainJson:true, parseOnLoad:true, preventBackButtonFix: false, locale: '<?php echo $lang ?>'}</script>
<!--<script type="text/javascript"> djConfig={usePlainJson:true, parseOnLoad:true, preventBackButtonFix: false, locale: '<?php echo $lang ?>', isDebug: true, debugAtAllCosts: true}</script>-->
<script type="text/javascript" src="<?php echo $url ?>/scripts/dojoroot/dojo/dojo.js"></script>

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

<script type="text/javascript">
<!--
miolo.loadDeps(true);
miolo.configureHistory("<?php echo $action ?>");
dojo.addOnLoad(miolo.initHistory);
//-->
</script>

</head>
<body class="mThemeBody">
<!-- begin of page -->
<div id="<?php echo $id ?>">
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
