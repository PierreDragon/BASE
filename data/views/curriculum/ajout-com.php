<form action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
	<?php  
	echo '<div class="form-group">';
	foreach($columns as $colonne)
	{
		switch($colonne)
		{
			case 'id_commentaire':
				echo '<input type="hidden" name="id_commentaire" value="'.++$id_commentaire.'">';
			break;
			case 'commentaire':
				echo '<input type="text" class="form-control" name="commentaire" placeholder="commentaire" value="">'.'<br>';
			break;
			case 'pseudo':
				echo '<input type="text" class="form-control" name="pseudo" placeholder="pseudo" value="">';
			break;
			case 'date':
				echo '<input type="hidden" name="date" value="'.date("Y-m-d H:i:s").'">';
			break;
			/*case 'user_id';
				echo '<input type="hidden" name="user_id" value="'.$user_id.'">';
			break;*/
			
		}
		//echo '<input type="text" class="form-control" name="'.$colonne.'" placeholder="'.$colonne.'">';
	}
	echo '</div>';
	echo '<input type="hidden" name="table" value="'.$table.'">';
	?>
	<button type="submit" class="btn btn-default">Envoyer</button>
</form>