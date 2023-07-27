<!DOCTYPE html>
 <head>
 <?php echo (isset($head))? $head:'$head'; ?>
 <title>Example</title>
 </head>
<style>
* {
  box-sizing: border-box; 
}
body {
  display: flex;
  min-height: 100vh;
  flex-direction: column;
  margin: 0;
}
#main {
  display: flex;
  flex: 1;
}
#main > article {
  flex: 1;
}
#main > nav, 
#main > aside {
  flex: 0 0 20vw;
 /* background: beige;*/
}
#main > nav {
  order: -1;
}
header, footer {
  /*background: yellowgreen;*/
  height: 20vh;
    height: 10vh;
}
header, footer, article, nav, aside {
  padding: 1em;
}
@media screen and (max-width: 575px) {
  #main {
    display: block;
  }
}
</style>
<body>
  <header><?php echo (isset($header))? $header:'$header'; ?></header>
  <div id="main">
    <article><?php echo (isset($content))? $content:'$content'; ?></article>
    <nav><?php echo (isset($left))? $left:'$left'; ?></nav>
    <aside><?php echo (isset($right))? $right:'$right'; ?></aside>
  </div>
  <footer><?php echo (isset($footer))? $footer:'$footer'; ?></footer>
</body>