<?php
    session_start();
    
    unset($_SESSION["SPhotoManager"]["photoInfo"]);
    $uploadInfo = array();
    $path = '../html/tmp/';
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
            else
            {
                $_SESSION["SPhotoManager"]["photoInfo"] = null;
                
            }
            

        }

    }
        
?>

