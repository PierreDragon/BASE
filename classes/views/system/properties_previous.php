<div class="panel panel-default">
	<div class="panel-body">
	Table   : <strong><a href="<?php echo WEBROOT.$controller.'/show_table/'.$thead?>" onclick="includeHTML()"><?=$thead?></a></strong><br> 
	Fields  : <span class="badge"><?=$nbrcolonne?></span><br>    
	Records : <span class="badge"><?=$nbrligne?></span><br>
	<?php
	$cols = [2=>'script',3=>'urlaction'];
	$menu = $sys->select($cols,'scripts');
	/*echo '<pre>';
	var_dump($menu); exit;
	echo '</pre>';*/
	echo '<hr>';
	switch($thead)
	{
	case 'actions':
		echo '<div><a href="'.WEBROOT.$controller.'/add_action">Add a action</a></div>';
	break;
	default:
		echo '<div><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record</a></div>';
	}
	echo '<hr>';
	echo '<div><a href="'.WEBROOT.$controller.'/add_field/'.$thead.'">Add a field</a></div>';
	echo '<div><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></div>';
	if($thead=='blocks')
	{
		echo '<div><a href="'.WEBROOT.$controller.'/renumber_column/'.$thead.'">Renumber a column</a></div>';
	}
	?>
	</div>
</div>