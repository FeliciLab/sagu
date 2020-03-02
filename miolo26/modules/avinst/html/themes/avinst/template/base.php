<?php
$url = $miolo->getConf('home.url');
$action = $miolo->getPage()->action;
$id = 'avinst';
$lang = strtolower(str_replace('_', '-', $miolo->getConf('i18n.language')));
$favicon = $theme->getFavicon();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>SAGU - Avaliação Institucional</title>
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/dojo.css">
<link rel="stylesheet" type="text/css" href="<?php echo $url ?>/themes/<?php echo $theme->id ?>/miolo.css">
<link rel="stylesheet" type="text/css" href="<?php echo $url?>/scripts/datepicker/datepickr.css" />

<?php echo $favicon; ?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="Generator" content="MIOLO Version Miolo 2.5; http://www.miolo.org.br">

<!-- It is used with Internet Explorer to debug javascript
<script language="javascript" type="text/javascript" src="<?php echo $url ?>/scripts/firebug/firebug-lite.js"></script>
-->

<script type="text/javascript"> djConfig={usePlainJson:true, parseOnLoad:true, preventBackButtonFix: false, locale: '<?php echo $lang ?>'}</script>
<script type="text/javascript" src="<?php echo $url ?>/scripts/dojoroot-1.7.2/dojo/dojo.js"></script>

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
miolo.loadDeps();
var historyControl = {
    url: "<?php echo $action ?>",
    lastUrl: '',

    // this method is called by onhashchange and by MIOLO's init function
    callback: function(hash) {
        if ( hash )
        {
            this.url = this.url.split('index.php')[0] + 'index.php' + hash;
        }
        else
        {
            this.url = "<?php echo $action ?>";
        }

        // call doHandler if it's the first access or if it's coming back from future
        if ( this.lastUrl == '' || this.url.split('index.php')[1] != this.lastUrl.split('index.php')[1] )
        {
            miolo.doHandler(this.url, '__mainForm', true);
        }
    }
}
dojo.subscribe("/dojo/hashchange", historyControl, 'callback');

function init()
{
    if ( dojo.hash() )
    {
        historyControl.callback(dojo.hash());
    }
    else
    {
        historyControl.callback(historyControl.url.split('index.php')[1]);
    }
}
dojo.addOnLoad(init);
//-->
</script>

</head>
<body class="mThemeBody">
<!-- begin of page -->
<div id="<?php echo $id ?>">
<div id="stdout" class="mStdOut"></div>

<div id="mLoadingMessageBg"></div>
<div id="mLoadingMessage">
    <div id="mLoadingMessageImage"></div>
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
