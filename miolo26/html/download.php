<?php

    /**
     * Arquivo auxiliar para fazer o download de um arquivo.
     *
     * Os parâmetros esperados na URL são:
     * filename - Caminho absoluto do arquivo.
     * name - Nome do arquivo.
     * contenttype - Tipo de conteúdo do arquivo.
     */
    
    // Verificar se existe um usuário logado.
    session_start();
    $login = $_SESSION['login'];

    if ( $login )
    {
        ob_clean();
          
        $fileName = $_GET['filename'];
        $name = $_GET['name'] ? $_GET['name'] : basename($fileName);
        $contentType = $_GET['contenttype'] ? $_GET['contenttype'] : 'application/force-download';
        
        header('Content-Description: ');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Type: ' . $contentType);
        header('Content-Description: File Transfer');
        header('Content-Length: ' . filesize($fileName));
        header('Content-Transfer-Encoding: binary');
        
        readfile($fileName);
        exit;
    }
    else
    {
        echo 'Não há um usuário válido logado no sistema.';
    }
    
?>
