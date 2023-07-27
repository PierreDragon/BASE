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
			<li>Show limit : <?php echo $showlimit; ?></li>
			<li>Load limit : <?php echo $offset; ?></li>
		  <ul>
	  </div>
</div>
<pre>
What is BASE?
BASE is a format data file management system (.php).

Data structure
All data is stored in a three-dimensional array. So, for each piece of data, a [table][row][column] coordinate.

But where to store the table names in this case? Table names are stored at indices [0][0][n] for example:
$data[0][0][1]='tableOne';
$data[0][0][2]='tableTwo';

Column names are stored at line [n][0][n] for example:
$data[1][0][1]='field1';
$data[1][0][2]='field2';
$data[2][0][1]='field1';
$data[2][0][2]='field2';

Concrete example of a data file in php...
$data[0][0][1]='people';
$data[1][0][1]='name';
$data[1][0][2]='firstname';
$data[1][1][1]='trump';
$data[1][1][2]='donald';
$data[1][2][1]='obama';
$data[1][2][2]='barack';
</pre>