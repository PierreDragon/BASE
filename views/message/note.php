<blockquote><?php echo WEBROOT.$link;?></blockquote>
<div class="panel panel-primary">
  <div class="panel-heading"><h3>Coordinate <?php echo $coord; ?></h3></div>
	  <div class="panel-body">
		  <ul>
			<li>Coordinate <?php echo $coord; ?> = "<?php echo $cell; ?>"</li>
			<li>Coordinate [<?=$i_table?>][<?=$i_line?>] as php object : <?php print_r($obj); ?></li>
			<li>Coordinate [<?=$i_table?>][<?=$i_line?>] as .json format: <?php echo($json); ?></li>
		  <ul>
	  </div>
</div>