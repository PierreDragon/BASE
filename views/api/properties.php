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
	$cols = [2=>'script',3=>'urlaction',4=>'level'];
	$menu = $sys->select($cols,'scripts');
	echo '<hr>';
	switch($thead)
	{
		case 'rules':
			echo '<div><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record</a></div>';
			echo '<div><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></div>';
		break;
		default:
			echo '<div><a href="'.WEBROOT.$controller.'/add_record/'.$thead.'">Add a record</a></div>';
			echo '<div><a href="'.WEBROOT.$controller.'/show_fields/'.$thead.'">Show fields</a></div>';
			echo '<hr>';
			foreach($menu as $m=>$script)
			{
				if($m==0 OR $script[3]=='import_table' OR $script[3]=='add_table' OR $script[3]=='edit_field' OR $script[3]=='delete_field' OR $script[3]=='show_field'  OR $script[3]=='load_last_bkp' OR $script[3]=='bkp' OR $script[4] < $_SESSION['level'] 
				//OR $script[3]=='load_php'	//OR $script[3]=='export_to_mysql'	//OR $script[3]=='save_as_php' 
				) 
				continue;
				echo '<div><a href="'.WEBROOT.$controller.'/'.$script[3].'/'.$thead.'">'.ucfirst($script[2]).'</a></div>';	
			}
	}
	?>
	</div>
</div>