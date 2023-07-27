<div style="padding-bottom:20px;" class="text-center">
	<form action="<?php echo WEBROOT; ?>main/execute_actions" method="post" >					
	<button  onclick="includeHTML()"  type="submit" class="btn btn-primary btn-lg">Execute Current Actions Script</button>
	</form>
</div>
<div class="panel panel-default">
	<div class="panel-body">
		Table   : <strong><a href="<?php echo WEBROOT.$controller.'/show_table/'.$thead?>" onclick="includeHTML()"><?=$thead?></a></strong><br> 
		Fields  : <span class="badge"><?=$nbrcolonne?></span><br>    
		Records : <span class="badge"><?=$nbrligne?></span><br>
		<?php
		echo '<hr>';
		echo '<div><a href="'.WEBROOT.$controller.'/add_action">Add a action</a></div>';
		echo '<div><a href="'.WEBROOT.$controller.'/empty_table/'.$thead.'">Empty the current table</a></div>';
		echo '<div><a href="'.WEBROOT.$controller.'/renumber_column/'.$thead.'">Renumber a column</a></div>';
		echo '<hr>';
		?>
			<form name="frmSaveScript" action="<?php echo WEBROOT.$controller;?>/save_script" method="post">
					<legend><?php echo 'Save actions script block' ?></legend>
			<?php  
				$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'';
				echo '<div class="form-group">';
				echo '<label for="nblock">name</label>';
				echo '<input class="form-control input-sm" id="nblock" name="nblock" type="text" value="'.$block.'">';
				echo '</div>';
			?>
			<button type="submit" class="btn btn-default">Save</button>
			</form>
		<hr>
			<form name="frmLoadScript" action="<?php echo WEBROOT.$controller;?>/load_script" method="post">
			<legend><?php echo 'Load actions script block' ?></legend>
			<?php  
				echo $listblocks;
			?>
			<button type="submit" class="btn btn-default">Load</button>
			</form>
	</div>
</div>
<div style="padding-bottom:20px;" class="text-center">
	<form action="<?php echo WEBROOT; ?>main/execute_all_actions" method="post" >					
	<button  onclick="includeHTML()"  type="submit" class="btn btn-primary btn-lg">Execute All Scripts Blocks</button>
	</form>
</div>