<?php

/**
 * Class for creating MIOLO files
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/10/19
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2010 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

// Change directory to the one where the script is located, so it can be called anywhere you are
chdir(preg_replace('~/[^/]*.php~', '', $argv[0]));
require_once '../classes/miolo.class.php';

$_SERVER['SCRIPT_FILENAME']   = '../html';

class MioloAdmin
{
    public $MIOLO;

    /**
     * MioloAdmin constructor
     */
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->MIOLO->initialize();
    }

    /**
     * Configure miolo.conf directories.
     */
    public static function configure()
    {
        $mioloRoot =  realpath('..');
        $confSample = '../etc/miolo.conf.dist';
        $confFile = '../etc/miolo.conf';

        $content = fread(fopen($confSample, 'r'), filesize($confSample));
        $content = str_replace('/var/www/miolo', $mioloRoot, $content);

        $handler = fopen($confFile, 'w');
        fwrite($handler, $content);
        fclose($handler);
    }

    /**
     * Set a configuration value.
     *
     * @param string $config Configuration name.
     * @param string $value Configuration value.
     * @param string $module If it's informed, set the module.conf configuration.
     */
    public function setConfig($config, $value, $module=NULL)
    {
        if ( !$module )
        {
            $confFile = $this->MIOLO->getConf('home.miolo') . "/etc/miolo.conf";
        }
        else
        {
            $confFile = $this->MIOLO->getConf('home.modules') . "/$module/etc/module.conf";
        }

        $xml = simplexml_load_file($confFile);
        $configVars = str_replace('.', '->', $config);
        eval("\$xml->$configVars = '$value';");

        $this->writeContentToFile($xml->asXML(), $confFile);
    }

    /**
     * Get a configurartion value.
     *
     * @param string $config Configuration name.
     * @param string $module If it's informed, get the module.conf configuration.
     * @return string Configuration value.
     */
    public function getConfig($config, $module=NULL)
    {
        if ( !$module )
        {
            $conf = $this->MIOLO->getConf($config);
        }
        else
        {
            $this->MIOLO->conf->loadConf($module);
            $conf = $this->MIOLO->getConf($config);
        }

        return $conf;
    }

    /**
     * Remove configuration.
     *
     * @param string $config Configuration name.
     * @param string $module If it's informed, remove the module.conf configuration.
     */
    public function removeConfig($config, $module=NULL)
    {
        if ( !$module )
        {
            $confFile = $this->MIOLO->getConf('home.miolo') . "/etc/miolo.conf";
        }
        else
        {
            $confFile = $this->MIOLO->getConf('home.modules') . "/$module/etc/module.conf";
        }

        $xml = simplexml_load_file($confFile);
        $configVars = str_replace('.', '->', $config);
        eval("unset(\$xml->$configVars);");

        $this->writeContentToFile($xml->asXML(), $confFile);
    }

    public function start($mioloPath)
    {
        $svnPath = $this->MIOLO->getConf('home.miolo');

        if ( !file_exists($mioloPath) )
        {
            mkdir($mioloPath);
        }
        elseif ( count(scandir($mioloPath)) > 2 )
        {
            $this->error("start: Directory is not empty.");
        }

        // get absolute path
        $mioloPath = realpath($mioloPath);

        mkdir("$mioloPath/bin");
        symlink("$svnPath/bin/miolo.php", "$mioloPath/bin/miolo.php");
        symlink("$svnPath/bin/mioloadmin.class.php", "$mioloPath/bin/mioloadmin.class.php");
        symlink("$svnPath/bin/samples", "$mioloPath/bin/samples");
        symlink("$svnPath/bin/templates", "$mioloPath/bin/templates");
        symlink("$svnPath/bin/flog.sh", "$mioloPath/bin/flog.sh");
        symlink("$svnPath/bin/JMioloTrace.jar", "$mioloPath/bin/JMioloTrace.jar");

        symlink("$svnPath/classes", "$mioloPath/classes");
        symlink("$svnPath/COPYING", "$mioloPath/COPYING");
        symlink("$svnPath/docs", "$mioloPath/docs");
        symlink("$svnPath/LICENSE", "$mioloPath/LICENSE");
        symlink("$svnPath/locale", "$mioloPath/locale");
        symlink("$svnPath/misc", "$mioloPath/misc");
        symlink("$svnPath/package", "$mioloPath/package");
        symlink("$svnPath/var", "$mioloPath/var");
        mkdir("$mioloPath/etc");
        symlink("$svnPath/etc/mkrono.conf", "$mioloPath/etc/mkrono.conf");

        mkdir("$mioloPath/html");
        foreach ( scandir("$svnPath/html") as $dest )
        {
            if ( !in_array($dest, array('.', '..', '.svn', 'themes')) )
            {
                symlink("$svnPath/html/$dest", "$mioloPath/html/$dest");
            }
        }
        mkdir("$mioloPath/html/themes");
        symlink("$svnPath/html/themes/blue", "$mioloPath/html/themes/blue");
        symlink("$svnPath/html/themes/modern", "$mioloPath/html/themes/modern");

        mkdir("$mioloPath/modules");
        symlink("$svnPath/modules/admin", "$mioloPath/modules/admin");
        symlink("$svnPath/modules/common", "$mioloPath/modules/common");
        symlink("$svnPath/modules/example", "$mioloPath/modules/example");
        symlink("$svnPath/modules/generator", "$mioloPath/modules/generator");
        symlink("$svnPath/modules/hangman", "$mioloPath/modules/hangman");
        symlink("$svnPath/modules/helloworld", "$mioloPath/modules/helloworld");
        symlink("$svnPath/modules/modules.inc.php", "$mioloPath/modules/modules.inc.php");
        symlink("$svnPath/modules/main_menu.inc.php", "$mioloPath/modules/main_menu.inc.php");
        symlink("$svnPath/modules/modules_menu.xml", "$mioloPath/modules/modules_menu.xml");

        $confSample = "$svnPath/etc/miolo.conf.dist";
        $confFile = "$mioloPath/etc/miolo.conf";

        $content = fread(fopen($confSample, 'r'), filesize($confSample));
        $content = str_replace('/var/www/miolo', $mioloPath, $content);

        $handler = fopen($confFile, 'w');
        fwrite($handler, $content);
        fclose($handler);

        $xml = simplexml_load_file($confFile);
        unset($xml->home->url);
        $this->writeContentToFile($xml->asXML(), $confFile);
    }

    /**
     * Create a new module with a MVC structure sample.
     *
     * @param string $module Name of the new module.
     * @param string $baseModule If it's informed, create a copy of this module.
     */
    public function createModule($module, $baseModule=NULL)
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        if ( !file_exists($moduleDir) )
        {
            mkdir($moduleDir);
        }
        elseif ( count(scandir($moduleDir)) > 2 )
        {
            $this->error("createmodule: Module $module exists: $moduleDir");
        }

        // Create default directories
        echo "Creating default directory structure...\n";
        mkdir("$moduleDir/classes");
        mkdir("$moduleDir/db");
        mkdir("$moduleDir/etc");
        mkdir("$moduleDir/forms");
        mkdir("$moduleDir/grids");
        mkdir("$moduleDir/handlers");
        mkdir("$moduleDir/sql");

        // Create handler.class.php
        $template = $this->readTemplateContent('handler.class.php');
        $content = str_replace('#Module', ucfirst($module), $template);
        $this->writeContentToFile($content, "$moduleDir/handlers/handler.class.php");

        if ( !$baseModule )
        {
            $this->createSampleModule($module);
        }
        else
        {
            $this->copyModule($baseModule, $module);
        }

        $url = $this->MIOLO->getConf('home.url') . "/index.php?module=$module";
        echo "\nModule \"\033[0;32m{$module}\033[0m\" successfully created!\n";
        echo "You can check it at the following address: \033[0;34m{$url}\033[0m\n";
    }

    /**
     * Create a sample module.
     *
     * @param string $module Name of the new module.
     */
    public function createSampleModule($module)
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        // Create a main.inc.php
        echo "Creating main menu...\n";
        $template = $this->readTemplateContent('main.inc.php');
        $content = str_replace('#actions', $this->getPanelActionItem('browser', 'Navegador'), $template);
        $this->writeContentToFile($content, "$moduleDir/handlers/main.inc.php");

        // Create module.conf file
        echo "Creating configuration file...\n";
        $this->createModuleConf($module);

        // Copy "browser" complete samples
        echo "Copying samples to new module...\n";
        $this->copySamplesToModule($module);
    }

    /**
     * Create a MVC structure sample.
     *
     * @param string $module Module where the MVC must be created.
     */
    public function createMVC($module)
    {
        $db = 'browser';
        $dbDir = $this->MIOLO->getConf('home.modules') . "/$module/sql/$db.db";

        $this->setConfig("db.$db.system", 'sqlite', $module);
        $this->setConfig("db.$db.host", 'localhost', $module);
        $this->setConfig("db.$db.name", $dbDir, $module);
        $this->setConfig("db.$db.user", 'miolo', $module);
        $this->setConfig("db.$db.password", '', $module);

        $this->copySamplesToModule($module, $db);

        echo $this->getPanelActionItem('browser', 'Browser') . "\n";
        $url = $this->MIOLO->getConf('home.url') . "/index.php?module=$module&action=main:browser";
        echo "\nMVC sample successfully created!\n";
        echo "You can check it at the following address: \033[0;34m{$url}\033[0m\n";
    }

    /**
     * Copy the examples on templates directory to the given module.
     *
     * @param string $module Module name where the examples must be copied.
     * @param string $db Database sample name.
     */
    public function copySamplesToModule($module, $db=NULL)
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;
        $businessDir = $this->getConfig('namespace.business', $module);

        if ( !file_exists("$moduleDir/$businessDir") )
        {
            mkdir("$moduleDir/$businessDir");
        }
        if ( !file_exists("$moduleDir/handlers") )
        {
            mkdir("$moduleDir/handlers");
        }
        if ( !file_exists("$moduleDir/forms") )
        {
            mkdir("$moduleDir/forms");
        }
        if ( !file_exists("$moduleDir/grids") )
        {
            mkdir("$moduleDir/grids");
        }

        // Copy "browser" samples
        if ( !$db )
        {
            $db = $module;
        }

        $this->copySampleToModule('browser.class.php', $businessDir, $module, $db);
        $this->copySampleToModule('lookup.class.php', $businessDir, $module, $db);
        $this->copySampleToModule('browser.inc.php', 'handlers', $module, $db);
        $this->copySampleToModule('lookup.inc.php', 'handlers', $module, $db);
        $this->copySampleToModule('frmBrowser.class.php', 'forms', $module, $db);
        $this->copySampleToModule('frmSearchBrowser.class.php', 'forms', $module, $db);
        $this->copySampleToModule('grdBrowser.class.php', 'grids', $module, $db);

        // Copy database example with 'browser' table to module sql directory
        copy('templates/database.db', "$moduleDir/sql/$db.db");
    }

    /**
     * Copy a whole module to a new one.
     *
     * @param string $srcModule Module name to be copied.
     * @param string $module New module name.
     */
    public function copyModule($srcModule, $module)
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;
        $srcModuleDir = $this->MIOLO->getConf('home.modules') . '/' . $srcModule;

        if ( !file_exists($srcModuleDir) )
        {
            $this->error("createmodule: Base module \"$srcModule\" does not exist.");
        }

        echo "Copying base module directory...\n";
        $this->copyDir($srcModuleDir, $moduleDir);

        // Change module name to the new module
        echo "Changing new module name";
        $SrcModule = ucfirst($srcModule);
        $Module = ucfirst($module);
        echo ".";
        exec("find $moduleDir -type f -exec sed -i 's/$SrcModule/$Module/g' {} \;");
        echo ".";
        exec("find $moduleDir -type f -exec sed -i 's/$srcModule/$module/gi' {} \;");
        echo ".\n";
    }

    /**
     * Copy a directory.
     *
     * @param string $srcDir Directory to be copied.
     * @param string $destDir New directory.
     */
    private function copyDir($srcDir, $destDir)
    {
        if ( !file_exists($destDir) )
        {
            mkdir($destDir);
        }

        foreach ( scandir($srcDir) as $file )
        {
            if ( $file == '.' || $file == '..' || $file == '.svn' || substr($file, -1) == '~' )
            {
                continue;
            }

            if ( is_dir("$srcDir/$file") )
            {
                $this->copyDir("$srcDir/$file", "$destDir/$file");
            }
            else
            {
                copy("$srcDir/$file", "$destDir/$file");
            }
        }
    }

    /**
     * Create a handler file.
     *
     * @param string $module Module name.
     * @param string $handler Handler name.
     * @param string $title Handler title.
     * @param string $form Form class name
     * @return string Path where the handler will be saved.
     */
    public function createHandler($module, $handler, $title, $form)
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        if ( substr(strtolower($form), 0, 3) != 'frm' )
        {
            $form = 'frm' . ucfirst($form);
        }

        $template = $this->readTemplateContent('handler.inc.php');
        $content = str_replace('#title', $title, $template);
        $content = str_replace('#formName', $form, $content);

        $filePath = "$moduleDir/handlers/$handler.inc.php";
        $this->writeContentToFile($content, $filePath);
        return $filePath;
    }

    /**
     * Create a form.
     *
     * @param string $module Module name.
     * @param string $form Form class name.
     * @param string $title Form title.
     * @param string $fields Form fields declaration string (PHP code with a $fields array).
     * @return string Path where the form will be saved.
     */
    public function createForm($module, $form, $title, $fields = '')
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        $form = strtolower($form);
        if ( substr($form, 0, 3) == 'frm' )
        {
            $form = substr($form, 3);
        }

        $Form = ucfirst($form);

        $template = $this->readTemplateContent('frmForm.class.php');
        $content = str_replace('#title', $title, $template);
        $content = str_replace('#Form', $Form, $content);
        $content = str_replace('#fields', $fields, $content);

        $filePath = "$moduleDir/forms/frm$Form.class.php";
        $this->writeContentToFile($content, $filePath);
        return $filePath;
    }

    /**
     * Create a form.
     *
     * @param string $module Module name.
     * @param string $form Form class name.
     * @param string $title Form title.
     * @param string $grid Grid class name.
     * @param string $table Database table name.
     * @param array $filters Filters array with 'index' => 'Title' format.
     * @return string Path where the form will be saved.
     */
    public function createSearchForm($module, $form, $title, $grid, $table, $filters = array())
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        $form = strtolower($form);
        if ( substr($form, 0, 3) == 'frm' )
        {
            $form = substr($form, 3);
        }

        $grid = strtolower($grid);
        if ( substr($grid, 0, 3) == 'grd' )
        {
            $grid = substr($grid, 3);
        }

        $Form = ucfirst($form);
        $Grid = ucfirst($grid);

        $template = $this->readTemplateContent('frmSearchForm.class.php');
        $content = str_replace('#title', $title, $template);
        $content = str_replace('#Form', $Form, $content);
        $content = str_replace('#Grid', $Grid, $content);
        $content = str_replace('#table', $table, $content);
        $content = str_replace('#module', $module, $content);

        $filtersText = '';
        foreach ( $filters as $filterId => $filterTitle )
        {
            $filtersText .= '        $fields[] = ' . $this->generateField($filterId, $filterTitle) . ";\n";
        }
        $content = str_replace('#filters', $filtersText, $content);

        $filePath = "$moduleDir/forms/frm$Form.class.php";
        $this->writeContentToFile($content, $filePath);
        return $filePath;
    }

    /**
     * Create a grid.
     *
     * @param string $module Module name.
     * @param string $grid Grid class name.
     * @param string $title Grid title.
     * @param array $columns Array with the title of the columns.
     * @return string Path where the grid will be saved.
     */
    public function createGrid($module, $grid, $title, $columns = array())
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        $grid = strtolower($grid);
        if ( substr($grid, 0, 3) == 'grd' )
        {
            $grid = substr($grid, 3);
        }
        $Grid = ucfirst($grid);

        $template = $this->readTemplateContent('grdGrid.class.php');
        $content = str_replace('#title', $title, $template);
        $content = str_replace('#Grid', $Grid, $content);

        $columnsText = '';
        foreach ( $columns as $column )
        {
            $columnsText .= "        \$columns[] = new MGridColumn(_M('$column', \$module));\n";
        }
        $content = str_replace('#columnsText', $columnsText, $content);

        $filePath = "$moduleDir/grids/grd$Grid.class.php";
        $this->writeContentToFile($content, $filePath);
        return $filePath;
    }

    /**
     * Create a module configuration file (module.conf).
     *
     * @param string $module Module name.
     * @return string Path where the module.conf will be saved.
     */
    public function createModuleConf($module)
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        $template = $this->readTemplateContent('module.conf');
        $content = str_replace('#moduleDir', $moduleDir, $template);
        $content = str_replace('#module', $module, $content);

        $filePath = "$moduleDir/etc/module.conf";
        $this->writeContentToFile($content, $filePath);
        return $filePath;
    }

    /**
     * Create a business.
     *
     * @param string $module Module name.
     * @param string $table Table name.
     * @param array $filters Array with filters ids.
     * @return string Path where the business will be saved.
     */
    public function createBusiness($module, $table, $filters = array())
    {
        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;

        $template = $this->readTemplateContent('table.class.php');
        $content = str_replace('#Table', ucfirst($table), $template);
        $content = str_replace('#table', $table, $content);
        $content = str_replace('#Module', ucfirst($module), $content);
        $content = str_replace('#module', $module, $content);

        $conditionText = '';
        foreach ( $filters as $id )
        {
            $conditionText .= "        if ( \$filters->$id )\n";
            $conditionText .= "        {\n";
            $conditionText .= "            \$msql->setWhere(\"$id = '{\$filters->$id}'\");\n";
            $conditionText .= "        }\n";
        }
        $content = str_replace('#condition', $conditionText, $content);

        $filePath = "$moduleDir/db/$table.class.php";
        $this->writeContentToFile($content, $filePath);
        return $filePath;
    }

    /**
     * Create a theme based on an existing one.
     *
     * @param string $theme New theme name.
     * @param string $themeBase Theme base name.
     */
    public function createTheme($theme, $themeBase)
    {
        $themeDir = $this->MIOLO->getConf('home.themes') . '/' . $theme;
        $themeBaseDir = $this->MIOLO->getConf('home.themes') . '/' . $themeBase;

        if ( !file_exists($themeDir) )
        {
            mkdir($themeDir);
        }
        elseif ( count(scandir($themeDir)) > 2 )
        {
            $this->error("createtheme: Theme \"$theme\" already exists.");
        }
        if ( !file_exists($themeBaseDir) )
        {
            $this->error("createtheme: Theme base \"$themeBase\" does not exist.");
        }

        foreach ( scandir($themeBaseDir) as $file )
        {
            if ( $file == '.' || $file == '..' || $file == '.svn' )
            {
                continue;
            }
            if ( is_dir("$themeBaseDir/$file") )
            {
                mkdir("$themeDir/$file");
                foreach ( scandir("$themeBaseDir/$file") as $f )
                {
                    if ( $f == '.' || $f == '..' || $f == '.svn' || is_dir("$themeBaseDir/$file/$f") )
                    {
                        continue;
                    }

                    if ( $file == 'template' && $f == 'base.php' )
                    {
                        $base = fread(fopen("$themeBaseDir/$file/$f", 'r'), filesize("$themeBaseDir/$file/$f"));
                        $base = str_replace("$themeBase.css", "$theme.css", $base);
                        $this->writeContentToFile($base, "$themeDir/$file/$f");
                    }
                    else
                    {
                        copy("$themeBaseDir/$file/$f", "$themeDir/$file/$f");
                    }
                }
            }
            elseif ( $file == "$themeBase.css" )
            {
                copy("$themeBaseDir/$file", "$themeDir/$theme.css");
            }
            elseif ( $file == 'theme.class.php' )
            {
                $themeClass = fread(fopen("$themeBaseDir/$file", 'r'), filesize("$themeBaseDir/$file"));
                $themeClass = str_replace('Theme' . ucfirst($themeBase), 'Theme' . ucfirst($theme), $themeClass);
                $themeClass = str_replace("/$themeBase/template/", "/$theme/template/", $themeClass);
                $this->writeContentToFile($themeClass, "$themeDir/$file");
            }
            else
            {
                copy("$themeBaseDir/$file", "$themeDir/$file");
            }
        }
        echo "Theme \"$theme\" successfully created!\n";
    }

    /**
     * Get the PHP code for adding an action on the panel.
     *
     * @param string $handler Handler name.
     * @param string $title Action title.
     * @return string PHP code.
     */
    public function getPanelActionItem($handler, $title)
    {
        return "\$panel->addAction(_M('$title', \$module), \$ui->getImage('admin', 'globals.png'), \$module, 'main:$handler');";
    }

    /**
     * Extract strings to be translated.
     *
     * @global array $argv Command line arguments.
     */
    public function translateExtract()
    {
        global $argv;
        $mioloHome = $this->MIOLO->getConf('home.miolo');
        chdir("$mioloHome/misc/i18n");
        if ( exec('./extract_strings.sh') )
        {
            echo "Strings successfully exctracted!\n";
            echo "Edit the *.po files in $mioloHome/misc/i18n and then run \"$argv[0] translate generate\".\n";
        }
        else
        {
            $admin->error("tranlate extract: Couldn't extract strings.");
        }
    }

    /**
     * Generate translation files.
     */
    public function translateGenerate()
    {
        $mioloHome = $this->MIOLO->getConf('home.miolo');
        chdir("$mioloHome/misc/i18n");
        if ( exec('./generate_i18_files.sh') )
        {
            echo "Translation files successfully generated!\n";
        }
        else
        {
            $admin->error("tranlate extract: Couldn't generate translation files.");
        }
    }

    /**
     * Write a content to a file.
     *
     * @param string $content Content.
     * @param string $file File name.
     */
    private function writeContentToFile($content, $file)
    {
        $handler = fopen($file, 'w');
        fwrite($handler, $content);
        fclose($handler);
    }

    /**
     * Read the content of a template.
     *
     * @param string $file Template file name.
     * @return string Template content.
     */
    private function readTemplateContent($file)
    {
        return fread(fopen("templates/$file", 'r'), filesize("templates/$file"));
    }

    /**
     * Copy an example to a module.
     *
     * @param string $sample Example file.
     * @param string $dir Directory where the example will be saved.
     * @param string $module Module name.
     * @param string $db Database file (sqlite).
     */
    private function copySampleToModule($sample, $dir, $module, $db)
    {
        $content = fread(fopen("samples/$sample", 'r'), filesize("samples/$sample"));
        $content = str_replace('#module', $module, $content);
        $content = str_replace('#Module', ucfirst($module), $content);
        $content = str_replace('#db', $db, $content);
        $content = str_replace('#Db', ucfirst($db), $content);

        $moduleDir = $this->MIOLO->getConf('home.modules') . '/' . $module;
        $this->writeContentToFile($content, "$moduleDir/$dir/$sample");
    }

    /**
     * Error message for command line.
     *
     * @global array $argv Command line arguments array.
     * @param string $msg Error message.
     */
    public function error($msg)
    {
        global $argv;
        exit("{$argv[0]}: $msg\n");
    }

    /**
     * Generate a PHP code with a field declaration.
     *
     * @param string $id Field id.
     * @param string $title Field title.
     * @param string $type Field type: integer, double precision, boolean, date, text
     * @return string Field declaration.
     */
    public function generateField($id, $title, $type='')
    {
        switch ( $type )
        {
            case 'integer':
                $field = "new MIntegerField('$id', NULL, _M('$title', \$module), 10)";
                break;
            case 'double precision':
                $field = "new MFloatField('$id', NULL, _M('$title', \$module), 15)";
                break;
            case 'boolean':
                $field = "new MCheckBox('$id', 't', _M('$title', \$module))";
                break;
            case 'date':
                $field = "new MCalendarField('$id', NULL, _M('$title', \$module), 15)";
                break;
            default:
                $field = "new MTextField('$id', NULL, _M('$title', \$module), 50)";
                break;
        }

        return $field;
    }
}

?>