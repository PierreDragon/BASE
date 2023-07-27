<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Message
* @version: 7.2 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2023-01-01
* @licence MIT
*/
class Message extends Core\Controller
{
	function __construct()
	{
		parent::__construct('messages','php','message');
		// <HEAD>
		$this->data['title'] =' Messages';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		
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
	function add_table()
	{
		$this->denied('add a table ');
	}	
	function edit_table($url)
	{
		$this->denied('edit a table');
	}
	function delete_table($url)
	{		
		$this->denied('delete table');
	}
	function add_field($url)
	{
		$this->denied('add a field');
	}
	function edit_field($url)
	{
		$this->denied('edit a field');
	}
	function delete_field($url)
	{
		$this->denied('delete a field');
	}
	function add_record($url)
	{
		$this->denied('add a record');
	}
	function edit_record($url)
	{
		$this->denied('edit a record');
	}
	function delete_record($url)
	{
		$this->denied('delete a record');
	}
	function denied($string)
	{
		$this->Msg->set_msg("You don't have the right to $string in this module.");
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}

	function empty_table($url)
	{	
		$strTable=$url[TABLE];
		$this->DB->empty_table($this->DB->id_table($strTable));
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
	}
}
?>