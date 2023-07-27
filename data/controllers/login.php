<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Login
* @version: 7.2 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2023-01-01
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
				$colApi = $this->Sys->id_column($idTable,'apikey');
				
				if($user[$colPass] == trim(md5($post['password'])) && $user[$colUser] == trim($post['username']))
				{
					$post['loggedin'] = TRUE;
					$post['table'] = $idTable;
					$post['line'] = $this->Sys->real_id($idTable,'username',$user[$colUser]);
					$post['password'] = $user[$colPass];
					$post['id_user'] = $user[$colId];
					$post['username'] = $user[$colUser];
					$post['jumbo'] = $user[$colJumbo];
					$post['apikey'] = $user[$colApi];

					$this->Sys->set_line($post);
					$_SESSION = $post;
					
					$this->Msg->set_msg('You are logged in! '.$post['username']);
					
					$controller = (file_exists(ROOT.'controllers/'.$post['username'].'.php'))?$post['username']:DEFAULTCONTROLLER;
					//header('Location:'.WEBROOT.DEFAULTCONTROLLER);	
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
				//$this->Msg->set_msg('Username not found!');
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
		$this->Msg->set_msg('You have logged out! ');
		
		// remove all session variables
		session_unset(); 
		// destroy the session 
		session_destroy();

		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
}
?>