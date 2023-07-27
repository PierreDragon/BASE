<h3>Commentaires</h3>
<?php
foreach($commentaires as $i=>$commentaire)
{
	if($i==0) continue;
	//$this->Get->unescape($commentaire);
	echo '<div>';
	echo '<em>'.$commentaire[4].'</em><blockquote>'.$commentaire[2].'<small>'.$commentaire[3].'</small></blockquote>';
	echo '</div>';
}
?>