<?php
include("config.php");

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