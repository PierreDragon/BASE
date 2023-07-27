<form name="frmMoveOneToMany" action="<?php echo $action; ?>" method="post">
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
	<button type="submit" class="btn btn-default">Move one to many column !</button>
</form>
<br>
<h4>TIP : <em>testkey</em> can be named differently from one table to another.</h4>
<img src="<?=ASSETDIRECTORY?>/img/onetomany.png">