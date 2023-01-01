<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Portfolio
* @version:	6.7
* @author: pierre.martin@live.ca
* @php: 7.1.9
* @revision: 2022-12-20
* @licence MIT
*/
class Curriculum extends Controller
{
	function __construct()
	{
		parent::__construct('curriculum','php','curriculum');
				
		if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}
		// BANNER
		//$this->data['title'] = '<a href="'.strtolower(get_class($this)).'">'.$this->data['title'].' '.VERSION.'</a>';
		$this->data['title'] = '<a href="'.strtolower(get_class($this)).'">'.$this->data['title'].'</a>';
		$this->data['banner']= $this->Template->load('banner', $this->data,TRUE);
		// NAVIGATION
		$this->data['nav'] = $this->Template->load('nav',$this->data,TRUE);
		// MESSAGE
		$this->get_message();
		// GAUCHE
		$this->data['tables'] = $this->DB->get_tables();
		// LEFT
		$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
		// FOOTER
		$this->data['footer'] = $this->Template->load('footer', $this->data,TRUE);
		// CHECK SYSTEM
		try
		{
			$this->DB->check_system();
		}
		catch (Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}

		//EXPERIENCES
		$experiences = $this->DB->table('experiences');
		foreach($experiences as $e=>$exp)
		{
			$exp[2] = $entreprise = $this->DB->get_field_value_where_unique('entreprises','id_entreprise',$exp[2],'entreprise');
			$exp[3] = $ville = $this->DB->get_field_value_where_unique('villes','id_ville',$exp[3],'ville');
			$exp[4] = $poste = $this->DB->get_field_value_where_unique('postes','id_poste',$exp[4],'poste');
			$this->data['experiences'][$e] = $exp;
		}
		$this->data['experiences'] = $this->Template->load('experiences2',$this->data,TRUE);
		$this->DB->unescape($this->data['experiences']);
		//FORMATIONS
		$this->data['formations'] = $this->DB->table('formations');
		$this->data['formations'] = $this->Template->load('formations',$this->data,TRUE);
		//COMPETENCES
		$this->data['competences'] = $this->DB->table('competences');
		$this->data['competences'] = $this->Template->load('competences',$this->data,TRUE);
		$this->DB->unescape($this->data['competences']);
		//TUTOS
		$this->data['tutos'] = $this->DB->table('tutoriels');
		$this->data['tutos'] = $this->Template->load('tutoriels',$this->data,TRUE);
		$this->DB->unescape($this->data['tutos']);
		//REALISATIONS
		$this->data['realisations'] = $this->DB->table('realisations');
		$this->data['realisations'] = $this->Template->load('realisations',$this->data,TRUE);
		$this->DB->unescape($this->data['realisations']);
		//INTERETS
		$this->data['interets'] = $this->DB->table('interets');
		$this->data['interets'] = $this->Template->load('interets',$this->data,TRUE);
		$this->DB->unescape($this->data['tutos']);
		//LEFT
		$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
		//RIGHT
		$this->data['right'] = $this->Template->load('right',$this->data,TRUE);
	}
	function index()
	{
		//parent::index();
		// STATS
		$this->data['file'] = $this->DB->filename;
		$this->data['ffilesize'] = $this->DB->ffilesize;
		$this->data['numtables'] = $this->DB->count_tables();
		$this->data['maxlines'] = $this->DB->count_max_lines();
		$this->data['maxcols'] = $this->DB->count_max_columns();
		//ITEMS
		$this->data['items'] = $this->DB->table('items');
		$this->data['items'] = $this->Template->load('items',$this->data,TRUE);
		//ARTICLES
		$this->data['titres'] = $this->DB->table('titres');
		foreach($this->data['titres'] as $i=>$titre)
		{
			if($i > 3) continue;
			$this->data['titre'][$i] = $titre[2];
			$this->data['paragraphes'][$i] = $this->DB->get_where_multiple('paragraphes','titre_id','==',$titre[1]);
		}
		$this->data['paragraphes'] = $this->Template->load('paragraphes',$this->data,TRUE);
		//COMMENTAIRES
		try
		{
			$post = @$_POST;
			$this->DB->add_line($post,@$post['commentaire']);
			$this->Msg->set_msg('<span style="color:red">Votre commentaire a été ajouté.</span>');
			header('Location:'.WEBROOT.strtolower(get_class($this)));
		}
		catch(Throwable $t)
		{
			$this->Msg->set_msg($t->getMessage());
		}
		//FORM
		$this->data['legend'] = 'Envoyez un commentaire' ;
		$this->data['placeholder'] = '200 caractères max.';
		$this->data['path'] = strtolower(get_class($this));
		$this->data['columns'] = [1=>'id_commentaire',2=>'commentaire',3=>'pseudo',4=>'date'];
		$this->data['table'] = $this->DB->get_id_table('commentaires');
		$this->data['action'] = WEBROOT.strtolower(get_class($this));
		$this->data['id_commentaire'] = $this->DB->get_last_number('commentaires','id_commentaire');
		$this->data['form'] = $this->Template->load('ajout-com', $this->data,TRUE);
		// COMMENTAIRES
		$this->data['commentaires'] = $this->DB->select([1=>'id_commentaire',2=>'commentaire',3=>'pseudo',4=>'date'],'commentaires');
		$this->data['com'] = $this->Template->load('commentaires', $this->data,TRUE);
		//$this->data['content'] = $this->Template->load('details',$this->data,TRUE);
		// MAIN PAGE
		if($this->mobile())
		{
			$this->data['content'] = $this->Template->load('m_details',$this->data,TRUE);
			$this->data['left'] = $this->Template->load('m_left',$this->data,TRUE);
			$this->Template->load('m_layout',$this->data);
		}
		else
		{
			$this->data['content'] = $this->Template->load('details',$this->data,TRUE);
			$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
			$this->Template->load('layout',$this->data);
		}
	}
	function article($url)
	{
		//$_SESSION['jumbo']=FALSE;
		//ARTICLES
		$titre = $this->DB->get_record('titres',$url[TABLE]);
		$this->data['titre'][$url[TABLE]] = $titre['titre'];
		$this->data['id_titre'] = $titre['id_titre']; 
		$this->data['paragraphes'][$url[TABLE]] = $this->DB->get_where_multiple('paragraphes','titre_id','==',$titre['id_titre']);
		$this->data['paragraphes'] = $this->Template->load('paragraphes',$this->data,TRUE);
		// MAIN PAGE
		if($this->mobile())
		{
			$this->data['content'] = $this->Template->load('article',$this->data,TRUE);
			$this->data['left'] = $this->Template->load('m_left',$this->data,TRUE);
			$this->Template->load('m_layout',$this->data);
		}
		else
		{
			$this->data['content'] = $this->Template->load('article',$this->data,TRUE);
			$this->data['left'] = $this->Template->load('left',$this->data,TRUE);
			$this->Template->load('layout',$this->data);
		}
	}
	function add_table()
	{
		$this->denied('add a table');
	}	
	function edit_table($url)
	{
		$this->denied('edit a table');
	}
	function delete_table($url)
	{		
		$this->denied('delete table');
	}
	function empty_table($url)
	{		
		$this->denied('empty table');
	}
	function add_field($url)
	{
		$this->denied('add a field');
	}
	function edit_field($url)
	{
		$this->denied('edit a field');
	}
	function delete_field($url)
	{
		$this->denied('delete a field');
	}
	function add_record($url)
	{
		$this->denied('add a record');
	}
	function edit_record($url)
	{
		$this->denied('edit a record');
	}
	function delete_record($url)
	{
		$this->denied('delete a record');
	}
	function ini()
	{
		$this->DB->initialize();
		$this->Msg->set_msg('You have initialized Pierre Martin resume');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
	}
	function denied($string)
	{
		$this->Msg->set_msg('<span style="color:red">You don\'t have the right to '.$string.' in this module.</span>');
		header('Location:'.WEBROOT.strtolower(get_class($this)));
		exit();
	}
	function mobile()
	{
		$mobile_browser = '0';
		if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$mobile_browser++;
		}
		if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$mobile_browser++;
		}    
		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
		$mobile_agents = array(
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
			'wapr','webc','winw','winw','xda ','xda-');
		if (in_array($mobile_ua,$mobile_agents)) {
			$mobile_browser++;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows') > 0) {
			$mobile_browser = 0;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'mac') > 0) {
				$mobile_browser = 0;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'ios') > 0) {
				$mobile_browser = 1;
		}
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'android') > 0) {
				$mobile_browser = 1;
		}
		if($mobile_browser == 0)
		{
			//its not a mobile browser
			//echo"You are not a mobile browser";
			return 0;
		} else {
			//its a mobile browser
			//echo"You are a mobile browser!";
			return 1;
		}
	}
	//$this->DB->del_lines_where($strTable,'EFID','==','-','CarrierNumber');
}
?>