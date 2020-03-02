<?php
$url = $miolo->getConf('home.url');
$action = $miolo->getPage()->action;
$id = $miolo->getPage()->name;
$lang = strtolower(str_replace('_', '-', $miolo->getConf('i18n.language')));
$charset = $miolo->getConf('options.charset');
define('TITLE', 'Portal SAGU');
$favicon = $theme->getFavicon();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php echo TITLE;?></title>
        
        <?php
        
        //descomentar para usar o fileUpload
        
        /*
        <link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/base/jquery-ui.css" id="theme">
        <!-- jQuery Image Gallery styles -->
        <link type="text/css" rel="stylesheet" href="http://fileUpload.github.com/jQuery-Image-Gallery/css/jquery.image-gallery.min.css">
        <!-- CSS to style the file input field as button and adjust the jQuery UI progress bars -->
        <link  type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/jquery.fileupload-ui.css">
        <!-- CSS adjustments for browsers with JavaScript disabled -->
        <noscript><link type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/jquery.fileupload-ui-noscript.css"></noscript>
         * 
         */
        
        ?>
        
        <?php echo $favicon; ?>
        
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/dojo.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/miolo.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/mobile.min.css" media="handheld">
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/scripts/jquery/jquery.mobile-1.1.0.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/scripts/jquery/jquery.jqplot.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/jqmobilefix.min.css">
        
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/s_calendar.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/m_eventcalendar.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/portal.min.css">
        
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/scripts/datepicker/datepickr.css" />
        
        <!-- jQuery UI styles
        <link type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/jquery-ui.css" id="theme">
        <!-- jQuery Image Gallery styles
        <link type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/jquery.image-gallery.min.css">
        <!-- CSS to style the file input field as button and adjust the jQuery UI progress bars
        <link type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/jquery.fileupload-ui.css">
        <!-- CSS adjustments for browsers with JavaScript disabled
        <noscript><link type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/jquery.fileupload-ui-noscript.css"></noscript>
        <!-- Generic page styles
        <link type="text/css" rel="stylesheet" href="<?php echo $url?>/fileUpload/css/style.css"> -->
        
        <?php
        
        //descomentar para usar o uploadify
        /*
        <link rel="stylesheet" type="text/css" href="<?php echo $url?>/themes/<?php echo $theme->id?>/uploadify.css">
         * 
         */
        ?>

        
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset?>" />
        <meta name="Generator" content="MIOLO Version Miolo 2.5; http://www.miolo.org.br">
        <meta name="viewport" content="initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,width=device-width,user-scalable=no" />

    </head>
    <body class="mThemeBody">
        <div id="<?php echo $id?>">
            <div id="stdout" class="mStdOut"></div>

            <div id="mLoadingMessageBg"></div>
            <div id="mLoadingMessage">
                <div id="mLoadingMessageImage">
                    <div id="mLoadingMessageText">Carregando...</div>
                </div>
            </div>

            <div id="__mainForm__scripts" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="true" cleanContent="true">
            </div>

             <div id="__mainPage" data-role="page" data-theme="c">
                 
                <div id="__mainForm" dojoType="dojox.layout.ContentPane" layoutAlign="client" executeScripts="false" cleanContent="false" data-role="content">
                    
                </div>
            </div>

        </div>
        
        <div id="mDialogContainer"></div>

        <script type="text/javascript" src="<?php echo $url?>/scripts/jquery/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/jquery/dojo-fix.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/jquery/jquery.mobile-1.1.0.min.js"></script>
        <script src="<?php echo $url?>/themes/<?php echo $theme->id?>/jquery.uploadify.js" type="text/javascript"></script>
        
        <script src="<?php echo $url?>/themes/<?php echo $theme->id?>/portal.js" type="text/javascript"></script>
        
        <?php
		/*
        	echo '<script src="<?php echo $url?>/scripts/jquery/plugin/jquery.printElement.js"></script>';
		*/
	?>
        
        <script id="template-upload" type="text/x-tmpl">
        {% for (var i=0, file; file=o.files[i]; i++) { %}
            <tr class="template-upload fade">
                <td class="preview"><span class="fade"></span></td>
                <td class="name"><span>{%=file.name%}</span></td>
                <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
                {% if (file.error) { %}
                    <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
                {% } else if (o.files.valid && !i) { %}
                    <td>
                        <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
                    </td>
                    <td class="start">{% if (!o.options.autoUpload) { %}
                        <button class="btn btn-primary">
                            <i class="icon-upload icon-white"></i>
                            <span>Start</span>
                        </button>
                    {% } %}</td>
                {% } else { %}
                    <td colspan="2"></td>
                {% } %}
                <td class="cancel">{% if (!i) { %}
                    <button class="btn btn-warning">
                        <i class="icon-ban-circle icon-white"></i>
                        <span>Cancel</span>
                    </button>
                {% } %}</td>
            </tr>
        {% } %}
        </script>
        <!-- The template to display files available for download -->
        <script id="template-download" type="text/x-tmpl">
        {% for (var i=0, file; file=o.files[i]; i++) { %}
            <tr class="template-download fade">
                {% if (file.error) { %}
                    <td></td>
                    <td class="name"><span>{%=file.name%}</span></td>
                    <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
                    <td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>
                {% } else { %}
                    <td class="preview">{% if (file.thumbnail_url) { %}
                        <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
                    {% } %}</td>
                    <td class="name">
                        <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
                    </td>
                    <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
                    <td colspan="2"></td>
                {% } %}
                <td class="delete">
                    <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                        <i class="icon-trash icon-white"></i>
                        <span>Delete</span>
                    </button>
                    <input type="checkbox" name="delete" value="1">
                </td>
            </tr>
        {% } %}
        </script>
         <!--
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/tmpl.min.js"></script>
        The Load Image plugin is included for the preview images and image resizing functionality
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/load-image.min.js"></script>
        <!-- The Canvas to Blob plugin is included for image resizing functionality
        <!-- jQuery Image Gallery
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/jquery.image-gallery.min.js"></script>
        <!-- The Iframe Transport is required for browsers without support for XHR file uploads
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/jquery.iframe-transport.js"></script>
        <!-- The basic File Upload plugin
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/jquery.fileupload.js"></script>
        <!-- The File Upload file processing plugin
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/jquery.fileupload-fp.js"></script>
        <!-- The File Upload user interface plugin
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/jquery.fileupload-ui.js"></script>
        <!-- The File Upload jQuery UI plugin
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/jquery.fileupload-jui.js"></script>
        <!-- The main application script
        <script type="text/javascript" src="<?php echo $url?>/fileUpload/js/main.js"></script>-->
        <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
        <!--[if gte IE 8]><script src="<?php echo $url?>/fileUpload/js/cors/jquery.xdr-transport.js"></script><![endif]-->
       
        <script type="text/javascript" src="<?php echo $url?>/scripts/datepicker/datepicker_main.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/calendar/m_eventcalendar.min.js"></script>
        <!--<script async type="text/javascript" src="<?php echo $url?>/scripts/datepicker/calendar.min.js"></script>
        <script async type="text/javascript" src="<?php echo $url?>/scripts/datepicker/calendar-setup.min.js"></script>-->
        
        <script type="text/javascript" src="<?php echo $url?>/scripts/dojominbuild/dojo/dojo.js" 
        data-dojo-config="usePlainJson:true, parseOnLoad:false, preventBackButtonFix:false, locale:'<?php echo $lang?>'"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_miolo_main.min.js"></script>
        <!--<script type="text/javascript" src="<?php echo $url?>/scripts/m_miolo.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_hash.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_page.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_ajax.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_encoding.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_box.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_form.min.js"></script>
        <script type="text/javascript" src="<?php echo $url?>/scripts/m_md5.min.js"></script>-->
        
        
        <script type="text/javascript">
            <!--
            miolo.loadDeps(false);
            miolo.configureHistory("<?php echo $action?>");
            dojo.addOnLoad(miolo.initHistory);
            //-->
        </script>
    </body>
</html>