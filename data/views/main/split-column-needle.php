<form name="frmSplitColumn" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
			<?php  
			foreach($columns as $id=>$colonne)
			{
				switch ($colonne) 
				{
					case 'strfield':
						echo $liststrfields;
						if(isset($sample))
						{
							echo '<div class="form-group">';
							echo '<h5>Sample data of the chosen column  [ <span style="color:red;">'.$sample.' </span>]</h5>';
							echo '</div>';
						}
					break;
					case 'string':
						echo $divstring;
					break;
					case 'value':
						echo $divvalue;
					break;
				}
			}
			echo '<input type="hidden" name="table" value="'.$table.'">';
			?>
	<button type="submit" class="btn btn-default">Split it!</button>
</form>