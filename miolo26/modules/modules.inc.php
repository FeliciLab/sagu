<?php



$MIOLO = MIOLO::getInstance();
$module = MIOLO::getCurrentModule();
$action = MIOLO::getCurrentAction();
global $autoload;

$autoload->setFile('SAGU', $MIOLO->getModulePath('basic', 'classes/sagu.class'));
$autoload->setFile('SDatabase', $MIOLO->getModulePath('basic', 'classes/SDatabase.class'));
if ($MIOLO->getConf('options.miolo2modules'))
{
    $MIOLO->uses('classes/sAutoload.class', 'basic');

    $sAutoload = new sAutoload();
    $sAutoload->definePaths();
    //classes requeridas pelo base
    $MIOLO->uses('classes/BString.class.php', 'base');
    $MIOLO->uses('classes/bBaseDeDados.class.php', 'base');
    $MIOLO->uses('classes/bCatalogo.class.php', 'base');
    $MIOLO->uses('classes/bForm.class.php', 'base');
    $MIOLO->uses( 'classes/bFormCadastro.class.php','base');
    $MIOLO->uses( 'classes/bFormBusca.class.php','base');
    $MIOLO->uses('classes/bTipo.class.php', 'base');
    $MIOLO->uses( 'classes/bBarraDeFerramentas.class.php','base');
    $MIOLO->uses( 'classes/bJavascript.class.php','base');
    $MIOLO->uses( 'classes/bInfoColuna.class.php','base');
    $MIOLO->uses( 'classes/bBooleano.class.php','base');
    $MIOLO->uses( 'classes/bUtil.class.php','base');
    $MIOLO->uses('classes/bInfoColuna.class.php', 'base');

    //classes requeridas pelo sagu 
    $MIOLO->uses('classes/sBusiness.class', 'basic');
    $MIOLO->uses('classes/SDatabase.class', 'basic');
    $MIOLO->uses('types.class', 'basic');
    $MIOLO->uses('classes/SType.class', 'basic');

    //adicionados para a multiunidade
    $MIOLO->uses('db/BusUnit.class', 'basic');
    $MIOLO->uses('db/BusCourseCoordinator.class', 'academic');
    $MIOLO->uses('db/BusCenter.class', 'academic');
    $MIOLO->uses('types/BasSessao.class', 'basic');
    $MIOLO->uses('classes/sagu.class', 'basic');
    //fim dos adicionados
}

// Check login false quando for acao permitida
if ( SAGU::isAllowedAction() )
{
    $MIOLO->setConf('login.check', 'false');
}

//
// defines user access right constants, which are used in the
// $NIOLO->checkAccess() and ThemeMenu->addUserOption() methods.
//
define('A_ACCESS',    1); // 000001
define('A_QUERY',     1); // 000001

define('A_INSERT',    3); // 000010
define('A_DELETE',    5); // 000100
define('A_UPDATE',    9); // 001000
define('A_EXECUTE',  17); // 001111

define('A_SYSTEM',   31); // 011111
define('A_ADMIN',    31); // 011111

define('A_DEVELOP',  32); // 100000

// Constantes para o correto funcionamento do módulo.
define(DB_TRUE, 't');
define(DB_FALSE, 'f');
define(FUNCAO_BUSCAR, 'buscar');
define(FUNCAO_EDITAR, 'editar');
define(FUNCAO_INSERIR, 'inserir');
define(FUNCAO_REMOVER, 'remover');
define(FUNCAO_EXPLORAR, 'explorar');
define(MODULO, 'base');
define(DB_NAME, 'base');

// Constantes para tamanho de campo.
define(T_CODIGO, 10);
define(T_INTEIRO, 10);
define(T_DESCRICAO, 25);
define(T_VERTICAL_TEXTO, 4);
define(T_HORIZONTAL_TEXTO, 50);

// Utilizado para sinalizar que esta em uma instalacao SAGU
$MIOLO->setConf('temp.is.from.sagu', true);
?>