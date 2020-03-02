<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Obtem o download de arquivo pedido para o usuario
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @since
 * Class created on 25/04/2011
 */

unset($_SESSION["SPhotoManager"]["photoInfo"]);
$uploadInfo = array();
$path = SPhotoManager::getTmpFolder();
$allowedExt = array('jpg', 'jpeg', 'png', 'gif');

if (!is_dir($path)) {
    mkdir($path, 0777, true);

}

foreach($_FILES as $field => $file)
{
    if (!($file["error"] > 0))
    {
        $operatorId = $_POST["operatorId"];

        $explode = explode(".", $file["name"]);

        $fileExt = $explode[sizeof($explode) - 1];

        $fileName = 'photo_' . $operatorId . '.';

        // Limpa a pasta temporária por arquivos não salvos pelo operador.
        foreach($allowedExt as $ext)
        {
            if(file_exists($path . $fileName . $ext))
            {
                unlink($path . $fileName . $ext);

            }

        }

        $fileName = $fileName . $fileExt;

        if((in_array($fileExt, $allowedExt)) && (move_uploaded_file($file["tmp_name"], $path . $fileName)))
        {
            $uploadInfo = new stdClass();

            $uploadInfo->tmpName = $fileName;
            $uploadInfo->ext = $fileExt;
            $uploadInfo->type = $file["type"];

            $_SESSION["SPhotoManager"]["photoInfo"] = $uploadInfo;
                        
        }

    }

}