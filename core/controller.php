<?php 
namespace Core;

if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Controller
* @version:	9.2
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2023-12-27 17:59
* @added function tables_to_system()
* @licence MIT
*/
class Controller
{
	public static $version = '9.2';
	protected $data = array();
	public $path,$Sys,$Msg,$DB,$Template;
	protected $actions = [1=>'id_action',2=>'action',3=>'strtable',4=>'strfield',5=>'totable',6=>'tofield',7=>'left',8=>'right',9=>'string',10=>'operator',11=>'value',12=>'unique'];
	function __construct($file,$ext,$path=NULL)
	{
		$this->path = $path;
		$this->load_model('Sys');
		$this->load_model('Msg');
		$this->load_model('DB');
		$this->load_class('Template');

		$this->Sys->connect(DATADIRECTORY,'system','php');
		$this->Msg->connect(DATADIRECTORY,'messages','php');

		if(isset($_SESSION['username']) && get_class($this) != 'Message' && get_class($this) != 'System' && get_class($this) != 'Curriculum' )
		{
			$this->DB->connect(DATADIRECTORY,$_SESSION['username'],$ext);
		}
		else
		{
			$this->DB->connect(DATADIRECTORY,$file,$ext);
		}
		//Delete duplicates in sys files table
		$table = $this->Sys->id_table('files');
		$column = $this->Sys->id_column($table,'file');
		$this->Sys->del_duplicates($table,$column);
		//Delete duplicates in sys table tables
		$table = $this->Sys->id_table('tables');
		$column = $this->Sys->id_column($table,'strtable');
		$this->Sys->del_duplicates($table,$column);
		
		$configs=$this->Sys->table('configs');
		//model public function id_table($table,$strColumn)
		$table=$this->Sys->id_table('configs');
		//model  public function id_column($table,$strColumn)
		$key=$this->Sys->id_column($table,'key');
		$value=$this->Sys->id_column($table,'value');
		// $rec[2] == key $rec[3]== value
		foreach($configs as $i=>$rec)
		{
			$this->data[$rec[$key]] = $rec[$value];
		}
		//PATH
		$this->data['path'] = $path;
		//LINK
		$this->data['link'] = strtolower(get_class($this));
		//<HEAD>
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		// BANNER
		// title from configs
		if(!$this->data['title'])
		{
			$this->data['title'] = '<a href="'.DEFAULTCONTROLLER.'" target="_blank">'.ucfirst(DEFAULTCONTROLLER).'</a>';
		}
		$this->data['banner']= $this->Template->load('banner', $this->data,TRUE);
		// NAVIGATION
		$this->data['nav'] = $this->Template->load('nav',$this->data,TRUE);
		// MESSAGE
		$this->get_message();
		// LEFT
		$this->data['tables'] = $this->DB->tables();

		$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
		// FOOTER
		$this->data['footer'] = $this->Template->load('footer', $this->data,TRUE);
		// CHECK SYSTEM
		try
		{
			$this->DB->check_system();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
	}
	function index()
	{
		// CONTENU
		$this->data['file'] = $this->DB->filename;
		$this->data['ffilesize'] = $this->DB->ffilesize;
		$this->data['numtables'] = $this->DB->count_tables();
		$this->data['maxlines'] = $this->DB->count_max_lines();
		$this->data['maxcols'] = $this->DB->count_max_columns();
		//$this->data['path'] = NULL;
		$this->data['content'] = $this->Template->load('details',$this->data,TRUE);
		// MAIN PAGE
		$this->Template->load('layout',$this->data);
	}
	function add_table()
	{
		try
		{
			$strTable = @$_POST['table'];
			$strTable = $this->DB->remove_accents($strTable);
			if($this->DB->add_table($strTable))
			{
			//For system tables list 
				$last = $this->Sys->last('tables');
				$idtab = $this->Sys->id_table('tables');
				$post['table'] = $idtab;
				$post['id_table'] = $last+1;
				$post['strtable'] = $strTable;
				$this->Sys->add_line($post,'id_table');
				$this->Msg->set_msg('You have added the table : '.$strTable);
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				exit;
			}
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Add a table';
		$this->data['tip'] = 'The table name must be lowercase, plural, contain only alphabetic characters and have a minimum of 4 caracters.';
		$this->data['placeholder'] = 'New table name';
		$this->data['name'] = 'table';
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_table';
		$this->data['content'] = $this->Template->load('add', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_table($url)
	{
		//LEFT
		//$this->properties('left',$url[TABLE]);
		try
		{
			$id_table = $this->DB->id_table($url[TABLE]);
			$strTableName = $this->DB->table_name($id_table);
			$strTable = @strtolower($_POST['newname']);
			$strTable = $this->DB->remove_accents($strTable);
			if($this->DB->edit_table($id_table,$strTable))
			{
				//For tables list
				$idtab = $this->Sys->id_table('tables');
				$line = $this->Sys->real_id($idtab,'strtable',$strTableName);
				$post = ['table' => $idtab,'line' => $line,'id_table'=>$id_table, 'strtable'=>$strTable];
				$this->Sys->set_line($post);

				$this->Msg->set_msg('You renamed the table: '.$strTableName.' for: '.$strTable);
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				exit;
			}
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Rename the table '.$url[TABLE] ;
		$this->data['tip'] = 'When you add or rename a table, it will be added to the table [tables] from system. Rename table are automatically lowercase.';
		$this->data['placeholder'] = 'New name for table';
		$this->data['name'] = 'newname';
		$this->data['value'] = $strTable;
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_table/'.$url[TABLE];
		$this->data['content'] = $this->Template->load('edit', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function delete_table($url)
	{
		$strTable=$url[TABLE];
		if($this->DB->table_exists($strTable))
		{
			try
			{
				$answer = @$_POST['inlineRadioOptions'];
				if(!isset($answer) && isset($strTable))
				{
					$tab = $this->DB->id_table($strTable);
					$refaction = WEBROOT.strtolower(get_class($this)).'/delete_table/'.$url[TABLE];
					$this->question('Are you sure you want to delete table '.$this->colorize($url[TABLE],'red').' ?',$refaction,$tab);
					exit;
				}
				elseif($answer == 'yes')
				{
					$this->DB->delete_table($strTable);
					$this->Sys->del_lines_where('tables','strtable','==',$strTable,'id_table');
					$this->Msg->set_msg("You have deleted the table: $strTable");
				}
			}
			catch (\Exception $t)
			{
				$this->Msg->set_msg($t->getMessage());
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
	}
	function empty_table($url)
	{
		$strTable=$url[TABLE];
		if($this->DB->table_exists($strTable))
		{
			try
			{
				$answer = @$_POST['inlineRadioOptions'];
				if(!isset($answer) && isset($strTable))
				{
					$tab = $this->DB->id_table($strTable);
					$refaction = WEBROOT.strtolower(get_class($this)).'/empty_table/'.$url[TABLE];
					$this->question('Are you sure you want to empty table '.$this->colorize($url[TABLE],'red').' ?',$refaction,$tab);
					exit;
				}
				elseif($answer == 'yes')
				{
					$this->DB->empty_table($this->DB->id_table($strTable));
					$this->Msg->set_msg("You empty the table: $strTable");
				}
			}
			catch (\Exception $t)
			{
				$this->Msg->set_msg($t->getMessage());
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
	}
	function verif_session($strTable)
	{
		if(file_exists(DATADIRECTORY.$strTable.'.php'))
		{
			$_SESSION['phpfile']=$strTable.'.php';
		}
		else
		{
			unset($_SESSION['phpfile']);
		}
		if(file_exists(DATADIRECTORY.$strTable.'.csv'))
		{
			$_SESSION['csvfile']=$strTable.'.csv';
		}
		else
		{
			unset($_SESSION['csvfile']);
		}
		if(file_exists(DATADIRECTORY.$strTable.'.json'))
		{
			$_SESSION['jsonfile']=$strTable.'.json';
		}
		else
		{
			unset($_SESSION['jsonfile']);
		}
		if(file_exists(DATADIRECTORY.$strTable.'.js'))
		{
			$_SESSION['jsfile']=$strTable.'.js';
		}
		else
		{
			unset($_SESSION['jsfile']);
		}
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

	function add_field($url)
	{
		$strTable=$url[TABLE];

		if(!$this->DB->table_exists($strTable))
		{
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit;
		}

		$this->properties('left',$strTable);

		try
		{
			$strColumn= @$_POST['field'];
			if($this->DB->add_column($strTable,$strColumn))
			{
				$this->Msg->set_msg("You have added the field: $strColumn to the table: $strTable");
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
				exit;
			}
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Add a field to the table: $strTable" ;
		$this->data['tip'] ='';
		$this->data['placeholder'] = 'Name of the field';
		$this->data['name'] = 'field';
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_field/'.$strTable;
		$this->data['content'] = $this->Template->load('add', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_field($url)
	{
		$strTable = $url[TABLE];
		$intColumn = $url[FIELD];

		$strColumn = $this->DB->column_name($strTable,$intColumn);
		
		if(!$this->DB->table_exists($strTable) || !$this->DB->column_exists($strTable, $strColumn))
		{
			header('Location:'.WEBROOT.strtolower(get_class($this)));
			exit;
		}
		// Left side of the website
		$this->properties('left',$strTable);

		try
		{
			$strColumnNew = isset($_POST['field'])?$_POST['field']:'';	
			if($this->DB->edit_column($strTable,$strColumn,$strColumnNew))
			{
				$this->Msg->set_msg("You renamed the field: $strColumn for $strColumnNew .");
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
				exit;
			}
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Change the name of the field $strColumn to the table: $strTable" ;
		$this->data['tip'] ='';
		$this->data['placeholder'] = 'Rename the field';
		$this->data['name'] = 'field';
		$this->data['value'] = $strColumn;
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_field/'.$url[TABLE].'/'.$url[FIELD];
		$this->data['content'] = $this->Template->load('edit', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function delete_field($url)
	{
		if(isset($url[TABLE]) && isset($url[FIELD]))
		{
			$strTable = $url[TABLE];
			$intColumn = $url[FIELD];
			$nbrColumn = $this->DB->count_columns($strTable);
			
			$answer = @$_POST['inlineRadioOptions'];
			if(!isset($answer) && isset($strTable))
			{
				$intTable = $this->DB->id_table($strTable);
				$strColumn = $this->DB->column_name($strTable,$intColumn);
				$refaction = WEBROOT.strtolower(get_class($this)).'/delete_field/'.$url[TABLE].'/'.$url[FIELD];
				$this->question('Are you sure you want to delete this field  '.$this->colorize($strColumn,'red').' ?',$refaction,$intTable);
				exit;
			}
			elseif($answer == 'yes')
			{
				try
				{
					$strColumn = $this->DB->column_name($strTable,$intColumn);
					$this->DB->delete_column($strTable,$intColumn);
					if(--$nbrColumn == 0)
					{
						@$this->Sys->del_lines_where('tables','strtable','==',$strTable,'id_table');
						$this->Msg->set_msg("Since there is no more field, you deleted the table: $strTable");
						header('Location:'.WEBROOT.$url[CONTROLLER]);
						exit;
					}
					else
					{
						$this->Msg->set_msg("You removed the field $strColumn from the table $strTable.");
						if(isset($url[VALUE]))
						{
							header('Location:'.WEBROOT.$url[CONTROLLER].'/show_fields/'.$url[TABLE]);
							exit;
						}
						else
						{
							header('Location:'.WEBROOT.$url[CONTROLLER].'/show_table/'.$url[TABLE]);
							exit;
						}
					}
				}
				catch (\Exception $t)
				{
					$this->Msg->set_msg($t->getMessage());
					header('Location:'.WEBROOT.$url[CONTROLLER].'/show_table/'.$url[TABLE]);
				}
			}
			else 
			{
				header('Location:'.WEBROOT.$url[CONTROLLER].'/show_table/'.$url[TABLE]);
			}
		}
		else
		{
			header('Location:'.WEBROOT.$url[CONTROLLER]);
		}
	}
	function show_fields($url)
	{
		$this->properties('left',$url[TABLE]);
		$id = $this->DB->id_table($url[TABLE]);
		$this->data['idtable'] = $id;
		$this->data['columns'] = $this->DB->columns($id);
		$this->data['content'] = $this->Template->load('fields',$this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function add_record($url)
	{
		$strTable=$url[TABLE];
		// Properties function is setting the id_table, primary and table. See properties function.
		$this->properties('left',$strTable);
		$post = @$_POST;
		try
		{
			if(!$this->DB->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			$last = $this->DB->last($this->DB->table);
			$post[$this->DB->primary] = ++$last;
			if($strTable=='users' && strtolower(get_class($this))=='system' && isset($post['password']))
			{
				$post['password'] = trim(md5($post['password']));
			}
			if($strTable == 'images')
			{
				$post['alt']=$post['image'] = @$this->fupload();
			}
			$this->DB->add_line($post,$this->DB->primary);
			$this->Msg->set_msg("You have added a record to table: $strTable");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Add a record to the table: $strTable";
		$this->data['placeholder'] = 'Add a record';
		$this->data['columns'] = $this->DB->columns($strTable);
		//var_dump($this->data['columns']); exit;
		foreach($this->data['columns'] as $key=>$col)
		{
			if(substr($col, -3, 1)=="_")
			{
				$tblList = stristr($col, '_', true).'s';
				$strListColumns = $this->DB->columns($tblList);
				//dropdown($cols,$strTable,$selectName,$value=null)
				$this->data['tblList'][$key] = $this->dropdown($strListColumns,$tblList,$col);
			}
		}
		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/add_record/'.$strTable;
		$this->data['content'] = $this->Template->load('add-rec', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function edit_record($url)
	{
		$strTable=$url[TABLE];
		try
		{
			if(!$this->DB->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)),false);
				exit;
			}
			//LEFT
			$this->properties('left',$strTable);
			$post = @$_POST;
			$primary = $this->DB->column_name($strTable,1);
			$this->DB->set_line($post);
			$this->Msg->set_msg("You have changed record $post[$primary] at the table: $strTable");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			//exit;
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = "Edit a record in the table: $strTable" ;
		$this->data['placeholder'] = 'Edit a record';
		$this->data['columns'] = $this->DB->columns($strTable);
		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['line'] = $url[INDEX];
		$this->data['record'] = $this->DB->line($this->data['table'],$url[INDEX]);
		foreach($this->data['columns'] as $key=>$col)
		{
			if(substr($col, -3, 1)=="_")
			{
				$tblList = stristr($col, '_', true).'s';
				$strListColumns = $this->DB->columns($tblList);
				//dropdown($cols,$strTable,$selectName,$value=null)
				$value = $this->data['record'][$key];
				$this->data['tblList'][$key] = $this->dropdown($strListColumns,$tblList,$col,$value);
			}
		}
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_record/'.$strTable.'/'.$url[INDEX];
		$this->data['content'] = $this->Template->load('edit-rec', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function delete_record($url)
	{
		if(isset($url[TABLE]) && isset($url[FIELD]))
		{
			$strTable = $url[TABLE];
			$idRec = $url[FIELD];
			$idTable = $this->DB->id_table($strTable);
			$answer = @$_POST['inlineRadioOptions'];
			if(!isset($answer) && isset($strTable))
			{
				$tab = $this->DB->id_table($strTable);
				$refaction = WEBROOT.strtolower(get_class($this)).'/delete_record/'.$url[TABLE].'/'.$url[FIELD];
				$this->question('Are you sure you want to delete this record '.$this->colorize($idRec,'red').' ?',$refaction,$tab);
				exit;
			}
			elseif($answer == 'yes')
			{
				$this->DB->check_rule($strTable,$idRec);		
				if($strTable == 'images')
				{
					$image = $this->DB->get($strTable,$idRec,'image');
					unlink($_SERVER['DOCUMENT_ROOT'].ASSETDIRECTORY.'uploads/'.$image);
				}
				$this->DB->del_line($idTable,$idRec);
				$this->Msg->set_msg("You have deleted record: $idRec in the table: $strTable");
			}
		}
		else
		{
			header('Location:'.WEBROOT.$url[CONTROLLER]);
			exit;
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function delete_duplicates($url)
	{
		$strTable=$url[TABLE];
		$this->properties('left',$strTable);
		$post = @$_POST;
		try
		{
			$table = $this->DB->id_table($strTable);
			$column = @$this->DB->id_column($strTable,$post['strfield']);
			@$this->DB->del_duplicates($table,$column);
			$this->Msg->set_msg("You deleted duplicates  from the table: ".$url[TABLE]);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Delete duplicates  in the table: '.$strTable ;
		$this->data['placeholder'] = 'Delete  duplicates';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'column',' : Use this column to identify duplicates.');
	
		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/delete_duplicates/'.$strTable;
		$this->data['content'] = $this->Template->load('del-duplicates', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function set_cell($url)
	{
		if (is_array($_POST['value']))
		{
			echo implode(', ', $_POST['value']);
		} 
		else
		{
			echo $_POST['value'];
		}
		$this->DB->set_cell($url[TABLE],$url[INDEX],$url[VALUE],$_POST['value']);
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
		catch(\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}

		if($records)
		{
			$strTable = $url[TABLE];
			//LEFT
			$this->properties('left',$strTable);
			//CONTENT
			$this->data['columns'] = $this->DB->columns($strTable);

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
								//NEW
								$idImage = $value;
							}
							catch (\Exception $t)
							{
								$tbody .= '<td>'.$value.'</td>';
							}
						}
						else
						{
							$tbody .= '<td>'.$value.'</td>';
						}
					}
					elseif($col == 'image')
					{
						//$tbody .= '<td><img id="img'.$idImage .'"  class="minresize" src="'.ASSETDIRECTORY.'uploads/'.$value.'" alt="'.$value.'" title="'.$value.'" onclick="$(this).toggleClass(\'maxresize\');" /></td>';
						$tbody .= '<td><img id="img'.$idImage .'"  class="minresize" src="'.ASSETDIRECTORY.'uploads/'.$value.'" alt="'.$value.'" title="'.$value.'" onclick="$(this).toggleClass(\'minresize\');" /></td>';
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

	function get_message()
	{
		$this->data['msg'] = $this->Msg->get_msg(TRUE);
		$this->data['msg'] = $this->Template->load('msg',$this->data,TRUE);
	}
	function load_model($name)
	{
		require_once(ROOT.'models/'.strtolower($name).'.php');
		$this->$name = new $name();
	}
	function load_class($name)
	{
		require_once(ROOT.'classes/'.strtolower($name).'.php');
		$this->$name = new $name();
	}
	function bkp()
	{
		$this->DB->save(TRUE);
		$this->Msg->set_msg('Your back-up is complete.');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	function preprint($res)
	{
		echo '<pre>';
		print_r($res);
		echo '</pre>';
	}
	function dropdown($cols,$strTable,$selectName,$value=null,$label=null)
	{
		$rec = $this->DB->select($cols,$strTable);
		//Désactive la ligne des noms de colonnes
		//unset($array[$i][0]);
		$colkeys = array_keys($cols);
		$html  = '<div class="form-group">';
		$label = (isset($label))?$label:$selectName;
		$html .= '<label for="'.$selectName.'">'.$label.'</label>';
		$html .= '<select class="form-control input-sm"  id="'.$selectName.'" name="'.$selectName.'">';
		$str='';
		$selected='';

			foreach($rec as $row)
			{
				for($i=1;$i<count($colkeys);$i++)
				{
					$str .= ' * '.$row[$colkeys[$i]];
				}
				if($row[$colkeys[0]]===$value)
				{
					$selected = 'selected="selected"';
				}
				$html .= '<option value="'.$row[$colkeys[0]].'"' .$selected. '>'.$str.'</option>';
				$str ='';
				$selected='';
			}
		//}
		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	function dropdown_where($cols,$strTable,$selectName,$value=null,$strColumn=null,$op=null,$val=null)
	{
		//MODEL::select_where(array $columns,$strTable,$strColumn,$op='==',$value)
		$rec = $this->DB->select_where($cols,$strTable,$strColumn,$op,$val);

		$colkeys = array_keys($cols);
		$html  = '<div class="form-group">';
		$html .= '<label for="'.$selectName.'">'.$selectName.'</label>';
		$html .= '<select class="form-control input-sm" name="'.$selectName.'">';
		$str='';
		$selected='';

			foreach($rec as $row)
			{
				for($i=1;$i<count($colkeys);$i++)
				{
					$str .= ' * '.$row[$colkeys[$i]];
				}
				if($row[$colkeys[0]]===$value)
				{
					$selected = 'selected="selected"';
				}
				$html .= '<option value="'.$row[$colkeys[0]].'"' .$selected. '>'.$str.'</option>';
				$str ='';
				$selected='';
			}

		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	function properties($view,$strTable,$properties='properties')
	{
		try
		{
			$this->DB->set_table(array('table'=>$strTable,'primary'=>'id_'.$strTable));
			$this->data['id'] = $this->DB->id_table;
			$this->data['thead'] = $this->DB->table;
			$this->data['nbrligne'] = $this->DB->table_nbrlines;
			$this->data['nbrcolonne'] = $this->DB->table_nbrcolumns;
			$this->data['controller'] = strtolower(get_class($this));
			//No need to set path. __construct doing it.
			//$this->data['path'] = $this->path;
			$this->data['sys'] = $this->Sys;
			$this->data[$view] = $this->Template->load($properties, $this->data,TRUE);
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
	}
	function question($strQuestion=null,$action=null,$table=null,$post=null)
	{
		$this->data['question'] = $strQuestion;
		$this->data['action'] = $action;
		$this->data['table'] = $table;
		$this->data['post'] = $post;
		$this->data['content'] = $this->Template->load('yesno',$this->data,TRUE);
		// MAIN PAGE
		$this->Template->load('layout',$this->data);
	}
	function colorize($string,$color)
	{
		return '<span style="color:'.$color.';"> '.$string.' </span>';
	}
	function cleanup()
	{
		foreach ($this as $key => $value)
		{
            unset($this->$key);
        }
	}
	function jumbo($bool)
	{
		$_SESSION['jumbo']=$bool;
	}
	function mobile()
	{
		$mobile_browser = '0';

		if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}

		if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}

		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda ','xda-');

		if (in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
			$mobile_browser = 0;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mac') > 0) {
				$mobile_browser = 0;
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'ios') > 0) {
				$mobile_browser = 1;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'android') > 0) {
				$mobile_browser = 1;
		}

		if($mobile_browser == 0)
		{
			//its not a mobile browser
			//echo"You are not a mobile browser";
			return 0;
		} else {
			//its a mobile browser
			//echo"You are a mobile browser!";
			return 1;
		}
	}
	function delete_where($url)
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
			//del_lines_where($strTable,$strColumn,$op='==',$multiple,$strKeyCol)
			//$this->DB->del_lines_where('Carrier','EFID','==','-','CarrierNumber');
			@$this->DB->del_lines_where($strTable,$post['strfield'],$post['operator'],$post['value'],$post['unique']);
			$this->Msg->set_msg("You have deleted selection from table: $strTable");
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Delete a selection in the table: '.$strTable ;
		$this->data['placeholder'] = 'Delete a selection';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'column',' : Where column *operator value. Operator could be anything in the list');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2);
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will be use by the operator for comparison');
		$this->data['listuniques'] = $this->Template->cdropdown($this->DB,$strTable,'unique',NULL,NULL,'unique',' : A field name that contains only unique value. Usually begin with id_');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/delete_where/'.$strTable;
		$this->data['content'] = $this->Template->load('del-rec-where', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_column($url)
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
			@$this->DB->copy_column($strTable,$post['strfield'],$post['string']);
			$this->Msg->set_msg('You have duplicate column '.$post['strfield'].' to '.$post['string'].' in the table  '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Duplicate a column of the table: '.$strTable ;
		$this->data['placeholder'] = 'Duplicate a column';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,TRUE,'column');
		$this->data['divstring'] = $this->Template->makediv('string','new column',' : New name for the field');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_column/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function split_column($url)
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

			@$this->DB->split_column($strTable,$post['strfield'],$post['string'],$post['left'],$post['right']);
			$this->Msg->set_msg('You have splitted column '.$post['strfield'].' to '.$post['string'].' in the table  '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Split a column of the table: '.$strTable ;
		$this->data['placeholder'] = 'Split a column';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,'column');
		$this->data['divleft'] = $this->Template->makediv('left','left',' : A number representing the length you want to keep from the left');
		$this->data['divright'] = $this->Template->makediv('right','right',' : A number representing the length you want to keep from the right');
		$this->data['divstring'] = $this->Template->makediv('string','newcolumn',' : Enter a name for the new column');

		if(isset($post['strfield']))
		{
			$this->data['sample'] = $this->DB->get($strTable,1,$post['strfield']);
			$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',$post['strfield']);
		}

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/split_column/'.$strTable;
		$this->data['content'] = $this->Template->load('split-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function split_column_needle($url)
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

			@$this->DB->split_column_needle($strTable,$post['strfield'],$post['string'],$post['value']);
			$this->Msg->set_msg('You have splitted column '.$post['strfield'].' to '.$post['string'].' in the table  '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Split a column with needle of the table: '.$strTable ;
		$this->data['placeholder'] = 'Split a column with a needle';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,'column');
		$this->data['divstring'] = $this->Template->makediv('string','newcolumn',' : Enter a name for the new column');
		$this->data['divvalue'] = $this->Template->makediv('value','needle',' : The research string.');

		if(isset($post['strfield']))
		{
			$this->data['sample'] = $this->DB->get($strTable,1,$post['strfield']);
			$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',$post['strfield']);
		}

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/split_column_needle/'.$strTable;
		$this->data['content'] = $this->Template->load('split-column-needle', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function move_column($url)
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
			@$this->DB->move_column($strTable,$post['strfield'],$post['totable']);
			$this->Msg->set_msg('You have move column '.$post['strfield'].' to table  '.$post['totable']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Move a column from table '.$strTable.' to another table.' ;
		$this->data['placeholder'] = 'Move a column';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'column');
		$this->data['listtotables'] = $this->Template->dropdown($this->Sys,'tables','totable',2);

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/move_column/'.$strTable;
		$this->data['content'] = $this->Template->load('move-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_column_keys($url)
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
			@$this->DB->copy_column_keys($strTable,$post['strfield'],$post['totable'],$post['tofield'],$post['string'],$post['operator'],$post['value']);
			$this->Msg->set_msg('You copied column '.$post['strfield'].' to  '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Copy a column from table '.$strTable.' to another column by matching condition.';
		$this->data['placeholder'] = 'Copy a column to another';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,NULL,' : This column to be copy to another');
		$this->data['listtotables'] = $this->Template->dropdown($this->Sys,'tables','totable',2,NULL,FALSE,NULL,' : The table that will receive the column. It can be the same table which is '.$strTable);
		$this->data['listtofields'] = $this->Template->cdropdown($this->DB,NULL,'tofield',NULL,FALSE,NULL,' : The column that will receive the copy. It should already be created.');
		$this->data['divstring'] = $this->Template->cdropdown($this->DB,$strTable,'string',NULL,FALSE,'where',' : The field that will serve for matching condition');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,FALSE);
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will serve for matching condition');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_column_keys/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-column-keys', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_data_keys($url)
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
			//copy_data_keys($strTable,$strColumn,$strToTable,$strToField,$left,$right,$string,$op='==',$value=null)
			@$this->DB->copy_data_keys($strTable,$post['strfield'],$post['totable'],$post['tofield'],$post['left'],$post['right'],$post['string'],$post['operator'],$post['value']);
			$this->Msg->set_msg('You copied data from '.$post['strfield'].' to  '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Copy data from table '.$strTable.' [left] to another table [right] column by matching keys.';
		$this->data['placeholder'] = 'Copy data column to another table';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,NULL,' : This column to be copy to another');
		$this->data['listtotables'] = $this->Template->dropdown($this->Sys,'tables','totable',2,NULL,FALSE,NULL,' : The table that will receive the column. It can be the same table which is '.$strTable);
		$this->data['listtofields'] = $this->Template->cdropdown($this->DB,NULL,'tofield',NULL,FALSE,NULL,' : The column that will receive the copy. It should already be created.');
		$this->data['divleft'] = $this->Template->makediv('left','left',' : Left keyname field to match');
		$this->data['divright'] = $this->Template->makediv('right','right',' : Right keyname field to match');
		$this->data['divstring'] = $this->Template->cdropdown($this->DB,$strTable,'string',NULL,FALSE,'where',' : The field that will serve for matching condition');
		//dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0)
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,FALSE,NULL,' : You can use the "LIKE" operator to search a string into the field.');
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will serve for matching condition');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_data_keys/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-data-keys', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_text_where($url)
	{
		$strTable=$url[TABLE];
		$this->properties('left',$strTable);
		$post = @$_POST;
		//var_dump($post); exit;
		try
		{
			if(!$this->DB->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			//copy_text_where($strTable,$strColumn,$strLeft,$string,$op='==',$value)
			@$this->DB->copy_text_where($strTable,$post['strfield'],$post['left'],$post['string'],$post['operator'],$post['value']);
			$this->Msg->set_msg('You copied text '.$post['left'].' to  '.$post['strfield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Table '.$strTable.' : copy text to column by matching condition.';
		$this->data['placeholder'] = 'Copy text by mathching condition ';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,'column',' : This column will receive the text');
		$this->data['divleft'] =  $this->Template->makediv('left','text',' : The text that will be copied');
		$this->data['divstring'] = $this->Template->cdropdown($this->DB,$strTable,'string',NULL,FALSE,'where',' : The field that will serve for matching condition');
		$this->data['listoperators'] = $this->Template->dropdown($this->Sys,'operators','operator',2,NULL,FALSE);
		$this->data['divvalue'] = $this->Template->makediv('value','value',' : The value that will serve for matching condition');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/copy_text_where/'.$strTable;
		$this->data['content'] = $this->Template->load('copy-text-where', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function switch_column($url)
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
			/*elseif( ! isset($_POST['strfield']) || ! isset($_POST['tofield']))
			{
				$post['strfield']=$post['tofield']='';
			}*/
			@$this->DB->switch_column($strTable,$post['strfield'],$post['tofield']);
			$this->Msg->set_msg('You switched column '.$post['strfield'].' to  '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Table '.$strTable.' : switch column with another.';
		$this->data['placeholder'] = 'Switch column with another ';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,'column A',' : This column to be move');
		$this->data['listtofields'] = $this->Template->cdropdown($this->DB,$strTable,'tofield',NULL,FALSE,'column B',' : The field that will serve for switching');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/switch_column/'.$strTable;
		$this->data['content'] = $this->Template->load('switch-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function merge_rows($url)
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
			$answer = @$_POST['inlineRadioOptions'];
			if(!isset($answer) && isset($post['table']) && isset($post['strfield']) && isset($post['tofield']) && isset($post['unique']))
			{
				$tab = $this->DB->id_table($strTable);
				$refaction = WEBROOT.strtolower(get_class($this)).'/merge_rows/'.$url[TABLE];
				$this->question('Are you sure you want to merge rows of table '.$url[TABLE].' into '.$this->colorize($post['unique'],'red').' ?',$refaction,$tab,$post);
				exit;
			}
			elseif($answer=='yes')
			{
				//merge_rows($strTable,$strColKey,$strColOrder,$strColResult)
				@$this->DB->merge_rows($strTable,$post['strfield'],$post['tofield'],$post['unique']);
				$this->Msg->set_msg('You merge column '.$post['unique'].' using '.$post['strfield'].' order by '.$post['tofield']);
				header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
				exit;
			}
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Merge rows from table '.$strTable.' to a column in the first row by matching keys.';
		$this->data['placeholder'] = 'Merge rows to a single column';

		$this->data['columns'] = $this->actions;
		//$this->Template->cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL);
		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,FALSE,'multiple',' : Multiple keys matching rows');
		$this->data['listtofields'] = $this->Template->cdropdown($this->DB,$strTable,'tofield',NULL,FALSE,'line',' : The field that will serve for sorting');
		$this->data['listuniques'] = $this->Template->cdropdown($this->DB,$strTable,'unique',NULL,FALSE,'concatenation',' : The column that will receive the concat text. First row of all. Other rows will be deleted.');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/merge_rows/'.$strTable;
		$this->data['content'] = $this->Template->load('merge-rows', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function move_one_to_many($url)
	{
		$strTable=$url[TABLE];
		$this->properties('left',$strTable);
		$post = @$_POST;
		//var_dump($post); exit;
		try
		{
			if(!$this->DB->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			//move_column_keys($strTable,$strColumn,$strToTable,$strTableKey,$strToTableKey)
			//$this->preprint($post); exit;
			@$this->DB->move_one_to_many($strTable,$post['column'],$post['totable'],$post['tofield'],$post['unique']);
			$this->Msg->set_msg('You have move column '.$post['column'].' to table '.$post['totable']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$post['totable']);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Move a column from table '.$strTable.' to any table by matching keys. (one to many)';
		$this->data['placeholder'] = 'Move a column';

		$this->data['columns'] = $this->actions;
		// cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL)
		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'column',NULL,FALSE,NULL,' : This column to be move');
		$this->data['listtotables'] = $this->Template->dropdown($this->Sys,'tables','totable',2,NULL,TRUE,NULL,' : The table that will receive the column.');
		$this->data['listtofields'] = $this->Template->cdropdown($this->DB,'empty','tofield',NULL,FALSE,NULL,' : Match keys of the table that will receive the column');
		$this->data['listuniques'] = $this->Template->cdropdown($this->DB,$strTable,'unique',NULL,FALSE,NULL,' : Unique key of the table that has the column you want to move');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/move_one_to_many/'.$strTable;
		$this->data['content'] = $this->Template->load('move-one-to-many', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
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
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Renumber a column of the table: '.$strTable ;
		$this->data['placeholder'] = 'Renumber a column';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'column',' : Column to be renumbered');
		$this->data['divvalue'] = $this->Template->makediv('value','start',' : Beginning value');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/renumber_column/'.$strTable;
		$this->data['content'] = $this->Template->load('renumber-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function match_column($url)
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
			//matches($strMaster,$strMasterOldColumn,$strSlave,$strSlaveOldColumn,$strMasterNewNumbersColumn)
			@$this->DB->matches($strTable,$post['strfield'],$post['totable'],$post['tofield'],$post['unique']);
			$this->Msg->set_msg('You have reassigned column '.$post['unique'].' from '.$strTable.' to the table '.$post['totable'].' field : '.$post['tofield']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Reassign key values from the master table : '.$strTable ;
		$this->data['placeholder'] = 'Reassign a key column';
		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'Master old key',' : This column contains original keys');
		$this->data['listtotables'] = $this->Template->dropdown($this->Sys,'tables','totable',2,NULL,FALSE,'Slave',' : Slave table that will have multiple key value from master table','Slave');
		$this->data['listtofields'] = $this->Template->cdropdown($this->DB,NULL,'tofield',NULL,NULL,'Slave key',' : Column to match the master old key and then change it for new key.');
		$this->data['listuniques'] = $this->Template->cdropdown($this->DB,$strTable,'unique',NULL,NULL,'Master new key',' : This column contains new keys');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/match_column/'.$strTable;
		$this->data['content'] = $this->Template->load('match-column', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function concat_columns($url)
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
			//$this->DB->concat_columns($strTable,$filter,$strToColumn,$sep=',')
			@$this->DB->concat_columns($strTable,$post['string'],$post['column'],$post['value']);
			$this->Msg->set_msg('You have concated columns '.$post['string'].' to '.$post['column'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Concat two or more columns of the table: '.$strTable ;
		$this->data['placeholder'] = 'Concat columns';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'column',NULL,TRUE,NULL,' : This column to receive the concatening');
		$this->data['divstring'] = $this->Template->makediv('string','filter',' : Separate wanted fields with a comma ex: Address,City,State');
		$this->data['divvalue'] = $this->Template->makediv('value','delimiter',' : Set a result delimiter, if empty it will be a space by default');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/concat_columns/'.$strTable;
		//$this->data['design'] = (object)$this->Template;
		$this->data['content'] = $this->Template->load('concat-columns', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function date_corrector($url)
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
			//$this->DB->concat_columns($strTable,$filter,$strToColumn,$sep=',')
			@$this->DB->date_corrector($strTable,$post['strfield'],$post['operator']);
			$this->Msg->set_msg('You fixed date column '.$post['strfield'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Fix dates in the table: '.$strTable ;
		$this->data['placeholder'] = 'Fix dates';

		$this->data['columns'] = $this->actions;
		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,TRUE,'column',' : This date column to be corrected');
		//dropdown_where($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0,$op='==')
		$this->data['listoperators'] = $this->Template->dropdown_where($this->Sys,'operators','operator',2,10,TRUE,NULL,' : Identify the current format of the date you want to change',NULL,'>=');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/date_corrector/'.$strTable;
		$this->data['content'] = $this->Template->load('date-corrector', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function time_corrector($url)
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
			@$this->DB->time_corrector($strTable,$post['strfield'],$post['operator']);
			$this->Msg->set_msg('You fixed time column '.$post['strfield'].' in the table '.$strTable);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Fix time field in the table: '.$strTable ;
		$this->data['placeholder'] = 'Fix time field';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,TRUE,'column',' : This time column to be corrected');
		$this->data['listoperators'] = $this->Template->dropdown_where($this->Sys,'operators','operator',2,28,TRUE,NULL,' : Identify the current format of the time field you want to change',NULL,'>');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/time_corrector/'.$strTable;
		$this->data['content'] = $this->Template->load('time-corrector', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	function copy_table($url)
	{
		$copy = $this->DB->copy_table($url[TABLE]);
		$lastdm = $this->Sys->last('tables');
		$idtabdm = $this->Sys->id_table('tables');
		$post['table'] = $idtabdm;
		$post['id_table'] = ++$lastdm;
		$post['strtable'] = $copy;
		$this->Sys->add_line($post,'id_table');

		$this->Msg->set_msg('You have duplicated table : '.$url[TABLE]);
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.'copy'.strtolower($url[TABLE]));
	}
	function get_fields()
	{
		$cols = $this->DB->columns($_POST['strtable']);
		foreach($cols as $id=>$col)
		{
			$fields_arr[] = array("id" => $id, "col" => $col);
		}
		echo json_encode($fields_arr);
	}
	/*function get($url)
	{
		$cols = $this->DB->columns($url[TABLE]);
		$rec = $this->DB->where_unique($url[TABLE],$url[FIELD],$url[VALUE]);
		$this->DB->unescape($rec);
		$record = $this->DB->combine($cols,$rec);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($record,JSON_UNESCAPED_UNICODE);
		return json_encode($record);
	}*/
	function testcurl($url)
	{ 
		//$url = "https://base.webiciel.ca/main/get/".$url[TABLE].'/'.$url[FIELD].'/'.$url[VALUE];
		$url = "https://base.webiciel.ca/main/get/pressions/id_pression/2";
		//$url = 'https://core.ndax.io/v1/ticker';

        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

		var_dump($output); 
		
        // close curl resource to free up system resources
        curl_close($ch);      
	}
	function get($url)
	{
		$cols = $this->DB->columns($url[TABLE]);
		$rec = $this->DB->where_unique($url[TABLE],$url[FIELD],$url[VALUE]);
		$this->DB->unescape($rec);
		$record = $this->DB->combine($cols,$rec);
		//return $record;
		//header('Content-Type: text/html; charset=UTF-8');
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($record,JSON_UNESCAPED_UNICODE);
	}
	function find_replace($url)
	{
		$strTable=$url[TABLE];
		//LEFT
		$this->properties('left',$strTable);

		$post = @$_POST;
		try
		{
			if(!$this->DB->table_exists($strTable))
			{
				header('location:'.WEBROOT.strtolower(get_class($this)));
				exit;
			}
			//find_replace($strTable,$strColumn,$find,$replace)
			@$this->DB->find_replace($strTable,$post['strfield'],$post['string'],$post['value']);
			$this->Msg->set_msg('You have replaced '.$post['string'].' to  '.$post['value']);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$strTable);
			exit();
		}
		catch (\Exception $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		$this->get_message();
		$this->data['legend'] = 'Find and replace a text in a column of the table '.$strTable;
		$this->data['placeholder'] = 'Search a column';

		$this->data['columns'] = $this->actions;

		$this->data['liststrfields'] = $this->Template->cdropdown($this->DB,$strTable,'strfield',NULL,NULL,'column',' : Search this column','column');
		$this->data['divstring'] = $this->Template->makediv('string','filter',' : Text to search');
		$this->data['divvalue'] = $this->Template->makediv('value','text',' : Replace by this text');

		$this->data['table'] = $this->DB->id_table($strTable);
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/find_replace/'.$strTable;
		$this->data['design'] = (object)$this->Template;
		$this->data['content'] = $this->Template->load('find-replace', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	public function fupload()
	{
		//$target_dir ='C:/xampp/htdocs/BASIC/assets/uploads/';
		$target_dir =$_SERVER['DOCUMENT_ROOT'].ASSETDIRECTORY.'uploads/';
		$target_file = $target_dir . basename($_FILES["image"]["name"]);
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"]))
		{
			$check = getimagesize($_FILES["image"]["tmp_name"]);
			if($check !== false)
			{
				$this->Msg->set_msg( "File is an image - " . $check["mime"] . ".");
				$uploadOk = 1;
			} 
			else
			{
				$msg = 'File is not an image.';
				$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
				throw new \Exception($msg);
				//$this->Msg->set_msg("File is not an image.");
				$uploadOk = 0;
			}
		}
		// Check if file already exists
		if (file_exists($target_file))
		{
			$msg = 'Sorry, file already exists.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
			//exit;
			//$this->Msg->set_msg( "Sorry, file already exists.");
			$uploadOk = 0;
		}
		// Check file size
		if ($_FILES["image"]["size"] > 500000) 
		{
			$msg = 'Sorry, your file is too large.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
			//$this->Msg->set_msg( "Sorry, your file is too large.");		 
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" )
		{
			$msg = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
			//$this->Msg->set_msg( "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");	
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) 
		{
			$msg = 'Sorry, your file was not uploaded.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
			//$this->Msg->set_msg( "Sorry, your file was not uploaded.");	
		} 
		else 
		{
			if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) 
			{
				$this->Msg->set_msg("The file ". htmlspecialchars( basename( $_FILES["image"]["name"])). " has been uploaded.");
				return $_FILES["image"]["name"];
			} 
			else
			{
				$msg = 'Sorry, there was an error uploading your file.';
				$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
				throw new \Exception($msg);
				//$this->Msg->set_msg("Sorry, there was an error uploading your file.");
			}
		}
	}
	
	function ini()
	{
			$answer = @$_POST['inlineRadioOptions'];
			if(!isset($answer))
			{
				$refaction = WEBROOT.strtolower(get_class($this)).'/ini';
				$this->question('Are you sure you want to initialize the database ? '.$this->colorize('everything will be erase except table rules ! ','red'),$refaction);
				exit;
			}
			elseif($answer == 'yes')
			{
				$this->DB->initialize();
				//For tables list
				$idtab = $this->Sys->id_table('tables');
				$this->Sys->empty_table($idtab);
				$this->Msg->set_msg('You have initialized '.$this->data['title']);
			}
			header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
	function demo()
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!isset($answer))
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/demo';
			$this->question('Are you sure you want to load the demo database ? '.$this->colorize('If it was a mistake try to load the last back-up ! ','red'),$refaction);
			exit;
		}
		elseif($answer == 'yes')
		{
			$this->DB->demo();
			//For system tables list
			$last = $this->Sys->last('tables');
			$idtab = $this->Sys->id_table('tables');
			$post['table'] = $idtab;
			
			$post['id_table'] = ++$last;
			$post['strtable'] = 'users';
			$this->Sys->add_line($post,'id_table');
			
			$post['id_table'] = ++$last;
			$post['strtable'] = 'notes';
			$this->Sys->add_line($post,'id_table');
			
			$this->Msg->set_msg('You have loaded demo data ');
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}

	function load_last_bkp()
	{
		$dir = DATADIRECTORY;
		$flag = false;
		// Ouvre un dossier et liste tous les fichiers
		if (is_dir($dir)) 
		{
			if ($dh = opendir($dir)) 
			{
				while (($file = readdir($dh)) !== false)
				{
					$files[]=$file;
				}
				closedir($dh);
			}
		}
		rsort($files, SORT_NATURAL | SORT_FLAG_CASE);
		
		foreach ($files as $key => $val)
		{
			//DEFAULTDATABASE
			$pos = strripos($val, $_SESSION['username']);
			//$pos = strripos($val, DEFAULTDATABASE);

			if ($pos !== false)
			{
				$flag = true;
				//$str = "Found! file [$val] : type [". filetype($dir . $val) ."]";
				//$this->Msg->set_msg($str);
				$sfile = explode('.',$val);
				if( strlen($sfile[1]) > 3 && $sfile[1] != 'html')
				{
					rename( DATADIRECTORY.$val,DATADIRECTORY.$sfile[0].'.php');
					$this->DB->connect(DATADIRECTORY,$_SESSION['username'],'php');
					//$this->DB->connect(DATADIRECTORY,DEFAULTDATABASE,'php');
					$this->Msg->set_msg('You have loaded your last back-up! '.$val);
					break;
				}
				else
				{
					$this->Msg->set_msg('Sorry, there is no more back-up! ');
				}
			}
		}
		if ($flag === false)
		{
			$this->Msg->set_msg('Sorry, we did not find '.$_SESSION['username'].'.php');
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
	function load_php($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/load_php/'.$url[TABLE];
			$this->question('Are you sure you want to replace current data of '.$url[TABLE].' with '.$url[TABLE].'.php ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			if(file_exists(DATADIRECTORY.$url[TABLE].'.php'))
			{
				try
				{
					$this->DB->load_php($url[TABLE]);
				}
				catch(\Exception $e)
				{
					$this->Msg->set_msg($e->getMessage());
					header('Location:'.WEBROOT.strtolower(get_class($this)));
				}
			}
			else
			{
				$this->Msg->set_msg('The file '.$url[TABLE].'.php does not exists!');
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function load_json($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/load_json/'.$url[TABLE];
			$this->question('Are you sure you want to replace current data of '.$url[TABLE].' with '.$url[TABLE].'.json ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			if(file_exists(DATADIRECTORY.$url[TABLE].'.json'))
			{
				$this->DB->load_json($url[TABLE]);
				$this->Msg->set_msg('You have loaded '.$url[TABLE].'.json');
			}
			else
			{
				$this->Msg->set_msg('The file '.$url[TABLE].'.json does not exists!');
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function save_as_php($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/save_as_php/'.$url[TABLE];
			$this->question('Do you want to append the current data of '.$url[TABLE].' to '.$url[TABLE].'.php ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			$this->DB->save_php($url[TABLE],TRUE);
		}
		elseif ($answer == 'no')
		{
			$this->DB->save_php($url[TABLE]);
		}

		// Ajoute un fichier a la table files de la base system.
		$this->add_file_to_sys($url[TABLE],'php');
		$this->Msg->set_msg('The table : '.$url[TABLE].' has been saved to '.$url[TABLE].'.php');
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function add_file_to_sys($strTable,$extension)
	{
		// Ajoute un fichier a la table files de la base system.
		$post['table'] = $this->Sys->id_table('files');
		$post['file'] = $strTable.'.'.$extension;
		$this->Sys->add_line($post,'id_file');
	}
	function save_as_csv($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/save_as_csv/'.$url[TABLE];
			$this->question('Do you want to append the current data of '.$url[TABLE].' to '.$url[TABLE].'.csv ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			$this->DB->save_csv($url[TABLE],TRUE);
		}
		elseif ($answer == 'no')
		{
			$this->DB->save_csv($url[TABLE]);
		}

		// Ajoute un fichier a la table files de la base system.
		$this->add_file_to_sys($url[TABLE],'csv');

		$this->Msg->set_msg('The table : '.$url[TABLE].' has been saved to '.$url[TABLE].'.csv');
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function load_csv($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/load_csv/'.$url[TABLE];
			$this->question('Are you sure you want to replace current data of '.$url[TABLE].' with '.$url[TABLE].'.csv ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			if(file_exists(DATADIRECTORY.$url[TABLE].'.csv'))
			{
				$this->DB->load_csv($url[TABLE]);
				$this->Msg->set_msg('You have loaded '.$url[TABLE].'.csv');
			}
			else
			{
				$this->Msg->set_msg('The file '.$url[TABLE].'.csv does not exists!');
			}
		}
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function save_as_json($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/save_as_json/'.$url[TABLE];
			$this->question('Do you want to append the current data of '.$url[TABLE].' to '.$url[TABLE].'.json ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			$this->DB->save_json($url[TABLE],TRUE);
		}
		elseif ($answer == 'no')
		{
			$this->DB->save_json($url[TABLE]);
		}

		// Ajoute un fichier a la table files de la base system.
		$this->add_file_to_sys($url[TABLE],'json');
		$this->Msg->set_msg('The table : '.$url[TABLE].' has been saved to '.$url[TABLE].'.json');
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function save_as_js($url)
	{
		$answer = @$_POST['inlineRadioOptions'];
		if(!$answer)
		{
			$refaction = WEBROOT.strtolower(get_class($this)).'/save_as_js/'.$url[TABLE];
			$this->question('Do you want to append the current data of '.$url[TABLE].' to '.$url[TABLE].'.js ?',$refaction);
			exit;
		}
		elseif ($answer == 'yes')
		{
			$this->DB->save_js($url[TABLE],TRUE);
		}
		elseif ($answer == 'no')
		{
			$this->DB->save_js($url[TABLE]);
		}

		// Ajoute un fichier a la table files de la base system.
		$this->add_file_to_sys($url[TABLE],'js');
		$this->Msg->set_msg('The table : '.$url[TABLE].' has been saved to '.$url[TABLE].'.js');
		header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE]);
	}
	function __destruct()
	{
		$this->cleanup();
	}

	function get_php_file($url)
	{
		//var_dump($url); exit;
		header("Content-Type: text/plain");
		echo file_get_contents(DATADIRECTORY.$url[TABLE].'.php');
	}
	function list_files()
	{
		$dir = DATADIRECTORY;
		// Ouvre un dossier bien connu, et liste tous les fichiers
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
					//echo "fichier : $file : type : " . filetype($dir . $file) . "\n";
					$files[]=$file;
				}
				closedir($dh);
			}
		}
		rsort($files, SORT_NATURAL | SORT_FLAG_CASE);
		foreach ($files as $key => $val)
		{
		 echo $val.'<br>';
		}
	}
	function reflection($obj)
	{
		$oReflectionClass = new \ReflectionClass(strtolower(get_class($obj)));
		$this->data['methods'] = $oReflectionClass->getMethods();
		return $this->Template->load('methods',$this->data,TRUE);
		//$this->Template->load('layout',$this->data);
	}
	
	function tables_to_system()
	{
		$tables = $this->DB->tables();
		
		foreach($tables as $strTable)
		{
			//echo $strTable;
			$last = $this->Sys->last('tables');
			$idtab = $this->Sys->id_table('tables');
			$post['table'] = $idtab;
			$post['id_table'] = $last+1;
			$post['strtable'] = $strTable;
			$this->Sys->add_line($post,'id_table');
			$this->Msg->set_msg('You have transfered the table : '.$strTable.' to the system.');
			sleep(1);
		}
		header('Location:'.WEBROOT);
	}
}
?>