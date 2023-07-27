<blockquote>DIALOG</blockquote>
<form name="frmQuestion" action="<?php echo $action; ?>" method="post">
<div class="panel panel-default">
  <div class="panel-heading">Hi, i have a question.</div>
	  <div class="panel-body">
		<p><?php echo $question; ?></p>
		<label class="radio-inline">
		  <input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="yes"> Yes
		</label>
		<label class="radio-inline">
		  <input type="radio" name="inlineRadioOptions" id="inlineRadio2" value="no"> No
		</label>
		<?php
		if(isset($post))
		{
			foreach($post as $col=>$value)
			{
				echo '<input id="'.$col.'" name="'.$col.'" type="hidden" value="'.$value.'">';
			}		
		}
		?>
	  </div>
	</div>
	<button  onclick="includeHTML()" type="submit" class="btn btn-default">Submit</button>
</form>