<?php
// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro Universitário  |
// +-----------------------------------------------------------------+
// | Copyleft (l) 2001 UNIVATES, Lajeado/RS - Brasil                 |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://miolo.codigoaberto.org.br                          |
// | E-mail: vgartner@univates.br                                    |
// |         ts@interact2000.com.br                                  |
// +-----------------------------------------------------------------+
// | Abstract: This file contains utils functions                    |
// |                                                                 |
// | Created: 2001/08/14 Thomas Spriestersbach                       |
// |                     Vilson Cristiano Gärtner,                   |
// |                                                                 |
// | History: Initial Revision                                       |
// +-----------------------------------------------------------------+

/**
 * Classe para descompactar arquivos zip.
 * Esta classe e´ ser utilizada para descompactar arquivos .zip
 *
 * Requerer: extensao zip do php: http://pecl.php.net/packages/zip
 * Instalaçao:
 *             - download do arquivo do pacote
 *             - descompactar o arquivo
 *             $ phpize5 (dentro do diretorio criado)
 *             $ ./configure
 *             $ make
 *             $ make install (como usuario root)
 *             - adicionar ao php.ini:
 *               extension=zip.so
 *             - reiniciar o apache
 *
 * Mais informaçoes, consulte: http://php.net/manual/en/install.pecl.phpize.php
 */
class MZip
{
    public static function unzip($file, $dir)
    {

        $zip = new ZipArchive();

        $zip->open("$file");

        $files = array(substr($file,0,-4), $dir);

        if ( ! $zip->extractTo($dir) )
        {
            echo "Error!\n";
            echo $zip->status . "\n";
            echo $zip->statusSys . "\n";

        }

        $zip->close();
    }
}

?>
