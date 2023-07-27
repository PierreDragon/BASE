<?php
if(isset($strTable))
{
	//include("top-cache.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
	<?php echo (isset($head))? $head:'$head'; ?>
</head>
  <body style="overflow-y:scroll;">
	<div id="page" class="container-fluid">
		<div class="row-fluid">
			<div id="banner" class="col-xs-12">
				<?php 
				if(isset($_SESSION['jumbo']) && $_SESSION['jumbo']===TRUE)
				{
					echo (isset($banner))? $banner:'$banner'; 
				}
				?>
			</div>
		</div>
		<div class="row-fluid">
			<div id="navigation" class="col-xs-12">
				<?php echo (isset($nav))? $nav:'$nav'; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div id="menuleft" class="col-xs-3">
				<?php echo (isset($left))? $left:'$left'; ?>
			</div>
			<div id="contenu" class="col-xs-9">
				<?php echo (isset($msg))? $msg:'$msg'; ?>
				<div w3-include-html="<?php echo WEBROOT; ?>views/loader.php"></div>
				<?php echo (isset($content))? $content:'$content'; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div id="footer" class="col-xs-12">
				<?php echo (isset($footer))? $footer:'$footer'; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="col-xs-6">
				<h4 id="copyright"><?php echo(isset($copyright))?$copyright:'$copyright'; ?></h4> 
			</div>
			<div class="col-xs-6">
				<h6 class="text-right"><?=$title?> <?=$author?></h6>
			</div>
		</div>
	</div>
  </body>
</html>
<?php
if(isset($strTable))
{
//	include("bottom-cache.php");
}
?>