<form name="frmMatchColumn" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
			<?php  
			foreach($columns as $id=>$colonne)
			{
				switch ($colonne) 
				{
					case 'strfield':
						echo $liststrfields;
					break;
					case 'totable':
						echo $listtotables;
					break;
					case 'tofield':
						echo $listtofields;
					break;
					case 'unique':
						echo $listuniques;
					break;
				}
			}
			echo '<input type="hidden" name="table" value="'.$table.'">';
			?>
	<button type="submit" class="btn btn-default">Match it !</button>
</form>