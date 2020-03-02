<?php
require 'sconsole.php';

list($fileName, $serializedData) = $argv;

$data = unserialize($serializedData);
$className = $data['ClassName'];

// Chama metodos de sThread
$obj = new $className(); // Instancia de sThread
$obj->setParameters($data); // Recria parametros da classe
$obj->__run(); // Executa processo
$obj->setProcessStatus(sThread::PROCESS_FINISHED);

?>
