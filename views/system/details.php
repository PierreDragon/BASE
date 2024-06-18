<blockquote><strong>Version</strong> <?=VERSION?></blockquote>
<div class="panel panel-default">
  <div class="panel-heading">
	  <h6>Main <span class="badge"> <?=VERSION?> </span></h6>
	  <h6>Controller <span class="badge"> <?=Core\Controller::$version?> </span></h6>
	  <h6>Model <span class="badge"> <?=Core\Model::$version?> </span></h6>
	  <h6>PHP Version  <span class="badge"><?php echo phpversion(); ?></span></h6>
  </div>
	<div class="panel-body">	
		<ul>
		<li>Last review: 2024-06-16 19:08</li>
		<li>@added function add_record():int</li>
		<li>@added function primary_column()</li>
		<li>@optimized function set_line()</li>
		</ul>
	</div>
</div>
<!--<div class="panel panel-primary">
  <div class="panel-heading">Database : <?php echo '<strong>'.$file.'</strong>'; ?></div>
	  <div class="panel-body">
		  <ul>
			<li>File size : <?php echo $ffilesize; ?> octets</li>
			<li>Import | Export : [<em>.php</em>, <em>.ser</em>, <em>.json</em>]</li>
			<li>Symmetry : <?php echo '['.$numtables.']['.$maxlines.']['.$maxcols.']'; ?></li>
			<li>Number of tables : <?php echo $numtables; ?></li>
			<li>Number of lines : <?php echo $maxlines; ?></li>
			<li>Number of columns : <?php echo $maxcols; ?></li>
		  <ul>
	  </div>
</div>-->