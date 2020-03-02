<?php
    include_once '../etc/miolo/miolo.conf';

    $fname = $MIOLOCONF['home']['url.reports'] . '/' . $_REQUEST['fname'] . '.pdf';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<HTML>
    <HEAD>
        <TITLE>Miolo Report</TITLE>

        <SCRIPT LANGUAGE = "JavaScript">
            <!--  


            public function go_now()
                {
                window.location = "<? echo $fname;?>";
                }

            -->
        </SCRIPT>
    </HEAD>

    <BODY onLoad = "go_now();">
    </BODY>
</HTML>
