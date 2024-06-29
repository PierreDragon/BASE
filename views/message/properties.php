<div class="panel panel-default">
	<div class="panel-body">
	Id.table : <strong><?=$id?></strong><br>
	Table    : <strong><a href="<?php echo WEBROOT.$controller.'/show_table/'.$thead?>" onclick="includeHTML()"><?=$thead?></a></strong><br> 
	Fields   : <span class="badge"><?=$nbrcolonne?></span><br>    
	Records  : <span class="badge"><?=$nbrligne?></span><br>
	Show Limit per page : <span class="badge"><?=$showlimit?></span><br>
	Offset : <span class="badge"><?=$offset?></span><br>
	<?php if(isset($_SESSION['phpfile'])): $link = strstr($_SESSION['phpfile'],'.',TRUE)?>
	PHP data file : <strong><a href="<?php echo WEBROOT.$controller.'/get_php_file/'.$link ?>" target="_blank"><?=$_SESSION['phpfile']?></a></strong><br>
	<?php endif; ?>
	<?php if(isset($_SESSION['csvfile'])): ?>
	CSV data file : <strong><a href="<?php echo WEBROOT.'data/'.$_SESSION['csvfile'];?>" target="_blank"><?=$_SESSION['csvfile']?></a></strong><br>
	<?php endif; ?>
	<?php if(isset($_SESSION['jsonfile'])): ?>
	JSON data file :  <strong><a href="<?php echo WEBROOT.'data/'.$_SESSION['jsonfile'];?>" target="_blank"><?=$_SESSION['jsonfile']?></a></strong><br>
	<?php endif; ?>
	<?php if(isset($_SESSION['jsfile'])): ?>
	JS data file :  <strong><a href="<?php echo WEBROOT.'data/'.$_SESSION['jsfile'];?>" target="_blank"><?=$_SESSION['jsfile']?></a></strong><br>
	<?php endif; ?>
	
	<?php
	$cols = [2=>'script',3=>'urlaction'];
	$menu = $sys->select($cols,'scripts');

	//echo '<hr>';
	switch($thead)
	{
		case 'rules':
			echo '<div><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record</a></div>';
			echo '<div><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></div>';
		break;
		default:
			//echo '<a href=" '.WEBROOT.$link.'/bkp">Make a back-up</a>';
			//echo '<div><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record</a></div>';
			//echo '<div><a href="'.WEBROOT.$controller.'/show_fields/'.$thead.'">Show fields</a></div>';
			echo '<hr>';
			echo '<div><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></div>';
			foreach($menu as $m=>$desc)
			{
				if($m==0 OR $desc[3]=='import_table' 
				OR $desc[3]=='add_table' 
				OR $desc[3]=='edit_field' 
				OR $desc[3]=='delete_field' 
				//OR $desc[3]=='load_php'
				//OR $desc[3]=='export_to_mysql'
				//OR $desc[3]=='save_as_php'
				) 
				continue;
				//echo '<div><a href="'.WEBROOT.$controller.'/'.$desc[3].'/'.$thead.'">'.ucfirst($desc[2]).'</a></div>';	
			}
	}

	/* exemple : echo '<div><small><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></small></div>';*/
	?>
	
	</div>
</div>