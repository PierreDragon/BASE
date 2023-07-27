<div style="page-break-before:always;">
<h3>Expériences</h3>
<div id="accordion">
<?php
foreach($experiences as $e=>$exp)
{
	echo '<h4>'.$exp[2].' '.$exp[3].'</h4>';	
	echo '<div>';
	echo '<strong>'.$exp[5].'</strong>';
	echo '<h6>'.$exp[4].'</h6>';
	echo $exp[6];
	echo '</div>';
}
?>
</div>
<div id="accord">
<?php
foreach($experiences as $e=>$exp)
{
	echo'<div style="page-break-inside:avoid;">';
	echo '<h4>'.$exp[2].', '.$exp[3].' <span>'.$exp[5].'</span></h4>';	
	echo '<h5>'.$exp[4].'</h5>';
	echo $exp[6];
	echo '<hr>';
	echo '</div>';
}
?>
</div>
</div>