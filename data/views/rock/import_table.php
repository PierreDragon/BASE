<form role="form" action="<?php echo $action; ?>" method="post">
  <legend><?php echo $legend ?></legend>
  <?php echo $list; ?>
  <br>
  <button onclick="includeHTML()" type="submit" class="btn btn-default">Import</button>
</form>