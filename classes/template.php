<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Controller
* @version: 8.9
* @author: webiciel.ca
* @php: 7.4
* @revision: 2022-12-30 15:00
* @Ajouter la capture de fichier view inexistant 
* @licence MIT
*/
class Template 
{
	public function load($view, $data = array(), $return=FALSE)
	{
		if(!empty($data)) extract($data);
		$path =(isset($path))? $path.'/' : '';
		if(!file_exists(VIEWDIRECTORY.$path.$view.'.php'))
		{
			return  $view . ' is missing';
		}
		ob_start();
		require(VIEWDIRECTORY.$path.$view.'.php');
		$contents = ob_get_clean();
		
		if($return==FALSE)
		{
			echo $contents;
		}
		else
		{
			return $contents;
		}
	}
	public function makediv($colonne,$label=null,$help=null,$value=null)
	{
		$label = (isset($label)?$label:$colonne);
		$html  = '<div id="div'.$colonne.'"  class="form-group">';
		$html .= '<label id="lbl'.$colonne.'" for="'.$colonne.'">'.$label.'</label><span id="help'.$colonne.'">'.(isset($help)?$help:'').'</span>';
		$html .= '<input class="form-control input-sm" id="'.$colonne.'" name="'.$colonne.'" type="text" value="'.$value.'">';
		$html .= '</div>';
		return $html;
	}
	public function datalist($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL)
	{
		
		$cols = $db->get_columns_of($strTable);
		$rec = $db->select($cols,$strTable);
		
		$label = (isset($label)?$label:$selectName);
		$html  = '<div id="div'.$selectName.'" class="form-group">';
		$html .= '<label id="lbl'.$selectName.'" for="'.$selectName.'"> '.$label.' </label><span id="help'.$selectName.'">'.(isset($help)?$help:'').'</span>';
		$html .= '<input list="l'.$selectName.'" class="form-control input-sm" id="'.$selectName.'" name="'.$selectName.'" value="'.$value.'">';
		$html .= '<datalist id="l'.$selectName.'">';
		$str='';
		$selected='';
		if(!$header)
		{
			unset($rec[0]);
			$html .= '<option value=""></option>';
		}
		
		foreach($rec as $row)
		{
			for($c=$retcol;$c<=count($cols);$c++)
			{
				$str .= ' * '.$row[$c];
			}
			
			if($row[$retcol]===$value)
			{
				$selected = 'selected="selected"';
			}
	
			$html .= '<option value="'.$row[$retcol].'"' .$selected. '>';
			
			$str ='';
			$selected='';
		}
		$html .= '</datalist>';
		$html .= '</div>';
		return $html;
	}
	public function dropdown($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0)
	{
		$cols = $db->get_columns_of($strTable);
		$rec = $db->select($cols,$strTable);
		$label = (isset($label)?$label:$selectName);
		$html  = '<div id="div'.$selectName.'" class="form-group">';
		$html .= '<label id="lbl'.$selectName.'" for="'.$selectName.'"> '.$label.' </label><span id="help'.$selectName.'">'.(isset($help)?$help:'').'</span>';
		$html .= '<select class="form-control input-sm" id="'.$selectName.'" name="'.$selectName.'">';
		//$html .= '<option value=""></option>';
		$str='';
		$selected='';
		
		if(!$header)
		{
			unset($rec[0]);
			$html .= '<option value=""></option>';
		}
		
		foreach($rec as $row)
		{
			$limit = count($cols)-(int)$offset;
			for($c=$retcol;$c<=$limit;$c++)
			{
				$str .= ' * '.$row[$c];
			}
			
			if($row[$retcol]===$value)
			{
				$selected = 'selected="selected"';
			}
	
			$html .= '<option value="'.$row[$retcol].'"' .$selected. '>'.$str.'</option>';
			
			$str ='';
			$selected='';
		}
		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	
	public function dropdown_where($db,$strTable,$selectName,$retcol=1,$value=NULL,$header=FALSE,$label=NULL,$help=NULL,$offset=0,$op='==')
	{
		$cols = $db->get_columns_of($strTable);
		$rec = $db->select_where($cols,$strTable,$cols[1],$op,$value);
		if(is_null($rec))
		{
			return FALSE;
		}
		$label = (isset($label)?$label:$selectName);
		$html  = '<div id="div'.$selectName.'" class="form-group">';
		$html .= '<label id="lbl'.$selectName.'" for="'.$selectName.'"> '.$label.' </label><span id="help'.$selectName.'">'.(isset($help)?$help:'').'</span>';
		$html .= '<select class="form-control input-sm" id="'.$selectName.'" name="'.$selectName.'">';
		//$html .= '<option value=""></option>';
		$str='';
		$selected='';
		
		if(!$header)
		{
			unset($rec[0]);
			$html .= '<option value=""></option>';
		}
		
		foreach($rec as $row)
		{
			$limit = count($cols)-(int)$offset;
			for($c=$retcol;$c<=$limit;$c++)
			{
				$str .= ' * '.$row[$c];
			}
			
			if($row[$retcol]===$value)
			{
				$selected = 'selected="selected"';
			}
	
			$html .= '<option value="'.$row[$retcol].'"' .$selected. '>'.$str.'</option>';
			
			$str ='';
			$selected='';
		}
		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	public function cdropdown($db,$strTable,$selectName,$value=NULL,$header=FALSE,$label=NULL,$help=NULL)
	{
		$label = (isset($label)?$label:$selectName);
		$html  = '<div id="div'.$selectName.'" class="form-group">';
		$html .= '<label id="lbl'.$selectName.'" for="'.$selectName.'">'.$label.'</label><span id="help'.$selectName.'">'.(isset($help)?$help:'').'</span>';
		$html .= '<select class="form-control input-sm" id="'.$selectName.'" name="'.$selectName.'">';
		if(isset($strTable))
		{
			$cols = $db->get_columns_of($strTable);
			if($header)
			{
				$html .= '<option value="">-- select field --</option>';
			}
			else
			{
				$html .= '<option value=""></option>';
			}
			
			foreach($cols as $r=>$col)
			{
				$selected='';
				//if($r==0) continue;
				if($col===$value)
				{
					$selected = 'selected="selected"';
				}
				$html .= '<option value="'.$col.'"' .$selected. '>'.$col.'</option>';
			}
		}
		else
		{
			$html .= '<option value=""></option>';
			//$header = true;
			//$cols = [1=>''];
		}
		
		$html .= '</select>';
		$html .= '</div>';
		return $html;
	}
	public function cut_text($texte,$maxchars=50)
	{
		if( $this->str_word_count_utf8($texte)==1 and strlen($texte) > 34 )
		{
			$texte = substr($texte, 0, 34);
		}

		// Test si la longueur du texte dépasse la limite
		if (strlen($texte)>$maxchars)
		{
			// Séléction du maximum de caractères
			$texte = substr($texte, 0, $maxchars);
			// Récupération de la position du dernier espace (afin déviter de tronquer un mot)
			$position_espace = strrpos($texte, " ");
			$texte = substr($texte, 0, $position_espace);
			// Ajout des "..."
			$texte = $texte."...";
		}
		return $texte;
	}
	public function str_word_count_utf8($str)
	{
		return count(preg_split('~[^\p{L}\p{N}\']+~u',$str));
	}
	public function html_table($class,$records)
	{	
		if(isset($records))
		{
			$tbody = '<table class="'.$class.'">';
			foreach($records as $key=>$rec)
			{
				$tbody .= '<tr>';
				if($key == 0)
				{
					foreach($rec as $col=>$value)
					{
						$tbody .= '<th>'.$value.'</th>';
					}
				}
				else
				{
					foreach($rec as $col=>$value)
					{
						$tbody .= '<td>'.$value.'</td>';
					}
				}
				$tbody .= '</tr>';
			}
			$tbody .='</table>';
			return $tbody;
		}
	}
	public function escape(&$mixed)
	{
		// Remplace ' par &#039;, < par &lt; , > par &gt;
		if (is_array($mixed))
		{
			foreach($mixed as $key => $value)
			{
				$mixed[$key] = trim(preg_replace('/\s+/', ' ', $mixed[$key]));
				$mixed[$key] = preg_replace("/'/", "&#039;", $mixed[$key]);
				$mixed[$key] = preg_replace("/</", "&lt;", $mixed[$key]);
				$mixed[$key] = preg_replace("/>/", "&gt;", $mixed[$key]);
				$mixed[$key] = str_replace("\\", "&#092;", $mixed[$key]);
				$mixed[$key] = str_replace("/", "&#047;", $mixed[$key]);
				//$mixed[$key] = str_replace("à", "&agrave;", $mixed[$key]);
			}
		}
		else
		{
			$mixed = trim(preg_replace('/\s+/', ' ', $mixed));
			$mixed = preg_replace("/'/", "&#039;", $mixed);
			$mixed = preg_replace("/</", "&lt;", $mixed);
			$mixed = preg_replace("/>/", "&gt;", $mixed);
			$mixed = str_replace("\\", "&#092;", $mixed);
			$mixed = str_replace("/", "&#047;", $mixed);
			//$mixed = str_replace("à", "&agrave;", $mixed);
		}
	}
	/**
	 *	Contraire de escape
	 *
	 *	@param 	mixed &$mixed	Une chaîne de caractère ou un tableau
	 *							passage par référence, pas besoin de réaffecter $mixed
	 *	@return	void			
	 */
	public function unescape(&$mixed)
	{	
		if (is_array($mixed))
		{
			foreach($mixed as $key => $value)
			{
				$mixed[$key] = preg_replace("/&#039;/", "'", $mixed[$key]);
				$mixed[$key] = preg_replace("/&lt;/", "<", $mixed[$key]);
				$mixed[$key] = preg_replace("/&gt;/", ">", $mixed[$key]);
				$mixed[$key] = str_replace("&#092;", "\\", $mixed[$key]);
				$mixed[$key] = str_replace("&#047;","/", $mixed[$key]);
				//$mixed[$key] = str_replace("&agrave;", "&agrave;", $mixed[$key]);
			}
		}
		else
		{
			$mixed = preg_replace("/&#039;/", "'",$mixed);
			$mixed = preg_replace("/&lt;/", "<", $mixed);
			$mixed = preg_replace("/&gt;/", ">", $mixed);
			$mixed = str_replace("&#092;", "\\", $mixed);
			$mixed = str_replace("&#047;","/", $mixed);
			//$mixed = str_replace("&agrave;", "&agrave;", $mixed);
		}
	}
} 
/* Location: ./classes/template.php */

?>
