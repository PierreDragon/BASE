<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Rock
* @version: 7.2 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2023-01-01
* @licence MIT
*/
class Rock extends Core\Controller
{	
	function __construct()
	{
		parent::__construct('rock','php','rock');
		
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
	
	function show_table($url)
	{
		$debut = microtime(true)*1000;

		if(isset($url[TABLE]) && $this->DB->table_exists($url[TABLE]))
		{
			$strTable = $url[TABLE];
			$this->data['strTable'] = $strTable;
		}
		else
		{
			$this->Msg->set_msg('Record not found in: '.$url[TABLE]);
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}
		//CONTROLLER
		$this->verif_session($strTable);

		//LEFT
		$this->properties('left',$strTable);

		//CONTENTS
		$this->data['columns'] = $this->DB->columns($strTable);

		if(isset($url[FIELD]) && ! is_numeric($url[FIELD]))
		{
			$this->DB->order_by($strTable,$url[FIELD]);
		}
		$records = $this->DB->all(false);
		if(isset($records))
		{
			$x=0;
			$p=0;
			$tbody ='';
			$page[1]='';
			foreach($records as $key=>$t)
			{
				if($key < $this->data['offset']) continue; 
				$x+=1;
				if($x > $this->data['showlimit']) 
				{
					$p+=1;
					$page[$p] = $tbody;
					$tbody='';
					$x=0;
				}
				
				$tbody .= '<tr id="tr'.$key.'">';
				$i = 0;
				foreach($t as $k=>$value)
				{
					$table = $this->DB->id_table($strTable);
					$col = $this->DB->column_name($table,$k);
					if(substr($col, -3, 3)=="_id")
					{
						$strForeignTable = stristr($col, '_', true).'s';
						$col = stristr($col, '_', true);
						try
						{
							$rec = $this->DB->where_unique($strForeignTable,'id_'.$col,$value);

							$tbody .= '<td>';
							if($rec)
							{
								foreach($rec as $r=>$val)
								{
									if(($r <=> 2) !== 0) continue;
									$tbody .=  '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$strForeignTable.'/id_'.$col.'/'.$rec[1].'">'.$rec[2].'</a>';
								}
							}
							$tbody .= '</td>';
						}
						catch (Throwable $t)
						{
							$this->Msg->set_msg($t->getMessage());
						}
					}
					elseif(substr($col, 0, 3)=="id_")
					{
						$arr = explode('_',$col);
						if(isset($arr[1]))
						{
							$str = $arr[1].'s';
							try
							{
								$records =$this->DB->where('rules','master','==',$str);
								if($records)
								{
									$a = '<span>'.$value.' </span>';
									foreach($records as $r=>$rule)
									{
										$a .= '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$rule[3].'/'.$arr[1].'_'.$arr[0].'/'.$value.'" title="Slave: '.$rule[3].'">['.$rule[3].']</a>';
									}
									$tbody .= '<td>'.$a.'</td>';
								}
								else
								{
									//$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
									$tbody .= '<script>
									$(document).ready(function(){
									$("#td'.$key.'").editable("'.WEBROOT.'main/set_cell/'.$table.'/'.$key.'/'.$k.'",{name: \'value\'});
									});
									</script>';
									$tbody .= '<td id="td'.$key.'" style="text-decoration:underline;">'.$value.'</td>';
								}
							}
							catch (Throwable $t)
							{
								$this->Msg->set_msg($t->getMessage());
							}
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
					}
					elseif(strpos($value, '.mp3'))
					{
						$tbody .= '<td><audio src="'.WEBROOT.'data/musique/'.$value.'" controls="controls"></audio></td>';
					}
					else
					{
						$tbody .= '<td>'.$value.'</td>';
					}
					$i++;
				}
				while($i < $this->data['nbrcolonne'] )
				{
					$tbody .= '<td>-</td>';
					$i++;
				}

				$tbody .='<td><a title="Edit this record ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_record/'.$this->data['thead'].'/'.$key.' ">edit</a></td>';
				$tbody .= '<td><a title="Are you sure you want to delete this record ?"  href=" '.WEBROOT.$this->data['controller'].'/delete_record/'.$this->data['thead'].'/'.$key.' ">delete</a></td>';

				$tbody .= '</tr>';
			}
			//$this->data['tbody'] = $tbody;
			$page[$p+1] = $tbody;
			$end = count($page);
			$pagination = '<a href="'.WEBROOT.$this->data['controller'].'/show_table/'.$this->data['thead'].'?page=1">&laquo;</a>';
			foreach($page as $i=>$pag)
			{
				$pagination .= '<a href="'.WEBROOT.$this->data['controller'].'/show_table/'.$this->data['thead'].'?page='.$i.'">'.$i.'</a>';
			}
			$pagination .= '<a href="'.WEBROOT.$this->data['controller'].'/show_table/'.$this->data['thead'].'?page='.$end.'">&raquo;</a>';
			$this->data['pagination'] = $pagination;
			$this->data['page'] = $page;
			$pg = (isset($_GET['page'])?$_GET['page']:1);
			$this->data['tbody'] = $page[$pg];
		}
		$fin = microtime(true)*1000;
		$this->data['performance'] = $fin-$debut;
		$this->data['content'] = $this->Template->load('tables', $this->data,TRUE);
		//LAYOUT
		$this->Template->load('layout',$this->data);
	}
	
	function show($url)
	{
		$debut = microtime(true)*1000;
		try
		{
			//$records = $this->DB->get_where($url[TABLE],$url[FIELD],'==',$url[VALUE]);
			$columns = $this->DB->columns($url[TABLE]);
			$records = $this->DB->select_where($columns,$url[TABLE],$url[FIELD],'==',$url[VALUE]);
		}
		catch(Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}

		if($records)
		{
			$this->data['nrecords'] = count($records);
			$strTable = $url[TABLE];
			//LEFT
			$this->properties('left',$strTable);
			//CONTENT
			$this->data['columns'] = $this->DB->columns($strTable);
			//$this->DB->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
			$x=0;
			$p=0;
			$tbody ='';
			$page[1]='';
			foreach($records as $key=>$t)
			{
				if($key < $this->data['offset']) continue; 
				$x+=1;
				if($x > $this->data['showlimit']) 
				{
					$p+=1;
					$page[$p] = $tbody;
					$tbody='';
					$x=0;
				}
				$tbody .= '<tr>';
				$i = 0;
				foreach($t as $k=>$value)
				{
					$table = $this->DB->id_table($strTable);
					$col = $this->DB->column_name($table,$k);
					if(substr($col, -3, 3)=="_id")
					{
						$strForeignTable = stristr($col, '_', true).'s';
						$col = stristr($col, '_', true);
						$rec = $this->DB->where_unique($strForeignTable,'id_'.$col,$value);
						$tbody .= '<td>';
						if($rec)
						{
							foreach($rec as $r=>$value)
							{
								if(($r <=> 2) !== 0) continue;
								$value = '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$strForeignTable.'/id_'.$col.'/'.$rec[1].'">'.$rec[2].'</a>';
								$tbody .= $value;
							}
						}
						$tbody .= '</td>';
					}
					elseif(substr($col, 0, 3)=="id_")
					{
						$arr=explode('_',$col);
						if($col=='id_'.$arr[1])
						{
							$str = $arr[1].'s';
							try
							{
								$records =$this->DB->where('rules','master','==',$str);
								if($records)
								{
									$a = '<span>'.$value.' </span>';
									foreach($records as $i=>$rule)
									{
										$a .= '<a href="'.WEBROOT.strtolower(get_class($this)).'/show/'.$rule[3].'/'.$arr[1].'_'.$arr[0].'/'.$value.'" title="Slave: '.$rule[3].'">['.$rule[3].']</a>';
									}
									$tbody .= '<td>'.$a.'</td>';
								}
								else
								{
									$tbody .= '<td>'.$value.'</td>';
								}
							}
							catch (Throwable $t)
							{
								$tbody .= '<td>'.$value.'</td>';
							}
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
					}
					elseif(strpos($value, '.mp3'))
					{
						$tbody .= '<td><audio src="'.WEBROOT.'data/musique/'.$value.'" controls="controls"></audio></td>';
					}
					else
					{
						$tbody .= '<td>'.$value.'</td>';
					}
					$i++;
				}
				while($i < $this->DB->table_nbrcolumns)
				{
					$tbody .= '<td>-</td>';
					$i++;
				}
				$tbody .='<td><a title="Edit this record ?"  href="'.WEBROOT.strtolower(get_class($this)).'/edit_record/'.$strTable.'/'.$key.' ">edit</a></td>';
				$tbody .= '<td><a title="Are you sure you want to delete this record ?"  href=" '.WEBROOT.strtolower(get_class($this)).'/delete_record/'.$strTable.'/'.$key.' ">delete</a></td>';
				$tbody .= '</tr>';
			}
			//$this->data['tbody'] = $tbody;
			$page[$p+1] = $tbody;
			$end = count($page);
			$pagination = '<a href="'.WEBROOT.$this->data['controller'].'/show_table/'.$this->data['thead'].'?page=1">&laquo;</a>';
			foreach($page as $i=>$pag)
			{
				$pagination .= '<a href="'.WEBROOT.$this->data['controller'].'/show_table/'.$this->data['thead'].'?page='.$i.'">'.$i.'</a>';
			}
			$pagination .= '<a href="'.WEBROOT.$this->data['controller'].'/show_table/'.$this->data['thead'].'?page='.$end.'">&raquo;</a>';
			$this->data['pagination'] = $pagination;
			$this->data['page'] = $page;
			$pg = (isset($_GET['page'])?$_GET['page']:1);
			$this->data['tbody'] = $page[$pg];
		}
		else
		{
			$this->Msg->set_msg("Record not found in: $url[TABLE]");
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}
		$fin = microtime(true)*1000;
		$this->data['performance'] = $fin-$debut;
		$this->data['content'] = $this->Template->load('tables', $this->data,TRUE);
		//LAYOUT
		$this->Template->load('layout',$this->data);
	}

	/*function special()
	{			
		$songs = $this->DB->table('songs');
		foreach($songs as $s=>$song)
		{
			$r = $this->DB->get_where_unique('bands','band',$song[2]);
			$this->DB->set_cell(2,$s,6,$r[1]);
		}
		$this->DB->save();
	}*/
}
?>