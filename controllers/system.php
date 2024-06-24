<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: System
* @version: 7.4 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2024-06-24 12:38
* @added field level to users table
* @licence MIT
*/
class System extends Core\Controller
{
	function __construct()
	{
		parent::__construct('system','php','system');
		$this->data['title'] =' System';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		
		if(!isset($_SESSION['loggedin']) || $_SESSION['level']!=1)
		{
			$this->Msg->set_msg("You don't have the right to access this module.");
			header('Location:'.WEBROOT);
			exit();
		}
	}
	function index()
	{
	/*	if(isset($_SESSION['line'])>1 || empty($_SESSION))
		exit('No direct script access allowed');*/
		parent::index();
	}
	//IMPORTANT DO NOT DELETE ini() FUNCTION
	function ini()
	{
			$answer = @$_POST['inlineRadioOptions'];
			if(!isset($answer))
			{
				$refaction = WEBROOT.strtolower(get_class($this)).'/ini';
				$this->question('Are you sure you want to initialize the database ? '.$this->colorize('everything will be erase ! ','red'),$refaction);
				exit;
			}
			elseif($answer == 'yes')
			{
				$this->Sys->initialize();
				//For tables list. Usefull for core/controller 
				//$idtab = $this->Sys->id_table('tables');
				//$this->Sys->empty_table($idtab);
				$this->Msg->set_msg('You have initialized '.$this->data['title']);
			}
			header('Location:'.WEBROOT.strtolower(get_class($this)));
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
	/*function add_record($url)
	{
		$this->denied('add a record');
	}
	function edit_record($url)
	{
		$this->denied('edit a record');
	}*/
	function delete_record($url)
	{
		if(($url[TABLE]=='configs' || $url[TABLE]=='users' || $url[TABLE]=='tables' || $url[TABLE]=='operators' || $url[TABLE]=='scripts' || $url[TABLE]=='files' || $url[TABLE]=='maths' ) && $_SESSION['id_user'] !== "1" )
		{
			$this->denied('delete a record');
		}
		else
		{
			if($url[TABLE]=='files')
			{
				$rec = $this->DB->record($url[TABLE],$url[INDEX]);
				if(file_exists(DATADIRECTORY.$rec['file']) && $rec['file'] !== 'messages.php')
				{
					unlink(DATADIRECTORY.$rec['file']);
				}
			}
			parent::delete_record($url);
		}
	}
	function denied($string)
	{
		$this->Msg->set_msg("You don't have the right to $string in this module.");
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	
	function list_files()
	{
		foreach (glob(DATADIRECTORY."*.php") as $filename)
		{
			echo "$filename size " . filesize($filename) . "\n";
		}
	}
}
?>