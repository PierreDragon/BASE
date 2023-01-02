<?php 
/**
* @class: Get
* @version: 8.1
* @author: info@webiciel.ca
* @php: 7.4
* @revision: 2022-12-29 10:00
* @note: retrait de tout ce qui concerne mysql
* @licence MIT
*/ 
class DB extends Model
{
	public function initialize()
	{
		unset($this->data);
		$this->data[0][0][1]='rules';
		$this->data[1][0][1]='id_rule';
		$this->data[1][0][2]='master';
		$this->data[1][0][3]='slave';
		$this->data[1][0][4]='comment';
		$this->save();
	}
	public function demo()
	{
		unset($this->data);
		$this->data[0][0][1]='rules';
		$this->data[0][0][2]='users';
		$this->data[0][0][3]='notes';
		$this->data[1][0][1]='id_rule';
		$this->data[1][0][2]='master';
		$this->data[1][0][3]='slave';
		$this->data[1][0][4]='comment';
		$this->data[1][1][1]='1';
		$this->data[1][1][2]='users';
		$this->data[1][1][3]='notes';
		$this->data[1][1][4]='';
		$this->data[2][0][1]='id_user';
		$this->data[2][0][2]='user';
		$this->data[2][1][1]='1';
		$this->data[2][1][2]='user 1';
		$this->data[2][2][1]='2';
		$this->data[2][2][2]='user 2';
		$this->data[2][3][1]='3';
		$this->data[2][3][2]='user 3';
		$this->data[3][0][1]='id_note';
		$this->data[3][0][2]='note';
		$this->data[3][0][3]='user_id';
		$this->data[3][1][1]='1';
		$this->data[3][1][2]='note 1';
		$this->data[3][1][3]='1';
		$this->data[3][2][1]='2';
		$this->data[3][2][2]='note 2';
		$this->data[3][2][3]='2';
		$this->data[3][3][1]='3';
		$this->data[3][3][2]='note 3';
		$this->data[3][3][3]='2';
		$this->data[3][4][1]='4';
		$this->data[3][4][2]='note 4';
		$this->data[3][4][3]='2';
		$this->data[3][5][1]='5';
		$this->data[3][5][2]='note 5';
		$this->data[3][5][3]='3';
		$this->save();
	}
	public function load_php($strTable)
	{
		$table = $this->get_id_table($strTable);
		//var_dump($table); exit;
		if($table == 0)
		{
			$msg = 'You tried to load a table that does not have a key table. Try to import the original table before loading a big file that is attached to it.';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
			exit;
		}
		if(file_exists($this->datapath.$strTable.'.php')) 
		{
			try
			{
				include($this->datapath.$strTable.'.php');
				sleep(1);
				$firstKey = array_key_first($data);
				if($firstKey !== $table)
				{
					$msg = "First keys : $firstKey of the table does not match the main key : $table for this table !";
					$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
					throw new Exception($msg);
					exit;
				}
				//$this->preprint($data); exit;
				//unset($this->data[$table]);
				$this->data[$table] = $data[$firstKey]; 
				//Cette fonction sert en cas que les indices d'un fichier loader ne commence pas a 1 mais a 10000 par exemple.
				//sleep(1);
				$this->repair_table($table);
				//$_SESSION['phpfile'] = $strTable.$index.'.php';
				$_SESSION['phpfile'] = $strTable;
				//$this->save();
			}
			catch (Throwable $t)
			{
				$msg = $t->getMessage();
				$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
				throw new Exception($msg);
			}
		} 
		else 
		{
			$msg = 'The file '.$this->datapath.$strTable.'.php does not exist';
			$msg = htmlentities($msg,ENT_COMPAT,"UTF-8");
			throw new Exception($msg);
		}
	}
	public function save_php($strTable,$append=FALSE)
	{
		unset($_SESSION['phpfile']);
		
		if (is_string($append) && ($append == "FALSE" || $append == "false"))
		{
			$append =FALSE;
		}
		
		if($append)
		{
			// Premièrement ouvrir le fichier existant pour enlever le "?" à la fin.
			$string = file_get_contents(DATADIRECTORY.$strTable.'.php');
			$string = str_replace("?>","",$string);
			// Sauvegarder le fichier sans la fin php.
			file_put_contents(DATADIRECTORY.$strTable.'.php',trim($string));
			
			$this->load_php($strTable);
			$table = $this->get_id_table($strTable);
			$data = $this->table($strTable);
			//$this->preprint($data); exit;
			$puts = '';
			if(isset($data))
			{
				$append_line = count($data);
				//echo $append_line; exit;
				foreach($data as $line=>$columns)
				{
					$append_line++; 
					foreach($columns as $column=>$value)
					{
						$puts .= PHP_EOL;
						$this->escape($value);
						//$value = utf8_encode($value); 
						$puts .= '$data['.$table.']['.$append_line.']['.$column.']='."'".$value."'".';';
					}
				}
			}
			$puts .= PHP_EOL;
			$puts .= '?>';
			file_put_contents(DATADIRECTORY.$strTable.'.php',$puts,FILE_APPEND | LOCK_EX);
		}
		else
		{
			$data = $this->table($strTable,TRUE);
			$table = $this->get_id_table($strTable);
			//$this->preprint($data); exit;
			$puts = '<?php';
			if(isset($data))
			{
				foreach($data as $line=>$columns)
				{
					foreach($columns as $column=>$value)
					{
						$puts .= PHP_EOL;
						$this->escape($value);
						//$value = utf8_encode($value); 
						$puts .= '$data['.$table.']['.$line.']['.$column.']='."'".$value."'".';';
					}
				}
			}
			$puts .= PHP_EOL;
			$puts .= '?>';
			
			file_put_contents(DATADIRECTORY.$strTable.'.php',$puts,LOCK_EX);
		}
		$_SESSION['phpfile'] = $strTable.'.php';
	}
	
