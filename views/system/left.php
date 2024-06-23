<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-title">Tables</div> 
		<a href="<?php echo WEBROOT.$link;?>/ini" title="Initialize">Initialize</a> | 
		<a href="<?php echo WEBROOT.$link;?>/add_table">Add a table</a>
	</div>
	<div class="panel-body">
		<?php 
		if(isset($tables))
		{
			echo '<ul class="list-group">';
			foreach($tables as $i=>$table)
			{
				echo '<li class="list-group-item"><a href="'.WEBROOT.$link.'/show_table/'.$table.'" onclick="includeHTML()">'.$i.'. '.$table.'</a></li>';
			}
			echo '</ul>';
		}
		?>
	</div>
</div>