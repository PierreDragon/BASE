<form name="frmSumColumnWhere" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
			<?php  
			foreach($columns as $id=>$colonne)
			{
				switch ($colonne) 
				{
					case 'strfield':
						echo $liststrfields;
					break;
					case 'left':
						echo $listmaths;
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
				}
			}
			echo '<input type="hidden" name="table" value="'.$table.'">';
			?>
	<button type="submit" class="btn btn-default">Math a column match condition !</button>
</form>