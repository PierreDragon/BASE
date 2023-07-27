<?php 
	echo '<blockquote>'.$thead.'</blockquote>';
	echo '<input class="form-control" id="myInput" type="text" placeholder="Search..">';
	echo '<table id="tab" class="table table-striped">';
	echo '<thead>';
	echo '<tr>';
	foreach($columns as $id=>$col)
	{
		$width = ($col=='datetime')?'150px':'';
		echo '<th style="width:'.$width.'">';
		echo $col;
		echo'</th>';
	}
	echo '<th>edit</th>';
	echo '<th>delete</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody id="myTable" class="row_drag">';
	echo $tbody;
	echo '<tr id="exec"><th colspan="'.($nbrcolonne+2).'"><span>Execution time : '.number_format($performance,2).' sec.</span></th></tr>';
	echo '</tbody>';
	echo '</table>';
?>