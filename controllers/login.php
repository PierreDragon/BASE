<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Login
* @version: 7.3 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2024-06-24 12:38
* @added field level to users table
* @licence MIT
*/
class Login extends Core\Controller
{
	function __construct()
	{
		parent::__construct(DEFAULTDATABASE,'php','main');
	}
	function index()
	{
		$post = @$_POST;
		try
		{
			$user = $this->Sys->where_unique('users','username',@$post['username']);
			if($user)
			{
				$idTable = $this->Sys->id_table('users');
				$colId = $this->Sys->id_column($idTable,'id_user');
				$colPass = $this->Sys->id_column($idTable,'password');
				$colUser = $this->Sys->id_column($idTable,'username');
				$colJumbo = $this->Sys->id_column($idTable,'jumbo');
				$colLevel = $this->Sys->id_column($idTable,'level');
				
				if($user[$colPass] == trim(md5($post['password'])) && $user[$colUser] == trim($post['username']))
				{
					$post['loggedin'] = TRUE;
					$post['table'] = $idTable;
					$post['line'] = $this->Sys->real_id($idTable,'username',$user[$colUser]);
					$post['password'] = $user[$colPass];
					$post['id_user'] = $user[$colId];
					$post['username'] = $user[$colUser];
					$post['jumbo'] = $user[$colJumbo];
					$post['level'] = $user[$colLevel];

					$this->Sys->set_line($post);
					$_SESSION = $post;
					
					$this->Msg->set_msg('You are logged in! '.$post['username']);
					
					$controller = (file_exists(ROOT.'controllers/'.$post['username'].'.php'))?$post['username']:DEFAULTCONTROLLER;
					header('Location:'.WEBROOT.$controller);						
					exit;
				}
				else
				{
					$this->Msg->set_msg('Username not found!');
					$this->data['action'] = WEBROOT.strtolower(get_class($this));
					$this->Template->load('login',$this->data);
				}
			}
			else
			{
				$this->Msg->set_msg('Create your own base ! | <a href="'.WEBROOT.'login">Login</a>');
				$this->data['action'] = WEBROOT.strtolower(get_class($this));
				$this->Template->load('login',$this->data);
			}
		}
		catch(Exception $e)
		{
			$this->Msg->set_msg($e->getMessage());
			$this->data['action'] = WEBROOT.strtolower(get_class($this));
			$this->Template->load('login',$this->data);
		}
	}
	
	function logout()
	{
		if(isset($_SESSION['loggedin']))
		{
			$this->DB->save(TRUE);
			$idTable = $this->Sys->id_table('users');
			$colLoggedin = $this->Sys->id_column($idTable,'loggedin');
			$realID = $this->Sys->real_id($idTable,'id_user',$_SESSION['id_user']);
			$this->Sys->set_cell($idTable,$realID,$colLoggedin,"0");
		}		
		//$this->Msg->set_msg('You have logged out! ');
		$this->Msg->set_msg('Please provide a base name and a password! | <a href="/login">Login</a>');
		//$this->Msg->set_msg('Please provide a base name and a password!');
		// remove all session variables
		session_unset(); 
		// destroy the session 
		session_destroy();

		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
	
	function create()
	{
		try
		{
			$post = @$_POST;
			
			$idTable = $this->Sys->id_table('users');
			//$post['loggedin'] = TRUE;
			$post['table'] = $idTable;
			//var_dump($_POST); exit;
			if(empty($post['username']) || empty($post['password']))
			{
				$this->Msg->set_msg('Please provide a base name and a password!  | <a href="/login">Login</a>');
				//header('Location:'.WEBROOT.strtolower(get_class($this).'/create'));	
				$this->data['action'] = WEBROOT.strtolower(get_class($this).'/create');
				$this->Template->load('login-create',$this->data);
				exit;
			}
			elseif(file_exists(DATADIRECTORY.$post['username'].'.php'))
			{
				$this->Msg->set_msg('<strong style="color:tomato">This base already exists ! Choose another name.</strong> | <a href="/login">Login</a>');
				/*$this->data['action'] = WEBROOT.strtolower(get_class($this).'/create');
				$this->Template->load('login-create',$this->data);*/
				header('Location:'.WEBROOT.strtolower(get_class($this).'/create'));	
				exit;
			}
			//SANITIZE
			$post['username'] = strip_tags($post['username']);
			$post['password'] = strip_tags($post['password']);
			$post['password'] = trim(md5($post['password']));
			$post['jumbo'] = "1"; 
			$post['apikey'] = $post['password']; 
			//var_dump($post); exit;
			$this->Sys->add_line($post,'id_user');
			$_SESSION = $post;
				
			$this->Msg->set_msg('Congratulation ! You have created your base! <strong>'.$post['username']. '</strong> | <a href="/login">Login</a>');
			$controller = (file_exists(ROOT.'controllers/'.$post['username'].'.php'))?$post['username']:DEFAULTCONTROLLER;
			header('Location:'.WEBROOT.$controller);						
			exit;
		}
		catch(Exception $e)
		{
			$this->Msg->set_msg($e->getMessage());
			$this->data['action'] = WEBROOT.strtolower(get_class($this).'/create');
			$this->Template->load('login-create',$this->data);
		}
	}
}
?>