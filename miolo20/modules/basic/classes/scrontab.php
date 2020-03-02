<?php

# instancia do miolo
include dirname(__FILE__) . '/sconsole.php';

BasSystemTask::generateTasks();
BasCrontabLog::insertCrontabLog();

?>
