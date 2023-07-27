<div class="panel panel-default">
	<div class="panel-body">
	Table   : <strong><a href="<?php echo WEBROOT.$controller.'/show_table/'.$thead?>" onclick="includeHTML()"><?=$thead?></a></strong><br> 
	Fields  : <span class="badge"><?=$nbrcolonne?></span><br>    
	Records : <span class="badge"><?=$nbrligne?></span><br>
	Show Limit : <span class="badge"><?=SHOWLIMIT?></span><br>
	Load Limit : <span class="badge"><?=LOADLIMIT?></span><br>
	<?php if(isset($_SESSION['sbigfile'])): ?>
	PHP data file : <span class="badge"><?=$_SESSION['sbigfile']?></span>
	<?php endif; ?>
	</form>
	<hr>
	<?php if(isset($listbigfiles)) : ?>
	<form name="frmLoadBigData" action="<?php echo WEBROOT.$controller;?>/load_post_data" method="post">
	<legend><?php echo 'Load php data file' ?></legend>
	<?php  
		echo '<input type="hidden" name="table" value="'.$thead.'">';
		echo $listbigfiles;
	?>
	<button onclick="includeHTML()" type="submit" class="btn btn-default">Load</button>
	</form>
	<?php endif; ?>
	
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
	echo '<div><a href="'.WEBROOT.$controller.'/show_fields/'.$thead.'">Show fields</a></div>';
	echo '<hr>';
	foreach($menu as $m=>$desc)
	{
		if($m==0 OR $desc[3]=='import_table' 
		OR $desc[3]=='add_table' 
		OR $desc[3]=='edit_field' 
		OR $desc[3]=='delete_field' 
		OR $desc[3]=='load_big_data'
		//OR $desc[3]=='export_to_mysql'
		//OR $desc[3]=='save_big_data'
		) 
		continue;
		echo '<div><a href="'.WEBROOT.$controller.'/'.$desc[3].'/'.$thead.'">'.ucfirst($desc[2]).'</a></div>';	
	}
	
	/*echo '<div><small><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/edit_table/'.$thead.'">Rename the current table</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/delete_table/'.$thead.'">Delete the current table</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/copy_table/'.$thead.'">Duplicate the current table</a></small></div>';
	//echo '<div><small><a href="'.WEBROOT.$controller.'/export/'.$thead.'">Export the current table to mysql</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/add_field/'.$thead.'">Add a field</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/show_fields/'.$thead.'">Show fields</a></small></div>';
	switch($thead)
	{
	case 'actions':
		echo '<div><small><a href="'.WEBROOT.$controller.'/add_action">Add a action</a></small></div>';
	break;
	default:
		echo '<div><small><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record</a></small></div>';
	}
	echo '<div><small><a href="'.WEBROOT.$controller.'/delete_where/'.$thead.'">Delete records selection</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/split_column/'.$thead.'">Split a column</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/copy_column/'.$thead.'">Duplicate a column</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/renumber_column/'.$thead.'">Renumber a column</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/concat_columns/'.$thead.'">Concat columns</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/match_column/'.$thead.'">Column reassignment</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/find_replace/'.$thead.'">Find and replace</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/move_one_to_many/'.$thead.'">Move a column match one to many</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/copy_column_keys/'.$thead.'">Copy column match keys</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/move_column/'.$thead.'">Move a column to another table</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/date_corrector/'.$thead.'">Date corrector</a></small></div>';
	//echo '<div><small><a href="'.WEBROOT.$controller.'/transfert/'.$thead.'/yes" target="_blank">Transfert the current table to drdata(php)</a></small></div>';
	echo '<div><small><a href="'.WEBROOT.$controller.'/transfert/'.$thead.'/">Transfert the current table to drdata(php)</a></small></div>';
	//echo '<div><small><a class="image-link" href="'.ASSETDIRECTORY.'/img/matches.png">How to remake foreigh keys value ?</a></small></div>';
	//echo '<img src="'.ASSETDIRECTORY.'/img/matches.png">';*/
	?>
	</div>
</div>