<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class Hello extends Core\Controller
{	
	function __construct()
	{
		parent::__construct('hello','php','hello');	
		/*if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}*/
		
		//$this->data['title'] = ' '.ucfirst(DEFAULTCONTROLLER).' ';
		//<HEAD>
		//$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		// NAVIGATION
	//	$this->data['nav'] = $this->Template->load('nav',$this->data,TRUE); 
		//HEADER
		//$this->data['header']= $this->Template->load('header', $this->data,TRUE);
		// FOOTER
		//$this->data['footer'] = $this->Template->load('footer', $this->data,TRUE);
	}
	function index()
	{
		//parent::index();
		$fields = ['id_note','note'];
		$notes = $this->DB->select($fields,'notes');
		echo '<pre>';
		print_r($notes);
		echo '</pre>';
		
	}
}
?>