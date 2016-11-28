<?php
/*
	je pense qu'un rouge plus rouge serai mieu...
	implémentation des multi link et des dossiers
	rajouter une initialisation des tables après avoir checker qu'elle existent ou pas si elles existent pas => un peu de regex
	ajouter un système de termin...
	ajouter StudentTask c'est a dire une liste des choses à faire dans la semaine avec un overview de ce qui reste à faire et ce qui est déjà fait
	agenda en gros
	je pense qu'il faut virer série et le mettre comme lien mais bon comme de tt façon y a pas de db showtracteur sur cette ordi...
	rajouter des animations je pense ça va avec le fait de différencier le php et le js sur une app. on verra avec auxilio 4.0
	rajouter des tag pour les notes et les todo
	update l'icone => trouver un logo
	passer les new note et new todo en POST
	update todo/notes


*/
/*
	Dump:
	
	header('Location: http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$result['show_imdbID']);
	die();
	
	echo '<script type="text/javascript">
           window.location = "http://localhost/showTracteur/showTracteur.php?page=tvShow&imdbID='.$result['show_imdbID'].'"
      </script>';
      
    
    print "<pre>";
	print_r($episodeToWatch);
	print "</pre>";
  
*/

///functions

function loadDB($caller, $dbName='auxilio'){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname='.$dbName, 'root', '');
		$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $bdd;
	}catch (Exception $e){
		alertError($e->getMessage(), $caller);
	}
}

checkDB();

function checkDB(){
	$db = loadDB(__FUNCTION__, 'information_schema');
	//$table_array = [note, todo];
	
	$stmt = $db->prepare("SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = 'auxilio';");
		
	try{
		$stmt->execute();
	}catch (Exception $e){
		alertError($e->getMessage(), __FUNCTION__);
		die();
	}
	$result = $stmt->fetch();
	
	//alertError($result, __FUNCTION__);
	unset($stmt);
	
	//...
	//create auxilio
	
	//CREATE TABLE `auxilio`.`note` ( `id` INT NOT NULL AUTO_INCREMENT , `value` TEXT NOT NULL , `displayNote` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`id`)) ENGINE = MyISAM;
	//CREATE TABLE `auxilio`.`todo` ( `id` INT NOT NULL AUTO_INCREMENT , `value` INT NOT NULL , `displayStatus` BOOLEAN NOT NULL DEFAULT TRUE , `prio` INT(1) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM; 
}

