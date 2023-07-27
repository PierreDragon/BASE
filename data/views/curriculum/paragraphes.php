<?php
foreach($paragraphes as $i=>$para)
{
	echo '<h4>'.html_entity_decode($titre[$i]).'</h4>';
	foreach($para as $p)
	{
		echo html_entity_decode($p[2]);
	}
	echo '<hr>';
}
?>