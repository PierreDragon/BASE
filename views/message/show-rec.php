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
			echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="date" value="'.$record[$id].'" disabled>';
			echo '</div>';
		}
		elseif(strpos($colonne, 'time')!== false)
		{
			echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="time" value="'.$record[$id].'">';
			echo '</div>';
		}
		elseif($colonne ==  'id_musique')
		{
			echo '<input type="hidden" value=" '.$record[$id].' " />';
			//echo '<div class="form-group">';
			//echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			//echo '<audio controls autoplay><source src="'.ASSETDIRECTORY.'/uploads/'.$record[$id].' " type="audio/mpeg"> Votre navigateur ne supporte pas l\'élément audio.</audio>';
			//echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="time" value="'.$record[$id].'">';
			//echo '<p><audio controls><source src="'.ASSETDIRECTORY.'/uploads/'.$record[$id].' " type="audio/mpeg"> Votre navigateur ne supporte pas l\'élément audio.</audio></p>';
			//echo '</div>';
		}
		elseif($colonne ==  'musique')
		{
			//echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			//echo '<audio controls autoplay><source src="'.ASSETDIRECTORY.'/uploads/'.$record[$id].' " type="audio/mpeg"> Votre navigateur ne supporte pas l\'élément audio.</audio>';
			//echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="time" value="'.$record[$id].'">';
			echo '<p><audio controls><source src="'.ASSETDIRECTORY.'/uploads/'.$record[$id].' " type="audio/mpeg"> Votre navigateur ne supporte pas l\'élément audio.</audio></p>';
			//echo '</div>';
		}
		elseif($colonne ==  'alt')
		{
			echo '<h3>'.$record[$id].'</h3>';
		}
		elseif($colonne ==  'auteur')
		{
			echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			echo '<input id="auteur" name="auteur"  class="form-control input-sm" readonly  value=" '.$record[$id].' " />';
			echo '</div>';
		}
		elseif($colonne ==  'id_image')
		{
			echo '<input type="hidden" value=" '.$record[$id].' " />';
		}
		elseif($colonne ==  'image')
		{
			echo '<img src="'.ASSETDIRECTORY.'/uploads/'.$record[$id].' " alt="'.$record[$id].'" onclick="$(this).toggleClass(\'minresize\')">';
			//echo '<input readonly  value=" '.$record[$id].' " />';
		}
		else
		{
			echo '<div class="form-group">';
			echo '<label for="'.$colonne.'">'.$colonne.'</label>';
			if(strlen(@$record[$id]) > 32)
			{
				echo '<textarea readonly  rows="10" class="form-control input-sm"  id="'.$colonne.'" name="'.$colonne.'"> '.$record[$id].'</textarea>';	
			}
			else
			{
				//echo '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" value="'.$record[$id].'" type="text">';
				echo '<p class="small" id="'.$colonne.'" name="'.$colonne.'">'.$record[$id].'</p>';
			}
			echo '</div>';
		}
	}
	echo '<input type="hidden" name="table" value="'.$table.'">';
	echo '<input type="hidden" name="line" value="'.$line.'">';
	?>
</form>