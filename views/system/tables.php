<?php 
	echo '<blockquote id="thead">'.$thead.'</blockquote>';

	if($thead == 'rules')
	{
		echo '<h4>How to create a one to many relation between two tables.</h4>';
		echo '<h5><strong>1.</strong> Tablenames must be plural . 
		<strong>2.</strong> Both tables must have unique keys begin with id_ with the name of the table but singular.
		<strong>3.</strong> In the slave table the key of the master table must be ending by _id. And make a rule to link it.
		<strong>4.</strong> A deletion in the master table will automatically delete all matching records in the slave table.
		</h5>';
	}

	echo '<input class="form-control" id="myInput" type="text" placeholder="Search..">';
	echo '<div class="pagination">';
	echo $pagination;
	echo  '</div>';
	echo '<div class="table-responsive">';
	echo '<table id="tab" class="table table-striped">';
	echo '<thead>';
	echo '<tr>';
	foreach($columns as $id=>$col)
	{
		echo '<th>';
		if($thead <> "rules")
		{
			echo '<a href="'.WEBROOT.$controller.'/show_table/'.$thead.'/'.$col.'" title="sort by '.$col.'" onclick="includeHTML()">'.$col.'</a>';
			echo '&nbsp;';
			echo '<a title="Edit a field" style="color:red; font-weight:normal; text-decoration:none;" href="'.WEBROOT.$controller.'/edit_field/'.$thead.'/'.$id.'"><em>edit</em></a>';
			echo '&nbsp;';
			echo '<a title="Delete a field"  style="color:red; font-weight:normal; text-decoration:none;"  href="'.WEBROOT.$controller.'/delete_field/'.$thead.'/'.$id.'"><em>delete</em></a>';
		}
		else
		{
			echo $col;
		}
		echo'</th>';
	}
	echo '<th class="right">show</th>';
	echo '<th class="right">edit</th>';
	echo '<th class="right">delete</th>';
	echo '</tr>';
	echo '</thead>';
	if($thead == "rules")
	{
		echo '<tbody id="myTable">';
	}
	else
	{
		echo '<tbody id="myTable" class="row_drag">';
	}
	echo $tbody;
	echo '<tr id="exec"><th>Records: '.$nombre.'</th><th style="text-align:right" colspan="'.($nbrcolonne+2).'"><span>Execution time: '.number_format($performance,2).' sec.</span></th></tr>';
	echo '</tbody>';
	echo '</table>';
	echo '<div style="text-align:right"><a href="'.WEBROOT.$controller.'/add_record/'.$thead.' " class="btn btn-primary btn-sm" role="button">Ajoutez à la table '.$thead.'</a></div>';
	echo '<div class="pagination">';
	echo $pagination;
	echo  '</div>';
	echo '</div>';
?>