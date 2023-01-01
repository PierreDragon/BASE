<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-title">Tables</div>
	</div>
	<div class="panel-body">
		<?php 
		if(isset($tables))
		{
		echo '<ul class="list-group">';
		foreach($tables as $table)
		{
			echo '<li class="list-group-item"><a href="'.WEBROOT.$link.'/show_table/'.$table.'" onclick="includeHTML()">'.$table.'</a></li>';
		}
		echo '</ul>';
		}
		?>
	</div>
</div>
