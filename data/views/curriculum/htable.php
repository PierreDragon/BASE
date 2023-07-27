<?php 
	echo '<blockquote id="thead">'.$thead.'</blockquote>';
	if($thead == 'actions')
	{
		$block = isset($_SESSION['sblock'])?$_SESSION['sblock']:'working copy';
		echo '<h2 id="acsc">Actions script  <small class="text-muted">'.$block.'</small> </h2>';
		echo '<h5><strong>TIP:</strong> To have a fully functional actions script. You must <a href="'.WEBROOT.$link.'/import_table">Import the table</a> 
		you want to work with before starting <a href="'.WEBROOT.$link.'/add_action">Adding an action</a>. You will want to <a href="'.WEBROOT.$link.'/ini">Initialize</a> the DB Convert once your script will be complete.
		If you run out of space you can use decimals for id_ to sort. e.g: <em>2, 2.1, 3</em></h5>';
	}
	elseif($thead == 'rules')
	{
		echo '<h4>How to create a one to many relation between two tables.</h4>';
		echo '<h5><strong>1.</strong> Tablenames must be plural . <strong>2.</strong> Both tables must have unique keys begin with id_ with the name of the table but singular. <strong>3.</strong> In the slave table the key of the master table must be ending by _id. And make a rule to link it.</h5>';
	}
	//echo '<h3>'.ucfirst($thead).' <small>Secondary text</small></h3>';
	echo '<input class="form-control" id="myInput" type="text" placeholder="Search..">';
?>