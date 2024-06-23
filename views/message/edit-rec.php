<form name="frmEdit" action="<?php echo $action; ?>" method="post" >
	<legend><?php echo $legend ?></legend>
	<?php 
	foreach($columns as $id=>$colonne)
	{
		if(substr($colonne, -3, 1)=="_")
		{
			echo $tblList[$id];
		}
		elseif(strpos($colonne, 'date')!== false)
		{
			echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="date" value="'.$record[$id].'">';
			echo '</div>';
		}
		elseif(strpos($colonne, 'time')!== false)
		{
			echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="time" value="'.$record[$id].'">';
			echo '</div>';
		}
		else
		{
			echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			if(strlen(@$record[$id]) > 32)
			{
				echo '<textarea rows="10" class="form-control input-sm"  id="'.$colonne.'" name="'.$colonne.'"> '.$record[$id].'</textarea>';	
			}
			else
			{
				echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" value="'.$record[$id].'" type="text">';
			}
			echo '</div>';
		}
	}
	echo '<input type="hidden" name="table" value="'.$table.'">';
	echo '<input type="hidden" name="line" value="'.$line.'">';
	?>
	<button type="submit" class="btn btn-default">Save</button>
</form>