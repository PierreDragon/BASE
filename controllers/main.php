<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Main
* @version: 7.2 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2023-01-01
* @licence MIT
*/
class Main extends Core\Controller
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