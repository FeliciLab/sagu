<?php

/*********************************************\
 | main.php                                      |
 | ----------------------------------------------|
 | Daniel Afonso Heisler (daniel@solis.coop.br)  |
 \*********************************************/
include_once("lib/ide.class");

class ide2 extends ide {

    function closeWindow() {
        Gtk::main_quit();
    }

}

if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
dl("php_gtk.dll");
else
dl("php_gtk.so");

$ide = new ide2();
$ide->window_dia2sql->show_all();

Gtk::main();

?>
