<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="expires" content="-1" />
<meta http-equiv="pragma" content="no-cache" />
<base href="<?php echo WEBROOT; ?>">
<title><?php echo (isset($title))? $title:"$title";?></title>
<meta name="description" content="<?php echo (isset($desc))? $desc:"$desc"; ?>">
<meta name="author" content="<?php echo (isset($author))? $author:"$author"; ?>">
<meta name="keywords" content="<?php echo (isset($keywords))? $keywords:"$keywords"; ?>">
<link rel="icon" type="image/png" href="<?php echo WEBROOT; ?>favicon.png">
<?php
if(!empty($data)) extract($data);
$path =(isset($path))? $path.'/' : '';
?>
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/bootstrap.min.css" media="screen">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/note.css" media="screen">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/nav.css" media="screen">
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/myjavascript.js"></script>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/jquery.jeditable.js"></script>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/jquery-ui.min.js"></script>
<script>
$(document).ready(function(){
    $( ".row_drag" ).sortable({
        delay: 100,
        stop: function() {
            var selectedRow = new Array();
            $('.row_drag>tr').each(function() {
                selectedRow.push($(this).attr("id"));
            });
           //alert(selectedRow);
        }
    });
	//$("#td1").editable("/conversion/main/set_cell/2/1/1",{ name : 'value'});
	//$("td").editable("/conversion/main/set_cell/");
});
</script>