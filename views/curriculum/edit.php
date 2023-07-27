<form action="<?php echo $action; ?>" method="post" >
	<legend><?php echo $legend ?></legend>
	<div class="form-group">
		<h5><?php echo $tip?></h5>
		<label for="<?php echo $name ?>"><?php echo $name ?></label>
		<input type="text" class="form-control" id="<?php echo $name ?>" name="<?php echo $name ?>" placeholder="<?php echo $placeholder ?>"  title="<?php echo $name ?>">
	</div>
	<button type="submit" class="btn btn-default">Save</button>
</form>