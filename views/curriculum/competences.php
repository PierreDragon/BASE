<?php
$i=1;
$count = count($competences);
foreach($competences as $c=>$compe)
{
	echo '<h4><strong>'.$compe[2].'</strong></h4>';
	echo '<p>'.$compe[3].'</p>';
	if($i < $count)
	{
		echo '<hr>';	
		$i++;
	}
}
?>