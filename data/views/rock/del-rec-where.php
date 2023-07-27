<form name="frmDeleteWhere" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
			<?php  
			foreach($columns as $id=>$colonne)
			{
				switch ($colonne) 
				{
					case 'strfield':
						echo $liststrfields;
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
	<button type="submit" class="btn btn-default">Delete where</button>
</form>