	public function save_csv($strTable,$append=FALSE)
	{
		unset($_SESSION['csvfile']);
		
		if (is_string($append) && ($append == "FALSE" || $append == "false"))
		{
			$append =FALSE;
		}

		$data = $this->table($strTable,!$append);	
		$puts = '';
		if(isset($data))
		{
			foreach($data as $line=>$l)
			{
				foreach($l as $column=>$value)
				{
					$this->unescape($value);
					$res = strstr($value, ','); 
					if($res)
					{
						$value = '"'.$value.'"'; 
					}
					$puts .= $value.',';
				}
				$puts = rtrim($puts,',');
				$puts .= "\n";
			}
		}
		if($append)
		{
			file_put_contents(DATADIRECTORY.$strTable.'.csv',$puts,FILE_APPEND | LOCK_EX);
		}
		else
		{
			file_put_contents(DATADIRECTORY.$strTable.'.csv',$puts,LOCK_EX);	
		}
		$_SESSION['csvfile'] = $strTable.'.csv';
	}	
	
	public function load_csv($strTable)
	{
		$t = $this->get_id_table($strTable);
		$row = 0;
		if (($handle = fopen(DATADIRECTORY.$strTable.'.csv', "r")) !== FALSE) 
		{
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
			{
				$num = count($data);
				//echo "<p> $num fields in line $row: <br /></p>\n";
				
				for ($c=0; $c < $num; $c++)
				{
						if($data[$c] || $data[$c]== 0 || $data[$c]== "0")
						{
							$this->data[$t][$row][$c+1] = $data[$c];
						}
						else
						{
							$this->data[$t][$row][$c+1] = '';
						}
					//echo $data[$c] . "<br />\n";
				}
				$row++;
		  }
		  fclose($handle);
		  $this->save();
		}
	}
	
	public function load_json($strTable)
	{
		$json =  json_decode(file_get_contents( DATADIRECTORY.$strTable.'.json'),TRUE);
		$table= $this->get_id_table($strTable);
		$records =  $json[$strTable];
		$columns = $this->get_columns($table);
		foreach($records as $r=>$record)
		{
			$line = $r+1;
			foreach($columns as $id=>$name)
			{
				$column = $this->get_id_column($table,$name);
				$this->data[$table][$line][$column] = $record[$name];
			}
		}
		//$this->preprint($this->data[$table]); exit;
		$this->save();	
	}
	
