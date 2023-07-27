<form name="frmAddAction" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
	<?php  
	foreach($columns as $id=>$colonne)
	{
		/*Fields of table: actions 1 id_action 2 action	3 strtable 4 strfield 5 totable	6 tofield 7 left 8 right 9 string 10 operator 11 value 12 unique */
		switch ($colonne) 
		{
			case 'id_action':
				echo '<input type="hidden" name="id_action" value="">';
			break;
			case 'action':
				echo $listactions;
			break;
			case 'strtable':
				echo $liststrtables;
			break;
			case 'strfield':
				echo $liststrfields;
			break;
			case 'totable':
				echo $listtotables;
			break;
			case 'tofield':
				echo $listtofields;
			break;
			case 'left':	
				echo $divleft;		
			break;
			case 'right':
				echo $divright;
			break;
			case 'string':
				echo $divstring;
			break;
			case 'operator':
				echo $listoperators;
			break;
			case 'value':
				echo $divvalue;
			break;
			case 'unique':
				echo $listuniques;
			break;
		}
	}
	echo '<input type="hidden" name="table" value="'.$table.'">';
	?>
	<button type="submit" class="btn btn-default">Add an action</button>
</form>