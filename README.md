# BASE
NO SQL PHP datafiles

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
$data[0][0][1]='persons';
$data[0][0][2]='cities';

$data[1][0][1]='id_person';
$data[1][0][2]='name';
$data[1][0][3]='firstname';

$data[2][0][1]='id_citie';
$data[2][0][2]='citie';

$data[1][1][1]='1';
$data[1][1][2]='trump';
$data[1][1][3]='donald';

$data[1][2][1]='2';
$data[1][2][2]='obama';
$data[1][2][3]='barack';
  
$data[2][1][1]='1';
$data[2][1][2]='montréal';
  
$data[2][2][1]='2';
$data[2][2][2]='new-york';
</pre>

## TUTORIAL
<p>All you have to do is put all the folders and files on a server with php. (> php7)<p>
