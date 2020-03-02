<?php
$width      = $_REQUEST['width'] ? $_REQUEST['width'] : '700';
$height     = $_REQUEST['height'] ? $_REQUEST['height'] : '400';
$btnClose   = $_REQUEST['btnClose'];
$identifier = $_REQUEST['identifier'];

?>
<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Gnuteca - Google Book Viewer</title>
    <script type="text/javascript" src="//www.google.com/jsapi"></script>
    <link rel="stylesheet" type="text/css" href="https://miolo25gnuteca3/themes/gnuteca/dojo.css"/>
    <link rel="stylesheet" type="text/css" href="https://miolo25gnuteca3/themes/gnuteca/miolo.css"/>
    <style type="text/css">
        * 
        {
            margin: 0;
            padding: 0;
        }

    </style>
    <script type="text/javascript">
    google.load("books", "0");

    var viewer ;

    function alertError()
    {
        alert("não encontramos o livro");
    }

    function onSucess()
    {
        document.getElementById('identifier').value ='sucesso';
    }

    function initialize()
    {
        viewer = new google.books.DefaultViewer(document.getElementById('viewerCanvas'));
        loadBook();
    }

    function loadBook()
    {
        viewer.load(document.getElementById('identifier').value);
    }

    function upateCurrentPage()
    {
         if (viewer.isLoaded())
        {
             document.getElementById('page').innerHTML =viewer.getPageNumber();
        }
    }

    google.setOnLoadCallback(initialize);

    </script>
  </head>

  <body>
    <div id="viewerCanvas" style="width: <?php echo $width?>px; height: <?php echo $height?>px">Aguarde enquando o livro é carregado.</div>
    <div style="float:left;">
        <!--<span>Página atual:</span>
        <span id="page">0</span>
        <button class="m-button" onclick="loadBook();" >Visualizar livro</button>-->
        <!--<button class="m-button" onclick="viewer.previousPage();upateCurrentPage()" >
            <img alt="Fechar" src="index.php?module=gnuteca3&amp;action=images:previous-16x16.png">Anterior</img>
        </button>
        <button class="m-button" onclick="viewer.nextPage(); upateCurrentPage()" >
            <img alt="Fechar" src="index.php?module=gnuteca3&amp;action=images:next-16x16.png">Próximo</img>
        </button>-->
        <?php
        if ($btnClose)
        {
            echo '
        <button onclick="parent.gnuteca.closeAction()" value="Fechar" name="btnClose" class="m-button" type="button" id="btnClose">
            <img alt="Fechar" src="index.php?module=gnuteca3&amp;action=images:exit-16x16.png">Fechar</img>
        </button>';
        }
        ?>
        <input type="hidden" id="identifier" value="<?php echo $identifier?>" />
    </div>
  </body>
</html>