function alertError($error, $process){
	echo '<script type="text/javascript">
    	alert("The process identified as || '.$process.' || recieved the error :'.$error.'");
    </script>';
}
?>
<!-- over this line everything is strictly php-->
<!DOCTYPE html>
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="images/icone.ico" type="image/x-icon" />
	<title>Auxilio 3.0 #Beta</title>
	<meta name="description" content="To serve and help" />
	<meta name="keywords" content="auxilio,accuile,starting page, 3.0"/>
    <link type="text/css" rel="stylesheet" href="default.css" />
    <script type="text/javascript">
    	/* ########## affichage de la bonne page ########## */
    	function setArticle(article ){
			
    		//en fct de la page détérminer la classe hidden			
			var x = document.getElementById("article");
			var y = x.getElementsByTagName("article");
			var i;
			for (i = 0; i < y.length; i++) {
			    y[i].className = "hidden";
			}
    		document.getElementById(article).className = "show";
    		
    		
    		//just un peu de styling
			var b = document.getElementsByName("navLink");
			var j;
			for (j = 0; j < b.length; j++) {
			    b[j].style.backgroundColor = '#0074D9';
    			b[j].style.color = '#222';
			}
			//c est la divLink de l'article séléctionné
    		var c = document.getElementById('nav'+ article.charAt(0).toUpperCase() + article.slice(1))
    		c.style.backgroundColor = '#0064C9';
    		c.style.color = '#000';
    	}
    	
		//pour quand on press enter dans un form
		function inputKeyUp(e, action) {
    		e.which = e.which || e.keyCode;
    		if(e.which == 13 && action == 'saveNote') {
     		   saveNote();
    		}else if(e.which == 13 && action == 'saveTodo') {
     		   saveTodo();
    		}else if(e.which == 13 && action == 'saveLink') {
     		   saveLink();
			}else if(e.which == 13 && action == 'saveUpdateLink') {
     		   saveUpdateLink();
    		}
		}
    	
    	
    	/* ########## clock ########## */
    	//source:http://www.w3schools.com/js/tryit.asp?filename=tryjs_timing_clock
    	function showClock() {
    		var today = new Date();
    		var h = today.getHours();
    		var m = today.getMinutes();
    		var s = today.getSeconds();
    		h = checkTime(h);
    		m = checkTime(m);
    		s = checkTime(s);
    		document.getElementById('clockToSleep').innerHTML = h + ":" + m + ":" + s;
    		var t = setTimeout(showClock, 500);
		}
		function checkTime(i) {
    		if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    		return i;
		}
		
		function getFocus(input) {
    		document.getElementById(input).focus();
		}
		
    	//console.log('salut');
    	//alert(linkOfHome[0][0]);
    </script>
    <style>
    	
    </style>
  </head>
  <?php
  	//$page = 'home';
	$page = 'link';
	$fct = '';
  	if(isset($_GET['page'])){
  		$page = $_GET['page'];
		if($page=='note'){
			$fct .= " getFocus('noteInput');";
		}elseif($page=='todo'){
			$fct .= " getFocus('todoInput');";
		}
  	}
	if(isset($_POST['page'])){
  		$page = $_POST['page'];
		if($page=='note'){
			$fct .= " getFocus('noteInput');";
		}elseif($page=='todo'){
			$fct .= " getFocus('todoInput');";
		}
  	}
  		
  	echo 
'<body onload="setArticle(\''.$page.'\'); showClock();'.$fct.'">';
	?>
    <header>
    		<div id="logo" class="clickable" onclick="window.location = 'http://localhost/auxilio/auxilio3_0.php'">
    			Auxilio 3.0 <span class="red">#Beta</span>
    		</div>
    		<div id="clockToSleep" class="clock">
    			06:13:12
    		</div>
    		
    </header>
    <nav>
    	<div id="navMenu">
    		<div class="navLink" name="navLink" onclick="setArticle('home')" id="navHome">
    			Home
    		</div>
			<div class="navLink" name="navLink" onclick="setArticle('link')" id="navLink">
    			Liens
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('note'); getFocus('noteInput');" id="navNote">
    			Notes
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('todo'); getFocus('todoInput');" id="navTodo">
    			A faire
    		</div>
    		<div class="navLink" name="navLink" onclick="window.open('http://localhost/showTracteur/showTracteur.php')" id="navShow">
    			Series
    		</div>
    		<div class="navLink" name="navLink" onclick="setArticle('para')" id="navPara">
    			Param
    		</div>
    	</div>
    
    </nav>
    
    <div class="container" id="article">
    	<!-- ========== HOME ========== -->
    	<article id="home">
    		<?php include 'include3_0/home.php';?>	
    	</article>
		<!-- ========== LINK ========== -->
    	<article id="link">
    		<?php include 'include3_0/link.php';?>	
    	</article>
    	<!-- ========== NOTE ========== -->
    	<article id="note">
    		<?php include 'include3_0/note.php';?>
    	</article>
    	<!-- ========== TODO ========== -->
    	<article id="todo">
    		<?php include 'include3_0/toDo.php';?>
    	</article>
    	<!-- ========== PARA ========== -->
    	<article id="para">
    		<?php include 'include3_0/para.php';?>
    	</article>
	</div>
    
    <footer>
    	<p>
    		Copyright &copy; 2016 userOne0One
    	</p>
    </footer>
    <script type="text/javascript">
    	//where to call functions
		
    	//setArticle();
    	
    </script>
  </body>
</html>
