<?php 
declare(strict_types=1);
session_start();
error_reporting(E_ALL);
ini_set("display_errors", "1"); // display_errors : 0 or 1
set_time_limit(60); //seconds
date_default_timezone_set('America/New_York'); 

define('VERSION','2.1');
define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);
chdir(ROOT);

define('DEFAULTCONTROLLER','main');
define('DEFAULTDATABASE','data');

define('CLASSDIRECTORY', realpath(ROOT . 'classes') . DIRECTORY_SEPARATOR);
define('DATADIRECTORY', realpath(ROOT . 'data') . DIRECTORY_SEPARATOR);
define('VIEWDIRECTORY', realpath(ROOT . 'views') . DIRECTORY_SEPARATOR);

define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
define('ASSETDIRECTORY', WEBROOT . 'assets/');

define('CONTROLLER', 0);
define('ACTION', 1);
define('PRIMARY', 1);
define('TABLE', 2);
define('LINE', 2);
define('FIELD', 3);
define('INDEX', 3);
define('VALUE', 4);

// Vérifie que les répertoires critiques existent
foreach (['CLASSDIRECTORY', 'DATADIRECTORY', 'VIEWDIRECTORY'] as $dirConst) {
    if (!is_dir(constant($dirConst))) {
        die("⚠️ Erreur : le dossier " . constant($dirConst) . " est introuvable.");
    }
}

require(ROOT . 'core/model.php');
require(ROOT . 'core/controller.php');

?>