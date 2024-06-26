<?php ?>
<h1>What is BASE?</h1>
<h3>BASE is a 3D array datafile management system (.php).</h3>

<h4>Data structure</h4>
<p>All data is stored in a three-dimensional array. So, for each piece of data, a [table][row][column] coordinate.</p>

<p>But where to store the table names in this case? Table names are stored at indices [0][0][table] for example:</p>
<pre>
$data[0][0][1]='tableOne';
$data[0][0][2]='tableTwo';
</pre>

<h4>Column names are stored at line zero [table][0][column] for example:</h4>
<pre>
$data[1][0][1]='field1';
$data[1][0][2]='field2';
$data[2][0][1]='field1';
$data[2][0][2]='field2';
</pre>

<h4>Concrete example of a datafile in php...</h4>
<pre>
$data[0][0][1]='persons';
$data[1][0][1]='id_person';
$data[1][0][2]='name';
$data[1][0][3]='firstname';
$data[1][1][1]='1';
$data[1][1][2]='trump';
$data[1][1][3]='donald';
$data[1][2][1]='2';
$data[1][2][2]='obama';
$data[1][2][3]='barack';
</pre>