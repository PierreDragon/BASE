<?php
$i=1;
$count = count($realisations);
foreach($realisations as $r=>$reali)
{
	echo '<h6><strong>'.$reali[2].'</strong></h6>';
	echo '<p>'.$reali[3].'</p>';
	echo '<span style="font-family:Courier New;font-size:9pt;font-weight:bold;">'.$reali[4].'</span>';
	if($i < $count)
	{
		echo '<hr>';	
		$i++;
	}
}
?>