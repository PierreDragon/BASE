<form name="frmMergeRows" action="<?php echo $action; ?>" method="post">
	<legend><?php echo $legend ?></legend>
			<?php  
			foreach($columns as $id=>$colonne)
			{
				switch ($colonne) 
				{
					case 'strfield':
						echo $liststrfields;
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
	<button type="submit" class="btn btn-default">Merge rows !</button>
</form>
<hr>
<h5>TIP : To accomplish this task, you need a commun keys for many rows, a column dedicate to the number of a line and a column to receive the concated text 
from all matching rows into one single cell of the first row.</h5>
<img src="<?=ASSETDIRECTORY?>/img/mergerows.png">