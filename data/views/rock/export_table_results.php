<?php
foreach($export_results as $result)
{
	$error = strpos($result, 'Error');
	$empty = strpos($result, 'empty');
	
	if ($error === false && $empty === false) 
	{
		$class = 'success';	
		$text = 'Success';
	}
	elseif($empty > 0)
	{
		$class = 'warning';	
		$text = 'Empty';
	}
	else
	{
		$class = 'danger';	
		$text = 'Error';
	}
    echo'<div class="alert alert-'.$class.'" role="alert">';
	echo'<strong>'.$text .'!</strong> <br>'. $result;
	echo'</div>';
}