<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<base href="<?php echo WEBROOT; ?>">
<title><?php echo (isset($title))? $title:"$title"; ?></title>
<meta name="description" content="<?php echo (isset($desc))? $desc:"$desc"; ?>">
<meta name="author" content="<?php echo (isset($author))? $author:"$author"; ?>">
<meta name="keywords" content="<?php echo (isset($keywords))? $keywords:"$keywords"; ?>">
<link rel="icon" type="image/ico" href="<?php echo WEBROOT; ?>favicon.ico">
<?php
if(!empty($data)) extract($data);
$path =(isset($path))? $path.'/' : '';
?>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/myjavascript.js"></script>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/action.js"></script>
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/bootstrap.min.css" media="screen">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/actions.css" media="screen">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/actionsprint.css" media="print">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/note.css" media="screen">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/nav.css" media="screen">
<script>
$(document).ready(function(){

	$("#strtable").change(function(){
		var stable = $(this).val();
		$.ajax({
			url: 'main/get_fields',
			type: 'post',
			data: {strtable:stable},
			dataType: 'json',
			success:function(response){

				var len = response.length;

				$("#strfield").empty();
				for( var i = 0; i<len; i++){
					var id = response[i]['id'];
					var col = response[i]['col'];

					$("#strfield").append("<option value='"+col+"'>"+col+"</option>");
				}

				$("#tofield").empty();
				for( var i = 0; i<len; i++){
					var id = response[i]['id'];
					var col = response[i]['col'];

					$("#tofield").append("<option value='"+col+"'>"+col+"</option>");
				}
				
				$("#lstring").empty();
				for( var i = 0; i<len; i++){
					var id = response[i]['id'];
					var col = response[i]['col'];

					$("#lstring").append("<option value='"+col+"'>");
				}
				
				$("#unique").empty();
				for( var i = 0; i<len; i++){
					var id = response[i]['id'];
					var col = response[i]['col'];

					$("#unique").append("<option value='"+col+"'>"+col+"</option>");
				}
			}
		});
	});
	
	$("#totable").change(function(){
		var stable = $(this).val();
		$.ajax({
			url: 'main/get_sfields',
			type: 'post',
			data: {totable:stable},
			dataType: 'json',
			success:function(response){

				var len = response.length;

				$("#tofield").empty();
				for( var i = 0; i<len; i++){
					var id = response[i]['id'];
					var col = response[i]['col'];

					$("#tofield").append("<option value='"+col+"'>"+col+"</option>");
				}
			}
		});
	});
	
});
</script>