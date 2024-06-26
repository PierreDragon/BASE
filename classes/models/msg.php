<?php  
class Msg extends  Core\Model
{
	public function initialize()
	{
		unset($this->data);
		$this->data[0][0][1]='messages';
		$this->data[1][0][1]='id_message';
		$this->data[1][0][2]='message';
		$this->data[1][0][3]='user';
		$this->data[1][0][4]='datetime';
		$this->save();
	}
	
	public function get_msg($unescape=FALSE)
	{
		$msg = 'Bienvenue';
		$int = $this->count_lines(1);
		if($int>0)
		{
			$msg = $this->get_cell(1,$int,2);
			if($unescape)
			{
				$this->unescape($msg);	
			}
		}
		return $msg;
	}
	public function set_msg($string)
	{
		$int = $this->count_lines(1)+1;
		$post['table'] = 1;
		$post['line'] = $int;
		$post['id_message'] = $int;
		if(isset($_SESSION['username']))
		{
			$post['user'] = $_SESSION['username'];
		}
		$post['message'] = $string;
		$post['datetime'] =  date("Y-m-d H:i:s",time());
		if(!empty($string))
		{
			$this->set_line($post);
		}
	}
	
}
?>