	public function save_json($strTable,$append=FALSE)
	{
		unset($_SESSION['jsonfile']);
		
		if (is_string($append) && ($append == "FALSE" || $append == "false"))
		{
			$append =FALSE;
		}

		if($append)
		{
			$string = file_get_contents(DATADIRECTORY.$strTable.'.json');
			$string = str_replace("]}","",$string);
			file_put_contents(DATADIRECTORY.$strTable.'.json',trim($string).','."\n");
			$fields = $this->get_columns_of($strTable);
			$data = $this->table($strTable);	
			//$puts = '{ ';
			$puts = '';
			if(isset($data))
			{
				//$puts .= '"'.$strTable.'":';
				//$puts .= ',';
				$puts .= "\n";
				foreach($data as $line=>$l)
				{
					$puts .= '{ ';
					foreach($l as $column=>$value)
					{
						$this->unescape($value);
						$res = !(is_numeric($value)); 
						if($res)
						{
							$value = '"'.$value.'"'; 
						}
						$puts .= '"'.$fields[$column].'"'.':'.$value.',';
					}
					$puts = rtrim($puts,',');
					$puts .= ' },';
					$puts .= "\n";
				}
				$puts = rtrim(trim($puts),',');
				$puts .= "\n";
				$puts .= ']';
			}
			$puts .= '}';
			file_put_contents(DATADIRECTORY.$strTable.'.json',$puts,FILE_APPEND | LOCK_EX);
		}
		else
		{
			$fields = $this->get_columns_of($strTable);
			$data = $this->table($strTable);	
			$puts = '{ ';
			if(isset($data))
			{
				$puts .= '"'.$strTable.'":';
				$puts .= '[ ';
				$puts .= "\n";
				foreach($data as $line=>$l)
				{
					$puts .= '{ ';
					foreach($l as $column=>$value)
					{
						$this->unescape($value);
						$res = !(is_numeric($value)); 
						if($res)
						{
							$value = '"'.$value.'"'; 
						}
						$puts .= '"'.$fields[$column].'"'.':'.$value.',';
					}
					$puts = rtrim($puts,',');
					$puts .= ' },';
					$puts .= "\n";
				}
				$puts = rtrim(trim($puts),',');
				$puts .= "\n";
				$puts .= ']';
			}
			$puts .= '}';
			
			file_put_contents(DATADIRECTORY.$strTable.'.json',$puts,LOCK_EX);	
		}
		$_SESSION['jsonfile'] = $strTable.'.json';
	}
	
	public function save_js($strTable,$append=FALSE)
	{
		unset($_SESSION['jsfile']);
		
		if (is_string($append) && ($append == "FALSE" || $append == "false"))
		{
			$append =FALSE;
		}

		if($append)
		{
			$string = file_get_contents(DATADIRECTORY.$strTable.'.js');
			$string = str_replace("];","",$string);
			file_put_contents(DATADIRECTORY.$strTable.'.js',trim($string).','."\n");
			$fields = $this->get_columns_of($strTable);
			$data = $this->table($strTable);	
			//$puts = '{ ';
			$puts = '';
			if(isset($data))
			{
				//$puts .= '"'.$strTable.'":';
				//$puts .= ',';
				$puts .= "\n";
				foreach($data as $line=>$l)
				{
					$puts .= '{ ';
					foreach($l as $column=>$value)
					{
						$this->unescape($value);
						$res = !(is_numeric($value)); 
						if($res)
						{
							$value = '"'.$value.'"'; 
						}
						$puts .= '"'.$fields[$column].'"'.':'.$value.',';
					}
					$puts = rtrim($puts,',');
					$puts .= ' },';
					$puts .= "\n";
				}
				$puts = rtrim(trim($puts),',');
				$puts .= "\n";
				$puts .= ']';
			}
			$puts .= ';';
			file_put_contents(DATADIRECTORY.$strTable.'.js',$puts,FILE_APPEND | LOCK_EX);
		}
		else
		{
			$fields = $this->get_columns_of($strTable);
			$data = $this->table($strTable);	
			$puts = '';
			if(isset($data))
			{
				$puts .= 'const '.$strTable. '=' ;
				$puts .= '[ ';
				$puts .= "\n";
				foreach($data as $line=>$l)
				{
					$puts .= '{ ';
					foreach($l as $column=>$value)
					{
						$this->unescape($value);
						$res = !(is_numeric($value)); 
						if($res)
						{
							$value = '"'.$value.'"'; 
						}
						$puts .= '"'.$fields[$column].'"'.':'.$value.',';
					}
					$puts = rtrim($puts,',');
					$puts .= ' },';
					$puts .= "\n";
				}
				$puts = rtrim(trim($puts),',');
				$puts .= "\n";
				$puts .= ']';
			}
			$puts .= ';';

			file_put_contents(DATADIRECTORY.$strTable.'.js',$puts,LOCK_EX);	
		}
		$_SESSION['jsfile'] = $strTable.'.js';
	}
}
?>
