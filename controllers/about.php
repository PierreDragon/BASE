<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class About extends Core\Controller
{	
	function __construct()
	{
		parent::__construct('demo','php','about');	
		/*if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}*/
		$this->data['numtables'] = $this->DB->count_tables();
		$this->data['maxlines'] = $this->DB->count_max_lines();
		$this->data['maxcols'] = $this->DB->count_max_columns();
		
		$this->data['title'] = ' '.ucfirst(DEFAULTCONTROLLER).' ';
		//<HEAD>
		$this->data['head'] = $this->Template->load('head',$this->data,TRUE);
		// NAVIGATION
		$this->data['nav'] = $this->Template->load('nav',$this->data,TRUE); 
		//HEADER
		$this->data['header']= $this->Template->load('header', $this->data,TRUE);
		// FOOTER
		$this->data['footer'] = $this->Template->load('footer', $this->data,TRUE);
	}
	function index()
	{
		//parent::index();
		// ARTICLE
		$this->data['content']  = $this->Template->load('info', $this->data,TRUE);
		$this->data['maxcols'] = ($this->data['numtables'] > $this->data['maxcols'] )?$this->data['numtables']:$this->data['maxcols'];
	//	$this->data['content'] .= $this->oldtech($this->data['numtables'] ,$this->data['maxlines'] ,$this->data['maxcols'] );
		//.aside-1
		$this->data['reflectionl'] = $r =$this->reflection($this);
		$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
		//.aside-2
		$this->data['reflectionr'] = $this->reflection($this->DB);
		$this->data['right'] = $this->Template->load('right',$this->data,TRUE);

		// LAYOUT
		$this->Template->load('layout', $this->data);
	}
	function oldtech($x,$y,$z)
	{ 
			$username = (isset($_SESSION['username']))?$_SESSION['username']:'not logged';
			$html = '<h1>Actual database <small> :  '.$username.'.php</small></h1>';
			$html .='<p>You must be logged in. If you want to see the current demo database, please log in with user: <strong>demo</strong> pass: <strong>demo</strong> or your own database using your username and password . Go ahead modify the database, create tables and columns, data and come back to see your table.</p>';
			$html .='<small>note : Cells that contain coordinates do not contain any data, it is only for the display of this datafile as its maximum for all  index. </small>';
			$html .= '<h5>TABLE:<span class="badge"> '.$x.'</span>  LINE:<span class="badge"> '.$y.'</span>   COLUMNS: <span class="badge">'.$z.'</span></h5>';
			$html .= '<table width="100%"  border="1">';
			for($a=0;$a<=$x;$a++)
			{
				for($b=0;$b<=$y;$b++)
				{
					$html .= '<tr>';
						for($c=1;$c<=$z;$c++)
						{
							if($value = $this->DB->get_cell($a,$b,$c))
							{
								$html .= '<td title="'.$a.'.'.$b.'.'.$c.'"><strong>'.$value.'</strong></td>';
							}
							else
							{
								$html .= '<td>'.$a.'.'.$b.'.'.$c.'</td>';	
							}
						}
					$html .= '</tr>';
				}
			}
			$html .= '</table>';
			return $html;
	}
}
?>