#!/usr/bin/php
<?php

// do not show unnecessary messages
ini_set("error_reporting", "E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED");

if ( $argc < 2 )
{
    $message = "Usage: {$argv[0]} <command> [<parameters>]\n";
    $message .= "Use \"{$argv[0]} help\" for possible commands\n";
    die($message);
}

require_once 'mioloadmin.class.php';

if ( $argv[1] == 'configure' )
{
    MioloAdmin::configure();

    $admin = new MioloAdmin();

    if ( $argv[2] )
    {
        $admin->setConfig('home.url', $argv[2]);
    }
    else
    {
        $admin->removeConfig('home.url');
    }

    exit("Created initial configuration file successfully!\n");
}

if ( !file_exists('../etc/miolo.conf') )
{
    MioloAdmin::configure();
    $admin = new MioloAdmin();
    $admin->removeConfig('home.url');
}
else
{
    $admin = new MioloAdmin();
}

switch ($argv[1])
{
    case "setconfig":
        if ( $argc < 4 )
        {
            $admin->error("setconfig command expects at least two arguments: <config> <value> [module]");
        }
        $admin->setConfig($argv[2], $argv[3], $argv[4]);
        break;

    case "getconfig":
        if ( $argc < 3 )
        {
            $admin->error("getconfig command expects at least one argument: <config> [module]");
        }
        echo $admin->getConfig($argv[2], $argv[3]) . "\n";
        break;

    case "removeconfig":
        if ( $argc < 3 )
        {
            $admin->error("removeconfig command expects at least one argument: <config> [module]");
        }
        $admin->removeConfig($argv[2], $argv[3]);
        break;

    case "createmodule":
        if ( $argc < 3 )
        {
            $admin->error("createmodule command expects at least one argument: <module> [base_module]");
        }
        $admin->createModule($argv[2], $argv[3]);
        break;

    case "createhandler":
        if ( $argc != 6 )
        {
            $admin->error("createhandler command requires four arguments: <module> <handler> <title> <form>");
        }
        $file = $admin->createHandler($argv[2], $argv[3], $argv[4], $argv[5]);
        echo "Handler $argv[3] successfully created on $argv[2] module!\nCreated file: $file\n";
        echo "\nYou can add the following line at modules/$argv[2]/handlers/main.inc.php to put a link in the main handler.\n";
        echo "\$panel->addAction(_M('$argv[4]'), \$ui->getImage('example', 'forms.png'), \$module, 'main:$argv[3]');\n";
        break;

    case "createform":
        if ( $argc != 5 )
        {
            $admin->error("createform command expects three arguments: <module> <form> <title>");
        }
        $file = $admin->createForm($argv[2], $argv[3], $argv[4]);
        echo "Form $argv[3] successfully created on $argv[2] module!\nCreated file: $file\n";
        break;

    case 'createsearchform':
        if ( $argc < 7 )
        {
            $admin->error("createsearchform command expects at least five arguments: <module> <form> <title> <grid> <table> [filter1 ... filterN]");
        }

        $filters = array();
        for ( $i = 7; $i < $argc; $i++ )
        {
            $filters[] = $argv[$i];
        }
        $file = $admin->createForm($argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $filters);
        echo "Form $argv[3] successfully created on $argv[2] module!\nCreated file: $file\n";
        break;

    case 'creategrid':
        if ( $argc < 5 )
        {
            $admin->error("creategrid command expects at least three arguments: <module> <grid> <title> [column1 ... columnN]");
        }

        $columns = array();
        for ( $i = 5; $i < $argc; $i++ )
        {
            $columns[] = $argv[$i];
        }
        $file = $admin->createGrid($argv[2], $argv[3], $argv[4], $columns);
        echo "Grid $argv[3] successfully created on $argv[2] module!\nCreated file: $file\n";
        break;

    case 'createbusiness':
        if ( $argc != 4 )
        {
            $admin->error("createbusiness command expects two arguments: <module> <table>");
        }

        $file = $admin->createBusiness($argv[2], $argv[3]);
        echo "Business $argv[3] successfully created on $argv[2] module!\nCreated file: $file\n";
        break;

    case 'createmvc':
        if ( !$argv[2] )
        {
            $admin->error("createmvc command expects one argument: <module>");
        }

        $admin->createMVC($argv[2]);
        break;

    case 'createtheme':
        if ( $argc != 4 )
        {
            $admin->error("createtheme command expects two arguments: <name> <base_theme>");
        }
        $admin->createTheme($argv[2], $argv[3]);
        break;

    case 'start':
        if ( $argc != 3 )
        {
            $admin->error("start command expects one argument: <path>");
        }
        $admin->start($argv[2]);
        echo "Installation has been created on $argv[2].\n";
        break;

    case 'translate':
        switch ( $argv[2] )
        {
            case 'extract':
                $admin->translateExtract();
                break;

            case 'generate':
                $admin->translateGenerate();
                break;

            default:
                $admin->error("translate command expects a sub command: extract | generate");
        }
        break;

    case 'help':
        switch ( $argv[2] )
        {
            case 'configure':
                echo "Create an initial configuration file.\n";
                echo "Usage: {$argv[0]} configure [url]\n";
                break;

            case 'setconfig':
                echo "Sets the given value to the given configuration parameter of miolo.conf (or module.conf if module is given).\n";
                echo "Usage: {$argv[0]} setconfig <config> <value> [module]\n";
                break;

            case 'getconfig':
                echo "Gets the value of the given configuration parameter of miolo.conf (or module.conf if module is given).\n";
                echo "Usage: {$argv[0]} getconfig <config> [module]\n";
                break;

            case 'removeconfig':
                echo "Removes the given configuration parameter of miolo.conf (or module.conf if module is given).\n";
                echo "Usage: {$argv[0]} removeconfig <config> [module]\n";
                break;

            case 'createmodule':
                echo "Creates a module with the given name, creating a initial basic structure.\n";
                echo "If base_module is given, it creates a new module based on the given one.\n";
                echo "Usage: {$argv[0]} createmodule <module> [base_module]\n";
                break;

            case 'createhandler':
                echo "Usage: {$argv[0]} createhandler <module> <handler> <title> <form>\n";
                break;

            case 'createform':
                echo "Usage: {$argv[0]} createform <module> <form> <title>\n";
                break;

            case 'createsearchform':
                echo "Usage: {$argv[0]} createsearchform <module> <form> <title> <grid> <table> [filter1 ... filterN]\n";
                break;

            case 'creategrid':
                echo "Usage: {$argv[0]} creategrid <module> <grid> <title> [column1 ... columnN]\n";
                break;

            case 'createbusiness':
                echo "Usage: {$argv[0]} createbusiness <module> <table>\n";
                break;

            case 'createmvc':
                echo "Usage: {$argv[0]} createmvc <module>\n";
                break;

            case 'createtheme':
                echo "Creates a new theme with the given name based on an existing one.\n";
                echo "Usage: {$argv[0]} createtheme <name> <base_theme>\n";
                break;

            case 'start':
                echo "Creates an independent installation for this MIOLO repository.\n";
                echo "The installation will be created on <path> directory.\n";
                echo "Usage: {$argv[0]} start <path>\n";
                echo "Note: If you're calling miolo.php outside bin directory, don't use relative paths.\n";
                break;

            case 'translate':
                echo "Translation utility:\n";
                echo "\textract: extracts used strings.\n";
                echo "\tgenerate: generates the translation files.\n";
                echo "Usage: {$argv[0]} translate extract | generate\n";
                break;

            default:
                echo "Usage: {$argv[0]} <command> [<parameters>]\n";
                echo "Available commands:\n";
                echo "    configure\n";
                echo "    setconfig\n";
                echo "    getconfig\n";
                echo "    removeconfig\n";
                echo "    createmodule\n";
                echo "    createhandler\n";
                echo "    createform\n";
                echo "    createsearchform\n";
                echo "    creategrid\n";
                echo "    createbusiness\n";
                echo "    createmvc\n";
                echo "    createtheme\n";
                echo "    start\n";
                echo "    translate\n";
                echo "    help\n";
                echo "Use \"{$argv[0]} help <command>\" for specific command help.\n";
                echo "Note: If you're calling miolo.php outside bin directory, don't use relative paths.\n";
                break;
        }
        break;

    default:
        die("Invalid command\n");
        break;
}
?>
