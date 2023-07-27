<form name="frmCopyDataKeys" action="<?php echo $action; ?>" method="post">
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
				}
			}
			echo '<input type="hidden" name="table" value="'.$table.'">';
			?>
	<button type="submit" class="btn btn-default">Copy data match keys !</button>
</form>
<hr>
<h5>Example:</h5>
<img src="<?=ASSETDIRECTORY?>/img/copydatakeys.png">