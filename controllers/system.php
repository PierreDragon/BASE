<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: System
* @version:	7.3
* @author: pierre.martin@live.ca
* @php: 7.4
* @revision: 2022-11-13 12:35
* @licence MIT
*/
class System extends Controller
{
	function __construct()
	{
		parent::__construct('system','php','system');
		// <HEAD>
		$this->data['title'] =' System';
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		
		if(!isset($_SESSION['loggedin']) || $_SESSION['id_user']!=1)
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}
	}
	function index()
	{
		if(isset($_SESSION['line'])>1 || empty($_SESSION))
		exit('No direct script access allowed');
		parent::index();
	}
	function renumber_column($url)
	{
		$strTable=$url[TABLE];		
		$this->properties('left',$strTable);
		$post = @$_POST;
		try
		{
			if(!$this->DB->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			@$this->DB->renumber($strTable,$post['strfield'],$post['value']);
			$this->Msg->set_msg('You have renumbered column '.$post['strfield'].' from '.$post['value'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Renumber a column of the table: $strTable" ;
		$this->data['placeholder'] = 'Renumber a column';
		
		//$this->data['columns'] = $this->DB->get_columns_of('actions');
		$this->data['columns'] = array(1=>'strfield',2=>'value');
		
		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'column',' : Column to be renumbered');	
		$this->data['divvalue'] = $this->Template->makediv('value','start',' : Beginning value');	
	
		$this->data['table'] = $this->DB->get_id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/renumber_column/'.$strTable;
		$this->data['content'] = $this->Template->load('renumber-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function show_table($url)
	{
		$debut = microtime(true)*1000;

		if(isset($url[TABLE]) && $this->DB->table_exists($url[TABLE]))
		{
			$strTable = $url[TABLE];
		}
		else
		{
			$this->Msg->set_msg("Record not found in: $url[TABLE]");
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit();
		}

		//$this->DB->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
		
		//LEFT
		$this->properties('left',$strTable);
		
		//CONTENTS
		$this->data['columns'] = $this->DB->get_columns_of($strTable);

		if(isset($url[FIELD]))
		{
			$this->DB->order_by($strTable,$url[FIELD]);
		}

		$records = $this->DB->all();

		if(isset($records))
		{
			$tbody ='';
			foreach($records as $key=>$t)
			{
				$tbody .= '<tr id="tr'.$key.'">';
				$i = 0;
				foreach($t as $k=>$value)
				{
					$table = $this->DB->get_id_table($strTable);
					$col = $this->DB->get_column_name($table,$k);
					if(substr($col, -3, 1)=="_")
					{
						$strForeignTable = stristr($col, '_', true).'s';
						$col = stristr($col, '_', true);

						$rec = $this->DB->get_where_unique($strForeignTable,'id_'.$col,$value);
						$intForeignTable = $this->DB->get_id_table($strForeignTable);
						$tbody .= '<td>';
						if($rec)
						{
							$tbody .= $rec[1];
						}
						$tbody .= '</td>';
					}
					elseif(substr($col, 2, 1)=="_")
					{
						$arr=null;
						if(strstr($col, '_'))
						{
							$arr=explode('_',$col);
						}
						if($col=='id_'.$arr[1] && isset($arr))
						{
							try
							{
								if($strTable=='blocks')
								{
									$tbody .= '<script>
									$(document).ready(function(){
									$("#td'.$key.'").editable("'.WEBROOT.'system/set_cell/'.$table.'/'.$key.'/'.$k.'",{name: \'value\'});
									});
									</script>';
									$tbody .= '<td id="td'.$key.'" style="text-decoration:underline;">'.$value.'</td>';
								}
								else
								{
									$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
								}
							}
							catch (Throwable $t)
							{
								$tbody .= '<td id="td'.$key.'">'.$value.'</td>';
							}
						}
						elseif($strTable=='blocks' && $col=='block')
						{
							//<a href=" '.WEBROOT.DEFAULTCONTROLLER'/load_script/'">'.$value.'</a>
							//$_SESSION['sblock'] = $value;
							//get_field_value_where_unique($strTable,$strColumn,$unique,$strField)
							$id_block = $this->Sys->get_field_value_where_unique($strTable,'block',$value,'id_block');
							$tbody .= '<td><a href="'.WEBROOT.DEFAULTCONTROLLER.'/load_script_get/'.$id_block.'">'.$value.'</a></td>';
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
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

				switch($this->data['thead'])
				{
				case 'actions':
					$tbody .='<td><a title="Edit this action ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_action/'.$this->data['thead'].'/'.$key.' ">edit</a></td>';
				break;
				default:
					//$tbody .='<td><a title="Edit this record ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_record/'.$this->data['thead'].'/'.$R.' ">edit</a></td>';
					$tbody .='<td><a title="Edit this record ?"  href=" '.WEBROOT.$this->data['controller'].'/edit_record/'.$this->data['thead'].'/'.$key.' ">edit</a></td>';
				}
				$tbody .= '<td><a title="Are you sure you want to delete this record ?"  href=" '.WEBROOT.$this->data['controller'].'/delete_record/'.$this->data['thead'].'/'.$key.' ">delete</a></td>';

				$tbody .= '</tr>';
			}
			$this->data['tbody'] = $tbody;
		}
		$fin = microtime(true)*1000;
		$this->data['performance'] = $fin-$debut;
		$this->data['content'] = $this->Template->load('tables', $this->data,TRUE);
		//LAYOUT
		$this->Template->load('layout',$this->data);
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
	}*/
	function edit_record($url)
	{
		$this->denied('edit a record');
	}
	function delete_record($url)
	{
		if( ($url[TABLE]=='users' || $url[TABLE]=='scripts' || $url[TABLE]=='operators'  || $url[TABLE]=='rwords' || $url[TABLE]=='configs') && $_SESSION['id_user'] !== "1" )
		{
			$this->denied('delete a record');
		}
		else
		{
			if($url[TABLE]=='files')
			{
				$rec = $this->DB->get_record($url[TABLE],$url[INDEX]);
				if(file_exists(DATADIRECTORY.$rec['file']))
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