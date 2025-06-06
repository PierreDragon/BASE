<?php  
namespace Core;
if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Model
* @version: 9.5
* @author: info@webiciel.ca
* @php: 8
* @review: 2024-06-07 11:30
 * @changes Added move_column_after() and move_column_before() with full base-1 index support
*/
class Model
{
	public static $version = '9.5';
	public $data = array();
	public $datapath = null;
	public $filename = null;
	public $ffilesize = 0;
	public $n_tables = 0;
	public $max_lines = 0;
	public $max_columns = 0;
	public $id_table;
	public $table;
	public $primary;
	public $table_nbrlines;
	public $table_nbrcolumns;
	public function connect($path,$file,$ext='php')
	{
		$this->datapath = $path;
		$file = $file.'.'.$ext;		
		if(file_exists($this->datapath.$file))
		{
			include($this->datapath.$file);
			if(isset($data))
			{
				$this->set_data($data);
				$this->count_tables();
				$this->count_max_lines();
				$this->count_max_columns();
			}
			$this->filename = $file;
			$this->ffilesize = filesize($this->datapath.$file);
		}
		else
		{
			$this->filename = $file;
			$this->save();
		}
	}
	public static function version(): string
    {
		return self::$version;
	}	
	public function data()
	{
		return $this->data;
	}
	public function set_data($array)
	{
		$this->data = $array;
	}
	public function count_tables(): int
    {
		if(!empty($this->data[0][0]))
		{
			$this->n_tables = count($this->data[0][0]);
		}
		return $this->n_tables;
	}
	public function count_columns($table): int
    {
		$n_columns = 0;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(isset($this->data[$table][0]))
		{
			$n_columns = count($this->data[$table][0]);
		}
		return $n_columns;
	}
	public function count_max_columns(): int
	{
		$i = 1;
		while($i <= $this->n_tables)
		{
			$temp = $this->count_columns($i);
			if($temp > $this->max_columns)
			{
				$this->max_columns = $temp;
			}
			$i++;
		}
		return $this->max_columns;
	}
	public function count_lines($table): int
    {
		$lines = 0;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(isset($this->data[$table]))
		{
			$lines = count($this->data[$table])-1;
		}
		return $lines;
	}
	public function count_max_lines(): int
	{
		$i = 1;
		while($i <= $this->n_tables)
		{
			$temp = $this->count_lines($i);
			if($temp >= $this->max_lines)
			{
				$this->max_lines = $temp;
			}
			$i++;
		}
		return $this->max_lines;
	}
	//*************************************************//
	//******************** TABLES ********************//
	//*************************************************//
	public function add_table($strTable)
	{
		$strTable = $this->remove_accents($strTable);
		if($this->table_exists($strTable))
		{
		 	$msg = 'The table ['.$strTable.'] already exists.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		elseif(empty($strTable) || !(ctype_alpha($strTable)) || !(ctype_lower($strTable)) ||  strlen($strTable) < 4 || !($this->right($strTable,1)=='s'))
		{
			$msg = 'The table name must be lowercase, plural, contain only alphabetic characters and have a minimum of 4 caracters.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$new=1;
		if(!empty($this->data))
		{
			foreach($this->data as $table)
			{
				if(isset($this->data[$new]))
				{
					$new++;
				}
				else
				{
					break;
				}
			}
		}
		$this->data[0][0][$new] = $strTable;
		$substr = substr($this->data[0][0][$new], 0, -1);
		// Automatically add a primary column when adding a table
		$this->data[$new][0][1] = 'id_'.$substr;
		$this->n_tables = $new;
		return $this->save();
	}
	public function copy_table($strTable)
	{		
		$table = $this->id_table($strTable);
		$new=1;
		if(!empty($this->data))
		{
			foreach($this->data as $i=>$tab)
			{
				if(isset($this->data[$new]))
				{
					$new++;
				}
				else
				{
					break;
				}
			}
		}
		$this->data[0][0][$new] = strtolower('copy'.$strTable);
		$this->data[$new] = $this->data[$table];
		$substr = substr($this->data[0][0][$new], 0, -1);
		// Automatically add a primary column when adding a table
		$this->data[$new][0][1] = 'id_'.$substr;
		$this->n_tables = $new;
		$this->save();
		return $this->data[0][0][$new];
	}
	public function add_primary_key($strTable)
	{
		$table = $this->id_table($strTable);
		// Force table name to lowercase
		$strTable = strtolower($strTable);
		if($this->right($strTable,1)=='s')
		{
			$strKey = substr($strTable, 0, -1);
		}
		else
		{
			$strKey = $strTable;
			$strTable = $strTable.'s';
		}
		//  Add a primary column to the table
		$this->data[0][0][$table] = $strTable;
		array_unshift($this->data[$table][0], 'id_'.$strKey);
		$this->save();
	}
	public function primary_key($strTable,$line)
	{
		$table = $this->id_table($strTable);
		return $this->data[$table][$line][PRIMARY];
	}
	public function edit_table($table,$strTable)
	{
		if($this->valid_rule($table,1))
		{
			$msg = 'A foreigh key constraint in the rules table does not allow the edition of this table.';
			throw new \Exception($msg);
		}
		elseif(empty($strTable) || !(ctype_alpha($strTable)) || !(ctype_lower($strTable)) ||  strlen($strTable) < 4 || !($this->right($strTable,1)=='s'))
		{
			$msg = 'The table name must be lowercase, plural, contain only alphabetic characters and have a minimum of 4 caracters.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//$this->data[0][0][$table] = strtolower($strTable);\
		$this->data[0][0][$table] = $strTable;
		$this->save();
		return true;
	}
	public function delete_table($table)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if($this->valid_rule($table,1))
		{
			$msg = 'A foreigh key constraint in the rules table does not allow the deletion of this table.';
			throw new \Exception($msg);
		}
		unset($this->data[$table]);
		unset($this->data[0][0][$table]);
		ksort($this->data);
		$this->save();
	}	
	function empty_table($table)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		$save_columns = $this->data[$table][0];
		unset($this->data[$table]);
		$this->data[$table][0]=$save_columns;
		ksort($this->data);
		$this->save();
	}
	//***************************************************//
	//******************* COLUMNS ******************//
	//***************************************************//
	public function add_column($table,$strColumn)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if( empty($table) || empty($strColumn))
		{
			$msg = 'The fieldname must contain only alphabetic characters. Add <em>_id</em> suffix if you want to referring to a master table key. See table rules.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		elseif($this->verif_alpha_underscore($strColumn) && !$this->valid_foreign_key($strColumn))
		{
			$msg = 'If you try to create a foreigh key it must be terminated by "_id" ';
			$msg .= 'and must referencing an existing master in the rules table.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		elseif($this->column_exists($table,$strColumn))
		{
			$msg = 'Column ['.$strColumn.'] already exists !';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$n_columns = $this->count_columns($table);
		$this->data[$table][0][$n_columns+1] = $strColumn;
		$i = 1;
		$count = count($this->data[$table]);
		while($i < $count)   
		{
			$this->data[$table][$i++][$n_columns+1]='';	
		}
		$this->save();
		return true;
	}
	public function edit_column($table,$column,$strColumn)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(!is_numeric($column))
		{
			$column = $this->id_column($table,$column);
		}
		if( empty($table) || empty($strColumn))
		{
			$msg = 'The fieldname must contain only alphabetic characters. Add <em>_id</em> suffix if you want to referring to a master table key. See table rules.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		elseif($this->verif_alpha_underscore($strColumn) && !$this->valid_foreign_key($strColumn))
		{
			if($this->right($strColumn,3) == '_id')
			{
				$msg = 'If you try to create a foreigh key it must be terminated by "_id" ';
				$msg .= 'and must referencing an existing master in the rules table.';
				$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
				throw new \Exception($msg);
			}
		}
		$this->set_cell($table,0,$column,$strColumn);
		return true;
	} 
	public function delete_column($table,$column)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(!is_numeric($column))
		{
			$column = $this->id_column($table,$column);
		}
		if($this->valid_rule($table,$column))
		{
			$msg = 'A foreigh key constraint in the rules table does not allow the deletion of this key.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$nbrColumn = $this->count_columns($table);
		$nbrLigne = $this->count_lines($table);
		for( $line = 0; $line <= $nbrLigne; $line++ )
		{
			for ( $c = $column; $c <= $nbrColumn; $c++ )
			{
				if($c == $nbrColumn)
				{
					unset($this->data[$table][$line][$c]);
				}
				else
				{
					$this->data[$table][$line][$c] = $this->data[$table][$line][$c+1];
				}
			}
			unset($this->data[$table][$line][$nbrColumn]);
		}
		if(empty($this->data[$table][0]))
		{
			$this->delete_table($table);
		}
		$this->save();
	}
	public function switch_column($strTable,$strCol1,$strCol2)
	{
		if(empty($strTable) || empty($strCol1) || empty($strCol2))
		{
			$msg = 'Switch a column to another'; 
			if(!$this->table_exists($strTable))
			{
				$msg = 'Table '.$strTable.' has not been created yet or fields missing.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$nbrLigne = $this->count_lines($table);
		$col1 = $this->id_column($table,$strCol1);
		$col2 = $this->id_column($table,$strCol2);
		for( $line = 0; $line <= $nbrLigne; $line++ )
		{
			$temp = $this->data[$table][$line][$col1];
			$this->data[$table][$line][$col1] = $this->data[$table][$line][$col2];
			$this->data[$table][$line][$col2] = $temp;
		}
		$this->save();
	}  

	public function move_column_after($strTable, $columnToMove, $afterColumn)
	{
		if (empty($strTable) || empty($columnToMove) || empty($afterColumn)) {
			$msg = 'Switch a column after another'; 
			if (!$this->table_exists($strTable)) {
				$msg = 'Table '.$strTable.' has not been created yet or fields missing.'; 
			}
			throw new \Exception(htmlentities($msg, ENT_COMPAT, "UTF-8"));
		}
		$tableIndex = $this->id_table($strTable);

		// Entête (ligne 0)
		$header = $this->data[$tableIndex][0];
		$header0 = array_values($header); // convertit en 0-based temporaire

		$from = array_search($columnToMove, $header0);
		$to   = array_search($afterColumn, $header0);

		if ($from === false || $to === false || $from == $to) return;

		$newPos = $to + 1;
		if ($from < $newPos) $newPos--;

		// Réorganiser l'entête
		$col = array_splice($header0, $from, 1);
		array_splice($header0, $newPos, 0, $col);

		// Remettre en 1-based
		$headerFixed = [];
		foreach ($header0 as $i => $val) {
			$headerFixed[$i + 1] = $val;
		}
		$this->data[$tableIndex][0] = $headerFixed;

		// Réorganiser toutes les lignes de données
		foreach ($this->data[$tableIndex] as $line => $row) {
			if ($line === 0) continue;

			$row0 = array_values($row); // 0-based temporaire
			$val = array_splice($row0, $from, 1);
			array_splice($row0, $newPos, 0, $val);

			// Remettre en 1-based
			$rowFixed = [];
			foreach ($row0 as $i => $v) {
				$rowFixed[$i + 1] = $v;
			}
			$this->data[$tableIndex][$line] = $rowFixed;
		}
		$this->save();
	}

	public function move_column_before($strTable, $columnToMove, $beforeColumn)
	{
		if (empty($strTable) || empty($columnToMove) || empty($beforeColumn)) {
			$msg = 'Switch a column before another'; 
			if (!$this->table_exists($strTable)) {
				$msg = 'Table '.$strTable.' has not been created yet or fields missing.'; 
			}
			throw new \Exception(htmlentities($msg, ENT_COMPAT, "UTF-8"));
		}

		$tableIndex = $this->id_table($strTable);

		// Entête (ligne 0)
		$header = $this->data[$tableIndex][0];
		$header0 = array_values($header); // convertit en 0-based temporaire

		$from = array_search($columnToMove, $header0);
		$to   = array_search($beforeColumn, $header0);

		if ($from === false || $to === false || $from == $to) return;

		$newPos = $to;
		if ($from < $to) $newPos--; // Corriger si on tire vers la droite

		// Réorganiser l'entête
		$col = array_splice($header0, $from, 1);
		array_splice($header0, $newPos, 0, $col);

		// Re-indexer en base 1
		$headerFixed = [];
		foreach ($header0 as $i => $val) {
			$headerFixed[$i + 1] = $val;
		}
		$this->data[$tableIndex][0] = $headerFixed;

		// Réorganiser les lignes
		foreach ($this->data[$tableIndex] as $line => $row) {
			if ($line === 0) continue;

			$row0 = array_values($row); // base 0
			$val = array_splice($row0, $from, 1);
			array_splice($row0, $newPos, 0, $val);

			$rowFixed = [];
			foreach ($row0 as $i => $v) {
				$rowFixed[$i + 1] = $v;
			}
			$this->data[$tableIndex][$line] = $rowFixed;
		}

		$this->save();
	}



	public function column_name($table,$column)
	{
		$return = false;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if( isset($this->data[$table][0][$column]) )
		{
			$return = $this->data[$table][0][$column];
		} 
		return $return;
	}
	public function columns($table)
	{
		$return = false;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if( isset($this->data[$table][0]) )
		{
			$return = $this->data[$table][0];
		}
		return $return;
	}
	public function column_exists($table,$strColumn)
	{
		$return = false;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if($this->id_column($table,$strColumn) != 0)
		{
			$return = true;
		}
		return $return;
	}
	public function filter_columns(array $columns,array $filter)
	{
		return array_intersect($columns,$filter);
	}
	public function id_column($table,$strColumn)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		$id = 0;
		$columns = $this->data[$table][0];
		foreach($columns as $index=>$value)
		{
			if($value == $strColumn)
			{
				$id = (int)$index;
			}
		}
		return $id;
	}
	public function concat_columns($strTable,$filter,$strToColumn,$delim=null)
	{
		$table = $this->id_table($strTable);
		if(empty($strTable) || empty($filter) || empty($strToColumn))
		{
			$msg ='Concat two or more columns';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		elseif(!$this->column_exists($table,$strToColumn))
		{
			$msg ='Field '.$strToColumn.' from '.$this->colorize($strTable,'red').' does not exists!';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$filter = explode(',',$filter);
		foreach($filter as $c=>$field)
		{
			$order[] = $this->id_column($table,$filter[$c]);
		}
		$columns = $this->columns($strTable);
		//$columns = $this->filter_columns($columns,$filter);
		$rows = $this->select($columns,$strTable);
		$tocol = $this->id_column($table,$strToColumn);
		foreach($rows as $i=>$rec)
		{
			if($i == 0) continue;
			$string='';
			foreach($order as $o=>$c)
			{
				$string .= $rec[$c].$delim.' ';
			}
			//$rest = substr("abcdef", 0, -1);  // returns "abcde"
			//-2 a cause de l espace laisser apres le delim
			$offset = ($delim != '')?2:1;
			$this->data[$table][$i][$tocol]	= substr($string, 0, -$offset);
		}
		$this->save();
	}
	//*************************************************//
	//******************* END COLUMNS *************//
	//*************************************************//
	public function id_table($strTable)
	{
		$id = 0;
		$tables = $this->data[0][0];
		if(empty($tables))
		{
			$msg = 'Empty table !';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);				
		}
		foreach($tables as $index=>$value)
		{
			if($value == $strTable)
			{
				$id = (int)$index;
			}
		}
		return $id;
	}
	public function get_table($table,$limit=0)
	{
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(isset($this->data[$table]))
		{
			if($limit==0)
			{
				$records =  $this->data[$table];
			}
			else
			{
				foreach($this->data[$table] as $i=>$rec)
				{
					if( $i > $limit ) exit;
					$records[$i] = $this->data[$table][$i];  
				}
			}
			return $records;
		}
		else
		{
			return false;
		}
	}
	public function table($string,$col=false)
	{
		$return = false;
		if($this->table_exists($string))
		{
			$id = $this->id_table($string);
			$return = $this->get_table($id);
		}
		if(!$col)
		{
			unset($return[0]);
		}
		return $return;
	}
	public function table_exists($string)
	{
		$return = false;
		if(!empty($this->data[0][0]))
		{
			foreach($this->data[0][0] as $key=>$value)
			{
				if($string === $value)
				{
					$return = true;
				}
			}
		}
		return $return;
	}
	public function table_name($table):string
	{
		$return = false;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if( isset($this->data[0][0][$table]) )
		{
			$return = $this->data[0][0][$table];
		}
		return $return;
	}
	public function tables()
	{
		if(!empty($this->data))
		{
			return $this->data[0][0];
		}
	}
	public function set_cell($x,$y,$z,$value=null)
	{
		$this->data[$x][$y][$z] = $value;
		return $this->save();
	}
	public function get_cell($x,$y,$z)
	{
		$return = false;		
		if(isset($this->data[$x][$y][$z]))
		{
			$return = $this->data[$x][$y][$z];
		}		
		return $return;
	}
	public function del_cell($x,$y,$z)
	{
		$return = false;
		if(isset($this->data[$x][$y][$z]))
		{
			unset($this->data[$x][$y][$z]);
			$this->save();
			$return = true;
		}
		return $return;
	}
	public function line($table,$line)
	{
		$return = false;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}	
		if(isset($this->data[$table][$line]))
		{
			 $return = $this->data[$table][$line];
		}		
		return $return;
	}
	public function primary_column($strTable)
	{
		$table = $this->id_table($strTable);
		return $this->data[$table][0][PRIMARY];
	}
	public function set_line($post):int
	{
		$strTable="";
		if(empty($post['table']) || empty($post['line']))
		{
			$msg ='';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		elseif(is_numeric($post['table']))
		{
			$strTable = $this->table_name($post['table']);
		}
		else
		{
			$strTable = $post['table'];
		}
		$mandatory =  $this->primary_column($strTable);
		if(empty($post[$mandatory]))
		{
			$msg ='A mandatory field is not set !';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $post['table'];
		$line = $post['line'];
		unset($post['table'],$post['line']);
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		$nbrCols = $this->count_columns($table);	
		for($i=1;$i<=$nbrCols;$i++)
		{
			$flag=false;
			foreach($post as $strColumn=>$strValue)
			{
				$column = $this->id_column($table,$strColumn);
				if($column == $i)
				{
					$flag=true;
					$strValue = strval(trim($strValue));
					//$strValue = strval($strValue);
					if($strValue || $strValue == 0 || $strValue == "0")
					{
						$this->data[$table][$line][$column] = $strValue;
					}
					else
					{
						$this->data[$table][$line][$column] = '';
					}
				}
			}
			if($flag==false)
			{
				$this->data[$table][$line][$i] = '';
			}
		}
		//ksort($this->data[$table][$line]);
		$this->save();
		return $line;
	}
	public function add_line($post,$mandatory='')
	{
		if(empty($post['table']) || empty($mandatory))
		{
			$msg = 'Add a new line';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$this->repair_table($post['table']);
		$n_lines = $this->count_lines($post['table']);
		$post['line'] = ++$n_lines;
		if(empty($post[$mandatory]))
		{
			$strTable = $this->table_name($post['table']);
			$last = $this->last_number($strTable,$mandatory);
			$post[$mandatory] = $last+1;
		}
		return $this->set_line($post);
	}
	public function del_line($table,$line)
	{		
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(isset($this->data[$table][$line]))
		{
			unset($this->data[$table][$line]);
			$this->repair_table($table);
		}
		else
		{	
			$msg = 'Real line index not found!';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
	}
	public function add_record($post)
	{
		if(empty($post['table']))
		{
			$msg = 'Table name or id is missing';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$this->repair_table($post['table']);
		$n_lines = $this->count_lines($post['table']);
		$post['line'] = ++$n_lines;
		$strTable = $this->table_name($post['table']);
		$mandatory = 'id_'.rtrim($strTable,'s');
		if(empty($post[$mandatory]))
		{
			$last = $this->last_number($strTable,$mandatory);
			$post[$mandatory] = $last+1;
		}
		return $this->set_line($post);
	}
	public function del_lines_where($strTable,$strColumn,$op='==',$multiple='',$strKeyCol='')
	{
		//if(!$this->table_exists($strTable) || empty($strColumn) ||  empty($op) ||  empty($multiple) || empty($strKeyCol))
		if(!$this->table_exists($strTable) || empty($strColumn) ||  empty($op) || empty($strKeyCol))
		{
			$msg = 'To delete a selection from a table. You need to identify the field (field $a) you want to work with and '; 
			$msg.= 'then write the according value of this field (string $b) that you will use to delete unwanted records with '; 
			$msg.= 'a conditional operator and a key column that contains a unique value. ';
			$msg.= 'It could be any field that contains unique value and it is mandatory to reconstruct the table properly. ';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$records =  $this->where_multiple($strTable,$strColumn,$op,$multiple);
		$column = $this->id_column($table,$strKeyCol);
		foreach($records as $i=>$record)
		{
			//Eliminate row of columns
			if($i==0) continue;
			$keys[] = $record[$column];
			// New added 2023-12-27
			$this->check_rule($strTable,$record[$column]);	
		}
		foreach($keys as $col)
		{
			$line = $this->real_id($table,$strKeyCol,$col);
			unset($this->data[$table][$line]);
			//$this->del_line($table,$line);
		}
		$this->repair_table($table);
	}
	public function last($strTable)
	{
		$return = 0;
		if(!$this->table_exists($strTable))
		{
			$msg = 'The table does not exist!';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$tab = $this->id_table($strTable);
		$i = count($this->data[$tab])-1;
		if(is_numeric($this->data[$tab][$i][1]))
		{
			$strColumn = $this->column_name($tab,1);
			$return = $this->last_number($strTable,$strColumn);
		}
		else
		{
			$return = ($i>0)?$this->data[$tab][$i][1]:0;
		}
		return $return;
	}
	public function last_number($strTable,$strColumn):int
	{
		$last = 0;
		if(!$this->table_exists($strTable))
		{
			$msg = 'The table does not exist!';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$tab = $this->id_table($strTable);
		$col = $this->id_column($tab,$strColumn);
		foreach($this->data[$tab] as $rec)
		{
			if(is_numeric($rec[$col]) && $rec[$col] > $last)
			{	
				$last = $rec[$col];
			}
		}
		return (int)$last;
	}
	public function real_id($table,$strColumn,$unique)
	{
		$return = false;
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		$column = $this->id_column($table,$strColumn);
		if($column == 0)
		{
			$msg = 'This realId doesn\'t exists.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		foreach( $this->data[$table] as $index=>$record )
		{	
			if($index == 0) continue;
			if($record[$column] == $unique)
			{
				$return = $index;
				break;
			}
		}
		return $return;
	}
	public function last_real_id($strTable)
	{
		$this->repair_table($this->id_table($strTable));
		return $this->count_lines($this->id_table($strTable));
	}
	public function get($strTable,$line,$strCol)
	{
		$result = false;
		$tab = $this->id_table($strTable);
		if($tab != 0)
		{
			$col = $this->id_column($tab,$strCol);
			if($col != 0)
			{
				if($this->get_cell($tab,$line,$col))
				{
					$result = $this->get_cell($tab,$line,$col);
				}
			}
		}
		return $result;
	}
	public function combine(array $column,array $line)
	{
		if(count($column) == count($line))
		{
			return array_combine($column,$line);
		}	
		else
		{
			$msg = 'Error combining columns and data!';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
	}
	public function where_unique($strTable,$strColumn,$unique)
	{
		$return = null;
		$table = $this->id_table($strTable);
		$column = $this->id_column($table,$strColumn);
		if($column !== 0)
		{
			foreach( $this->data[$table] as $index=>$record )
			{	
				if($index == 0) continue;
				if($record[$column] == $unique)
				{
					$return = $record;
					break;
				}
			}
		}
		else
		{
			throw new \Exception('Table:'.$strTable.' Column '.$strColumn.' not found ! : index 0');
		}
		return $return;
	}
	public function is_unique($strTable,$strColumn,$unique)
	{
		$return = true;
		$counter = 0;
		$table = $this->id_table($strTable);
		$column = $this->id_column($table,$strColumn);
		if($column !== 0)
		{
			foreach( $this->data[$table] as $index=>$record )
			{	
				if($index == 0) continue;
				if($record[$column] == $unique)
				{
					$counter++;
					if($counter > 1)
					{
						$return = false;
						break;
					}
				}
			}
		}
		else
		{
			throw new \Exception('Table:'.$strTable.' Column index 0');
		}
		return $return;
	}
	/* OPERATORS
	$a == $b	Equal	true if $a is equal to $b after type juggling.
	$a === $b	Identical	true if $a is equal to $b, and they are of the same type.
	$a != $b	Not equal	true if $a is not equal to $b after type juggling.
	$a <> $b	Not equal	true if $a is not equal to $b after type juggling.
	$a !== $b	Not identical	true if $a is not equal to $b, or they are not of the same type.
	$a < $b	Less than	true if $a is strictly less than $b.
	$a > $b	Greater than	true if $a is strictly greater than $b.
	$a <= $b	Less than or equal to	true if $a is less than or equal to $b.
	$a >= $b	Greater than or equal to	true if $a is greater than or equal to $b.
	$a <=> $b	Spaceship	An integer less than, equal to, or greater than zero when $a is
	respectively less than, equal to, or greater than $b. Available as of PHP 7.
	*/	
	// $this->where_multiple($strTable,$strColumn,$multiple,$op='==');
	// The values passed in parameters are sensitive to the case.
	public function where_multiple($strTable,$strColumn,$op='==',$multiple='')
	{
		$return = null;
		$table = $this->id_table($strTable);
		$column = $this->id_column($table,$strColumn);
		if($column !== 0)
		{
			foreach( $this->data[$table] as $realID=>$record )
			{
				switch($op)
				{
					case '==':
						if($record[$column] == $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '===':
						if($record[$column] === $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '!=':
						if($record[$column] != $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '<>':
						if($record[$column] <> $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '!==':
						if($record[$column] !== $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '<':
						if($record[$column] < $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '>':
						if($record[$column] > $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '<=':
						if($record[$column] <= $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					case '>=':
						if($record[$column] >= $multiple)
						{
							$return[$realID] = $record;
						}
					break;
					/* $a <=> $b	Spaceship	An integer less than, equal to, or greater than zero when $a is
					respectively less than, equal to, or greater than $b.
					case '<=>':
						if($record[$column] <=> $multiple)
						{
							$return[$realID] = $record;
						}
					break;*/
					case 'BETWEEN':
							if( str_contains($multiple,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$multiple);
							if($record[$column] >= $test[0] && $record[$column] <= $test[1])
							{
								$return[$realID] = $record; 
							}
					break;
					case 'LIST':
							if(str_contains($multiple,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$multiple);
							foreach($test as $tes)
							{
								if($record[$column] == $tes)
								{
									$return[$realID] = $record; 
								}
							}
						break;
					case 'LIKE':
							if(stripos($record[$column],$multiple) !== false)
							{
								$return[$realID] = $record; 
							}
					break;
					default:
						if($record[$column] == $multiple)
						{
							$return[$realID] = $record;
						}				
				}
			}
		}
		/*else
		{
			throw new \Exception($strTable.' Index 0');
		}*/
		return $return;
	}
	public function where($strTable,$strColumn,$op='==',$value='')
	{
		return $this->where_multiple($strTable,$strColumn,$op,$value);
	}
	public function record_exists($strTable,$myRecord)
	{
		$records = $this->table($strTable);
		foreach($records as $record)
		{
			unset($record[1],$myRecord[1]);
			$currentRecord = array_intersect_assoc($record,$myRecord);
			if(($currentRecord <=> $myRecord) == 0)
			{
				return true;
			}
		}
		return false;
	}
	public function value_where_unique($strTable,$strColumn,$unique,$strField)
	{
		$return = null;
		$idTable = $this->id_table($strTable);
		$array = $this->where_unique($strTable,$strColumn,$unique);
		if( ! is_null($array) )
		{
			$id_column = $this->id_column($idTable,$strField);
			$return = $array[$id_column];
		}
		return $return;
	}
	public function save($backup = false)
	{ 
		$puts = '<?php';
		if(isset($this->data[0][0]))
		{
			ksort($this->data[0][0],SORT_NUMERIC);
			ksort($this->data,SORT_NUMERIC);
			foreach($this->data as $table=>$t)
			{
				foreach($t as $line=>$l)
				{
					foreach($l as $column=>$value)
					{
						$puts .= PHP_EOL;
						//$value = htmlentities($value);
						$value = trim($value);
						$this->escape($value);
						$puts .= '$data['.$table.']['.$line.']['.$column.']='."'".$value."'".';';
					}
				}
			}
		}
		$puts .= PHP_EOL;
		$puts .= '?>';
		$dat = date('Y-m-d H:i:s',time());
		$dat = str_replace(' ', '', $dat);
		$dat = str_replace(':', '', $dat);
		$dat = str_replace('-', '', $dat);
		$d=($backup)? $dat:'';

		$result = file_put_contents($this->datapath.$this->filename.$d,$puts,LOCK_EX);

		if($result === false)
		{
			$msg = 'The file is locked.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		else
		{
			$result = true;
		}
		return $result;
	}
	/*public function save($backup = false)
	{ 
		$puts = '<?php' . PHP_EOL;

		if (isset($this->data[0][0])) {
			ksort($this->data[0][0], SORT_NUMERIC);
			ksort($this->data, SORT_NUMERIC);

			foreach ($this->data as $table => $t) {
				foreach ($t as $line => $l) {
					foreach ($l as $column => $value) {
						$value = trim($value);

						// 🔐 Encodage sûr via var_export
						$exportedValue = var_export($value, true);

						$puts .= '$data[' . $table . '][' . $line . '][' . $column . ']=' . $exportedValue . ';' . PHP_EOL;
					}
				}
			}
		}

		$puts .= '?>' . PHP_EOL;

		$dat = date('YmdHis'); // ex: 20250517235901
		$d = ($backup) ? $dat : '';

		$filename = $this->datapath . $this->filename . $d;

		$result = file_put_contents($filename, $puts, LOCK_EX);

		if ($result === false) {
			throw new \Exception('The file is locked.');
		}

		return true;
	}*/

	public function escape(&$mixed)
	{
		if (is_array($mixed))
		{
			foreach($mixed as $key => $value)
			{
				//$mixed[$key] = trim(preg_replace('/\s+/', ' ', $mixed[$key]));
				$mixed[$key] = preg_replace("/'/", "&#039;", $mixed[$key]);
				$mixed[$key] = preg_replace("/</", "&lt;", $mixed[$key]);
				$mixed[$key] = preg_replace("/>/", "&gt;", $mixed[$key]);
				$mixed[$key] = str_replace("\\", "&#092;", $mixed[$key]);
				$mixed[$key] = str_replace("/", "&#047;", $mixed[$key]);
			}
		}
		else
		{
			//$mixed = trim(preg_replace('/\s+/', ' ', $mixed));
			$mixed = preg_replace("/'/", "&#039;", $mixed);
			$mixed = preg_replace("/</", "&lt;", $mixed);
			$mixed = preg_replace("/>/", "&gt;", $mixed);
			$mixed = str_replace("\\", "&#092;", $mixed);
			$mixed = str_replace("/", "&#047;", $mixed);
		}
	}
	/**
	 *	Contraire de escape
	 *
	 *	@param 	mixed &$mixed	Une chaîne de caractère ou un tableau
	 *							passage par référence, pas besoin de réaffecter $mixed
	 *	@return	void			
	 */
	public function unescape(&$mixed)
	{	
		if (is_array($mixed))
		{
			foreach($mixed as $key => $value)
			{
				$mixed[$key] = preg_replace("/&#039;/", "'", $mixed[$key]);
				$mixed[$key] = preg_replace("/&lt;/", "<", $mixed[$key]);
				$mixed[$key] = preg_replace("/&gt;/", ">", $mixed[$key]);
				$mixed[$key] = str_replace("&#092;", "\\", $mixed[$key]);
				$mixed[$key] = str_replace("&#047;","/", $mixed[$key]);
			}
		}
		else
		{
			$mixed = preg_replace("/&#039;/", "'",$mixed);
			$mixed = preg_replace("/&lt;/", "<", $mixed);
			$mixed = preg_replace("/&gt;/", ">", $mixed);
			$mixed = str_replace("&#092;", "\\", $mixed);
			$mixed = str_replace("&#047;","/", $mixed);
		}
	}
	function verif_alpha_underscore($str)
	{
		$result = false;
		if($pos = strpos($str, '_') && ctype_alpha(str_replace('_','',$str)))
		{
			$result = true;
		}
		return $result;
	}
	function record($strTable,$line)
	{
		$idTable = $this->id_table($strTable);
		$lines = $this->line($idTable,$line);
		$columns = $this->columns($idTable);
		if($lines)
		{
			return $this->combine($columns,$lines);
		}
		else
		{
			return false;
		}
	}
	function records($table)
	{
		$records = [];
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(isset($this->data[$table]))
		{
			$columns = $this->columns($table);
			foreach($this->data[$table] as $i=>$rec)
			{
				if($i == 0) continue;
				$records[$i] = $this->combine($columns,$rec);
			}
			return $records;
		}
		else
		{
			return false;
		}
	}
	function select(array $columns,$strTable)
	{
		$id = $this->id_table($strTable);
		$cols = $this->columns($id);
		$columns = $this->filter_columns($cols,$columns);		
		$select = array();
		$records = $this->get_table($id);
		foreach($records as $i=>$record)
		{
			foreach($columns as $c=>$col)
			{
				$select[$id][$i][$c] = $this->data[$id][$i][$c];		
			}	
		}
		return $select[$id];
	}
	function select_where(array $columns,$strTable,$strColumn,$op='==',$value='')
	{
		$id = $this->id_table($strTable);
		$cols = $this->columns($id);
		$columns = $this->filter_columns($cols,$columns);
		$select = array();
		$records = $this->where($strTable,$strColumn,$op,$value);
		foreach($records as $i=>$record)
		{
			foreach($columns as $c=>$col)
			{
				if(isset($record[$c]))
				{
					$select[$id][$i][$c] = $record[$c];	
				}
				else
				{
					$select[$id][$i][$c] ='';	
				}
			}	
		}
		return $select[$id];
	}
	function pick(array $columns,$strTable)
	{
		$id = $this->id_table($strTable);
		$cols = $this->columns($id);
		$columns = $this->filter_columns($cols,$columns);		
		$select = array();
		$result = array();
		$records = $this->table($strTable);
		foreach($records as $i=>$record)
		{
			foreach($columns as $c=>$col)
			{
				$select[$id][$i][$c] = $this->data[$id][$i][$c];		
			}	
			$result[$i] = $this->combine($columns,$select[$id][$i]);
		}
		return $result;
	}
	function pick_where(array $columns,$strTable,$strColumn,$op='==',$value='',$unique=false)
	{
		$id = $this->id_table($strTable);
		$cols = $this->columns($id);
		$columns = $this->filter_columns($cols,$columns);
		$select = array();
		$result = array();
		$records = $this->where($strTable,$strColumn,$op,$value);
		foreach($records as $i=>$record)
		{
			foreach($columns as $c=>$col)
			{
				$select[$id][$i][$c] = $this->data[$id][$i][$c];		
			}	
			$result[$i] = $this->combine($columns,$select[$id][$i]);
		}
		if($unique)
		{
			$result = $result[array_key_first($result)];
		}
		return $result;
	}
	function sum($strTable,$strColumnToSum,$strColumn=null,$intKey=null)
	{
		$sum = 0;
		$intTable = $this->id_table($strTable);
		$columnToSum = $this->id_column($intTable,$strColumnToSum);
		if(! is_null($intKey))
		{
			$column = $this->id_column($intTable,$strColumn);
			foreach( $this->data[$intTable] as $realID=>$record )
			{
				if($realID==0) continue;
				if($record[$column] == $intKey)
				{
					$sum += $record[$columnToSum];  				
				}
			}
		}
		else
		{
			foreach( $this->data[$intTable] as $realID=>$record )
			{
				if($realID==0) continue;
				$sum += $record[$columnToSum];  				
			}
		}
		return $sum;
	}
	function math_column_where($strTable,$strColumn,$math, $string,$op='==',$value=null)
	{
		if(empty($strTable) || empty($strColumn) || empty($math) || empty($string) || empty($op))
		{
			$msg = 'Math a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$fieldwhere = $this->id_column($table,$string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		if($math == "+1")
		{
			$this->increment_where($strTable,$strColumn,1,$string,$op,$value);
			// increment_where($strTable,$strColumn,$text,$string,$op='==',$value=null)
			return true;
		}
		elseif($math == "-1")
		{
			$this->decrement_where($strTable,$strColumn,1,$string,$op,$value);
			// increment_where($strTable,$strColumn,$text,$string,$op='==',$value=null)
			return true;
		}
		$sum = 0;
		$avg= 0;
		$counter = 0;
		$result = 0;
		$max = 0;
		$min = 0;
		if(empty($value))
		{
			$value = '';
		}
		$tab = $this->data[$table];
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere)
				{				
					if(empty($this->data[$table][$i][$column])) continue;
					$this->data[$table][$i][$column] = preg_replace('/[^0-9.]/', '', $this->data[$table][$i][$column]);
					//$this->data[$table][$i][$column] = floatval($this->data[$table][$i][$column] );
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$sum +=  $this->data[$table][$i][$column]; 
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}	
								$counter++;	
								$avg = $sum/$counter;
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$sum +=  $this->data[$table][$i][$column]; 
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}		
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$sum +=  $this->data[$table][$i][$column]; 
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}	
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$sum +=  $this->data[$table][$i][$column];
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}	
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$sum +=  $this->data[$table][$i][$column];
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$sum +=  $this->data[$table][$i][$column];
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}	
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$sum +=  $this->data[$table][$i][$column];
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$sum +=  $this->data[$table][$i][$column]; 
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;	
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}		
								$counter++;	
								$avg = $sum/$counter;											
							}
						break;
						case '>=':	
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$sum +=  $this->data[$table][$i][$column];
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}		
								$counter++;	
								$avg = $sum/$counter;								
							}
						break;
						/*case '<=>':
							if($this->data[$table][$i][$fieldwhere] <=> $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;*/
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$sum +=  $this->data[$table][$i][$column]; 
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}	
								$counter++;	
								$avg = $sum/$counter;			
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$sum +=  $this->data[$table][$i][$column]; 
									$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
									if($min !==0)
									{
										$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
									}
									else
									{
										$min = $this->data[$table][$i][$column];
									}	
									$counter++;	
									$avg = $sum/$counter;		
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$sum +=  $this->data[$table][$i][$column];  
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}	
								$counter++;	
								$avg = $sum/$counter;			
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$sum +=  $this->data[$table][$i][$column];
								$max = ($max < $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$max;
								if($min !==0)
								{
									$min = ($min > $this->data[$table][$i][$column])?$this->data[$table][$i][$column]:$min;
								}
								else
								{
									$min = $this->data[$table][$i][$column];
								}
								$counter++;	
								$avg = $sum/$counter;											
							}		
					}
				}
			}
		}
		switch($math)
		{
			case 'Sum':
				$result=$sum;
			break;
			case 'Avg':
				$result=$avg;
			break;
			case 'Max':
				$result=$max;
			break;
			case 'Min':
				$result=$min;
			break;		
			// Medium difference - Écart moyen
			case 'Mdi':
				$result=($max - $min) / ($counter-1);
			break;				
			default:
				$result=$sum;
		}
		return $result;
	}
	function sub($strTable,$strColumnToSub,$strColumn=null,$intKey=null)
	{
		$sub = 0;
		$intTable = $this->id_table($strTable);
		$columnToSub = $this->id_column($intTable,$strColumnToSub);
		if(! is_null($intKey))
		{
			$column = $this->id_column($intTable,$strColumn);
			foreach( $this->data[$intTable] as $realID=>$record )
			{
				if($realID==0) continue;
				if($record[$column] == $intKey)
				{
					$sub -= $record[$columnToSub];  				
				}
			}
		}
		else
		{
			foreach( $this->data[$intTable] as $realID=>$record )
			{
				if($realID==0) continue;
				$sub -= $record[$columnToSub];  				
			}
		}
		return $sub;
	}
	/*****************************************/
	// Check foreign keys
	//****************************************/
	function valid_foreign_key($strColumn)
	{
		$return = false;
		$arr = explode('_',$strColumn);
		$strTable = $arr[0].'s';
		if(array_key_exists(1,$arr) && $arr[1]=='id' && $this->table_exists($strTable))
		{
			$return = $this->valid_master_table($strTable);
		}
		return $return;
	}
	function left($str, $length) 
	{
		return substr($str, 0, $length);
	}
	function right($str, $length) 
	{
		return substr($str, -$length);
	}
	function valid_rule($table,$column)
	{
		$return = false;		
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(!is_numeric($column))
		{
			$column = $this->id_column($table,$column);
		}
		$strColumn=$this->column_name($table,$column);
		if(stripos($strColumn,'_') != false)
		{
			$lenstr = strlen($strColumn);
			$idk = $this->right($strColumn,3);
			if($idk == '_id')
			{
				$strTable = $this->left($strColumn,$lenstr-3);
			}
			$idk = $this->left($strColumn,3);
			if($idk == 'id_')
			{
				$strTable = $this->right($strColumn,$lenstr-3);
			}
			if($this->table_exists('rules'))
			{
				$id =$this->id_table('rules');
				$rules = $this->get_table($id);
				foreach($rules as $line=>$rec)
				{
					if($rec[2]==$strTable.'s' || $rec[3]==$strTable.'s')
					{
						$return = true;
						break;
					}			
				}
			}
		}
		return $return;
	}
	function valid_master_table($strTable)
	{
		$return = false;
		$records = $this->where('rules','master','==',$strTable);
		if($records)
		{
			$return=true;
		}
		return $return;
	}
	function valid_slave_table($strTable)
	{
		$return = false;
		$records = $this->where('rules','slave','==',$strTable);
		if($records)
		{
			$return=true;
		}
		return $return;
	}	
	public function check_rule($str,$key)
	{
		$master = $str;
		try
		{
			$regles = $this->where_multiple('rules','master','==',$str);
			$str = rtrim($str,'s');
			$key = $this->get($master,$key,'id_'.$str);
			//indice 3 == slave field
			if($regles)
			{
				foreach($regles as $r=>$regle)
				{
					$strSlave =$regles[$r][3];
					$idSlave = $this->id_table($strSlave);
					try
					{
						$records = $this->where_multiple($strSlave,$str.'_id','==',$key);
						if($records)
						{
							foreach($records as $line=>$record)
							{
								//$this->preprint($records);
								// Last update 2023-12-02
								$this->check_rule($strSlave,$line);
								unset($this->data[$idSlave][$line]);
							}
							$this->repair_table($idSlave);
						}
					}
					catch (Exception $e) 
					{
						echo 'Caught exception: ',  $e->getMessage(), "\n";
					}
				}
			}
		}
		catch (Exception $e) 
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		//$this->save();
	}
	public function time_corrector($strTable,$strColumn,$format)
	{
		if(empty($strTable) || empty($strColumn))
		{
			$msg = 'To fix a column choose a time column and identify the format. It will transform as HH:MM:SS'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$column = $this->id_column($table,$strColumn);		
		$rows = $this->get_table($table);
		foreach($rows as $i=>$rec)
		{
			if($i == 0) continue;
			if($this->data[$table][$i][$column] !='')
			{
				$this->data[$table][$i][$column] = $this->valid_time($this->data[$table][$i][$column],$format);
			}
		}
		$this->save();
	}
	public function valid_time($string,$format='H:i:s')
	{
		$return = '';
		switch($format)
		{
			case 'H:i:s':
				$return = date($format, strtotime($string));
			break;
			case 'serialtime':
				$return = gmdate('H:i:s',$string);
			break;
		}
		return $return;
	}
	public function date_corrector($strTable,$strColumn,$format)
	{
		if(empty($strTable) || empty($strColumn) || empty($format))
		{
			$msg = 'To fix a column choose a column and identify the format. It will transform as YYYY-MM-DD'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$column = $this->id_column($table,$strColumn);		
		$rows = $this->get_table($table);
		foreach($rows as $i=>$rec)
		{
			if($i == 0) continue;
			if($this->data[$table][$i][$column] !='')
			{
				$this->data[$table][$i][$column] = $this->valid_date($this->data[$table][$i][$column],$format);
			}
		}
		$this->save();
	}
	function valid_date($string,$format,$time=false)
	{
		//var_dump($format); exit;
		$this->unescape($string);
		$gyear = 39;
		$date = new \DateTime();
		if(str_contains($format,'-'))
		{
			$del ='-';
		}
		elseif(str_contains($format,'/'))
		{
			$del = '/';
		}
		else
		{
			$del ='';
		}
		//echo empty($del); exit;
		if($del != '')
		{
			switch($format)
			{
				case 'MM'.$del.'DD'.$del.'YY':
					$array = explode($del,$string);
					$prefixyear = ($array[2] > $gyear)?'19':'20';
					$year = $prefixyear.$array[2];
					$month = $array[0];
					$day = $array[1];
				break;
				case 'MM'.$del.'DD'.$del.'YYYY':
					$array = explode($del,$string);
					$year = $array[2];
					$month = $array[0];
					$day = $array[1];
				break;
				case 'DD'.$del.'MM'.$del.'YY':
					$array = explode($del,$string);
					$prefixyear = ($array[2] > $gyear)?'19':'20';
					$year = $prefixyear.$array[2];
					$month = $array[1];
					$day = $array[0];
				break;
				case 'DD'.$del.'MM'.$del.'YYYY':
					$array = explode($del,$string);
					$year = $array[2];
					$month = $array[1];
					$day = $array[0];
				break;
				case 'YY'.$del.'MM'.$del.'DD':
					$array = explode($del,$string);
					$prefixyear = ($array[0] > $gyear)?'19':'20';
					$year = $prefixyear.$array[0];
					$month = $array[1];
					$day = $array[2];
				break;
				case 'YYYY'.$del.'MM'.$del.'DD':
					$array = explode($del,$string);
					$year = $array[0];
					$month = $array[1];
					$day = $array[2];
				break;
				default:
					$array = explode($del,$string);
					$year = $array[0];
					$month = $array[1];
					$day = $array[2];
			}
		}
		else
		{
			switch($format)
			{
				//MDY
				case 'MMDDYY':
					//('082619','MMDDYY');
					$month = $this->left($string,2);
					$day = substr($string, -4, 2);				
					$year = $this->right($string,2);
					$prefixyear = ($year > $gyear)?'19':'20';
					$year = $prefixyear.$year;
				break;
				case 'MMDDYYYY':
					//('08262019','MMDDYYYY');
					$month = $this->left($string,2);
					$day = substr($string, -6, 2);				
					$year = $this->right($string,4);
				break;
				//DMY
				case 'DDMMYY':
					//('210819','DDMMYY');
					$day = $this->left($string,2);
					$month = substr($string, -4, 2);				
					$year = $this->right($string,2);
					$prefixyear = ($year > $gyear)?'19':'20';
					$year = $prefixyear.$year;
				break;
				case 'DDMMYYYY':
					//('21082019','DDMMYYYY');
					$day = $this->left($string,2);
					$month = substr($string, -6, 2);				
					$year = $this->right($string,4);
				break;
				//YMD
				case 'YYMMDD':
					//('190826','YYMMDD');
					$year = $this->left($string,2);
					$month = substr($string, -4, 2);				
					$day = $this->right($string,2);
					$prefixyear = ($year > $gyear)?'19':'20';
					$year = $prefixyear.$year;
				break;
				case 'YYYYMMDD':
					//('20190826','YYYYMMDD');
					$year = $this->left($string,4);
					$month = substr($string, -4, 2);				
					$day = $this->right($string,2);
				break;
				//MM
				case 'MM':
					//('20190826','YYYYMMDD');
					$year = date("Y");
					$month = $string;
					$day = 15;
					$a_date = $year.'-'.$month.'-'.$day;
					$darr = date("Y-m-t", strtotime($a_date));					
					return $darr;
				break;
			}
		}
		$date->setDate((int)$year,(int)$month,(int)$day);
		$newDate = ($time)? $date->format('Y-m-d H:i:s'):$date->format('Y-m-d');
		return $newDate;
	}
	//***************************************************************//
	//********  SETTING ONE TABLE FOR USAGE   *********//
	//***************************************************************//
	function set_table(array $a)
	{
		$this->table = strtolower($a['table']);
		$this->id_table = $this->id_table($this->table);
		$this->primary = strtolower($a['primary']);
		$this->table_nbrlines = $this->count_lines($this->table);
		$this->table_nbrcolumns = $this->count_columns($this->table);
	}
	function all($col=false)
	{
		$recordset = $this->get_table($this->id_table);
		if(!$col)
		{
			unset($recordset[0]);
		}
		return $recordset;
	}
	public function find_replace($strTable,$strColumn,$find=' ',$replace=' ')
	{
		//$bodytag = str_ireplace("%body%", "black", "<body text=%BODY%
		//if(empty($strTable) || empty($strColumn) || empty($find) || empty($replace))
		if(empty($strTable) || empty($strColumn))
		{
			$msg = 'Search a column, find and replace.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}	
		$table = $this->id_table($strTable);
		$rows = $this->get_table($table);
		$col = $this->id_column($table,$strColumn);
		foreach($rows as $i=>$rec)
		{
			if($i==0) continue;
			if(empty($this->data[$table][$i][$col]))
			{
				$this->data[$table][$i][$col] = '';
			}
			else
			{
				$str = $this->data[$table][$i][$col];
				$str = str_ireplace($find,$replace,$str);
				$this->data[$table][$i][$col] = $str;
			}
		}
		$this->save();
	}

	function replace_name_with_id($strTableFrom, $strColumnFrom, $strTableTo, $strColumnTo, $compare = 2) 
	{
		// Vérification des paramètres de base
		if(empty($strTableFrom) || empty($strColumnFrom))
		{
			$msg = 'Search a column, find and replace.'; 
			$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
			throw new \Exception($msg);
		}
		
		// Vérification des tables et colonnes
		$FromTableIndex = $this->id_table($strTableFrom);
		if($FromTableIndex === false) {
			throw new \Exception("Table source '$strTableFrom' non trouvée.");
		}
		
		$FromColumnIndex = $this->id_column($strTableFrom, $strColumnFrom);
		if($FromColumnIndex === false) {
			throw new \Exception("Colonne source '$strColumnFrom' non trouvée.");
		}
		
		$ToTableIndex = $this->id_table($strTableTo);
		if($ToTableIndex === false) {
			throw new \Exception("Table destination '$strTableTo' non trouvée.");
		}
		
		$ToColumnIndex = $this->id_column($strTableTo, $strColumnTo);
		if($ToColumnIndex === false) {
			throw new \Exception("Colonne destination '$strColumnTo' non trouvée.");
		}
		
		// Créer un tableau associatif des clients (nom => id)
		$customerMap = [];
		$errors = [];
		
		for ($i = 1; isset($this->data[$FromTableIndex][$i]); $i++)
		{
			try {
				if(isset($this->data[$FromTableIndex][$i][$FromColumnIndex]) && 
				   isset($this->data[$FromTableIndex][$i][$compare])) {
					$customerId = $this->data[$FromTableIndex][$i][$FromColumnIndex];
					$customerName = $this->data[$FromTableIndex][$i][$compare];
					$customerMap[$customerName] = $customerId;
				}
			} catch (\Exception $e) {
				// Noter l'erreur mais continuer
				$errors[] = "Erreur ligne $i: " . $e->getMessage();
				continue;
			}
		}
		
		// Parcourir la table copyprojets et remplacer le nom par l'id
		for ($i = 1; isset($this->data[$ToTableIndex][$i]); $i++) 
		{
			try {
				if(isset($this->data[$ToTableIndex][$i][$ToColumnIndex])) {
					$customerName = $this->data[$ToTableIndex][$i][$ToColumnIndex];
					
					// Si le nom du client existe dans la table customers, le remplacer par son ID
					if (!empty($customerName) && isset($customerMap[$customerName]))
					{
						$this->data[$ToTableIndex][$i][$ToColumnIndex] = $customerMap[$customerName];
					} 
					else
					{
						// Si le client n'est pas trouvé ou que le champ est vide, garder la valeur actuelle
						if (!empty($customerName)) {
							$errors[] = "Client '$customerName' non trouvé dans la table source.";
							$this->data[$ToTableIndex][$i][$ToColumnIndex] = '0';
						} else {
							$this->data[$ToTableIndex][$i][$ToColumnIndex] = '';
						}
					}
				}
			} catch (\Exception $e) {
				// Noter l'erreur mais continuer
				$errors[] = "Erreur ligne $i: " . $e->getMessage();
				continue;
			}
		}
		
		// S'il y a des erreurs, informer l'utilisateur mais ne pas arrêter le traitement
		if (!empty($errors)) {
			$errorMsg = implode("<br>", $errors);
			$errors[] = "Certains enregistrements n'ont pas pu être traités:<br>" . $errorMsg;
		}
		
		// Sauvegarder les modifications
		if ($this->save()) {
			return true;
		} else {
			throw new \Exception("Erreur lors de la sauvegarde des données.");
		}
	}
	public function copy_column($strTable,$strColumnFrom,$strColumnTo)
	{
		if(empty($strTable) || empty($strColumnFrom) || empty($strColumnTo))
		{
			$msg = 'To duplicate a column you need to identify the column that you want to duplicate, named the new column. '; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$from = $this->id_column($table,$strColumnFrom);
		$this->add_column($table,$strColumnTo);
		$count = count($this->data[$table]);
		$to = $this->id_column($table,$strColumnTo);
		$i = 1;
		while($i < $count)   
		{
			$this->data[$table][$i][$to] = $this->data[$table][$i][$from];	
			$i++;
		}
		$this->save();
	}
	public function split_column($strTable,$strColumnFrom,$strColumnTo,$left=null,$right=null)
	{
		if( empty($strTable) || empty($strColumnFrom) || empty($strColumnTo))
		{
			$msg = 'To split a column you need to identify the column that you want to work with, named the new column. '; 
			$msg .= 'You will need to set how much left and right caracters you want to keep.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$from = $this->id_column($table,$strColumnFrom);
		$this->add_column($table,$strColumnTo);
		$count = count($this->data[$table]);
		$to = $this->id_column($table,$strColumnTo);
		$i = 1;
		while($i < $count)   
		{
			$strFrom =  $this->data[$table][$i][$from];
			$strFrom = trim(preg_replace('/\s+/', ' ', $strFrom));
			$lengthFrom = strlen($strFrom);
			if($left > 0 and $right > 0)
			{
				//var_dump($lengthFrom); 
				$strLeft = $this->left($strFrom,$left);
				$strRight = $this->right($strFrom,$right);
				$this->data[$table][$i][$from] = $strLeft;	
				$this->data[$table][$i][$to] = $strRight ;
			}
			elseif($left > 0 and $right =='')
			{
				//var_dump($lengthFrom); 
				$strLeft = $this->left($strFrom,$left);
				$right = $lengthFrom - $left;
				$strRight = $this->right($strFrom,$right);
				$this->data[$table][$i][$from] = $strLeft;	
				$this->data[$table][$i][$to] = $strRight;
			}
			elseif($right > 0 and $left =='')
			{
				//var_dump($lengthFrom); 
				$strRight = $this->right($strFrom,$right);
				$left = $lengthFrom - $right;
				$strLeft = $this->left($strFrom,$left);
				$this->data[$table][$i][$from] = $strLeft;	
				$this->data[$table][$i][$to] = $strRight;
			}
			elseif($left > 0 and $right == 0)
			{
				//var_dump($lengthFrom); 
				$strLeft = $this->left($strFrom,$left);
				$right = $lengthFrom - $left;
				$strRight = $this->right($strFrom,$right);
				$this->data[$table][$i][$from] = $strLeft;	
				$this->data[$table][$i][$to] = '';
			}
			elseif($right > 0 and $left == 0 )
			{
				//var_dump($lengthFrom); 
				$strRight = $this->right($strFrom,$right);
				$left = $lengthFrom - $right;
				$strLeft = $this->left($strFrom,$left);
				$this->data[$table][$i][$from] = '';	
				$this->data[$table][$i][$to] = $strRight;
			}
			$i++;
		}
		$this->save();
	}
	public function split_column_needle($strTable,$strColumnFrom,$strColumnTo,$needle=null)
	{
		if( empty($strTable) || empty($strColumnFrom) || empty($strColumnTo))
		{
			$msg = 'To split a column you need to identify the column that you want to work with, named the new column. '; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$from = $this->id_column($table,$strColumnFrom);
		$this->add_column($table,$strColumnTo);
		$count = count($this->data[$table]);
		$to = $this->id_column($table,$strColumnTo);
		$i = 1;
		while($i < $count)   
		{
			$strFrom =  $this->data[$table][$i][$from];
			$strFrom = trim(preg_replace('/\s+/', ' ', $strFrom));
			$lengthFrom = strlen($strFrom);
			if(empty($needle))
			{
				$needle = ' ';
			}
			$pos = stripos($strFrom,$needle);
			if($pos === false)
			{
				$left = $lengthFrom;
				$right = 0;
			}
			else
			{
				$left = $pos; 
				$right = $lengthFrom - $left;
			}
			if($left > 0 and $right > 0)
			{
				//var_dump($lengthFrom); 
				$strLeft = $this->left($strFrom,$left);
				$strRight = $this->right($strFrom,$right);
				$this->data[$table][$i][$from] = $strLeft;	
				$this->data[$table][$i][$to] = $strRight ;
			}
			elseif($left > 0 and $right == 0)
			{
				//var_dump($lengthFrom); 
				$strLeft = $this->left($strFrom,$left);
				$right = $lengthFrom - $left;
				$strRight = $this->right($strFrom,$right);
				$this->data[$table][$i][$from] = $strLeft;	
				$this->data[$table][$i][$to] = '-';
			}
			elseif($right > 0 and $left == 0)
			{
				//var_dump($lengthFrom); 
				$strRight = $this->right($strFrom,$right);
				$left = $lengthFrom - $right;
				$strLeft = $this->left($strFrom,$left);
				$this->data[$table][$i][$from] = '-';	
				$this->data[$table][$i][$to] = $strRight;
			}
			$i++;
		}
		$this->save();
	}
	public function move_column($strTable,$strColumn,$strToTable)
	{
		if(empty($strTable) || empty($strColumn) || empty($strToTable) || !$this->table_exists($strToTable))
		{
			$msg = 'To move a column you need to identify the column that you want to move, and the table where you want to move it. '; 
			$msg .='Noticed that if your column contains more records than the table receiving it. It will be truncate.';
			if(!$this->table_exists($strToTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strToTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$totable = $this->id_table($strToTable);
		$from = $this->id_column($table,$strColumn);
		$this->add_column($totable,$strColumn);
		$count = count($this->data[$table]);
		$to = $this->id_column($totable,$strColumn);
		$i = 1;
		while($i < $count)   
		{
			$this->data[$totable][$i][1] = $i;
			if(isset($this->data[$table][$i][$from]))
			{
				$this->data[$totable][$i][$to] = $this->data[$table][$i][$from];	
			}
			else
			{
				$this->data[$totable][$i][$to] = '-';	
			}
			$i++;
		}
		$this->save();
		$this->delete_column($table,$from);
	}
	public function copy_column_keys($strTable,$strColumn,$strToTable,$strToField,$string,$op='==',$value=null)
	{
		if(empty($strTable) || empty($strColumn)  || empty($strToTable) || empty($strToField)  || empty($string) || empty($op))
		{
			$msg = 'Allow me to manually tell it what the key for the column is? example: phonetype1="H" '; 
			$msg .='means that phone1 should move to the HomePhone column'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strToTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$totable = $this->id_table($strToTable);
			$tofield = $this->id_column($totable,$strToField);
			$fieldwhere = $this->id_column($table,$string);
			$maxnbrlines = $this->count_lines($totable);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$strToField.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$tab = $this->data[$table];
		if(empty($value))
		{
			$value = '';
		}
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere && $i<=$maxnbrlines)
				//if($col == $fieldwhere)
				{
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column]; 
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '>=':
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						/*case '<=>':
							if($this->data[$table][$i][$fieldwhere] <=> $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;*/
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be numeric and separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$this->data[$table][$i][$column] = $text;  
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}		
					}	
				}
			}
		}
		$this->save();
	}
	public function copy_data_keys($strTable,$strColumn,$strToTable,$strToField,$left,$right,$string,$op='==',$value='')
	{
		if(empty($strTable) || empty($strColumn)  || empty($strToTable) || empty($strToField)  || empty($left) || empty($right) || empty($string) || empty($op))
		{
			$msg = 'See the image exemple below: Copied the Insured field from the [PatIns] table into the [Patients] table to a new previously created field called PrimarySub. Use [PatIns]PatNum and [Patients]PatientNumber to match records. Then add a condition to the [PatIns]InsOrd field which must specifically be an "A".'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strToTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		//if($this->column_exists($table,$strColumn) && $this->column_exists($table,$strToField) && $this->column_exists($table,$string))
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$keyleft = $this->id_column($table,$left);	
			$totable = $this->id_table($strToTable);
			$tofield = $this->id_column($totable,$strToField);
			$keyright = $this->id_column($totable,$right);
			$fieldwhere = $this->id_column($table,$string);
			$maxnbrlines = $this->count_lines($totable);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$strToField.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$tab = $this->data[$table];
		$totab = $this->data[$totable];
		if(empty($value))
		{
			$value = '';
		}
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($totab as $j=>$sec)
			{
				if($j==0) continue;
				if($this->data[$table][$i][$keyleft] == $this->data[$totable][$j][$keyright])
				{
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column]; 
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case '>=':
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be numeric and separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$this->data[$totable][$i][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$this->data[$table][$i][$column] = $text;  
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$totable][$j][$tofield] = $this->data[$table][$i][$column];  
							}
					}
				}					
			}				
		}
		$this->save();
	}
	public function move_one_to_many($strTable,$strColumn,$strToTable,$strToTableKey,$strTableKey)
	{
		if(empty($strTable) || empty($strColumn) || empty($strToTable) || empty($strTableKey) || empty($strToTableKey) || !$this->table_exists($strToTable))
		{
			$msg = 'To move a column you need to identify the column that you want to move, and the table where you want to move it. '; 
			$msg .='You also need to match the keys.';
			if(!$this->table_exists($strToTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strToTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		$fromkey = $this->id_column($table,$strTableKey);
		$from = $this->id_column($table,$strColumn);
		$countfrom = count($this->data[$table]);
		//TO TABLE
		$totable = $this->id_table($strToTable);
		$tokey = $this->id_column($totable,$strToTableKey);
		$this->add_column($totable,$strColumn);
		$to = $this->id_column($totable,$strColumn);
		$count = count($this->data[$totable]);
		$j = 1;
		while($j < $countfrom)   
		{
			$i=1;
			while($i < $count)
			{ 
				if($this->data[$totable][$i][$tokey] == $this->data[$table][$j][$fromkey])
				{
					$this->data[$totable][$i][$to] = $this->data[$table][$j][$from];
				}
				$i++;
			}
			$j++;
		}
		$this->save();
		$this->delete_column($table,$from);
	}
	public function colorize($string,$color)
	{
		return '<span style="color:'.$color.';"> '.$string.' </span>';
	}
	public function order_by($strTable,$strColumn,$sort=SORT_ASC)
	{
		$records = array();
		$tab = $this->id_table($strTable);
		$col = $this->id_column($tab,$strColumn);				
		$lines = $this->count_lines($tab);	
		$datas = $this->data[$tab];
		$columns = $this->data[$tab][0];
		foreach($datas as $key=>$row)
		{
			if($key==0) continue;
			try
			{
				$dat[$key] = $this->combine($columns,$row);
			}
			catch(Exception $e) 
			{
				echo $e->getMessage().' Data line: '.$key;
			}
		}
		$strColumn = array_column($dat, $strColumn);
		array_multisort($strColumn,$sort,$dat);
		foreach($dat as $i=>$rec)
		{
			$j=1;
			foreach($rec as $col=>$value)
			{
				$this->data[$tab][$i+1][$j++]=$value;
			}
		}
		$this->save();
	}
	public function merge_rows($strTable,$strColKey,$strColOrder,$strColResult)
	{
		if(empty($strTable) || empty($strColKey)  || empty($strColOrder) || empty($strColResult))
		{
			$msg = 'Merge rows from table '.$this->colorize($strTable,'red').' to a column in the first row by matching keys.'; 
			if(!$this->table_exists($strTable))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$table = $this->id_table($strTable);
		$colkey = $this->id_column($table,$strColKey);
		$colorder = $this->id_column($table,$strColOrder);
		$colresult = $this->id_column($table,$strColResult);
		$hkey = array();		
		$nbr = $this->count_lines($table);
		for($i=1;$i<=$nbr;$i++)
		{
			$key = $this->data[$table][$i][$colkey]; 
			if(!array_key_exists($key,$hkey))
			{
				$rows = $this->where($strTable,$strColKey,'==',$key);
				$arr = array();
				foreach($rows as $real=>$row)
				{
					$arr[$real] = $row[$colorder];
				}
				asort($arr);
				$firstkey = array_key_first($arr);
				$string='';
				foreach($arr as $k=>$value)
				{
					$string .= $this->data[$table][$k][$colresult].' ';
					if($k != $firstkey)
					{
						$tobedelete[$k]=$k;
					}
				}
				$hkey[$key] = $key;
				$this->data[$table][$firstkey][$colresult] = $string;
			}
		}
		foreach($tobedelete as $k)
		{
			unset($this->data[$table][$k]);
		}
		//$this->save();
		$this->repair_table($table);
	}
	public function repair_table($table)
	{
		$j=1;
		$records = $this->get_table($table);
		foreach($records as $i=>$rec)
		{
			if($i==0) continue;
			if($i !== $j)
			{
				$this->data[$table][$j] = $this->data[$table][$i];
				unset($this->data[$table][$i]);
			}
			$j++;
		}
		ksort($this->data,SORT_NUMERIC);
		$this->save();
	}
	public function del_duplicates($table,$column)
	{
		if(empty($column))
		{
			$msg = 'To delete duplicates from a table. You need to identify the field you want to work with'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		if(!is_numeric($table))
		{
			$table = $this->id_table($table);
		}
		if(!is_numeric($column))
		{
			$column = $this->id_column($table,$column);
		}
		$str=array();
		$records = $this->get_table($table);
		foreach($records as $i=>$rec)
		{
			if($i==0) continue;
			$str[$i] = $this->data[$table][$i][$column];
		}
		$str = array_unique($str);
		foreach($records as $i=>$rec)
		{
			if($i==0) continue;
			if(!array_key_exists($i,$str))
			{
				unset($this->data[$table][$i]);
			}
		}
		$this->repair_table($table);
	}
	public function strtoint($table,$column=null)
	{
		$result = array();
		if(!is_numeric($table))
		{
			$result['table'] = $this->id_table($table);
		}
		if(isset($column) && !is_numeric($column))
		{
			$result['column'] = $this->id_column($table,$column);
		}
		return $result;
	}
	public function check_system()
	{
		$i=1;
		foreach($this->data as $t)
		{
			if(!isset($this->data[0][0][$i]) && isset($this->data[$i]))
			{
				$msg = 'ERROR[1] Something break the system between [0][0][1] (table name) and [1][0][1] (table usage). <a href="'.WEBROOT.'main/ini">Initialize</a>'; 
				throw new \Exception($msg);
			}
			++$i;
		}
	}
	public function renumber($strTable,$strColumn,$start=1)
	{
		if(empty($strTable) || empty($strColumn) || empty($start))
		{
			$msg = 'It is not a must but the best practice would be to duplicate a column before.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//$this->save(true);
		$table = $this->id_table($strTable);
		$column = $this->id_column($table,$strColumn);
		//select(array $columns,$strTable);
		$arr_columns = array($column=>$strColumn);
		//var_dump($arr_columns); exit;
		$records = $this->select($arr_columns,$strTable);
		foreach($records as $i=>$rec)
		{
			if($i==0) continue;
			//++int parceque on veut pas la premiere ligne 0;
			$this->data[$table][$i][$column] = $start;
			$start++;
		}
		$this->save();
	}
	public function matches($strMaster,$strMasterOldColumn,$strSlave,$strSlaveOldColumn,$strMasterNewNumbersColumn)
	{
		if(empty($strMaster) || empty($strMasterOldColumn) || empty($strSlave) || empty($strSlaveOldColumn) || empty($strMasterNewNumbersColumn))
		{
			$msg = "Reassign key values of a column in a slave table against new values in the master table. First you will need to duplicate a column in $strMaster and renumber it.";
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$id_master = $this->id_table($strMaster);
		$mrecords = $this->get_table($id_master);
		$id_m_old_column = $this->id_column($id_master,$strMasterOldColumn);
		$id_m_newnumbers_column = $this->id_column($id_master,$strMasterNewNumbersColumn);
		$id_slave = $this->id_table($strSlave);
		$srecords = $this->get_table($id_slave);
		$id_s_old_column = $this->id_column($id_slave,$strSlaveOldColumn);
		//erase column row header
		unset($mrecords[0]);
		unset($srecords[0]);
		foreach($mrecords as $m=>$mrec)
		{
			foreach($srecords as $s=>$srec)
			{
				if($mrec[$id_m_old_column] == $srec[$id_s_old_column])
				{
					$this->data[$id_slave][$s][$id_s_old_column] = $this->data[$id_master][$m][$id_m_newnumbers_column];
				}
			}
		}
		//$this->preprint($this->data[$id_slave]); 
		$this->save();
	}
	public function copy_text_where($strTable,$strColumn,$text,$string,$op='==',$value=null)
	{
		if(empty($strTable) || empty($strColumn) || empty($text) || empty($string) || empty($op))
		{
			$msg = 'Copy a text in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$fieldwhere = $this->id_column($table,$string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		if(empty($value))
		{
			$value = '';
		}
		$tab = $this->data[$table];
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere)
				{
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] = $text; 
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case '>=':
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						/*case '<=>':
							if($this->data[$table][$i][$fieldwhere] <=> $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;*/
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$this->data[$table][$i][$column] = $text;  
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$this->data[$table][$i][$column] = $text;  
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] = $text;  
							}		
					}	
				}
			}
		}
		$this->save();
	}
public function erase_text_where($strTable,$strColumn,$string,$op='==',$value=null)
	{
		if(empty($strTable) || empty($strColumn) || empty($string) || empty($op))
		{
			$msg = 'Copy a text in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$fieldwhere = $this->id_column($table,$string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		if(empty($value))
		{
			$value = '';
		}
		$tab = $this->data[$table];
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere)
				{
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] = ''; 
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$this->data[$table][$i][$column] = ''; 
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$this->data[$table][$i][$column] = ''; 
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$this->data[$table][$i][$column] = '';
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$this->data[$table][$i][$column] = '';
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;
						case '>=':
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;
						/*case '<=>':
							if($this->data[$table][$i][$fieldwhere] <=> $value)
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;*/
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$this->data[$table][$i][$column] = '';  
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$this->data[$table][$i][$column] = '';  
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] = '';  
							}		
					}	
				}
			}
		}
		$this->save();
	}
	public function increment_where($strTable,$strColumn,$text,$string,$op='==',$value=null)
	{
		if(empty($strTable) || empty($strColumn) || empty($text) || empty($string) || empty($op))
		{
			$msg = 'Copy a text in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$fieldwhere = $this->id_column($table,$string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$text = intval($text);
		if(empty($value))
		{
			$value = '';
		}
		$tab = $this->data[$table];
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere)
				{
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] += $text; 
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case '>=':
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						/*case '<=>':
							if($this->data[$table][$i][$fieldwhere] <=> $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;*/
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$this->data[$table][$i][$column] += $text;  
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$this->data[$table][$i][$column] += $text;  
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] += $text;  
							}		
					}	
				}
			}
		}
		$this->save();
	}
	public function decrement_where($strTable,$strColumn,$text,$string,$op='==',$value=null)
	{
		if(empty($strTable) || empty($strColumn) || empty($text) || empty($string) || empty($op))
		{
			$msg = 'Copy a text in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn) && $this->column_exists($table,$string))
		{
			$column = $this->id_column($table,$strColumn);
			$fieldwhere = $this->id_column($table,$string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		$text = intval($text);
		if(empty($value))
		{
			$value = '';
		}
		$tab = $this->data[$table];
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere)
				{
					switch($op)
					{
						case '==':
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] -= $text; 
							}
						break;
						case '===':
							if($this->data[$table][$i][$fieldwhere] === $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '!=':
							if($this->data[$table][$i][$fieldwhere] != $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '<>':
							if($this->data[$table][$i][$fieldwhere] <> $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '!==':
							if($this->data[$table][$i][$fieldwhere] !== $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '<':
							if($this->data[$table][$i][$fieldwhere] < $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '>':
							if($this->data[$table][$i][$fieldwhere] > $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '<=':
							if($this->data[$table][$i][$fieldwhere] <= $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case '>=':
							if($this->data[$table][$i][$fieldwhere] >= $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						/*case '<=>':
							if($this->data[$table][$i][$fieldwhere] <=> $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;*/
						case 'BETWEEN':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						case 'LIST':
							if(str_contains($value,',') == false )
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',',$value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$this->data[$table][$i][$column] -= $text;   
								}
							}
						break;
						case 'LIKE':
							if(stripos($this->data[$table][$i][$fieldwhere],$value) !== false)
							{
								$this->data[$table][$i][$column] -= $text;  
							}
						break;
						default:
							if($this->data[$table][$i][$fieldwhere] == $value)
							{
								$this->data[$table][$i][$column] -= $text;  
							}		
					}	
				}
			}
		}
		$this->save();
	}
	public function reverse_sequence_where($strTable,$strColumn,$value=null)
	{
		if(empty($strTable) || empty($strColumn) || empty($value) )
		{
			$msg = 'Copy a text in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		//FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table,$strColumn))
		{
			$column = $this->id_column($table,$strColumn);
			$fieldwhere = $this->id_column($table,$strColumn);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new \Exception($msg);
		}
		if(empty($value))
		{
			$value = '';
		}
		$tab = $this->data[$table];
		$test = explode(',',$value);
		$count = $test[1];
		foreach($tab as $i=>$rec)
		{
			if($i==0) continue;
			foreach($rec as $col=>$val)
			{
				if($col == $fieldwhere)
				{
					if(str_contains($value,',') == false )
					{
						$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
						$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
						throw new \Exception($msg);
					}
					//echo 'here!';exit;
					if($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1])
					{
						$this->data[$table][$i][$column] = $count;
						$count--;
					}	
				}
			}
		}
		$this->save();
	}

	public function lowercase_text_where($strTable, $strColumn, $string, $op = '==', $value = null, $encoding = 'UTF-8')
	{
		if(empty($strTable) || empty($strColumn) || empty($string) || empty($op))
		{
			$msg = 'Convert text to lowercase in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
			throw new \Exception($msg);
		}
		
		// FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table, $strColumn) && $this->column_exists($table, $string))
		{
			$column = $this->id_column($table, $strColumn);
			$fieldwhere = $this->id_column($table, $string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
			throw new \Exception($msg);
		}
		
		if(empty($value))
		{
			$value = '';
		}
		
		$tab = $this->data[$table];
		foreach($tab as $i => $rec)
		{
			if($i == 0) continue;
			foreach($rec as $col => $val)
			{
				if($col == $fieldwhere)
				{
					$conditionMet = false;
					
					switch($op)
					{
						case '==':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] == $value);
							break;
						case '===':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] === $value);
							break;
						case '!=':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] != $value);
							break;
						case '<>':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] <> $value);
							break;
						case '!==':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] !== $value);
							break;
						case '<':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] < $value);
							break;
						case '>':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] > $value);
							break;
						case '<=':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] <= $value);
							break;
						case '>=':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] >= $value);
							break;
						case 'BETWEEN':
							if(str_contains($value, ',') == false)
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',', $value);
							$conditionMet = ($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1]);
							break;
						case 'LIST':
							if(str_contains($value, ',') == false)
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',', $value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$conditionMet = true;
									break;
								}
							}
							break;
						case 'LIKE':
							$conditionMet = (stripos($this->data[$table][$i][$fieldwhere], $value) !== false);
							break;
						default:
							$conditionMet = ($this->data[$table][$i][$fieldwhere] == $value);
					}
					
					// Si la condition est remplie, convertir le texte en majuscules
					if($conditionMet)
					{
						$currentText = $this->data[$table][$i][$column];
						// Utiliser mb_strtoupper pour gérer correctement les caractères accentués
						$this->data[$table][$i][$column] = mb_strtolower($currentText, $encoding);
					}
				}
			}
		}
		$this->save();
	}

	public function uppercase_text_where($strTable, $strColumn, $string, $op = '==', $value = null, $encoding = 'UTF-8')
	{
		if(empty($strTable) || empty($strColumn) || empty($string) || empty($op))
		{
			$msg = 'Convert text to lowercase in a column provided it respects the key'; 
			if(!$this->table_exists($strTable) && !empty($strColumn))
			{
				$msg = 'Table '.$strTable.' has not been imported yet.'; 
			}
			$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
			throw new \Exception($msg);
		}
		
		// FROM TABLE
		$table = $this->id_table($strTable);
		if($this->column_exists($table, $strColumn) && $this->column_exists($table, $string))
		{
			$column = $this->id_column($table, $strColumn);
			$fieldwhere = $this->id_column($table, $string);
		}
		else
		{
			$msg = 'The column '.$strColumn.' or '.$string.' does not exists or are misspell.'; 
			$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
			throw new \Exception($msg);
		}
		
		if(empty($value))
		{
			$value = '';
		}
		
		$tab = $this->data[$table];
		foreach($tab as $i => $rec)
		{
			if($i == 0) continue;
			foreach($rec as $col => $val)
			{
				if($col == $fieldwhere)
				{
					$conditionMet = false;
					
					switch($op)
					{
						case '==':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] == $value);
							break;
						case '===':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] === $value);
							break;
						case '!=':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] != $value);
							break;
						case '<>':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] <> $value);
							break;
						case '!==':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] !== $value);
							break;
						case '<':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] < $value);
							break;
						case '>':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] > $value);
							break;
						case '<=':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] <= $value);
							break;
						case '>=':
							$conditionMet = ($this->data[$table][$i][$fieldwhere] >= $value);
							break;
						case 'BETWEEN':
							if(str_contains($value, ',') == false)
							{
								$msg = 'When the operator is BETWEEN the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',', $value);
							$conditionMet = ($this->data[$table][$i][$fieldwhere] >= $test[0] && $this->data[$table][$i][$fieldwhere] <= $test[1]);
							break;
						case 'LIST':
							if(str_contains($value, ',') == false)
							{
								$msg = 'When the operator is LIST the values provided must be separated by a comma.'; 
								$msg = htmlentities($msg, ENT_COMPAT, "UTF-8");
								throw new \Exception($msg);
							}
							$test = explode(',', $value);
							foreach($test as $tes)
							{
								if($this->data[$table][$i][$fieldwhere] == $tes)
								{
									$conditionMet = true;
									break;
								}
							}
							break;
						case 'LIKE':
							$conditionMet = (stripos($this->data[$table][$i][$fieldwhere], $value) !== false);
							break;
						default:
							$conditionMet = ($this->data[$table][$i][$fieldwhere] == $value);
					}
					
					// Si la condition est remplie, convertir le texte en majuscules
					if($conditionMet)
					{
						$currentText = $this->data[$table][$i][$column];
						// Utiliser mb_strtoupper pour gérer correctement les caractères accentués
						$this->data[$table][$i][$column] = mb_strtoupper($currentText, $encoding);
					}
				}
			}
		}
		$this->save();
	}
	public function preprint($array)
	{
		echo '<pre>';
		var_dump($array);
		echo '</pre>';
		//exit;
	}
	public function __destruct()
	{
		$this->cleanup();
	}
	public function cleanup() 
	{
		foreach ($this as $key => $value) 
		{
            unset($this->$key);
        }
	}	
	public function remove_accents($string) 
	{
		if(!empty($string))
		{
			if ( !preg_match('/[\x80-\xff]/', $string) )
			{
				return $string;
			}
		}
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
		chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
		chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
		chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
		chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
		chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
		chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
		chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
		chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
		chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
		chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
		chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
		chr(195).chr(191) => 'y',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
		);
		if(!empty($string))
		{
			$string = strtr($string, $chars);
		}
		return $string;
	}
}
?>