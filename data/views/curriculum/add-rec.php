<form name="frmAjout" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
			<?php  
			foreach($columns as $id=>$colonne)
			{
				if(substr($colonne, -3, 1)=="_")
				{
					echo $tblList[$id];
				}
				else
				{
					echo '<div class="form-group">';
					echo '<label for="'.$colonne.'">'.$colonne.'</label>';
					echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="text">';
					echo '</div>';
				}
			}
			echo '<input type="hidden" name="table" value="'.$table.'">';
			?>
	<button type="submit" class="btn btn-default">Add</button>
</form>