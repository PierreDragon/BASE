<script>
function HideBanner() {
  var x = document.getElementById("banner");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
  $('#div_session_write').load('index.php?jumbo=0');
}
</script>
<style> 
#bouton {
 line-height: 12px;
 width: 18px;
 font-size: 8pt;
 font-family: tahoma;
 margin-top: 1px;
 margin-right: 2px;
 position:absolute;
 top:0;
 right:0;
 }
 </style>
<div class="jumbotron jumbotron-fluid">
    <button onclick="HideBanner()" type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
    <h1 class="display-4" style="color: #337ab7;"><?php echo (isset($title))? $title:"$title"; ?></h1>
	<p class="lead"><?php echo (isset($desc))? $desc:"$desc"; echo '&nbsp;version '. VERSION; ?></p>
</div>
<div id='div_session_write'></div>