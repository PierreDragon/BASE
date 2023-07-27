<form name="frmCopyColumnKeys" action="<?php echo $action; ?>" method="post">
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
	<button type="submit" class="btn btn-default">Copy column match condition !</button>
</form>
<hr>
<h5>TIP : This example is when you using <strong>add action</strong>.
 It is a little different from this current original form ^ because fields from table actions are filled dynamically but
 they have static positions. It could be optimized in the future.</h5>
<img src="<?=ASSETDIRECTORY?>/img/copycolumnkeys.png">