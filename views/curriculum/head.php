<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />						   
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<base href="<?php echo WEBROOT; ?>">
<title><?php echo (isset($title))? $title:"$title";?></title>
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
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/jquery.jeditable.js"></script>
<script type="text/javascript" src="<?=ASSETDIRECTORY?><?=$path?>js/jquery-ui.min.js"></script>

<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/bootstrap.css" media="screen">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/note.css" media="screen">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/nav.css" media="screen">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:wght@100&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=ASSETDIRECTORY?><?=$path?>css/print.css" media="print">
    
<script>
$(document).ready(function(){
	
	$("#strtable").change(function(){
		var stable = $(this).val();
		$.ajax({
			url: '/main/get_fields',
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
			}
		});
	});
	
	$("#totable").change(function(){
		var stable = $(this).val();
		$.ajax({
			url: '/main/get_fields',
			type: 'post',
			data: {strtable:stable},
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
		
	$( "#accordion" ).accordion();
});
</script>