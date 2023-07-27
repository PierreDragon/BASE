<h4>Expériences</h4>
<?php
foreach($experiences as $e=>$exp)
{
	echo '<table style="font-size:10pt" class="table table-striped">';
	//echo '<tr>';
	foreach($exp as $col=>$value)
	{
		if($col==1 OR $col==3) continue;
		switch($col)
		{
			case 2:
				$width='33%';
				echo '<tr><td width="'.$width.'">'.$value.'</td>';	
			break;
			case 4:
				$width='34%';
				echo '<td width="'.$width.'" style="text-align:center">'.$value.'</td>';	
			break;
			case 5:
				$width='33%';
				echo '<td width="'.$width.'" style="text-align:right">'.$value.'</td></tr>';	
			break;
			case 6:
				$width='100%';
				echo '<tr><td colspan="3" width="'.$width.'">'.$value.'</td></tr>';	
			break;
		}
		//echo '<td width="'.$width.'">'.$value.'</td>';	
	}
	//echo '</tr>';
	echo '</table>';
}
?>