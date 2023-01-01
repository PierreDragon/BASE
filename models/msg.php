<?php  
class Msg extends Model
{
	public function get_msg($unescape=FALSE)
	{
		$msg = 'Bienvenue';
		$int = $this->count_lines(1);
		if($int>0)
		{
			$msg = $this->get_cell(1,$int,1);
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
		$post['message'] = $string;
		$post['datetime'] =  date("Y-m-d H:i:s",time());
		if(!empty($string))
		{
			$this->set_line($post);
		}
	}
	
}
?>