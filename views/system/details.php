<blockquote><?php echo WEBROOT.$link;?></blockquote>
<div class="panel panel-primary">
  <div class="panel-heading">Database : <?php echo '<strong>'.$file.'</strong>'; ?></div>
	  <div class="panel-body">
		  <ul>
			<li>File size : <?php echo $ffilesize; ?> octets</li>
			<li>Import | Export : <span class="badge">php</span><span class="badge">csv</span><span class="badge">json</span><span class="badge">js</span></li>
			<li>Symmetry : <?php echo '['.$numtables.']['.$maxlines.']['.$maxcols.']'; ?></li>
			<li>Number of tables : <?php echo $numtables; ?></li>
			<li>Max number of lines : <?php echo $maxlines; ?></li>
			<li>Max number of columns : <?php echo $maxcols; ?></li>
			<li>Show limit default: <?php echo $showlimitdefault; ?> per page</li>
			<li>Offset default: <?php echo $offsetdefault;  ?></li>
		  <ul>
	  </div>
</div>
