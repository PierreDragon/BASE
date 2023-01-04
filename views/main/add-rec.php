<form name="frmAjout" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
	<legend><?php echo $legend ?></legend>
		<?php  
		foreach($columns as $id=>$colonne)
		{
			if(substr($colonne, -3, 1)=="_")
			{
				echo $tblList[$id];
			}
			elseif(substr($colonne, 2, 1)=="_")
			{
				echo '<input type="hidden" name="'.$colonne.'" >';
			}
			elseif(strpos($colonne, 'date')!== false)
			{
				echo '<div class="form-group">';
				echo '<label for="'.$colonne.'">'.$colonne.'</label>';
				echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="date">';
				echo '</div>';
			}
			elseif(strpos($colonne, 'time')!== false)
			{
				echo '<div class="form-group">';
				echo '<label for="'.$colonne.'">'.$colonne.'</label>';
				echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="time">';
				echo '</div>';
			}
			elseif($colonne=='image')
			{
				echo '<div class="form-group">';
				echo '<label for="'.$colonne.'">'.$colonne.'</label>';
				echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="file">';
				echo '</div>';
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