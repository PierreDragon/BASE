<?php 
	echo '<blockquote id="thead">'.$thead.'</blockquote>';
	if($thead == 'actions')
	{
		$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'working copy';
		echo '<h2 id="acsc">Actions script  <small class="text-muted">'.$block.'</small> </h2>';
		echo '<h5><strong>TIP:</strong> To have a fully functional actions script. You must <a href="'.WEBROOT.$link.'/import_table">Import the table</a> 
		you want to work with before starting <a href="'.WEBROOT.$link.'/add_action">Adding an action</a>. You will want to <a href="'.WEBROOT.$link.'/ini">Initialize</a> the DB once your script will be complete.
		If you run out of space you can use decimals for id_ to sort. e.g: <em>2, 2.1, 3</em></h5>';
	}
	elseif($thead == 'rules')
	{
		echo '<h4>How to create a one to many relation between two tables.</h4>';
		echo '<h5><strong>1.</strong> Tablenames must be plural . <strong>2.</strong> Both tables must have unique keys begin with id_ with the name of the table but singular. <strong>3.</strong> In the slave table the key of the master table must be ending by _id. And make a rule to link it.</h5>';
	}
	//echo '<h3>'.ucfirst($thead).' <small>Secondary text</small></h3>';
	echo '<input class="form-control" id="myInput" type="text" placeholder="Search..">';
	echo '<div class="table-responsive">';
	echo '<table id="tab" class="table table-striped">';
	echo '<thead>';
	echo '<tr>';
	foreach($columns as $id=>$col)
	{
		echo '<th>';
		if($thead <> "actions" && $thead <> "rules")
		{
			echo '<a href="'.WEBROOT.$controller.'/show_table/'.$thead.'/'.$col.'" title="sort by '.$col.'" onclick="includeHTML()">'.$col.'</a>';
			echo '&nbsp;';
			echo '<a title="Edit a field" style="color:red; font-weight:normal; text-decoration:none;" href="'.WEBROOT.$controller.'/edit_field/'.$thead.'/'.$id.'"><em>edit</em></a>';
			echo '&nbsp;';
			echo '<a title="Delete a field"  style="color:red; font-weight:normal; text-decoration:none;"  href="'.WEBROOT.$controller.'/delete_field/'.$thead.'/'.$id.'"><em>delete</em></a>';
		}
		elseif($thead == "actions")
		{
			echo '<a href="'.WEBROOT.$controller.'/show_table/'.$thead.'/'.$col.'" title="sort by '.$col.'">'.$col.'</a>';
		}
		else
		{
			echo $col;
		}
		echo'</th>';
	}
	echo '<th></th>';
	echo '<th></th>';
	echo '</tr>';
	echo '</thead>';
	if($thead <> "actions" && $thead <> "rules")
	{
		echo '<tbody id="myTable">';
	}
	else
	{
		echo '<tbody id="myTable" class="row_drag">';
	}
	echo $tbody;
	echo '<tr id="exec"><th colspan="'.($nbrcolonne+2).'"><span>Execution time : '.number_format($performance,2).' sec.</span></th></tr>';
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
?>