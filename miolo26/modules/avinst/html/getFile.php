<?php
//
// Este arquivo "pega" um arquivo não visível ao apache e força seu download
// Primeiramente, ele faz uma série de validações como:
// 1. Verifica se o usuário tem um login válido na sessão
// 2. Verifica se o arquivo existe
// 3. Faz uma validação com o hash passado via url, batendo o nome do arquivo
//    com um crypt do hash
// TODO: Para a avaliação, dois locais utilizam a mesma estrutura,
// TODO: seria importante unificar a estrutura em um local padrão, com chamadas
// TODO: iguais para todos os módulos
//

//
// IMPORTANTE: Para utilizar o controle de login, a configuração "login.shared" do miolo.conf deve
// estar marcada como TRUE, permitindo assim, ao sistema, verificar pela variável $_SESSION, a 
// estrutura de login do sistema
//
$file = urldecode($_GET['file']);
$hash = urldecode($_GET['hash']);
session_start();
$login = $_SESSION['login'];
$uri = $_SERVER['SCRIPT_NAME'];

$uri = str_replace('getFile.php', '', $uri);

// Verifica se tem login
if (strlen($file)>0)
{
    $file = base64_decode($file);
}
if (strlen($hash)>0)
{
    $hash = base64_decode($hash);
}
// Verifica o login na sessão
if (isset($login))
{
    // Se tiver, verifica se tem um seed nas configurações
    if (is_array($_SESSION['configs']))
    {
        foreach ($_SESSION['configs'] as $config)
        {
            // O seed é o SEED_REQUEST_FILE
            if ($config['0'] == 'SEED_REQUEST_FILE')
            {
                $seed = $config[1];
                if (crypt($file, $seed) == $hash)
                {
                    if (file_exists($file))
                    {
                         if(ini_get('zlib.output_compression'))
                         {
                            ini_set('zlib.output_compression', 'Off');
                         }
                         header('Pragma: public');
                         header('Expires: 0');
                         header('Cache-Control: must-revalidate, post-check=0 pre-check=0');
                         header('Content-type: '.mime_content_type($file));
                         header('Content-lenght: '.filesize($file));
                         header("Content-Disposition: inline; filename=".rawurlencode(basename($file)));
                         header("Content-Transfer-Encoding: binary");
                         echo file_get_contents($file);
                         header('Connection: close'."\n");
                         $sended = true;
                    }
                }
            }
        }
    }
}
if ($sended !== true)
{
    header('Location: '.$uri);
}
?>
