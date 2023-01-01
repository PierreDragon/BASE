<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Main
* @version:	1.1 (main.php)
* @author: Pierre Martin
* @php: 7.4
* @revision: 2021-01-20
* @licence MIT
*/
class Main extends Controller
{	
	function __construct()
	{
		parent::__construct(DEFAULTDATABASE,'php','main');	
		if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}
	}
	function index()
	{
		parent::index();
	}
}
?>