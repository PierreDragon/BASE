<div class="panel panel-default">
	<div class="panel-body">
	Table : <strong><?=$thead?></strong><br> 
	Fields : <strong><?=$nbrcolonne?></strong><br>    
	Records : <strong><?=$nbrligne?></strong><br>
	<?php
	echo '<hr>';
	echo '<div><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></div>';
	//echo '<div><small><a href="'.WEBROOT.$controller.'/show_fields/'.$thead.'">Show fields</a></small></div>';
	?>
	</div>
</div>