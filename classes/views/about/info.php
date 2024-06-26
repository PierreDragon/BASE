<?php ?>
<h1>What is BASE?</h1>
<h3>BASE is a 3D matrix datafile management system (.php).</h3>
<p>BASE allows you to manage data files without the need for an SQL server. It uses an efficient and robust algorithm, which guarantees speed, reliability and security of operations. BASE is a versatile computer program, which adapts to different types of data, such as texts, numbers, images or sounds. BASE is a computer program that simplifies users' lives by offering them a practical and efficient solution for managing their data files.</p>

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

<h4>Relation between tables</h4>
<p>There is only a one to many relationship in the system. However, it is possible to create a many-to-many relationship by creating an intermediate table that will combine the primary keys of the two tables you want to join. The structure of the relationships between the tables is only at one level. That is to say that a master table can have one or more slave tables. Deleting a record in the master table deletes all records from the slave tables.</p>
<h5><strong>Many to many, e.g.</strong></h5>
<img style="height: auto;  width: auto;  max-width: 500px; max-height: 500px;" src="<?=ASSETDIRECTORY.$path ?>img/many_to_many.png" alt="many_to_many.png" />