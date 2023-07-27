<?php 
	echo '<blockquote>Fields of : '.$thead.'</blockquote>';
	echo '<ol>';
	foreach($columns as $id=>$col)
	{
		
		echo '<script>
		$(document).ready(function(){
		$("#coltoedit'.$id.'").editable("'.WEBROOT.'main/set_cell/'.$idtable.'/0/'.$id.'",{name: \'value\'});
		});
		</script>';
		
		echo '<li>';
		echo '<span id="coltoedit'.$id.'" style="text-decoration:underline">'.$col.'</span>';
		echo '&nbsp;';
		echo '<a title="Delete a field"  style="color:red; font-weight:normal; text-decoration:none;"  href="'.WEBROOT.$controller.'/delete_field/'.$thead.'/'.$id.'/fx"><em>delete</em></a>';
		echo'</li>';
	}
	echo '</ol>';
	//echo '</div>';
	
		//var_dump($this->data['columns']);
		/*$html = '<span>Fields of table: <strong>'.$url[TABLE].'</strong></span>';
		$html .= '<ol>';
		foreach($fields as $i=>$field)
		{
			$html.='<li>'.$field.'</li>';
		}
		$html .= '</ol>';
		$this->data['content']=$html;*/
?>