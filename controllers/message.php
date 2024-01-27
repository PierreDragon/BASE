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
	function show_table($url)
	{
		$debut = microtime(true);

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
			$nombre[1] = ''; 
			foreach($records as $key=>$t)
			{
				if($key < $this->data['offset']) continue; 
				$x+=1;
				if($x > $this->data['showlimit']) 
				{
					$p+=1;
					$page[$p] = $tbody;
					$nombre[$p] = $this->data['showlimit'];
					$tbody='';
					$x=1;
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
						catch (\Exception $t)
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
								//NEW
								$idImage = $value;
							}
							catch (\Exception $t)
							{
								$this->Msg->set_msg($t->getMessage());
							}
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
					}
					elseif($col == 'image')
					{
						$tbody .= '<td><img id="img'.$idImage .'"  class="minresize" src="'.ASSETDIRECTORY.'uploads/'.$value.'" alt="'.$value.'" title="'.$value.'" onclick="$(this).toggleClass(\'minresize\');" /></td>';
						//$tbody .= '<td><img id="img'.$idImage .'"  class="minresize" src="'.ASSETDIRECTORY.'uploads/'.$value.'" alt="'.$value.'" title="'.$value.'" onclick="$(this).removeClass(\'maxresize\');" /></td>';
					}
					elseif($col == 'message')
					{
						$this->DB->unescape($value);
						$value = strip_tags($value);
						$tbody .= '<td>'.$value.'</td>';
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
			$nombre[$p+1] = $x;
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
			$this->data['nombre'] = $nombre[$pg];
		}
		$fin = microtime(true);
		$this->data['performance'] = $fin-$debut;
		$this->data['content'] = $this->Template->load('tables', $this->data,TRUE);
		//LAYOUT
		$this->Template->load('layout',$this->data);
	}

	
}
?>