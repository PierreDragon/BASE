<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: System
* @version: 7.5 
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2024-06-2 18:08
* @added field level to users table
* @fixed demo ini
* @link control with user and script level
* @licence MIT
*/
class System extends Core\Controller
{
	public static $version = '7.5';
	
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
	/*function add_field($url)
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
	}*/
	/*function add_record($url)
	{
		$this->denied('add a record');
	}
	function edit_record($url)
	{
		$this->denied('edit a record');
	}*/
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
			if($strTable == 'musiques')
			{
				$post['alt']=$post['musique'] = @$this->fupload_music();
			}
			$line = $this->DB->add_line($post,$this->DB->primary);
			$this->Msg->set_msg("You have added a record to table: $strTable");
			$lines = $this->DB->count_lines($strTable);
			$pag = $lines/$this->data['showlimit'];
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE].'?page='.ceil($pag).'#tr'.$line);
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
			/*$filter = $this->Sys->select_where(array(FIELD=>'lstfields'),'tables','strtable','==',$tblList);
				$string = ($filter[array_key_first($filter)][FIELD]); 
				if(!empty($string))
				{
					$explodes = explode(',',$string);
					foreach($explodes as $f=>$field)
					{
						$i = $this->DB->id_column($tblList,$field);
						$choices[$i] = $field;
					}
					$choices[PRIMARY] = 'id_'.stristr($col, '_', true);
					$filterColumns = $this->DB->filter_columns($strListColumns,$choices);
				}
				else
				{
					$filterColumns = $strListColumns;
				}	
				$this->data['tblList'][$key] = $this->dropdown($filterColumns,$tblList,$col);*/
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
			$this->DB->set_line($post);
			$this->Msg->set_msg("You have changed record $post[$primary] at the table: $strTable");
			$n = $post['line']/$this->data['showlimit'];
			$pag = ceil($n);
			header('Location:'.WEBROOT.strtolower(get_class($this)).'/show_table/'.$url[TABLE].'?page='.(int)$pag.'#tr'.$post['line']);
			exit;
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
				/*$filter = $this->Sys->select_where(array(FIELD=>'lstfields'),'tables','strtable','==',$tblList);
				$string = ($filter[array_key_first($filter)][FIELD]); 
				if(!empty($string))
				{
					$explodes = explode(',',$string);
					foreach($explodes as $f=>$field)
					{
						$i = $this->DB->id_column($tblList,$field);
						$choices[$i] = $field;
					}
					$choices[PRIMARY] = 'id_'.stristr($col, '_', true);
					$filterColumns = $this->DB->filter_columns($strListColumns,$choices);
				}
				else
				{
					$filterColumns = $strListColumns;
				}	
				$value = $this->data['record'][$key];
				$this->data['tblList'][$key] = $this->dropdown($filterColumns,$tblList,$col,$value);*/
				$value = $this->data['record'][$key];
				$this->data['tblList'][$key] = $this->dropdown($strListColumns ,$tblList,$col,$value);
			}
		}
		$this->data['action'] = WEBROOT.strtolower(get_class($this)).'/edit_record/'.$strTable.'/'.$url[INDEX];
		$this->data['content'] = $this->Template->load('edit-rec', $this->data,TRUE);
		$this->Template->load('layout',$this->data);
	}
	
	function delete_record($url)
	{
		if(($url[TABLE]=='configs' || $url[TABLE]=='users' || $url[TABLE]=='tables' || $url[TABLE]=='operators' || $url[TABLE]=='scripts' || $url[TABLE]=='files' || $url[TABLE]=='maths' ) && $_SESSION['level'] !== "1" )
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
	
	function dropdown($cols,$strTable,$selectName,$value=null,$label=null,$disabled=null)
	{
		$rec = $this->DB->select($cols,$strTable);
		//Désactive la ligne des noms de colonnes
		//unset($array[$i][0]);
		$colkeys = array_keys($cols);
		$html  = '<div class="form-group">';
		$label = (isset($label))?$label:$selectName;
		$html .= '<label for="'.$selectName.'">'.$label.'</label>';
		$html .= '<select class="form-control input-sm" id="'.$selectName.'" name="'.$selectName.'" '.$disabled.'>';
		$str='';
		$selected='';
		///// 10.5
		/*$sys = $this->Sys->where_unique('tables','strtable',$strTable);
		$idlistcol = $this->Sys->id_column('tables','idlist');
		$firstField = ($sys[$idlistcol]=='on')?0:1;*/
		/////////////////
		foreach($rec as $row)
		{
			for($i=0;$i<count($colkeys);$i++)
			{
				$str .= $row[$colkeys[$i]].'  *  ';
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
		///// 10.5
		/*$sys = $this->Sys->where_unique('tables','strtable',$strTable);
		$idlistcol = $this->Sys->id_column('tables','idlist');
		$firstField = ($sys[$idlistcol]=='on')?0:1;*/
		/////////////////
		foreach($rec as $row)
		{
			for($i=0;$i<count($colkeys);$i++)
			{
				$str .= $row[$colkeys[$i]].'  *  ';
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