<?php

$uploadInfo = array();
$path = '../html/files/tmp';
$fields = '';
$uploadFiles = array();
$errors = array();

foreach ( $_FILES as $field => $file )
{
    if ( is_array($file['name']) )
    {
        foreach ( $file['name'] as $n => $name )
        {
            if ( $file['error'][$n] == UPLOAD_ERR_OK )
            {
                $uploadFiles[] = array(
                    'name' => $name,
                    'tmp_name' => $file['tmp_name'][$n],
                    'field' => "{$field}[$n]"
                );
            }
            else
            {
                $errors[] = implode(';', array( $file['name'][$n], $file['error'][$n] ));
            }
        }
    }
    else
    {
        if ( $file['error'] == UPLOAD_ERR_OK )
        {
            $uploadFiles[] = array(
                'name' => $file['name'],
                'tmp_name' => $file['tmp_name'],
                'field' => $field
            );
        }
        else
        {
            $errors[] = implode(';', array( $file['name'], $file['error'] ));
        }
    }
}

$i = 1;

foreach ( $uploadFiles as $file )
{
    $fileName = date('Y-m-d_hms_') . $file['name'];

    if ( file_exists("$path/$fileName") )
    {
        $fData = pathinfo("$path/$fileName");
        $fileName = $fData['filename'] . "-$i";

        if ( $fData['extension'] )
        {
            $fileName .= ".{$fData['extension']}";
        }

        $i += 1;
    }

    if ( move_uploaded_file($file['tmp_name'], "$path/$fileName") )
    {
        $uploadInfo[] = implode(';', array($file['name'], $fileName));

        $fields .= "<input type=\"hidden\" name=\"{$file['field']}\" value=\"{$file['name']}\" />";
    }
}

$uploadInfo = implode(',', $uploadInfo);
$errors = implode(',', $errors);

?>
<html>
    <head>
        <script lang="text/javascript">
            var div = parent.document.createElement('div');
            div.innerHTML = '<?php echo $fields; ?>';
            parent.document.forms[0].appendChild(div);
        </script>
    </head>
    <body>
        <textarea style="display:none;">{'uploaded':'true','uploadInfo':'<?php echo $uploadInfo; ?>','uploadErrors':'<?php echo $errors; ?>'}</textarea>
        <?php echo $fields; ?>
    </body>
</html>
