<?php 
declare(strict_types=1);
session_start();
error_reporting(E_ALL);
ini_set("display_errors", "1"); // display_errors : 1 or off 
set_time_limit(60); //seconds
date_default_timezone_set('America/New_York'); 

define('VERSION','2.0');
define('ROOT',__DIR__.DIRECTORY_SEPARATOR);
define('DEFAULTCONTROLLER','main');
define('DEFAULTDATABASE','data');
define('CLASSDIRECTORY',ROOT.'classes/');
define('DATADIRECTORY',ROOT.'data/');
define('VIEWDIRECTORY',ROOT.'views/');
define('WEBROOT',str_replace('index.php','',$_SERVER['SCRIPT_NAME']));
define('ASSETDIRECTORY',WEBROOT.'assets/');
define('CONTROLLER',0);
define('ACTION',1);
define('PRIMARY',1);
define('TABLE',2);
define('LINE',2);
define('FIELD',3);
define('INDEX',3);
define('VALUE',4);

require(ROOT.'core/model.php');
require(ROOT.'core/controller.php');
?>