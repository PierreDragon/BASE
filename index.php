<?php
declare(strict_types=1);
session_start();
error_reporting(E_ALL);
// display_errors : 1 or off 
ini_set("display_errors", "1");
set_time_limit(60); //seconds
date_default_timezone_set('America/New_York'); 

define('VERSION','2.0');
define('WEBROOT',str_replace('index.php','',$_SERVER['SCRIPT_NAME']));
define('ROOT',str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']));
define('DEFAULTCONTROLLER','main');
define('DEFAULTDATABASE','data');
define('CLASSDIRECTORY',ROOT.'classes/');
define('DATADIRECTORY',ROOT.'data/');
define('VIEWDIRECTORY',ROOT.'views/');
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

if (isset($_GET['jumbo'])) {$_SESSION['jumbo'] = $_GET['jumbo'];}
		
// - The .htaccess file
// ** RewriteEngine on
// ** RewriteRule ^ ([a-zA-Z0-9 \ - \ _ \ /] *) $ index.php? Url = $ 1 [QSA, L] ** /

// - $ _GET ['url'] represents the segment or segments of the URL.
// - We must explode the segments to get the name of the class and the action separately.
// - The @ character prevents the display of an error when the segments are empty.
$url ='';
if(isset($_GET['url']))
{
    $url = @explode('/',$_GET['url']);
}
// - Add a default controller to avoid errors
// - in case the controller is not specified in the URL.
// - For example if you go to this address: exemple.com or exemple.com/index.php
// - In the following code: "main" is the default controller.
$controller = (empty($url[CONTROLLER]))?DEFAULTCONTROLLER:$url[CONTROLLER];

// - This condition checks whether the class file of the called controller exists.
// - Otherwise the program loads the page not found. (error 404)
if(file_exists('controllers/'.$controller.'.php'))
{
	require(ROOT.'controllers/'.$controller.'.php');
	$controller = new $controller();
	
	// - Check if the action segment is empty, if so, replace the action with: "index"
	// - which must be declared in all the controllers to avoid errors.
	$action = (empty($url[ACTION]))?'index':$url[ACTION];

	// - Check if the instance of the $ controller class has this method (action),
	// - Otherwise the program loads the default action: index.
	$action = (method_exists($controller,$action))?$action:'index';	

	$controller->$action($url);
}
else
{
	header('Location: http://'.$_SERVER['HTTP_HOST'].WEBROOT.'404.html');
}
?>